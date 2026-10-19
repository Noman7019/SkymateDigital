"""Re-applies repo edits to the pretty-printed live bundle.
Usage: python3 scripts/patch-live-bundle.py <live-bundle-pretty.js> src/live/app.js"""
import re, sys
src, dst = sys.argv[1], sys.argv[2]
s = open(src).read()
def rep(old, new, n=1):
    global s
    c = s.count(old); assert c == n, (old[:60], c); s = s.replace(old, new)

# 1. Blog/admin -> PHP API (see scripts/patch_blog.py)
exec(open(__import__('os').path.join(__import__('os').path.dirname(__file__), 'patch_blog.py')).read())
# 1b. Technical/performance/SEO fixes (see scripts/patch_tech.py)
exec(open(__import__('os').path.join(__import__('os').path.dirname(__file__), 'patch_tech.py')).read())
# 1c. Animation-loop performance (see scripts/patch_perf.py)
exec(open(__import__('os').path.join(__import__('os').path.dirname(__file__), 'patch_perf.py')).read())

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

# 4. Site contact email -> contact@skymatedigitals.com (owner's real mail domain)
rep("contact@skymatedigital.com", "contact@skymatedigitals.com", 13)
rep('href: "mailto:skymatedigital@gmail.com",', 'href: "mailto:contact@skymatedigitals.com",')  # old /terms-condition page (unrouted)
# 5. Dubai office address (see scripts/patch_address.py)
exec(open(__import__('os').path.join(__import__('os').path.dirname(__file__), 'patch_address.py')).read())

# 6. Owner content: authors, copy, phones (see scripts/patch_content.py)
exec(open(__import__('os').path.join(__import__('os').path.dirname(__file__), 'patch_content.py')).read())

open(dst, "w").write(s)
print("patched ok")
