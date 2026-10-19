<?php
// Sky Mate Digital blog: shared library (DB, auth, sanitizing, SEO helpers).
declare(strict_types=1);
if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === 'lib.php') { http_response_code(404); exit; }
// Never print PHP errors into HTML/JSON responses; log them to a protected file instead.
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

const SMD_SITE_URL = 'https://skymatedigital.com';
const SMD_SITE_NAME = 'Sky Mate Digital';
const SMD_DEFAULT_AUTHOR = 'Noman Rashid';
const SMD_CONTACT_EMAIL = 'contact@skymatedigitals.com';
const SMD_DEFAULT_AUTHOR_ROLE = 'Founder & CEO, Sky Mate Digital';
const SMD_DEFAULT_AUTHOR_AVATAR = '/assets/author-nr.svg';
const SMD_TELEPHONES = ['+971 58 862 1413', '+92 300 0968859'];

function smd_config(): array {
    static $cfg = null;
    if ($cfg !== null) return $cfg;
    $cfg = ['admin_user' => 'admin', 'admin_hash' => '', 'data_dir' => '', 'site_url' => SMD_SITE_URL];
    $f = __DIR__ . '/config.php';
    if (is_file($f)) { $c = include $f; if (is_array($c)) $cfg = array_merge($cfg, $c); }
    if (getenv('SMD_CONFIG_FILE') && is_file((string)getenv('SMD_CONFIG_FILE'))) { // local testing only
        $c = include (string)getenv('SMD_CONFIG_FILE'); if (is_array($c)) $cfg = array_merge($cfg, $c);
    }
    return $cfg;
}
function smd_site_url(): string { return rtrim((string)smd_config()['site_url'], '/'); }

// True when $path is allowed by open_basedir (or no restriction is set). Avoids PHP warnings.
function smd_path_allowed(string $path): bool {
    $ob = (string)ini_get('open_basedir');
    if ($ob === '') return true;
    $path = rtrim(str_replace('\\', '/', $path), '/') . '/';
    foreach (explode(PATH_SEPARATOR, $ob) as $base) {
        $base = rtrim(str_replace('\\', '/', trim($base)), '/');
        if ($base !== '' && str_starts_with($path, $base . '/')) return true;
    }
    return false;
}
function smd_data_dir(): string {
    static $dir = null;
    if ($dir !== null) return $dir;
    $cands = [];
    if (smd_config()['data_dir']) $cands[] = rtrim((string)smd_config()['data_dir'], '/');  // set by deploy config
    $cands[] = dirname(__DIR__, 2) . '/smd-blog-data';   // outside public_html (only if open_basedir allows)
    $cands[] = __DIR__ . '/data';                         // fallback inside public_html, denied by .htaccess
    foreach ($cands as $d) {
        if (!smd_path_allowed($d)) continue;
        if (@is_dir($d)) { if (@is_writable($d)) return $dir = smd_protect_dir($d); continue; }
        if (@mkdir($d, 0750, true)) return $dir = smd_protect_dir($d);
    }
    throw new RuntimeException('No writable data directory');
}
function smd_protect_dir(string $d): string {
    if (!@is_file($d . '/.htaccess')) @file_put_contents($d . '/.htaccess', "Require all denied\n");
    // log PHP errors next to the data (never web-readable)
    ini_set('error_log', $d . '/php-errors.log');
    return $d;
}

