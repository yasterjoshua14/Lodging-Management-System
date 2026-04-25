<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class NormalizeTenantPortalRoles extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('role', 'users')) {
            return;
        }

        $this->db->query("UPDATE `users` SET `role` = 'tenant' WHERE `role` <> 'admin'");
        $this->db->query("ALTER TABLE `users` MODIFY `role` VARCHAR(20) NOT NULL DEFAULT 'tenant'");
    }

    public function down()
    {
    }
}
