<?php

namespace App\Controllers;

use App\Models\BookingModel;
use App\Models\RoomModel;
use App\Models\TenantModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

class AdminBookingsController extends BaseController
{
    public function index(): string
    {
        sync_room_booking_statuses();

        $bookings = (new BookingModel())
            ->withRelations()
            ->orderBy('bookings.created_at', 'DESC')
            ->findAll();

        return view('admin/bookings/index', [
            'title'    => 'Bookings',
            'bookings' => $bookings,
        ]);
    }

    public function create(): string
    {
        sync_room_booking_statuses();

        return view('admin/bookings/form', [
            'title'   => 'Add Booking',
            'booking' => null,
            'rooms'   => $this->getRoomOptions(),
            'tenants' => $this->getTenantOptions(),
            'action'  => admin_path('bookings'),
            'heading' => 'Add Booking',
        ]);
    }

    public function store(): RedirectResponse
    {
        $data = $this->getValidatedData();

        if ($data instanceof RedirectResponse) {
            return $data;
        }

        if ($this->bookingStatusBlocksRoom($data['status']) && $this->hasActiveConflict($data['room_id'])) {
            return redirect()->back()->withInput()->with('error', 'The selected room already has an active booking.');
        }

        (new BookingModel())->insert($data);
        sync_room_booking_statuses([$data['room_id']]);

        return redirect()->to(admin_path('bookings'))->with('success', 'Booking created successfully.');
    }

    public function edit(int $id): string
    {
        sync_room_booking_statuses();
        $booking = $this->findBookingOrFail($id);

        return view('admin/bookings/form', [
            'title'   => 'Edit Booking',
            'booking' => $booking,
            'rooms'   => $this->getRoomOptions(),
            'tenants' => $this->getTenantOptions(),
            'action'  => admin_path('bookings/' . $id),
            'heading' => 'Edit Booking',
        ]);
    }

    public function update(int $id): RedirectResponse
    {
        $existingBooking = $this->findBookingOrFail($id);
        $data = $this->getValidatedData($existingBooking);

        if ($data instanceof RedirectResponse) {
            return $data;
        }

        if ($this->bookingStatusBlocksRoom($data['status']) && $this->hasActiveConflict($data['room_id'], $id)) {
            return redirect()->back()->withInput()->with('error', 'The selected room already has an active booking.');
        }

        (new BookingModel())->update($id, $data);
        sync_room_booking_statuses([
            (int) ($existingBooking['room_id'] ?? 0),
            (int) ($data['room_id'] ?? 0),
        ]);

        return redirect()->to(admin_path('bookings'))->with('success', 'Booking updated successfully.');
    }

    public function delete(int $id): RedirectResponse
    {
        $booking = $this->findBookingOrFail($id);
        (new BookingModel())->delete($id);
        sync_room_booking_statuses([(int) ($booking['room_id'] ?? 0)]);

        return redirect()->to(admin_path('bookings'))->with('success', 'Booking deleted successfully.');
    }

    /**
     * @param array<string, mixed>|null $existingBooking
     *
     * @return array<string, mixed>|RedirectResponse
     */
    private function getValidatedData(?array $existingBooking = null)
    {
        $rules = [
            'room_id' => [
                'label' => 'Room',
                'rules' => 'required|integer',
            ],
            'tenant_id' => [
                'label' => 'Tenant',
                'rules' => 'required|integer',
            ],
            'total_amount' => [
                'label' => 'Total Amount',
                'rules' => 'required|decimal|greater_than_equal_to[0]',
            ],
            'status' => [
                'label' => 'Booking Status',
                'rules' => 'required|in_list[' . implode(',', array_keys(booking_status_options())) . ']',
            ],
            'notes' => [
                'label' => 'Notes',
                'rules' => 'permit_empty|max_length[500]',
            ],
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $roomId   = (int) $this->request->getPost('room_id');
        $tenantId = (int) $this->request->getPost('tenant_id');
        $status   = (string) $this->request->getPost('status');
        $bookingDate = (string) ($existingBooking['check_in'] ?? date('Y-m-d'));

        $room = (new RoomModel())->find($roomId);
        if ($room === null) {
            return redirect()->back()->withInput()->with('error', 'The selected room could not be found.');
        }

        if ((new TenantModel())->find($tenantId) === null) {
            return redirect()->back()->withInput()->with('error', 'The selected tenant could not be found.');
        }

        if ($this->bookingStatusBlocksRoom($status) && (string) ($room['status'] ?? '') === 'maintenance') {
            return redirect()->back()->withInput()->with('error', 'Rooms under maintenance cannot receive active bookings.');
        }

        return [
            'room_id'       => $roomId,
            'tenant_id'     => $tenantId,
            'check_in'      => $bookingDate,
            'check_out'     => (string) ($existingBooking['check_out'] ?? $bookingDate),
            'total_amount'  => (float) $this->request->getPost('total_amount'),
            'status'        => $status,
            'notes'         => trim((string) $this->request->getPost('notes')),
        ];
    }

    private function getRoomOptions(): array
    {
        $rooms = (new RoomModel())
            ->orderBy('room_number', 'ASC')
            ->findAll();

        return array_map(
            static function (array $room): array {
                $room['price_per_hour'] = (float) ($room['price_per_hour'] ?? $room['price_per_night'] ?? 0);

                return $room;
            },
            $rooms,
        );
    }

    private function getTenantOptions(): array
    {
        return (new TenantModel())
            ->orderBy('first_name', 'ASC')
            ->orderBy('last_name', 'ASC')
            ->findAll();
    }

    private function hasActiveConflict(int $roomId, ?int $ignoreId = null): bool
    {
        $query = (new BookingModel())
            ->where('room_id', $roomId)
            ->whereIn('status', active_booking_statuses());

        if ($ignoreId !== null) {
            $query->where('id !=', $ignoreId);
        }

        return $query->first() !== null;
    }

    private function bookingStatusBlocksRoom(string $status): bool
    {
        return in_array($status, active_booking_statuses(), true);
    }

    private function findBookingOrFail(int $id): array
    {
        $booking = (new BookingModel())->find($id);

        if ($booking === null) {
            throw PageNotFoundException::forPageNotFound('Booking not found.');
        }

        return $booking;
    }
}
