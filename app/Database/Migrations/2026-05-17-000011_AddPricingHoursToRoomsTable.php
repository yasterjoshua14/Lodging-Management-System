<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPricingHoursToRoomsTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('rooms', [
            'pricing_hours' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 1,
                'after' => 'price_per_night',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('rooms', 'pricing_hours');
    }
}
