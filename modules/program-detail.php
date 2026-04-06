<?php
/**
 * Radio Mehna V2 - Détail d'une émission
 */
$slug = sanitize($_GET['slug'] ?? '');
if (empty($slug)) {
    http_response_code(404);
    require_once MODULES_PATH . '404.php';
    return;
}

try {
    $program = getProgramBySlug($slug);
} catch (Exception $e) {
    $program = null;
}

if (!$program) {
    http_response_code(404);
    require_once MODULES_PATH . '404.php';
    return;
}

setPageMeta($program['title'], truncateText($program['description'] ?? '', 160), $program['image'] ?? '', 'article');

require_once INCLUDES_PATH . '../templates/header.php';
?>

<div class="page-header">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= langUrl('/') ?>"><?= __('nav.home') ?></a></li>
                <li class="breadcrumb-item"><a href="<?= langUrl('programs') ?>"><?= __('nav.programs') ?></a></li>
                <li class="breadcrumb-item active"><?= e($program['title']) ?></li>
            </ol>
        </nav>
        <h1><?= e($program['title']) ?></h1>
    </div>
</div>

<section class="py-4">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-8">
                <!-- Program Image -->
                <?php if (!empty($program['image'])): ?>
                <img src="<?= mediaUrl($program['image']) ?>" alt="<?= e($program['title']) ?>"
                     class="w-100 rounded-3 mb-4" style="max-height:400px;object-fit:cover;">
                <?php else: ?>
                <div class="placeholder-img rounded-3 mb-4" style="height:300px;">
                    <i class="bi bi-broadcast"></i>
                </div>
                <?php endif; ?>

                <!-- Description -->
                <div class="card-mehna p-4 mb-4">
                    <h3 class="mb-3"><?= __('programs.description') ?></h3>
                    <p style="color:var(--text-secondary);line-height:1.8;">
                        <?= nl2br(e($program['description'] ?? '')) ?>
                    </p>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Info Card -->
                <div class="card-mehna p-4 mb-4">
                    <h5 class="text-accent mb-3"><i class="bi bi-info-circle"></i> Informations</h5>

                    <?php if (!empty($program['host_name'])): ?>
                    <div class="mb-3">
                        <small class="text-muted d-block"><?= __('programs.host') ?></small>
                        <div class="d-flex align-items-center gap-2 mt-1">
                            <?php if (!empty($program['host_avatar'])): ?>
                            <img src="<?= mediaUrl($program['host_avatar']) ?>" class="rounded-circle" width="32" height="32" alt="">
                            <?php endif; ?>
                            <strong><?= e($program['host_name']) ?></strong>
                        </div>
                        <?php if (!empty($program['host_bio'])): ?>
                        <p class="mt-2 small text-muted"><?= truncateText(e($program['host_bio']), 120) ?></p>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($program['start_time']) && !empty($program['end_time'])): ?>
                    <div class="mb-3">
                        <small class="text-muted d-block"><?= __('programs.time') ?></small>
                        <strong><?= e($program['start_time']) ?> - <?= e($program['end_time']) ?></strong>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($program['broadcast_days'])): ?>
                    <div class="mb-3">
                        <small class="text-muted d-block"><?= __('programs.days') ?></small>
                        <?php
                        $days = explode(',', $program['broadcast_days']);
                        foreach ($days as $day):
                            $dayKey = trim($day);
                        ?>
                        <span class="badge bg-dark border border-secondary me-1 mb-1"><?= __('days.' . $dayKey) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <button class="btn btn-accent w-100 mt-2" data-action="play-radio">
                        <i class="bi bi-play-fill"></i> <?= __('programs.listen') ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once INCLUDES_PATH . '../templates/footer.php'; ?>
