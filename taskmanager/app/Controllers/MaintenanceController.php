<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use Config\Services;

class MaintenanceController extends BaseController
{
    private function storagePath(): string
    {
        return WRITEPATH . 'maintenance.json';
    }

    public function getMaintenanceStatus(): array
    {
        $file = $this->storagePath();
        if (!is_file($file)) {
            return [
                'enabled'    => false,
                'admin_ips'  => [],
                'toggled_at' => null,
                'toggled_by' => null,
            ];
        }

        $json = file_get_contents($file);
        $data = json_decode($json, true);
        if (!is_array($data)) {
            return [
                'enabled'    => false,
                'admin_ips'  => [],
                'toggled_at' => null,
                'toggled_by' => null,
            ];
        }

        $data['enabled']    = !empty($data['enabled']);
        $data['admin_ips']  = is_array($data['admin_ips']) ? array_values($data['admin_ips']) : [];
        $data['toggled_at'] = $data['toggled_at'] ?? null;
        $data['toggled_by'] = $data['toggled_by'] ?? null;

        return $data;
    }

    private function saveMaintenanceStatus(array $status): bool
    {
        $file = $this->storagePath();
        $dir = dirname($file);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $json = json_encode($status, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        return (bool) file_put_contents($file, $json);
    }

    private function clientIp(): string
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return trim($_SERVER['HTTP_CLIENT_IP']);
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
        }
        return trim($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
    }

    public function checkMaintenance()
    {
        $status = $this->getMaintenanceStatus();
        return view('maintenance', ['status' => $status, 'currentIp' => $this->clientIp()]);
    }

    public function toggle()
    {
        helper('activity');
        helper('url');

        $status = $this->getMaintenanceStatus();
        $enabled = !$status['enabled'];
        $status['enabled'] = $enabled;
        $status['toggled_at'] = date('Y-m-d H:i:s');
        $status['toggled_by'] = session()->get('username') ?? 'system';

        $adminIps = is_array($status['admin_ips']) ? $status['admin_ips'] : [];
        if ($enabled) {
            $ip = $this->clientIp();
            if (!in_array($ip, $adminIps, true)) {
                $adminIps[] = $ip;
            }
            $status['admin_ips'] = array_values($adminIps);
        }

        $ok = $this->saveMaintenanceStatus($status);
        if ($ok) {
            session()->setFlashdata('success', $enabled ? 'Maintenance enabled.' : 'Maintenance disabled.');
            activity_log($enabled ? 'Enable Maintenance' : 'Disable Maintenance', 'Maintenance toggled by ' . ($status['toggled_by'] ?? 'system') . ' - enabled=' . ($enabled ? '1':'0'));
        } else {
            session()->setFlashdata('error', 'Failed to update maintenance status.');
        }

        return redirect()->back();
    }

    public function addWhitelistIp()
    {
        helper('activity');
        helper('url');

        $ip = trim($this->request->getPost('whitelist_ip') ?? '');
        if (empty($ip) || !filter_var($ip, FILTER_VALIDATE_IP)) {
            session()->setFlashdata('error', 'Invalid IP address.');
            return redirect()->back();
        }

        $status = $this->getMaintenanceStatus();
        $adminIps = is_array($status['admin_ips']) ? $status['admin_ips'] : [];
        if (!in_array($ip, $adminIps, true)) {
            $adminIps[] = $ip;
            $status['admin_ips'] = array_values($adminIps);
            $this->saveMaintenanceStatus($status);
            session()->setFlashdata('success', 'IP added to whitelist.');
            activity_log('Add Whitelist IP', 'Added IP ' . $ip . ' to maintenance whitelist');
        } else {
            session()->setFlashdata('error', 'IP already in whitelist.');
        }

        return redirect()->back();
    }

    public function removeWhitelistIp()
    {
        helper('activity');
        helper('url');

        $ip = trim($this->request->getPost('remove_ip') ?? '');
        if (empty($ip) || !filter_var($ip, FILTER_VALIDATE_IP)) {
            session()->setFlashdata('error', 'Invalid IP address.');
            return redirect()->back();
        }

        $status = $this->getMaintenanceStatus();
        $adminIps = is_array($status['admin_ips']) ? $status['admin_ips'] : [];
        $filtered = array_values(array_filter($adminIps, fn($x) => $x !== $ip));

        if (count($filtered) === count($adminIps)) {
            session()->setFlashdata('error', 'IP not found in whitelist.');
            return redirect()->back();
        }

        $status['admin_ips'] = $filtered;
        $this->saveMaintenanceStatus($status);
        session()->setFlashdata('success', 'IP removed from whitelist.');
        activity_log('Remove Whitelist IP', 'Removed IP ' . $ip . ' from maintenance whitelist');

        return redirect()->back();
    }
}