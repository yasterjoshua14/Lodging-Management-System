<?php

namespace App\Controllers;

use App\Models\BookingModel;

class CustomerBookingsController extends BaseController
{
    public function index(): string
    {
        $bookings = (new BookingModel())
            ->withRelations()
            ->where('bookings.tenant_id', auth_tenant_id())
            ->orderBy('bookings.check_in', 'DESC')
            ->findAll();

        return view('customer/bookings', [
            'title'    => 'My Bookings',
            'bookings' => $bookings,
        ]);
    }
}
