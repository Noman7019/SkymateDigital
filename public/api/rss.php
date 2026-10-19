<?php
declare(strict_types=1);
require __DIR__ . '/lib.php';
header('Content-Type: application/rss+xml; charset=utf-8');
header('Cache-Control: public, max-age=1800');
$site = smd_site_url();
try { $posts = array_slice(smd_all_posts(false), 0, 30); } catch (Throwable $e) { $posts = []; }
$cdata = fn(string $s) => '<![CDATA[' . str_replace(']]>', ']]]]><![CDATA[>', $s) . ']]>';
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:dc="http://purl.org/dc/elements/1.1/"><channel>' . "\n";
echo '<title>Sky Mate Digital Blog</title><link>' . smd_xml($site . '/blog') . '</link>';
echo '<description>Insights on Shopify, e-commerce growth, branding, SEO and AI automation.</description><language>en</language>';
echo '<atom:link href="' . smd_xml($site . '/blog/rss.xml') . '" rel="self" type="application/rss+xml" />';
if ($posts) echo '<lastBuildDate>' . gmdate(DATE_RSS, strtotime($posts[0]['updatedAt'])) . '</lastBuildDate>';
echo "\n";
foreach ($posts as $p) {
    $url = $site . '/blog/' . $p['slug'];
    echo '<item><title>' . smd_xml($p['title']) . '</title><link>' . smd_xml($url) . '</link><guid isPermaLink="true">' . smd_xml($url) . '</guid>'
        . '<pubDate>' . gmdate(DATE_RSS, strtotime($p['publishedAt'] ?: $p['createdAt'])) . '</pubDate>'
        . '<dc:creator>' . smd_xml(($p['author']['name'] ?? '') ?: SMD_DEFAULT_AUTHOR) . '</dc:creator>'
        . '<category>' . smd_xml($p['category']) . '</category>'
        . '<description>' . smd_xml($p['metaDescription'] ?: $p['excerpt']) . '</description>'
        . '<content:encoded>' . $cdata($p['content']) . "</content:encoded></item>\n";
}
echo "</channel></rss>\n";
