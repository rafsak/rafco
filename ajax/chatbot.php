<?php
/**
 * Radio Mehna V2 - AJAX Chatbot Handler
 */
require_once __DIR__ . '/../includes/init.php';

if (!isAjax() && !isset($_POST['message'])) {
    jsonResponse(['error' => 'Invalid request'], 400);
}

$message = sanitize($_POST['message'] ?? '');
$lang = sanitize($_POST['lang'] ?? currentLang());

if (empty($message)) {
    jsonResponse(['error' => 'Empty message'], 400);
}

$response = processChatMessage($message, $lang);
jsonResponse($response);
