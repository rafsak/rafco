<?php
/**
 * ATFP Chatbot - Configuration
 * Connexion PDO MySQL + constantes globales
 */

// -- Paramètres base de données --
define('DB_HOST', 'localhost');
define('DB_NAME', 'atfp_chatbot');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// -- Paramètres application --
define('APP_NAME', 'ATFP Chatbot');
define('APP_VERSION', '1.0.0');
define('DEFAULT_LANG', 'fr');

// -- Sécurité --
define('SESSION_LIFETIME', 3600); // 1 heure

/**
 * Connexion PDO singleton
 */
function getDB(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    }
    return $pdo;
}

/**
 * Échappement XSS
 */
function e(string $str): string
{
    return htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Réponse JSON
 */
function jsonResponse(array $data, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Charger les intents depuis intents.json
 */
function loadIntents(): array
{
    $file = __DIR__ . '/intents.json';
    if (!file_exists($file)) {
        return [];
    }
    $json = file_get_contents($file);
    $data = json_decode($json, true);
    return is_array($data) ? $data : [];
}
