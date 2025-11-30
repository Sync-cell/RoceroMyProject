<?php


namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class MaintenanceFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $maintenanceFile = WRITEPATH . 'maintenance.json';
        if (!is_file($maintenanceFile)) {
            return null;
        }

        $status = json_decode(file_get_contents($maintenanceFile), true);
        if (!($status['enabled'] ?? false)) {
            return null;
        }

        // Prefer framework method to get IP
        $userIp = $request->getIPAddress() ?: $this->getClientIp();
        $adminIps = $status['admin_ips'] ?? [];

        // Allow if the IP is whitelisted
        if (in_array($userIp, $adminIps, true)) {
            return null;
        }

        // Allow admin and maintenance routes so admins can toggle/manage maintenance
        $path = trim($request->getUri()->getPath(), '/');
        if ($path === '' || preg_match('#^(admin|maintenance|admin/login|admin/authenticate|admin/logout)#i', $path)) {
            return null;
        }

        // Return a proper Response with maintenance view and 503 status
        $body = view('maintenance', ['status' => $status, 'userIp' => $userIp]);
        $response = Services::response();
        $response->setStatusCode(503)->setBody($body);
        return $response;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // nothing
    }

    private function getClientIp()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        }
        return trim($ip);
    }
}