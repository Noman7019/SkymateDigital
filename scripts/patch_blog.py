# Blog/admin patches, exec'd by patch-live-bundle.py (uses its `s` and `rep`).
import os
# 1. API client helpers prepended to the bundle
s = open(os.path.join(os.path.dirname(__file__), "blog-api-client.js")).read() + "\n" + s

# 1a. storage class -> API client
i = s.index("class NG {"); j = s.index("const er = new NG(),")
s = s[:i] + "class NG extends __SmdBlogApi {}\n" + s[j:]

# 1b. auth provider -> server session (no client-side password check)
i = s.index("  p8 = ({ children: t }) => {"); j = s.index("  Fv = () => {", i)
s = s[:i] + """  p8 = ({ children: t }) => {
    const [e, n] = S.useState(null),
      [r, s] = S.useState(!0);
    S.useEffect(() => {
      __smdAuth.me().then((a) => n(a.user)).catch((a) => console.error("Auth check failed", a)).finally(() => s(!1));
    }, []);
    const i = async (a, l) => {
        try {
          const f = await __smdAuth.login(a, l);
          return (n(f), !0);
        } catch (f) {
          if (f && f.status !== 401) Ke.error(f.message);
          return !1;
        }
      },
      o = () => {
        (n(null), __smdAuth.logout().catch(() => {}));
      };
    return c.jsx(b5.Provider, {
      value: { user: e, isAuthenticated: !!e, login: i, logout: o, isLoading: r },
      children: t,
    });
  },
""" + s[j:]
rep('[n, r] = S.useState("password123"),', '[n, r] = S.useState(""),')
i = s.index('"bg-[#141a2a] p-3 rounded-xl border border-white/5 text-[11px] text-white/60"')
a = s.rindex('c.jsxs("div", {', 0, i)
k = s.index('c.jsx("button", {', i)
s = s[:a] + """c.jsx("div", {
                    className:
                      "bg-[#141a2a] p-3 rounded-xl border border-white/5 text-[11px] text-white/60",
                    children: "Authorized administrators only. Sessions are verified by the server.",
                  }),
                  """ + s[k:]
assert "password123" not in s

# 1c. remove the "Reset Demo Data" dialog (posts live on the server now)
i = s.index('title: "Reset blog posts to initial sample posts"')
a = s.rindex("c.jsxs(cP, {", 0, i)
k = s.index('c.jsx("div", { className: "h-5 w-[1px] bg-white/10" }),', i)
s = s[:a] + s[k:]

# 1d. editor: SEO fields (meta title/description, image alt, publish date)
rep("      [te, ce] = S.useState(!1);\n    S.useEffect(() => {\n      if (!t) return;",
    "      [te, ce] = S.useState(!1),\n      [smdSeo, smdSetSeo] = S.useState({ metaTitle: \"\", metaDescription: \"\", featuredImageAlt: \"\" });\n    S.useEffect(() => {\n      if (!t) return;")
rep('            se(ee.publishedAt || ""));',
    '            se(ee.publishedAt || ""),\n            smdSetSeo({ metaTitle: ee.metaTitle || "", metaDescription: ee.metaDescription || "", featuredImageAlt: ee.featuredImageAlt || "" }));')
PUB = """                featured: P,
                ...smdSeo,
                publishedAt: H
                  ? new Date(H).toISOString()
                  : ee === "published"
                    ? new Date().toISOString()
                    : "","""
rep("""                featured: P,
                publishedAt:
                  ee === "published" && !H ? new Date().toISOString() : H,""", PUB)
rep("""                featured: P,
                publishedAt: ee === "published" ? new Date().toISOString() : "",""", PUB)
i = s.index('"Short Excerpt ",')
a = i
for _ in range(2): a = s.rindex('c.jsxs("div", {', 0, a)
s = s[:a] + "c.jsx(__SmdSeoPanel, { seo: smdSeo, setSeo: smdSetSeo, publishedAt: H, setPublishedAt: se, title: l, excerpt: w, slug: d }),\n                          " + s[a:]
