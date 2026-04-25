<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class LodgingSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        $this->db->table('users')->insert([
            'full_name'     => 'Demo Manager',
            'email'         => 'admin@lodging.test',
            'role'          => 'admin',
            'tenant_id'     => null,
            'password_hash' => password_hash('password123', PASSWORD_DEFAULT),
            'created_at'    => $now,
            'updated_at'    => $now,
        ]);

        $roomBuilder = $this->db->table('rooms');

        $roomBuilder->insert([
            'room_number'     => '101',
            'type'            => 'standard',
            'capacity'        => 2,
            'price_per_night' => 1800.00,
            'status'          => 'available',
            'description'     => 'Cozy room with twin beds and garden view.',
            'created_at'      => $now,
            'updated_at'      => $now,
        ]);
        $room101 = $this->db->insertID();

        $roomBuilder->insert([
            'room_number'     => '205',
            'type'            => 'deluxe',
            'capacity'        => 3,
            'price_per_night' => 2800.00,
            'status'          => 'occupied',
            'description'     => 'Deluxe room with balcony and breakfast inclusion.',
            'created_at'      => $now,
            'updated_at'      => $now,
        ]);
        $room205 = $this->db->insertID();

        $roomBuilder->insert([
            'room_number'     => '301',
            'type'            => 'suite',
            'capacity'        => 4,
            'price_per_night' => 4500.00,
            'status'          => 'maintenance',
            'description'     => 'Premium suite undergoing scheduled maintenance.',
            'created_at'      => $now,
            'updated_at'      => $now,
        ]);

        $tenantBuilder = $this->db->table('tenants');

        $tenantBuilder->insert([
            'full_name'               => 'Maria Santos',
            'email'                   => 'maria@example.com',
            'phone'                   => '09171234567',
            'id_type'                 => 'Passport',
            'id_number'               => 'P1234567',
            'address'                 => 'Cebu City',
            'emergency_contact_name'  => 'Luis Santos',
            'emergency_contact_phone' => '09179876543',
            'created_at'              => $now,
            'updated_at'              => $now,
        ]);
        $maria = $this->db->insertID();

        $this->db->table('users')->insert([
            'full_name'     => 'Maria Santos',
            'email'         => 'maria@example.com',
            'role'          => 'tenant',
            'tenant_id'     => $maria,
            'password_hash' => password_hash('password123', PASSWORD_DEFAULT),
            'created_at'    => $now,
            'updated_at'    => $now,
        ]);

        $tenantBuilder->insert([
            'full_name'               => 'John Dela Cruz',
            'email'                   => 'john@example.com',
            'phone'                   => '09170001111',
            'id_type'                 => 'National ID',
            'id_number'               => 'NID-567890',
            'address'                 => 'Davao City',
            'emergency_contact_name'  => 'Anna Dela Cruz',
            'emergency_contact_phone' => '09175550000',
            'created_at'              => $now,
            'updated_at'              => $now,
        ]);
        $john = $this->db->insertID();

        $this->db->table('bookings')->insertBatch([
            [
                'room_id'       => $room101,
                'tenant_id'     => $maria,
                'check_in'      => '2026-04-21',
                'check_out'     => '2026-04-24',
                'total_amount'  => 5400.00,
                'status'        => 'pending',
                'notes'         => 'Arrival expected in the afternoon.',
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'room_id'       => $room205,
                'tenant_id'     => $john,
                'check_in'      => '2026-04-18',
                'check_out'     => '2026-04-22',
                'total_amount'  => 11200.00,
                'status'        => 'checked_in',
                'notes'         => 'Extended stay guest.',
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
        ]);
    }
}
