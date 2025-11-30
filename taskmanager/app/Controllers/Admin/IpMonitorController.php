<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\IpMonitorModel;

class IpMonitorController extends BaseController
{
    protected $model;

    public function __construct()
    {
        $this->model = new IpMonitorModel();
        helper(['url', 'form', 'session']);
    }

    /**
     * Display IP monitor table
     */
    public function index()
    {
        $data['ips'] = $this->model->orderBy('id', 'DESC')->findAll();
        return view('admin/ip_monitor', $data);
    }

    /**
     * Add or update IP manually
     */
    public function manualAdd()
    {
        if ($this->request->getMethod() !== 'post') {
            return redirect()->back();
        }

        $ip = trim($this->request->getPost('ip_address') ?? '');
        $username = trim($this->request->getPost('username') ?? null);

        // Validate IP
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return redirect()->back()->with('error', 'Invalid IP address.');
        }

        $now = date('Y-m-d H:i:s');
        $existing = $this->model->where('ip_address', $ip)->first();

        if ($existing) {
            // Update existing IP
            $this->model->update($existing['id'], [
                'hits'      => ((int)$existing['hits'] + 1),
                'username'  => $username ?: $existing['username'],
                'last_seen' => $now,
            ]);
            return redirect()->back()->with('message', 'IP updated successfully.');
        }

        // Insert new IP
        $this->model->insert([
            'ip_address' => $ip,
            'username'   => $username,
            'hits'       => 1,
            'first_seen' => $now,
            'last_seen'  => $now,
            'blocked'    => 0,
        ]);

        return redirect()->back()->with('message', 'IP added successfully.');
    }

    /**
     * Block an IP
     */
    public function block($id = null)
{
    if (!is_numeric($id)) {
        return redirect()->back()->with('error', 'Invalid IP ID.');
    }

    $row = $this->model->find($id);
    if (!$row) {
        return redirect()->back()->with('error', 'IP not found.');
    }

    $this->model->update($id, [
        'blocked'        => 1,
        'blocked_by'     => session()->get('username') ?? 'admin',
        'blocked_reason' => 'Manual block',
        'blocked_at'     => date('Y-m-d H:i:s'),
    ]);

    return redirect()->back()->with('message', 'IP blocked successfully.');
}

public function unblock($id = null)
{
    if (!is_numeric($id)) {
        return redirect()->back()->with('error', 'Invalid IP ID.');
    }

    $row = $this->model->find($id);
    if (!$row) {
        return redirect()->back()->with('error', 'IP not found.');
    }

    $this->model->update($id, [
        'blocked'        => 0,
        'blocked_by'     => null,
        'blocked_reason' => null,
        'blocked_at'     => null,
    ]);

    return redirect()->back()->with('message', 'IP unblocked successfully.');
}

}
