<?php

namespace App\Controllers;

use App\Libraries\BookingAvailability;
use App\Libraries\PayMongoCheckout;
use App\Models\BookingModel;
use App\Models\RoomModel;
use App\Models\TenantModel;
use CodeIgniter\HTTP\RedirectResponse;
use DateTimeImmutable;
use Throwable;

class TenantBookingsController extends BaseController
{
    private BookingAvailability $availability;

    private PayMongoCheckout $paymongo;

    public function __construct()
    {
        $this->availability = new BookingAvailability();
        $this->paymongo     = new PayMongoCheckout();
    }

    public function index(): string
    {
        $bookings = (new BookingModel())
            ->withRelations()
            ->where('bookings.tenant_id', auth_tenant_id())
            ->orderBy('bookings.check_in', 'DESC')
            ->findAll();

        return view('tenant/bookings/index', [
            'bookings' => $bookings,
            'title'    => 'My Bookings',
        ]);
    }

    public function rooms(): string
    {
        sync_room_booking_statuses();

        $search       = $this->bookingSearchStateFromRequest();
        $searchErrors = [];
        $rooms        = [];
        $selectedRoom = null;

        if ($search['submitted']) {
            $searchErrors = $this->validateSearch($search);

            if ($searchErrors === []) {
                $rooms = $this->buildRoomCatalog($search);

                $selectedRoom = $this->resolveSelectedRoom(
                    array_values(array_filter(
                        $rooms,
                        static fn (array $room): bool => (bool) ($room['is_bookable'] ?? false)
                    )),
                    $search['selected_room_id']
                );
            }
        }

        return view('tenant/rooms/index', [
            'rooms'          => $rooms,
            'paymongoReady'  => $this->paymongo->isConfigured(),
            'search'         => $search,
            'searchErrors'   => $searchErrors,
            'selectedRoom'   => $selectedRoom,
            'stayNights'     => $search['submitted'] && $searchErrors === [] ? $this->availability->countNights($search['check_in'], $search['check_out']) : 0,
            'title'          => 'Rooms',
        ]);
    }

    public function book(): RedirectResponse
    {
        if (! $this->paymongo->isConfigured()) {
            return redirect()->to(tenant_path('myRooms'))
                ->with('warning', 'PayMongo is not configured yet. Add your PayMongo keys before accepting tenant payments.');
        }

        $requestData = [
            'check_in'  => trim((string) $this->request->getPost('check_in')),
            'check_out' => trim((string) $this->request->getPost('check_out')),
            'guests'    => trim((string) $this->request->getPost('guests')),
            'notes'     => trim((string) $this->request->getPost('notes')),
            'room_id'   => (int) $this->request->getPost('room_id'),
            'selected_room_id' => (int) $this->request->getPost('room_id'),
        ];

        $errors = $this->validateSearch($requestData);

        if ($requestData['room_id'] <= 0) {
            $errors['room_id'] = 'Select a room before continuing to payment.';
        }

        if ($errors !== []) {
            return redirect()->to($this->buildSearchUrl($requestData))
                ->with('error', implode(' ', array_values($errors)));
        }

        $room = (new RoomModel())->find($requestData['room_id']);

        if ($room === null || ($room['status'] ?? '') !== 'available') {
            return redirect()->to($this->buildSearchUrl($requestData))
                ->with('error', 'The selected room is no longer available for online booking.');
        }

        if ($requestData['guests'] !== '' && (int) ($room['capacity'] ?? 0) < (int) $requestData['guests']) {
            return redirect()->to($this->buildSearchUrl($requestData))
                ->with('error', 'The selected room does not fit the requested guest count.');
        }

        if ($this->availability->hasDateConflict($requestData['room_id'], $requestData['check_in'], $requestData['check_out'])) {
            return redirect()->to($this->buildSearchUrl($requestData))
                ->with('error', 'That room has already been reserved for the selected stay dates.');
        }

        $totalAmount = $this->availability->calculateTotalAmount($room, $requestData['check_in'], $requestData['check_out']);

        if ($totalAmount <= 0) {
            return redirect()->to($this->buildSearchUrl($requestData))
                ->with('error', 'The booking amount could not be calculated for the selected stay dates.');
        }

        $bookingModel = new BookingModel();
        $bookingModel->insert([
            'check_in'     => $requestData['check_in'],
            'check_out'    => $requestData['check_out'],
            'notes'        => $requestData['notes'],
            'room_id'      => $requestData['room_id'],
            'status'       => 'awaiting_payment',
            'tenant_id'    => auth_tenant_id(),
            'total_amount' => $totalAmount,
        ]);

        $bookingId = (int) $bookingModel->getInsertID();
        $booking   = $bookingModel->find($bookingId);

        if ($booking === null) {
            return redirect()->to($this->buildSearchUrl($requestData))
                ->with('error', 'The booking hold could not be prepared for payment.');
        }

        try {
            $checkout = $this->paymongo->createCheckoutSession(
                $booking,
                $room,
                (new TenantModel())->find(auth_tenant_id())
            );
        } catch (Throwable $exception) {
            $bookingModel->delete($bookingId);

            log_message('error', 'PayMongo checkout creation failed for booking #{id}: {message}', [
                'id'      => $bookingId,
                'message' => $exception->getMessage(),
            ]);

            return redirect()->to($this->buildSearchUrl($requestData))
                ->with('error', $exception->getMessage());
        }

        $bookingModel->update($bookingId, [
            'checkout_session_id' => $checkout['session_id'],
            'checkout_url'        => $checkout['checkout_url'],
            'payment_reference'   => $checkout['reference_number'] !== '' ? $checkout['reference_number'] : null,
        ]);

        return redirect()->to((string) $checkout['checkout_url']);
    }

