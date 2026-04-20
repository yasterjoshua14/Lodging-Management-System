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

    public function withRelations()
    {
        return $this->select('bookings.*, rooms.room_number, rooms.type AS room_type, tenants.full_name AS tenant_name')
            ->join('rooms', 'rooms.id = bookings.room_id')
            ->join('tenants', 'tenants.id = bookings.tenant_id');
    }
}