function smd_db(): PDO {
    static $pdo = null;
    if ($pdo) return $pdo;
    $pdo = new PDO('sqlite:' . smd_data_dir() . '/blog.sqlite', null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
    $pdo->exec('PRAGMA journal_mode=WAL; PRAGMA busy_timeout=5000;');
    $pdo->exec('CREATE TABLE IF NOT EXISTS posts (
        id TEXT PRIMARY KEY, slug TEXT UNIQUE NOT NULL, status TEXT NOT NULL DEFAULT "draft",
        published_at TEXT, created_at TEXT NOT NULL, updated_at TEXT NOT NULL, data TEXT NOT NULL)');
    $pdo->exec('CREATE TABLE IF NOT EXISTS meta (k TEXT PRIMARY KEY, v TEXT)');
    $pdo->exec('CREATE TABLE IF NOT EXISTS login_attempts (ip TEXT, at INTEGER)');
    smd_migrate($pdo);
    return $pdo;
}

// Migration: seed the 6 default posts from the original site once.
function smd_migrate(PDO $pdo): void {
    $v = $pdo->query("SELECT v FROM meta WHERE k='seeded'")->fetchColumn();
    if ($v) { smd_migrate_authors($pdo); return; }
    $seed = json_decode((string)@file_get_contents(__DIR__ . '/seed-posts.json'), true) ?: [];
    $pdo->beginTransaction();
    foreach ($seed as $p) {
        $p['content'] = smd_sanitize_html((string)$p['content']);
        $p += ['metaTitle' => '', 'metaDescription' => '', 'featuredImageAlt' => $p['title']];
        smd_save_row($pdo, $p, true);
    }
    $pdo->exec("INSERT OR REPLACE INTO meta (k,v) VALUES ('seeded','1')");
    $pdo->commit();
    smd_migrate_authors($pdo);
}

// Migration 2 (2026-10-20): all posts authored by Noman Rashid; remove placeholder personas and stock photos.
function smd_migrate_authors(PDO $pdo): void {
    if ($pdo->query("SELECT v FROM meta WHERE k='authors_v2'")->fetchColumn()) return;
    $pdo->beginTransaction();
    foreach ($pdo->query('SELECT data FROM posts')->fetchAll(PDO::FETCH_COLUMN) as $row) {
        $p = json_decode($row, true);
        $p['author'] = ['name' => SMD_DEFAULT_AUTHOR, 'role' => SMD_DEFAULT_AUTHOR_ROLE, 'avatar' => SMD_DEFAULT_AUTHOR_AVATAR];
        smd_save_row($pdo, $p, false);
    }
    $pdo->exec("INSERT OR REPLACE INTO meta (k,v) VALUES ('authors_v2','1')");
    $pdo->commit();
}

function smd_save_row(PDO $pdo, array $p, bool $insert): void {
    $sql = $insert
        ? 'INSERT OR IGNORE INTO posts (id,slug,status,published_at,created_at,updated_at,data) VALUES (:id,:slug,:status,:pub,:c,:u,:data)'
        : 'UPDATE posts SET slug=:slug,status=:status,published_at=:pub,created_at=:c,updated_at=:u,data=:data WHERE id=:id';
    $pdo->prepare($sql)->execute([':id' => $p['id'], ':slug' => $p['slug'], ':status' => $p['status'],
        ':pub' => $p['publishedAt'] ?: null, ':c' => $p['createdAt'], ':u' => $p['updatedAt'],
        ':data' => json_encode($p, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)]);
}

function smd_all_posts(bool $includeDrafts = false): array {
    $sql = 'SELECT data FROM posts' . ($includeDrafts ? '' : " WHERE status='published'");
    $rows = smd_db()->query($sql)->fetchAll(PDO::FETCH_COLUMN);
    $posts = array_map(fn($r) => json_decode($r, true), $rows);
    if (!$includeDrafts) {  // scheduled posts: hide until publish date
        $now = time();
        $posts = array_values(array_filter($posts, fn($p) => !$p['publishedAt'] || strtotime($p['publishedAt']) <= $now));
    }
    usort($posts, fn($a, $b) => strcmp($b['publishedAt'] ?: $b['createdAt'], $a['publishedAt'] ?: $a['createdAt']));
    return $posts;
}
function smd_post_by(string $field, string $val, bool $includeDrafts = false): ?array {
    $col = $field === 'id' ? 'id' : 'slug';
    $st = smd_db()->prepare("SELECT data FROM posts WHERE $col = ?");
    $st->execute([$val]);
    $r = $st->fetchColumn();
    if (!$r) return null;
    $p = json_decode($r, true);
    if (!$includeDrafts && ($p['status'] !== 'published' || ($p['publishedAt'] && strtotime($p['publishedAt']) > time()))) return null;
    return $p;
}

// ASCII slug; non-Latin titles (e.g. Urdu) are transliterated when ext-intl is available,
// otherwise kept as lowercase Unicode letters/digits. Uniqueness is enforced by the caller.
function smd_slugify(string $s): string {
    $s = trim($s);
    if (function_exists('transliterator_transliterate')) {
        $t = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $s);
        if (is_string($t) && preg_match('/[A-Za-z0-9]/', $t)) $s = $t;
    }
    $s = mb_strtolower((string)preg_replace('/[^\p{L}\p{M}\p{N}]+/u', '-', $s), 'UTF-8');
    $s = trim($s, '-');
    return trim(mb_substr($s, 0, 100, 'UTF-8'), '-') ?: 'post';
}
function smd_read_time(string $html): string {
    $words = str_word_count(strip_tags($html));
    return max(1, (int)ceil($words / 200)) . ' min read';
}

