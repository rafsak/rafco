<?php
/**
 * Radio Mehna V2 - Détail d'un podcast
 */
$slug = sanitize($_GET['slug'] ?? '');
if (empty($slug)) {
    http_response_code(404);
    require_once MODULES_PATH . '404.php';
    return;
}

try {
    $podcast = getPodcastBySlug($slug);
} catch (Exception $e) {
    $podcast = null;
}

if (!$podcast) {
    http_response_code(404);
    require_once MODULES_PATH . '404.php';
    return;
}

setPageMeta($podcast['title'], truncateText($podcast['description'] ?? '', 160), $podcast['image'] ?? '', 'article');

try {
    $related = getRelatedPodcasts($podcast['id'], $podcast['category'] ?? '', 4);
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
                <li class="breadcrumb-item"><a href="<?= langUrl('podcasts') ?>"><?= __('nav.podcasts') ?></a></li>
                <li class="breadcrumb-item active"><?= e($podcast['title']) ?></li>
            </ol>
        </nav>
        <h1><?= e($podcast['title']) ?></h1>
    </div>
</div>

<section class="py-4">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-8">
                <!-- Podcast Player -->
                <div class="podcast-player mb-4">
                    <div class="row align-items-center g-4">
                        <div class="col-md-4 text-center">
                            <?php if (!empty($podcast['image'])): ?>
                            <img src="<?= mediaUrl($podcast['image']) ?>" class="podcast-art" alt="<?= e($podcast['title']) ?>">
                            <?php else: ?>
                            <div class="placeholder-img rounded" style="height:200px;max-width:200px;margin:auto;">
                                <i class="bi bi-headphones"></i>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-8">
                            <?php if (!empty($podcast['category'])): ?>
                            <span class="badge-category mb-2 d-inline-block"><?= e($podcast['category']) ?></span>
                            <?php endif; ?>
                            <h3 class="mb-2"><?= e($podcast['title']) ?></h3>
                            <div class="text-muted mb-3">
                                <span><i class="bi bi-person"></i> <?= e($podcast['author_name'] ?? '') ?></span>
                                <span class="ms-3"><i class="bi bi-clock"></i> <?= formatDuration($podcast['duration'] ?? 0) ?></span>
                                <span class="ms-3"><i class="bi bi-play-circle"></i> <?= number_format($podcast['play_count'] ?? 0) ?></span>
                            </div>

                            <!-- Audio Player -->
                            <audio id="podcastAudio" data-podcast-id="<?= $podcast['id'] ?>" preload="metadata">
                                <source src="<?= mediaUrl($podcast['audio_file'] ?? '') ?>" type="audio/mpeg">
                            </audio>

                            <div class="d-flex align-items-center gap-3 mb-3">
                                <button class="player-btn-main" id="podcastPlayBtn">
                                    <i class="bi bi-play-fill"></i>
                                </button>
                                <div class="flex-grow-1">
                                    <div class="podcast-progress" id="podcastProgress">
                                        <div class="progress-fill" id="podcastProgressFill"></div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-1">
                                        <small class="text-muted" id="podcastCurrentTime">0:00</small>
                                        <small class="text-muted" id="podcastTotalTime"><?= formatDuration($podcast['duration'] ?? 0) ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div class="card-mehna p-4">
                    <h4 class="mb-3"><?= __('programs.description') ?></h4>
                    <p style="color:var(--text-secondary);line-height:1.8;">
                        <?= nl2br(e($podcast['description'] ?? '')) ?>
                    </p>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Info -->
                <div class="card-mehna p-4 mb-4">
                    <h5 class="text-accent mb-3"><i class="bi bi-info-circle"></i> Informations</h5>
                    <ul class="list-unstyled">
                        <?php if (!empty($podcast['program_title'])): ?>
                        <li class="mb-2"><small class="text-muted"><?= __('nav.programs') ?>:</small><br>
                            <a href="<?= langUrl('programs/' . e($podcast['program_slug'] ?? '')) ?>"><?= e($podcast['program_title']) ?></a>
                        </li>
                        <?php endif; ?>
                        <li class="mb-2"><small class="text-muted"><?= __('news.published') ?>:</small><br>
                            <?= formatDate($podcast['published_at'] ?? $podcast['created_at']) ?>
                        </li>
                        <?php if (!empty($podcast['category'])): ?>
                        <li class="mb-2"><small class="text-muted"><?= __('podcasts.category') ?>:</small><br>
                            <?= e($podcast['category']) ?>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- Related -->
                <?php if (!empty($related)): ?>
                <div class="card-mehna p-4">
                    <h5 class="mb-3"><?= __('news.related') ?></h5>
                    <?php foreach ($related as $rel): ?>
                    <a href="<?= langUrl('podcasts/' . e($rel['slug'])) ?>" class="d-flex align-items-center gap-3 mb-3 text-decoration-none">
                        <div class="flex-shrink-0" style="width:50px;height:50px;background:var(--bg-tertiary);border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;">
                            <i class="bi bi-headphones text-accent"></i>
                        </div>
                        <div>
                            <div class="fw-semibold" style="color:var(--text-primary);font-size:0.9rem;"><?= e($rel['title']) ?></div>
                            <small class="text-muted"><?= formatDuration($rel['duration'] ?? 0) ?></small>
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
