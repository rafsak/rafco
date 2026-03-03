<?php
$pageTitle = 'Accès refusé';
include __DIR__ . '/../../includes/layout/header.php';
?>
<div class="alert alert-danger">
    Vous n'avez pas les droits nécessaires pour accéder à cette fonctionnalité.
</div>
<a href="/modules/dashboard/index.php" class="btn btn-secondary">Retour au dashboard</a>
<?php include __DIR__ . '/../../includes/layout/footer.php'; ?>
