<?php
/**
 * Radio Mehna V2 - Fonctions Médias
 */

/**
 * Récupérer tous les médias
 */
function getMediaFiles(int $limit = 0, int $offset = 0, ?string $type = null): array
{
    $where = '1=1';
    $params = [];

    if ($type) {
        $where .= ' AND type = ?';
        $params[] = $type;
    }

    $sql = "SELECT * FROM media WHERE {$where} ORDER BY created_at DESC";

    if ($limit > 0) {
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
    }

    return dbFetchAll($sql, $params);
}

/**
 * Récupérer un média par ID
 */
function getMediaById(int $id): ?array
{
    return dbFetchOne("SELECT * FROM media WHERE id = ?", [$id]);
}

/**
 * Enregistrer un fichier média
 */
function saveMedia(array $file, string $type = 'image'): ?array
{
    $allowedTypes = $type === 'audio' ? ALLOWED_AUDIO_TYPES : ALLOWED_IMAGE_TYPES;
    $destination = UPLOADS_PATH . ($type === 'audio' ? 'podcasts' : 'media');

    $filename = secureUpload($file, $destination, $allowedTypes);
    if (!$filename) {
        return null;
    }

    $data = [
        'filename'      => $filename,
        'original_name' => sanitize($file['name']),
        'type'          => $type,
        'mime_type'     => $file['type'],
        'size'          => $file['size'],
        'path'          => ($type === 'audio' ? 'uploads/podcasts/' : 'uploads/media/') . $filename,
        'created_at'    => date('Y-m-d H:i:s'),
    ];

    $id = dbInsert('media', $data);
    $data['id'] = $id;

    return $data;
}

/**
 * Supprimer un média
 */
function deleteMedia(int $id): bool
{
    $media = getMediaById($id);
    if (!$media) {
        return false;
    }

    $filepath = ROOT_PATH . $media['path'];
    if (file_exists($filepath)) {
        unlink($filepath);
    }

    dbDelete('media', 'id = ?', [$id]);
    return true;
}

/**
 * Compter les médias
 */
function countMedia(?string $type = null): int
{
    $where = '1=1';
    $params = [];

    if ($type) {
        $where .= ' AND type = ?';
        $params[] = $type;
    }

    return dbCount('media', $where, $params);
}

/**
 * Obtenir l'URL d'un média
 */
function mediaUrl(string $path): string
{
    if (empty($path)) {
        return ASSETS_URL . '/images/placeholder.jpg';
    }
    if (str_starts_with($path, 'http')) {
        return $path;
    }
    return SITE_URL . '/' . ltrim($path, '/');
}
