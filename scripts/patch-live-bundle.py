"""Re-applies repo edits to the pretty-printed live bundle.
Usage: python3 scripts/patch-live-bundle.py <live-bundle-pretty.js> src/live/app.js"""
import re, sys
src, dst = sys.argv[1], sys.argv[2]
s = open(src).read()
def rep(old, new, n=1):
    global s
    c = s.count(old); assert c == n, (old[:60], c); s = s.replace(old, new)

# 1. Admin login: SHA-256 hash from build env; disabled if unset. Remove hard-coded password.
rep('''        if (a.trim().toLowerCase() === "admin" && l === "password123") {''',
'''        if (
          a.trim().toLowerCase() === "admin" &&
          (await __smdCheckAdminPassword(l))
        ) {''')
rep('[n, r] = S.useState("password123"),', '[n, r] = S.useState(""),')
i = s.index('"bg-[#141a2a] p-3 rounded-xl border border-white/5 text-[11px] text-white/60"')
a = s.rindex('c.jsxs("div", {', 0, i)
k = s.index('c.jsx("button", {', i)
s = s[:a] + '''c.jsx("div", {
                    className:
                      "bg-[#141a2a] p-3 rounded-xl border border-white/5 text-[11px] text-white/60",
                    children: __SMD_ADMIN_ENABLED
                      ? "Authorized administrators only."
                      : "Admin login is disabled on this build (VITE_ADMIN_PASSWORD_HASH not set).",
                  }),
                  ''' + s[k:]
assert "password123" not in s
s = '''// Admin password check: compares SHA-256(password) with build-time VITE_ADMIN_PASSWORD_HASH.
// If the env var is unset/invalid, admin login is disabled. Client-side only: NOT real security.
const __SMD_ADMIN_HASH = (import.meta.env.VITE_ADMIN_PASSWORD_HASH || "").trim().toLowerCase();
const __SMD_ADMIN_ENABLED = /^[0-9a-f]{64}$/.test(__SMD_ADMIN_HASH);
async function __smdCheckAdminPassword(pw) {
  if (!__SMD_ADMIN_ENABLED || !pw || !globalThis.crypto?.subtle) return false;
  const buf = await crypto.subtle.digest("SHA-256", new TextEncoder().encode(pw));
  const hex = [...new Uint8Array(buf)].map((b) => b.toString(16).padStart(2, "0")).join("");
  return hex === __SMD_ADMIN_HASH;
}
''' + s

# 2. Routes: drop removed pages (they fall through to the 404 route).
for path in ["/pricing", "/careers", "/services/app-development", "/services/digital-marketing",
             "/services/media-buying", "/services/seo-content-writing"]:
    m = re.search(r'\n\s*c\.jsx\(Ie, \{\n\s*path: "' + re.escape(path) + r'",\n\s*element: c\.jsx\(\w+, \{\}\),\n\s*\}\),', s)
    assert m, path; s = s[:m.start()] + s[m.end():]
rep('element: c.jsx(nq, {}),', 'element: c.jsx(bG, {}),')  # /terms-condition shows live Terms of Service

# 3. Nav / footer / links
rep('{ label: "Store Development", to: "/portfolio" },', '{ label: "Store Development", to: "/services/store-development" },')
rep('{ label: "Branding", to: "/content-creation" },', '{ label: "Branding", to: "/services/add-branding" },')
rep('{ label: "Privacy Policy", to: "/privacy-policy " },', '{ label: "Privacy Policy", to: "/privacy-policy" },')
rep('''    "/blog",
    "/careers",
    "/contact-us",''', '''    "/blog",
    "/contact-us",''')
rep('detailPath: "/services/media-buying ",', 'detailPath: "/services",')
rep('href: "/terms-condition",', 'href: "/terms-of-service",')
rep('    { slug: "media-buying", label: "Media Buying " },\n', '')  # sidebar 'All Services' list on sub-pages
open(dst, "w").write(s)
print("patched ok")
