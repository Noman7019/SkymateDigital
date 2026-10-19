<?php
// Sky Mate Digital blog JSON API.  /api/blog.php?action=...
declare(strict_types=1);
require __DIR__ . '/lib.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');

function out($data, int $code = 200): never { http_response_code($code); echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); exit; }
function fail(string $msg, int $code = 400): never { out(['error' => $msg], $code); }
function body(): array { $j = json_decode((string)file_get_contents('php://input'), true); return is_array($j) ? $j : []; }
function require_post(): void { if ($_SERVER['REQUEST_METHOD'] !== 'POST') fail('POST required', 405); }
function require_csrf(): void {
    smd_session();
    $t = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!is_string($t) || !hash_equals($_SESSION['csrf'], $t)) fail('Invalid CSRF token', 403);
    // same-origin check
    $o = $_SERVER['HTTP_ORIGIN'] ?? '';
    if ($o !== '' && parse_url($o, PHP_URL_HOST) !== ($_SERVER['HTTP_HOST'] ? explode(':', $_SERVER['HTTP_HOST'])[0] : '')) fail('Bad origin', 403);
}
function require_admin(): void { require_post(); require_csrf(); if (!smd_is_admin()) fail('Not authenticated', 401); }
function client_ip(): string { return (string)($_SERVER['REMOTE_ADDR'] ?? '0'); }

function normalize_post(array $in, ?array $old): array {
    $now = gmdate('Y-m-d\TH:i:s.000\Z');
    $p = $old ?? ['id' => 'post-' . bin2hex(random_bytes(6)), 'createdAt' => $now, 'author' => [], 'tags' => []];
    $str = fn($k, $max = 500) => array_key_exists($k, $in) ? smd_clean_text($in[$k], $max) : ($p[$k] ?? '');
    $p['title'] = $str('title', 200);
    if ($p['title'] === '') fail('Title is required');
    $p['excerpt'] = $str('excerpt', 600);
    $p['metaTitle'] = $str('metaTitle', 120);
    $p['metaDescription'] = $str('metaDescription', 320);
    $p['featuredImageAlt'] = $str('featuredImageAlt', 250);
    $p['category'] = $str('category', 80) ?: 'General';
    if (array_key_exists('content', $in)) $p['content'] = smd_sanitize_html((string)$in['content']);
    $p['content'] = $p['content'] ?? '';
    if (array_key_exists('featuredImage', $in)) $p['featuredImage'] = smd_clean_url($in['featuredImage']);
    if (array_key_exists('tags', $in) && is_array($in['tags']))
        $p['tags'] = array_values(array_unique(array_filter(array_map(fn($t) => smd_clean_text($t, 50), array_slice($in['tags'], 0, 20)))));
    if (array_key_exists('author', $in) && is_array($in['author']))
        $p['author'] = ['name' => smd_clean_text($in['author']['name'] ?? '', 100), 'role' => smd_clean_text($in['author']['role'] ?? '', 100),
            'avatar' => smd_clean_url($in['author']['avatar'] ?? '')];
    if (empty($p['author']['name'])) $p['author'] = array_merge(is_array($p['author'] ?? null) ? $p['author'] : [], ['name' => SMD_DEFAULT_AUTHOR]);
    if (array_key_exists('featured', $in)) $p['featured'] = (bool)$in['featured'];
    $status = array_key_exists('status', $in) ? (string)$in['status'] : ($p['status'] ?? 'draft');
    $p['status'] = in_array($status, ['published', 'draft'], true) ? $status : 'draft';
    if (array_key_exists('publishedAt', $in)) {
        $ts = $in['publishedAt'] ? strtotime((string)$in['publishedAt']) : false;
        $p['publishedAt'] = $ts ? gmdate('Y-m-d\TH:i:s.000\Z', $ts) : '';
    }
    if ($p['status'] === 'published' && empty($p['publishedAt'])) $p['publishedAt'] = $now;
    $p['publishedAt'] = $p['publishedAt'] ?? '';
    // unique slug
    $base = smd_slugify((string)(array_key_exists('slug', $in) && $in['slug'] !== '' ? $in['slug'] : ($p['slug'] ?? $p['title'])));
    $slug = $base; $i = 1;
    $st = smd_db()->prepare('SELECT id FROM posts WHERE slug = ?');
    while (true) { $st->execute([$slug]); $id = $st->fetchColumn(); if (!$id || $id === $p['id']) break; $slug = $base . '-' . $i++; }
    $p['slug'] = $slug;
    $p['readTime'] = smd_read_time($p['content']);
    $p['updatedAt'] = $now;
    return $p;
}

