<?php
// Sky Mate Digital blog: shared library (DB, auth, sanitizing, SEO helpers).
declare(strict_types=1);
if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === 'lib.php') { http_response_code(404); exit; }

const SMD_SITE_URL = 'https://skymatedigital.com';
const SMD_SITE_NAME = 'Sky Mate Digital';

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

function smd_data_dir(): string {
    $cands = [];
    if (smd_config()['data_dir']) $cands[] = smd_config()['data_dir'];
    $cands[] = dirname(__DIR__, 2) . '/smd-blog-data';   // outside public_html (preferred)
    $cands[] = __DIR__ . '/data';                         // fallback, denied by api/.htaccess
    foreach ($cands as $d) {
        if (is_dir($d) && is_writable($d)) return $d;
        if (!is_dir($d) && @mkdir($d, 0750, true)) {
            @file_put_contents($d . '/.htaccess', "Require all denied\nDeny from all\n");
            return $d;
        }
    }
    throw new RuntimeException('No writable data directory');
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
    if ($v) return;
    $seed = json_decode((string)@file_get_contents(__DIR__ . '/seed-posts.json'), true) ?: [];
    $pdo->beginTransaction();
    foreach ($seed as $p) {
        $p['content'] = smd_sanitize_html((string)$p['content']);
        $p += ['metaTitle' => '', 'metaDescription' => '', 'featuredImageAlt' => $p['title']];
        smd_save_row($pdo, $p, true);
    }
    $pdo->exec("INSERT OR REPLACE INTO meta (k,v) VALUES ('seeded','1')");
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

function smd_slugify(string $s): string {
    $s = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $s), '-'));
    return substr($s, 0, 100) ?: 'post';
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
function smd_is_admin(): bool { smd_session(); return !empty($_SESSION['admin']); }
function smd_admin_enabled(): bool { $h = (string)smd_config()['admin_hash']; return $h !== '' && str_starts_with($h, '$2'); }

// ---------- output helpers ----------
function h(?string $s): string { return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function smd_abs_url(string $u): string {
    if ($u === '') return smd_site_url() . '/og-image.png';
    return preg_match('#^https?://#i', $u) ? $u : smd_site_url() . '/' . ltrim($u, '/');
}
function smd_xml(string $s): string { return htmlspecialchars($s, ENT_XML1 | ENT_QUOTES, 'UTF-8'); }
