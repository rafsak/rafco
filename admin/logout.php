<?php
/**
 * Radio Mehna V2 - Admin Logout
 */
require_once __DIR__ . '/../includes/init.php';

if (isLoggedIn()) {
    try {
        dbInsert('logs', [
            'user_id'    => $_SESSION['user_id'],
            'action'     => 'logout',
            'details'    => 'Déconnexion',
            'ip_address' => getClientIP(),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    } catch (Exception $e) {}
}

session_destroy();
redirect(SITE_URL . '/admin/login.php');
