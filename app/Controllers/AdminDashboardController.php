<?php

namespace App\Controllers;

use App\Models\AdminReportModel;

class AdminDashboardController extends BaseController
{
    public function index(): string
    {
        $report = (new AdminReportModel())->getAdminAnalytics();

        return view('admin/dashboard/index', [
            'title'                  => 'Dashboard',
            'recentBookings'         => $report['recentBookings'],
            'roomsByStatus'          => $report['roomStatusBreakdown'],
            'monthlyEarnings'        => $report['monthlyEarnings'],
            'revenueBreakdown'       => $report['revenueBreakdown'],
            'bookingStatusBreakdown' => $report['bookingStatusBreakdown'],
        ]);
    }
}
