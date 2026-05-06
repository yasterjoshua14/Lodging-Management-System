<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPaymentFieldsToBookingsTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('bookings', [
            'checkout_session_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'notes',
            ],
            'checkout_url' => [
                'type'  => 'TEXT',
                'null'  => true,
                'after' => 'checkout_session_id',
            ],
            'payment_reference' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
                'null'       => true,
                'after'      => 'checkout_url',
            ],
            'payment_paid_at' => [
                'type'  => 'DATETIME',
                'null'  => true,
                'after' => 'payment_reference',
            ],
        ]);
    }

    public function down()
    {
        foreach (['payment_paid_at', 'payment_reference', 'checkout_url', 'checkout_session_id'] as $column) {
            $this->forge->dropColumn('bookings', $column);
        }
    }
}
