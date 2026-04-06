<?php
/**
 * Radio Mehna V2 - Configuration
 * Fichier de configuration principal
 */

// Mode debug (mettre à false en production)
define('DEBUG_MODE', true);

// Informations du site
define('SITE_NAME', 'Radio Mehna');
define('SITE_TAGLINE', 'La voix de la Tunisie');
define('SITE_VERSION', '2.0.0');
define('SITE_URL', 'http://localhost:8080');

// Base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'radio_mehna');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Stream radio
define('RADIO_STREAM_URL', 'https://stream6.tanitweb.com/radiomehna');

// Chemins
define('ROOT_PATH', dirname(__DIR__) . '/');
define('INCLUDES_PATH', ROOT_PATH . 'includes/');
define('FUNCTIONS_PATH', ROOT_PATH . 'functions/');
define('MODULES_PATH', ROOT_PATH . 'modules/');
define('ADMIN_PATH', ROOT_PATH . 'admin/');
define('UPLOADS_PATH', ROOT_PATH . 'uploads/');
define('ASSETS_PATH', ROOT_PATH . 'assets/');
define('LANG_PATH', ROOT_PATH . 'lang/');
define('CACHE_PATH', ROOT_PATH . 'cache/');

// URLs relatives
define('ASSETS_URL', SITE_URL . '/assets');
define('UPLOADS_URL', SITE_URL . '/uploads');

// Langues supportées
define('SUPPORTED_LANGS', ['fr', 'ar', 'en']);
define('DEFAULT_LANG', 'fr');
define('RTL_LANGS', ['ar']);

// Sécurité
define('CSRF_TOKEN_NAME', 'csrf_token');
define('SESSION_LIFETIME', 3600);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_TIME', 900);

// Upload
define('MAX_UPLOAD_SIZE', 50 * 1024 * 1024); // 50 MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
define('ALLOWED_AUDIO_TYPES', ['audio/mpeg', 'audio/mp3', 'audio/wav', 'audio/ogg']);

// Pagination
define('ITEMS_PER_PAGE', 12);
define('ADMIN_ITEMS_PER_PAGE', 20);

// Chatbot IA
define('CHATBOT_ENABLED', true);
define('CHATBOT_MAX_HISTORY', 50);

// Analytics
define('ANALYTICS_ENABLED', true);

// PWA
define('PWA_ENABLED', true);

// Gestion des erreurs
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Timezone
date_default_timezone_set('Africa/Tunis');
