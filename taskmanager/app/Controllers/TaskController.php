<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\TaskModel;
use App\Models\UserModel;
use Config\Database;

class TaskController extends BaseController
{
    protected $taskModel;
    protected $userModel;
    protected $db;

    public function __construct()
    {
        $this->taskModel = new TaskModel();
        $this->userModel = new UserModel();
        $this->db = Database::connect();
    }

    // Show tasks (admin and user)
    public function index()
    {
        if (!session()->get('user_logged_in')) {
            return redirect()->to('/admin/login')->with('error', 'Please log in.');
        }

        $role = session()->get('role');

        if ($role === 'admin') {
            $tasks = $this->getAllTasksWithAcceptances();
            $users = $this->userModel->where('role', 'user')->findAll() ?: [];

            return view('admin/dashboard', [
                'tasks' => $tasks,
                'users' => $users
            ]);
        }

        // Normal user sees only tasks assigned to them
        $userId = (int) session()->get('user_id');
        $tasks = $this->taskModel
            ->where('assigned_to', $userId)
            ->findAll() ?: [];

        foreach ($tasks as &$task) {
            $task['accepted_users'] = $this->db->table('task_acceptances')
                ->select('users.id as user_id, users.username')
                ->join('users', 'users.id = task_acceptances.user_id')
                ->where('task_acceptances.task_id', $task['id'])
                ->get()
                ->getResultArray();
            $task['assigned_username'] = session()->get('username');
        }
        unset($task);

        return view('dashboard', ['tasks' => $tasks]);
    }

    // Admin: store new task
    public function store()
    {
        if ($this->isNotAdmin()) return $this->denyAccess();

        $assignedTo = (int) $this->request->getPost('assigned_to');
        if (empty($assignedTo)) {
            return redirect()->back()->with('error', 'Please select a user.');
        }

        $data = [
            'title'       => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'priority'    => $this->request->getPost('priority'),
            'deadline'    => $this->request->getPost('deadline'),
            'status'      => $this->request->getPost('status'),
            'assigned_to' => $assignedTo,
        ];

        $this->taskModel->save($data);

        $this->logActivity('Create Task', 'Created task: ' . ($data['title'] ?? '') . ' (assigned_to=' . $assignedTo . ')');

        return redirect()->to('/tasks')->with('success', 'Task created and assigned.');
    }

    // Admin: edit task
    public function edit($id)
    {
        if ($this->isNotAdmin()) return $this->denyAccess();

        $task = $this->taskModel->find((int)$id);
        if (!$task) return redirect()->to('/tasks')->with('error', 'Task not found.');

        $users = $this->userModel->where('role', 'user')->findAll();

        return view('tasks/edit', [
            'task'  => $task,
            'users' => $users
        ]);
    }

    // Admin: update task
    public function update($id)
    {
        if ($this->isNotAdmin()) return $this->denyAccess();

        $assignedTo = (int) $this->request->getPost('assigned_to');
        if (empty($assignedTo)) {
            return redirect()->back()->with('error', 'Please select a user.');
        }

        $data = [
            'title'       => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'priority'    => $this->request->getPost('priority'),
            'deadline'    => $this->request->getPost('deadline'),
            'status'      => $this->request->getPost('status'),
            'assigned_to' => $assignedTo,
        ];

        $this->taskModel->update((int)$id, $data);

        $this->logActivity('Update Task', 'Updated task ID: ' . (int)$id);

        return redirect()->to('/tasks')->with('success', 'Task updated.');
    }

    // Admin: delete task
    public function delete($id)
    {
        if ($this->isNotAdmin()) return $this->denyAccess();

        $id = (int)$id;
        if ($this->taskModel->find($id)) {
            // Also delete task acceptances
            $this->db->table('task_acceptances')->where('task_id', $id)->delete();
            $this->taskModel->delete($id);

            $this->logActivity('Delete Task', 'Deleted task ID: ' . $id);

            return redirect()->to('/tasks')->with('success', 'Task deleted.');
        }

        return redirect()->to('/tasks')->with('error', 'Task not found.');
    }

    // User: accept task (sets status to In Progress)
    public function accept($task_id)
    {
        if (!$this->isUser()) {
            return redirect()->to('/admin/login')->with('error', 'Please log in as user.');
        }

        $task_id = (int)$task_id;
        $task = $this->taskModel->find($task_id);

        if (!$task) {
            return redirect()->back()->with('error', 'Task not found.');
        }

        if ((int)$task['assigned_to'] !== (int) session()->get('user_id')) {
            return redirect()->back()->with('error', 'You are not assigned to this task.');
        }

        $existing = $this->db->table('task_acceptances')
            ->where('task_id', $task_id)
            ->where('user_id', session()->get('user_id'))
            ->get()
            ->getRow();

        if (!$existing) {
            // insert acceptance
            $this->db->table('task_acceptances')->insert([
                'task_id' => $task_id,
                'user_id' => session()->get('user_id'),
            ]);

            // update task status to In Progress (only if not already Completed)
            if (!isset($task['status']) || strtolower($task['status']) !== 'completed') {
                $this->taskModel->update($task_id, ['status' => 'In Progress']);
            }

            $this->logActivity('Accept Task', 'Accepted task ID: ' . $task_id);

            return redirect()->to('/dashboard')->with('success', 'Task accepted and marked In Progress.');
        }

        return redirect()->back()->with('error', 'You already accepted this task.');
    }

