<?php
// Server-side SEO renderer: every non-file URL is rewritten here (see /.htaccess).
// Injects per-route <title>, meta, canonical, Open Graph/Twitter, JSON-LD and, for blog pages,
// crawlable initial HTML into the SPA shell (index.html). The React app then takes over.
declare(strict_types=1);
require __DIR__ . '/lib.php';

$shellFile = dirname(__DIR__) . '/index.html';
$shell = (string)@file_get_contents($shellFile);
$path = '/' . trim((string)parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
$path = rawurldecode($path);
$site = smd_site_url();

$pages = [
    '/' => ['Sky Mate Digital | Shopify Store Development, Branding & Growth Agency',
            'Sky Mate Digital builds high-converting Shopify stores, brands and AI automations that grow e-commerce revenue. Free store audit available.'],
    '/about' => ['About Us | Sky Mate Digital', 'Meet Sky Mate Digital: an e-commerce growth agency helping Shopify brands design, launch and scale.'],
    '/services' => ['Services | Sky Mate Digital', 'Shopify store development, branding, paid campaigns, organic growth and store automation services from Sky Mate Digital.'],
    '/contact-us' => ['Contact Us | Sky Mate Digital', 'Get in touch with Sky Mate Digital for a free Shopify store audit or to discuss your next e-commerce project.'],
    '/portfolio' => ['Portfolio | Sky Mate Digital', 'Selected Shopify stores, brands and automation projects delivered by Sky Mate Digital.'],
    '/services/store-development' => ['Shopify Store Development | Sky Mate Digital', 'Building high-converting Shopify stores designed to grow your business and maximise sales.'],
    '/services/add-branding' => ['Branding & Identity | Sky Mate Digital', 'Brand identity, packaging and creative systems that make your e-commerce brand impossible to scroll past.'],
    '/services/ai-automation' => ['AI Automation for E-commerce | Sky Mate Digital', 'AI and n8n automations that cut operational costs and speed up support for online stores.'],
    '/blog' => ['Blog | Sky Mate Digital', 'Insights on Shopify, e-commerce growth, branding, SEO, performance marketing and AI automation from the Sky Mate Digital team.'],
    '/privacy-policy' => ['Privacy Policy | Sky Mate Digital', 'How Sky Mate Digital collects, uses and protects your personal information.'],
    '/terms-of-service' => ['Terms of Service | Sky Mate Digital', 'The terms that govern use of skymatedigital.com and Sky Mate Digital services.'],
    '/terms-condition' => ['Terms of Service | Sky Mate Digital', 'The terms that govern use of skymatedigital.com and Sky Mate Digital services.'],
    '/content-creation' => ['Content Creation Portfolio | Sky Mate Digital', 'Content creation work produced by Sky Mate Digital.'],
];

$status = 200; $title = ''; $desc = ''; $canonical = $site . ($path === '/' ? '/' : $path);
$image = $site . '/og-image.png'; $imageAlt = SMD_SITE_NAME; $ogType = 'website'; $robots = 'index, follow, max-image-preview:large';
$jsonld = []; $body = ''; $extraHead = '';
$org = ['@type' => ['Organization', 'ProfessionalService'], '@id' => $site . '/#organization', 'name' => SMD_SITE_NAME, 'url' => $site . '/', 'logo' => $site . '/og-image.png', 'email' => SMD_CONTACT_EMAIL,
    'telephone' => SMD_TELEPHONES,
    'address' => ['@type' => 'PostalAddress', 'streetAddress' => 'RAG Tower Business Center', 'addressLocality' => 'Al Barsha, Dubai',
        'addressRegion' => 'Dubai', 'addressCountry' => 'AE'],
    'hasMap' => 'https://www.google.com/maps/search/?api=1&query=RAG+Tower+Business+Center+Al+Barsha+Dubai',
    'image' => $site . '/og-image.png',
    'contactPoint' => ['@type' => 'ContactPoint', 'contactType' => 'customer support', 'email' => SMD_CONTACT_EMAIL, 'telephone' => SMD_TELEPHONES]];
$crumbs = fn(array $items) => ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' =>
    array_map(fn($i, $n) => ['@type' => 'ListItem', 'position' => $n + 1, 'name' => $i[0], 'item' => $i[1]], $items, array_keys($items))];

