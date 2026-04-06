<?php
/**
 * Radio Mehna V2 - Page d'accueil
 */
setPageMeta(__('site.name') . ' - ' . __('site.tagline'), __('site.description'));

// Charger les données
try {
    $featuredPrograms = getFeaturedPrograms(4);
    $recentPodcasts = getRecentPodcasts(6);
    $recentPosts = getRecentPosts(3);
    $currentProgram = getCurrentProgram();
} catch (Exception $e) {
    $featuredPrograms = [];
    $recentPodcasts = [];
    $recentPosts = [];
    $currentProgram = null;
}

require_once INCLUDES_PATH . '../templates/header.php';
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <div class="hero-content text-lg-start">
                    <div class="live-badge mb-4 d-inline-flex" data-action="play-radio" role="button">
                        <span class="dot"></span> <?= __('player.live') ?>
                    </div>
                    <h1 class="hero-title"><?= __('home.hero_title') ?></h1>
                    <p class="hero-subtitle mx-lg-0"><?= __('home.hero_subtitle') ?></p>

                    <?php if ($currentProgram): ?>
                    <div class="mb-4">
                        <span class="text-accent fw-bold"><i class="bi bi-broadcast"></i> <?= __('player.now_playing') ?>:</span>
                        <span class="text-white ms-2"><?= e($currentProgram['title']) ?></span>
                    </div>
                    <?php endif; ?>

                    <button class="btn-live" data-action="play-radio">
                        <i class="bi bi-play-fill play-icon"></i>
                        <?= __('home.listen_live') ?>
                    </button>
                </div>
            </div>
            <div class="col-lg-5 d-none d-lg-flex justify-content-center">
                <div class="waveform paused" style="height:120px;gap:6px;">
                    <span class="bar" style="width:8px;"></span>
                    <span class="bar" style="width:8px;"></span>
                    <span class="bar" style="width:8px;"></span>
                    <span class="bar" style="width:8px;"></span>
                    <span class="bar" style="width:8px;"></span>
                    <span class="bar" style="width:8px;"></span>
                    <span class="bar" style="width:8px;"></span>
                    <span class="bar" style="width:8px;"></span>
                    <span class="bar" style="width:8px;"></span>
                    <span class="bar" style="width:8px;"></span>
                    <span class="bar" style="width:8px;"></span>
                    <span class="bar" style="width:8px;"></span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Programs -->
<?php if (!empty($featuredPrograms)): ?>
<section class="py-5">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">
                <span class="accent-line"></span>
                <?= __('home.latest_programs') ?>
            </h2>
            <a href="<?= langUrl('programs') ?>" class="section-link">
                <?= __('home.view_all') ?> <i class="bi bi-arrow-right"></i>
            </a>
        </div>
        <div class="row g-4">
            <?php foreach ($featuredPrograms as $program): ?>
            <div class="col-lg-3 col-md-6">
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
                        <p class="card-text"><?= truncateText(e($program['description'] ?? ''), 80) ?></p>
                        <div class="card-meta">
                            <span><i class="bi bi-person-fill"></i> <?= e($program['host_name'] ?? '') ?></span>
                            <span><i class="bi bi-clock"></i> <?= e($program['start_time'] ?? '') ?></span>
                        </div>
                        <a href="<?= langUrl('programs/' . e($program['slug'])) ?>" class="stretched-link"></a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Recent Podcasts -->
<?php if (!empty($recentPodcasts)): ?>
<section class="py-5" style="background:var(--bg-secondary);">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">
                <span class="accent-line"></span>
                <?= __('home.latest_podcasts') ?>
            </h2>
            <a href="<?= langUrl('podcasts') ?>" class="section-link">
                <?= __('home.view_all') ?> <i class="bi bi-arrow-right"></i>
            </a>
        </div>
        <div class="row g-4">
            <?php foreach ($recentPodcasts as $podcast): ?>
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
                        </div>
                        <a href="<?= langUrl('podcasts/' . e($podcast['slug'])) ?>" class="stretched-link"></a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Latest News -->
<?php if (!empty($recentPosts)): ?>
<section class="py-5">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">
                <span class="accent-line"></span>
                <?= __('home.latest_news') ?>
            </h2>
            <a href="<?= langUrl('news') ?>" class="section-link">
                <?= __('home.view_all') ?> <i class="bi bi-arrow-right"></i>
            </a>
        </div>
        <div class="row g-4">
            <?php foreach ($recentPosts as $post): ?>
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
                        </div>
                        <a href="<?= langUrl('news/' . e($post['slug'])) ?>" class="stretched-link"></a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php require_once INCLUDES_PATH . '../templates/footer.php'; ?>