    public function paymentSuccess(): RedirectResponse
    {
        $booking = $this->tenantBookingFromQuery();

        if ($booking instanceof RedirectResponse) {
            return $booking;
        }

        if (($booking['status'] ?? '') === 'cancelled') {
            return redirect()->to(tenant_path('myBookings'))
                ->with('warning', 'This booking hold has already been cancelled.');
        }

        if (($booking['status'] ?? '') !== 'awaiting_payment') {
            return redirect()->to(tenant_path('myBookings'))
                ->with('success', 'This booking is already secured in your account.');
        }

        $paymentCheck = $this->checkBookingPayment($booking);

        if ($paymentCheck['error'] !== null) {
            return redirect()->to(tenant_path('myBookings'))
                ->with('warning', 'We could not confirm the payment yet. Please open My Bookings again in a moment.');
        }

        if ($paymentCheck['paid']) {
            $this->markBookingAsPaid($booking, $paymentCheck['session']);

            return redirect()->to(tenant_path('myBookings'))
                ->with('success', 'Payment received. Your room is now secured.');
        }

        if (in_array($paymentCheck['status'], ['cancelled', 'expired'], true)) {
            (new BookingModel())->update((int) $booking['id'], ['status' => 'cancelled']);

            return redirect()->to(tenant_path('myBookings'))
                ->with('warning', 'The checkout was not completed, so the room hold was released.');
        }

        return redirect()->to(tenant_path('myBookings'))
            ->with('warning', 'Payment is still incomplete. Use Continue Payment to finish the booking.');
    }

    public function paymentCancel(): RedirectResponse
    {
        $booking = $this->tenantBookingFromQuery();

        if ($booking instanceof RedirectResponse) {
            return $booking;
        }

        return $this->cancelAwaitingPaymentBooking($booking, 'Payment cancelled. The room slot is available again.');
    }

    public function cancel(int $id): RedirectResponse
    {
        $booking = $this->findTenantBooking($id);

        if ($booking === null) {
            return redirect()->to(tenant_path('myBookings'))
                ->with('error', 'That booking could not be found in your account.');
        }

        return $this->cancelAwaitingPaymentBooking($booking, 'Unpaid booking cancelled and the room hold released.');
    }

