<?php
/**
 * Radio Mehna V2 - Page de recherche
 */
$query = getParam('q', '');
setPageMeta(__('search.title'), __('search.title') . ' - ' . __('site.description'));

$results = [];
if (!empty($query)) {
    try {
        $results = smartSearch($query, 20);
    } catch (Exception $e) {
        $results = [];
    }
}

require_once INCLUDES_PATH . '../templates/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1><i class="bi bi-search text-accent"></i> <?= __('search.title') ?></h1>
    </div>
</div>

<section class="py-4">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Search Box -->
                <div class="search-box mb-4">
                    <i class="bi bi-search search-icon"></i>
                    <form method="GET" action="<?= langUrl('search') ?>">
                        <input type="text" name="q" id="globalSearch" value="<?= e($query) ?>"
                               placeholder="<?= __('search.placeholder') ?>" autocomplete="off">
                    </form>
                    <div class="search-results-dropdown" id="searchResults"></div>
                </div>

                <?php if (!empty($query)): ?>
                    <?php if (empty($results)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-search display-1 text-muted"></i>
                        <p class="mt-3 text-muted"><?= str_replace(':query', e($query), __('search.no_results')) ?></p>
                    </div>
                    <?php else: ?>
                    <h5 class="mb-4"><?= str_replace(':query', e($query), __('search.results')) ?> (<?= count($results) ?>)</h5>
                    <?php foreach ($results as $result):
                        $typeIcons = ['program' => 'bi-broadcast', 'podcast' => 'bi-headphones', 'post' => 'bi-newspaper'];
                        $typeUrls = ['program' => 'programs', 'podcast' => 'podcasts', 'post' => 'news'];
                        $icon = $typeIcons[$result['type']] ?? 'bi-file-text';
                        $urlPrefix = $typeUrls[$result['type']] ?? '';
                    ?>
                    <a href="<?= langUrl($urlPrefix . '/' . e($result['slug'])) ?>" class="card-mehna d-flex align-items-center gap-3 p-3 mb-3 text-decoration-none">
                        <div style="width:50px;height:50px;background:var(--accent-soft);border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;">
                            <i class="bi <?= $icon ?> text-accent fs-4"></i>
                        </div>
                        <div class="flex-grow-1">
                            <span class="badge-category mb-1 d-inline-block"><?= e(ucfirst($result['type'])) ?></span>
                            <h6 class="mb-1" style="color:var(--text-primary);"><?= e($result['title']) ?></h6>
                            <p class="mb-0 small" style="color:var(--text-muted);"><?= truncateText(e($result['description'] ?? ''), 120) ?></p>
                        </div>
                        <i class="bi bi-arrow-right text-muted"></i>
                    </a>
                    <?php endforeach; ?>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php require_once INCLUDES_PATH . '../templates/footer.php'; ?>
