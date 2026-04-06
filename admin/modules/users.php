<?php
/**
 * Radio Mehna V2 - Admin Users CRUD
 */
require_once __DIR__ . '/../../includes/init.php';
requireLogin();
requireRole('admin');

$action = getParam('action', 'list');
$id = (int)getParam('id', 0);
$pageTitle = 'Utilisateurs';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        setFlash('error', 'Token CSRF invalide.');
        redirect(SITE_URL . '/admin/modules/users.php');
    }

    $postAction = postParam('post_action');

    if ($postAction === 'delete' && $id > 0) {
        if ($id == ($_SESSION['user_id'] ?? 0)) {
            setFlash('error', 'Impossible de supprimer votre propre compte.');
        } else {
            try {
                dbDelete('users', 'id = ?', [$id]);
                setFlash('success', 'Utilisateur supprimé.');
            } catch (Exception $e) {
                setFlash('error', 'Erreur lors de la suppression.');
            }
        }
        redirect(SITE_URL . '/admin/modules/users.php');
    }

    $data = [
        'username'  => postParam('username'),
        'email'     => sanitizeEmail($_POST['email'] ?? ''),
        'full_name' => postParam('full_name'),
        'role_id'   => (int)postParam('role_id', 2),
        'is_active' => (int)($_POST['is_active'] ?? 1),
        'updated_at' => date('Y-m-d H:i:s'),
    ];

    $password = $_POST['password'] ?? '';
    if (!empty($password)) {
        $data['password'] = hashPassword($password);
    }

    if (!empty($_FILES['avatar']['name'])) {
        $upload = handleUpload($_FILES['avatar'], 'avatars');
        if ($upload) $data['avatar'] = $upload;
    }

    try {
        if ($id > 0) {
            dbUpdate('users', $data, 'id = ?', [$id]);
            setFlash('success', 'Utilisateur mis à jour.');
        } else {
            if (empty($password)) {
                setFlash('error', 'Le mot de passe est requis.');
                redirect(SITE_URL . '/admin/modules/users.php?action=create');
            }
            $data['created_at'] = date('Y-m-d H:i:s');
            dbInsert('users', $data);
            setFlash('success', 'Utilisateur créé.');
        }
    } catch (Exception $e) {
        setFlash('error', 'Erreur: ' . $e->getMessage());
    }
    redirect(SITE_URL . '/admin/modules/users.php');
}

if ($action === 'edit' && $id > 0) {
    try { $user = dbFetchOne("SELECT u.*, r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = ?", [$id]); } catch (Exception $e) { $user = null; }
    if (!$user) { setFlash('error', 'Utilisateur non trouvé.'); redirect(SITE_URL . '/admin/modules/users.php'); }
}

if ($action === 'list') {
    try { $users = dbFetchAll("SELECT u.*, r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id ORDER BY u.created_at DESC"); } catch (Exception $e) { $users = []; }
}

try { $roles = dbFetchAll("SELECT * FROM roles ORDER BY id"); } catch (Exception $e) { $roles = []; }

require_once __DIR__ . '/../includes/header.php';
?>

<?php if ($action === 'list'): ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold"><i class="bi bi-people text-accent"></i> Utilisateurs</h3>
        <a href="?action=create" class="btn btn-accent"><i class="bi bi-plus-lg"></i> Nouveau</a>
    </div>

    <div class="card-mehna">
        <div class="table-responsive">
            <table class="table table-borderless mb-0">
                <thead><tr style="color:var(--text-muted);">
                    <th>Nom</th><th>Email</th><th>Rôle</th><th>Statut</th><th>Dernière connexion</th><th>Actions</th>
                </tr></thead>
                <tbody>
                <?php foreach ($users as $u): ?>
                <tr style="color:var(--text-secondary);">
                    <td class="fw-semibold" style="color:var(--text-primary);"><?= e($u['full_name'] ?? $u['username']) ?></td>
                    <td><?= e($u['email']) ?></td>
                    <td><span class="badge bg-info"><?= e($u['role_name']) ?></span></td>
                    <td><span class="badge <?= $u['is_active'] ? 'bg-success' : 'bg-secondary' ?>"><?= $u['is_active'] ? 'Actif' : 'Inactif' ?></span></td>
                    <td><?= $u['last_login'] ? timeAgo($u['last_login']) : '-' ?></td>
                    <td>
                        <a href="?action=edit&id=<?= $u['id'] ?>" class="btn btn-sm btn-outline-accent"><i class="bi bi-pencil"></i></a>
                        <?php if ($u['id'] != ($_SESSION['user_id'] ?? 0)): ?>
                        <form method="POST" action="?id=<?= $u['id'] ?>" class="d-inline">
                            <?= csrfField() ?>
                            <input type="hidden" name="post_action" value="delete">
                            <button type="submit" class="btn btn-sm btn-outline-danger" data-confirm="Supprimer cet utilisateur ?"><i class="bi bi-trash"></i></button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php elseif ($action === 'create' || $action === 'edit'): ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold"><?= $action === 'edit' ? 'Modifier' : 'Nouvel' ?> utilisateur</h3>
        <a href="?action=list" class="btn btn-outline-accent"><i class="bi bi-arrow-left"></i> Retour</a>
    </div>

    <div class="card-mehna p-4">
        <form method="POST" enctype="multipart/form-data" action="?action=<?= $action ?>&id=<?= $id ?>">
            <?= csrfField() ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nom d'utilisateur</label>
                    <input type="text" name="username" class="form-control" required value="<?= e($user['username'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required value="<?= e($user['email'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nom complet</label>
                    <input type="text" name="full_name" class="form-control" value="<?= e($user['full_name'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Mot de passe <?= $action === 'edit' ? '(laisser vide pour garder)' : '' ?></label>
                    <input type="password" name="password" class="form-control" <?= $action === 'create' ? 'required' : '' ?>>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Rôle</label>
                    <select name="role_id" class="form-select">
                        <?php foreach ($roles as $r): ?>
                        <option value="<?= $r['id'] ?>" <?= ($user['role_id'] ?? 2) == $r['id'] ? 'selected' : '' ?>><?= e(ucfirst($r['name'])) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Statut</label>
                    <select name="is_active" class="form-select">
                        <option value="1" <?= ($user['is_active'] ?? 1) ? 'selected' : '' ?>>Actif</option>
                        <option value="0" <?= !($user['is_active'] ?? 1) ? 'selected' : '' ?>>Inactif</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Avatar</label>
                    <input type="file" name="avatar" class="form-control" accept="image/*">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-accent"><i class="bi bi-check-lg"></i> Enregistrer</button>
                </div>
            </div>
        </form>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
