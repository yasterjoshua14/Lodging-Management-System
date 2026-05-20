<?php

use App\Models\AdminReportModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Tests\Support\Database\Seeds\AdminReportSeeder;

/**
 * @internal
 */
final class AdminReportModelTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $seed = AdminReportSeeder::class;

    public function testDashboardReportSummarizesRevenueAndOccupancy(): void
    {
        $report = (new AdminReportModel())->getDashboardReport();

        $this->assertSame(3, $report['stats']['totalRooms']);
        $this->assertSame(1, $report['stats']['availableRooms']);
        $this->assertSame(2, $report['stats']['activeBookings']);
        $this->assertSame(3, $report['stats']['totalTenants']);
        $this->assertSame(25000.00, $report['stats']['bookedRevenue']);
        $this->assertSame(8400.00, $report['stats']['realizedRevenue']);
        $this->assertSame(33.3, $report['stats']['occupancyRate']);
        $this->assertSame(3.3, $report['stats']['averageStayLength']);
        $this->assertSame(8333.33, $report['stats']['averageBookingValue']);
    }

    public function testAnalyticsRanksRoomsAndBreaksDownBookingStatuses(): void
    {
        $analytics = (new AdminReportModel())->getAdminAnalytics(24);

        $statusMetrics = [];
        foreach ($analytics['bookingStatusBreakdown'] as $item) {
            $statusMetrics[$item['status']] = $item;
        }

        $this->assertSame(1, $statusMetrics['pending']['count']);
        $this->assertSame(5400.00, $statusMetrics['pending']['amount']);
        $this->assertSame(1, $statusMetrics['checked_in']['count']);
        $this->assertSame(11200.00, $statusMetrics['checked_in']['amount']);
        $this->assertSame(1, $statusMetrics['checked_out']['count']);
        $this->assertSame(8400.00, $statusMetrics['checked_out']['amount']);
        $this->assertSame(1, $statusMetrics['cancelled']['count']);
        $this->assertSame(9000.00, $statusMetrics['cancelled']['amount']);

        $this->assertSame('205', $analytics['topRooms'][0]['room_number']);
        $this->assertSame(19600.00, $analytics['topRooms'][0]['total_revenue']);
        $this->assertSame(8, $analytics['topRooms'][0]['guest_nights']);
        $this->assertSame('101', $analytics['topRooms'][1]['room_number']);

        $monthlyRevenueBuckets = array_values(array_filter(
            $analytics['revenueBreakdownByMonth'],
            static fn (array $items): bool => array_sum(array_column($items, 'amount')) > 0
        ));
        $monthlyBookingBuckets = array_values(array_filter(
            $analytics['bookingStatusBreakdownByMonth'],
            static fn (array $items): bool => array_sum(array_column($items, 'count')) > 0
        ));

        $this->assertCount(1, $monthlyRevenueBuckets);
        $this->assertCount(1, $monthlyBookingBuckets);

        $aprilRevenueMetrics = [];
        foreach ($monthlyRevenueBuckets[0] as $item) {
            $aprilRevenueMetrics[$item['status']] = $item;
        }

        $aprilBookingMetrics = [];
        foreach ($monthlyBookingBuckets[0] as $item) {
            $aprilBookingMetrics[$item['status']] = $item;
        }

        $this->assertSame(5400.00, $aprilRevenueMetrics['pending']['amount']);
        $this->assertSame(11200.00, $aprilRevenueMetrics['checked_in']['amount']);
        $this->assertSame(8400.00, $aprilRevenueMetrics['checked_out']['amount']);
        $this->assertSame(9000.00, $aprilRevenueMetrics['cancelled']['amount']);
        $this->assertSame(1, $aprilBookingMetrics['pending']['count']);
        $this->assertSame(1, $aprilBookingMetrics['checked_in']['count']);
        $this->assertSame(1, $aprilBookingMetrics['checked_out']['count']);
        $this->assertSame(1, $aprilBookingMetrics['cancelled']['count']);
    }
}
