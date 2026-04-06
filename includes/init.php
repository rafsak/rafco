<?php
/**
 * Radio Mehna V2 - Initialisation
 * Fichier principal d'amorçage
 */

// Charger la configuration
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/i18n.php';

// Charger les fonctions
require_once FUNCTIONS_PATH . 'helpers.php';
require_once FUNCTIONS_PATH . 'programs.php';
require_once FUNCTIONS_PATH . 'podcasts.php';
require_once FUNCTIONS_PATH . 'posts.php';
require_once FUNCTIONS_PATH . 'media.php';
require_once FUNCTIONS_PATH . 'analytics.php';
require_once FUNCTIONS_PATH . 'chatbot.php';

// Initialiser la session
initSecureSession();

// Détecter la langue
detectLanguage();

// Enregistrer la visite (analytics)
if (ANALYTICS_ENABLED && php_sapi_name() !== 'cli') {
    trackPageView();
}
