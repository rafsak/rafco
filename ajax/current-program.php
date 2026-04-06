<?php
/**
 * Radio Mehna V2 - AJAX Current Program Handler
 */
require_once __DIR__ . '/../includes/init.php';

try {
    $program = getCurrentProgram();
    if ($program) {
        jsonResponse([
            'title'      => $program['title'],
            'host'       => $program['host_name'] ?? '',
            'start_time' => $program['start_time'] ?? '',
            'end_time'   => $program['end_time'] ?? '',
        ]);
    }
} catch (Exception $e) {
    // DB not available
}

jsonResponse(['title' => __('site.name'), 'host' => '']);
