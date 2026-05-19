<?php

namespace App\Controllers;

use App\Models\BookingModel;
use App\Models\TenantModel;

class TenantDashboardController extends BaseController
{
    public function index(): string
    {
        $tenantId = auth_tenant_id();

        $stats = [
            'totalBookings'     => (new BookingModel())->where('tenant_id', $tenantId)->countAllResults(),
            'pendingBookings'   => (new BookingModel())
                ->where('tenant_id', $tenantId)
                ->whereIn('status', ['awaiting_payment', 'pending'])
                ->countAllResults(),
            'activeBookings'    => (new BookingModel())
                ->where('tenant_id', $tenantId)
                ->where('status', 'checked_in')
                ->countAllResults(),
            'completedBookings' => (new BookingModel())
                ->where('tenant_id', $tenantId)
                ->where('status', 'checked_out')
                ->countAllResults(),
        ];

        $currentBooking = (new BookingModel())
            ->withRelations()
            ->where('bookings.tenant_id', $tenantId)
            ->whereIn('bookings.status', active_booking_statuses())
            ->orderBy('bookings.created_at', 'DESC')
            ->first();

        $recentBookings = (new BookingModel())
            ->withRelations()
            ->where('bookings.tenant_id', $tenantId)
            ->orderBy('bookings.created_at', 'DESC')
            ->findAll(5);

        return view('tenant/dashboard/index', [
            'title'          => 'My Dashboard',
            'stats'          => $stats,
            'currentBooking' => $currentBooking,
            'recentBookings' => $recentBookings,
            'tenant'         => (new TenantModel())->find($tenantId),
        ]);
    }
}