    // Admin or assigned user marks task as completed
    public function done($task_id)
    {
        $task_id = (int)$task_id;
        $task = $this->taskModel->find($task_id);

        if (!$task) {
            return redirect()->back()->with('error', 'Task not found.');
        }

        $userId = (int) session()->get('user_id');
        $role = session()->get('role');

        if ((int)$task['assigned_to'] !== $userId && $role !== 'admin') {
            return redirect()->back()->with('error', 'You cannot complete a task that is not assigned to you.');
        }

        // Check if user accepted task (skip for admin)
        if ($role !== 'admin') {
            $accepted = $this->db->table('task_acceptances')
                ->where('task_id', $task_id)
                ->where('user_id', $userId)
                ->get()
                ->getRow();

            if (!$accepted) {
                return redirect()->back()->with('error', 'You must accept the task before marking it as done.');
            }
        }

        $this->taskModel->update($task_id, ['status' => 'Completed']);

        $this->logActivity('Complete Task', 'Completed task ID: ' . $task_id);

        // Redirect explicitly to the correct dashboard so the completed list reloads
        if ($role === 'admin') {
            return redirect()->to('/admin/dashboard')->with('success', 'Task marked as completed.');
        }

        return redirect()->to('/dashboard')->with('success', 'Task marked as completed.');
    }

    // View completed tasks
    public function completed()
    {
        if (!session()->get('user_logged_in')) {
            return redirect()->to('/admin/login')->with('error', 'Please log in.');
        }

        $role = session()->get('role');
        $userId = (int) session()->get('user_id');

        if ($role === 'admin') {
            $tasks = $this->taskModel->where('status', 'Completed')->findAll() ?: [];
        } else {
            $tasks = $this->taskModel
                ->where('status', 'Completed')
                ->where('assigned_to', $userId)
                ->findAll() ?: [];
        }

        foreach ($tasks as &$task) {
            $assignedUser = $this->userModel->find($task['assigned_to']);
            $task['assigned_username'] = $assignedUser ? $assignedUser['username'] : 'Unassigned';
        }
        unset($task);

        return view('tasks/completed', ['tasks' => $tasks]);
    }

    // Export completed tasks as CSV
    public function exportCsv()
    {
        if (!session()->get('user_logged_in')) {
            return redirect()->to('/admin/login')->with('error', 'Please log in.');
        }

        // fetch tasks with assigned username
        $tasks = $this->getAllTasksWithAcceptances();

        // keep only completed tasks
        $completedTasks = array_filter($tasks, function($t){
            $s = strtolower(trim((string) ($t['status'] ?? '')));
            return in_array($s, ['completed','done','finished'], true);
        });

        // Log export action (before output)
        $this->logActivity('Export Completed CSV', 'Exported completed tasks CSV, count: ' . count($completedTasks));

        // normalize priority
        $normalizePriority = function($p): string {
            $p = (string) ($p ?? '');
            $p = trim(strtolower($p));
            if ($p === 'high' || $p === 'h' || $p === '3') return 'High';
            if ($p === 'low'  || $p === 'l' || $p === '1') return 'Low';
            return 'Normal';
        };

        // format deadline as YYYY-MM-DD
        $formatDeadline = function($d): string {
            if ($d === null || $d === '') return '';
            $d = trim((string)$d);
            if (in_array($d, ['#','-','N/A','null','0'], true)) return '';

            // if already in YYYY-MM-DD format, return as is
            if (preg_match('/^\d{4}-\d{2}-\d{2}/', $d)) {
                return substr($d, 0, 10);
            }

            if (is_numeric($d) && (int)$d > 0) {
                try {
                    $dt = new \DateTime('@' . (int)$d);
                    return $dt->format('Y-m-d');
                } catch (\Exception $e) { /* fallthrough */ }
            }

            try {
                $dt = new \DateTime($d);
                return $dt->format('Y-m-d');
            } catch (\Exception $e) {
                return '';
            }
        };

        // resolve assigned username from assigned_to id
        $resolveAssignedUsername = function($assignedToId): string {
            if (empty($assignedToId)) return '';
            try {
                $user = $this->userModel->find((int)$assignedToId);
                if ($user) {
                    return $user['username'] ?? ($user['name'] ?? '');
                }
            } catch (\Throwable $e) {
                // ignore
            }
            return '';
        };

        $filename = 'Completed-Tasks-' . date('Y-m-d_H-i-s') . '.csv';
        header('Content-Type: text/csv; charset=utf-8-sig');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');

        // Write BOM for Excel UTF-8 support
        fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

        // CSV header (presentable format)
        fputcsv($out, [
            'Task ID',
            'Task Title',
            'Description',
            'Priority Level',
            'Deadline',
            'Status',
            'Assigned To'
        ]);

        // output completed tasks
        foreach ($completedTasks as $t) {
            $priority = $normalizePriority($t['priority'] ?? '');
            $deadline = $formatDeadline($t['deadline'] ?? '');
            $assigned = $resolveAssignedUsername($t['assigned_to'] ?? null);

            fputcsv($out, [
                $t['id'] ?? '',
                $t['title'] ?? '',
                $t['description'] ?? '',
                $priority,
                $deadline,
                'Completed',
                $assigned
            ]);
        }

        fclose($out);
        exit;
    }

