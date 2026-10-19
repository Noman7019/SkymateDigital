// ---- Sky Mate Digital: blog API client (injected by scripts/patch_blog.py) ----
const __SMD_API = "/api/blog.php";
let __smdCsrf = "";
async function __smdFetch(action, { method = "GET", params = {}, body, form } = {}) {
  const q = new URLSearchParams({ action });
  for (const [k, v] of Object.entries(params)) if (v !== undefined && v !== null && v !== "") q.set(k, String(v));
  const headers = { Accept: "application/json" };
  if (method !== "GET") {
    if (!__smdCsrf) await __smdAuth.me();
    headers["X-CSRF-Token"] = __smdCsrf;
    if (!form) headers["Content-Type"] = "application/json";
  }
  const res = await fetch(`${__SMD_API}?${q}`, {
    method, headers, credentials: "same-origin",
    body: form ? form : body !== undefined ? JSON.stringify(body) : undefined,
  });
  let data = null;
  try { data = await res.json(); } catch { /* non-JSON */ }
  if (!res.ok) {
    const err = new Error((data && data.error) || `Request failed (${res.status})`);
    err.status = res.status;
    throw err;
  }
  return data;
}
const __smdAuth = {
  async me() { const d = await __smdFetch("me"); __smdCsrf = d.csrf; return d; },
  async login(username, password) {
    const d = await __smdFetch("login", { method: "POST", body: { username, password } });
    __smdCsrf = d.csrf; return d.user;
  },
  async logout() { await __smdFetch("logout", { method: "POST", body: {} }); __smdCsrf = ""; },
};
class __SmdBlogApi {
  async getPosts(e = {}) {
    const { category, search, status = "published", page = 1, limit = 9, tag } = e;
    return __smdFetch("list", { params: { category, search, status, page, limit, tag } });
  }
  async getPostBySlug(slug) {
    try { return (await __smdFetch("post", { params: { slug } })).post; } catch (e) { if (e.status === 404) return null; throw e; }
  }
  async getPostById(id) {
    try { return (await __smdFetch("post", { params: { id } })).post; } catch (e) { if (e.status === 404) return null; throw e; }
  }
  async getRelatedPosts(slug, category, limit = 3) {
    return (await __smdFetch("related", { params: { slug, category, limit } })).posts;
  }
  async createPost(p) { return (await __smdFetch("create", { method: "POST", body: p })).post; }
  async updatePost(id, p) { return (await __smdFetch("update", { method: "POST", params: { id }, body: p })).post; }
  async togglePublishStatus(id) { return (await __smdFetch("toggle", { method: "POST", params: { id }, body: {} })).post; }
  async deletePost(id) { return (await __smdFetch("delete", { method: "POST", params: { id }, body: {} })).ok; }
  async uploadImage(file) {
    if (file.size > 5 * 1024 * 1024) throw new Error("Image too large (max 5 MB)");
    const form = new FormData(); form.append("file", file);
    return (await __smdFetch("upload", { method: "POST", form })).url;
  }
  resetToDefaults() {}
}
function __smdToLocalInput(iso) {
  if (!iso) return "";
  const d = new Date(iso); if (isNaN(d)) return "";
  const p = (n) => String(n).padStart(2, "0");
  return `${d.getFullYear()}-${p(d.getMonth() + 1)}-${p(d.getDate())}T${p(d.getHours())}:${p(d.getMinutes())}`;
}
function __SmdSeoPanel({ seo, setSeo, publishedAt, setPublishedAt, title, excerpt, slug }) {
  const inp = "w-full bg-[#161c2d] border border-white/10 rounded-xl p-3 text-sm text-white placeholder-white/30 focus:outline-none focus:border-[#2A62EB]";
  const lab = "block text-xs font-semibold uppercase tracking-wider text-white/70 mb-1.5";
  const set = (k) => (ev) => setSeo({ ...seo, [k]: ev.target.value });
  const mt = seo.metaTitle || title || "", md = seo.metaDescription || excerpt || "";
  return c.jsxs("div", {
    className: "bg-[#101626] border border-white/10 rounded-2xl p-5 space-y-4",
    children: [
      c.jsx("p", { className: "text-xs font-semibold uppercase tracking-wider text-[#60A5FA]", children: "SEO & Publishing" }),
      c.jsxs("div", { children: [
        c.jsxs("label", { className: lab, children: ["Meta Title ", c.jsxs("span", { className: "text-white/40", style: { textTransform: "none" }, children: ["(", mt.length, "/60, defaults to post title)"] })] }),
        c.jsx("input", { type: "text", maxLength: 120, value: seo.metaTitle, onChange: set("metaTitle"), placeholder: title || "Meta title", className: inp }),
      ] }),
      c.jsxs("div", { children: [
        c.jsxs("label", { className: lab, children: ["Meta Description ", c.jsxs("span", { className: "text-white/40", style: { textTransform: "none" }, children: ["(", md.length, "/160, defaults to excerpt)"] })] }),
        c.jsx("textarea", { rows: 2, maxLength: 320, value: seo.metaDescription, onChange: set("metaDescription"), placeholder: excerpt || "Meta description", className: inp + " resize-none" }),
      ] }),
      c.jsxs("div", { children: [
        c.jsx("label", { className: lab, children: "Featured Image Alt Text" }),
        c.jsx("input", { type: "text", maxLength: 250, value: seo.featuredImageAlt, onChange: set("featuredImageAlt"), placeholder: "Describe the featured image", className: inp }),
      ] }),
      c.jsxs("div", { children: [
        c.jsx("label", { className: lab, children: "Publish Date" }),
        c.jsx("input", { type: "datetime-local", value: __smdToLocalInput(publishedAt), onChange: (ev) => setPublishedAt(ev.target.value ? new Date(ev.target.value).toISOString() : ""), className: inp }),
        c.jsx("p", { className: "text-[11px] text-white/40 mt-1", children: "Leave empty to use the moment you publish. Future dates are hidden until then." }),
      ] }),
      c.jsxs("div", { style: { background: "#fff", borderRadius: 12, padding: 12, fontFamily: "Arial, sans-serif" }, children: [
        c.jsx("div", { style: { fontSize: 11, color: "#5f6368", textTransform: "uppercase", letterSpacing: 0.5, marginBottom: 4 }, children: "Google preview" }),
        c.jsx("div", { style: { fontSize: 16, color: "#1a0dab", whiteSpace: "nowrap", overflow: "hidden", textOverflow: "ellipsis" }, children: mt ? mt + " | Sky Mate Digital" : "" }),
        c.jsx("div", { style: { fontSize: 12, color: "#006621" }, children: "skymatedigital.com/blog/" + (slug || "") }),
        c.jsx("div", { style: { fontSize: 13, color: "#4d5156", lineHeight: 1.4 }, children: md.length > 160 ? md.slice(0, 157) + "..." : md }),
      ] }),
    ],
  });
}
// ---- end blog API client ----
