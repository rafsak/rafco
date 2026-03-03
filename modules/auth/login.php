<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';

if (is_authenticated()) {
    redirect('/modules/dashboard/index.php');
}

if (is_post()) {
    if (!csrf_validate('login_form')) {
        flash('error', 'Session expirée ou CSRF invalide.');
        redirect('/modules/auth/login.php');
    }

    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if (!validate_email($email) || $password === '') {
        flash('error', 'Informations de connexion invalides.');
        redirect('/modules/auth/login.php');
    }

    $query = db()->prepare('SELECT id, full_name, email, password_hash, is_active FROM users WHERE email = :email LIMIT 1');
    $query->execute(['email' => $email]);
    $user = $query->fetch();

    if (!$user || (int) $user['is_active'] !== 1 || !password_verify($password, (string) $user['password_hash'])) {
        audit_log(null, 'AUTH_LOGIN_FAILED', 'AUTH', 'users', null, 'Échec de connexion', ['email' => $email]);
        flash('error', 'Email ou mot de passe incorrect.');
        redirect('/modules/auth/login.php');
    }

    login_user($user);
    flash('success', 'Connexion réussie.');
    redirect('/modules/dashboard/index.php');
}

$pageTitle = 'Connexion';
include __DIR__ . '/../../includes/layout/header.php';
?>
<div class="card shadow auth-card">
    <div class="card-body p-4">
        <h1 class="h4 mb-4 text-center">Connexion ERP</h1>
        <form method="post" action="/modules/auth/login.php" novalidate>
            <?= csrf_input('login_form') ?>
            <div class="mb-3">
                <label class="form-label" for="email">Email</label>
                <input class="form-control" type="email" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label class="form-label" for="password">Mot de passe</label>
                <input class="form-control" type="password" id="password" name="password" required>
            </div>
            <button class="btn btn-primary w-100" type="submit">Se connecter</button>
        </form>
    </div>
</div>
<?php include __DIR__ . '/../../includes/layout/footer.php'; ?>
