<?php
/**
 * Radio Mehna V2 - Admin Media Manager
 */
require_once __DIR__ . '/../../includes/init.php';
requireLogin();

$pageTitle = 'Médiathèque';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        setFlash('error', 'Token CSRF invalide.');
        redirect(SITE_URL . '/admin/modules/media.php');
    }

    $postAction = postParam('post_action');
    $id = (int)postParam('id', 0);

    if ($postAction === 'delete' && $id > 0) {
        try {
            $media = dbFetchOne("SELECT * FROM media WHERE id = ?", [$id]);
            if ($media && !empty($media['file_path'])) {
                $filePath = ROOT_PATH . '/uploads/' . $media['file_path'];
                if (file_exists($filePath)) unlink($filePath);
            }
            dbDelete('media', 'id = ?', [$id]);
            setFlash('success', 'Média supprimé.');
        } catch (Exception $e) {
            setFlash('error', 'Erreur lors de la suppression.');
        }
        redirect(SITE_URL . '/admin/modules/media.php');
    }

    // Upload
    if (!empty($_FILES['files']['name'][0])) {
        $uploaded = 0;
        $errors = 0;
        $fileCount = count($_FILES['files']['name']);

        for ($i = 0; $i < $fileCount; $i++) {
            $file = [
                'name'     => $_FILES['files']['name'][$i],
                'type'     => $_FILES['files']['type'][$i],
                'tmp_name' => $_FILES['files']['tmp_name'][$i],
                'error'    => $_FILES['files']['error'][$i],
                'size'     => $_FILES['files']['size'][$i],
            ];

            $upload = handleUpload($file, 'media');
            if ($upload) {
                try {
                    dbInsert('media', [
                        'title'      => pathinfo($file['name'], PATHINFO_FILENAME),
                        'file_path'  => $upload,
                        'file_type'  => $file['type'],
                        'file_size'  => $file['size'],
                        'uploaded_by' => $_SESSION['user_id'] ?? 1,
                        'created_at' => date('Y-m-d H:i:s'),
                    ]);
                    $uploaded++;
                } catch (Exception $e) {
                    $errors++;
                }
            } else {
                $errors++;
            }
        }
        setFlash('success', "$uploaded fichier(s) uploadé(s)" . ($errors ? ", $errors erreur(s)" : ''));
        redirect(SITE_URL . '/admin/modules/media.php');
    }
}

try {
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = 24;
    $total = (int)dbFetchOne("SELECT COUNT(*) as c FROM media")['c'];
    $offset = ($page - 1) * $perPage;
    $mediaList = dbFetchAll("SELECT m.*, u.full_name as uploader FROM media m LEFT JOIN users u ON m.uploaded_by = u.id ORDER BY m.created_at DESC LIMIT $perPage OFFSET $offset");
} catch (Exception $e) {
    $mediaList = [];
    $total = 0;
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold"><i class="bi bi-images text-accent"></i> Médiathèque</h3>
    <button class="btn btn-accent" data-bs-toggle="modal" data-bs-target="#uploadModal">
        <i class="bi bi-cloud-arrow-up"></i> Upload
    </button>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card-mehna p-3 text-center">
            <h4 class="fw-bold mb-0"><?= $total ?></h4>
            <small class="text-muted">Fichiers</small>
        </div>
    </div>
</div>

<!-- Media Grid -->
<div class="row g-3">
    <?php foreach ($mediaList as $m): ?>
    <div class="col-lg-2 col-md-3 col-4">
        <div class="card-mehna p-2" style="position:relative;">
            <?php if (str_starts_with($m['file_type'] ?? '', 'image/')): ?>
            <img src="<?= mediaUrl($m['file_path']) ?>" alt="<?= e($m['title']) ?>"
                 style="width:100%;height:120px;object-fit:cover;border-radius:var(--radius-sm);">
            <?php else: ?>
            <div style="width:100%;height:120px;background:var(--bg-tertiary);border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;">
                <i class="bi bi-file-earmark fs-1 text-muted"></i>
            </div>
            <?php endif; ?>
            <div class="mt-2">
                <small class="text-truncate d-block" style="color:var(--text-primary);"><?= e($m['title']) ?></small>
                <small class="text-muted"><?= formatFileSize($m['file_size'] ?? 0) ?></small>
            </div>
            <form method="POST" class="d-inline" style="position:absolute;top:8px;right:8px;">
                <?= csrfField() ?>
                <input type="hidden" name="post_action" value="delete">
                <input type="hidden" name="id" value="<?= $m['id'] ?>">
                <button type="submit" class="btn btn-sm btn-danger" data-confirm="Supprimer ?"
                        style="padding:2px 6px;font-size:0.7rem;"><i class="bi bi-trash"></i></button>
            </form>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="background:var(--bg-card);border-color:var(--border-color);">
            <div class="modal-header border-0">
                <h5 class="modal-title">Upload de fichiers</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" enctype="multipart/form-data">
                    <?= csrfField() ?>
                    <div class="mb-3">
                        <label class="form-label">Fichiers</label>
                        <input type="file" name="files[]" class="form-control" multiple accept="image/*,audio/*,video/*">
                        <small class="text-muted">Max <?= MAX_UPLOAD_SIZE / 1048576 ?> MB par fichier</small>
                    </div>
                    <button type="submit" class="btn btn-accent w-100">
                        <i class="bi bi-cloud-arrow-up"></i> Uploader
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
