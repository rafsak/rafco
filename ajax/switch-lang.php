<?php
/**
 * Radio Mehna V2 - AJAX Language Switch Handler
 */
require_once __DIR__ . '/../includes/init.php';

$lang = sanitize($_POST['lang'] ?? '');

if (in_array($lang, SUPPORTED_LANGS)) {
    $_SESSION['lang'] = $lang;
    setcookie('lang', $lang, time() + 86400 * 365, '/');
    jsonResponse(['success' => true, 'lang' => $lang]);
} else {
    jsonResponse(['success' => false, 'error' => 'Invalid language'], 400);
}
