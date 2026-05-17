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

        if ($requestedBy === 'price_per_hour') {
            $requestedBy = 'price_per_night';
        }

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
            'rooms'         => $this->presentRooms($roomsQuery->findAll()),
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
        $room = $this->presentRoom($this->findRoomOrFail($id));

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
            'room_number' => [
                'label' => 'Room Number',
                'rules' => 'required|max_length[20]',
            ],
            'type' => [
                'label' => 'Room Type',
                'rules' => 'required|in_list[' . implode(',', array_keys(room_type_options())) . ']',
            ],
            'capacity' => [
                'label' => 'Capacity',
                'rules' => 'required|integer|greater_than[0]',
            ],
            'price_per_hour' => [
                'label' => 'Price per Hour',
                'rules' => 'required|decimal|greater_than_equal_to[0]',
            ],
            'pricing_hours' => [
                'label' => 'Duration',
                'rules' => 'required|integer|greater_than[0]|less_than_equal_to[24]',
            ],
            'status' => [
                'label' => 'Status',
                'rules' => 'required|in_list[' . implode(',', array_keys(room_status_options())) . ']',
            ],
            'description' => [
                'label' => 'Description',
                'rules' => 'permit_empty|max_length[500]',
            ],
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        return [
            'room_number'     => trim((string) $this->request->getPost('room_number')),
            'type'            => (string) $this->request->getPost('type'),
            'capacity'        => (int) $this->request->getPost('capacity'),
            'price_per_night' => (float) $this->request->getPost('price_per_hour'),
            'pricing_hours'   => (int) $this->request->getPost('pricing_hours'),
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
            'price_per_night' => 'Price',
            'pricing_hours'   => 'Duration',
            'status'          => 'Status',
        ];
    }

    /**
     * @param list<array<string, mixed>> $rooms
     * @return list<array<string, mixed>>
     */
    private function presentRooms(array $rooms): array
    {
        return array_map($this->presentRoom(...), $rooms);
    }

    /**
     * @param array<string, mixed> $room
     * @return array<string, mixed>
     */
    private function presentRoom(array $room): array
    {
        $room['price_per_hour'] = (float) ($room['price_per_hour'] ?? $room['price_per_night'] ?? 0);
        $room['pricing_hours'] = max(1, (int) ($room['pricing_hours'] ?? 1));

        return $room;
    }
}
