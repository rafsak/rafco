<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

if (is_authenticated()) {
    redirect('/modules/dashboard/index.php');
}

redirect('/modules/auth/login.php');
