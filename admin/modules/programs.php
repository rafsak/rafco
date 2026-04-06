<?php
/**
 * Radio Mehna V2 - Admin Programs CRUD
 */
require_once __DIR__ . '/../../includes/init.php';
requireLogin();

$action = getParam('action', 'list');
$id = (int)getParam('id', 0);
$pageTitle = __('nav.programs');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        setFlash('error', 'Token CSRF invalide.');
        redirect(SITE_URL . '/admin/modules/programs.php');
    }

    $postAction = postParam('post_action');

    if ($postAction === 'delete' && $id > 0) {
        try {
            dbDelete('programs', 'id = ?', [$id]);
            setFlash('success', 'Émission supprimée.');
        } catch (Exception $e) {
            setFlash('error', 'Erreur lors de la suppression.');
        }
        redirect(SITE_URL . '/admin/modules/programs.php');
    }

    $data = [
        'title'          => postParam('title'),
        'slug'           => generateSlug(postParam('title')),
        'description'    => postParam('description'),
        'host_name'      => postParam('host_name'),
        'host_bio'       => postParam('host_bio'),
        'start_time'     => postParam('start_time'),
        'end_time'       => postParam('end_time'),
        'broadcast_days' => postParam('broadcast_days'),
        'is_active'      => (int)($_POST['is_active'] ?? 1),
        'updated_at'     => date('Y-m-d H:i:s'),
    ];

    if (!empty($_FILES['image']['name'])) {
        $upload = handleUpload($_FILES['image'], 'media');
        if ($upload) $data['image'] = $upload;
    }
    if (!empty($_FILES['host_avatar']['name'])) {
        $upload = handleUpload($_FILES['host_avatar'], 'avatars');
        if ($upload) $data['host_avatar'] = $upload;
    }

    try {
        if ($id > 0) {
            dbUpdate('programs', $data, 'id = ?', [$id]);
            setFlash('success', 'Émission mise à jour.');
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            dbInsert('programs', $data);
            setFlash('success', 'Émission créée.');
        }
    } catch (Exception $e) {
        setFlash('error', 'Erreur: ' . $e->getMessage());
    }
    redirect(SITE_URL . '/admin/modules/programs.php');
}

if ($action === 'edit' && $id > 0) {
    try { $program = dbFetchOne("SELECT * FROM programs WHERE id = ?", [$id]); } catch (Exception $e) { $program = null; }
    if (!$program) { setFlash('error', 'Émission non trouvée.'); redirect(SITE_URL . '/admin/modules/programs.php'); }
}

if ($action === 'list') {
    try { $programs = dbFetchAll("SELECT * FROM programs ORDER BY start_time ASC"); } catch (Exception $e) { $programs = []; }
}

require_once __DIR__ . '/../includes/header.php';
?>

<?php if ($action === 'list'): ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold"><i class="bi bi-broadcast text-accent"></i> Émissions</h3>
        <a href="?action=create" class="btn btn-accent"><i class="bi bi-plus-lg"></i> Nouvelle</a>
    </div>

    <div class="card-mehna">
        <div class="table-responsive">
            <table class="table table-borderless mb-0">
                <thead><tr style="color:var(--text-muted);">
                    <th>Émission</th><th>Animateur</th><th>Horaire</th><th>Jours</th><th>Statut</th><th>Actions</th>
                </tr></thead>
                <tbody>
                <?php foreach ($programs as $p): ?>
                <tr style="color:var(--text-secondary);">
                    <td class="fw-semibold" style="color:var(--text-primary);"><?= e($p['title']) ?></td>
                    <td><?= e($p['host_name'] ?? '') ?></td>
                    <td><?= e(substr($p['start_time'] ?? '', 0, 5)) ?> - <?= e(substr($p['end_time'] ?? '', 0, 5)) ?></td>
                    <td><small><?= e($p['broadcast_days'] ?? '') ?></small></td>
                    <td><span class="badge <?= $p['is_active'] ? 'bg-success' : 'bg-secondary' ?>"><?= $p['is_active'] ? 'Actif' : 'Inactif' ?></span></td>
                    <td>
                        <a href="?action=edit&id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-accent"><i class="bi bi-pencil"></i></a>
                        <form method="POST" action="?id=<?= $p['id'] ?>" class="d-inline">
                            <?= csrfField() ?>
                            <input type="hidden" name="post_action" value="delete">
                            <button type="submit" class="btn btn-sm btn-outline-danger" data-confirm="Supprimer cette émission ?"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php elseif ($action === 'create' || $action === 'edit'): ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold"><?= $action === 'edit' ? 'Modifier' : 'Nouvelle' ?> émission</h3>
        <a href="?action=list" class="btn btn-outline-accent"><i class="bi bi-arrow-left"></i> Retour</a>
    </div>

    <div class="card-mehna p-4">
        <form method="POST" enctype="multipart/form-data" action="?action=<?= $action ?>&id=<?= $id ?>">
            <?= csrfField() ?>
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">Titre</label>
                    <input type="text" name="title" class="form-control" required value="<?= e($program['title'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Statut</label>
                    <select name="is_active" class="form-select">
                        <option value="1" <?= ($program['is_active'] ?? 1) ? 'selected' : '' ?>>Actif</option>
                        <option value="0" <?= !($program['is_active'] ?? 1) ? 'selected' : '' ?>>Inactif</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="4"><?= e($program['description'] ?? '') ?></textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Animateur</label>
                    <input type="text" name="host_name" class="form-control" value="<?= e($program['host_name'] ?? '') ?>">
                </div>
                <div class="col-md-8">
                    <label class="form-label">Bio animateur</label>
                    <input type="text" name="host_bio" class="form-control" value="<?= e($program['host_bio'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Début</label>
                    <input type="time" name="start_time" class="form-control" value="<?= e($program['start_time'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Fin</label>
                    <input type="time" name="end_time" class="form-control" value="<?= e($program['end_time'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Jours de diffusion</label>
                    <input type="text" name="broadcast_days" class="form-control" placeholder="Lun-Ven" value="<?= e($program['broadcast_days'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Image émission</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Avatar animateur</label>
                    <input type="file" name="host_avatar" class="form-control" accept="image/*">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-accent"><i class="bi bi-check-lg"></i> Enregistrer</button>
                </div>
            </div>
        </form>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
