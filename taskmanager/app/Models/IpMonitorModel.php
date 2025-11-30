<?php

namespace App\Models;

use CodeIgniter\Model;

class IpMonitorModel extends Model
{
    protected $table      = 'ip_monitor';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'ip_address', 'user_id', 'username', 'hits',
        'first_seen', 'last_seen',
        'blocked', 'blocked_by', 'blocked_reason', 'blocked_at'
    ];
}