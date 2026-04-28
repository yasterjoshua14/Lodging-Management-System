<?php

namespace App\Models;

use CodeIgniter\Model;
use DateTimeImmutable;
use Throwable;

class AdminReportModel extends Model
{
    protected $table            = 'bookings';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [];

    /**
     * @var array<string, string>
     */
    private const ROOM_STATUS_LABELS = [
        'available'   => 'Available',
        'occupied'    => 'Occupied',
        'maintenance' => 'Maintenance',
    ];

    /**
     * @var array<string, string>
     */
    private const BOOKING_STATUS_LABELS = [
        'pending'     => 'Pending',
        'checked_in'  => 'Checked In',
        'checked_out' => 'Checked Out',
        'cancelled'   => 'Cancelled',
    ];

    /**
     * @var array<string, string>
     */
    private const REVENUE_LABELS = [
        'pending'     => 'Pending Revenue',
        'checked_in'  => 'In-House Revenue',
        'checked_out' => 'Realized Earnings',
        'cancelled'   => 'Cancelled Revenue',
    ];

    public function getDashboardReport(int $recentLimit = 5, int $trendMonths = 4): array
    {
        $report = $this->buildAnalytics($trendMonths, 3, $recentLimit);

        return [
            'stats'             => $report['stats'],
            'recentBookings'    => $report['recentBookings'],
            'roomsByStatus'     => $report['roomStatusBreakdown'],
            'earningsTrend'     => $report['monthlyEarnings'],
            'revenueBreakdown'  => $report['revenueBreakdown'],
        ];
    }

    public function getAdminAnalytics(int $trendMonths = 6, int $topRoomsLimit = 5, int $recentLimit = 8): array
    {
        return $this->buildAnalytics($trendMonths, $topRoomsLimit, $recentLimit);
    }

