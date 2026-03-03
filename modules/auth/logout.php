<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';

logout_user();
session_start();
flash('success', 'Vous êtes déconnecté.');
redirect('/modules/auth/login.php');
