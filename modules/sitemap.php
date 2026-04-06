<?php
/**
 * Radio Mehna V2 - Sitemap XML
 */
header('Content-Type: application/xml; charset=utf-8');

$baseUrl = SITE_URL;

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:xhtml="http://www.w3.org/1999/xhtml">

<?php foreach (SUPPORTED_LANGS as $lang): ?>
    <!-- Homepage -->
    <url>
        <loc><?= $baseUrl ?>/<?= $lang ?>/</loc>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
        <?php foreach (SUPPORTED_LANGS as $altLang): ?>
        <xhtml:link rel="alternate" hreflang="<?= $altLang ?>" href="<?= $baseUrl ?>/<?= $altLang ?>/" />
        <?php endforeach; ?>
    </url>

    <!-- Static Pages -->
    <?php
    $staticPages = ['programs', 'podcasts', 'news', 'contact', 'search'];
    foreach ($staticPages as $page):
    ?>
    <url>
        <loc><?= $baseUrl ?>/<?= $lang ?>/<?= $page ?></loc>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    <?php endforeach; ?>
<?php endforeach; ?>

<?php
// Dynamic pages
try {
    $programs = dbFetchAll("SELECT slug, updated_at FROM programs WHERE is_active = 1");
    foreach ($programs as $item):
        foreach (SUPPORTED_LANGS as $lang):
?>
    <url>
        <loc><?= $baseUrl ?>/<?= $lang ?>/programs/<?= e($item['slug']) ?></loc>
        <lastmod><?= date('Y-m-d', strtotime($item['updated_at'])) ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
    </url>
<?php
        endforeach;
    endforeach;

    $podcasts = dbFetchAll("SELECT slug, updated_at FROM podcasts WHERE is_published = 1");
    foreach ($podcasts as $item):
        foreach (SUPPORTED_LANGS as $lang):
?>
    <url>
        <loc><?= $baseUrl ?>/<?= $lang ?>/podcasts/<?= e($item['slug']) ?></loc>
        <lastmod><?= date('Y-m-d', strtotime($item['updated_at'])) ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
    </url>
<?php
        endforeach;
    endforeach;

    $posts = dbFetchAll("SELECT slug, updated_at FROM posts WHERE status = 'published'");
    foreach ($posts as $item):
        foreach (SUPPORTED_LANGS as $lang):
?>
    <url>
        <loc><?= $baseUrl ?>/<?= $lang ?>/news/<?= e($item['slug']) ?></loc>
        <lastmod><?= date('Y-m-d', strtotime($item['updated_at'])) ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.6</priority>
    </url>
<?php
        endforeach;
    endforeach;
} catch (Exception $e) {
    // DB not available
}
?>
</urlset>
