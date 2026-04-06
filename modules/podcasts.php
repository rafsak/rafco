<?php
/**
 * Radio Mehna V2 - Liste des podcasts
 */
setPageMeta(__('podcasts.title'), __('podcasts.title') . ' - ' . __('site.description'));

$category = getParam('category');

try {
    $page = max(1, (int)($_GET['page'] ?? 1));
    $total = countPodcasts($category);
    $pagination = paginate($total, ITEMS_PER_PAGE, $page);
    $podcasts = getPodcasts(ITEMS_PER_PAGE, $pagination['offset'], $category);
    $categories = getPodcastCategories();
} catch (Exception $e) {
    $podcasts = [];
    $categories = [];
    $pagination = paginate(0, ITEMS_PER_PAGE, 1);
}

require_once INCLUDES_PATH . '../templates/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1><i class="bi bi-headphones text-accent"></i> <?= __('podcasts.title') ?></h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= langUrl('/') ?>"><?= __('nav.home') ?></a></li>
                <li class="breadcrumb-item active"><?= __('nav.podcasts') ?></li>
            </ol>
        </nav>
    </div>
</div>

<section class="py-4">
    <div class="container">
        <!-- Categories Filter -->
        <?php if (!empty($categories)): ?>
        <div class="mb-4 d-flex flex-wrap gap-2">
            <a href="<?= langUrl('podcasts') ?>"
               class="btn <?= !$category ? 'btn-accent' : 'btn-outline-accent' ?> btn-sm">
                <?= __('podcasts.all') ?>
            </a>
            <?php foreach ($categories as $cat): ?>
            <a href="<?= langUrl('podcasts') ?>?category=<?= urlencode($cat['category']) ?>"
               class="btn <?= $category === $cat['category'] ? 'btn-accent' : 'btn-outline-accent' ?> btn-sm">
                <?= e($cat['category']) ?> <span class="opacity-75">(<?= $cat['count'] ?>)</span>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if (empty($podcasts)): ?>
        <div class="text-center py-5">
            <i class="bi bi-headphones display-1 text-muted"></i>
            <p class="mt-3 text-muted"><?= __('podcasts.no_podcasts') ?></p>
        </div>
        <?php else: ?>
        <div class="row g-4">
            <?php foreach ($podcasts as $podcast): ?>
            <div class="col-lg-4 col-md-6">
                <div class="card-mehna">
                    <div class="card-img-wrapper">
                        <?php if (!empty($podcast['image'])): ?>
                        <img src="<?= mediaUrl($podcast['image']) ?>" class="card-img-top" alt="<?= e($podcast['title']) ?>">
                        <?php else: ?>
                        <div class="placeholder-img"><i class="bi bi-headphones"></i></div>
                        <?php endif; ?>
                        <div class="card-img-overlay-play">
                            <div class="play-btn-overlay"><i class="bi bi-play-fill"></i></div>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($podcast['category'])): ?>
                        <span class="badge-category mb-2 d-inline-block"><?= e($podcast['category']) ?></span>
                        <?php endif; ?>
                        <h5 class="card-title"><?= e($podcast['title']) ?></h5>
                        <p class="card-text"><?= truncateText(e($podcast['description'] ?? ''), 80) ?></p>
                        <div class="card-meta">
                            <span><i class="bi bi-clock"></i> <?= formatDuration($podcast['duration'] ?? 0) ?></span>
                            <span><i class="bi bi-play-circle"></i> <?= number_format($podcast['play_count'] ?? 0) ?></span>
                            <span><i class="bi bi-calendar3"></i> <?= timeAgo($podcast['published_at'] ?? $podcast['created_at']) ?></span>
                        </div>
                        <a href="<?= langUrl('podcasts/' . e($podcast['slug'])) ?>" class="stretched-link"></a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?= paginationHtml($pagination, langUrl('podcasts') . ($category ? '?category=' . urlencode($category) . '&' : '')) ?>
        <?php endif; ?>
    </div>
</section>

<?php require_once INCLUDES_PATH . '../templates/footer.php'; ?>