try {
    if (isset($pages[$path])) {
        [$title, $desc] = $pages[$path];
        if ($path === '/terms-condition') $canonical = $site . '/terms-of-service';
        if ($path === '/') $jsonld[] = ['@context' => 'https://schema.org'] + $org;
    }
    if ($path === '/blog') {
        $posts = smd_all_posts(false);
        $jsonld[] = ['@context' => 'https://schema.org', '@type' => 'Blog', 'name' => 'Sky Mate Digital Blog', 'url' => $site . '/blog',
            'publisher' => $org, 'blogPost' => array_map(fn($p) => ['@type' => 'BlogPosting', 'headline' => $p['title'],
                'url' => $site . '/blog/' . $p['slug'], 'datePublished' => $p['publishedAt']], array_slice($posts, 0, 20))];
        $jsonld[] = $crumbs([['Home', $site . '/'], ['Blog', $site . '/blog']]);
        $extraHead .= '<link rel="alternate" type="application/rss+xml" title="Sky Mate Digital Blog" href="' . h($site) . '/blog/rss.xml" />' . "\n";
        $body = '<main class="smd-ssr"><h1>Sky Mate Digital Blog</h1><p>' . h($desc) . '</p><ul>';
        foreach ($posts as $p) $body .= '<li><article><h2><a href="/blog/' . h($p['slug']) . '">' . h($p['title']) . '</a></h2><p>' . h($p['excerpt']) . '</p><time datetime="' . h($p['publishedAt']) . '">' . h(substr($p['publishedAt'], 0, 10)) . '</time></article></li>';
        $body .= '</ul></main>';
    } elseif (preg_match('#^/blog/([a-z0-9-]+)$#', $path, $m)) {
        $p = smd_post_by('slug', $m[1], false);
        if (!$p) {
            $status = 404; $title = 'Post not found | Sky Mate Digital'; $desc = 'This article could not be found.'; $robots = 'noindex, follow';
        } else {
            $title = ($p['metaTitle'] ?: $p['title']) . ' | Sky Mate Digital';
            $desc = $p['metaDescription'] ?: $p['excerpt'];
            $canonical = $site . '/blog/' . $p['slug'];
            $image = smd_abs_url((string)($p['featuredImage'] ?? '')); $imageAlt = $p['featuredImageAlt'] ?: $p['title'];
            $ogType = 'article';
            $extraHead .= '<meta property="article:published_time" content="' . h($p['publishedAt']) . '" />' . "\n";
            $extraHead .= '<meta property="article:modified_time" content="' . h($p['updatedAt']) . '" />' . "\n";
            $extraHead .= '<meta property="article:section" content="' . h($p['category']) . '" />' . "\n";
            foreach ($p['tags'] ?? [] as $t) $extraHead .= '<meta property="article:tag" content="' . h($t) . '" />' . "\n";
            $extraHead .= '<link rel="alternate" type="application/rss+xml" title="Sky Mate Digital Blog" href="' . h($site) . '/blog/rss.xml" />' . "\n";
            $jsonld[] = ['@context' => 'https://schema.org', '@type' => 'BlogPosting', 'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $canonical],
                'headline' => mb_substr($p['title'], 0, 110), 'description' => $desc, 'image' => [$image],
                'datePublished' => $p['publishedAt'], 'dateModified' => $p['updatedAt'],
                'author' => ['@type' => 'Person', 'name' => ($p['author']['name'] ?? '') ?: SMD_DEFAULT_AUTHOR, 'jobTitle' => $p['author']['role'] ?? ''],
                'publisher' => $org, 'articleSection' => $p['category'], 'keywords' => implode(', ', $p['tags'] ?? []),
                'wordCount' => str_word_count(strip_tags($p['content'])), 'url' => $canonical];
            $jsonld[] = $crumbs([['Home', $site . '/'], ['Blog', $site . '/blog'], [$p['title'], $canonical]]);
            $body = '<main class="smd-ssr"><nav aria-label="Breadcrumb"><a href="/">Home</a> › <a href="/blog">Blog</a></nav><article>'
                . '<h1>' . h($p['title']) . '</h1>'
                . '<p>By ' . h(($p['author']['name'] ?? '') ?: SMD_DEFAULT_AUTHOR) . ' · <time datetime="' . h($p['publishedAt']) . '">' . h(substr($p['publishedAt'], 0, 10)) . '</time> · ' . h($p['readTime'] ?? '') . '</p>'
                . ($p['featuredImage'] ? '<img src="' . h($p['featuredImage']) . '" alt="' . h($imageAlt) . '" width="1200" height="630" />' : '')
                . $p['content'] . '</article></main>';  // content is sanitized on save
        }
    } elseif (preg_match('#^/admin(/|$)#', $path)) {
        $title = 'Blog Admin | Sky Mate Digital'; $desc = 'Sky Mate Digital blog administration.'; $robots = 'noindex, nofollow';
        header('X-Robots-Tag: noindex, nofollow');
    } elseif (!isset($pages[$path])) {
        $status = 404; $title = 'Page not found | Sky Mate Digital'; $desc = 'The page you are looking for does not exist.'; $robots = 'noindex, follow';
    }
} catch (Throwable $e) {
    error_log('[smd-render] ' . $e->getMessage());
}