try {
    $action = (string)($_GET['action'] ?? '');
    switch ($action) {
    case 'me':
        smd_session();
        out(['user' => smd_is_admin() ? ['username' => 'admin', 'name' => 'Administrator', 'role' => 'Super Admin'] : null,
             'csrf' => $_SESSION['csrf'], 'loginEnabled' => smd_admin_enabled()]);

    case 'login':
        require_post(); require_csrf();
        if (!smd_admin_enabled()) fail('Admin login is not configured on this server', 503);
        $db = smd_db(); $ip = client_ip(); $now = time();
        $db->prepare('DELETE FROM login_attempts WHERE at < ?')->execute([$now - 900]);
        $st = $db->prepare('SELECT COUNT(*) FROM login_attempts WHERE ip = ? AND at >= ?'); $st->execute([$ip, $now - 900]);
        if ((int)$st->fetchColumn() >= 5) fail('Too many login attempts. Try again in 15 minutes.', 429);
        $b = body();
        $ok = strtolower(trim((string)($b['username'] ?? ''))) === smd_config()['admin_user']
            && password_verify((string)($b['password'] ?? ''), (string)smd_config()['admin_hash']);
        if (!$ok) { $db->prepare('INSERT INTO login_attempts (ip, at) VALUES (?, ?)')->execute([$ip, $now]); usleep(400000); fail('Invalid username or password', 401); }
        $db->prepare('DELETE FROM login_attempts WHERE ip = ?')->execute([$ip]);
        session_regenerate_id(true);
        $_SESSION['admin'] = true; $_SESSION['csrf'] = bin2hex(random_bytes(32));
        out(['user' => ['username' => 'admin', 'name' => 'Administrator', 'role' => 'Super Admin'], 'csrf' => $_SESSION['csrf']]);

    case 'logout':
        require_post(); require_csrf();
        $_SESSION = []; session_destroy();
        out(['ok' => true]);

    case 'list':
        $admin = ($_GET['status'] ?? 'published') !== 'published' && smd_is_admin();
        $posts = smd_all_posts($admin);
        $status = (string)($_GET['status'] ?? 'published');
        if ($admin && in_array($status, ['published', 'draft'], true)) $posts = array_values(array_filter($posts, fn($p) => $p['status'] === $status));
        if (($c = (string)($_GET['category'] ?? '')) !== '' && $c !== 'All') $posts = array_values(array_filter($posts, fn($p) => strcasecmp($p['category'], $c) === 0));
        if (($t = (string)($_GET['tag'] ?? '')) !== '') $posts = array_values(array_filter($posts, fn($p) => in_array(strtolower($t), array_map('strtolower', $p['tags'] ?? []), true)));
        if (($q = mb_strtolower(trim((string)($_GET['search'] ?? '')))) !== '')
            $posts = array_values(array_filter($posts, fn($p) => str_contains(mb_strtolower($p['title'] . ' ' . $p['excerpt'] . ' ' . implode(' ', $p['tags'] ?? []) . ' ' . ($p['author']['name'] ?? '')), $q)));
        $limit = max(1, min(100, (int)($_GET['limit'] ?? 9))); $page = max(1, (int)($_GET['page'] ?? 1));
        $total = count($posts);
        out(['posts' => array_slice($posts, ($page - 1) * $limit, $limit), 'total' => $total, 'page' => $page, 'limit' => $limit,
             'totalPages' => max(1, (int)ceil($total / $limit))]);

    case 'post':
        $admin = smd_is_admin();
        $p = isset($_GET['id']) ? smd_post_by('id', (string)$_GET['id'], $admin) : smd_post_by('slug', (string)($_GET['slug'] ?? ''), $admin);
        $p ? out(['post' => $p]) : fail('Post not found', 404);

    case 'related':
        $slug = (string)($_GET['slug'] ?? ''); $cat = strtolower((string)($_GET['category'] ?? '')); $n = max(1, min(12, (int)($_GET['limit'] ?? 3)));
        $all = array_values(array_filter(smd_all_posts(false), fn($p) => $p['slug'] !== $slug));
        $same = array_values(array_filter($all, fn($p) => strtolower($p['category']) === $cat));
        $rest = array_values(array_filter($all, fn($p) => strtolower($p['category']) !== $cat));
        out(['posts' => array_slice(array_merge($same, $rest), 0, $n)]);

    case 'create':
        require_admin();
        $p = normalize_post(body(), null);
        smd_save_row(smd_db(), $p, true);
        out(['post' => $p], 201);

    case 'update':
        require_admin();
        $old = smd_post_by('id', (string)($_GET['id'] ?? ''), true) ?? fail('Post not found', 404);
        $p = normalize_post(body(), $old);
        smd_save_row(smd_db(), $p, false);
        out(['post' => $p]);

    case 'toggle':
        require_admin();
        $old = smd_post_by('id', (string)($_GET['id'] ?? ''), true) ?? fail('Post not found', 404);
        $p = normalize_post(['status' => $old['status'] === 'published' ? 'draft' : 'published'], $old);
        smd_save_row(smd_db(), $p, false);
        out(['post' => $p]);

    case 'delete':
        require_admin();
        $st = smd_db()->prepare('DELETE FROM posts WHERE id = ?'); $st->execute([(string)($_GET['id'] ?? '')]);
        out(['ok' => $st->rowCount() > 0]);

    case 'upload':
        require_admin();
        $f = $_FILES['file'] ?? null;
        if (!$f || $f['error'] !== UPLOAD_ERR_OK) fail('No file uploaded');
        if ($f['size'] > 5 * 1024 * 1024) fail('Image too large (max 5 MB)', 413);
        $types = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
        if (!isset($types[$mime]) || @getimagesize($f['tmp_name']) === false) fail('Only JPG, PNG, WebP or GIF images are allowed', 415);
        $dir = dirname(__DIR__) . '/uploads/blog/' . gmdate('Y/m');
        if (!is_dir($dir) && !mkdir($dir, 0755, true)) fail('Upload directory not writable', 500);
        $name = bin2hex(random_bytes(8)) . '.' . $types[$mime];
        if (!move_uploaded_file($f['tmp_name'], "$dir/$name")) fail('Failed to store file', 500);
        @chmod("$dir/$name", 0644);
        out(['url' => '/uploads/blog/' . gmdate('Y/m') . '/' . $name], 201);

    default:
        fail('Unknown action', 404);
    }
} catch (Throwable $e) {
    error_log('[smd-blog] ' . $e->getMessage());
    fail('Server error', 500);
}