    /**
     * @param array<string, mixed> $booking
     */
    private function cancelAwaitingPaymentBooking(array $booking, string $successMessage): RedirectResponse
    {
        if (($booking['status'] ?? '') === 'cancelled') {
            return redirect()->to(tenant_path('myBookings'))
                ->with('warning', 'That booking hold has already been cancelled.');
        }

        if (($booking['status'] ?? '') !== 'awaiting_payment') {
            return redirect()->to(tenant_path('myBookings'))
                ->with('warning', 'Only unpaid booking holds can be cancelled from the tenant portal.');
        }

        $paymentCheck = $this->checkBookingPayment($booking);

        if ($paymentCheck['error'] !== null) {
            if ($paymentCheck['error'] === 'missing_configuration') {
                (new BookingModel())->update((int) $booking['id'], ['status' => 'cancelled']);

                return redirect()->to(tenant_path('myBookings'))
                    ->with('success', $successMessage);
            }

            return redirect()->to(tenant_path('myBookings'))
                ->with('warning', 'We could not confirm the payment status, so the booking was left unchanged. Please try again shortly.');
        }

        if ($paymentCheck['paid']) {
            $this->markBookingAsPaid($booking, $paymentCheck['session']);

            return redirect()->to(tenant_path('myBookings'))
                ->with('success', 'Payment was already completed, so the booking remains secured.');
        }

        (new BookingModel())->update((int) $booking['id'], ['status' => 'cancelled']);

        return redirect()->to(tenant_path('myBookings'))
            ->with('success', $successMessage);
    }

    /**
     * @param array<string, mixed> $booking
     * @param array<string, mixed> $session
     */
    private function markBookingAsPaid(array $booking, array $session): void
    {
        $paidAt = $this->paymongo->getPaidAt($session) ?? gmdate('Y-m-d H:i:s');
        $data   = [
            'payment_paid_at' => $paidAt,
            'status'          => 'pending',
        ];

        $referenceNumber = $this->paymongo->getReferenceNumber($session);
        if ($referenceNumber !== '') {
            $data['payment_reference'] = $referenceNumber;
        }

        (new BookingModel())->update((int) $booking['id'], $data);
    }

    /**
     * @param array<string, mixed> $booking
     *
     * @return array{error: string|null, paid: bool, session: array<string, mixed>, status: string}
     */
    private function checkBookingPayment(array $booking): array
    {
        $sessionId = trim((string) ($booking['checkout_session_id'] ?? ''));

        if ($sessionId === '' || ! $this->paymongo->isConfigured()) {
            return [
                'error'   => 'missing_configuration',
                'paid'    => false,
                'session' => [],
                'status'  => '',
            ];
        }

        try {
            $session = $this->paymongo->retrieveCheckoutSession($sessionId);
        } catch (Throwable $exception) {
            log_message('error', 'PayMongo checkout retrieval failed for booking #{id}: {message}', [
                'id'      => (int) ($booking['id'] ?? 0),
                'message' => $exception->getMessage(),
            ]);

            return [
                'error'   => $exception->getMessage(),
                'paid'    => false,
                'session' => [],
                'status'  => '',
            ];
        }

        return [
            'error'   => null,
            'paid'    => $this->paymongo->isPaid($session),
            'session' => $session,
            'status'  => $this->paymongo->getStatus($session),
        ];
    }

