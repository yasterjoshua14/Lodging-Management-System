<?php

namespace App\Libraries;

use App\Models\BookingModel;
use App\Models\RoomModel;

class BookingAvailability
{
    /**
     * @return list<array<string, mixed>>
     */
    public function findAvailableRooms(?int $guestCount = null): array
    {
        $rooms = (new RoomModel())
            ->where('status', 'available')
            ->orderBy('room_number', 'ASC')
            ->findAll();

        $conflictingRoomIds = $this->findConflictingRoomIds();

        return array_values(array_filter($rooms, static function (array $room) use ($conflictingRoomIds, $guestCount): bool {
            $roomId = (int) ($room['id'] ?? 0);

            if (in_array($roomId, $conflictingRoomIds, true)) {
                return false;
            }

            if ($guestCount !== null && (int) ($room['capacity'] ?? 0) < $guestCount) {
                return false;
            }

            return true;
        }));
    }

    public function hasActiveConflict(int $roomId, ?int $ignoreId = null): bool
    {
        $query = (new BookingModel())
            ->where('room_id', $roomId)
            ->whereIn('status', active_booking_statuses());

        if ($ignoreId !== null) {
            $query->where('id !=', $ignoreId);
        }

        return $query->first() !== null;
    }

    /**
     * @param array<string, mixed> $room
     */
    public function calculateTotalAmount(array $room): float
    {
        return round((float) ($room['price_per_night'] ?? 0), 2);
    }

    /**
     * @return list<int>
     */
    private function findConflictingRoomIds(): array
    {
        $bookings = (new BookingModel())
            ->select('room_id')
            ->whereIn('status', active_booking_statuses())
            ->findAll();

        return array_values(array_unique(array_map(
            static fn (array $booking): int => (int) ($booking['room_id'] ?? 0),
            $bookings
        )));
    }
}
