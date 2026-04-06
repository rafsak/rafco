<?php
/**
 * Radio Mehna V2 - Admin Login
 */
require_once __DIR__ . '/../includes/init.php';

// Rediriger si déjà connecté
if (isLoggedIn()) {
    redirect(SITE_URL . '/admin/');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $error = 'Session invalide.';
    } else {
        $username = sanitize($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $ip = getClientIP();

        if (!checkLoginAttempts($ip)) {
            $error = 'Trop de tentatives. Réessayez plus tard.';
        } elseif (empty($username) || empty($password)) {
            $error = __('admin.login_error');
        } else {
            try {
                $user = dbFetchOne(
                    "SELECT u.*, r.name as role_name FROM users u
                     JOIN roles r ON u.role_id = r.id
                     WHERE (u.username = ? OR u.email = ?) AND u.is_active = 1",
                    [$username, $username]
                );

                if ($user && verifyPassword($password, $user['password'])) {
                    // Connexion réussie
                    resetLoginAttempts();
                    session_regenerate_id(true);

                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['full_name'];
                    $_SESSION['user_role'] = $user['role_name'];
                    $_SESSION['user_email'] = $user['email'];

                    // Mettre à jour la dernière connexion
                    dbUpdate('users', ['last_login' => date('Y-m-d H:i:s')], 'id = ?', [$user['id']]);

                    // Log
                    try {
                        dbInsert('logs', [
                            'user_id'    => $user['id'],
                            'action'     => 'login',
                            'details'    => 'Connexion réussie',
                            'ip_address' => $ip,
                            'created_at' => date('Y-m-d H:i:s'),
                        ]);
                    } catch (Exception $e) {}

                    redirect(SITE_URL . '/admin/');
                } else {
                    recordLoginAttempt();
                    $error = __('admin.login_error');
                }
            } catch (Exception $e) {
                $error = 'Erreur de connexion à la base de données.';
            }
        }
    }
}

$lang = currentLang();
$dir = textDirection();
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $dir ?>" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= __('admin.login') ?> - <?= __('site.name') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Noto+Sans+Arabic:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="<?= ASSETS_URL ?>/css/style.css" rel="stylesheet">
    <?php if (isRTL()): ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <?php endif; ?>
    <style>
        body { padding-bottom: 0; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .login-card { width: 100%; max-width: 420px; }
    </style>
</head>
<body>

<div class="login-card">
    <div class="card-mehna p-5">
        <div class="text-center mb-4">
            <i class="bi bi-broadcast-pin text-accent display-4"></i>
            <h3 class="fw-bold mt-2">Radio <span class="text-accent">Mehna</span></h3>
            <p class="text-muted"><?= __('admin.dashboard') ?></p>
        </div>

        <?php if ($error): ?>
        <div class="alert alert-danger py-2 small"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <?= csrfField() ?>

            <div class="mb-3">
                <label class="form-label text-muted small"><?= __('admin.username') ?></label>
                <div class="input-group">
                    <span class="input-group-text" style="background:var(--bg-tertiary);border-color:var(--border-color);color:var(--text-muted);">
                        <i class="bi bi-person"></i>
                    </span>
                    <input type="text" name="username" class="form-control" required autofocus
                           placeholder="<?= __('admin.username') ?>"
                           style="background:var(--bg-input);border-color:var(--border-color);color:var(--text-primary);">
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label text-muted small"><?= __('admin.password') ?></label>
                <div class="input-group">
                    <span class="input-group-text" style="background:var(--bg-tertiary);border-color:var(--border-color);color:var(--text-muted);">
                        <i class="bi bi-lock"></i>
                    </span>
                    <input type="password" name="password" class="form-control" required
                           placeholder="<?= __('admin.password') ?>"
                           style="background:var(--bg-input);border-color:var(--border-color);color:var(--text-primary);">
                </div>
            </div>

            <button type="submit" class="btn btn-accent w-100 py-2 fw-bold">
                <i class="bi bi-box-arrow-in-right"></i> <?= __('admin.login_btn') ?>
            </button>
        </form>

        <div class="text-center mt-4">
            <a href="<?= SITE_URL ?>/" class="text-muted small">
                <i class="bi bi-arrow-left"></i> Retour au site
            </a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
