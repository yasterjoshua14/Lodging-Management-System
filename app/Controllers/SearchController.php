<?php

namespace App\Controllers;

use App\Models\BookingModel;
use App\Models\RoomModel;
use App\Models\TenantModel;

class SearchController extends BaseController
{
    public function admin(): string
    {
        $query    = $this->normalizeQuery();
        $sections = [
            $this->buildSection(
                'Quick Links',
                'Jump to the most used admin pages and actions.',
                $this->filterQuickLinks($this->adminQuickLinks(), $query),
                'No admin pages matched that search.'
            ),
            $this->buildSection(
                'Rooms',
                'Search by room number, type, status, capacity, or description.',
                $query !== '' ? $this->searchRooms($query) : [],
                'No rooms matched that search.'
            ),
            $this->buildSection(
                'Tenants',
                'Search tenant names, contact details, ID details, and addresses.',
                $query !== '' ? $this->searchTenants($query) : [],
                'No tenants matched that search.'
            ),
            $this->buildSection(
                'Bookings',
                'Search guests, rooms, notes, payment details, and booking statuses.',
                $query !== '' ? $this->searchAdminBookings($query) : [],
                'No bookings matched that search.'
            ),
        ];

        return view('search/index', $this->buildViewData(
            'Admin Search',
            'Search rooms, tenants, bookings, and admin actions from one place.',
            $query,
            $sections
        ));
    }

    public function tenant(): string
    {
        $query    = $this->normalizeQuery();
        $sections = [
            $this->buildSection(
                'Quick Links',
                'Jump to your dashboard, rooms, bookings, and account pages.',
                $this->filterQuickLinks($this->tenantQuickLinks(), $query),
                'No portal pages matched that search.'
            ),
            $this->buildSection(
                'My Bookings',
                'Search your room numbers, notes, payment details, and booking statuses.',
                $query !== '' ? $this->searchTenantBookings($query) : [],
                'No bookings matched that search.'
            ),
            $this->buildSection(
                'My Account',
                'Search the profile details linked to your tenant account.',
                $query !== '' ? $this->searchTenantProfile($query) : [],
                'No profile details matched that search.'
            ),
        ];

        return view('search/index', $this->buildViewData(
            'Portal Search',
            'Search your booking history, account details, and portal pages from one place.',
            $query,
            $sections
        ));
    }

    private function buildViewData(string $title, string $hint, string $query, array $sections): array
    {
        $resultCount = 0;

        foreach ($sections as $section) {
            $resultCount += count($section['items']);
        }

        return [
            'title'          => $title,
            'searchHint'     => $hint,
            'searchQuery'    => $query,
            'hasQuery'       => $query !== '',
            'resultCount'    => $resultCount,
            'searchSections' => $sections,
        ];
    }

    private function buildSection(string $title, string $description, array $items, string $emptyMessage): array
    {
        return [
            'title'        => $title,
            'description'  => $description,
            'items'        => $items,
            'emptyMessage' => $emptyMessage,
        ];
    }

    private function normalizeQuery(): string
    {
        return substr(trim((string) $this->request->getGet('q')), 0, 80);
    }

    private function filterQuickLinks(array $links, string $query): array
    {
        if ($query === '') {
            return $links;
        }

        return array_values(array_filter($links, function (array $link) use ($query): bool {
            return $this->matchesQuery($query, [
                $link['title'] ?? '',
                $link['summary'] ?? '',
                $link['meta'] ?? '',
            ]);
        }));
    }

    private function adminQuickLinks(): array
    {
        return [
            $this->makeItem('Dashboard', 'Open the admin analytics and performance overview.', admin_path('dashboard'), 'Admin page'),
            $this->makeItem('Rooms', 'Review room inventory, availability, rates, and statuses.', admin_path('rooms'), 'Admin page'),
            $this->makeItem('Tenants', 'Manage tenant profiles, contact details, and records.', admin_path('tenants'), 'Admin page'),
            $this->makeItem('Bookings', 'Review active room bookings, statuses, and totals.', admin_path('bookings'), 'Admin page'),
            $this->makeItem('Add Room', 'Create a new room listing for the inventory.', admin_path('rooms/create'), 'Quick action'),
            $this->makeItem('Add Tenant', 'Create a new tenant profile.', admin_path('tenants/create'), 'Quick action'),
            $this->makeItem('Add Booking', 'Create a new booking record.', admin_path('bookings/create'), 'Quick action'),
        ];
    }

