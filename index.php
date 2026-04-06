<?php
/**
 * Radio Mehna V2 - Page d'accueil / Router
 */
require_once __DIR__ . '/includes/init.php';

// Simple router basé sur l'URL
$requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$segments = explode('/', trim($requestUri, '/'));

// Détecter la langue dans l'URL
$lang = DEFAULT_LANG;
if (!empty($segments[0]) && in_array($segments[0], SUPPORTED_LANGS)) {
    $lang = $segments[0];
    $_SESSION['lang'] = $lang;
    setcookie('lang', $lang, time() + 86400 * 365, '/');
    array_shift($segments);
}

// Déterminer la page
$page = $segments[0] ?? 'home';
$param = $segments[1] ?? null;
$subParam = $segments[2] ?? null;

// Router
switch ($page) {
    case '':
    case 'home':
        require_once MODULES_PATH . 'home.php';
        break;

    case 'programs':
    case 'emissions':
        if ($param === 'schedule') {
            require_once MODULES_PATH . 'schedule.php';
        } elseif ($param) {
            $_GET['slug'] = $param;
            require_once MODULES_PATH . 'program-detail.php';
        } else {
            require_once MODULES_PATH . 'programs.php';
        }
        break;

    case 'podcasts':
        if ($param) {
            $_GET['slug'] = $param;
            require_once MODULES_PATH . 'podcast-detail.php';
        } else {
            require_once MODULES_PATH . 'podcasts.php';
        }
        break;

    case 'news':
    case 'actualites':
        if ($param) {
            $_GET['slug'] = $param;
            require_once MODULES_PATH . 'post-detail.php';
        } else {
            require_once MODULES_PATH . 'news.php';
        }
        break;

    case 'contact':
        require_once MODULES_PATH . 'contact.php';
        break;

    case 'search':
        require_once MODULES_PATH . 'search.php';
        break;

    case 'sitemap.xml':
        require_once MODULES_PATH . 'sitemap.php';
        break;

    default:
        http_response_code(404);
        require_once MODULES_PATH . '404.php';
        break;
}
