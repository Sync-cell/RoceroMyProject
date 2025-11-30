<?php
// ...existing code...


/** @var array $ips */
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>IP Monitor</title>
    <meta name="csrf-name" content="<?= csrf_token() ?>">
    <meta name="csrf-hash" content="<?= csrf_hash() ?>">
    <style>
        body{font-family:Arial,Helvetica,sans-serif;margin:16px}
        table{width:100%;border-collapse:collapse;margin-top:12px}
        th,td{padding:8px;border:1px solid #ddd;text-align:left}
        th{background:#f4f4f4}
        .btn{display:inline-block;padding:6px 10px;text-decoration:none;border-radius:4px;font-size:14px}
        .btn-block{background:#c82333;color:#fff;border:none;cursor:pointer}
        .btn-unblock{background:#28a745;color:#fff;border:none;cursor:pointer}
        .btn-add{background:#007bff;color:#fff;border:none;cursor:pointer}
        form.inline{display:inline}
        .controls{margin-top:8px}
        input[type="text"]{padding:6px;margin-right:6px;border:1px solid #ccc;border-radius:4px}
    </style>
</head>
<body>
    <h1>IP Monitor</h1>

    <p>
        <a href="<?= base_url('admin/dashboard') ?>" class="btn">Back to Dashboard</a>
    </p>

    <div class="controls">
        <form action="<?= base_url('admin/ip_manual-add') ?>" method="post" class="inline" style="margin-bottom:12px;">
            <?= csrf_field() ?>
            <input type="text" name="ip_address" placeholder="IP address" required>
            <input type="text" name="username" placeholder="username (optional)">
            <button type="submit" class="btn btn-add">Add IP</button>
        </form>
    </div>

    <?php if (empty($ips) || !is_array($ips)): ?>
        <p>No IPs tracked yet.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>IP Address</th>
                    <th>User ID</th>
                    <th>Username</th>
                    <th>Hits</th>
                    <th>First Seen</th>
                    <th>Last Seen</th>
                    <th>Blocked</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($ips as $row): ?>
                <tr data-id="<?= (int)$row['id'] ?>">
                    <td><?= esc($row['id'] ?? '') ?></td>
                    <td><?= esc($row['ip_address'] ?? '') ?></td>
                    <td><?= esc($row['user_id'] ?? '') ?></td>
                    <td><?= esc($row['username'] ?? '') ?></td>
                    <td><?= esc($row['hits'] ?? '') ?></td>
                    <td><?= esc($row['first_seen'] ?? '') ?></td>
                    <td><?= esc($row['last_seen'] ?? '') ?></td>
                    <td class="blocked-cell"><?= ((int)($row['blocked'] ?? 0)) ? 'Yes' : 'No' ?></td>
                    <td>
                        <?php if ((int)($row['blocked'] ?? 0)): ?>
                            <form action="<?= base_url('admin/ip_unblock/' . (int)$row['id']) ?>" method="post" class="inline js-toggle-form" data-action="unblock" onsubmit="return confirm('Unblock this IP?')">
                                <?= csrf_field() ?>
                                <button class="btn btn-unblock" type="submit">Unblock</button>
                            </form>
                        <?php else: ?>
                            <form action="<?= base_url('admin/ip_block/' . (int)$row['id']) ?>" method="post" class="inline js-toggle-form" data-action="block" onsubmit="return confirm('Block this IP?')">
                                <?= csrf_field() ?>
                                <button class="btn btn-block" type="submit">Block</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

<script>
(function(){
    // Progressive enhancement: intercept block/unblock POSTs and use fetch (reload on success).
    const tokenName = document.querySelector('meta[name="csrf-name"]').getAttribute('content') || '';
    const tokenValue = document.querySelector('meta[name="csrf-hash"]').getAttribute('content') || '';

    document.addEventListener('submit', function(e){
        const form = e.target;
        if (!form.classList.contains('js-toggle-form')) return; // only handle toggle forms

        e.preventDefault();
        if (!confirm(form.getAttribute('onsubmit')?.replace('return ','').replace(/['"]/g,'') || 'Proceed?')) {
            return;
        }

        const action = form.getAttribute('action');
        const fd = new FormData();

        // append CSRF token (backend expects either POST param or header)
        if (tokenName && tokenValue) {
            fd.append(tokenName, tokenValue);
        }

        // Include any other inputs inside form (none required here)
        for (const el of form.querySelectorAll('input[name]:not([type="submit"])')) {
            fd.append(el.name, el.value);
        }

        fetch(action, {
            method: 'POST',
            body: fd,
            credentials: 'same-origin'
        }).then(async res => {
            if (res.ok) {
                // Reload to reflect changes and refresh CSRF token
                window.location.reload();
            } else {
                const text = await res.text();
                alert('Request failed: ' + res.status + '\nSee console for details.');
                console.error('IP toggle failed', res.status, text);
            }
        }).catch(err => {
            console.error(err);
            alert('Request error. Check console.');
        });
    }, false);
})();
</script>
</body>
</html>
