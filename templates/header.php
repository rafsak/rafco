<?php
/**
 * Radio Mehna V2 - Header Template
 */
$meta = getPageMeta();
$lang = currentLang();
$dir = textDirection();
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $dir ?>" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <!-- SEO Meta -->
    <title><?= e($meta['title']) ?></title>
    <meta name="description" content="<?= e($meta['description']) ?>">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?= e($meta['url']) ?>">

    <!-- Open Graph -->
    <meta property="og:type" content="<?= e($meta['type']) ?>">
    <meta property="og:title" content="<?= e($meta['title']) ?>">
    <meta property="og:description" content="<?= e($meta['description']) ?>">
    <meta property="og:image" content="<?= e($meta['image']) ?>">
    <meta property="og:url" content="<?= e($meta['url']) ?>">
    <meta property="og:site_name" content="<?= __('site.name') ?>">
    <meta property="og:locale" content="<?= $lang === 'ar' ? 'ar_TN' : ($lang === 'en' ? 'en_US' : 'fr_FR') ?>">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($meta['title']) ?>">
    <meta name="twitter:description" content="<?= e($meta['description']) ?>">
    <meta name="twitter:image" content="<?= e($meta['image']) ?>">

    <!-- Alternate languages -->
    <?php foreach (SUPPORTED_LANGS as $altLang): ?>
    <link rel="alternate" hreflang="<?= $altLang ?>" href="<?= e(switchLangUrl($altLang)) ?>">
    <?php endforeach; ?>

    <!-- PWA -->
    <link rel="manifest" href="<?= SITE_URL ?>/manifest.json">
    <meta name="theme-color" content="#0a0a0a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?= ASSETS_URL ?>/images/favicon.svg">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Noto+Sans+Arabic:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?= ASSETS_URL ?>/css/style.css" rel="stylesheet">

    <?php if (isRTL()): ?>
    <!-- Bootstrap RTL -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <?php endif; ?>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-mehna">
    <div class="container">
        <!-- Brand -->
        <a class="navbar-brand" href="<?= langUrl('/') ?>">
            <i class="bi bi-broadcast-pin text-accent"></i>
            Radio <span class="brand-accent">Mehna</span>
        </a>

        <!-- Mobile Toggle -->
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <i class="bi bi-list text-white fs-4"></i>
        </button>

        <!-- Navigation -->
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="<?= langUrl('/') ?>">
                        <i class="bi bi-house-door me-1"></i><?= __('nav.home') ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= langUrl('programs') ?>">
                        <i class="bi bi-broadcast me-1"></i><?= __('nav.programs') ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= langUrl('podcasts') ?>">
                        <i class="bi bi-headphones me-1"></i><?= __('nav.podcasts') ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= langUrl('news') ?>">
                        <i class="bi bi-newspaper me-1"></i><?= __('nav.news') ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= langUrl('contact') ?>">
                        <i class="bi bi-envelope me-1"></i><?= __('nav.contact') ?>
                    </a>
                </li>
            </ul>

            <!-- Right Side -->
            <div class="d-flex align-items-center gap-2">
                <!-- Live Badge -->
                <span class="live-badge" data-action="play-radio" role="button">
                    <span class="dot"></span> <?= __('player.live') ?>
                </span>

                <!-- Language Switcher -->
                <div class="lang-switcher dropdown">
                    <button class="dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <?= langFlag($lang) ?> <?= strtoupper($lang) ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <?php foreach (SUPPORTED_LANGS as $switchLang): ?>
                        <li>
                            <a class="dropdown-item <?= $switchLang === $lang ? 'active' : '' ?>"
                               href="#" onclick="RadioMehna.switchLanguage('<?= $switchLang ?>'); return false;">
                                <?= langFlag($switchLang) ?> <?= langName($switchLang) ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Theme Toggle -->
                <button class="theme-toggle" id="themeToggle" title="Toggle theme">
                    <i class="bi bi-sun-fill"></i>
                </button>

                <!-- Search -->
                <a href="<?= langUrl('search') ?>" class="theme-toggle" title="<?= __('nav.search') ?>">
                    <i class="bi bi-search"></i>
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- Flash Messages -->
<div class="container mt-3">
    <?= flashHtml() ?>
</div>

<!-- Main Content -->
<main>
