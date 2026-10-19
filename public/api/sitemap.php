<?php
declare(strict_types=1);
require __DIR__ . '/lib.php';
header('Content-Type: application/xml; charset=utf-8');
header('Cache-Control: public, max-age=3600');
$site = smd_site_url();
$static = ['/' => '1.0', '/about' => '0.7', '/services' => '0.8', '/portfolio' => '0.7', '/services/store-development' => '0.8',
    '/services/add-branding' => '0.8', '/services/ai-automation' => '0.8', '/blog' => '0.9', '/contact-us' => '0.6',
    '/content-creation' => '0.5', '/privacy-policy' => '0.3', '/terms-of-service' => '0.3'];
try { $posts = smd_all_posts(false); } catch (Throwable $e) { $posts = []; error_log('[smd-sitemap] ' . $e->getMessage()); }
$latest = $posts ? max(array_map(fn($p) => $p['updatedAt'], $posts)) : gmdate('c');
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";
foreach ($static as $u => $prio) {
    echo '  <url><loc>' . smd_xml($site . $u) . '</loc>' . ($u === '/blog' ? '<lastmod>' . smd_xml(substr($latest, 0, 10)) . '</lastmod>' : '')
        . '<priority>' . $prio . "</priority></url>\n";
}
foreach ($posts as $p) {
    echo '  <url><loc>' . smd_xml($site . '/blog/' . $p['slug']) . '</loc><lastmod>' . smd_xml(substr($p['updatedAt'], 0, 10)) . '</lastmod><priority>0.8</priority>';
    if (!empty($p['featuredImage'])) echo '<image:image><image:loc>' . smd_xml(smd_abs_url($p['featuredImage'])) . '</image:loc></image:image>';
    echo "</url>\n";
}
echo "</urlset>\n";
