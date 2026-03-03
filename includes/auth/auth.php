<?php

declare(strict_types=1);

function current_user(): ?array
{
    if (!isset($_SESSION['auth']['user'])) {
        return null;
    }

    return $_SESSION['auth']['user'];
}

function is_authenticated(): bool
{
    return current_user() !== null;
}

function login_user(array $user): void
{
    session_regenerate_id(true);

    $_SESSION['auth'] = [
        'user' => [
            'id' => (int) $user['id'],
            'full_name' => (string) $user['full_name'],
            'email' => (string) $user['email'],
        ],
        'permissions' => fetch_user_permissions((int) $user['id']),
        'last_regenerated' => time(),
    ];

    $query = db()->prepare('UPDATE users SET last_login_at = NOW() WHERE id = :id');
    $query->execute(['id' => $user['id']]);

    audit_log((int) $user['id'], 'AUTH_LOGIN', 'AUTH', 'users', (int) $user['id'], 'Connexion utilisateur réussie');
}

function logout_user(): void
{
    $user = current_user();

    if ($user) {
        audit_log((int) $user['id'], 'AUTH_LOGOUT', 'AUTH', 'users', (int) $user['id'], 'Déconnexion utilisateur');
    }

    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool) $params['secure'], (bool) $params['httponly']);
    }

    session_destroy();
}

function require_authentication(): void
{
    if (!is_authenticated()) {
        flash('error', 'Veuillez vous connecter pour accéder à cette ressource.');
        redirect('/modules/auth/login.php');
    }

    $lastRegenerated = (int) ($_SESSION['auth']['last_regenerated'] ?? 0);
    if (time() - $lastRegenerated > 300) {
        session_regenerate_id(true);
        $_SESSION['auth']['last_regenerated'] = time();
    }
}
