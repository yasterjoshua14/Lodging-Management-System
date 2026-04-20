<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRolesAndTenantLinksToUsersTable extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('role', 'users')) {
            $this->db->query("ALTER TABLE `users` ADD COLUMN `role` VARCHAR(20) NOT NULL DEFAULT 'customer' AFTER `email`");
        }

        if (! $this->db->fieldExists('tenant_id', 'users')) {
            $this->db->query("ALTER TABLE `users` ADD COLUMN `tenant_id` INT(11) UNSIGNED NULL DEFAULT NULL AFTER `role`");
            $this->db->query('ALTER TABLE `users` ADD UNIQUE KEY `users_tenant_id_unique` (`tenant_id`)');
            $this->db->query('ALTER TABLE `users` ADD CONSTRAINT `users_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON UPDATE RESTRICT ON DELETE RESTRICT');
        }

        $this->db->table('users')
            ->where('tenant_id', null)
            ->set('role', 'admin')
            ->update();
    }

    public function down()
    {
        if ($this->db->fieldExists('tenant_id', 'users')) {
            $this->db->query('ALTER TABLE `users` DROP FOREIGN KEY `users_tenant_id_foreign`');
            $this->db->query('ALTER TABLE `users` DROP INDEX `users_tenant_id_unique`');
            $this->db->query('ALTER TABLE `users` DROP COLUMN `tenant_id`');
        }

        if ($this->db->fieldExists('role', 'users')) {
            $this->db->query('ALTER TABLE `users` DROP COLUMN `role`');
        }
    }
}
