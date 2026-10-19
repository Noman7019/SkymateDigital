# Live site vs repo: differences (2026-10-20)

Live bundle: `/assets/index-NOW2XK6f.js` (1.29 MB), CSS `/assets/index-1P5hEBS-.css`.
Full backup and a pretty-printed/deobfuscated bundle are kept off-repo in
`/workspace/skymatedigital-live-backup-2026-10-20/` on the Grok Bot box.

## Ported on this branch
- Contact forms (Contact page + HomeGetInTouch) use Web3Forms (`src/lib/web3forms.ts`) with the
  live subjects/fields; key = `VITE_WEB3FORMS_KEY`, defaulting to the live public key.
- og:image / twitter:image -> `https://skymatedigitals.com/og-image.png` (same as live; note "digitals").
- Privacy Policy and Terms of Service text, copied word for word from live (`src/data/*-live.json`, 12 + 18 sections,
  "Last Updated: January 2026"). Layout is a simplified renderer, NOT the live design.
- `/terms-of-service` route (alias of terms page).

## On live but NOT in repo (not reconstructed)
The live site has been repositioned as a Shopify/e-commerce agency and has a much larger feature set:
- Routes: /blog, /blog/:slug, /admin/login, /admin/blog, /admin/blog/new, /admin/blog/:id/edit,
  /careers, /pricing, /services/add-branding, /services/ai-automation, /services/media-buying,
  /services/store-development.
- Repo-only routes missing on live: /testimonials, /services/website-development,
  /services/automation-ai, /services/website-maintenance.
- Blog CMS: TipTap/ProseMirror editor, posts stored in browser localStorage (`smd_blog_posts_v1`)
  with seed posts; client-side-only admin login hard-coded `admin` / `password123` (not real security).
- WebGL effects via `ogl` (+ assets such as AI-vid / n8n-automation mp4).
- Reworked copy across home/services/about/FAQ (~1,500 string literals only on live, ~900 only in repo).
