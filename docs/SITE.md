# Sky Mate Digital site: how this repo is built (branch `sync-live-2026-10-20`)

## Source of truth
The live site (skymatedigital.com, deployed 2026-10-19) was built from code that was never pushed here.
To reproduce it exactly, this branch uses the **live production bundle** as source:

- `reference/live-bundle-2026-10-19.pretty.js`: the live `/assets/index-NOW2XK6f.js`, pretty-printed, with no other changes.
- `scripts/patch-live-bundle.py`: the edits applied on top of it (removed pages, nav links, admin auth).
- `src/live/app.js`: the patched output that Vite builds (`index.html` → `/src/live/app.js`).
- `public/api/`, `public/.htaccess`: PHP blog backend + SEO renderer (see Blog below).
- `public/assets/`: every image/video/font/CSS file the live site serves (including the live CSS `index-1P5hEBS-.css`).

To change the site, edit `src/live/app.js` directly, or edit the patch script and regenerate:
`python3 scripts/patch-live-bundle.py reference/live-bundle-2026-10-19.pretty.js src/live/app.js`.
This is compiled code (React, framer-motion, TipTap, ogl etc. are inlined). It works and looks the same as live,
but it is not idiomatic React source. If the original source turns up, switch back to it.

## Pages
Home `/`, About `/about`, Services `/services`, Contact `/contact-us`, Portfolio `/portfolio` with
Store Development `/services/store-development`, Branding `/services/add-branding`, AI Automation `/services/ai-automation`,
Blog `/blog`, `/blog/:slug`, Admin `/admin/login`, `/admin/blog`, `/admin/blog/new`, `/admin/blog/:id/edit`,
Privacy `/privacy-policy`, Terms `/terms-of-service` (`/terms-condition` shows the same page), `/content-creation`.

Removed: /pricing, /careers, /testimonials, /services/{media-buying,app-development,digital-marketing,
seo-content-writing,website-development,automation-ai,website-maintenance}. `public/.htaccess` 301-redirects these
to `/`. Any other unknown URL shows the site's 404 page.

## Blog (public, server-side, SEO)
The blog is the priority content. Posts are stored **on the server** and every visitor sees them.

### Pieces (all in `public/`, copied to `dist/` by Vite)
| File | Purpose |
|---|---|
| `api/blog.php` | JSON API: `me`, `login`, `logout`, `list`, `post` (by slug/id), `related`, `create`, `update`, `toggle` (publish/unpublish), `delete`, `upload` |
| `api/lib.php` | SQLite (PDO) storage, seed migration, HTML allowlist sanitizer, session helpers (not web-accessible) |
| `api/render.php` | Server-side SEO renderer for **every page route**: per-route `<title>`, description, canonical, Open Graph/Twitter, JSON-LD; for `/blog` and `/blog/:slug` also the full article HTML inside `#root` (React replaces it after load) |
| `api/sitemap.php` | `/sitemap.xml`: all pages + published posts with `lastmod` and image |
| `api/rss.php` | `/blog/rss.xml`: RSS 2.0 feed with full content |
| `api/seed-posts.json` | The 6 default posts from the original site, inserted once on first run (`meta.seeded`) |
| `api/.htaccess` | Denies everything in `/api` except the four public endpoints (so `config.php`, `lib.php`, json, data are 403) |
| `uploads/.htaccess` | Uploaded images can never run as scripts |
| `.htaccess` | https/apex redirect, removed-page 301s, `sitemap.xml`/`rss.xml` rewrites, all routes -> `api/render.php` |

The React blog/admin pages (live design, TipTap editor) call the API through `scripts/blog-api-client.js`,
which `scripts/patch_blog.py` injects into the bundle. localStorage and client-side password checks are gone.

