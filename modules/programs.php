<?php
/**
 * Radio Mehna V2 - Liste des émissions
 */
setPageMeta(__('programs.title'), __('programs.title') . ' - ' . __('site.description'));

try {
    $page = max(1, (int)($_GET['page'] ?? 1));
    $total = countPrograms();
    $pagination = paginate($total, ITEMS_PER_PAGE, $page);
    $programs = getPrograms(ITEMS_PER_PAGE, $pagination['offset']);
} catch (Exception $e) {
    $programs = [];
    $pagination = paginate(0, ITEMS_PER_PAGE, 1);
}

require_once INCLUDES_PATH . '../templates/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1><i class="bi bi-broadcast text-accent"></i> <?= __('programs.title') ?></h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= langUrl('/') ?>"><?= __('nav.home') ?></a></li>
                <li class="breadcrumb-item active"><?= __('nav.programs') ?></li>
            </ol>
        </nav>
    </div>
</div>

<section class="py-4">
    <div class="container">
        <?php if (empty($programs)): ?>
        <div class="text-center py-5">
            <i class="bi bi-broadcast display-1 text-muted"></i>
            <p class="mt-3 text-muted"><?= __('programs.no_programs') ?></p>
        </div>
        <?php else: ?>
        <div class="row g-4">
            <?php foreach ($programs as $program): ?>
            <div class="col-lg-4 col-md-6">
                <div class="card-mehna">
                    <div class="card-img-wrapper">
                        <?php if (!empty($program['image'])): ?>
                        <img src="<?= mediaUrl($program['image']) ?>" class="card-img-top" alt="<?= e($program['title']) ?>">
                        <?php else: ?>
                        <div class="placeholder-img"><i class="bi bi-broadcast"></i></div>
                        <?php endif; ?>
                        <div class="card-img-overlay-play">
                            <div class="play-btn-overlay"><i class="bi bi-play-fill"></i></div>
                        </div>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title"><?= e($program['title']) ?></h5>
                        <p class="card-text"><?= truncateText(e($program['description'] ?? ''), 100) ?></p>
                        <div class="card-meta">
                            <span><i class="bi bi-person-fill"></i> <?= e($program['host_name'] ?? '') ?></span>
                            <span><i class="bi bi-clock"></i> <?= e(($program['start_time'] ?? '') . ' - ' . ($program['end_time'] ?? '')) ?></span>
                        </div>
                        <a href="<?= langUrl('programs/' . e($program['slug'])) ?>" class="stretched-link"></a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?= paginationHtml($pagination, langUrl('programs')) ?>
        <?php endif; ?>

        <!-- Schedule Link -->
        <div class="text-center mt-4">
            <a href="<?= langUrl('programs/schedule') ?>" class="btn btn-outline-accent">
                <i class="bi bi-calendar3"></i> <?= __('programs.schedule') ?>
            </a>
        </div>
    </div>
</section>

<?php require_once INCLUDES_PATH . '../templates/footer.php'; ?>
