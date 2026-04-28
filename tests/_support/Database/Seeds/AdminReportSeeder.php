<?php

namespace Tests\Support\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AdminReportSeeder extends Seeder
{
    public function run()
    {
        $now = '2026-04-28 09:00:00';

        $this->db->table('rooms')->insertBatch([
            [
                'id'              => 1,
                'room_number'     => '101',
                'type'            => 'standard',
                'capacity'        => 2,
                'price_per_night' => 1800.00,
                'status'          => 'available',
                'created_at'      => $now,
                'updated_at'      => $now,
            ],
            [
                'id'              => 2,
                'room_number'     => '205',
                'type'            => 'deluxe',
                'capacity'        => 3,
                'price_per_night' => 2800.00,
                'status'          => 'occupied',
                'created_at'      => $now,
                'updated_at'      => $now,
            ],
            [
                'id'              => 3,
                'room_number'     => '301',
                'type'            => 'suite',
                'capacity'        => 4,
                'price_per_night' => 4500.00,
                'status'          => 'maintenance',
                'created_at'      => $now,
                'updated_at'      => $now,
            ],
        ]);

        $this->db->table('tenants')->insertBatch([
            [
                'id'         => 1,
                'full_name'  => 'Maria Santos',
                'phone'      => '09171234567',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id'         => 2,
                'full_name'  => 'John Dela Cruz',
                'phone'      => '09170001111',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id'         => 3,
                'full_name'  => 'Ana Reyes',
                'phone'      => '09175557777',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        $this->db->table('bookings')->insertBatch([
            [
                'room_id'       => 1,
                'tenant_id'     => 1,
                'check_in'      => '2026-04-21',
                'check_out'     => '2026-04-24',
                'total_amount'  => 5400.00,
                'status'        => 'pending',
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'room_id'       => 2,
                'tenant_id'     => 2,
                'check_in'      => '2026-04-18',
                'check_out'     => '2026-04-22',
                'total_amount'  => 11200.00,
                'status'        => 'checked_in',
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'room_id'       => 2,
                'tenant_id'     => 3,
                'check_in'      => '2026-03-02',
                'check_out'     => '2026-03-05',
                'total_amount'  => 8400.00,
                'status'        => 'checked_out',
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'room_id'       => 3,
                'tenant_id'     => 3,
                'check_in'      => '2026-02-10',
                'check_out'     => '2026-02-12',
                'total_amount'  => 9000.00,
                'status'        => 'cancelled',
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
        ]);
    }
}