    private function buildAnalytics(int $trendMonths, int $topRoomsLimit, int $recentLimit): array
    {
        $bookings            = $this->getAnalyticsBookings();
        $roomStatusBreakdown = $this->getRoomStatusBreakdown();
        $bookingBreakdown    = $this->getBookingStatusBreakdown();
        $roomInventory       = $this->getRoomInventory();

        return [
            'stats'                 => $this->compileStats($bookings, $roomStatusBreakdown, $bookingBreakdown),
            'recentBookings'        => $this->getRecentBookings($recentLimit),
            'roomStatusBreakdown'   => $roomStatusBreakdown,
            'bookingStatusBreakdown'=> $bookingBreakdown,
            'revenueBreakdown'      => $this->buildRevenueBreakdown($bookingBreakdown),
            'monthlyEarnings'       => $this->buildMonthlyEarnings($bookings, $trendMonths),
            'topRooms'              => $this->buildTopRooms($roomInventory, $bookings, $topRoomsLimit),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function getAnalyticsBookings(): array
    {
        return $this->db->table('bookings')
            ->select('id, room_id, tenant_id, check_in, check_out, total_amount, status')
            ->orderBy('check_in', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function getRecentBookings(int $limit): array
    {
        return $this->db->table('bookings')
            ->select('bookings.*, rooms.room_number, rooms.type AS room_type, tenants.full_name AS tenant_name')
            ->join('rooms', 'rooms.id = bookings.room_id')
            ->join('tenants', 'tenants.id = bookings.tenant_id')
            ->orderBy('bookings.check_in', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function getRoomInventory(): array
    {
        return $this->db->table('rooms')
            ->select('id, room_number, type, capacity, price_per_night, status')
            ->orderBy('room_number', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * @return list<array{status: string, label: string, count: int}>
     */
    private function getRoomStatusBreakdown(): array
    {
        $counts = array_fill_keys(array_keys(self::ROOM_STATUS_LABELS), 0);
        $rows   = $this->db->table('rooms')
            ->select('status, COUNT(*) AS room_count')
            ->groupBy('status')
            ->get()
            ->getResultArray();

        foreach ($rows as $row) {
            $status = (string) ($row['status'] ?? '');

            if (array_key_exists($status, $counts)) {
                $counts[$status] = (int) ($row['room_count'] ?? 0);
            }
        }

        $breakdown = [];
        foreach (self::ROOM_STATUS_LABELS as $status => $label) {
            $breakdown[] = [
                'status' => $status,
                'label'  => $label,
                'count'  => $counts[$status] ?? 0,
            ];
        }

        return $breakdown;
    }

    /**
     * @return list<array{status: string, label: string, count: int, amount: float}>
     */
    private function getBookingStatusBreakdown(): array
    {
        $metrics = [];

        foreach (self::BOOKING_STATUS_LABELS as $status => $label) {
            $metrics[$status] = [
                'status' => $status,
                'label'  => $label,
                'count'  => 0,
                'amount' => 0.0,
            ];
        }

        $rows = $this->db->table('bookings')
            ->select('status, COUNT(*) AS booking_count, COALESCE(SUM(total_amount), 0) AS total_amount')
            ->groupBy('status')
            ->get()
            ->getResultArray();

        foreach ($rows as $row) {
            $status = (string) ($row['status'] ?? '');

            if (! array_key_exists($status, $metrics)) {
                continue;
            }

            $metrics[$status]['count']  = (int) ($row['booking_count'] ?? 0);
            $metrics[$status]['amount'] = $this->moneyValue($row['total_amount'] ?? 0);
        }

        return array_values($metrics);
    }

    /**
     * @param list<array<string, mixed>> $bookings
     * @param list<array<string, mixed>> $roomStatusBreakdown
     * @param list<array<string, mixed>> $bookingBreakdown
     *
     * @return array<string, float|int>
     */
    private function compileStats(array $bookings, array $roomStatusBreakdown, array $bookingBreakdown): array
    {
        $roomCounts = [];
        foreach ($roomStatusBreakdown as $item) {
            $roomCounts[$item['status']] = (int) $item['count'];
        }

        $bookingCounts  = [];
        $bookingAmounts = [];
        foreach ($bookingBreakdown as $item) {
            $bookingCounts[$item['status']]  = (int) $item['count'];
            $bookingAmounts[$item['status']] = $this->moneyValue($item['amount']);
        }

        $totalRooms          = array_sum($roomCounts);
        $nonCancelledBookings = ($bookingCounts['pending'] ?? 0) + ($bookingCounts['checked_in'] ?? 0) + ($bookingCounts['checked_out'] ?? 0);
        $bookedRevenue       = ($bookingAmounts['pending'] ?? 0.0) + ($bookingAmounts['checked_in'] ?? 0.0) + ($bookingAmounts['checked_out'] ?? 0.0);
        $averageStayLength   = $this->calculateAverageStayLength($bookings);
        $today               = date('Y-m-d');

        $upcomingCheckIns = 0;
        $stayingTonight   = 0;

        foreach ($bookings as $booking) {
            $status   = (string) ($booking['status'] ?? '');
            $checkIn  = (string) ($booking['check_in'] ?? '');
            $checkOut = (string) ($booking['check_out'] ?? '');

            if ($status === 'pending' && $checkIn >= $today) {
                $upcomingCheckIns++;
            }

            if ($status === 'checked_in' && $checkIn <= $today && $checkOut > $today) {
                $stayingTonight++;
            }
        }

        return [
            'totalRooms'          => $totalRooms,
            'availableRooms'      => $roomCounts['available'] ?? 0,
            'occupiedRooms'       => $roomCounts['occupied'] ?? 0,
            'maintenanceRooms'    => $roomCounts['maintenance'] ?? 0,
            'totalTenants'        => $this->db->table('tenants')->countAllResults(),
            'totalBookings'       => array_sum($bookingCounts),
            'activeBookings'      => ($bookingCounts['pending'] ?? 0) + ($bookingCounts['checked_in'] ?? 0),
            'completedStays'      => $bookingCounts['checked_out'] ?? 0,
            'cancelledBookings'   => $bookingCounts['cancelled'] ?? 0,
            'bookedRevenue'       => $this->moneyValue($bookedRevenue),
            'realizedRevenue'     => $this->moneyValue($bookingAmounts['checked_out'] ?? 0.0),
            'inHouseRevenue'      => $this->moneyValue($bookingAmounts['checked_in'] ?? 0.0),
            'pendingRevenue'      => $this->moneyValue($bookingAmounts['pending'] ?? 0.0),
            'cancelledRevenue'    => $this->moneyValue($bookingAmounts['cancelled'] ?? 0.0),
            'occupancyRate'       => $totalRooms > 0 ? round((($roomCounts['occupied'] ?? 0) / $totalRooms) * 100, 1) : 0.0,
            'completionRate'      => $nonCancelledBookings > 0 ? round((($bookingCounts['checked_out'] ?? 0) / $nonCancelledBookings) * 100, 1) : 0.0,
            'averageBookingValue' => $nonCancelledBookings > 0 ? round($bookedRevenue / $nonCancelledBookings, 2) : 0.0,
            'averageStayLength'   => $averageStayLength,
            'upcomingCheckIns'    => $upcomingCheckIns,
            'stayingTonight'      => $stayingTonight,
        ];
    }

    /**
     * @param list<array<string, mixed>> $bookings
     */
    private function calculateAverageStayLength(array $bookings): float
    {
        $totalNights = 0;
        $totalStays  = 0;

        foreach ($bookings as $booking) {
            if (($booking['status'] ?? '') === 'cancelled') {
                continue;
            }

            $nights = $this->countNights((string) ($booking['check_in'] ?? ''), (string) ($booking['check_out'] ?? ''));

            if ($nights <= 0) {
                continue;
            }

            $totalNights += $nights;
            $totalStays++;
        }

        if ($totalStays === 0) {
            return 0.0;
        }

        return round($totalNights / $totalStays, 1);
    }

    /**
     * @param list<array<string, mixed>> $bookingBreakdown
     *
     * @return list<array<string, mixed>>
     */
    private function buildRevenueBreakdown(array $bookingBreakdown): array
    {
        $items = [];

        foreach ($bookingBreakdown as $item) {
            $status = (string) $item['status'];

            $items[] = [
                'status' => $status,
                'label'  => self::REVENUE_LABELS[$status] ?? $item['label'],
                'count'  => (int) $item['count'],
                'amount' => $this->moneyValue($item['amount']),
            ];
        }

        return $items;
    }

    /**
     * @param list<array<string, mixed>> $bookings
     *
     * @return list<array<string, mixed>>
     */
    private function buildMonthlyEarnings(array $bookings, int $months): array
    {
        $months = max(1, $months);
        $start  = new DateTimeImmutable('first day of this month');
        $trend  = [];

        for ($offset = $months - 1; $offset >= 0; $offset--) {
            $month       = $start->modify("-{$offset} months");
            $key         = $month->format('Y-m');
            $trend[$key] = [
                'key'      => $key,
                'label'    => $month->format('M Y'),
                'bookings' => 0,
                'amount'   => 0.0,
                'percent'  => 0.0,
            ];
        }

        foreach ($bookings as $booking) {
            if (($booking['status'] ?? '') === 'cancelled') {
                continue;
            }

            $key = substr((string) ($booking['check_in'] ?? ''), 0, 7);

            if (! isset($trend[$key])) {
                continue;
            }

            $trend[$key]['bookings']++;
            $trend[$key]['amount'] = $this->moneyValue($trend[$key]['amount'] + (float) ($booking['total_amount'] ?? 0));
        }

        $peak = 0.0;
        foreach ($trend as $item) {
            $peak = max($peak, (float) $item['amount']);
        }

        foreach ($trend as $key => $item) {
            $trend[$key]['percent'] = $peak > 0 ? round((((float) $item['amount']) / $peak) * 100, 1) : 0.0;
        }

        return array_values($trend);
    }

    /**
     * @param list<array<string, mixed>> $rooms
     * @param list<array<string, mixed>> $bookings
     *
     * @return list<array<string, mixed>>
     */
    private function buildTopRooms(array $rooms, array $bookings, int $limit): array
    {
        $performance = [];

        foreach ($rooms as $room) {
            $roomId = (int) $room['id'];

            $performance[$roomId] = [
                'room_id'         => $roomId,
                'room_number'     => (string) $room['room_number'],
                'room_type'       => (string) $room['type'],
                'status'          => (string) $room['status'],
                'capacity'        => (int) $room['capacity'],
                'price_per_night' => $this->moneyValue($room['price_per_night'] ?? 0),
                'booking_count'   => 0,
                'completed_stays' => 0,
                'guest_nights'    => 0,
                'total_revenue'   => 0.0,
            ];
        }

        foreach ($bookings as $booking) {
            if (($booking['status'] ?? '') === 'cancelled') {
                continue;
            }

            $roomId = (int) ($booking['room_id'] ?? 0);

            if (! isset($performance[$roomId])) {
                continue;
            }

            $performance[$roomId]['booking_count']++;
            $performance[$roomId]['guest_nights'] += $this->countNights((string) ($booking['check_in'] ?? ''), (string) ($booking['check_out'] ?? ''));
            $performance[$roomId]['total_revenue'] = $this->moneyValue($performance[$roomId]['total_revenue'] + (float) ($booking['total_amount'] ?? 0));

            if (($booking['status'] ?? '') === 'checked_out') {
                $performance[$roomId]['completed_stays']++;
            }
        }

        $rankedRooms = array_values($performance);

        usort($rankedRooms, static function (array $left, array $right): int {
            if ($left['total_revenue'] !== $right['total_revenue']) {
                return $right['total_revenue'] <=> $left['total_revenue'];
            }

            if ($left['booking_count'] !== $right['booking_count']) {
                return $right['booking_count'] <=> $left['booking_count'];
            }

            return strcmp($left['room_number'], $right['room_number']);
        });

        return array_slice($rankedRooms, 0, max(1, $limit));
    }

    private function countNights(string $checkIn, string $checkOut): int
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
     * @param float|int|string|null $value
     */
    private function moneyValue($value): float
    {
        return round((float) $value, 2);
    }
}
