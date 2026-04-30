<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPasswordRecovery extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('recovery_phone', 'users')) {
            $this->forge->addColumn('users', [
                'recovery_phone' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 30,
                    'null'       => true,
                    'after'      => 'email',
                ],
            ]);

            $this->db->table('users')
                ->where('email', 'admin@lodging.test')
                ->where('role', 'admin')
                ->set('recovery_phone', '09170000000')
                ->update();
        }

        if (! $this->db->tableExists('password_reset_otps')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'user_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'role' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                ],
                'channel' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 10,
                ],
                'identifier_hash' => [
                    'type'       => 'CHAR',
                    'constraint' => 64,
                ],
                'otp_hash' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                ],
                'masked_destination' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 160,
                ],
                'attempts' => [
                    'type'       => 'TINYINT',
                    'constraint' => 3,
                    'unsigned'   => true,
                    'default'    => 0,
                ],
                'send_count' => [
                    'type'       => 'TINYINT',
                    'constraint' => 3,
                    'unsigned'   => true,
                    'default'    => 1,
                ],
                'last_sent_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'expires_at' => [
                    'type' => 'DATETIME',
                ],
                'verified_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'consumed_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'request_ip' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 45,
                    'null'       => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);

            $this->forge->addKey('id', true);
            $this->forge->addKey('user_id');
            $this->forge->addKey('identifier_hash');
            $this->forge->addKey('expires_at');
            $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
            $this->forge->createTable('password_reset_otps');
        }
    }

    public function down()
    {
        if ($this->db->tableExists('password_reset_otps')) {
            $this->forge->dropTable('password_reset_otps');
        }

        if ($this->db->fieldExists('recovery_phone', 'users')) {
            $this->forge->dropColumn('users', 'recovery_phone');
        }
    }
}
