<?php
/**
 * Radio Mehna V2 - AJAX Search Handler
 */
require_once __DIR__ . '/../includes/init.php';

$query = sanitize($_GET['q'] ?? '');

if (mb_strlen($query) < 2) {
    jsonResponse(['results' => []]);
}

$results = smartSearch($query, 8);
jsonResponse(['results' => $results]);
