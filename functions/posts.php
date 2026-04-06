<?php
/**
 * Radio Mehna V2 - Fonctions Articles/Actualités
 */

/**
 * Récupérer les articles avec pagination
 */
function getPosts(int $limit = 0, int $offset = 0): array
{
    $sql = "SELECT p.*, u.full_name as author_name, u.avatar as author_avatar
            FROM posts p
            LEFT JOIN users u ON p.author_id = u.id
            WHERE p.status = 'published'
            ORDER BY p.published_at DESC";

    $params = [];
    if ($limit > 0) {
        $sql .= " LIMIT ? OFFSET ?";
        $params = [$limit, $offset];
    }

    return dbFetchAll($sql, $params);
}

/**
 * Récupérer un article par slug
 */
function getPostBySlug(string $slug): ?array
{
    $sql = "SELECT p.*, u.full_name as author_name, u.avatar as author_avatar
            FROM posts p
            LEFT JOIN users u ON p.author_id = u.id
            WHERE p.slug = ? AND p.status = 'published'";
    return dbFetchOne($sql, [$slug]);
}

/**
 * Récupérer un article par ID
 */
function getPostById(int $id): ?array
{
    $sql = "SELECT p.*, u.full_name as author_name
            FROM posts p
            LEFT JOIN users u ON p.author_id = u.id
            WHERE p.id = ?";
    return dbFetchOne($sql, [$id]);
}

/**
 * Récupérer les articles récents
 */
function getRecentPosts(int $limit = 4): array
{
    $sql = "SELECT p.*, u.full_name as author_name
            FROM posts p
            LEFT JOIN users u ON p.author_id = u.id
            WHERE p.status = 'published'
            ORDER BY p.published_at DESC
            LIMIT ?";
    return dbFetchAll($sql, [$limit]);
}

/**
 * Compter les articles
 */
function countPosts(): int
{
    return dbCount('posts', "status = 'published'");
}

/**
 * Incrémenter les vues d'un article
 */
function incrementPostViews(int $id): void
{
    $sql = "UPDATE posts SET views = views + 1 WHERE id = ?";
    dbQuery($sql, [$id]);
}

/**
 * Articles liés
 */
function getRelatedPosts(int $postId, int $limit = 3): array
{
    $sql = "SELECT p.*, u.full_name as author_name
            FROM posts p
            LEFT JOIN users u ON p.author_id = u.id
            WHERE p.status = 'published' AND p.id != ?
            ORDER BY p.published_at DESC
            LIMIT ?";
    return dbFetchAll($sql, [$postId, $limit]);
}

/**
 * Créer un article (admin)
 */
function createPost(array $data): int
{
    return dbInsert('posts', $data);
}

/**
 * Mettre à jour un article (admin)
 */
function updatePost(int $id, array $data): int
{
    return dbUpdate('posts', $data, 'id = ?', [$id]);
}

/**
 * Supprimer un article (admin)
 */
function deletePost(int $id): int
{
    return dbDelete('posts', 'id = ?', [$id]);
}

/**
 * Récupérer tous les articles (admin - incluant brouillons)
 */
function getAllPosts(int $limit = 0, int $offset = 0): array
{
    $sql = "SELECT p.*, u.full_name as author_name
            FROM posts p
            LEFT JOIN users u ON p.author_id = u.id
            ORDER BY p.created_at DESC";

    $params = [];
    if ($limit > 0) {
        $sql .= " LIMIT ? OFFSET ?";
        $params = [$limit, $offset];
    }

    return dbFetchAll($sql, $params);
}
