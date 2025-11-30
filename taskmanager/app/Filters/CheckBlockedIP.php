<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\IpMonitorModel;

class CheckBlockedIP implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $ip = $request->getIPAddress();
        $model = new IpMonitorModel();
        
        $row = $model->where('ip_address', $ip)->first();
        if ($row && $row['blocked']) {
            return redirect()->to('/blocked');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // no-op
    }
}