### Data location (survives deploys)
SQLite file `blog.sqlite` goes in the first writable of: `config.php` `data_dir`, then **`<parent of public_html>/smd-blog-data/`**
(outside the web root, preferred), then `public_html/api/data/` (denied by .htaccess, plus an auto-written deny .htaccess).
Uploaded images go to `public_html/uploads/blog/YYYY/MM/`. The deploy workflow excludes `api/data/**`, `uploads/blog/**`
and `smd-blog-data/**` and never deletes remote files, so deploys never wipe posts or images.
**Back up** `smd-blog-data/blog.sqlite` and `uploads/blog/` from hPanel File Manager now and then.

### Admin
- URL `/admin/login`, username `admin`. The password is checked **server-side** with `password_verify()` against a bcrypt hash.
- The hash comes from GitHub secret `BLOG_ADMIN_BCRYPT`. At deploy, the workflow writes it to `api/config.php` (web access denied).
  The plaintext password is never stored in the repo, the build or the server.
  To change the password: `php -r 'echo password_hash("NEW", PASSWORD_BCRYPT, ["cost"=>12]);' > h && gh secret set BLOG_ADMIN_BCRYPT < h && rm h`, then redeploy.
- Session cookie `smd_admin`: HttpOnly, SameSite=Strict, Secure on https, session id regenerated on login.
- CSRF: every POST needs `X-CSRF-Token` (from `?action=me`), plus a same-origin `Origin` check.
- Login rate limit: 5 failed attempts per IP per 15 minutes -> HTTP 429.
- Post HTML is sanitized on save (allowlist of tags/attributes; scripts, iframes, event handlers and `javascript:` URLs removed; `target=_blank` gets `rel="noopener noreferrer"`).
- Image upload: JPG/PNG/WebP/GIF only (checked by content, not extension), max 5 MB, random file names.
- Editor fields: title, slug, excerpt, body (TipTap), category, tags, author name/role/avatar, featured image + **alt text**,
  **meta title**, **meta description**, **publish date** (future date = scheduled; hidden until then), status, featured.

### What search engines get
Every route is served by `render.php` with its own title/description/canonical/OG tags. Blog posts also get
`BlogPosting` + `BreadcrumbList` JSON-LD and the full article HTML in the initial response, so crawlers and social
previews work without running JavaScript. `/sitemap.xml` lists every page and post, and `robots.txt` points to it. Unknown URLs and
missing posts return HTTP 404 with `noindex`, and `/admin/*` is `noindex`.
After deploy: submit `https://skymatedigital.com/sitemap.xml` in Google Search Console and Bing Webmaster Tools.

### Hosting requirements (Hostinger)
PHP **8.1+** (8.2/8.3 recommended; set in hPanel → Advanced → PHP Configuration) with extensions `pdo_sqlite`, `dom`/`xml`,
`mbstring`, `fileinfo`, and `session`. These are enabled by default on Hostinger. `mod_rewrite` (LiteSpeed supports .htaccess).

### Local testing
`npm run build`, then `SMD_CONFIG_FILE=/path/to/test-config.php php -S 127.0.0.1:8090 -t dist scripts/php-dev-router.php`
(the router emulates `.htaccess`). A test config can compute the hash at runtime from an env var, so nothing is stored.

## Contact form
Uses Web3Forms (public access key embedded, as on live). The old `email-api/` Next.js + nodemailer server isn't used
(it can't run on Hostinger shared hosting).

## Deploy
`.github/workflows/deploy-hostinger.yml`: manual (`workflow_dispatch`) only. Builds, checks secrets, writes `api/config.php`, and uploads `dist/` over FTPS with
SamKirkland/FTP-Deploy-Action, never deleting remote files. `dist/.htaccess` comes from `public/.htaccess`.
Secrets: `FTP_HOST`, `FTP_USERNAME`, `FTP_PASSWORD`, `FTP_REMOTE_DIR` (e.g. `/public_html/`), `BLOG_ADMIN_BCRYPT`.
`VITE_ADMIN_PASSWORD_HASH` is no longer used and can be deleted.
Back up the current `public_html/.htaccess` before the first deploy.
