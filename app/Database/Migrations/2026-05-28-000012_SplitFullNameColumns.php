<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SplitFullNameColumns extends Migration
{
    public function up()
    {
        $this->splitNameColumn('users');
        $this->splitNameColumn('tenants');
    }

    public function down()
    {
        $this->mergeNameColumns('users');
        $this->mergeNameColumns('tenants');
    }

    private function splitNameColumn(string $table): void
    {
        if (! $this->db->tableExists($table)) {
            return;
        }

        if (! $this->db->fieldExists('first_name', $table)) {
            $this->forge->addColumn($table, [
                'first_name' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 60,
                    'default'    => '',
                    'after'      => 'id',
                ],
            ]);
        }

        if (! $this->db->fieldExists('last_name', $table)) {
            $this->forge->addColumn($table, [
                'last_name' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 60,
                    'default'    => '',
                    'after'      => 'first_name',
                ],
            ]);
        }

        if (! $this->db->fieldExists('full_name', $table)) {
            return;
        }

        $rows = $this->db->table($table)
            ->select('id, full_name')
            ->get()
            ->getResultArray();

        foreach ($rows as $row) {
            [$firstName, $lastName] = $this->splitFullName((string) ($row['full_name'] ?? ''));

            $this->db->table($table)
                ->where('id', $row['id'])
                ->update([
                    'first_name' => $firstName,
                    'last_name'  => $lastName,
                ]);
        }

        $this->forge->dropColumn($table, 'full_name');
    }

    private function mergeNameColumns(string $table): void
    {
        if (! $this->db->tableExists($table)) {
            return;
        }

        if (! $this->db->fieldExists('full_name', $table)) {
            $this->forge->addColumn($table, [
                'full_name' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 120,
                    'default'    => '',
                    'after'      => 'id',
                ],
            ]);
        }

        if ($this->db->fieldExists('first_name', $table) && $this->db->fieldExists('last_name', $table)) {
            $rows = $this->db->table($table)
                ->select('id, first_name, last_name')
                ->get()
                ->getResultArray();

            foreach ($rows as $row) {
                $this->db->table($table)
                    ->where('id', $row['id'])
                    ->update([
                        'full_name' => $this->joinName(
                            (string) ($row['first_name'] ?? ''),
                            (string) ($row['last_name'] ?? '')
                        ),
                    ]);
            }
        }

        if ($this->db->fieldExists('first_name', $table)) {
            $this->forge->dropColumn($table, 'first_name');
        }

        if ($this->db->fieldExists('last_name', $table)) {
            $this->forge->dropColumn($table, 'last_name');
        }
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function splitFullName(string $fullName): array
    {
        $normalized = trim((string) preg_replace('/\s+/', ' ', $fullName));

        if ($normalized === '') {
            return ['', ''];
        }

        $parts = preg_split('/\s+/', $normalized, 2);

        return [
            substr((string) ($parts[0] ?? ''), 0, 60),
            substr((string) ($parts[1] ?? ''), 0, 60),
        ];
    }

    private function joinName(string $firstName, string $lastName): string
    {
        return substr(trim($firstName . ' ' . $lastName), 0, 120);
    }
}
