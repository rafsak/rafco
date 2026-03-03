<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_permission('AUTH_MANAGE_ROLES');

$roles = db()->query('SELECT id, role_key, role_name, role_description FROM roles ORDER BY role_name')->fetchAll();

$pageTitle = 'Rôles et permissions';
include __DIR__ . '/../../includes/layout/header.php';
?>
<h1 class="h3 mb-3">Rôles & Permissions</h1>
<?php foreach ($roles as $role): ?>
    <div class="card mb-3 shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong><?= e($role['role_name']) ?></strong>
            <span class="badge bg-secondary"><?= e($role['role_key']) ?></span>
        </div>
        <div class="card-body">
            <p class="text-muted"><?= e((string) $role['role_description']) ?></p>
            <ul class="mb-0">
                <?php
                $query = db()->prepare('SELECT p.permission_name FROM permissions p INNER JOIN role_permissions rp ON rp.permission_id = p.id WHERE rp.role_id = :role_id ORDER BY p.permission_name');
                $query->execute(['role_id' => $role['id']]);
                $permissions = $query->fetchAll();
                ?>
                <?php foreach ($permissions as $permission): ?>
                    <li><?= e($permission['permission_name']) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
<?php endforeach; ?>
<?php include __DIR__ . '/../../includes/layout/footer.php'; ?>
