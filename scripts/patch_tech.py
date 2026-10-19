# Technical/performance/SEO fixes (exec'd by patch-live-bundle.py; uses its `s` and `rep`).
import re, json, os
_here = os.path.dirname(os.path.abspath(__file__))

# T1. Large PNG/JPG -> WebP (generated files listed in scripts/webp-map.json; originals kept in /assets)
for old, new in json.load(open(os.path.join(_here, 'webp-map.json'))).items():
    assert s.count('"%s"' % old) >= 1, old
    s = s.replace('"%s"' % old, '"%s"' % new)

# T2. Helpers: lazy video loader + poster naming (injected right after the blog API client header).
rep('const __SMD_API = "/api/blog.php";', '''const __SMD_API = "/api/blog.php";
const __smdPoster = (src) => String(src).replace(/\\.mp4$/, "-poster.webp");
const __smdLazyIO = typeof IntersectionObserver !== "undefined" ? new IntersectionObserver((entries) => {
  for (const e of entries) if (e.isIntersecting) {
    const v = e.target; __smdLazyIO.unobserve(v);
    v.querySelectorAll("source[data-src]").forEach((x) => { x.src = x.dataset.src; x.removeAttribute("data-src"); });
    v.load();
  }
}, { rootMargin: "300px 0px" }) : null;
function __smdLazyVideo(v) {
  if (!v || v.__smdLazy) return; v.__smdLazy = 1;
  if (__smdLazyIO) __smdLazyIO.observe(v);
  else v.querySelectorAll("source[data-src]").forEach((x) => { x.src = x.dataset.src; });
}''')

# T3. Videos: posters everywhere; hero keeps eager src (above the fold); others load when scrolled near.
pat = re.compile(r'c\.jsx\("video", \{(?P<props>(?:(?!c\.jsx\("video").){0,700}?)children: c\.jsx\("source", \{(?P<ws>\s*)src: (?P<src>\w+),?', re.S)
def _vid(m):
    props, src = m.group('props'), m.group('src')
    if 'ref: t,' in props:  # home hero video
        props = props.replace('preload: "auto"', 'preload: "metadata"')
        return 'c.jsx("video", {%s poster: __smdPoster(%s), children: c.jsx("source", {%ssrc: %s,' % (props, src, m.group('ws'), src)
    props = re.sub(r'\s*preload: "[a-z]+",', '', props)
    return ('c.jsx("video", {%s preload: "none", poster: __smdPoster(%s), ref: __smdLazyVideo, children: c.jsx("source", {%s"data-src": %s,'
            % (props, src, m.group('ws'), src))
s, n = pat.subn(_vid, s); assert n == 5, n

# T4. Native lazy-loading/async decoding for all <img> without an explicit loading attribute.
s, n = re.subn(r'c\.jsx\("img", \{(?![^}]*loading:)', 'c.jsx("img", { loading: "lazy", decoding: "async",', s); assert n > 20, n

# T5. Home: second H1 ("What Makes ... Different?") -> H2 (one H1 per page).
rep('''c.jsxs("h1", {
                  className:
                    "text-center font-display font-bold text-[32px] sm:text-[44px] lg:text-6xl''', '''c.jsxs("h2", {
                  className:
                    "text-center font-display font-bold text-[32px] sm:text-[44px] lg:text-6xl''')

# T6. No GA/Clarity on localhost / LAN dev hosts; remove server-rendered fallback copy after the app mounts.
rep('''x3.initialize("G-XM80XF3K6E");
Ise.clarity.init("t4g6ixdsp9");
x3.send({ hitType: "pageview", page: window.location.pathname });
ET(document.getElementById("root")).render(c.jsx(kse, {}));''', '''const __smdIsDev = /^(localhost|127\\.|0\\.0\\.0\\.0|\\[::1\\]|10\\.|192\\.168\\.)/.test(window.location.hostname);
if (!__smdIsDev) {
  x3.initialize("G-XM80XF3K6E");
  Ise.clarity.init("t4g6ixdsp9");
  x3.send({ hitType: "pageview", page: window.location.pathname });
}
ET(document.getElementById("root")).render(c.jsx(kse, {}));
requestAnimationFrame(() => setTimeout(() => { const e = document.getElementById("smd-ssr"); if (e) e.remove(); }, 0));''')

# T7. Session cookie only for admin: /api me() starts a session only on /admin or before a write.
rep('if (!__smdCsrf) await __smdAuth.me();', 'if (!__smdCsrf) await __smdAuth.me(true);')
rep('async me() { const d = await __smdFetch("me"); __smdCsrf = d.csrf; return d; },',
    'async me(init) { const d = await __smdFetch("me", { params: (init || window.location.pathname.startsWith("/admin")) ? { init: 1 } : {} }); __smdCsrf = d.csrf; return d; },')

# T8. Clean LinkedIn company URL (drop ?viewAsMember=true).
s = s.replace('https://www.linkedin.com/company/skymatedigital/?viewAsMember=true', 'https://www.linkedin.com/company/skymatedigital/')
