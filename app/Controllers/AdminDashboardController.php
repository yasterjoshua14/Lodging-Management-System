<?php

namespace App\Controllers;

use App\Models\BookingModel;
use App\Models\RoomModel;
use App\Models\TenantModel;

class AdminDashboardController extends BaseController
{
    public function index(): string
    {
        $roomModel    = new RoomModel();
        $tenantModel  = new TenantModel();
        $bookingModel = new BookingModel();

        $stats = [
            'totalRooms'      => $roomModel->countAllResults(),
            'availableRooms'  => $roomModel->where('status', 'available')->countAllResults(),
            'totalTenants'    => $tenantModel->countAllResults(),
            'activeBookings'  => $bookingModel->whereIn('status', ['pending', 'checked_in'])->countAllResults(),
            'completedStays'  => $bookingModel->where('status', 'checked_out')->countAllResults(),
        ];

        $recentBookings = $bookingModel->withRelations()
            ->orderBy('bookings.check_in', 'DESC')
            ->findAll(5);

        $roomsByStatus = [];
        foreach (room_status_options() as $statusKey => $label) {
            $roomsByStatus[] = [
                'label' => $label,
                'count' => $roomModel->where('status', $statusKey)->countAllResults(),
            ];
        }

        return view('admin/dashboard/index', [
            'title'          => 'Dashboard',
            'stats'          => $stats,
            'recentBookings' => $recentBookings,
            'roomsByStatus'  => $roomsByStatus,
        ]);
    }
}
