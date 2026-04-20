<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRoomsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'room_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'type' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
            ],
            'capacity' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'price_per_night' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'available',
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
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
        $this->forge->addUniqueKey('room_number');
        $this->forge->createTable('rooms');
    }

    public function down()
    {
        $this->forge->dropTable('rooms');
    }
}
