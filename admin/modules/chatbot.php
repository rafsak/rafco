<?php
/**
 * Radio Mehna V2 - Admin Chatbot IA Panel
 */
require_once __DIR__ . '/../../includes/init.php';
requireLogin();

$action = getParam('action', 'list');
$id = (int)getParam('id', 0);
$pageTitle = 'Chatbot IA';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        setFlash('error', 'Token CSRF invalide.');
        redirect(SITE_URL . '/admin/modules/chatbot.php');
    }

    $postAction = postParam('post_action');

    if ($postAction === 'delete' && $id > 0) {
        try {
            dbDelete('chatbot_data', 'id = ?', [$id]);
            setFlash('success', 'Réponse supprimée.');
        } catch (Exception $e) {
            setFlash('error', 'Erreur lors de la suppression.');
        }
        redirect(SITE_URL . '/admin/modules/chatbot.php');
    }

    $data = [
        'question'   => postParam('question'),
        'answer'     => postParam('answer'),
        'keywords'   => postParam('keywords'),
        'category'   => postParam('category'),
        'language'   => postParam('language', 'fr'),
        'is_active'  => (int)($_POST['is_active'] ?? 1),
        'updated_at' => date('Y-m-d H:i:s'),
    ];

    try {
        if ($id > 0) {
            dbUpdate('chatbot_data', $data, 'id = ?', [$id]);
            setFlash('success', 'Réponse mise à jour.');
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            dbInsert('chatbot_data', $data);
            setFlash('success', 'Réponse créée.');
        }
    } catch (Exception $e) {
        setFlash('error', 'Erreur: ' . $e->getMessage());
    }
    redirect(SITE_URL . '/admin/modules/chatbot.php');
}

if ($action === 'edit' && $id > 0) {
    try { $entry = dbFetchOne("SELECT * FROM chatbot_data WHERE id = ?", [$id]); } catch (Exception $e) { $entry = null; }
    if (!$entry) { setFlash('error', 'Entrée non trouvée.'); redirect(SITE_URL . '/admin/modules/chatbot.php'); }
}

if ($action === 'list') {
    try { $entries = dbFetchAll("SELECT * FROM chatbot_data ORDER BY language, category, id"); } catch (Exception $e) { $entries = []; }
}

require_once __DIR__ . '/../includes/header.php';
?>

<?php if ($action === 'list'): ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold"><i class="bi bi-robot text-accent"></i> Chatbot IA - Réponses</h3>
        <a href="?action=create" class="btn btn-accent"><i class="bi bi-plus-lg"></i> Nouvelle réponse</a>
    </div>

    <!-- Info Card -->
    <div class="card-mehna p-3 mb-4" style="border-left:3px solid var(--accent);">
        <small class="text-muted">
            <i class="bi bi-info-circle"></i> Le chatbot utilise la correspondance floue (Levenshtein) pour trouver les meilleures réponses.
            Ajoutez des mots-clés séparés par des virgules pour améliorer la détection.
        </small>
    </div>

    <div class="card-mehna">
        <div class="table-responsive">
            <table class="table table-borderless mb-0">
                <thead><tr style="color:var(--text-muted);">
                    <th>Question</th><th>Catégorie</th><th>Langue</th><th>Mots-clés</th><th>Statut</th><th>Actions</th>
                </tr></thead>
                <tbody>
                <?php foreach ($entries as $e2): ?>
                <tr style="color:var(--text-secondary);">
                    <td class="fw-semibold" style="color:var(--text-primary);max-width:300px;">
                        <?= truncateText(e($e2['question']), 60) ?>
                    </td>
                    <td><span class="badge-category"><?= e($e2['category'] ?? 'general') ?></span></td>
                    <td><?= langFlag($e2['language'] ?? 'fr') ?></td>
                    <td><small class="text-muted"><?= truncateText(e($e2['keywords'] ?? ''), 40) ?></small></td>
                    <td><span class="badge <?= $e2['is_active'] ? 'bg-success' : 'bg-secondary' ?>"><?= $e2['is_active'] ? 'Actif' : 'Inactif' ?></span></td>
                    <td>
                        <a href="?action=edit&id=<?= $e2['id'] ?>" class="btn btn-sm btn-outline-accent"><i class="bi bi-pencil"></i></a>
                        <form method="POST" action="?id=<?= $e2['id'] ?>" class="d-inline">
                            <?= csrfField() ?>
                            <input type="hidden" name="post_action" value="delete">
                            <button type="submit" class="btn btn-sm btn-outline-danger" data-confirm="Supprimer cette réponse ?"><i class="bi bi-trash"></i></button>
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
        <h3 class="fw-bold"><?= $action === 'edit' ? 'Modifier' : 'Nouvelle' ?> réponse chatbot</h3>
        <a href="?action=list" class="btn btn-outline-accent"><i class="bi bi-arrow-left"></i> Retour</a>
    </div>

    <div class="card-mehna p-4">
        <form method="POST" action="?action=<?= $action ?>&id=<?= $id ?>">
            <?= csrfField() ?>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Langue</label>
                    <select name="language" class="form-select">
                        <option value="fr" <?= ($entry['language'] ?? 'fr') === 'fr' ? 'selected' : '' ?>>Français</option>
                        <option value="ar" <?= ($entry['language'] ?? '') === 'ar' ? 'selected' : '' ?>>العربية</option>
                        <option value="en" <?= ($entry['language'] ?? '') === 'en' ? 'selected' : '' ?>>English</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Catégorie</label>
                    <input type="text" name="category" class="form-control" placeholder="general, emission, horaire..."
                           value="<?= e($entry['category'] ?? 'general') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Statut</label>
                    <select name="is_active" class="form-select">
                        <option value="1" <?= ($entry['is_active'] ?? 1) ? 'selected' : '' ?>>Actif</option>
                        <option value="0" <?= !($entry['is_active'] ?? 1) ? 'selected' : '' ?>>Inactif</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Question (ce que l'utilisateur demande)</label>
                    <input type="text" name="question" class="form-control" required value="<?= e($entry['question'] ?? '') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label">Mots-clés (séparés par des virgules)</label>
                    <input type="text" name="keywords" class="form-control" placeholder="emission, radio, programme..."
                           value="<?= e($entry['keywords'] ?? '') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label">Réponse</label>
                    <textarea name="answer" class="form-control" rows="5" required><?= e($entry['answer'] ?? '') ?></textarea>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-accent"><i class="bi bi-check-lg"></i> Enregistrer</button>
                </div>
            </div>
        </form>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
