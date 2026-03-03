<?php

declare(strict_types=1);

return [
    'app_name' => 'Rafco ERP Tunisia',
    'base_url' => 'http://localhost:8000',
    'timezone' => 'Africa/Tunis',
    'session_name' => 'RAFCO_ERP_SESSID',
    'csrf_ttl' => 3600,
    'security' => [
        'password_min_length' => 10,
        'session_regenerate_interval' => 300,
    ],
];
