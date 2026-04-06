<?php
/**
 * Radio Mehna V2 - Admin Podcasts CRUD
 */
require_once __DIR__ . '/../../includes/init.php';
requireLogin();

$action = getParam('action', 'list');
$id = (int)getParam('id', 0);
$pageTitle = __('nav.podcasts');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        setFlash('error', 'Token CSRF invalide.');
        redirect(SITE_URL . '/admin/modules/podcasts.php');
    }

    $postAction = postParam('post_action');

    if ($postAction === 'delete' && $id > 0) {
        try {
            dbDelete('podcasts', 'id = ?', [$id]);
            setFlash('success', 'Podcast supprimé.');
        } catch (Exception $e) {
            setFlash('error', 'Erreur lors de la suppression.');
        }
        redirect(SITE_URL . '/admin/modules/podcasts.php');
    }

    $data = [
        'title'        => postParam('title'),
        'slug'         => generateSlug(postParam('title')),
        'description'  => postParam('description'),
        'category'     => postParam('category'),
        'program_id'   => ((int)postParam('program_id')) ?: null,
        'duration'     => (int)postParam('duration', 0),
        'is_published' => (int)($_POST['is_published'] ?? 1),
        'updated_at'   => date('Y-m-d H:i:s'),
    ];

    if ($data['is_published'] && empty($_POST['published_at'])) {
        $data['published_at'] = date('Y-m-d H:i:s');
    }

    if (!empty($_FILES['audio_file']['name'])) {
        $upload = handleUpload($_FILES['audio_file'], 'podcasts', ['audio/mpeg', 'audio/mp3', 'audio/wav', 'audio/ogg']);
        if ($upload) $data['audio_file'] = $upload;
    }
    if (!empty($_FILES['image']['name'])) {
        $upload = handleUpload($_FILES['image'], 'media');
        if ($upload) $data['image'] = $upload;
    }

    try {
        if ($id > 0) {
            dbUpdate('podcasts', $data, 'id = ?', [$id]);
            setFlash('success', 'Podcast mis à jour.');
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            dbInsert('podcasts', $data);
            setFlash('success', 'Podcast créé.');
        }
    } catch (Exception $e) {
        setFlash('error', 'Erreur: ' . $e->getMessage());
    }
    redirect(SITE_URL . '/admin/modules/podcasts.php');
}

if ($action === 'edit' && $id > 0) {
    try { $podcast = dbFetchOne("SELECT * FROM podcasts WHERE id = ?", [$id]); } catch (Exception $e) { $podcast = null; }
    if (!$podcast) { setFlash('error', 'Podcast non trouvé.'); redirect(SITE_URL . '/admin/modules/podcasts.php'); }
}

if ($action === 'list') {
    try { $podcasts = dbFetchAll("SELECT p.*, pr.title as program_title FROM podcasts p LEFT JOIN programs pr ON p.program_id = pr.id ORDER BY p.created_at DESC"); } catch (Exception $e) { $podcasts = []; }
}

try { $allPrograms = dbFetchAll("SELECT id, title FROM programs WHERE is_active = 1 ORDER BY title"); } catch (Exception $e) { $allPrograms = []; }

require_once __DIR__ . '/../includes/header.php';
?>

<?php if ($action === 'list'): ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold"><i class="bi bi-headphones text-accent"></i> Podcasts</h3>
        <a href="?action=create" class="btn btn-accent"><i class="bi bi-plus-lg"></i> Nouveau</a>
    </div>

    <div class="card-mehna">
        <div class="table-responsive">
            <table class="table table-borderless mb-0">
                <thead><tr style="color:var(--text-muted);">
                    <th>Titre</th><th>Catégorie</th><th>Émission</th><th>Durée</th><th>Plays</th><th>Actions</th>
                </tr></thead>
                <tbody>
                <?php foreach ($podcasts as $p): ?>
                <tr style="color:var(--text-secondary);">
                    <td class="fw-semibold" style="color:var(--text-primary);"><?= e($p['title']) ?></td>
                    <td><?= e($p['category'] ?? '-') ?></td>
                    <td><?= e($p['program_title'] ?? '-') ?></td>
                    <td><?= formatDuration($p['duration'] ?? 0) ?></td>
                    <td><?= number_format($p['play_count'] ?? 0) ?></td>
                    <td>
                        <a href="?action=edit&id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-accent"><i class="bi bi-pencil"></i></a>
                        <form method="POST" action="?id=<?= $p['id'] ?>" class="d-inline">
                            <?= csrfField() ?>
                            <input type="hidden" name="post_action" value="delete">
                            <button type="submit" class="btn btn-sm btn-outline-danger" data-confirm="Supprimer ce podcast ?"><i class="bi bi-trash"></i></button>
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
        <h3 class="fw-bold"><?= $action === 'edit' ? 'Modifier' : 'Nouveau' ?> podcast</h3>
        <a href="?action=list" class="btn btn-outline-accent"><i class="bi bi-arrow-left"></i> Retour</a>
    </div>

    <div class="card-mehna p-4">
        <form method="POST" enctype="multipart/form-data" action="?action=<?= $action ?>&id=<?= $id ?>">
            <?= csrfField() ?>
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">Titre</label>
                    <input type="text" name="title" class="form-control" required value="<?= e($podcast['title'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Statut</label>
                    <select name="is_published" class="form-select">
                        <option value="1" <?= ($podcast['is_published'] ?? 1) ? 'selected' : '' ?>>Publié</option>
                        <option value="0" <?= !($podcast['is_published'] ?? 1) ? 'selected' : '' ?>>Brouillon</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="4"><?= e($podcast['description'] ?? '') ?></textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Catégorie</label>
                    <input type="text" name="category" class="form-control" value="<?= e($podcast['category'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Émission liée</label>
                    <select name="program_id" class="form-select">
                        <option value="">-- Aucune --</option>
                        <?php foreach ($allPrograms as $prog): ?>
                        <option value="<?= $prog['id'] ?>" <?= ($podcast['program_id'] ?? 0) == $prog['id'] ? 'selected' : '' ?>><?= e($prog['title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Durée (secondes)</label>
                    <input type="number" name="duration" class="form-control" value="<?= e($podcast['duration'] ?? 0) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Fichier audio (MP3)</label>
                    <input type="file" name="audio_file" class="form-control" accept="audio/*">
                    <?php if (!empty($podcast['audio_file'])): ?>
                    <small class="text-muted">Fichier: <?= e($podcast['audio_file']) ?></small>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Image</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-accent"><i class="bi bi-check-lg"></i> Enregistrer</button>
                </div>
            </div>
        </form>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