    // User: decline task (remove acceptance and reset status)
    public function decline($task_id)
    {
        if (!$this->isUser()) {
            return redirect()->to('/admin/login')->with('error', 'Please log in as user.');
        }

        $task_id = (int)$task_id;
        $task = $this->taskModel->find($task_id);

        if (!$task) {
            return redirect()->back()->with('error', 'Task not found.');
        }

        if ((int)$task['assigned_to'] !== (int) session()->get('user_id')) {
            return redirect()->back()->with('error', 'You are not assigned to this task.');
        }

        $userId = (int) session()->get('user_id');

        // Remove acceptance record
        $this->db->table('task_acceptances')
            ->where('task_id', $task_id)
            ->where('user_id', $userId)
            ->delete();

        // Reset task status to Pending
        $this->taskModel->update($task_id, ['status' => 'Pending']);

        $this->logActivity('Decline Task', 'Declined task ID: ' . $task_id);

        return redirect()->to('/dashboard')->with('success', 'Task declined and reset to Pending.');
    }

    // Get all tasks with acceptances (used by admin)
    public function getAllTasksWithAcceptances()
    {
        $query = $this->db->table('tasks')
            ->select('tasks.*, users.username as assigned_username')
            ->join('users', 'users.id = tasks.assigned_to', 'left')
            ->orderBy('tasks.status', 'ASC')
            ->orderBy('tasks.deadline', 'ASC')
            ->get();

        $tasks = $query->getResultArray();

        foreach ($tasks as &$task) {
            $acceptances = $this->db->table('task_acceptances')
                ->select('users.id, users.username')
                ->join('users', 'users.id = task_acceptances.user_id')
                ->where('task_acceptances.task_id', $task['id'])
                ->get()
                ->getResultArray();

            $task['accepted_users'] = $acceptances;
        }

        return $tasks;
    }

    /**
     * Best-effort MAC address lookup for an IP (server-side ARP).
     * Note: MAC cannot be obtained from HTTP reliably; this is a server-side ARP attempt and may fail.
     */
    private function getMacAddress(?string $ip): ?string
    {
        if (empty($ip)) return null;
        $mac = null;
        try {
            // Only attempt on non-localhost addresses
            if (in_array($ip, ['127.0.0.1', '::1'], true)) {
                return null;
            }

            // run arp -a (Windows) or arp -n (Linux/Mac) and parse (works if exec allowed)
            $cmd = (stripos(PHP_OS, 'WIN') === 0) ? 'arp -a ' . escapeshellarg($ip) : 'arp -n ' . escapeshellarg($ip);
            @exec($cmd, $out, $ret);
            if ($ret === 0 && is_array($out)) {
                $text = implode("\n", $out);
                // look for MAC pattern (xx:xx:xx:xx:xx:xx or xx-xx-xx-xx-xx-xx)
                if (preg_match('/([0-9a-f]{2}[:\-]){5}[0-9a-f]{2}/i', $text, $m)) {
                    $mac = $m[0];
                }
            }
        } catch (\Throwable $e) {
            // ignore - best-effort only
        }
        return $mac;
    }

    /**
     * Centralized activity logger using DB table 'activity_logs'.
     * This is a best-effort logger: failures are caught and ignored.
     */
    private function logActivity(string $action, string $details = ''): void
    {
        try {
            $ip = null;
            if (method_exists($this->request, 'getIPAddress')) {
                $ip = $this->request->getIPAddress();
            } else {
                $ip = $_SERVER['REMOTE_ADDR'] ?? null;
            }

            $data = [
                'user_id'    => session()->get('user_id') ?? null,
                'username'   => session()->get('username') ?? null,
                'action'     => $action,
                'details'    => $details,
                'ip_address' => $ip,
                'mac_address' => $this->getMacAddress($ip),
                'created_at' => date('Y-m-d H:i:s'),
            ];

            // Attempt to insert into activity_logs; if table doesn't exist or insert fails, ignore the error.
            $this->db->table('activity_logs')->insert($data);
        } catch (\Throwable $e) {
            // ignore logging failures
        }
    }

    // Helper methods
    private function isNotAdmin(): bool
    {
        return !session()->get('user_logged_in') || session()->get('role') !== 'admin';
    }

    private function isUser(): bool
    {
        return session()->get('user_logged_in') && session()->get('role') === 'user';
    }

    private function denyAccess()
    {
        return redirect()->to('/tasks')->with('error', 'Unauthorized action.');
    }
}