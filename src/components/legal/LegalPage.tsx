// Renders legal content reconstructed from the live site (data in src/data/*-live.json).
type Item = string | { label?: string; icon?: string; text?: string; highlight?: boolean };
type Block = { icon?: string; heading?: string; text?: string };
export type LegalSection = {
  id: string; title: string; type: string;
  intro?: string; content?: string | string[]; note?: string; contact?: string;
  items?: Item[]; cols?: { label: string; items: string[] }[]; blocks?: Block[];
};

const ItemView = ({ it }: { it: Item }) =>
  typeof it === "string" ? <>{it}</> : (
    <span className={it.highlight ? "font-semibold text-[#2A62EB]" : ""}>
      {it.icon ? <span className="mr-2">{it.icon}</span> : null}{it.label ?? it.text}
    </span>
  );

const pillTypes = new Set(["pills", "rights", "services"]);

const LegalPage = ({ kicker, title, accent, subtitle, sections }: {
  kicker: string; title: string; accent: string; subtitle: string; sections: LegalSection[];
}) => (
  <main className="min-h-screen bg-white text-black">
    <section className="w-full px-4 sm:px-6 md:px-12 lg:px-20 pt-24 pb-16 bg-black">
      <p className="text-white text-[13px] uppercase tracking-[0.12em] font-medium mb-4">{kicker}</p>
      <h1 className="font-display text-[40px] sm:text-[56px] md:text-[72px] leading-[0.95] tracking-[-0.03em] max-w-3xl text-white">
        {title} <span className="text-[#3B82F6]">{accent}</span>
      </h1>
      <p className="mt-6 text-white/60 text-[16px] leading-relaxed max-w-xl">{subtitle}</p>
      <p className="mt-4 text-white/40 text-[13px]">Last Updated: January 2026 · Domain: skymatedigital.com</p>
    </section>
    <section className="w-full px-4 sm:px-6 md:px-12 lg:px-20 py-16">
      <div className="max-w-4xl">
        {sections.map((s) => {
          const content = s.content == null ? [] : Array.isArray(s.content) ? s.content : [s.content];
          return (
            <div key={s.id} id={`section-${s.id}`} className="py-10 border-b border-black/10">
              <p className="text-[#2A62EB] text-[13px] font-medium mb-2">{s.id}</p>
              <h2 className="text-[24px] sm:text-[28px] font-semibold mb-4">{s.title}</h2>
              {s.intro && <p className="text-black/70 leading-relaxed mb-4">{s.intro}</p>}
              {content.map((p, i) => <p key={i} className="text-black/70 leading-relaxed mb-3">{p}</p>)}
              {s.cols && (
                <div className="grid sm:grid-cols-2 gap-6">
                  {s.cols.map((c) => (
                    <div key={c.label} className="rounded-xl border border-black/10 p-5">
                      <h3 className="font-semibold mb-3">{c.label}</h3>
                      <ul className="list-disc pl-5 space-y-1 text-black/70">{c.items.map((x) => <li key={x}>{x}</li>)}</ul>
                    </div>
                  ))}
                </div>
              )}
              {s.items && (pillTypes.has(s.type) ? (
                <div className="flex flex-wrap gap-2">
                  {s.items.map((it, i) => <span key={i} className="rounded-full border border-black/15 px-4 py-1.5 text-[14px]"><ItemView it={it} /></span>)}
                </div>
              ) : (
                <ul className="list-disc pl-5 space-y-2 text-black/70">{s.items.map((it, i) => <li key={i}><ItemView it={it} /></li>)}</ul>
              ))}
              {s.blocks && (
                <div className="grid sm:grid-cols-2 gap-6">
                  {s.blocks.map((b, i) => (
                    <div key={i} className="rounded-xl border border-black/10 p-5">
                      <h3 className="font-semibold mb-2">{b.icon} {b.heading}</h3>
                      <p className="text-black/70 leading-relaxed">{b.text}</p>
                    </div>
                  ))}
                </div>
              )}
              {s.contact && <p className="mt-4 text-black/70">Contact: <a className="text-[#2A62EB]" href={`mailto:${s.contact}`}>{s.contact}</a></p>}
              {s.note && <p className="mt-4 text-black/50 text-[14px] italic">{s.note}</p>}
            </div>
          );
        })}
      </div>
    </section>
  </main>
);
export default LegalPage;
