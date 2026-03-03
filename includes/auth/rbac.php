<?php

declare(strict_types=1);

function fetch_user_permissions(int $userId): array
{
    $sql = 'SELECT DISTINCT p.permission_key
            FROM permissions p
            INNER JOIN role_permissions rp ON rp.permission_id = p.id
            INNER JOIN user_roles ur ON ur.role_id = rp.role_id
            WHERE ur.user_id = :user_id';

    $query = db()->prepare($sql);
    $query->execute(['user_id' => $userId]);

    return array_column($query->fetchAll(), 'permission_key');
}

function has_permission(string $permission): bool
{
    $permissions = $_SESSION['auth']['permissions'] ?? [];
    return in_array($permission, $permissions, true);
}

function require_permission(string $permission): void
{
    require_authentication();

    if (!has_permission($permission)) {
        http_response_code(403);
        include __DIR__ . '/../../modules/auth/forbidden.php';
        exit;
    }
}

function user_roles(int $userId): array
{
    $sql = 'SELECT r.id, r.role_key, r.role_name
            FROM roles r
            INNER JOIN user_roles ur ON ur.role_id = r.id
            WHERE ur.user_id = :user_id
            ORDER BY r.role_name';

    $query = db()->prepare($sql);
    $query->execute(['user_id' => $userId]);

    return $query->fetchAll();
}
