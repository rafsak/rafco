<?php
/**
 * Radio Mehna V2 - Fonctions Émissions/Programmes
 */

/**
 * Récupérer toutes les émissions actives
 */
function getPrograms(int $limit = 0, int $offset = 0): array
{
    $sql = "SELECT p.*, u.full_name as host_name, u.avatar as host_avatar
            FROM programs p
            LEFT JOIN users u ON p.host_id = u.id
            WHERE p.is_active = 1
            ORDER BY p.sort_order ASC, p.created_at DESC";

    if ($limit > 0) {
        $sql .= " LIMIT ? OFFSET ?";
        return dbFetchAll($sql, [$limit, $offset]);
    }

    return dbFetchAll($sql);
}

/**
 * Récupérer une émission par slug
 */
function getProgramBySlug(string $slug): ?array
{
    $sql = "SELECT p.*, u.full_name as host_name, u.avatar as host_avatar, u.bio as host_bio
            FROM programs p
            LEFT JOIN users u ON p.host_id = u.id
            WHERE p.slug = ? AND p.is_active = 1";
    return dbFetchOne($sql, [$slug]);
}

/**
 * Récupérer une émission par ID
 */
function getProgramById(int $id): ?array
{
    $sql = "SELECT p.*, u.full_name as host_name
            FROM programs p
            LEFT JOIN users u ON p.host_id = u.id
            WHERE p.id = ?";
    return dbFetchOne($sql, [$id]);
}

/**
 * Récupérer les émissions en vedette
 */
function getFeaturedPrograms(int $limit = 4): array
{
    $sql = "SELECT p.*, u.full_name as host_name, u.avatar as host_avatar
            FROM programs p
            LEFT JOIN users u ON p.host_id = u.id
            WHERE p.is_active = 1 AND p.is_featured = 1
            ORDER BY p.sort_order ASC
            LIMIT ?";
    return dbFetchAll($sql, [$limit]);
}

/**
 * Récupérer l'émission en cours
 */
function getCurrentProgram(): ?array
{
    $now = date('H:i:s');
    $today = strtolower(date('l'));

    $sql = "SELECT p.*, u.full_name as host_name
            FROM programs p
            LEFT JOIN users u ON p.host_id = u.id
            WHERE p.is_active = 1
              AND p.start_time <= ?
              AND p.end_time >= ?
              AND FIND_IN_SET(?, p.broadcast_days) > 0
            LIMIT 1";
    return dbFetchOne($sql, [$now, $now, $today]);
}

/**
 * Récupérer la grille des programmes pour un jour
 */
function getScheduleForDay(string $day): array
{
    $sql = "SELECT p.*, u.full_name as host_name
            FROM programs p
            LEFT JOIN users u ON p.host_id = u.id
            WHERE p.is_active = 1
              AND FIND_IN_SET(?, p.broadcast_days) > 0
            ORDER BY p.start_time ASC";
    return dbFetchAll($sql, [$day]);
}

/**
 * Compter les émissions
 */
function countPrograms(): int
{
    return dbCount('programs', 'is_active = 1');
}

/**
 * Créer une émission (admin)
 */
function createProgram(array $data): int
{
    return dbInsert('programs', $data);
}

/**
 * Mettre à jour une émission (admin)
 */
function updateProgram(int $id, array $data): int
{
    return dbUpdate('programs', $data, 'id = ?', [$id]);
}

/**
 * Supprimer une émission (admin)
 */
function deleteProgram(int $id): int
{
    return dbDelete('programs', 'id = ?', [$id]);
}

/**
 * Récupérer les émissions populaires (suggestions IA)
 */
function getPopularPrograms(int $limit = 5): array
{
    $sql = "SELECT p.*, u.full_name as host_name, COUNT(a.id) as view_count
            FROM programs p
            LEFT JOIN users u ON p.host_id = u.id
            LEFT JOIN analytics a ON a.page_type = 'program' AND a.page_id = p.id
            WHERE p.is_active = 1
            GROUP BY p.id
            ORDER BY view_count DESC
            LIMIT ?";
    return dbFetchAll($sql, [$limit]);
}
