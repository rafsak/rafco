<?php

declare(strict_types=1);

function csrf_token(string $form = 'default'): string
{
    $token = bin2hex(random_bytes(32));
    $_SESSION['csrf'][$form] = [
        'token' => $token,
        'created_at' => time(),
    ];

    return $token;
}

function csrf_input(string $form = 'default'): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token($form)) . '">';
}

function csrf_validate(string $form = 'default'): bool
{
    $entry = $_SESSION['csrf'][$form] ?? null;
    $submitted = $_POST['csrf_token'] ?? '';

    if (!$entry || !is_string($submitted)) {
        return false;
    }

    $appConfig = require __DIR__ . '/../../config/app.php';
    $tokenLifetime = (int) ($appConfig['csrf_ttl'] ?? 3600);

    if ((time() - (int) $entry['created_at']) > $tokenLifetime) {
        unset($_SESSION['csrf'][$form]);
        return false;
    }

    $isValid = hash_equals((string) $entry['token'], $submitted);
    unset($_SESSION['csrf'][$form]);

    return $isValid;
}
