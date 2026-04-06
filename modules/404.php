<?php
/**
 * Radio Mehna V2 - Page 404
 */
setPageMeta('404 - Page non trouvée');
require_once INCLUDES_PATH . '../templates/header.php';
?>

<section class="py-5 text-center" style="min-height:50vh;display:flex;align-items:center;">
    <div class="container">
        <div class="display-1 text-accent fw-bold mb-3">404</div>
        <h2 class="mb-3">Page non trouvée</h2>
        <p class="text-muted mb-4">La page que vous recherchez n'existe pas ou a été déplacée.</p>
        <a href="<?= langUrl('/') ?>" class="btn btn-accent">
            <i class="bi bi-house-door"></i> <?= __('nav.home') ?>
        </a>
    </div>
</section>

<?php require_once INCLUDES_PATH . '../templates/footer.php'; ?>
