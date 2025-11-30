<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use App\Models\IpMonitorModel;

class IpBlockFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $ip = $request->getIPAddress();
        $model = new IpMonitorModel();

        // Check if IP is blocked
        $blocked = $model->where('ip_address', $ip)
                         ->where('blocked', 1)
                         ->first();

        if ($blocked) {
            // Show the blocked page immediately
            echo view('ip_blocked'); // create this view with your blocked page HTML
            exit;
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No action needed after request
    }
}
