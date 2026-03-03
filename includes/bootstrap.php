<?php

declare(strict_types=1);

$appConfig = require __DIR__ . '/../config/app.php';

date_default_timezone_set($appConfig['timezone']);

if (session_status() === PHP_SESSION_NONE) {
    session_name($appConfig['session_name']);
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/security/csrf.php';
require_once __DIR__ . '/security/validator.php';
require_once __DIR__ . '/auth/auth.php';
require_once __DIR__ . '/auth/rbac.php';
require_once __DIR__ . '/audit.php';
