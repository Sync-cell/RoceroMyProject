<?php

namespace App\Models;

use CodeIgniter\Model;

class UploadModel extends Model
{
    protected $table = 'uploads';
    protected $allowedFields = ['user_id', 'file_name', 'file_type', 'uploaded_at'];
    protected $useTimestamps = false;
}
