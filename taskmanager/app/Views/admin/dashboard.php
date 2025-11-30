<?php

$allTasks = $tasks ?? [];
$usersList = $users ?? [];

/** Fallback helpers when CI form helper not loaded */
if (!function_exists('old')) {
    function old(string $name, $default = '')
    {
        if (function_exists('set_value')) {
            $val = set_value($name);
            return $val === null ? $default : $val;
        }
        return $_POST[$name] ?? $default;
    }
}

if (!function_exists('select_attr')) {
    function select_attr(string $name, $value)
    {
        if (function_exists('set_select')) {
            return set_select($name, $value);
        }
        $post = $_POST[$name] ?? null;
        if (is_array($post)) {
            return in_array((string) $value, array_map('strval', $post), true) ? 'selected' : '';
        }
        return ((string) $post === (string) $value) ? 'selected' : '';
    }
}

/** Priority style helper (uses PHP 8 match) */
function priority_style($priority)
{
    $p = strtolower(trim((string) $priority));
    return match ($p) {
        'high' => ['bg' => '#ffebee', 'color' => '#c62828', 'label' => 'High'],
        'low'  => ['bg' => '#e8f5e9', 'color' => '#2e7d32', 'label' => 'Low'],
        default => ['bg' => '#fff8e1', 'color' => '#f57c00', 'label' => ($priority === '' ? 'Normal' : ucfirst($priority))],
    };
}

/** Normalize accepted users stored as string/array */
function safe_accepted_users($task)
{
    $accepted = $task['accepted_users'] ?? [];
    if (is_string($accepted)) {
        $decoded = json_decode($accepted, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $accepted = $decoded;
        }
    }
    if (!is_array($accepted)) {
        return [];
    }
    return array_values(array_filter(array_map(function ($u) {
        if (is_string($u)) return ['username' => $u];
        if (is_int($u)) return ['user_id' => $u, 'username' => (string) $u];
        if (is_array($u)) return $u;
        return null;
    }, $accepted)));
}

/** Safe maintenance status retrieval */
$maintenanceStatus = ['enabled' => false, 'admin_ips' => [], 'toggled_at' => null, 'toggled_by' => null];
try {
    if (class_exists(\App\Controllers\MaintenanceController::class)) {
        $mc = new \App\Controllers\MaintenanceController();
        $ms = $mc->getMaintenanceStatus();
        if (is_array($ms)) {
            $maintenanceStatus = array_merge($maintenanceStatus, $ms);
        }
    }
} catch (\Throwable $e) {
    // Silent fallback to disabled maintenance
}

$isMaintenanceEnabled = !empty($maintenanceStatus['enabled']);
$adminIps = is_array($maintenanceStatus['admin_ips'] ?? null) ? $maintenanceStatus['admin_ips'] : [];