    private function tenantQuickLinks(): array
    {
        return [
            $this->makeItem('Dashboard', 'Review your room bookings and recent account activity.', tenant_path('dashboard'), 'Portal page'),
            $this->makeItem('Rooms', 'Browse room availability and start a new booking.', tenant_path('myRooms'), 'Portal page'),
            $this->makeItem('My Bookings', 'Open your booking history, payments, and room details.', tenant_path('myBookings'), 'Portal page'),
            $this->makeItem('My Account', 'Update your contact details, ID information, and profile.', tenant_path('account'), 'Portal page'),
        ];
    }

    private function searchRooms(string $query): array
    {
        $roomsQuery = (new RoomModel())
            ->groupStart()
            ->like('room_number', $query)
            ->orLike('type', $query)
            ->orLike('status', $query)
            ->orLike('description', $query);

        if ($this->queryLooksNumeric($query)) {
            $roomsQuery
                ->orLike('capacity', $query)
                ->orLike('price_per_night', $query);
        }

        $rooms = $roomsQuery
            ->groupEnd()
            ->orderBy('room_number', 'ASC')
            ->findAll(8);

        return array_map(function (array $room): array {
            $typeLabel   = room_type_options()[$room['type']] ?? humanize_key($room['type'] ?? null);
            $statusLabel = room_status_options()[$room['status']] ?? humanize_key($room['status'] ?? null);

            return $this->makeItem(
                'Room ' . view_text($room['room_number']),
                $typeLabel . ' - Capacity ' . number_format((int) ($room['capacity'] ?? 0)) . ' - ' . format_money($room['price_per_night'] ?? 0),
                admin_path('rooms/' . $room['id'] . '/edit'),
                $statusLabel,
                trim((string) ($room['description'] ?? ''))
            );
        }, $rooms);
    }

    private function searchTenants(string $query): array
    {
        $tenants = (new TenantModel())
            ->groupStart()
            ->like('first_name', $query)
            ->orLike('last_name', $query)
            ->orLike('email', $query)
            ->orLike('phone', $query)
            ->orLike('id_type', $query)
            ->orLike('id_number', $query)
            ->orLike('address', $query)
            ->orLike('emergency_contact_name', $query)
            ->orLike('emergency_contact_phone', $query)
            ->groupEnd()
            ->orderBy('first_name', 'ASC')
            ->orderBy('last_name', 'ASC')
            ->findAll(8);

        return array_map(function (array $tenant): array {
            $summaryParts = array_filter([
                view_text($tenant['email'] ?? '', 'No email on file'),
                view_text($tenant['phone'] ?? '', 'No phone on file'),
            ]);

            $detailParts = array_filter([
                view_text($tenant['address'] ?? ''),
                view_text($tenant['id_type'] ?? ''),
                view_text($tenant['id_number'] ?? ''),
            ]);

            return $this->makeItem(
                person_name($tenant, 'Tenant'),
                implode(' - ', $summaryParts),
                admin_path('tenants/' . $tenant['id'] . '/edit'),
                'Tenant #' . view_text($tenant['id']),
                implode(' - ', $detailParts)
            );
        }, $tenants);
    }

