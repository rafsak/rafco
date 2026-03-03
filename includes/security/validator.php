<?php

declare(strict_types=1);

function validate_email(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validate_password_policy(string $password): array
{
    $errors = [];
    if (strlen($password) < 10) {
        $errors[] = 'Le mot de passe doit contenir au moins 10 caractères.';
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Le mot de passe doit contenir au moins une majuscule.';
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Le mot de passe doit contenir au moins une minuscule.';
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Le mot de passe doit contenir au moins un chiffre.';
    }
    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors[] = 'Le mot de passe doit contenir au moins un caractère spécial.';
    }

    return $errors;
}
