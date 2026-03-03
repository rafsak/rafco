<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_permission('AUTH_MANAGE_USERS');

$roles = db()->query('SELECT id, role_name FROM roles ORDER BY role_name')->fetchAll();

if (is_post()) {
    if (!csrf_validate('create_user_form')) {
        flash('error', 'CSRF invalide.');
        redirect('/modules/users/create.php');
    }

    $fullName = trim((string) ($_POST['full_name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $selectedRoles = $_POST['roles'] ?? [];

    if ($fullName === '' || !validate_email($email)) {
        flash('error', 'Nom et email valides obligatoires.');
        redirect('/modules/users/create.php');
    }

    $passwordErrors = validate_password_policy($password);
    if ($passwordErrors) {
        flash('error', implode(' ', $passwordErrors));
        redirect('/modules/users/create.php');
    }

    $pdo = db();
    $pdo->beginTransaction();
    try {
        $insertUser = $pdo->prepare('INSERT INTO users (full_name, email, password_hash, is_active) VALUES (:full_name, :email, :password_hash, 1)');
        $insertUser->execute([
            'full_name' => $fullName,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_BCRYPT),
        ]);

        $newUserId = (int) $pdo->lastInsertId();

        if (is_array($selectedRoles)) {
            $insertRole = $pdo->prepare('INSERT INTO user_roles (user_id, role_id) VALUES (:user_id, :role_id)');
            foreach ($selectedRoles as $roleId) {
                $insertRole->execute([
                    'user_id' => $newUserId,
                    'role_id' => (int) $roleId,
                ]);
            }
        }

        $pdo->commit();

        $current = current_user();
        audit_log((int) $current['id'], 'USER_CREATE', 'AUTH', 'users', $newUserId, 'Création utilisateur', [
            'created_email' => $email,
            'roles' => $selectedRoles,
        ]);

        flash('success', 'Utilisateur créé avec succès.');
        redirect('/modules/users/index.php');
    } catch (Throwable $e) {
        $pdo->rollBack();
        flash('error', 'Erreur lors de la création de l\'utilisateur.');
        redirect('/modules/users/create.php');
    }
}

$pageTitle = 'Créer utilisateur';
include __DIR__ . '/../../includes/layout/header.php';
?>
<h1 class="h3 mb-3">Créer un utilisateur</h1>
<div class="card shadow-sm">
    <div class="card-body">
        <form method="post" action="/modules/users/create.php">
            <?= csrf_input('create_user_form') ?>
            <div class="mb-3">
                <label class="form-label">Nom complet</label>
                <input type="text" name="full_name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Mot de passe</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Rôles</label>
                <?php foreach ($roles as $role): ?>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="roles[]" value="<?= (int) $role['id'] ?>" id="role_<?= (int) $role['id'] ?>">
                        <label class="form-check-label" for="role_<?= (int) $role['id'] ?>"><?= e($role['role_name']) ?></label>
                    </div>
                <?php endforeach; ?>
            </div>
            <button class="btn btn-primary" type="submit">Enregistrer</button>
            <a class="btn btn-secondary" href="/modules/users/index.php">Annuler</a>
        </form>
    </div>
</div>
<?php include __DIR__ . '/../../includes/layout/footer.php'; ?>
