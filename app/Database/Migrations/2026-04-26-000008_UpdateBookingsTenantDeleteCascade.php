<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateBookingsTenantDeleteCascade extends Migration
{
    public function up()
    {
        if ($this->db->DBDriver !== 'MySQLi') {
            return;
        }

        $this->db->query('ALTER TABLE `bookings` DROP FOREIGN KEY `bookings_tenant_id_foreign`');
        $this->db->query('ALTER TABLE `bookings` ADD CONSTRAINT `bookings_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE');
    }

    public function down()
    {
        if ($this->db->DBDriver !== 'MySQLi') {
            return;
        }

        $this->db->query('ALTER TABLE `bookings` DROP FOREIGN KEY `bookings_tenant_id_foreign`');
        $this->db->query('ALTER TABLE `bookings` ADD CONSTRAINT `bookings_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE');
    }
}
