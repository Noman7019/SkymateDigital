import { submitWeb3Forms } from "@/lib/web3forms";
import React, { useRef, useState } from "react";
import { Mail } from "lucide-react";
import Reveal from "../ui/Reveal";

const categories = [
  "Website Development",
  "App Development",
  "Digital Marketing",
  "Automation & AI",
  "Website Maintenance",
  "SEO & Content",
] as const;

const underlineInput =
  "w-full border-0 border-b border-[#d0d0d0] bg-transparent px-0 py-2.5 text-[15px] text-[#0a0a0a] placeholder:text-[#b0b0b0] focus:border-[#2A62EB] focus:ring-0 focus:outline-none transition-colors";

const HomeGetInTouch = () => {
  const [category, setCategory] = useState<(typeof categories)[number]>(categories[0]);
  const [submitted, setSubmitted] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);

  // Load Lead Connector HQ booking script
  React.useEffect(() => {
    const script = document.createElement("script");
    script.src = "https://api.leadconnectorhq.com/js/form_embed.js";
    script.type = "text/javascript";
    script.async = true;
    document.body.appendChild(script);
  }, []);

  const handleSubmit = async (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();

    const form = e.currentTarget;
    const formData = new FormData(form);

    const name = (formData.get("name") as string) || "";
    const email = (formData.get("email") as string) || "";
    const phone = (formData.get("phone") as string) || "";
    const message = (formData.get("message") as string) || "";

    setIsSubmitting(true);
    setError(null);

    try {
      await submitWeb3Forms({ subject: `New Inquiry: ${category} — ${name}`, replyto: email, name, email, phone: phone || "Not provided", message, category });

      setSubmitted(true);
      form.reset();
      window.setTimeout(() => setSubmitted(false), 5000);
    } catch (err) {
      console.error(err);
      setError("We couldn't send your message. Please try again in a moment.");
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <section
      className="py-16 sm:py-20 lg:py-24 bg-[#90afff41]"
      // style={{
      //   background: "linear-gradient(180deg, #fff0d8 0%, #fff7ec 38%, #ffffff 100%)",
      // }}
    >
      <div className="w-full px-4 sm:px-6 md:px-12 lg:px-20">
        <div className="mx-auto max-w-[1100px] rounded-[28px] bg-white p-6 sm:p-10 lg:p-12 shadow-[0_24px_80px_rgba(15,23,42,0.06)] border border-[#f2e8dc]">
          <div className="grid gap-12 lg:grid-cols-2 lg:gap-16 lg:items-start">
            {/* Left: headline + graphic */}
            <div className="relative min-h-[280px] lg:min-h-[360px]">
              <h2 className="font-display text-[40px] sm:text-[52px] lg:text-[56px] font-bold leading-[1.05] text-[#0a0a0a] relative z-10">
                Let&apos;s start
                <br />
                your project
              </h2>

              {/* Decorative globe / orbit (simplified) */}
              {/* <div className="absolute left-0 top-[52%] -translate-y-1/2 w-[min(100%,320px)] aspect-square pointer-events-none opacity-90">
                <svg viewBox="0 0 200 200" className="w-full h-full text-[#e8d4bc]" fill="none" aria-hidden="true">
                  <circle cx="100" cy="100" r="72" stroke="currentColor" strokeWidth="1" strokeDasharray="4 6" />
                  <ellipse cx="100" cy="100" rx="100" ry="36" stroke="currentColor" strokeWidth="1" strokeDasharray="3 5" transform="rotate(-18 100 100)" />
                  <ellipse cx="100" cy="100" rx="52" ry="92" stroke="currentColor" strokeWidth="1" strokeDasharray="3 5" transform="rotate(12 100 100)" />
                  <circle cx="100" cy="100" r="4" fill="#2A62EB" />
                </svg>
              </div> */}

              {/* Floating tilted card */}
              {/* <div className="absolute right-0 bottom-4 sm:bottom-8 w-[200px] sm:w-[220px] rounded-2xl bg-white border border-[#e8ecf0] p-4 shadow-[0_12px_40px_rgba(0,0,0,0.06)] -rotate-[6deg] z-20">
                <div className="flex justify-end mb-6">
                  <span className="text-[11px] font-semibold text-[#0a0a0a]">◎</span>
                </div>
                <p className="text-right text-[13px] font-medium text-[#6b7280]">@Sky Mate Digital</p>
              </div> */}
              <Reveal>
                <div className="mt-8 rounded-[28px] border border-[#efe3cb] bg-[#d0ddff] p-4 sm:p-6 shadow-card ">
                  <div className="flex items-center gap-3 mb-4">
                    <span className="inline-flex h-[10px] w-[10px] rounded-full bg-[#2A62EB]" />
                    <span className="text-xs tracking-[0.2em] font-medium text-[#9c9c9c] uppercase">Book a Call</span>
                  </div>
                  <iframe src="https://api.leadconnectorhq.com/widget/booking/fYHNirZKyGmhYGyY1pdu" style={{ width: "100%", border: "none", overflow: "hidden" }} scrolling="no" id="fYHNirZKyGmhYGyY1pdu_1756804087259" />
                </div>
              </Reveal>
            </div>

            {/* Right: form */}
            <div>
              <form className="space-y-8" onSubmit={handleSubmit}>
                <input type="hidden" name="category" value={category} readOnly />

                <div>
                  <p className="text-[15px] font-medium text-[#0a0a0a] mb-4">Tell us about your project</p>
                  <div className="flex flex-wrap gap-2">
                    {categories.map((c) => (
                      <button
                        key={c}
                        type="button"
                        onClick={() => setCategory(c)}
                        className={`rounded-full px-4 py-2 text-sm font-medium transition-colors ${
                          category === c
                            ? "bg-[#dce6ff] text-[#0a0a0a] border border-[#2A62EB]/40"
                            : "bg-transparent text-[#0a0a0a] border border-[#d8d8d8] hover:border-[#b8b8b8]"
                        }`}
                      >
                        {c}
                      </button>
                    ))}
                  </div>
                </div>

                <div className="space-y-6">
                  <div>
                    <label htmlFor="git-name" className="block text-[13px] text-[#6b7280] mb-1">
                      Your Name
                    </label>
                    <input id="git-name" name="name" type="text" required placeholder="" className={underlineInput} />
                  </div>
                  <div>
                    <label htmlFor="git-phone" className="block text-[13px] text-[#6b7280] mb-1">
                      Phone Number
                    </label>
                    <input id="git-phone" name="phone" type="tel" className={underlineInput} />
                  </div>
                  <div>
                    <label htmlFor="git-email" className="block text-[13px] text-[#6b7280] mb-1">
                      Email
                    </label>
                    <div className="relative flex items-end border-b border-[#d0d0d0] focus-within:border-[#2A62EB] transition-colors">
                      <input
                        id="git-email"
                        name="email"
                        type="email"
                        required
                        className="flex-1 border-0 bg-transparent px-0 py-2.5 text-[15px] text-[#0a0a0a] focus:ring-0 focus:outline-none"
                      />
                      <Mail className="mb-2.5 h-4 w-4 shrink-0 text-[#22c55e]" aria-hidden />
                    </div>
                  </div>
                  <div>
                    <label htmlFor="git-message" className="block text-[13px] text-[#6b7280] mb-1">
                      Message
                    </label>
                    <textarea
                      id="git-message"
                      name="message"
                      rows={3}
                      required
                      className={`${underlineInput} resize-y min-h-[88px]`}
                    />
                  </div>
                </div>

                <button
                  type="submit"
                  disabled={isSubmitting}
                  className="inline-flex items-center gap-2 rounded-full bg-[#0a0a0a] px-8 py-3.5 text-[15px] font-medium text-white transition-opacity hover:opacity-90 disabled:opacity-60 disabled:cursor-not-allowed"
                >
                  {isSubmitting ? "Sending…" : "Get in touch"}
                  <span className="text-lg leading-none" aria-hidden>
                    ↗
                  </span>
                </button>

                {submitted && (
                  <p className="text-sm text-[#15803d]" role="status">
                    Thanks — we&apos;ll be in touch soon.
                  </p>
                )}
                {error && (
                  <p className="text-sm text-red-600" role="alert">
                    {error}
                  </p>
                )}
              </form>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
};

export default HomeGetInTouch;
