# Sky Mate Digital site: how this repo is built (branch `sync-live-2026-10-20`)

## Source of truth
The live site (skymatedigital.com, deployed 2026-10-19) was built from code that was never pushed here.
To reproduce it exactly, this branch uses the **live production bundle** as source:

- `reference/live-bundle-2026-10-19.pretty.js`: the live `/assets/index-NOW2XK6f.js`, pretty-printed, with no other changes.
- `scripts/patch-live-bundle.py`: the edits applied on top of it (removed pages, nav links, admin auth).
- `src/live/app.js`: the patched output that Vite builds (`index.html` → `/src/live/app.js`).
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

## Blog admin and its security limits
- Username `admin`. The password is checked in the browser against `VITE_ADMIN_PASSWORD_HASH` (lowercase hex SHA-256),
  set at build time (GitHub secret `VITE_ADMIN_PASSWORD_HASH`). If it is unset, admin login is disabled.
  Generate it with: `printf '%s' 'your-password' | sha256sum`.
- **This is not real security.** The check runs in the visitor's browser, and anyone can read the hash in the public JS
  and try to brute-force it offline. More importantly, blog posts are stored in **that browser's localStorage**
  (`smd_blog_posts_v1`). Posts created in the admin are visible only on the browser that created them. Every
  visitor sees the built-in seed posts. The admin only changes the admin's own browser.
- For real publishing, use a small server-side endpoint, e.g. a PHP script on Hostinger (`public_html/api/posts.php`)
  that keeps posts in a JSON file or MySQL, checks a server-side password with `password_verify`, and is called
  by the blog/admin pages instead of localStorage. Or use a headless CMS.

## Contact form
Uses Web3Forms (public access key embedded, as on live). The old `email-api/` Next.js + nodemailer server isn't used
(it can't run on Hostinger shared hosting).

## Deploy
`.github/workflows/deploy-hostinger.yml`: manual (`workflow_dispatch`) only. Builds and uploads `dist/` over FTPS with
SamKirkland/FTP-Deploy-Action, never deleting remote files. `dist/.htaccess` comes from `public/.htaccess`.
Secrets: `FTP_HOST`, `FTP_USERNAME`, `FTP_PASSWORD`, `FTP_REMOTE_DIR` (e.g. `/public_html/`), `VITE_ADMIN_PASSWORD_HASH`.
Back up the current `public_html/.htaccess` before the first deploy.
