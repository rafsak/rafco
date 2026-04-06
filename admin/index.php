<?php
/**
 * Radio Mehna V2 - Admin Dashboard
 */
require_once __DIR__ . '/../includes/init.php';
requireLogin();

$pageTitle = __('admin.dashboard');

try {
    $stats = getGlobalStats();
    $recentPosts = getAllPosts(5);
    $viewsByDay = getViewsByDay(7);
} catch (Exception $e) {
    $stats = [
        'total_views' => 0, 'total_plays' => 0, 'today_views' => 0,
        'total_programs' => 0, 'total_podcasts' => 0, 'total_posts' => 0,
        'total_users' => 0,
    ];
    $recentPosts = [];
    $viewsByDay = [];
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container-fluid py-4">
    <!-- Welcome -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold"><?= str_replace(':name', e($_SESSION['user_name'] ?? 'Admin'), __('admin.welcome')) ?></h2>
            <p class="text-muted mb-0"><?= __('admin.dashboard') ?> - <?= date('d/m/Y H:i') ?></p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card-mehna p-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1 small"><?= __('admin.total_views') ?></p>
                        <h3 class="fw-bold mb-0"><?= number_format($stats['total_views']) ?></h3>
                        <small class="text-success"><i class="bi bi-arrow-up"></i> <?= number_format($stats['today_views']) ?> aujourd'hui</small>
                    </div>
                    <div style="width:50px;height:50px;background:var(--accent-soft);border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-eye-fill text-accent fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card-mehna p-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1 small"><?= __('admin.total_plays') ?></p>
                        <h3 class="fw-bold mb-0"><?= number_format($stats['total_plays']) ?></h3>
                    </div>
                    <div style="width:50px;height:50px;background:rgba(29,185,84,0.1);border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-play-circle-fill fs-4" style="color:var(--success);"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card-mehna p-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1 small"><?= __('nav.programs') ?></p>
                        <h3 class="fw-bold mb-0"><?= $stats['total_programs'] ?></h3>
                    </div>
                    <div style="width:50px;height:50px;background:rgba(59,130,246,0.1);border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-broadcast-pin fs-4" style="color:var(--info);"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card-mehna p-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1 small"><?= __('nav.podcasts') ?></p>
                        <h3 class="fw-bold mb-0"><?= $stats['total_podcasts'] ?></h3>
                    </div>
                    <div style="width:50px;height:50px;background:rgba(245,158,11,0.1);border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-headphones fs-4" style="color:var(--warning);"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Recent Posts -->
        <div class="col-lg-8">
            <div class="card-mehna p-4">
                <h5 class="mb-3"><i class="bi bi-clock-history text-accent"></i> <?= __('admin.recent') ?></h5>
                <div class="table-responsive">
                    <table class="table table-borderless">
                        <thead>
                            <tr style="color:var(--text-muted);">
                                <th>Titre</th>
                                <th>Statut</th>
                                <th>Vues</th>
                                <th>Date</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentPosts as $post): ?>
                            <tr style="color:var(--text-secondary);">
                                <td class="fw-semibold" style="color:var(--text-primary);"><?= e($post['title']) ?></td>
                                <td>
                                    <span class="badge <?= $post['status'] === 'published' ? 'bg-success' : 'bg-secondary' ?>">
                                        <?= e($post['status']) ?>
                                    </span>
                                </td>
                                <td><?= number_format($post['views'] ?? 0) ?></td>
                                <td><?= timeAgo($post['created_at']) ?></td>
                                <td>
                                    <a href="<?= SITE_URL ?>/admin/modules/posts.php?action=edit&id=<?= $post['id'] ?>"
                                       class="btn btn-sm btn-outline-accent"><i class="bi bi-pencil"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-lg-4">
            <div class="card-mehna p-4">
                <h5 class="mb-3"><i class="bi bi-lightning-fill text-accent"></i> Actions rapides</h5>
                <div class="d-grid gap-2">
                    <a href="<?= SITE_URL ?>/admin/modules/posts.php?action=create" class="btn btn-outline-accent btn-sm text-start">
                        <i class="bi bi-plus-circle"></i> Nouvel article
                    </a>
                    <a href="<?= SITE_URL ?>/admin/modules/programs.php?action=create" class="btn btn-outline-accent btn-sm text-start">
                        <i class="bi bi-plus-circle"></i> Nouvelle émission
                    </a>
                    <a href="<?= SITE_URL ?>/admin/modules/podcasts.php?action=create" class="btn btn-outline-accent btn-sm text-start">
                        <i class="bi bi-plus-circle"></i> Nouveau podcast
                    </a>
                    <a href="<?= SITE_URL ?>/admin/modules/chatbot.php" class="btn btn-outline-accent btn-sm text-start">
                        <i class="bi bi-robot"></i> Gérer le chatbot
                    </a>
                    <a href="<?= SITE_URL ?>/admin/modules/media.php" class="btn btn-outline-accent btn-sm text-start">
                        <i class="bi bi-images"></i> Médiathèque
                    </a>
                </div>
            </div>

            <!-- Language Stats -->
            <div class="card-mehna p-4 mt-4">
                <h5 class="mb-3"><i class="bi bi-globe text-accent"></i> Langues</h5>
                <?php
                try {
                    $langStats = getStatsByLanguage();
                } catch (Exception $e) {
                    $langStats = [];
                }
                foreach ($langStats as $ls):
                    $pct = $stats['total_views'] > 0 ? round(($ls['views'] / $stats['total_views']) * 100) : 0;
                ?>
                <div class="mb-2">
                    <div class="d-flex justify-content-between small mb-1">
                        <span><?= langFlag($ls['language'] ?? '') ?> <?= langName($ls['language'] ?? '') ?></span>
                        <span><?= $pct ?>%</span>
                    </div>
                    <div class="progress" style="height:6px;background:var(--bg-tertiary);">
                        <div class="progress-bar" style="width:<?= $pct ?>%;background:var(--accent);"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
