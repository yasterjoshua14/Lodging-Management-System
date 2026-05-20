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
            'revenueBreakdownByMonth' => $report['revenueBreakdownByMonth'],
            'bookingStatusBreakdown' => $report['bookingStatusBreakdown'],
            'bookingStatusBreakdownByMonth' => $report['bookingStatusBreakdownByMonth'],
        ]);
    }

    public function lndex(): string
    {
        $controller = auth_role() === 'admin'
            ? new AdminDashboardController()
            : new TenantDashboardController();

        $controller->initController($this->request, $this->response, service('logger'));

        return $controller->index();
    }

    public function legacyAdminRedirect()
    {
        return redirect()->to(admin_path('dashboard'));
    }
}
