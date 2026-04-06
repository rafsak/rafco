<?php
/**
 * Radio Mehna V2 - Grille des programmes
 */
setPageMeta(__('programs.schedule'), __('programs.schedule') . ' - ' . __('site.description'));

$daysOfWeek = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
$currentDay = strtolower(date('l'));

$scheduleData = [];
foreach ($daysOfWeek as $day) {
    try {
        $scheduleData[$day] = getScheduleForDay($day);
    } catch (Exception $e) {
        $scheduleData[$day] = [];
    }
}

require_once INCLUDES_PATH . '../templates/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1><i class="bi bi-calendar3 text-accent"></i> <?= __('programs.schedule') ?></h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= langUrl('/') ?>"><?= __('nav.home') ?></a></li>
                <li class="breadcrumb-item"><a href="<?= langUrl('programs') ?>"><?= __('nav.programs') ?></a></li>
                <li class="breadcrumb-item active"><?= __('programs.schedule') ?></li>
            </ol>
        </nav>
    </div>
</div>

<section class="py-4">
    <div class="container">
        <!-- Day Tabs -->
        <ul class="nav nav-pills mb-4 flex-nowrap overflow-auto" id="scheduleTabs" role="tablist">
            <?php foreach ($daysOfWeek as $i => $day): ?>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $day === $currentDay ? 'active' : '' ?> px-3"
                        id="tab-<?= $day ?>" data-bs-toggle="tab" data-bs-target="#pane-<?= $day ?>"
                        type="button" role="tab"
                        style="white-space:nowrap;color:var(--text-secondary);<?= $day === $currentDay ? 'background:var(--accent);color:#fff;' : '' ?>">
                    <?= __('days.' . $day) ?>
                </button>
            </li>
            <?php endforeach; ?>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="scheduleContent">
            <?php foreach ($daysOfWeek as $day): ?>
            <div class="tab-pane fade <?= $day === $currentDay ? 'show active' : '' ?>"
                 id="pane-<?= $day ?>" role="tabpanel">
                <?php if (empty($scheduleData[$day])): ?>
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-calendar-x display-4"></i>
                    <p class="mt-2"><?= __('programs.no_programs') ?></p>
                </div>
                <?php else: ?>
                <div class="schedule-table">
                    <table class="table table-borderless mb-0">
                        <thead>
                            <tr>
                                <th style="width:15%;"><?= __('programs.time') ?></th>
                                <th><?= __('nav.programs') ?></th>
                                <th style="width:20%;"><?= __('programs.host') ?></th>
                                <th style="width:10%;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $now = date('H:i:s');
                            foreach ($scheduleData[$day] as $prog):
                                $isCurrent = $day === $currentDay
                                    && $now >= ($prog['start_time'] ?? '')
                                    && $now <= ($prog['end_time'] ?? '');
                            ?>
                            <tr class="<?= $isCurrent ? 'current-program' : '' ?>">
                                <td>
                                    <strong><?= e(substr($prog['start_time'] ?? '', 0, 5)) ?></strong>
                                    <span class="text-muted"> - <?= e(substr($prog['end_time'] ?? '', 0, 5)) ?></span>
                                </td>
                                <td>
                                    <a href="<?= langUrl('programs/' . e($prog['slug'])) ?>" class="fw-semibold" style="color:var(--text-primary);">
                                        <?= e($prog['title']) ?>
                                    </a>
                                    <?php if ($isCurrent): ?>
                                    <span class="live-badge ms-2" style="font-size:0.65rem;padding:2px 8px;">
                                        <span class="dot" style="width:5px;height:5px;"></span> <?= __('player.live') ?>
                                    </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted"><?= e($prog['host_name'] ?? '') ?></td>
                                <td>
                                    <a href="<?= langUrl('programs/' . e($prog['slug'])) ?>" class="btn btn-sm btn-outline-accent">
                                        <i class="bi bi-arrow-right"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require_once INCLUDES_PATH . '../templates/footer.php'; ?>
