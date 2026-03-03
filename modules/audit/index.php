<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_permission('AUDIT_VIEW');

$sql = 'SELECT a.id, a.created_at, a.event_type, a.module_name, a.action_summary, a.ip_address, u.full_name
        FROM audit_logs a
        LEFT JOIN users u ON u.id = a.user_id
        ORDER BY a.created_at DESC
        LIMIT 200';
$logs = db()->query($sql)->fetchAll();

$pageTitle = 'Journal d\'audit';
include __DIR__ . '/../../includes/layout/header.php';
?>
<h1 class="h3 mb-3">Journal d'audit (non modifiable)</h1>
<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-sm table-striped mb-0">
            <thead>
            <tr><th>Date</th><th>Utilisateur</th><th>Module</th><th>Événement</th><th>Résumé</th><th>IP</th></tr>
            </thead>
            <tbody>
            <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?= e($log['created_at']) ?></td>
                    <td><?= e((string) ($log['full_name'] ?? 'Système')) ?></td>
                    <td><?= e($log['module_name']) ?></td>
                    <td><?= e($log['event_type']) ?></td>
                    <td><?= e($log['action_summary']) ?></td>
                    <td><?= e((string) $log['ip_address']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include __DIR__ . '/../../includes/layout/footer.php'; ?>
