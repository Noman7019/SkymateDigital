<?php
// Local emulation of public/.htaccess for `php -S` (testing only).
$root = $_SERVER['DOCUMENT_ROOT'];
$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
if (preg_match('#^/(pricing|careers|testimonials)/?$#i', $path) || preg_match('#^/services/(media-buying|app-development|digital-marketing|seo-content-writing|website-development|automation-ai|website-maintenance)/?$#i', $path)) { header('Location: /', true, 301); exit; }
if ($path !== '/' && str_ends_with($path, '/') && !is_dir($root.$path)) { header('Location: '.rtrim($path,'/'), true, 301); exit; }
if (preg_match('#^/api/data(/|$)#', $path)) { http_response_code(403); exit; }
if (preg_match('#^/api/(?!(blog|render|sitemap|rss)\.php$)#', $path)) { http_response_code(403); exit; }
if (preg_match('#^/uploads/.*\.(php|phtml|phar|html?|svg)$#i', $path)) { http_response_code(403); exit; }
$map = ['/sitemap.xml' => '/api/sitemap.php', '/blog/rss.xml' => '/api/rss.php'];
if (isset($map[$path])) { require $root.$map[$path]; return true; }
if (preg_match('#^/api/(blog|render|sitemap|rss)\.php$#', $path)) { require $root.$path; return true; }
if ($path === '/' || $path === '/index.html' || !is_file($root.$path)) { require $root.'/api/render.php'; return true; }
return false;