// ---------- HTML sanitizer (allowlist) ----------
function smd_sanitize_html(string $html): string {
    $allowed = ['p'=>[], 'br'=>[], 'h2'=>['id'], 'h3'=>['id'], 'h4'=>['id'], 'strong'=>[], 'b'=>[], 'em'=>[], 'i'=>[], 'u'=>[], 's'=>[],
        'blockquote'=>[], 'ul'=>[], 'ol'=>['start'], 'li'=>[], 'a'=>['href','title','target','rel'], 'img'=>['src','alt','title','width','height'],
        'code'=>[], 'pre'=>[], 'hr'=>[], 'span'=>[], 'mark'=>[], 'sub'=>[], 'sup'=>[], 'figure'=>[], 'figcaption'=>[],
        'table'=>[], 'thead'=>[], 'tbody'=>[], 'tr'=>[], 'th'=>['colspan','rowspan'], 'td'=>['colspan','rowspan']];
    $drop = ['script','style','iframe','object','embed','form','input','button','textarea','select','link','meta','svg','math','template','noscript'];
    if (trim($html) === '') return '';
    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $doc->loadHTML('<?xml encoding="utf-8"?><div id="smd-root">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET);
    libxml_clear_errors();
    $root = $doc->getElementById('smd-root') ?? $doc->documentElement;
    $walk = function (DOMNode $node) use (&$walk, $allowed, $drop, $doc) {
        for ($i = $node->childNodes->length - 1; $i >= 0; $i--) {
            $ch = $node->childNodes->item($i);
            if ($ch instanceof DOMComment || $ch instanceof DOMProcessingInstruction) { $node->removeChild($ch); continue; }
            if (!($ch instanceof DOMElement)) continue;
            $tag = strtolower($ch->tagName);
            if (in_array($tag, $drop, true)) { $node->removeChild($ch); continue; }
            $walk($ch);
            if (!isset($allowed[$tag])) {  // unwrap unknown tags, keep text
                while ($ch->firstChild) $node->insertBefore($ch->firstChild, $ch);
                $node->removeChild($ch); continue;
            }
            foreach (iterator_to_array($ch->attributes) as $attr) {
                $an = strtolower($attr->name);
                if (!in_array($an, $allowed[$tag], true)) { $ch->removeAttribute($attr->name); continue; }
                if (in_array($an, ['href','src'], true)) {
                    $v = trim($attr->value);
                    $ok = preg_match('#^(https?://|/(?!/)|\#|mailto:)#i', $v);
                    if (!$ok) $ch->removeAttribute($attr->name);
                }
            }
            if ($tag === 'a') {
                if ($ch->getAttribute('target') === '_blank') $ch->setAttribute('rel', 'noopener noreferrer');
                elseif ($ch->hasAttribute('target')) $ch->removeAttribute('target');
            }
        }
    };
    $walk($root);
    $out = '';
    foreach ($root->childNodes as $c) $out .= $doc->saveHTML($c);
    return trim($out);
}
function smd_clean_text($v, int $max = 500): string {
    return mb_substr(trim(strip_tags((string)$v)), 0, $max);
}
function smd_clean_url($v): string {
    $v = trim((string)$v);
    if ($v === '') return '';
    return preg_match('#^(https?://|/(?!/))#i', $v) && !preg_match('/[\s"<>]/', $v) ? mb_substr($v, 0, 1000) : '';
}

// ---------- session / auth ----------
function smd_session(): void {
    if (session_status() === PHP_SESSION_ACTIVE) return;
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    session_name('smd_admin');
    session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'secure' => $secure, 'httponly' => true, 'samesite' => 'Strict']);
    session_start();
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
// Only touch the session when the browser already has one: public visitors never get a cookie.
function smd_is_admin(): bool {
    if (session_status() !== PHP_SESSION_ACTIVE && empty($_COOKIE['smd_admin'])) return false;
    smd_session(); return !empty($_SESSION['admin']);
}
function smd_admin_enabled(): bool { $h = (string)smd_config()['admin_hash']; return $h !== '' && str_starts_with($h, '$2'); }

// ---------- output helpers ----------
function h(?string $s): string { return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function smd_abs_url(string $u): string {
    if ($u === '') return smd_site_url() . '/og-image.png';
    return preg_match('#^https?://#i', $u) ? $u : smd_site_url() . '/' . ltrim($u, '/');
}
function smd_xml(string $s): string { return htmlspecialchars($s, ENT_XML1 | ENT_QUOTES, 'UTF-8'); }

// ---------- uploads ----------
const SMD_UPLOAD_MAX_PX = 4000;    // reject anything larger than 4000x4000
const SMD_UPLOAD_FIT_PX = 1920;    // stored images are downscaled to fit 1920x1920 (needs ext-gd)
// Downscale (and strip metadata from) an uploaded JPG/PNG/WebP in place. GIFs are left as-is (animation).
function smd_resize_image(string $file, string $mime, int $w, int $h): void {
    if (!function_exists('imagecreatetruecolor') || $mime === 'image/gif') return;
    $scale = min(1, SMD_UPLOAD_FIT_PX / max($w, $h));
    $load = ['image/jpeg' => 'imagecreatefromjpeg', 'image/png' => 'imagecreatefrompng', 'image/webp' => 'imagecreatefromwebp'][$mime] ?? null;
    if (!$load || !function_exists($load)) return;
    $src = @$load($file); if (!$src) return;
    $nw = max(1, (int)round($w * $scale)); $nh = max(1, (int)round($h * $scale));
    $dst = imagecreatetruecolor($nw, $nh);
    if ($mime !== 'image/jpeg') { imagealphablending($dst, false); imagesavealpha($dst, true); }
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
    $ok = match ($mime) { 'image/jpeg' => imagejpeg($dst, $file, 82), 'image/png' => imagepng($dst, $file, 8), 'image/webp' => imagewebp($dst, $file, 80) };
    imagedestroy($src); imagedestroy($dst);
    if (!$ok) error_log('[smd-blog] resize failed for ' . basename($file));
}
