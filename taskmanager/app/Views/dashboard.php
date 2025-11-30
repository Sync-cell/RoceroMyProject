<?php

$allTasks = $tasks ?? [];

/**
 * Normalize priority value and return canonical label.
 */
if (!function_exists('normalize_priority')) {
    function normalize_priority($p): string {
        $p = (string) ($p ?? '');
        $p = trim(strtolower($p));
        if ($p === 'high' || $p === 'h' || $p === '3') return 'High';
        if ($p === 'low' || $p === 'l' || $p === '1') return 'Low';
        return 'Normal';
    }
}

if (!function_exists('priority_class')) {
    function priority_class(string $label): string {
        return match(strtolower($label)) {
            'high' => 'priority-high',
            'low'  => 'priority-low',
            default => 'priority-normal',
        };
    }
}

/** Safely decode accepted_users when stored as JSON */
if (!function_exists('safe_accepted_users')) {
    function safe_accepted_users($task): array {
        $au = $task['accepted_users'] ?? [];
        if (is_string($au)) {
            $decoded = json_decode($au, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) $au = $decoded;
        }
        return is_array($au) ? $au : [];
    }
}

// Precompute priority counts
$priorityCounts = ['High' => 0, 'Normal' => 0, 'Low' => 0];
foreach ($allTasks as $t) {
    $label = normalize_priority($t['priority'] ?? '');
    if (isset($priorityCounts[$label])) $priorityCounts[$label]++;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard</title>
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap');
        body{margin:0;font-family:Inter,system-ui,Arial;background:linear-gradient(135deg,#f6d365 0%,#fda085 100%);display:flex;justify-content:center;padding:28px}
        .dashboard-container{width:100%;max-width:1000px;background:#fff;padding:28px;border-radius:12px;box-shadow:0 10px 25px rgba(0,0,0,0.12)}
        h1,p{text-align:center;margin:0 0 10px}
        p{color:#636e72}
        .btn,.accept-btn{padding:8px 14px;border-radius:8px;border:0;font-weight:600;cursor:pointer;text-decoration:none}
        .btn{background:#0984e3;color:#fff}
        .accept-btn{background:#00b894;color:#fff}
        .accepted-badge{background:#c8e6c9;color:#2e7d32;padding:6px 10px;border-radius:6px;font-weight:700;border:2px solid #2e7d32}
        table{width:100%;border-collapse:collapse;margin-top:20px}
        th,td{padding:10px;border-bottom:1px solid #eee;text-align:left;vertical-align:top}
        th{background:#0984e3;color:#fff}
        .desc{color:#666;font-size:13px;margin-top:6px;display:block}
        .priority-high{background:#ffcdd2;color:#bf1e2e;padding:6px 8px;border-radius:6px;font-weight:700}
        .priority-normal{background:#fff9c4;color:#f57f17;padding:6px 8px;border-radius:6px;font-weight:700}
        .priority-low{background:#c8e6c9;color:#2e7d32;padding:6px 8px;border-radius:6px;font-weight:700}
        .status-pending{background:#fff0ff;color:#6a1b9a;padding:6px 8px;border-radius:6px;font-weight:700}
        .status-inprogress{background:#fff9c4;color:#f57f17;padding:6px 8px;border-radius:6px;font-weight:700}
        .status-completed{background:#e8f7ec;color:#2e7d32;padding:6px 8px;border-radius:6px;font-weight:700}
        .stats{display:flex;gap:12px;margin:18px 0}
        .stat-card{flex:1;background:#f5f5f5;padding:12px;border-radius:8px;text-align:center}
        .priority-legend{display:flex;gap:8px;justify-content:center;margin-top:8px}
        .assigned-badge{background:#bbdefb;color:#1565c0;padding:4px 8px;border-radius:4px;font-weight:600}
    </style>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<div class="dashboard-container">

    <?php if (session()->getFlashdata('success')): ?>
        <div style="margin-bottom:12px;color:#155724;font-weight:700">✓ <?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div style="margin-bottom:12px;color:#721c24;font-weight:700">✗ <?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <h1>Welcome, <?= esc(session()->get('username') ?? 'User') ?>!</h1>
    <p>You are logged in as <strong><?= esc(session()->get('role') ?? 'user') ?></strong></p>

    <div style="text-align:center;margin:14px 0">
        <button class="btn" data-swal="confirm" data-title="Logout" data-text="You will be logged out. Continue?" data-href="<?= base_url('admin/logout') ?>">Logout</button>
    </div>

    <!-- Task Counters -->
    <?php
    $acceptedTasks = 0;
    foreach ($allTasks as $task) {
        foreach (safe_accepted_users($task) as $user) {
            if (($user['user_id'] ?? null) == session()->get('user_id')) { $acceptedTasks++; break; }
        }
    }
    ?>
    <div class="stats">
        <div class="stat-card"><div style="font-size:13px;color:#333">Tasks Assigned</div><div style="font-size:20px;font-weight:800"><?= count($allTasks) ?></div></div>
        <div class="stat-card"><div style="font-size:13px;color:#333">You Accepted</div><div style="font-size:20px;font-weight:800"><?= $acceptedTasks ?></div></div>
        <div class="stat-card"><div style="font-size:13px;color:#333">Not Yet Accepted</div><div style="font-size:20px;font-weight:800"><?= count($allTasks) - $acceptedTasks ?></div></div>
    </div>

    <div class="priority-legend" aria-hidden="false">
        <div style="display:inline-flex;align-items:center;gap:8px;padding:6px 10px;border-radius:8px;background:#fff;border:1px solid #eef4ff">High: <?= $priorityCounts['High'] ?></div>
        <div style="display:inline-flex;align-items:center;gap:8px;padding:6px 10px;border-radius:8px;background:#fff;border:1px solid #eef4ff">Normal: <?= $priorityCounts['Normal'] ?></div>
        <div style="display:inline-flex;align-items:center;gap:8px;padding:6px 10px;border-radius:8px;background:#fff;border:1px solid #eef4ff">Low: <?= $priorityCounts['Low'] ?></div>
    </div>

    <!-- Assigned Tasks (includes description column) -->
    <div style="margin-top:20px">
        <h3 style="margin:0 0 8px">📋 Your Assigned Tasks</h3>
        <?php if (empty($allTasks)): ?>
            <div style="padding:20px;color:#999">📭 No tasks assigned yet.</div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Priority</th>
                        <th>Deadline</th>
                        <th>Status</th>
                        <th>Assigned To</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($allTasks as $task): ?>
                    <?php
                        $priorityLabel = normalize_priority($task['priority'] ?? '');
                        $priorityClass = priority_class($priorityLabel);
                        $accepted = false;
                        foreach (safe_accepted_users($task) as $u) {
                            if (($u['user_id'] ?? null) == session()->get('user_id')) { $accepted = true; break; }
                        }
                        $statusRaw = $task['status'] ?? 'Pending';
                        $statusClass = 'status-' . strtolower(str_replace(' ', '-', $statusRaw));
                    ?>
                    <tr>
                        <td><strong><?= esc($task['title'] ?? 'Untitled') ?></strong></td>
                        <td><span class="desc"><?= esc($task['description'] ?? '—') ?></span></td>
                        <td><span class="<?= $priorityClass ?>"><?= esc($priorityLabel) ?></span></td>
                        <td><?= esc($task['deadline'] ?? 'N/A') ?></td>
                        <td><span class="<?= $statusClass ?>"><?= esc($statusRaw) ?></span></td>
                        <td><span class="assigned-badge">👤 <?= esc($task['assigned_username'] ?? 'Unknown') ?></span></td>
                        <td>
                            <?php if ($accepted): ?>
                                <span class="accepted-badge">✓ Accepted</span>
                            <?php else: ?>
                                <button class="accept-btn" data-swal="confirm" data-title="Accept task" data-text="Do you want to accept this task?" data-href="<?= base_url('task/accept/' . ($task['id'] ?? '')) ?>">Accept</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Accepted Tasks (includes description) -->
    <div style="margin-top:20px">
        <h3 style="margin:0 0 8px">✅ Your Accepted Tasks</h3>
        <?php
            $acceptedTasksList = array_filter($allTasks, function($task){
                foreach (safe_accepted_users($task) as $u) {
                    if (($u['user_id'] ?? null) == session()->get('user_id')) return true;
                }
                return false;
            });
        ?>
        <?php if (empty($acceptedTasksList)): ?>
            <div style="padding:20px;color:#999">📋 You haven't accepted any tasks yet.</div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Priority</th>
                        <th>Deadline</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($acceptedTasksList as $task): 
                    $priorityLabel = normalize_priority($task['priority'] ?? '');
                    $priorityClass = priority_class($priorityLabel);
                    $statusRaw = $task['status'] ?? 'In Progress';
                    $statusClass = 'status-' . strtolower(str_replace(' ', '-', $statusRaw));
                ?>
                    <tr>
                        <td><strong><?= esc($task['title'] ?? 'Untitled') ?></strong></td>
                        <td><span class="desc"><?= esc($task['description'] ?? '—') ?></span></td>
                        <td><span class="<?= $priorityClass ?>"><?= esc($priorityLabel) ?></span></td>
                        <td><?= esc($task['deadline'] ?? 'N/A') ?></td>
                        <td><span class="<?= $statusClass ?>"><?= esc($statusRaw) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

</div>

<script>
document.addEventListener('click', function(e){
    const btn = e.target.closest('[data-swal="confirm"]');
    if (!btn) return;
    e.preventDefault();
    const title = btn.dataset.title || 'Are you sure?';
    const text  = btn.dataset.text  || '';
    const href  = btn.dataset.href || btn.getAttribute('href') || null;
    Swal.fire({
        title: title,
        text: text,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes',
        cancelButtonText: 'Cancel'
    }).then((res) => {
        if (res.isConfirmed && href) window.location.href = href;
    });
});

// toast for flash messages
(function(){
    const success = <?= json_encode(session()->getFlashdata('success') ?? '') ?>;
    const error   = <?= json_encode(session()->getFlashdata('error') ?? '') ?>;
    if (success) {
        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: success, showConfirmButton: false, timer: 2200 });
    } else if (error) {
        Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: error, showConfirmButton: false, timer: 2800 });
    }
})();
</script>
</body>
</html>