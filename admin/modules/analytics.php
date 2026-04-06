<?php
/**
 * Radio Mehna V2 - Admin Analytics
 */
require_once __DIR__ . '/../../includes/init.php';
requireLogin();

$pageTitle = __('admin.statistics');

try {
    $stats = getGlobalStats();
    $viewsByDay = getViewsByDay(30);
    $topPages = dbFetchAll("SELECT page_url, COUNT(*) as views FROM analytics GROUP BY page_url ORDER BY views DESC LIMIT 10");
    $topPodcasts = dbFetchAll("SELECT title, play_count FROM podcasts ORDER BY play_count DESC LIMIT 10");
    $recentLogs = dbFetchAll("SELECT l.*, u.full_name FROM logs l LEFT JOIN users u ON l.user_id = u.id ORDER BY l.created_at DESC LIMIT 20");
} catch (Exception $e) {
    $stats = ['total_views' => 0, 'total_plays' => 0, 'today_views' => 0];
    $viewsByDay = [];
    $topPages = [];
    $topPodcasts = [];
    $recentLogs = [];
}

require_once __DIR__ . '/../includes/header.php';
?>

<h3 class="fw-bold mb-4"><i class="bi bi-graph-up text-accent"></i> <?= __('admin.statistics') ?></h3>

<!-- Overview -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card-mehna p-4 text-center">
            <i class="bi bi-eye-fill text-accent fs-2"></i>
            <h3 class="fw-bold mt-2"><?= number_format($stats['total_views']) ?></h3>
            <small class="text-muted">Vues totales</small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card-mehna p-4 text-center">
            <i class="bi bi-play-circle-fill fs-2" style="color:var(--success);"></i>
            <h3 class="fw-bold mt-2"><?= number_format($stats['total_plays']) ?></h3>
            <small class="text-muted">Podcasts joués</small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card-mehna p-4 text-center">
            <i class="bi bi-calendar-check fs-2" style="color:var(--info);"></i>
            <h3 class="fw-bold mt-2"><?= number_format($stats['today_views']) ?></h3>
            <small class="text-muted">Vues aujourd'hui</small>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Views Chart -->
    <div class="col-lg-8">
        <div class="card-mehna p-4">
            <h5 class="mb-3">Vues par jour (30 derniers jours)</h5>
            <div style="display:flex;align-items:end;gap:4px;height:200px;">
                <?php
                $maxViews = max(array_column($viewsByDay ?: [['views' => 1]], 'views'));
                foreach ($viewsByDay as $day):
                    $height = $maxViews > 0 ? ($day['views'] / $maxViews) * 180 : 0;
                ?>
                <div style="flex:1;min-width:0;" title="<?= e($day['date']) ?>: <?= $day['views'] ?> vues">
                    <div style="height:<?= max(2, $height) ?>px;background:var(--accent);border-radius:2px 2px 0 0;transition:all 0.3s;"></div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="d-flex justify-content-between mt-2">
                <small class="text-muted"><?= $viewsByDay ? e($viewsByDay[0]['date'] ?? '') : '' ?></small>
                <small class="text-muted"><?= $viewsByDay ? e(end($viewsByDay)['date'] ?? '') : '' ?></small>
            </div>
        </div>
    </div>

    <!-- Top Pages -->
    <div class="col-lg-4">
        <div class="card-mehna p-4">
            <h5 class="mb-3">Pages populaires</h5>
            <?php foreach ($topPages as $i => $tp): ?>
            <div class="d-flex justify-content-between align-items-center mb-2">
                <small style="color:var(--text-secondary);max-width:70%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                    <?= ($i + 1) ?>. <?= e($tp['page_url']) ?>
                </small>
                <span class="badge bg-secondary"><?= number_format($tp['views']) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Top Podcasts -->
    <div class="col-lg-6">
        <div class="card-mehna p-4">
            <h5 class="mb-3">Podcasts populaires</h5>
            <?php foreach ($topPodcasts as $i => $tp): ?>
            <div class="d-flex justify-content-between align-items-center mb-2">
                <small style="color:var(--text-secondary);"><?= ($i + 1) ?>. <?= e($tp['title']) ?></small>
                <span class="badge bg-success"><?= number_format($tp['play_count']) ?> plays</span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Recent Logs -->
    <div class="col-lg-6">
        <div class="card-mehna p-4">
            <h5 class="mb-3">Journal d'activité</h5>
            <div style="max-height:300px;overflow-y:auto;">
                <?php foreach ($recentLogs as $log): ?>
                <div class="d-flex gap-2 mb-2 pb-2" style="border-bottom:1px solid var(--border-color);">
                    <small class="text-muted"><?= date('d/m H:i', strtotime($log['created_at'])) ?></small>
                    <small style="color:var(--text-secondary);">
                        <strong><?= e($log['full_name'] ?? 'Système') ?></strong> - <?= e($log['action']) ?>
                    </small>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