    private function searchAdminBookings(string $query): array
    {
        $bookingsQuery = (new BookingModel())
            ->withRelations()
            ->groupStart()
            ->like('tenants.first_name', $query)
            ->orLike('tenants.last_name', $query)
            ->orLike('rooms.room_number', $query)
            ->orLike('rooms.type', $query)
            ->orLike('bookings.status', $query)
            ->orLike('bookings.created_at', $query)
            ->orLike('bookings.payment_reference', $query)
            ->orLike('bookings.notes', $query);

        if ($this->queryLooksNumeric($query)) {
            $bookingsQuery
                ->orLike('bookings.id', $query)
                ->orLike('bookings.total_amount', $query);
        }

        $bookings = $bookingsQuery
            ->groupEnd()
            ->orderBy('bookings.created_at', 'DESC')
            ->findAll(8);

        return array_map(function (array $booking): array {
            $statusLabel     = booking_status_options()[$booking['status']] ?? humanize_key($booking['status'] ?? null);
            $roomTypeLabel   = room_type_options()[$booking['room_type']] ?? humanize_key($booking['room_type'] ?? null);
            $bookingDetails  = hour_duration_label($booking['pricing_hours'] ?? 1) . ' - ' . format_money($booking['total_amount'] ?? 0);
            $detailFragments = [
                $roomTypeLabel,
                'Booked ' . format_datetime($booking['created_at'] ?? null),
                view_text($booking['notes'] ?? '', 'No notes'),
            ];

            return $this->makeItem(
                'Booking #' . view_text($booking['id']) . ' - Room ' . view_text($booking['room_number']),
                view_text($booking['tenant_name']) . ' - ' . $bookingDetails,
                admin_path('bookings/' . $booking['id'] . '/edit'),
                $statusLabel,
                implode(' - ', $detailFragments)
            );
        }, $bookings);
    }

    private function searchTenantBookings(string $query): array
    {
        $bookingsQuery = (new BookingModel())
            ->withRelations()
            ->where('bookings.tenant_id', auth_tenant_id())
            ->groupStart()
            ->like('rooms.room_number', $query)
            ->orLike('rooms.type', $query)
            ->orLike('bookings.status', $query)
            ->orLike('bookings.created_at', $query)
            ->orLike('bookings.payment_reference', $query)
            ->orLike('bookings.notes', $query);

        if ($this->queryLooksNumeric($query)) {
            $bookingsQuery
                ->orLike('bookings.id', $query)
                ->orLike('bookings.total_amount', $query);
        }

        $bookings = $bookingsQuery
            ->groupEnd()
            ->orderBy('bookings.created_at', 'DESC')
            ->findAll(8);

        return array_map(function (array $booking): array {
            $statusLabel   = booking_status_options()[$booking['status']] ?? humanize_key($booking['status'] ?? null);
            $roomTypeLabel = room_type_options()[$booking['room_type']] ?? humanize_key($booking['room_type'] ?? null);
            $bookingDetail = hour_duration_label($booking['pricing_hours'] ?? 1) . ' - ' . format_money($booking['total_amount'] ?? 0);

            return $this->makeItem(
                'Room ' . view_text($booking['room_number']) . ' - ' . $roomTypeLabel,
                $bookingDetail,
                tenant_path('myBookings'),
                $statusLabel,
                'Booked ' . format_datetime($booking['created_at'] ?? null) . ' - ' . view_text($booking['notes'] ?? '', 'No notes')
            );
        }, $bookings);
    }

    private function searchTenantProfile(string $query): array
    {
        $tenantId = auth_tenant_id();

        if ($tenantId === null) {
            return [];
        }

        $tenant = (new TenantModel())->find($tenantId);

        if ($tenant === null || ! $this->matchesQuery($query, $tenant)) {
            return [];
        }

        $summaryParts = array_filter([
            view_text($tenant['email'] ?? ''),
            view_text($tenant['phone'] ?? ''),
            view_text($tenant['address'] ?? ''),
        ]);

        $detailParts = array_filter([
            view_text($tenant['id_type'] ?? ''),
            view_text($tenant['id_number'] ?? ''),
            view_text($tenant['emergency_contact_name'] ?? ''),
            view_text($tenant['emergency_contact_phone'] ?? ''),
        ]);

        return [
            $this->makeItem(
                person_name($tenant, 'My Account'),
                implode(' - ', $summaryParts),
                tenant_path('account'),
                'My profile',
                implode(' - ', $detailParts)
            ),
        ];
    }

    private function makeItem(string $title, string $summary, string $href, string $meta, string $detail = ''): array
    {
        return [
            'title'   => $title,
            'summary' => $summary,
            'detail'  => $detail,
            'href'    => $href,
            'meta'    => $meta,
        ];
    }

    private function matchesQuery(string $query, array $fields): bool
    {
        foreach ($fields as $field) {
            if (is_scalar($field) || $field instanceof \Stringable) {
                if (stripos((string) $field, $query) !== false) {
                    return true;
                }
            }
        }

        return false;
    }

    private function queryLooksNumeric(string $query): bool
    {
        return preg_match('/[0-9]/', $query) === 1;
    }
}
