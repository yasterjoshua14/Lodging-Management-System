<?php

namespace App\Models;

use CodeIgniter\Model;

class BookingModel extends Model
{
    protected $table            = 'bookings';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'room_id',
        'tenant_id',
        'check_in',
        'check_out',
        'total_amount',
        'status',
        'notes',
        'checkout_session_id',
        'checkout_url',
        'payment_reference',
        'payment_paid_at',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'room_id'   => 'integer',
        'tenant_id' => 'integer',
    ];
    protected array $castHandlers = [];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $afterFind     = ['appendTenantName'];

    public function withRelations()
    {
        return $this->select('bookings.*, rooms.room_number, rooms.type AS room_type, rooms.pricing_hours, rooms.price_per_night, tenants.first_name AS tenant_first_name, tenants.last_name AS tenant_last_name')
            ->join('rooms', 'rooms.id = bookings.room_id')
            ->join('tenants', 'tenants.id = bookings.tenant_id');
    }

    protected function appendTenantName(array $data): array
    {
        if (! array_key_exists('data', $data) || $data['data'] === null) {
            return $data;
        }

        if (array_is_list($data['data'])) {
            foreach ($data['data'] as $index => $row) {
                if (is_array($row)) {
                    $data['data'][$index] = $this->withTenantName($row);
                }
            }

            return $data;
        }

        if (is_array($data['data'])) {
            $data['data'] = $this->withTenantName($data['data']);
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $row
     *
     * @return array<string, mixed>
     */
    private function withTenantName(array $row): array
    {
        $firstName = trim((string) ($row['tenant_first_name'] ?? ''));
        $lastName  = trim((string) ($row['tenant_last_name'] ?? ''));

        if ($firstName !== '' || $lastName !== '') {
            $row['tenant_name'] = trim($firstName . ' ' . $lastName);
        }

        return $row;
    }
}
