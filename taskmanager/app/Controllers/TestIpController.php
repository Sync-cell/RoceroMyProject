<?php

namespace App\Controllers;

use App\Models\IpMonitorModel;

class TestIpController extends BaseController
{
    public function status()
    {
        $ip = $this->request->getIPAddress();
        $model = new IpMonitorModel();
        $row = $model->where('ip_address', $ip)->first();
        return $this->response->setJSON([
            'ip' => $ip,
            'found' => (bool)$row,
            'blocked' => $row ? (bool)$row['blocked'] : false,
            'row' => $row
        ]);
    }
}