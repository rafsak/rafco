<?php
/**
 * Radio Mehna V2 - Fonctions Podcasts
 */

/**
 * Récupérer les podcasts avec pagination
 */
function getPodcasts(int $limit = 0, int $offset = 0, ?string $category = null): array
{
    $params = [];
    $where = "p.is_published = 1";

    if ($category) {
        $where .= " AND p.category = ?";
        $params[] = $category;
    }

    $sql = "SELECT p.*, u.full_name as author_name, pr.title as program_title
            FROM podcasts p
            LEFT JOIN users u ON p.author_id = u.id
            LEFT JOIN programs pr ON p.program_id = pr.id
            WHERE {$where}
            ORDER BY p.published_at DESC";

    if ($limit > 0) {
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
    }

    return dbFetchAll($sql, $params);
}

/**
 * Récupérer un podcast par slug
 */
function getPodcastBySlug(string $slug): ?array
{
    $sql = "SELECT p.*, u.full_name as author_name, u.avatar as author_avatar,
                   pr.title as program_title, pr.slug as program_slug
            FROM podcasts p
            LEFT JOIN users u ON p.author_id = u.id
            LEFT JOIN programs pr ON p.program_id = pr.id
            WHERE p.slug = ? AND p.is_published = 1";
    return dbFetchOne($sql, [$slug]);
}

/**
 * Récupérer un podcast par ID
 */
function getPodcastById(int $id): ?array
{
    $sql = "SELECT p.*, u.full_name as author_name
            FROM podcasts p
            LEFT JOIN users u ON p.author_id = u.id
            WHERE p.id = ?";
    return dbFetchOne($sql, [$id]);
}

/**
 * Récupérer les podcasts récents
 */
function getRecentPodcasts(int $limit = 6): array
{
    $sql = "SELECT p.*, u.full_name as author_name, pr.title as program_title
            FROM podcasts p
            LEFT JOIN users u ON p.author_id = u.id
            LEFT JOIN programs pr ON p.program_id = pr.id
            WHERE p.is_published = 1
            ORDER BY p.published_at DESC
            LIMIT ?";
    return dbFetchAll($sql, [$limit]);
}

/**
 * Récupérer les catégories de podcasts
 */
function getPodcastCategories(): array
{
    $sql = "SELECT DISTINCT category, COUNT(*) as count
            FROM podcasts
            WHERE is_published = 1 AND category IS NOT NULL AND category != ''
            GROUP BY category
            ORDER BY count DESC";
    return dbFetchAll($sql);
}

/**
 * Compter les podcasts
 */
function countPodcasts(?string $category = null): int
{
    $where = 'is_published = 1';
    $params = [];

    if ($category) {
        $where .= ' AND category = ?';
        $params[] = $category;
    }

    return dbCount('podcasts', $where, $params);
}

/**
 * Incrémenter le compteur d'écoute
 */
function incrementPodcastPlays(int $id): void
{
    $sql = "UPDATE podcasts SET play_count = play_count + 1 WHERE id = ?";
    dbQuery($sql, [$id]);
}

/**
 * Créer un podcast (admin)
 */
function createPodcast(array $data): int
{
    return dbInsert('podcasts', $data);
}

/**
 * Mettre à jour un podcast (admin)
 */
function updatePodcast(int $id, array $data): int
{
    return dbUpdate('podcasts', $data, 'id = ?', [$id]);
}

/**
 * Supprimer un podcast (admin)
 */
function deletePodcast(int $id): int
{
    return dbDelete('podcasts', 'id = ?', [$id]);
}

/**
 * Podcasts populaires (suggestions IA)
 */
function getPopularPodcasts(int $limit = 5): array
{
    $sql = "SELECT p.*, u.full_name as author_name
            FROM podcasts p
            LEFT JOIN users u ON p.author_id = u.id
            WHERE p.is_published = 1
            ORDER BY p.play_count DESC
            LIMIT ?";
    return dbFetchAll($sql, [$limit]);
}

/**
 * Podcasts liés (même catégorie)
 */
function getRelatedPodcasts(int $podcastId, string $category, int $limit = 4): array
{
    $sql = "SELECT p.*, u.full_name as author_name
            FROM podcasts p
            LEFT JOIN users u ON p.author_id = u.id
            WHERE p.is_published = 1 AND p.category = ? AND p.id != ?
            ORDER BY p.published_at DESC
            LIMIT ?";
    return dbFetchAll($sql, [$category, $podcastId, $limit]);
}
