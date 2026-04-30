<?php

namespace App\Models;

use CodeIgniter\Model;

class PasswordResetOtpModel extends Model
{
    protected $table            = 'password_reset_otps';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'role',
        'channel',
        'identifier_hash',
        'otp_hash',
        'masked_destination',
        'attempts',
        'send_count',
        'last_sent_at',
        'expires_at',
        'verified_at',
        'consumed_at',
        'request_ip',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'id'         => 'integer',
        'user_id'    => 'integer',
        'attempts'   => 'integer',
        'send_count' => 'integer',
    ];
    protected array $castHandlers = [];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
