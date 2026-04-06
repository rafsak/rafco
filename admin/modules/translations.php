<?php
/**
 * Radio Mehna V2 - Admin Translations Manager
 */
require_once __DIR__ . '/../../includes/init.php';
requireLogin();

$pageTitle = 'Traductions';
$filterLang = getParam('lang', '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        setFlash('error', 'Token CSRF invalide.');
        redirect(SITE_URL . '/admin/modules/translations.php');
    }

    $postAction = postParam('post_action');
    $id = (int)postParam('id', 0);

    if ($postAction === 'delete' && $id > 0) {
        try {
            dbDelete('translations', 'id = ?', [$id]);
            setFlash('success', 'Traduction supprimée.');
        } catch (Exception $e) {
            setFlash('error', 'Erreur.');
        }
        redirect(SITE_URL . '/admin/modules/translations.php');
    }

    $data = [
        'translation_key' => postParam('translation_key'),
        'language'         => postParam('language', 'fr'),
        'value'            => postParam('value'),
        'updated_at'       => date('Y-m-d H:i:s'),
    ];

    try {
        if ($id > 0) {
            dbUpdate('translations', $data, 'id = ?', [$id]);
            setFlash('success', 'Traduction mise à jour.');
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            dbInsert('translations', $data);
            setFlash('success', 'Traduction créée.');
        }
    } catch (Exception $e) {
        setFlash('error', 'Erreur: ' . $e->getMessage());
    }
    redirect(SITE_URL . '/admin/modules/translations.php');
}

$action = getParam('action', 'list');
$id = (int)getParam('id', 0);

if ($action === 'edit' && $id > 0) {
    try { $entry = dbFetchOne("SELECT * FROM translations WHERE id = ?", [$id]); } catch (Exception $e) { $entry = null; }
    if (!$entry) { setFlash('error', 'Non trouvé.'); redirect(SITE_URL . '/admin/modules/translations.php'); }
}

if ($action === 'list') {
    $where = $filterLang ? "WHERE language = ?" : "";
    $params = $filterLang ? [$filterLang] : [];
    try { $translations = dbFetchAll("SELECT * FROM translations $where ORDER BY translation_key, language", $params); } catch (Exception $e) { $translations = []; }
}

require_once __DIR__ . '/../includes/header.php';
?>

<?php if ($action === 'list'): ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold"><i class="bi bi-translate text-accent"></i> Traductions</h3>
        <a href="?action=create" class="btn btn-accent"><i class="bi bi-plus-lg"></i> Nouvelle</a>
    </div>

    <div class="mb-3 d-flex gap-2">
        <a href="?lang=" class="btn btn-sm <?= !$filterLang ? 'btn-accent' : 'btn-outline-accent' ?>">Toutes</a>
        <a href="?lang=fr" class="btn btn-sm <?= $filterLang === 'fr' ? 'btn-accent' : 'btn-outline-accent' ?>">FR</a>
        <a href="?lang=ar" class="btn btn-sm <?= $filterLang === 'ar' ? 'btn-accent' : 'btn-outline-accent' ?>">AR</a>
        <a href="?lang=en" class="btn btn-sm <?= $filterLang === 'en' ? 'btn-accent' : 'btn-outline-accent' ?>">EN</a>
    </div>

    <div class="card-mehna">
        <div class="table-responsive">
            <table class="table table-borderless mb-0">
                <thead><tr style="color:var(--text-muted);">
                    <th>Clé</th><th>Langue</th><th>Valeur</th><th>Actions</th>
                </tr></thead>
                <tbody>
                <?php foreach ($translations as $t): ?>
                <tr style="color:var(--text-secondary);">
                    <td class="fw-semibold" style="color:var(--text-primary);"><?= e($t['translation_key']) ?></td>
                    <td><?= langFlag($t['language']) ?></td>
                    <td><?= truncateText(e($t['value']), 80) ?></td>
                    <td>
                        <a href="?action=edit&id=<?= $t['id'] ?>" class="btn btn-sm btn-outline-accent"><i class="bi bi-pencil"></i></a>
                        <form method="POST" class="d-inline">
                            <?= csrfField() ?>
                            <input type="hidden" name="post_action" value="delete">
                            <input type="hidden" name="id" value="<?= $t['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger" data-confirm="Supprimer ?"><i class="bi bi-trash"></i></button>
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
        <h3 class="fw-bold"><?= $action === 'edit' ? 'Modifier' : 'Nouvelle' ?> traduction</h3>
        <a href="?action=list" class="btn btn-outline-accent"><i class="bi bi-arrow-left"></i> Retour</a>
    </div>

    <div class="card-mehna p-4">
        <form method="POST" action="?action=<?= $action ?>&id=<?= $id ?>">
            <?= csrfField() ?>
            <input type="hidden" name="id" value="<?= $id ?>">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Clé de traduction</label>
                    <input type="text" name="translation_key" class="form-control" required value="<?= e($entry['translation_key'] ?? '') ?>"
                           placeholder="section.key_name">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Langue</label>
                    <select name="language" class="form-select">
                        <option value="fr" <?= ($entry['language'] ?? 'fr') === 'fr' ? 'selected' : '' ?>>Français</option>
                        <option value="ar" <?= ($entry['language'] ?? '') === 'ar' ? 'selected' : '' ?>>العربية</option>
                        <option value="en" <?= ($entry['language'] ?? '') === 'en' ? 'selected' : '' ?>>English</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Valeur</label>
                    <textarea name="value" class="form-control" rows="3" required><?= e($entry['value'] ?? '') ?></textarea>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-accent"><i class="bi bi-check-lg"></i> Enregistrer</button>
                </div>
            </div>
        </form>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
