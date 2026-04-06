<?php
/**
 * Radio Mehna V2 - Sécurité
 * Protection XSS, CSRF, sessions sécurisées
 */

/**
 * Initialiser la session sécurisée
 */
function initSecureSession(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_secure', '0');
        ini_set('session.use_strict_mode', '1');
        ini_set('session.cookie_samesite', 'Lax');
        session_start();
    }

    // Régénérer l'ID de session périodiquement
    if (!isset($_SESSION['_last_regeneration'])) {
        $_SESSION['_last_regeneration'] = time();
    } elseif (time() - $_SESSION['_last_regeneration'] > 300) {
        session_regenerate_id(true);
        $_SESSION['_last_regeneration'] = time();
    }
}

/**
 * Générer un token CSRF
 */
function generateCSRFToken(): string
{
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * Vérifier le token CSRF
 */
function verifyCSRFToken(?string $token): bool
{
    if (empty($token) || empty($_SESSION[CSRF_TOKEN_NAME])) {
        return false;
    }
    return hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

/**
 * Champ caché CSRF pour les formulaires
 */
function csrfField(): string
{
    $token = generateCSRFToken();
    return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . htmlspecialchars($token) . '">';
}

/**
 * Échapper les sorties HTML (protection XSS)
 */
function e(string $string): string
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Nettoyer une entrée utilisateur
 */
function sanitize(string $input): string
{
    return trim(strip_tags($input));
}

/**
 * Nettoyer un email
 */
function sanitizeEmail(string $email): string
{
    return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
}

/**
 * Valider un email
 */
function isValidEmail(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Hasher un mot de passe
 */
function hashPassword(string $password): string
{
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Vérifier un mot de passe
 */
function verifyPassword(string $password, string $hash): bool
{
    return password_verify($password, $hash);
}

/**
 * Vérifier si l'utilisateur est connecté
 */
function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Vérifier si l'utilisateur est admin
 */
function isAdmin(): bool
{
    return isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Vérifier si l'utilisateur est éditeur ou admin
 */
function isEditor(): bool
{
    return isLoggedIn() && isset($_SESSION['user_role'])
        && in_array($_SESSION['user_role'], ['admin', 'editor']);
}

/**
 * Rediriger si non connecté
 */
function requireLogin(): void
{
    if (!isLoggedIn()) {
        header('Location: ' . SITE_URL . '/admin/login.php');
        exit;
    }
}

/**
 * Rediriger si non admin
 */
function requireAdmin(): void
{
    requireLogin();
    if (!isAdmin()) {
        header('Location: ' . SITE_URL . '/admin/');
        exit;
    }
}

/**
 * Vérifier les tentatives de connexion (anti brute-force)
 */
function checkLoginAttempts(string $ip): bool
{
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = [];
    }

    $now = time();
    // Nettoyer les anciennes tentatives
    $_SESSION['login_attempts'] = array_filter(
        $_SESSION['login_attempts'],
        function ($timestamp) use ($now) {
            return ($now - $timestamp) < LOCKOUT_TIME;
        }
    );

    return count($_SESSION['login_attempts']) < MAX_LOGIN_ATTEMPTS;
}

/**
 * Enregistrer une tentative de connexion
 */
function recordLoginAttempt(): void
{
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = [];
    }
    $_SESSION['login_attempts'][] = time();
}

/**
 * Réinitialiser les tentatives de connexion
 */
function resetLoginAttempts(): void
{
    $_SESSION['login_attempts'] = [];
}

/**
 * Générer un slug sécurisé
 */
function generateSlug(string $text): string
{
    $text = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    $text = trim($text, '-');
    return $text;
}

/**
 * Vérifier le rôle de l'utilisateur
 */
function requireRole(string $role): void
{
    requireLogin();
    if (($_SESSION['user_role'] ?? '') !== $role && $_SESSION['user_role'] !== 'admin') {
        setFlash('error', 'Accès non autorisé.');
        redirect(SITE_URL . '/admin/');
    }
}

/**
 * Gérer l'upload d'un fichier (wrapper simplifié)
 */
function handleUpload(array $file, string $subfolder = 'media', ?array $allowedTypes = null): ?string
{
    if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > MAX_UPLOAD_SIZE) {
        return null;
    }

    $allowedTypes = $allowedTypes ?? ALLOWED_IMAGE_TYPES;
    $destination = UPLOADS_PATH . $subfolder;

    if (!is_dir($destination)) {
        mkdir($destination, 0755, true);
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedTypes)) {
        return null;
    }

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = bin2hex(random_bytes(16)) . '.' . $extension;
    $filepath = $destination . '/' . $filename;

    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return 'uploads/' . $subfolder . '/' . $filename;
    }

    return null;
}

/**
 * Formater la taille d'un fichier
 */
function formatFileSize(int $bytes): string
{
    $units = ['B', 'KB', 'MB', 'GB'];
    $i = 0;
    while ($bytes >= 1024 && $i < count($units) - 1) {
        $bytes /= 1024;
        $i++;
    }
    return round($bytes, 1) . ' ' . $units[$i];
}

/**
 * Upload sécurisé de fichier
 */
function secureUpload(array $file, string $destination, array $allowedTypes): ?string
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    if ($file['size'] > MAX_UPLOAD_SIZE) {
        return null;
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedTypes)) {
        return null;
    }

    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = bin2hex(random_bytes(16)) . '.' . strtolower($extension);
    $filepath = $destination . '/' . $filename;

    if (!is_dir($destination)) {
        mkdir($destination, 0755, true);
    }

    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return $filename;
    }

    return null;
}
