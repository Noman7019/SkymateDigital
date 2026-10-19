# Owner content decisions (exec'd by patch-live-bundle.py; uses its `s` and `rep`).
import re
AUTHOR, ROLE, AVATAR = "Noman Rashid", "Founder & CEO, Sky Mate Digital", "/assets/author-nr.svg"
UAE_TEL, UAE = "+971588621413", "+971 58 862 1413"
PK_TEL, PK = "+923000968859", "+92 300 0968859"

# 6a. Authors: all seeded posts -> Noman Rashid; no stock photos
for name in ["Mursleen Mamoon", "Hamza Sheikh", "Ayesha Malik", "Daniyal Qureshi"]:
    s = s.replace(f'name: "{name}",', f'name: "{AUTHOR}",')
for role in ["Lead Solutions Architect", "Automation & AI Specialist", "Senior Content Strategist", "Director of Growth Marketing"]:
    s = s.replace(f'role: "{role}",', f'role: "{ROLE}",')
s, n = re.subn(r'"https://images\.unsplash\.com/photo-(1534528741775|1507003211169|1494790108377|1500648767791)-[^"]*"', f'"{AVATAR}"', s)
assert n >= 7, n
rep('[L, _] = S.useState("Lead Solutions Architect"),', f'[L, _] = S.useState("{ROLE}"),')
for name in ["Mursleen", "Hamza Sheikh", "Ayesha Malik", "Daniyal"]:
    assert name not in s, name

# 6b. Copy: Pakistan-based -> Dubai and Pakistan based
rep("A Pakistan-based growth partner for ecommerce brands worldwide", "A Dubai and Pakistan based growth partner for ecommerce brands worldwide")
rep("Join a Pakistan-based team delivering", "Join a Dubai and Pakistan based team delivering")
rep("Based in Pakistan, trusted by clients worldwide.", "Based in Dubai and Pakistan, trusted by clients worldwide.")

# 6c. Phones: contact page info list (UAE first), tel: links
rep('    { icon: jB, label: "Phone", value: "+923000968859" },\n',
    f'    {{ icon: jB, label: "Phone (UAE)", value: "{UAE}", href: "tel:{UAE_TEL}" }},\n'
    f'    {{ icon: jB, label: "Phone (Pakistan)", value: "{PK}", href: "tel:{PK_TEL}" }},\n')
# contact list renderer: open maps in new tab, tel: in same tab
rep('children: d.href\n                                              ? c.jsx("a", { href: d.href, target: "_blank", rel: "noopener noreferrer",',
    'children: d.href\n                                              ? c.jsx("a", { href: d.href, ...(d.href.startsWith("tel:") ? {} : { target: "_blank", rel: "noopener noreferrer" }),')

# 6d. Footer: phones under the big email (right aligned on desktop)
old = '''                    children: "contact@skymatedigitals.com",
                  }),
                  c.jsx("div", {
                    className:
                      "mt-6 flex flex-col sm:flex-row sm:justify-end gap-6 sm:gap-16 text-[16px] text-white/70",'''
rep(old, '''                    children: "contact@skymatedigitals.com",
                  }),
                  c.jsxs("div", {
                    className:
                      "mt-3 flex flex-wrap sm:justify-end gap-x-6 gap-y-1 text-[16px] text-white/70",
                    children: [
                      c.jsx("a", { href: "tel:@UAE_TEL", className: "hover:text-[#2A62EB] transition-colors duration-300", children: "🇦🇪 @UAE" }),
                      c.jsx("a", { href: "tel:@PK_TEL", className: "hover:text-[#2A62EB] transition-colors duration-300", children: "🇵🇰 @PK" }),
                    ],
                  }),
                  c.jsx("div", {
                    className:
                      "mt-6 flex flex-col sm:flex-row sm:justify-end gap-6 sm:gap-16 text-[16px] text-white/70",'''.replace("@UAE_TEL", UAE_TEL).replace("@PK_TEL", PK_TEL).replace("@UAE", UAE).replace("@PK", PK))

# 6e. Privacy/Terms sidebar cards: phone links before the address link
pat = re.compile(r'(\n(\s*)c\.jsxs\("a", \{\n\s*href: "https://www\.google\.com/maps/search/[^"]*",\n\s*target: "_blank",\n\s*rel: "noopener noreferrer",\n\s*style: \{ display: "flex", alignItems: "flex-start")')
cnt = 0
def addtel(m):
    global cnt; cnt += 1
    ind = m.group(2)
    links = ""
    for tel, label in [(UAE_TEL, UAE), (PK_TEL, PK)]:
        links += f'''
{ind}c.jsxs("a", {{
{ind}  href: "tel:{tel}",
{ind}  style: {{ display: "flex", alignItems: "center", gap: 7, fontSize: 12, color: "rgba(255,255,255,0.38)", textDecoration: "none", marginTop: 8 }},
{ind}  children: [c.jsx(jB, {{ size: 12 }}), "{label}"],
{ind}}}),'''
    return links + m.group(1)
s = pat.sub(addtel, s)
assert cnt == 2, cnt
