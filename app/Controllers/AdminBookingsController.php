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
        $bookings = (new BookingModel())
            ->withRelations()
            ->orderBy('bookings.check_in', 'DESC')
            ->findAll();

        return view('admin/bookings/index', [
            'title'    => 'Bookings',
            'bookings' => $bookings,
        ]);
    }

    public function create(): string
    {
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

        if ($this->hasDateConflict($data['room_id'], $data['check_in'], $data['check_out'])) {
            return redirect()->back()->withInput()->with('error', 'The selected room already has an active booking for the chosen dates.');
        }

        (new BookingModel())->insert($data);

        return redirect()->to(admin_path('bookings'))->with('success', 'Booking created successfully.');
    }

    public function edit(int $id): string
    {
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
        $this->findBookingOrFail($id);
        $data = $this->getValidatedData();

        if ($data instanceof RedirectResponse) {
            return $data;
        }

        if ($this->hasDateConflict($data['room_id'], $data['check_in'], $data['check_out'], $id)) {
            return redirect()->back()->withInput()->with('error', 'The selected room already has an active booking for the chosen dates.');
        }

        (new BookingModel())->update($id, $data);

        return redirect()->to(admin_path('bookings'))->with('success', 'Booking updated successfully.');
    }

    public function delete(int $id): RedirectResponse
    {
        $this->findBookingOrFail($id);
        (new BookingModel())->delete($id);

        return redirect()->to(admin_path('bookings'))->with('success', 'Booking deleted successfully.');
    }

    private function getValidatedData()
    {
        $rules = [
            'room_id'       => 'required|integer',
            'tenant_id'     => 'required|integer',
            'check_in'      => 'required|valid_date[Y-m-d]',
            'check_out'     => 'required|valid_date[Y-m-d]',
            'total_amount'  => 'required|decimal|greater_than_equal_to[0]',
            'status'        => 'required|in_list[' . implode(',', array_keys(booking_status_options())) . ']',
            'notes'         => 'permit_empty|max_length[500]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $checkIn  = (string) $this->request->getPost('check_in');
        $checkOut = (string) $this->request->getPost('check_out');
        $roomId   = (int) $this->request->getPost('room_id');
        $tenantId = (int) $this->request->getPost('tenant_id');

        if ($checkOut <= $checkIn) {
            return redirect()->back()->withInput()->with('error', 'Check-out date must be after the check-in date.');
        }

        if ((new RoomModel())->find($roomId) === null) {
            return redirect()->back()->withInput()->with('error', 'The selected room could not be found.');
        }

        if ((new TenantModel())->find($tenantId) === null) {
            return redirect()->back()->withInput()->with('error', 'The selected tenant could not be found.');
        }

        return [
            'room_id'       => $roomId,
            'tenant_id'     => $tenantId,
            'check_in'      => $checkIn,
            'check_out'     => $checkOut,
            'total_amount'  => (float) $this->request->getPost('total_amount'),
            'status'        => (string) $this->request->getPost('status'),
            'notes'         => trim((string) $this->request->getPost('notes')),
        ];
    }

    private function getRoomOptions(): array
    {
        return (new RoomModel())
            ->orderBy('room_number', 'ASC')
            ->findAll();
    }

    private function getTenantOptions(): array
    {
        return (new TenantModel())
            ->orderBy('full_name', 'ASC')
            ->findAll();
    }

    private function hasDateConflict(int $roomId, string $checkIn, string $checkOut, ?int $ignoreId = null): bool
    {
        $query = (new BookingModel())
            ->where('room_id', $roomId)
            ->whereIn('status', ['pending', 'checked_in'])
            ->groupStart()
            ->where('check_in <', $checkOut)
            ->where('check_out >', $checkIn)
            ->groupEnd();

        if ($ignoreId !== null) {
            $query->where('id !=', $ignoreId);
        }

        return $query->first() !== null;
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