/** current IP helper */
function current_ip(): string
{
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return trim($_SERVER['HTTP_CLIENT_IP']);
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
    }
    return trim($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
}
$currentIp = current_ip();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap');
        body { margin:0; padding:0; font-family:Inter, sans-serif; background:linear-gradient(135deg,#f6d365,#fda085); display:flex; justify-content:center; padding:36px 12px; }
        .dashboard-container{ width:95%; max-width:1200px; background:#fff; border-radius:12px; padding:28px; box-shadow:0 10px 25px rgba(0,0,0,0.12); }
        h1{ margin:0 0 10px 0; font-size:26px; color:#2d3436 }
        h3{ margin:16px 0 12px 0; font-size:20px; color:#2d3436 }
        .header-actions{ display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:18px; flex-wrap:wrap; }
        .user-info p{ margin:0;color:#666 }
        .alert{ padding:12px;border-radius:8px;margin-bottom:14px;font-weight:600 }
        .alert-success{ background:#d4edda;color:#155724;border:1px solid #c3e6cb }
        .alert-error{ background:#f8d7da;color:#721c24;border:1px solid #f5c6cb }
        form .row{ display:grid; grid-template-columns: 1fr 1fr; gap:12px }
        label{ display:block; font-weight:600; margin-bottom:6px; color:#333 }
        input,select,textarea{ width:100%; padding:10px; border-radius:8px; border:1px solid #dfe6ee; font-family:inherit; }
        textarea{ min-height:86px; resize:vertical }
        .btn{ display:inline-block; padding:10px 14px; border-radius:8px; background:#0984e3; color:#fff; text-decoration:none; font-weight:700; border:none; cursor:pointer; transition:all 0.3s ease; }
        .btn-success{ background:#00b894 }
        .btn-danger{ background:#d63031 }
        .btn:disabled{ background:#bfc9d6; cursor:not-allowed; transform:none; }
        .section{ margin-top:20px }
        table{ width:100%; border-collapse:collapse; margin-top:12px }
        th,td{ padding:12px; border-bottom:1px solid #edf2f7; text-align:left; vertical-align:middle }
        thead th{ background:#0984e3; color:#fff; position:sticky; top:0 }
        .status-badge{ padding:6px 10px; border-radius:20px; font-weight:700; font-size:13px; display:inline-block }
        .status-pending{ background:#fff3cd; color:#856404; border:1px solid #ffc107 }
        .status-progress{ background:#cfe2ff; color:#084298; border:1px solid #0d6efd }
        .status-completed{ background:#d1e7dd; color:#0f5132; border:1px solid #198754 }
        .accepted-list{ background:#e8f5e9; padding:8px; border-radius:6px; border-left:3px solid #00b894 }
        .no-acceptance{ color:#c0392b; font-style:italic; background:#fff1f0; padding:8px; border-radius:6px }
        .btn-wrapper{ display:flex; gap:8px; flex-wrap:wrap }
        .maintenance-section{ background:#f8f9fa; padding:20px; border-radius:12px; border:2px solid #667eea; margin-bottom:20px; }
        .maintenance-status-badge{ padding:6px 12px; border-radius:6px; display:inline-block; font-weight:600; }
        .maintenance-enabled{ background:#ffcdd2; color:#c62828; }
        .maintenance-disabled{ background:#d4edda; color:#155724; }
        .ip-info-box{ background:#e3f2fd; padding:12px; border-radius:8px; margin-bottom:16px; border-left:4px solid #2196f3; }
        .tip-box{ background:#fff3cd; padding:12px; border-radius:8px; margin-top:16px; border:1px solid #ffc107; font-size:13px; }
        .no-data{ text-align:center; color:#999; padding:20px !important; }
        @media (max-width:800px){ form .row{ grid-template-columns:1fr } table th, table td{ font-size:13px } }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<div class="dashboard-container">

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success">✓ <?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-error">✗ <?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <div class="header-actions">
        <div class="user-info">
            <h1>Welcome, <?= esc(session()->get('username') ?? 'Admin') ?>!</h1>
            <p>Role: <strong><?= esc(session()->get('role') ?? 'admin') ?></strong></p>
        </div>
        <div>
            <a href="<?= base_url('admin/ip_monitor') ?>" class="btn" title="IP Monitor" style="margin-left:8px;background:#6f42c1">IP Monitor</a>
             <a href="<?= base_url('admin/activity') ?>" class="btn" title="Activity Logs">Activity Logs</a>
            <form action="<?= base_url('admin/logout') ?>" method="post" style="display:inline">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-danger" data-swal="confirm" data-title="Logout" data-text="You will be logged out. Continue?">Logout</button>
            </form>
        </div>
    </div>

    <!-- Maintenance Mode Section -->
    <div class="maintenance-section">
        <h3 style="margin-top:0">🔧 Maintenance Mode Control</h3>

        <div style="display:flex;gap:12px;align-items:center;margin-bottom:20px;flex-wrap:wrap">
            <div style="flex:1;min-width:250px">
                <p style="margin:0 0 8px;color:#555">
                    <strong>Status:</strong>
                    <span class="maintenance-status-badge <?= $isMaintenanceEnabled ? 'maintenance-enabled' : 'maintenance-disabled' ?>">
                        <?= $isMaintenanceEnabled ? '🔴 ENABLED' : '🟢 DISABLED' ?>
                    </span>
                </p>
                <?php if ($isMaintenanceEnabled && !empty($maintenanceStatus['toggled_at'])): ?>
                    <p style="margin:4px 0;font-size:13px;color:#666">
                        Started: <?= esc($maintenanceStatus['toggled_at']) ?> by <?= esc($maintenanceStatus['toggled_by']) ?>
                    </p>
                <?php endif; ?>
            </div>

            <form action="<?= base_url('maintenance/toggle') ?>" method="post" style="display:inline">
                <?= csrf_field() ?>
                <button type="submit" class="btn <?= $isMaintenanceEnabled ? 'btn-danger' : 'btn-success' ?>"
                        data-swal="confirm"
                        data-title="<?= $isMaintenanceEnabled ? 'Disable' : 'Enable' ?> Maintenance Mode"
                        data-text="<?= $isMaintenanceEnabled ? 'Disable maintenance for all users?' : 'Enable maintenance? Only whitelisted IPs can access.' ?>">
                    <?= $isMaintenanceEnabled ? '⏹️ Disable Maintenance' : '▶️ Enable Maintenance' ?>
                </button>
            </form>
        </div>

        <hr>

        <h4 style="margin-top:0">📋 IP Whitelist Management</h4>
        <p style="font-size:13px;color:#666;margin-top:0">Only whitelisted IP addresses can access the system during maintenance.</p>

        <div class="ip-info-box">
            <strong style="color:#1565c0">Your Current IP:</strong>
            <code><?= esc($currentIp) ?></code>
            <?php if (in_array($currentIp, $adminIps, true)): ?>
                <span style="background:#c8e6c9;color:#2e7d32;padding:4px 8px;border-radius:4px;font-size:12px;margin-left:8px">✓ Whitelisted</span>
            <?php endif; ?>
        </div>

        <form action="<?= base_url('maintenance/add-ip') ?>" method="post" style="margin-bottom:20px;display:flex;gap:8px;flex-wrap:wrap">
            <?= csrf_field() ?>
            <input type="text" name="whitelist_ip" placeholder="Enter IP address (e.g., 192.168.1.1)"
                   style="flex:1;min-width:250px;padding:10px;border:1px solid #ddd;border-radius:6px" required>
            <button type="submit" class="btn btn-success">Add IP</button>
        </form>

        <h5 style="margin-top:16px;margin-bottom:8px">Whitelisted IP Addresses (<?= (int) count($adminIps) ?>)</h5>
        <div style="overflow-x:auto;border:1px solid #ddd;border-radius:8px">
            <table>
                <thead>
                    <tr>
                        <th>IP Address</th>
                        <th>Your IP</th>
                        <th style="text-align:center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($adminIps)): ?>
                        <tr>
                            <td colspan="3" style="text-align:center;color:#999;padding:20px">No whitelisted IPs yet</td>
                        </tr>
                    <?php else: foreach ($adminIps as $ip): ?>
                        <tr>
                            <td><code style="background:#f5f5f5;padding:6px 10px;border-radius:4px;font-family:monospace"><?= esc($ip) ?></code></td>
                            <td style="text-align:center"><?= $ip === $currentIp ? '<span style="background:#c8e6c9;color:#2e7d32;padding:4px 8px;border-radius:4px;font-size:12px">✓ This Device</span>' : '' ?></td>
                            <td style="text-align:center">
                                <?php if ($ip !== $currentIp): ?>
                                    <form action="<?= base_url('maintenance/remove-ip') ?>" method="post" style="display:inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="remove_ip" value="<?= esc($ip) ?>">
                                        <button type="submit" class="btn" style="background:#d63031;padding:6px 10px;font-size:12px"
                                                data-swal="confirm" data-title="Remove IP" data-text="Remove <?= esc($ip) ?> from whitelist?">Remove</button>
                                    </form>
                                <?php else: ?>
                                    <span style="color:#999;font-size:12px">Cannot remove (current device)</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>

        <div class="tip-box">
            <strong>💡 Tip:</strong> Your current IP is automatically added when you first toggle maintenance mode. You can add additional IPs for other admin devices.
        </div>
    </div>

    <!-- Statistics -->
    <div class="section">
        <h3>📊 Task Statistics</h3>
        <?php
            $completedCount = count(array_filter($allTasks, fn($t) => strtolower($t['status'] ?? '') === 'completed'));
            $inProgressCount = count(array_filter($allTasks, function($t){
                $status = strtolower($t['status'] ?? '');
                $accepted = !empty($t['accepted_users']);
                return $status === 'in progress' || ($accepted && $status !== 'completed');
            }));
            $pendingCount = count(array_filter($allTasks, fn($t) => strtolower($t['status'] ?? '') === 'pending'));
        ?>
        <div style="display:flex;gap:12px;flex-wrap:wrap">
            <div style="background:#f5f7fa;padding:12px;border-radius:8px;flex:1;min-width:160px;text-align:center">
                <div style="font-weight:700">Total</div>
                <div style="font-size:20px"><?= (int) count($allTasks) ?></div>
            </div>
            <div style="background:#f5f7fa;padding:12px;border-radius:8px;flex:1;min-width:160px;text-align:center">
                <div style="font-weight:700">Completed</div>
                <div style="font-size:20px;color:#00b894"><?= (int) $completedCount ?></div>
            </div>
            <div style="background:#f5f7fa;padding:12px;border-radius:8px;flex:1;min-width:160px;text-align:center">
                <div style="font-weight:700">In Progress</div>
                <div style="font-size:20px;color:#0984e3"><?= (int) $inProgressCount ?></div>
            </div>
            <div style="background:#f5f7fa;padding:12px;border-radius:8px;flex:1;min-width:160px;text-align:center">
                <div style="font-weight:700">Pending</div>
                <div style="font-size:20px;color:#f39c12"><?= (int) $pendingCount ?></div>
            </div>
        </div>
    </div>

    <!-- Create Task Form -->
    <div class="section">
        <h3>➕ Create New Task</h3>
        <form action="<?= base_url('task/store') ?>" method="post" data-swal="submit-confirm" data-title="Create task" data-text="Create this task now?">
            <?= csrf_field() ?>
            <div class="row">
                <div>
                    <label for="title">Title</label>
                    <input id="title" name="title" type="text" value="<?= esc(old('title')) ?>" required>
                </div>
                <div>
                    <label for="priority">Priority</label>
                    <select id="priority" name="priority" required>
                        <option value="">-- Select Priority --</option>
                        <option value="Low" <?= select_attr('priority','Low') ?>>Low</option>
                        <option value="Normal" <?= select_attr('priority','Normal') ?>>Normal</option>
                        <option value="High" <?= select_attr('priority','High') ?>>High</option>
                    </select>
                </div>
            </div>

            <label for="description">Description</label>
            <textarea id="description" name="description" required><?= esc(old('description')) ?></textarea>

            <div class="row">
                <div>
                    <label for="deadline">Deadline</label>
                    <input id="deadline" name="deadline" type="date" value="<?= esc(old('deadline')) ?>" required>
                </div>
                <div>
                    <label for="status">Status</label>
                    <select id="status" name="status" required>
                        <option value="">-- Select Status --</option>
                        <option value="Pending" <?= select_attr('status','Pending') ?>>Pending</option>
                        <option value="In Progress" <?= select_attr('status','In Progress') ?>>In Progress</option>
                        <option value="Completed" <?= select_attr('status','Completed') ?>>Completed</option>
                    </select>
                </div>
            </div>

            <label for="assigned_to">Assign To (User)</label>
            <select id="assigned_to" name="assigned_to" required>
                <option value="">-- Select a User --</option>
                <?php if (!empty($usersList)): foreach ($usersList as $u): ?>
                    <option value="<?= esc($u['id']) ?>" <?= select_attr('assigned_to', $u['id']) ?>><?= esc($u['username']) ?></option>
                <?php endforeach; else: ?>
                    <option value="" disabled>No users registered</option>
                <?php endif; ?>
            </select>

            <div style="margin-top:12px">
                <button class="btn btn-success" type="submit">Create Task</button>
            </div>
        </form>
    </div>

    <!-- Active Tasks -->
    <div class="section">
        <h3>⚡ Active Tasks</h3>
        <div style="overflow-x:auto">
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Priority</th>
                        <th>Deadline</th>
                        <th>Status</th>
                        <th>Assigned To</th>
                        <th>Accepted By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    $activeTasks = array_filter($allTasks, fn($t) => strtolower($t['status'] ?? '') !== 'completed');
                    if (empty($activeTasks)):
                ?>
                    <tr><td colspan="7" class="no-data">No active tasks</td></tr>
                <?php else: foreach ($activeTasks as $task):
                    $acceptedUsers = safe_accepted_users($task);
                    $isAccepted = !empty($acceptedUsers);
                    $rawStatus = strtolower($task['status'] ?? 'pending');
                    if ($isAccepted && $rawStatus !== 'completed') { $displayStatus = 'In Progress'; $statusClass = 'status-progress'; }
                    elseif ($rawStatus === 'pending') { $displayStatus = 'Pending'; $statusClass = 'status-pending'; }
                    else { $displayStatus = ucfirst($rawStatus); $statusClass = ($rawStatus === 'completed' ? 'status-completed':'status-pending'); }
                    $ps = priority_style($task['priority'] ?? 'Normal');
                ?>
                    <tr>
                        <td>
                            <strong><?= esc($task['title'] ?? 'Untitled') ?></strong>
                            <div style="color:#666;margin-top:6px;font-size:13px"><?= esc($task['description'] ?? '') ?></div>
                        </td>
                        <td>
                            <span style="display:inline-block;padding:6px 8px;border-radius:6px;background:<?= esc($ps['bg']) ?>;color:<?= esc($ps['color']) ?>;font-weight:700">
                                <?= esc($ps['label']) ?>
                            </span>
                        </td>
                        <td><?= esc($task['deadline'] ?? 'N/A') ?></td>
                        <td><span class="status-badge <?= esc($statusClass) ?>"><?= esc($displayStatus) ?></span></td>
                        <td><?= esc($task['assigned_username'] ?? 'Unassigned') ?></td>
                        <td>
                            <?php if ($isAccepted): ?>
                                <div class="accepted-list">
                                    <strong>Accepted by</strong>
                                    <ul>
                                        <?php foreach ($acceptedUsers as $au): ?>
                                            <li><?= esc($au['username'] ?? ($au['user_id'] ?? 'User')) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php else: ?>
                                <div class="no-acceptance">Waiting for acceptance</div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-wrapper">
                                <?php if ($isAccepted): ?>
                                    <a href="<?= base_url('task/done/' . ($task['id'] ?? '')) ?>" class="btn btn-success" data-swal="confirm" data-title="Mark as completed" data-text="Mark this task as completed?">Mark Done</a>
                                <?php else: ?>
                                    <button class="btn" disabled>Mark Done</button>
                                <?php endif; ?>
                                <a href="<?= base_url('task/edit/' . ($task['id'] ?? '')) ?>" class="btn">Edit</a>
                                <a href="<?= base_url('task/delete/' . ($task['id'] ?? '')) ?>" class="btn btn-danger" data-swal="confirm" data-title="Delete task" data-text="Delete this task permanently?">Delete</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Completed Tasks -->
    <div id="completed-section" class="section">
        <h3>✅ Completed Tasks</h3>
        <div style="margin-bottom:10px;display:flex;justify-content:flex-end">
            <?php if (!empty($completedCount)): ?>
                <a href="<?= base_url('task/export-csv') ?>" class="btn" data-swal="confirm" data-title="Export CSV" data-text="Export completed tasks to CSV?">📥 Export CSV</a>
            <?php else: ?>
                <button class="btn" disabled>📥 Export CSV</button>
            <?php endif; ?>
        </div>

        <div style="overflow-x:auto">
            <table>
                <thead>
                    <tr><th>Title</th><th>Priority</th><th>Deadline</th><th>Assigned To</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php
                        $completedTasks = array_filter($allTasks, fn($t) => strtolower($t['status'] ?? '') === 'completed');
                        if (empty($completedTasks)):
                    ?>
                        <tr><td colspan="6" class="no-data">No completed tasks yet</td></tr>
                    <?php else: foreach ($completedTasks as $task):
                        $ps = priority_style($task['priority'] ?? 'Normal');
                    ?>
                        <tr>
                            <td><strong><?= esc($task['title'] ?? 'Untitled') ?></strong></td>
                            <td><span style="display:inline-block;padding:6px 8px;border-radius:6px;background:<?= esc($ps['bg']) ?>;color:<?= esc($ps['color']) ?>;font-weight:700"><?= esc($ps['label']) ?></span></td>
                            <td><?= esc($task['deadline'] ?? 'N/A') ?></td>
                            <td><?= esc($task['assigned_username'] ?? 'Unassigned') ?></td>
                            <td><span class="status-badge status-completed">Completed</span></td>
                            <td><a href="<?= base_url('task/delete/' . ($task['id'] ?? '')) ?>" class="btn btn-danger" data-swal="confirm" data-title="Delete task" data-text="Delete this task permanently?">Delete</a></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
    // SweetAlert handlers
    document.addEventListener('click', function(e){
        const btn = e.target.closest('[data-swal="confirm"]');
        if (!btn) return;
        e.preventDefault();
        const href = btn.getAttribute('href') || null;
        const title = btn.dataset.title || 'Are you sure?';
        const text = btn.dataset.text || '';
        Swal.fire({
            title: title,
            text: text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes',
            cancelButtonText: 'Cancel',
            reverseButtons: true
        }).then(function(result){
            if (result.isConfirmed) {
                if (href) {
                    window.location.href = href;
                } else {
                    const form = btn.closest('form');
                    if (form) form.submit();
                }
            }
        });
    });

    document.querySelectorAll('form[data-swal="submit-confirm"]').forEach(function(form){
        form.addEventListener('submit', function(e){
            e.preventDefault();
            const title = form.dataset.title || 'Confirm';
            const text = form.dataset.text || '';
            Swal.fire({
                title: title,
                text: text,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, proceed',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then(function(result){
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });

    (function(){
        const success = document.querySelector('.alert-success');
        const error = document.querySelector('.alert-error');
        if (success) {
            Swal.fire({ icon:'success', title: success.textContent.trim(), timer:2000, showConfirmButton:false, toast:true, position:'top-end' });
        } else if (error) {
            Swal.fire({ icon:'error', title: error.textContent.trim(), timer:2500, showConfirmButton:false, toast:true, position:'top-end' });
        }
    })();
</script>
</body>
</html>