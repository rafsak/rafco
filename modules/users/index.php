<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_permission('AUTH_MANAGE_USERS');

$users = db()->query('SELECT id, full_name, email, is_active, created_at FROM users ORDER BY created_at DESC')->fetchAll();

$pageTitle = 'Utilisateurs';
include __DIR__ . '/../../includes/layout/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Utilisateurs</h1>
    <a class="btn btn-primary" href="/modules/users/create.php">Nouvel utilisateur</a>
</div>
<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-striped mb-0">
            <thead>
            <tr><th>ID</th><th>Nom</th><th>Email</th><th>Statut</th><th>Rôles</th></tr>
            </thead>
            <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= (int) $u['id'] ?></td>
                    <td><?= e($u['full_name']) ?></td>
                    <td><?= e($u['email']) ?></td>
                    <td><?= ((int) $u['is_active'] === 1) ? 'Actif' : 'Inactif' ?></td>
                    <td>
                        <?php
                        $roles = array_column(user_roles((int) $u['id']), 'role_name');
                        echo e(implode(', ', $roles));
                        ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include __DIR__ . '/../../includes/layout/footer.php'; ?>
