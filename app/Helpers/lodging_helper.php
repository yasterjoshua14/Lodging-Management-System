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

if (! function_exists('booking_status_options')) {
    function booking_status_options(): array
    {
        return [
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
            'maintenance', 'cancelled' => 'badge badge-muted',
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
