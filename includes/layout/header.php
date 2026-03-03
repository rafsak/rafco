<?php
/** @var string $pageTitle */
$appConfig = require __DIR__ . '/../../config/app.php';
$user = current_user();
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e(($pageTitle ?? 'Dashboard') . ' - ' . $appConfig['app_name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="/modules/dashboard/index.php">Rafco ERP</a>
        <?php if ($user): ?>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMenu" aria-controls="navbarMenu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarMenu">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="/modules/dashboard/index.php">Dashboard</a></li>
                    <?php if (has_permission('AUTH_MANAGE_USERS')): ?>
                        <li class="nav-item"><a class="nav-link" href="/modules/users/index.php">Utilisateurs</a></li>
                    <?php endif; ?>
                    <?php if (has_permission('AUTH_MANAGE_ROLES')): ?>
                        <li class="nav-item"><a class="nav-link" href="/modules/roles/index.php">Rôles</a></li>
                    <?php endif; ?>
                    <?php if (has_permission('AUDIT_VIEW')): ?>
                        <li class="nav-item"><a class="nav-link" href="/modules/audit/index.php">Audit</a></li>
                    <?php endif; ?>
                </ul>
                <span class="navbar-text text-light me-3"><?= e($user['full_name']) ?></span>
                <a class="btn btn-outline-light btn-sm" href="/modules/auth/logout.php">Déconnexion</a>
            </div>
        <?php endif; ?>
    </div>
</nav>
<main class="container py-4">
<?php if ($error = flash('error')): ?>
    <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>
<?php if ($success = flash('success')): ?>
    <div class="alert alert-success"><?= e($success) ?></div>
<?php endif; ?>
