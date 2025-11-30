<?php

$logs = $logs ?? [];
$filterRole = $filterRole ?? '';
$searchQ = $searchQ ?? '';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Activity Logs</title>
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <style>
        :root{
            --bg:#f4f6fb;
            --card:#ffffff;
            --accent:#2563eb;
            --muted:#6b7280;
            --border:#e6e9ef;
            --success:#10b981;
        }
        *{box-sizing:border-box}
        body{font-family:system-ui,-apple-system,Segoe UI,Roboto,"Helvetica Neue",Arial;color:#111;background:var(--bg);margin:0;padding:28px}
        .wrap{max-width:1200px;margin:0 auto}
        .card{background:var(--card);border-radius:10px;padding:18px;box-shadow:0 8px 30px rgba(15,23,42,0.06);border:1px solid var(--border)}
        header{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:14px}
        header h1{font-size:20px;margin:0}
        .actions{display:flex;gap:8px;align-items:center}
        .btn{
            display:inline-flex;align-items:center;gap:8px;padding:8px 12px;border-radius:8px;text-decoration:none;border:0;cursor:pointer;
            background:var(--accent);color:#fff;font-weight:600;font-size:13px;
        }
        .btn.secondary{background:#f3f4f6;color:#111;border:1px solid var(--border);font-weight:600}
        .controls{display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin-bottom:16px}
        .controls .filter, .controls .search{display:flex;gap:8px;align-items:center}
        select,input[type="text"]{padding:8px 10px;border-radius:8px;border:1px solid var(--border);min-height:40px;background:#fff}
        form.inline{display:flex;gap:8px;align-items:center}
        .meta{color:var(--muted);font-size:13px;margin-bottom:12px}
        .table-wrap{overflow:auto;border-radius:8px;border:1px solid var(--border)}
        table{width:100%;border-collapse:collapse;min-width:900px}
        thead th{background:linear-gradient(180deg,rgba(37,99,235,0.95),rgba(37,99,235,0.95));color:#fff;padding:12px 14px;text-align:left;position:sticky;top:0;font-weight:600}
        tbody td{padding:12px 14px;border-bottom:1px solid #f1f3f6;vertical-align:middle;font-size:14px}
        tbody tr:nth-child(even){background:#fbfdff}
        .small{font-size:13px;color:var(--muted)}
        .pill{display:inline-block;padding:6px 10px;border-radius:999px;font-weight:600;font-size:12px}
        .role-admin{background:#eef2ff;color:#1e40af}
        .role-user{background:#f0fdf4;color:#065f46}
        .action-col{white-space:nowrap;width:180px}
        @media (max-width:900px){
            header{flex-direction:column;align-items:flex-start}
            .action-col{width:auto}
        }
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">
        <header>
            <div>
                <h1>Activity Logs</h1>
                <div class="meta">All recorded user actions. Use the filters to narrow results.</div>
            </div>
            <div class="actions">
                <a href="<?= base_url('admin/dashboard') ?>" class="btn secondary">Back to Dashboard</a>
                <a href="<?= current_url(); ?>" class="btn">Refresh</a>
            </div>
        </header>

        <div class="controls">
            <form method="get" action="<?= current_url() ?>" class="inline">
                <div class="filter">
                    <label for="role" class="small">Role</label>
                    <select id="role" name="role" aria-label="Filter by role">
                        <option value="" <?= $filterRole === '' ? 'selected' : '' ?>>All</option>
                        <option value="admin" <?= $filterRole === 'admin' ? 'selected' : '' ?>>Admin</option>
                        <option value="user" <?= $filterRole === 'user' ? 'selected' : '' ?>>User</option>
                    </select>
                </div>

                <div class="search">
                    <label for="q" class="small">Search</label>
                    <input id="q" name="q" type="text" value="<?= esc($searchQ) ?>" placeholder="username or details">
                </div>

                <div style="display:flex;gap:8px;align-items:center">
                    <button type="submit" class="btn">Apply</button>
                   
                </div>
            </form>
        </div>

        <div class="meta">
            Showing <?= (int) count($logs) ?> log<?= count($logs) === 1 ? '' : 's' ?>.
            <?php if ($filterRole !== ''): ?> Filtered by role: <strong><?= esc($filterRole) ?></strong>.<?php endif; ?>
            <?php if ($searchQ !== ''): ?> Search: <strong><?= esc($searchQ) ?></strong>.<?php endif; ?>
        </div>

        <?php if (empty($logs)): ?>
            <div class="card" style="padding:18px;text-align:center;background:#fffbe6;border:1px solid #fff1b8;color:#7c6b00">
                No logs found.
            </div>
        <?php else: ?>
            <div class="table-wrap" role="region" aria-label="Activity logs table">
                <table>
                    <thead>
                        <tr>
                            <th style="width:56px">#</th>
                            <th style="width:160px">When</th>
                            <th>User</th>
                            <th style="width:120px">Role</th>
                            <th style="width:160px">Action</th>
                            <th>Details</th>
                            <th style="width:140px">IP</th>
                            <th style="width:140px">MAC</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($logs as $i => $l): ?>
                        <tr>
                            <td class="small"><?= esc($l['id'] ?? ($i+1)) ?></td>
                            <td class="small"><?= esc($l['created_at'] ?? '') ?></td>
                            <td>
                                <div style="font-weight:600"><?= esc($l['username'] ?? ('uid:' . ($l['user_id'] ?? ''))) ?></div>
                                <div class="small"><?= esc($l['user_id'] ?? '') ?></div>
                            </td>
                            <td>
                                <?php if (!empty($l['user_role'])): ?>
                                    <span class="pill <?= $l['user_role'] === 'admin' ? 'role-admin' : 'role-user' ?>"><?= esc(ucfirst($l['user_role'])) ?></span>
                                <?php else: ?>
                                    <span class="small">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="small"><?= esc($l['action'] ?? '') ?></td>
                            <td class="small"><?= esc($l['details'] ?? '') ?></td>
                            <td class="small"><?= esc($l['ip_address'] ?? '') ?></td>
                            <td class="small"><?= esc($l['mac_address'] ?? '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>