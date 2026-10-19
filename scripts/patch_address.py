# Dubai office address (exec'd by patch-live-bundle.py; uses its `s` and `rep`).
import re
ADDR = "Dubai Office: RAG Tower Business Center, Al Barsha, Dubai, UAE"
MAPS = "https://www.google.com/maps/search/?api=1&query=RAG+Tower+Business+Center+Al+Barsha+Dubai"

# 5a. footer: second location column next to the existing Pakistan block
old_tail = '''                        " Street 329, Al Barsha 1, Dubai.",
                      ],
                    }),
                  }),'''
assert s.count(old_tail) == 1
i = s.index(old_tail)
j = s.rindex('children: c.jsxs("p", {', 0, i)
s = s[:j] + 'children: [c.jsxs("p", {' + s[j + len('children: c.jsxs("p", {'):]
s = s.replace(old_tail, '''                        " Street 329, Al Barsha 1, Dubai.",
                      ],
                    }),
                    c.jsxs("p", {
                      className: "text-left",
                      children: [
                        c.jsxs("b", {
                          className: "text-white flex items-center gap-2",
                          children: [
                            c.jsx("span", { children: "\U0001F1E6\U0001F1EA" }),
                            " Dubai Office",
                          ],
                        }),
                        c.jsxs("a", {
                          href: "%s",
                          target: "_blank",
                          rel: "noopener noreferrer",
                          className: "hover:text-[#2A62EB] transition-colors duration-300",
                          children: ["RAG Tower Business Center,", c.jsx("br", {}), " Al Barsha, Dubai, UAE"],
                        }),
                      ],
                    }),
                  ],
                  }),''' % MAPS, 1)

# 5b. contact page info list: address item with Google Maps link
rep('    { icon: CA, label: "Office", value: "Serving clients worldwide" },\n',
    '    { icon: CA, label: "Office", value: "Serving clients worldwide" },\n'
    '    { icon: CA, label: "Dubai Office", value: "RAG Tower Business Center, Al Barsha, Dubai, UAE", href: "%s" },\n' % MAPS)
rep('''                                          c.jsx("p", {
                                            className:
                                              "text-sm font-medium text-[#0a0a0a]",
                                            children: d.value,
                                          }),''',
'''                                          c.jsx("p", {
                                            className:
                                              "text-sm font-medium text-[#0a0a0a]",
                                            children: d.href
                                              ? c.jsx("a", { href: d.href, target: "_blank", rel: "noopener noreferrer", className: "hover:text-[#2A62EB] transition-colors", children: d.value })
                                              : d.value,
                                          }),''')

# 5c. privacy + terms sidebar contact cards: address link under email/website
pat = re.compile(r'(c\.jsx\(vs, \{ size: 12 \}\),\n\s*"skymatedigital\.com",\n(\s*)\],\n(\s*)\}\),)')
n = 0
def add(m):
    global n; n += 1
    ind = m.group(3)
    return m.group(1) + f'''
{ind}c.jsxs("a", {{
{ind}  href: "{MAPS}",
{ind}  target: "_blank",
{ind}  rel: "noopener noreferrer",
{ind}  style: {{ display: "flex", alignItems: "flex-start", gap: 7, fontSize: 12, color: "rgba(255,255,255,0.38)", textDecoration: "none", marginTop: 8, lineHeight: 1.5 }},
{ind}  children: [c.jsx(CA, {{ size: 12, style: {{ flexShrink: 0, marginTop: 2 }} }}), "{ADDR}"],
{ind}}}),'''
s = pat.sub(add, s)
assert n == 2, n