if ($shell === '') { http_response_code(500); echo 'Site shell missing'; exit; }
http_response_code($status);
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-cache');
if ($title === '') { echo $shell; exit; }

// strip existing SEO tags from the shell, then inject ours
$shell = preg_replace('#<title>.*?</title>\s*#s', '', $shell);
$shell = preg_replace('#\s*<meta\s+(name|property)="(description|og:[^"]+|twitter:[^"]+)"[^>]*>#i', '', $shell);
$head = '<title>' . h($title) . "</title>\n"
    . '<meta name="description" content="' . h($desc) . '" />' . "\n"
    . '<meta name="robots" content="' . h($robots) . '" />' . "\n"
    . ($status === 200 ? '<link rel="canonical" href="' . h($canonical) . '" />' . "\n" : '')
    . '<meta property="og:site_name" content="' . SMD_SITE_NAME . '" />' . "\n"
    . '<meta property="og:type" content="' . $ogType . '" />' . "\n"
    . '<meta property="og:title" content="' . h($title) . '" />' . "\n"
    . '<meta property="og:description" content="' . h($desc) . '" />' . "\n"
    . '<meta property="og:url" content="' . h($canonical) . '" />' . "\n"
    . '<meta property="og:image" content="' . h($image) . '" />' . "\n"
    . '<meta property="og:image:alt" content="' . h($imageAlt) . '" />' . "\n"
    . '<meta name="twitter:card" content="summary_large_image" />' . "\n"
    . '<meta name="twitter:title" content="' . h($title) . '" />' . "\n"
    . '<meta name="twitter:description" content="' . h($desc) . '" />' . "\n"
    . '<meta name="twitter:image" content="' . h($image) . '" />' . "\n"
    . $extraHead;
foreach ($jsonld as $j) $head .= '<script type="application/ld+json">' . json_encode($j, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) . "</script>\n";
$shell = preg_replace('#</head>#i', $head . '</head>', $shell, 1);
if ($body !== '') {
    $style = '<style>.smd-ssr{max-width:820px;margin:0 auto;padding:120px 20px 60px;font-family:Inter,system-ui,sans-serif;color:#111;line-height:1.7}.smd-ssr img{max-width:100%;height:auto;border-radius:12px}.smd-ssr h1{font-size:2.2rem;line-height:1.2}</style>';
    $shell = str_replace('<div id="root"></div>', $style . '<div id="root">' . $body . '</div>', $shell);
}
echo $shell;
