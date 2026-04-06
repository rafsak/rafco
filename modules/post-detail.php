<?php
/**
 * Radio Mehna V2 - Détail d'un article
 */
$slug = sanitize($_GET['slug'] ?? '');
if (empty($slug)) {
    http_response_code(404);
    require_once MODULES_PATH . '404.php';
    return;
}

try {
    $post = getPostBySlug($slug);
} catch (Exception $e) {
    $post = null;
}

if (!$post) {
    http_response_code(404);
    require_once MODULES_PATH . '404.php';
    return;
}

// Incrémenter les vues
try { incrementPostViews($post['id']); } catch (Exception $e) {}

setPageMeta($post['title'], truncateText(strip_tags($post['excerpt'] ?? $post['content'] ?? ''), 160), $post['image'] ?? '', 'article');

try {
    $related = getRelatedPosts($post['id'], 3);
} catch (Exception $e) {
    $related = [];
}

require_once INCLUDES_PATH . '../templates/header.php';
?>

<div class="page-header">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= langUrl('/') ?>"><?= __('nav.home') ?></a></li>
                <li class="breadcrumb-item"><a href="<?= langUrl('news') ?>"><?= __('nav.news') ?></a></li>
                <li class="breadcrumb-item active"><?= e($post['title']) ?></li>
            </ol>
        </nav>
        <h1><?= e($post['title']) ?></h1>
        <div class="mt-2" style="color:var(--text-muted);">
            <span><i class="bi bi-person"></i> <?= e($post['author_name'] ?? '') ?></span>
            <span class="ms-3"><i class="bi bi-calendar3"></i> <?= formatDate($post['published_at'] ?? $post['created_at']) ?></span>
            <span class="ms-3"><i class="bi bi-eye"></i> <?= number_format($post['views'] ?? 0) ?></span>
        </div>
    </div>
</div>

<section class="py-4">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-8">
                <?php if (!empty($post['image'])): ?>
                <img src="<?= mediaUrl($post['image']) ?>" alt="<?= e($post['title']) ?>"
                     class="w-100 rounded-3 mb-4" style="max-height:400px;object-fit:cover;">
                <?php endif; ?>

                <div class="card-mehna p-4">
                    <article style="color:var(--text-secondary);line-height:1.9;font-size:1.05rem;">
                        <?= $post['content'] ?? '' ?>
                    </article>
                </div>

                <!-- Share -->
                <div class="card-mehna p-3 mt-4">
                    <div class="d-flex align-items-center gap-3">
                        <span class="fw-bold"><?= __('news.share') ?>:</span>
                        <a href="https://facebook.com/sharer/sharer.php?u=<?= urlencode(currentUrl()) ?>" target="_blank" class="btn btn-sm btn-outline-accent">
                            <i class="bi bi-facebook"></i>
                        </a>
                        <a href="https://twitter.com/intent/tweet?url=<?= urlencode(currentUrl()) ?>&text=<?= urlencode($post['title']) ?>" target="_blank" class="btn btn-sm btn-outline-accent">
                            <i class="bi bi-twitter-x"></i>
                        </a>
                        <a href="https://wa.me/?text=<?= urlencode($post['title'] . ' ' . currentUrl()) ?>" target="_blank" class="btn btn-sm btn-outline-accent">
                            <i class="bi bi-whatsapp"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <?php if (!empty($related)): ?>
                <div class="card-mehna p-4">
                    <h5 class="mb-3"><?= __('news.related') ?></h5>
                    <?php foreach ($related as $rel): ?>
                    <a href="<?= langUrl('news/' . e($rel['slug'])) ?>" class="d-flex gap-3 mb-3 text-decoration-none">
                        <div class="flex-shrink-0" style="width:60px;height:60px;background:var(--bg-tertiary);border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;">
                            <i class="bi bi-newspaper text-accent"></i>
                        </div>
                        <div>
                            <div class="fw-semibold" style="color:var(--text-primary);font-size:0.9rem;"><?= e($rel['title']) ?></div>
                            <small class="text-muted"><?= timeAgo($rel['published_at'] ?? $rel['created_at']) ?></small>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php require_once INCLUDES_PATH . '../templates/footer.php'; ?>
