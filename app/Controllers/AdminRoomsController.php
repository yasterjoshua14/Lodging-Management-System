<?php

namespace App\Controllers;

use App\Models\BookingModel;
use App\Models\RoomModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

class AdminRoomsController extends BaseController
{
    public function index(): string
    {
        $roomModel   = new RoomModel();
        $sortOptions = $this->roomSortOptions();
        $requestedBy = strtolower(trim((string) $this->request->getGet('sort')));

        if (! array_key_exists($requestedBy, $sortOptions)) {
            $sortBy        = 'room_number';
            $sortDirection = 'asc';
        } else {
            $sortBy        = $requestedBy;
            $sortDirection = strtolower((string) $this->request->getGet('direction')) === 'desc' ? 'desc' : 'asc';
        }

        $roomsQuery = $roomModel->orderBy($sortBy, strtoupper($sortDirection));

        if ($sortBy !== 'room_number') {
            $roomsQuery->orderBy('room_number', 'ASC');
        }

        return view('admin/rooms/index', [
            'title'         => 'Rooms',
            'rooms'         => $roomsQuery->findAll(),
            'sortBy'        => $sortBy,
            'sortDirection' => $sortDirection,
            'sortOptions'   => $sortOptions,
        ]);
    }

    public function create(): string
    {
        return view('admin/rooms/form', [
            'title'   => 'Add Room',
            'room'    => null,
            'action'  => admin_path('rooms'),
            'heading' => 'Add Room',
        ]);
    }

    public function store(): RedirectResponse
    {
        $roomModel = new RoomModel();
        $data      = $this->getValidatedData();

        if ($data instanceof RedirectResponse) {
            return $data;
        }

        if ($this->roomNumberExists($data['room_number'])) {
            return redirect()->back()->withInput()->with('error', 'That room number already exists.');
        }

        $roomModel->insert($data);

        return redirect()->to(admin_path('rooms'))->with('success', 'Room created successfully.');
    }

    public function edit(int $id): string
    {
        $room = $this->findRoomOrFail($id);

        return view('admin/rooms/form', [
            'title'   => 'Edit Room',
            'room'    => $room,
            'action'  => admin_path('rooms/' . $id),
            'heading' => 'Edit Room',
        ]);
    }

    public function update(int $id): RedirectResponse
    {
        $room = $this->findRoomOrFail($id);
        $data = $this->getValidatedData();

        if ($data instanceof RedirectResponse) {
            return $data;
        }

        if ($this->roomNumberExists($data['room_number'], $room['id'])) {
            return redirect()->back()->withInput()->with('error', 'That room number already exists.');
        }

        (new RoomModel())->update($id, $data);

        return redirect()->to(admin_path('rooms'))->with('success', 'Room updated successfully.');
    }

    public function delete(int $id): RedirectResponse
    {
        $room = $this->findRoomOrFail($id);

        $bookingCount = (new BookingModel())->where('room_id', $room['id'])->countAllResults();
        if ($bookingCount > 0) {
            return redirect()->to(admin_path('rooms'))->with('error', 'Delete the room bookings first before removing this room.');
        }

        (new RoomModel())->delete($id);

        return redirect()->to(admin_path('rooms'))->with('success', 'Room deleted successfully.');
    }

    private function getValidatedData()
    {
        $rules = [
            'room_number'     => 'required|max_length[20]',
            'type'            => 'required|in_list[' . implode(',', array_keys(room_type_options())) . ']',
            'capacity'        => 'required|integer|greater_than[0]',
            'price_per_night' => 'required|decimal|greater_than_equal_to[0]',
            'status'          => 'required|in_list[' . implode(',', array_keys(room_status_options())) . ']',
            'description'     => 'permit_empty|max_length[500]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        return [
            'room_number'     => trim((string) $this->request->getPost('room_number')),
            'type'            => (string) $this->request->getPost('type'),
            'capacity'        => (int) $this->request->getPost('capacity'),
            'price_per_night' => (float) $this->request->getPost('price_per_night'),
            'status'          => (string) $this->request->getPost('status'),
            'description'     => trim((string) $this->request->getPost('description')),
        ];
    }

    private function roomNumberExists(string $roomNumber, ?int $ignoreId = null): bool
    {
        $query = (new RoomModel())->where('room_number', $roomNumber);

        if ($ignoreId !== null) {
            $query->where('id !=', $ignoreId);
        }

        return $query->first() !== null;
    }

    private function findRoomOrFail(int $id): array
    {
        $room = (new RoomModel())->find($id);

        if ($room === null) {
            throw PageNotFoundException::forPageNotFound('Room not found.');
        }

        return $room;
    }

    private function roomSortOptions(): array
    {
        return [
            'room_number'     => 'Room No.',
            'type'            => 'Type',
            'capacity'        => 'Capacity',
            'price_per_night' => 'Price / Night',
            'status'          => 'Status',
        ];
    }
}
