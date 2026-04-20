<?php

namespace App\Controllers;

use App\Models\BookingModel;
use App\Models\TenantModel;

class CustomerDashboardController extends BaseController
{
    public function index(): string
    {
        $tenantId = auth_tenant_id();
        $today    = date('Y-m-d');

        $stats = [
            'totalBookings'    => (new BookingModel())->where('tenant_id', $tenantId)->countAllResults(),
            'upcomingBookings' => (new BookingModel())
                ->where('tenant_id', $tenantId)
                ->where('check_in >', $today)
                ->whereIn('status', ['pending', 'checked_in'])
                ->countAllResults(),
            'activeStays'      => (new BookingModel())
                ->where('tenant_id', $tenantId)
                ->where('check_in <=', $today)
                ->where('check_out >', $today)
                ->whereIn('status', ['pending', 'checked_in'])
                ->countAllResults(),
            'completedStays'   => (new BookingModel())
                ->where('tenant_id', $tenantId)
                ->where('status', 'checked_out')
                ->countAllResults(),
        ];

        $nextBooking = (new BookingModel())
            ->withRelations()
            ->where('bookings.tenant_id', $tenantId)
            ->where('bookings.check_out >=', $today)
            ->orderBy('bookings.check_in', 'ASC')
            ->first();

        $recentBookings = (new BookingModel())
            ->withRelations()
            ->where('bookings.tenant_id', $tenantId)
            ->orderBy('bookings.check_in', 'DESC')
            ->findAll(5);

        return view('customer/dashboard', [
            'title'          => 'My Dashboard',
            'stats'          => $stats,
            'nextBooking'    => $nextBooking,
            'recentBookings' => $recentBookings,
            'tenant'         => (new TenantModel())->find($tenantId),
        ]);
    }
}
