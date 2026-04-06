<?php
/**
 * Radio Mehna V2 - Fonctions Analytics
 */

/**
 * Enregistrer une vue de page
 */
function trackPageView(): void
{
    // Ne pas tracker les requêtes AJAX ou les bots
    if (isAjax()) {
        return;
    }

    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $botPatterns = ['bot', 'crawl', 'spider', 'slurp', 'mediapartners'];
    foreach ($botPatterns as $pattern) {
        if (stripos($userAgent, $pattern) !== false) {
            return;
        }
    }

    try {
        $data = [
            'page_url'   => sanitize($_SERVER['REQUEST_URI'] ?? '/'),
            'page_type'  => 'page',
            'page_id'    => 0,
            'ip_address' => getClientIP(),
            'user_agent' => mb_substr($userAgent, 0, 500),
            'referrer'   => sanitize($_SERVER['HTTP_REFERER'] ?? ''),
            'language'   => currentLang(),
            'created_at' => date('Y-m-d H:i:s'),
        ];
        dbInsert('analytics', $data);
    } catch (Exception $e) {
        // Silencieusement ignorer les erreurs d'analytics
    }
}

/**
 * Enregistrer une action spécifique
 */
function trackAction(string $type, int $id, string $action = 'view'): void
{
    try {
        $data = [
            'page_url'   => sanitize($_SERVER['REQUEST_URI'] ?? '/'),
            'page_type'  => $type,
            'page_id'    => $id,
            'action'     => $action,
            'ip_address' => getClientIP(),
            'user_agent' => mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
            'language'   => currentLang(),
            'created_at' => date('Y-m-d H:i:s'),
        ];
        dbInsert('analytics', $data);
    } catch (Exception $e) {
        // Silencieusement ignorer
    }
}

/**
 * Obtenir les statistiques globales
 */
function getGlobalStats(): array
{
    try {
        return [
            'total_views'    => dbCount('analytics', "page_type = 'page'"),
            'total_plays'    => dbCount('analytics', "action = 'play'"),
            'today_views'    => dbCount('analytics', "page_type = 'page' AND DATE(created_at) = CURDATE()"),
            'total_programs' => dbCount('programs', 'is_active = 1'),
            'total_podcasts' => dbCount('podcasts', 'is_published = 1'),
            'total_posts'    => dbCount('posts', "status = 'published'"),
            'total_users'    => dbCount('users', '1=1'),
        ];
    } catch (Exception $e) {
        return [
            'total_views' => 0, 'total_plays' => 0, 'today_views' => 0,
            'total_programs' => 0, 'total_podcasts' => 0, 'total_posts' => 0,
            'total_users' => 0,
        ];
    }
}

/**
 * Obtenir les pages les plus vues
 */
function getTopPages(int $limit = 10): array
{
    $sql = "SELECT page_url, COUNT(*) as views
            FROM analytics
            WHERE page_type = 'page'
            GROUP BY page_url
            ORDER BY views DESC
            LIMIT ?";
    try {
        return dbFetchAll($sql, [$limit]);
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Obtenir les vues par jour (derniers N jours)
 */
function getViewsByDay(int $days = 30): array
{
    $sql = "SELECT DATE(created_at) as date, COUNT(*) as views
            FROM analytics
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
            GROUP BY DATE(created_at)
            ORDER BY date ASC";
    try {
        return dbFetchAll($sql, [$days]);
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Obtenir les statistiques par langue
 */
function getStatsByLanguage(): array
{
    $sql = "SELECT language, COUNT(*) as views
            FROM analytics
            GROUP BY language
            ORDER BY views DESC";
    try {
        return dbFetchAll($sql);
    } catch (Exception $e) {
        return [];
    }
}
