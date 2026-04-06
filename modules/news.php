<?php
/**
 * Radio Mehna V2 - Liste des actualités
 */
setPageMeta(__('news.title'), __('news.title') . ' - ' . __('site.description'));

try {
    $page = max(1, (int)($_GET['page'] ?? 1));
    $total = countPosts();
    $pagination = paginate($total, ITEMS_PER_PAGE, $page);
    $posts = getPosts(ITEMS_PER_PAGE, $pagination['offset']);
} catch (Exception $e) {
    $posts = [];
    $pagination = paginate(0, ITEMS_PER_PAGE, 1);
}

require_once INCLUDES_PATH . '../templates/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1><i class="bi bi-newspaper text-accent"></i> <?= __('news.title') ?></h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= langUrl('/') ?>"><?= __('nav.home') ?></a></li>
                <li class="breadcrumb-item active"><?= __('nav.news') ?></li>
            </ol>
        </nav>
    </div>
</div>

<section class="py-4">
    <div class="container">
        <?php if (empty($posts)): ?>
        <div class="text-center py-5">
            <i class="bi bi-newspaper display-1 text-muted"></i>
            <p class="mt-3 text-muted"><?= __('news.no_news') ?></p>
        </div>
        <?php else: ?>
        <div class="row g-4">
            <?php foreach ($posts as $post): ?>
            <div class="col-lg-4 col-md-6">
                <div class="card-mehna">
                    <div class="card-img-wrapper">
                        <?php if (!empty($post['image'])): ?>
                        <img src="<?= mediaUrl($post['image']) ?>" class="card-img-top" alt="<?= e($post['title']) ?>">
                        <?php else: ?>
                        <div class="placeholder-img"><i class="bi bi-newspaper"></i></div>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title"><?= e($post['title']) ?></h5>
                        <p class="card-text"><?= truncateText(e($post['excerpt'] ?? ''), 100) ?></p>
                        <div class="card-meta">
                            <span><i class="bi bi-calendar3"></i> <?= timeAgo($post['published_at'] ?? $post['created_at']) ?></span>
                            <span><i class="bi bi-person"></i> <?= e($post['author_name'] ?? '') ?></span>
                            <span><i class="bi bi-eye"></i> <?= number_format($post['views'] ?? 0) ?></span>
                        </div>
                        <a href="<?= langUrl('news/' . e($post['slug'])) ?>" class="stretched-link"></a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?= paginationHtml($pagination, langUrl('news')) ?>
        <?php endif; ?>
    </div>
</section>

<?php require_once INCLUDES_PATH . '../templates/footer.php'; ?>
