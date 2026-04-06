<?php
/**
 * Radio Mehna V2 - Admin Header
 */
$lang = currentLang();
$dir = textDirection();
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $dir ?>" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'Admin') ?> - <?= __('site.name') ?> Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Noto+Sans+Arabic:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="<?= ASSETS_URL ?>/css/style.css" rel="stylesheet">
    <link href="<?= ASSETS_URL ?>/css/admin.css" rel="stylesheet">
    <?php if (isRTL()): ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <?php endif; ?>
</head>
<body>

<div class="admin-wrapper">
    <!-- Sidebar -->
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-brand">
            <a href="<?= SITE_URL ?>/admin/">
                <i class="bi bi-broadcast-pin text-accent"></i>
                <span>Radio Mehna</span>
            </a>
        </div>

        <nav class="sidebar-nav">
            <a href="<?= SITE_URL ?>/admin/" class="sidebar-link">
                <i class="bi bi-speedometer2"></i> <span><?= __('admin.dashboard') ?></span>
            </a>

            <div class="sidebar-section"><?= __('admin.manage') ?></div>

            <a href="<?= SITE_URL ?>/admin/modules/posts.php" class="sidebar-link">
                <i class="bi bi-newspaper"></i> <span><?= __('nav.news') ?></span>
            </a>
            <a href="<?= SITE_URL ?>/admin/modules/programs.php" class="sidebar-link">
                <i class="bi bi-broadcast"></i> <span><?= __('nav.programs') ?></span>
            </a>
            <a href="<?= SITE_URL ?>/admin/modules/podcasts.php" class="sidebar-link">
                <i class="bi bi-headphones"></i> <span><?= __('nav.podcasts') ?></span>
            </a>
            <a href="<?= SITE_URL ?>/admin/modules/media.php" class="sidebar-link">
                <i class="bi bi-images"></i> <span>Médias</span>
            </a>
            <a href="<?= SITE_URL ?>/admin/modules/chatbot.php" class="sidebar-link">
                <i class="bi bi-robot"></i> <span>Chatbot IA</span>
            </a>

            <div class="sidebar-section">Système</div>

            <a href="<?= SITE_URL ?>/admin/modules/users.php" class="sidebar-link">
                <i class="bi bi-people"></i> <span>Utilisateurs</span>
            </a>
            <a href="<?= SITE_URL ?>/admin/modules/translations.php" class="sidebar-link">
                <i class="bi bi-translate"></i> <span>Traductions</span>
            </a>
            <a href="<?= SITE_URL ?>/admin/modules/analytics.php" class="sidebar-link">
                <i class="bi bi-graph-up"></i> <span><?= __('admin.statistics') ?></span>
            </a>

            <div class="sidebar-section"></div>

            <a href="<?= SITE_URL ?>/" class="sidebar-link" target="_blank">
                <i class="bi bi-box-arrow-up-right"></i> <span>Voir le site</span>
            </a>
            <a href="<?= SITE_URL ?>/admin/logout.php" class="sidebar-link text-danger">
                <i class="bi bi-box-arrow-left"></i> <span><?= __('nav.logout') ?></span>
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <div class="admin-content">
        <!-- Top Bar -->
        <header class="admin-topbar">
            <button class="sidebar-toggle" id="sidebarToggle">
                <i class="bi bi-list fs-4"></i>
            </button>
            <div class="d-flex align-items-center gap-3">
                <button class="theme-toggle" id="themeToggle"><i class="bi bi-sun-fill"></i></button>
                <div class="dropdown">
                    <button class="btn btn-sm d-flex align-items-center gap-2" data-bs-toggle="dropdown"
                            style="color:var(--text-secondary);">
                        <i class="bi bi-person-circle fs-5"></i>
                        <span><?= e($_SESSION['user_name'] ?? 'Admin') ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" style="background:var(--bg-card);border-color:var(--border-color);">
                        <li><a class="dropdown-item" href="<?= SITE_URL ?>/admin/logout.php" style="color:var(--text-secondary);">
                            <i class="bi bi-box-arrow-left"></i> <?= __('nav.logout') ?>
                        </a></li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <div class="admin-page">
            <?= flashHtml() ?>
