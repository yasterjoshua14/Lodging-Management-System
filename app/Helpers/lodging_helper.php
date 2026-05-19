<?php

if (! function_exists('room_status_options')) {
    function room_status_options(): array
    {
        return [
            'available'   => 'Available',
            'occupied'    => 'Occupied',
            'maintenance' => 'Maintenance',
        ];
    }
}

if (! function_exists('room_type_options')) {
    function room_type_options(): array
    {
        return [
            'standard' => 'Standard',
            'deluxe'   => 'Deluxe',
            'family'   => 'Family',
            'suite'    => 'Suite',
        ];
    }
}

if (! function_exists('hour_duration_options')) {
    function hour_duration_options(): array
    {
        $options = [];

        for ($hour = 1; $hour <= 24; $hour++) {
            $options[(string) $hour] = $hour === 1 ? '1hr' : $hour . 'hrs';
        }

        return $options;
    }
}

if (! function_exists('hour_duration_label')) {
    function hour_duration_label(mixed $hours): string
    {
        $normalizedHours = max(1, (int) $hours);
        $options = hour_duration_options();

        return $options[(string) $normalizedHours] ?? ($normalizedHours === 1 ? '1hr' : $normalizedHours . 'hrs');
    }
}

if (! function_exists('sync_room_booking_statuses')) {
    /**
     * @param list<int>|null $roomIds
     */
    function sync_room_booking_statuses(?array $roomIds = null): void
    {
        $db = db_connect();

        $normalizedRoomIds = array_values(array_unique(array_filter(
            array_map(
                static fn (mixed $roomId): int => (int) $roomId,
                $roomIds ?? []
            ),
            static fn (int $roomId): bool => $roomId > 0
        )));

        $roomsQuery = $db->table('rooms')->select('id, status');

        if ($normalizedRoomIds !== []) {
            $roomsQuery->whereIn('id', $normalizedRoomIds);
        }

        $rooms = $roomsQuery->get()->getResultArray();

        if ($rooms === []) {
            return;
        }

        $activeBookedRoomsQuery = $db->table('bookings')
            ->select('room_id')
            ->whereIn('status', active_booking_statuses())
            ->groupBy('room_id');

        if ($normalizedRoomIds !== []) {
            $activeBookedRoomsQuery->whereIn('room_id', $normalizedRoomIds);
        }

        $activeBookedRoomIds = array_fill_keys(
            array_map(
                static fn (array $row): int => (int) ($row['room_id'] ?? 0),
                $activeBookedRoomsQuery->get()->getResultArray()
            ),
            true
        );

        foreach ($rooms as $room) {
            $roomId = (int) ($room['id'] ?? 0);
            $status = (string) ($room['status'] ?? '');

            if ($roomId <= 0) {
                continue;
            }

            if (isset($activeBookedRoomIds[$roomId])) {
                if ($status !== 'occupied') {
                    $db->table('rooms')->where('id', $roomId)->update(['status' => 'occupied']);
                }

                continue;
            }

            if ($status === 'occupied') {
                $db->table('rooms')->where('id', $roomId)->update(['status' => 'available']);
            }
        }
    }
}

if (! function_exists('active_booking_statuses')) {
    /**
     * @return list<string>
     */
    function active_booking_statuses(): array
    {
        return ['awaiting_payment', 'pending', 'checked_in'];
    }
}

if (! function_exists('booking_status_options')) {
    function booking_status_options(): array
    {
        return [
            'awaiting_payment' => 'Awaiting Payment',
            'pending'     => 'Pending',
            'checked_in'  => 'Checked In',
            'checked_out' => 'Checked Out',
            'cancelled'   => 'Cancelled',
        ];
    }
}

if (! function_exists('humanize_key')) {
    function humanize_key(?string $value): string
    {
        if ($value === null || $value === '') {
            return 'N/A';
        }

        return ucwords(str_replace('_', ' ', $value));
    }
}

if (! function_exists('status_badge_class')) {
    function status_badge_class(string $value): string
    {
        return match ($value) {
            'available', 'checked_out' => 'badge badge-success',
            'occupied', 'checked_in'   => 'badge badge-warning',
            'maintenance'              => 'badge badge-neutral',
            'cancelled'                => 'badge badge-muted',
            'awaiting_payment', 'pending' => 'badge badge-info',
            default                    => 'badge badge-info',
        };
    }
}

if (! function_exists('format_money')) {
    function format_money($amount): string
    {
        return 'PHP ' . number_format((float) $amount, 2);
    }
}

if (! function_exists('format_datetime')) {
    function format_datetime(?string $value, string $fallback = 'N/A'): string
    {
        $normalizedValue = trim((string) $value);

        if ($normalizedValue === '') {
            return $fallback;
        }

        $timestamp = strtotime($normalizedValue);

        if ($timestamp === false) {
            return $normalizedValue;
        }

        return date('M j, Y g:i A', $timestamp);
    }
}

if (! function_exists('view_text')) {
    function view_text(mixed $value, string $fallback = ''): string
    {
        if ($value === null || $value === '') {
            return $fallback;
        }

        if (is_scalar($value) || $value instanceof Stringable) {
            return (string) $value;
        }

        return $fallback;
    }
}

if (! function_exists('view_esc')) {
    function view_esc(mixed $value, string $context = 'html', ?string $encoding = null, string $fallback = ''): string
    {
        return esc(view_text($value, $fallback), $context, $encoding);
    }
}
