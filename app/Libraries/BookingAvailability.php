<?php

namespace App\Libraries;

use App\Models\BookingModel;
use App\Models\RoomModel;
use DateTimeImmutable;
use Throwable;

class BookingAvailability
{
    /**
     * @var list<string>
     */
    private const ACTIVE_STATUSES = ['awaiting_payment', 'pending', 'checked_in'];

    /**
     * @return list<array<string, mixed>>
     */
    public function findAvailableRooms(string $checkIn, string $checkOut, ?int $guestCount = null): array
    {
        $rooms = (new RoomModel())
            ->where('status', 'available')
            ->orderBy('room_number', 'ASC')
            ->findAll();

        $conflictingRoomIds = $this->findConflictingRoomIds($checkIn, $checkOut);

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

    public function hasDateConflict(int $roomId, string $checkIn, string $checkOut, ?int $ignoreId = null): bool
    {
        $query = (new BookingModel())
            ->where('room_id', $roomId)
            ->whereIn('status', self::ACTIVE_STATUSES)
            ->groupStart()
            ->where('check_in <', $checkOut)
            ->where('check_out >', $checkIn)
            ->groupEnd();

        if ($ignoreId !== null) {
            $query->where('id !=', $ignoreId);
        }

        return $query->first() !== null;
    }

    public function countNights(string $checkIn, string $checkOut): int
    {
        if ($checkIn === '' || $checkOut === '') {
            return 0;
        }

        try {
            $start = new DateTimeImmutable($checkIn);
            $end   = new DateTimeImmutable($checkOut);
        } catch (Throwable) {
            return 0;
        }

        if ($end <= $start) {
            return 0;
        }

        return (int) $start->diff($end)->days;
    }

    /**
     * @param array<string, mixed> $room
     */
    public function calculateTotalAmount(array $room, string $checkIn, string $checkOut): float
    {
        $nights = $this->countNights($checkIn, $checkOut);

        if ($nights <= 0) {
            return 0.0;
        }

        return round(((float) ($room['price_per_night'] ?? 0)) * $nights, 2);
    }

    /**
     * @return list<int>
     */
    private function findConflictingRoomIds(string $checkIn, string $checkOut): array
    {
        $bookings = (new BookingModel())
            ->select('room_id')
            ->whereIn('status', self::ACTIVE_STATUSES)
            ->groupStart()
            ->where('check_in <', $checkOut)
            ->where('check_out >', $checkIn)
            ->groupEnd()
            ->findAll();

        return array_values(array_unique(array_map(
            static fn (array $booking): int => (int) ($booking['room_id'] ?? 0),
            $bookings
        )));
    }
}
