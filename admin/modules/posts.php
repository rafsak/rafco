<?php
/**
 * Radio Mehna V2 - Admin Posts CRUD
 */
require_once __DIR__ . '/../../includes/init.php';
requireLogin();

$action = getParam('action', 'list');
$id = (int)getParam('id', 0);
$pageTitle = __('nav.news');

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        setFlash('error', 'Token CSRF invalide.');
        redirect(SITE_URL . '/admin/modules/posts.php');
    }

    $postAction = postParam('post_action');

    if ($postAction === 'delete' && $id > 0) {
        try {
            dbDelete('posts', 'id = ?', [$id]);
            setFlash('success', 'Article supprimé.');
        } catch (Exception $e) {
            setFlash('error', 'Erreur lors de la suppression.');
        }
        redirect(SITE_URL . '/admin/modules/posts.php');
    }

    // Create or Update
    $data = [
        'title'        => postParam('title'),
        'slug'         => generateSlug(postParam('title')),
        'excerpt'      => postParam('excerpt'),
        'content'      => $_POST['content'] ?? '',
        'status'       => postParam('status', 'draft'),
        'author_id'    => $_SESSION['user_id'] ?? 1,
        'updated_at'   => date('Y-m-d H:i:s'),
    ];

    if ($data['status'] === 'published' && empty($_POST['published_at'])) {
        $data['published_at'] = date('Y-m-d H:i:s');
    }

    // Image upload
    if (!empty($_FILES['image']['name'])) {
        $upload = handleUpload($_FILES['image'], 'media');
        if ($upload) $data['image'] = $upload;
    }

    try {
        if ($id > 0) {
            dbUpdate('posts', $data, 'id = ?', [$id]);
            setFlash('success', 'Article mis à jour.');
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            dbInsert('posts', $data);
            setFlash('success', 'Article créé.');
        }
    } catch (Exception $e) {
        setFlash('error', 'Erreur: ' . $e->getMessage());
    }
    redirect(SITE_URL . '/admin/modules/posts.php');
}

// Load data
if ($action === 'edit' && $id > 0) {
    try { $post = dbFetchOne("SELECT * FROM posts WHERE id = ?", [$id]); } catch (Exception $e) { $post = null; }
    if (!$post) { setFlash('error', 'Article non trouvé.'); redirect(SITE_URL . '/admin/modules/posts.php'); }
}

if ($action === 'list') {
    try { $posts = getAllPosts(100); } catch (Exception $e) { $posts = []; }
}

require_once __DIR__ . '/../includes/header.php';
?>

<?php if ($action === 'list'): ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold"><i class="bi bi-newspaper text-accent"></i> Articles</h3>
        <a href="?action=create" class="btn btn-accent"><i class="bi bi-plus-lg"></i> Nouveau</a>
    </div>

    <div class="card-mehna">
        <div class="table-responsive">
            <table class="table table-borderless mb-0">
                <thead><tr style="color:var(--text-muted);">
                    <th>Titre</th><th>Statut</th><th>Vues</th><th>Date</th><th>Actions</th>
                </tr></thead>
                <tbody>
                <?php foreach ($posts as $p): ?>
                <tr style="color:var(--text-secondary);">
                    <td class="fw-semibold" style="color:var(--text-primary);"><?= e($p['title']) ?></td>
                    <td><span class="badge <?= $p['status'] === 'published' ? 'bg-success' : 'bg-secondary' ?>"><?= e($p['status']) ?></span></td>
                    <td><?= number_format($p['views'] ?? 0) ?></td>
                    <td><?= timeAgo($p['created_at']) ?></td>
                    <td>
                        <a href="?action=edit&id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-accent"><i class="bi bi-pencil"></i></a>
                        <form method="POST" action="?id=<?= $p['id'] ?>" class="d-inline">
                            <?= csrfField() ?>
                            <input type="hidden" name="post_action" value="delete">
                            <button type="submit" class="btn btn-sm btn-outline-danger" data-confirm="Supprimer cet article ?"><i class="bi bi-trash"></i></button>
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
        <h3 class="fw-bold"><?= $action === 'edit' ? 'Modifier' : 'Nouvel' ?> article</h3>
        <a href="?action=list" class="btn btn-outline-accent"><i class="bi bi-arrow-left"></i> Retour</a>
    </div>

    <div class="card-mehna p-4">
        <form method="POST" enctype="multipart/form-data" action="?action=<?= $action ?>&id=<?= $id ?>">
            <?= csrfField() ?>
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">Titre</label>
                    <input type="text" name="title" class="form-control" required value="<?= e($post['title'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Statut</label>
                    <select name="status" class="form-select">
                        <option value="draft" <?= ($post['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Brouillon</option>
                        <option value="published" <?= ($post['status'] ?? '') === 'published' ? 'selected' : '' ?>>Publié</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Extrait</label>
                    <textarea name="excerpt" class="form-control" rows="2"><?= e($post['excerpt'] ?? '') ?></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Contenu</label>
                    <textarea name="content" class="form-control" rows="12"><?= e($post['content'] ?? '') ?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Image</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                    <?php if (!empty($post['image'])): ?>
                    <small class="text-muted">Image actuelle: <?= e($post['image']) ?></small>
                    <?php endif; ?>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-accent"><i class="bi bi-check-lg"></i> Enregistrer</button>
                </div>
            </div>
        </form>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
