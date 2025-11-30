<?php
/** @var array $ips */
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>IP Monitor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f8f9fa; padding: 20px; }
        h1 { color: #343a40; margin-bottom: 20px; }
        table th { background: #343a40; color: #fff; }
        table tr:hover { background: #e9ecef; }
        .btn-add { background-color: #007bff; color: #fff; }
        .btn-block { background-color: #dc3545; color: #fff; }
        .btn-unblock { background-color: #28a745; color: #fff; }
        .flash { padding: 10px; border-radius: 6px; margin-bottom: 12px; font-weight: bold; }
        .flash-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .flash-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
    <div class="container">
        <h1>IP Monitor</h1>

        <a href="<?= site_url('admin/dashboard') ?>" class="btn btn-secondary mb-3">Back to Dashboard</a>

        <!-- Flash Messages -->
        <?php if (session()->getFlashdata('message')): ?>
            <div class="flash flash-success"><?= esc(session()->getFlashdata('message')) ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="flash flash-error"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif; ?>

        <!-- Manual IP Add Form -->
        <form action="<?= site_url('admin/ip-manual-add') ?>" method="post" class="row g-2 mb-4">
            <?= csrf_field() ?>
            <div class="col-auto">
                <input type="text" name="ip_address" class="form-control" placeholder="IP address" required>
            </div>
            <div class="col-auto">
                <input type="text" name="username" class="form-control" placeholder="Username (optional)">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-add">Add / Update IP</button>
            </div>
        </form>

        <!-- IP Table -->
        <?php if (empty($ips) || !is_array($ips)): ?>
            <p class="text-muted">No IPs tracked yet.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-bordered align-middle">
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
                        <tr>
                            <td><?= esc($row['id'] ?? '') ?></td>
                            <td><?= esc($row['ip_address'] ?? '') ?></td>
                            <td><?= esc($row['user_id'] ?? '-') ?></td>
                            <td><?= esc($row['username'] ?? '-') ?></td>
                            <td><?= esc($row['hits'] ?? '0') ?></td>
                            <td><?= esc($row['first_seen'] ?? '-') ?></td>
                            <td><?= esc($row['last_seen'] ?? '-') ?></td>
                            <td><?= ((int)($row['blocked'] ?? 0)) ? '<span class="badge bg-danger">Yes</span>' : '<span class="badge bg-success">No</span>' ?></td>
                            <td>
                                <?php if ((int)($row['blocked'] ?? 0)): ?>
                                    <button class="btn btn-unblock btn-sm unblock-btn" data-id="<?= $row['id'] ?>">Unblock</button>
                                <?php else: ?>
                                    <button class="btn btn-block btn-sm block-btn" data-id="<?= $row['id'] ?>">Block</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <form id="blockForm" method="post" style="display:none;">
        <?= csrf_field() ?>
    </form>

    <script>
        document.querySelectorAll('.block-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                Swal.fire({
                    title: 'Block this IP?',
                    text: "The user will be blocked from accessing the system.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, block it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = document.getElementById('blockForm');
                        form.action = '<?= site_url('admin/ip-block') ?>/' + id;
                        form.submit();
                    }
                });
            });
        });

        document.querySelectorAll('.unblock-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                Swal.fire({
                    title: 'Unblock this IP?',
                    text: "The user will regain access to the system.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, unblock it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = document.getElementById('blockForm');
                        form.action = '<?= site_url('admin/ip-unblock') ?>/' + id;
                        form.submit();
                    }
                });
            });
        });
    </script>
</body>
</html>
