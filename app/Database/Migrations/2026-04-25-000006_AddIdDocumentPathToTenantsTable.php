<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIdDocumentPathToTenantsTable extends Migration
{
    public function up()
    {
        if ($this->db->fieldExists('id_document_path', 'tenants')) {
            return;
        }

        $this->forge->addColumn('tenants', [
            'id_document_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'id_number',
            ],
        ]);
    }

    public function down()
    {
        if (! $this->db->fieldExists('id_document_path', 'tenants')) {
            return;
        }

        $this->forge->dropColumn('tenants', 'id_document_path');
    }
}
