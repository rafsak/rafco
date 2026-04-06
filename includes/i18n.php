<?php
/**
 * Radio Mehna V2 - Système Multilingue (i18n)
 * Traduction dynamique FR / AR / EN avec support RTL
 */

/**
 * Détecter la langue courante
 */
function detectLanguage(): string
{
    // 1. Paramètre URL
    if (isset($_GET['lang']) && in_array($_GET['lang'], SUPPORTED_LANGS)) {
        $_SESSION['lang'] = $_GET['lang'];
        return $_GET['lang'];
    }

    // 2. Session
    if (isset($_SESSION['lang']) && in_array($_SESSION['lang'], SUPPORTED_LANGS)) {
        return $_SESSION['lang'];
    }

    // 3. Cookie
    if (isset($_COOKIE['lang']) && in_array($_COOKIE['lang'], SUPPORTED_LANGS)) {
        $_SESSION['lang'] = $_COOKIE['lang'];
        return $_COOKIE['lang'];
    }

    // 4. URL path (/fr/, /ar/, /en/)
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
    $segments = explode('/', trim($path, '/'));
    if (!empty($segments[0]) && in_array($segments[0], SUPPORTED_LANGS)) {
        $_SESSION['lang'] = $segments[0];
        return $segments[0];
    }

    // 5. Défaut
    $_SESSION['lang'] = DEFAULT_LANG;
    return DEFAULT_LANG;
}

/**
 * Obtenir la langue courante
 */
function currentLang(): string
{
    return $_SESSION['lang'] ?? detectLanguage();
}

/**
 * Vérifier si la langue courante est RTL
 */
function isRTL(): bool
{
    return in_array(currentLang(), RTL_LANGS);
}

/**
 * Obtenir la direction du texte
 */
function textDirection(): string
{
    return isRTL() ? 'rtl' : 'ltr';
}

/**
 * Charger les traductions depuis les fichiers
 */
function loadTranslations(string $lang): array
{
    static $translations = [];

    if (isset($translations[$lang])) {
        return $translations[$lang];
    }

    $file = LANG_PATH . $lang . '.php';
    if (file_exists($file)) {
        $translations[$lang] = require $file;
    } else {
        $translations[$lang] = [];
    }

    return $translations[$lang];
}

/**
 * Traduire une clé
 */
function __($key, array $params = []): string
{
    $lang = currentLang();
    $translations = loadTranslations($lang);

    // Chercher la traduction avec support des clés imbriquées (ex: "nav.home")
    $keys = explode('.', $key);
    $value = $translations;
    foreach ($keys as $k) {
        if (is_array($value) && isset($value[$k])) {
            $value = $value[$k];
        } else {
            // Fallback vers la langue par défaut
            if ($lang !== DEFAULT_LANG) {
                $fallback = loadTranslations(DEFAULT_LANG);
                $value = $fallback;
                foreach ($keys as $fk) {
                    if (is_array($value) && isset($value[$fk])) {
                        $value = $value[$fk];
                    } else {
                        return $key; // Retourner la clé si rien trouvé
                    }
                }
                break;
            }
            return $key;
        }
    }

    if (!is_string($value)) {
        return $key;
    }

    // Remplacer les paramètres
    foreach ($params as $paramKey => $paramValue) {
        $value = str_replace(':' . $paramKey, $paramValue, $value);
    }

    return $value;
}

/**
 * Générer une URL multilingue
 */
function langUrl(string $path, ?string $lang = null): string
{
    $lang = $lang ?? currentLang();
    $path = ltrim($path, '/');
    return SITE_URL . '/' . $lang . '/' . $path;
}

/**
 * Générer un lien de changement de langue
 */
function switchLangUrl(string $lang): string
{
    $currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
    $segments = explode('/', trim($currentPath, '/'));

    // Remplacer le segment de langue s'il existe
    if (!empty($segments[0]) && in_array($segments[0], SUPPORTED_LANGS)) {
        $segments[0] = $lang;
    } else {
        array_unshift($segments, $lang);
    }

    return SITE_URL . '/' . implode('/', $segments);
}

/**
 * Obtenir le nom de la langue
 */
function langName(string $code): string
{
    $names = [
        'fr' => 'Français',
        'ar' => 'العربية',
        'en' => 'English',
    ];
    return $names[$code] ?? $code;
}

/**
 * Obtenir le drapeau emoji de la langue
 */
function langFlag(string $code): string
{
    $flags = [
        'fr' => '🇫🇷',
        'ar' => '🇹🇳',
        'en' => '🇬🇧',
    ];
    return $flags[$code] ?? '';
}

/**
 * Formater une date selon la langue
 */
function formatDate(string $date, ?string $lang = null): string
{
    $lang = $lang ?? currentLang();
    $timestamp = strtotime($date);

    $formats = [
        'fr' => 'd/m/Y à H:i',
        'ar' => 'Y/m/d H:i',
        'en' => 'M d, Y h:i A',
    ];

    return date($formats[$lang] ?? $formats['fr'], $timestamp);
}