    /**
     * @return array<string, mixed>|RedirectResponse
     */
    private function tenantBookingFromQuery()
    {
        $bookingId = (int) $this->request->getGet('booking');

        if ($bookingId <= 0) {
            return redirect()->to(tenant_path('myBookings'))
                ->with('error', 'A booking reference is required to confirm the payment result.');
        }

        $booking = $this->findTenantBooking($bookingId);

        if ($booking === null) {
            return redirect()->to(tenant_path('myBookings'))
                ->with('error', 'That booking could not be found in your account.');
        }

        return $booking;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function findTenantBooking(int $bookingId): ?array
    {
        return (new BookingModel())
            ->where('id', $bookingId)
            ->where('tenant_id', auth_tenant_id())
            ->first();
    }

    /**
     * @param array<string, mixed> $search
     *
     * @return list<array<string, mixed>>
     */
    private function buildRoomCatalog(array $search): array
    {
        $guestCount = $search['guests'] === '' ? null : (int) $search['guests'];
        $rooms = (new RoomModel())
            ->orderBy('room_number', 'ASC')
            ->findAll();

        return array_map(function (array $room) use ($search, $guestCount): array {
            $hasConflict = $this->availability->hasDateConflict(
                (int) ($room['id'] ?? 0),
                (string) $search['check_in'],
                (string) $search['check_out']
            );

            $displayStatus = $this->resolveTenantRoomStatus($room, $hasConflict);
            $fitsGuests = $guestCount === null || (int) ($room['capacity'] ?? 0) >= $guestCount;

            $room['pricing_hours'] = max(1, (int) ($room['pricing_hours'] ?? 1));
            $room['stay_nights']   = $this->availability->countNights($search['check_in'], $search['check_out']);
            $room['stay_total']    = $this->availability->calculateTotalAmount($room, $search['check_in'], $search['check_out']);
            $room['display_status'] = $displayStatus;
            $room['is_bookable']    = $displayStatus === 'available' && $fitsGuests;

            return $room;
        }, $rooms);
    }

    /**
     * @param array<string, mixed> $search
     *
     * @return array<string, string>
     */
    private function validateSearch(array $search): array
    {
        $errors   = [];
        $checkIn  = trim((string) ($search['check_in'] ?? ''));
        $checkOut = trim((string) ($search['check_out'] ?? ''));
        $guests   = trim((string) ($search['guests'] ?? ''));
        $notes    = trim((string) ($search['notes'] ?? ''));
        $today    = date('Y-m-d');

        if ($checkIn === '') {
            $errors['check_in'] = 'Choose a check-in date.';
        } elseif (! $this->isValidDate($checkIn)) {
            $errors['check_in'] = 'Use the YYYY-MM-DD format for check-in.';
        } elseif ($checkIn < $today) {
            $errors['check_in'] = 'Check-in date cannot be earlier than today.';
        }

        if ($checkOut === '') {
            $errors['check_out'] = 'Choose a check-out date.';
        } elseif (! $this->isValidDate($checkOut)) {
            $errors['check_out'] = 'Use the YYYY-MM-DD format for check-out.';
        } elseif ($checkIn !== '' && $this->isValidDate($checkIn) && $checkOut <= $checkIn) {
            $errors['check_out'] = 'Check-out date must be after the check-in date.';
        }

        if ($guests !== '' && (! ctype_digit($guests) || (int) $guests < 1)) {
            $errors['guests'] = 'Guest count must be at least 1.';
        }

        if (mb_strlen($notes) > 500) {
            $errors['notes'] = 'Arrival notes can only be up to 500 characters.';
        }

        return $errors;
    }

    /**
     * @param array<string, mixed> $search
     */
    private function buildSearchUrl(array $search): string
    {
        $query = array_filter([
            'check_in'  => trim((string) ($search['check_in'] ?? '')),
            'check_out' => trim((string) ($search['check_out'] ?? '')),
            'guests'    => trim((string) ($search['guests'] ?? '')),
            'notes'     => trim((string) ($search['notes'] ?? '')),
            'selected_room' => (int) ($search['selected_room_id'] ?? 0) > 0 ? (string) (int) $search['selected_room_id'] : '',
        ], static fn (string $value): bool => $value !== '');

        return tenant_path('myRooms') . ($query === [] ? '' : '?' . http_build_query($query));
    }

    /**
     * @return array{check_in: string, check_out: string, guests: string, notes: string, selected_room_id: int, submitted: bool}
     */
    private function bookingSearchStateFromRequest(): array
    {
        $today = new DateTimeImmutable('today');
        $search = [
            'check_in'         => trim((string) $this->request->getGet('check_in')),
            'check_out'        => trim((string) $this->request->getGet('check_out')),
            'guests'           => trim((string) $this->request->getGet('guests')),
            'notes'            => trim((string) $this->request->getGet('notes')),
            'selected_room_id' => max(0, (int) $this->request->getGet('selected_room')),
            'submitted'        => true,
        ];

        if ($search['check_in'] === '') {
            $search['check_in'] = $today->format('Y-m-d');
        }

        if ($search['check_out'] === '') {
            $search['check_out'] = $today->modify('+1 day')->format('Y-m-d');
        }

        return $search;
    }

    /**
     * @param list<array<string, mixed>> $rooms
     *
     * @return array<string, mixed>|null
     */
    private function resolveSelectedRoom(array $rooms, int $selectedRoomId): ?array
    {
        if ($selectedRoomId <= 0) {
            return null;
        }

        foreach ($rooms as $room) {
            if ((int) ($room['id'] ?? 0) === $selectedRoomId) {
                return $room;
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $room
     */
    private function resolveTenantRoomStatus(array $room, bool $hasConflict): string
    {
        $status = (string) ($room['status'] ?? 'available');

        if ($status !== 'available') {
            return $status;
        }

        if ($hasConflict) {
            return 'occupied';
        }

        return 'available';
    }

    private function isValidDate(string $value): bool
    {
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);

        return $date instanceof DateTimeImmutable && $date->format('Y-m-d') === $value;
    }
}
