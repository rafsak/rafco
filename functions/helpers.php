<?php
/**
 * Radio Mehna V2 - Fonctions utilitaires
 */

/**
 * Redirection HTTP
 */
function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
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
 * Obtenir le paramètre GET nettoyé
 */
function getParam(string $key, $default = null)
{
    return isset($_GET[$key]) ? sanitize($_GET[$key]) : $default;
}

/**
 * Obtenir le paramètre POST nettoyé
 */
function postParam(string $key, $default = null)
{
    return isset($_POST[$key]) ? sanitize($_POST[$key]) : $default;
}

/**
 * Pagination
 */
function paginate(int $total, int $perPage, int $currentPage): array
{
    $totalPages = max(1, (int) ceil($total / $perPage));
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $perPage;

    return [
        'total'        => $total,
        'per_page'     => $perPage,
        'current_page' => $currentPage,
        'total_pages'  => $totalPages,
        'offset'       => $offset,
        'has_prev'     => $currentPage > 1,
        'has_next'     => $currentPage < $totalPages,
    ];
}

/**
 * Générer le HTML de pagination
 */
function paginationHtml(array $pagination, string $baseUrl): string
{
    if ($pagination['total_pages'] <= 1) {
        return '';
    }

    $html = '<nav aria-label="Pagination"><ul class="pagination justify-content-center">';

    // Précédent
    if ($pagination['has_prev']) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=' . ($pagination['current_page'] - 1) . '">&laquo;</a></li>';
    }

    // Pages
    $start = max(1, $pagination['current_page'] - 2);
    $end = min($pagination['total_pages'], $pagination['current_page'] + 2);

    if ($start > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=1">1</a></li>';
        if ($start > 2) {
            $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }

    for ($i = $start; $i <= $end; $i++) {
        $active = $i === $pagination['current_page'] ? ' active' : '';
        $html .= '<li class="page-item' . $active . '"><a class="page-link" href="' . $baseUrl . '?page=' . $i . '">' . $i . '</a></li>';
    }

    if ($end < $pagination['total_pages']) {
        if ($end < $pagination['total_pages'] - 1) {
            $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
        $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=' . $pagination['total_pages'] . '">' . $pagination['total_pages'] . '</a></li>';
    }

    // Suivant
    if ($pagination['has_next']) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=' . ($pagination['current_page'] + 1) . '">&raquo;</a></li>';
    }

    $html .= '</ul></nav>';
    return $html;
}

/**
 * Tronquer un texte
 */
function truncateText(string $text, int $length = 150): string
{
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    return mb_substr($text, 0, $length) . '...';
}

/**
 * Formater la durée en minutes:secondes
 */
function formatDuration(int $seconds): string
{
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $secs = $seconds % 60;

    if ($hours > 0) {
        return sprintf('%d:%02d:%02d', $hours, $minutes, $secs);
    }
    return sprintf('%d:%02d', $minutes, $secs);
}

/**
 * Formater une date relative (il y a X)
 */
function timeAgo(string $datetime): string
{
    $now = new DateTime();
    $date = new DateTime($datetime);
    $diff = $now->diff($date);
    $lang = currentLang();

    if ($diff->y > 0) {
        $n = $diff->y;
        return match ($lang) {
            'ar' => "منذ {$n} سنة",
            'en' => "{$n} year" . ($n > 1 ? 's' : '') . " ago",
            default => "il y a {$n} an" . ($n > 1 ? 's' : ''),
        };
    }
    if ($diff->m > 0) {
        $n = $diff->m;
        return match ($lang) {
            'ar' => "منذ {$n} شهر",
            'en' => "{$n} month" . ($n > 1 ? 's' : '') . " ago",
            default => "il y a {$n} mois",
        };
    }
    if ($diff->d > 0) {
        $n = $diff->d;
        return match ($lang) {
            'ar' => "منذ {$n} يوم",
            'en' => "{$n} day" . ($n > 1 ? 's' : '') . " ago",
            default => "il y a {$n} jour" . ($n > 1 ? 's' : ''),
        };
    }
    if ($diff->h > 0) {
        $n = $diff->h;
        return match ($lang) {
            'ar' => "منذ {$n} ساعة",
            'en' => "{$n} hour" . ($n > 1 ? 's' : '') . " ago",
            default => "il y a {$n} heure" . ($n > 1 ? 's' : ''),
        };
    }
    if ($diff->i > 0) {
        $n = $diff->i;
        return match ($lang) {
            'ar' => "منذ {$n} دقيقة",
            'en' => "{$n} minute" . ($n > 1 ? 's' : '') . " ago",
            default => "il y a {$n} minute" . ($n > 1 ? 's' : ''),
        };
    }

    return match ($lang) {
        'ar' => 'الآن',
        'en' => 'just now',
        default => 'à l\'instant',
    };
}

/**
 * Obtenir l'URL actuelle
 */
function currentUrl(): string
{
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    return $protocol . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ($_SERVER['REQUEST_URI'] ?? '/');
}

/**
 * Vérifier si la requête est AJAX
 */
function isAjax(): bool
{
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
        && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Obtenir l'IP du client
 */
function getClientIP(): string
{
    $headers = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
    foreach ($headers as $header) {
        if (!empty($_SERVER[$header])) {
            $ip = explode(',', $_SERVER[$header])[0];
            if (filter_var(trim($ip), FILTER_VALIDATE_IP)) {
                return trim($ip);
            }
        }
    }
    return '0.0.0.0';
}

/**
 * Définir les meta tags SEO
 */
function setPageMeta(string $title, string $description = '', string $image = '', string $type = 'website'): void
{
    $GLOBALS['page_meta'] = [
        'title'       => $title . ' | ' . __('site.name'),
        'description' => $description ?: __('site.description'),
        'image'       => $image ?: ASSETS_URL . '/images/og-default.jpg',
        'type'        => $type,
        'url'         => currentUrl(),
    ];
}

/**
 * Obtenir les meta tags
 */
function getPageMeta(): array
{
    return $GLOBALS['page_meta'] ?? [
        'title'       => __('site.name') . ' - ' . __('site.tagline'),
        'description' => __('site.description'),
        'image'       => ASSETS_URL . '/images/og-default.jpg',
        'type'        => 'website',
        'url'         => currentUrl(),
    ];
}

/**
 * Afficher un message flash
 */
function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Obtenir et supprimer le message flash
 */
function getFlash(): ?array
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Afficher le HTML du message flash
 */
function flashHtml(): string
{
    $flash = getFlash();
    if (!$flash) {
        return '';
    }

    $typeClass = match ($flash['type']) {
        'success' => 'alert-success',
        'error'   => 'alert-danger',
        'warning' => 'alert-warning',
        default   => 'alert-info',
    };

    return '<div class="alert ' . $typeClass . ' alert-dismissible fade show" role="alert">'
        . e($flash['message'])
        . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>'
        . '</div>';
}
