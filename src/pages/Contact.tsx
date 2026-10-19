import { submitWeb3Forms } from "@/lib/web3forms";
import { useEffect, useRef, useState } from "react";
import Reveal from "@/components/ui/Reveal";
import AnimatedButton from "@/components/ui/AnimatedButton";
import CTASection from "@/components/sections/CTASection";
import { Mail, Phone, MapPin, Clock, ChevronDown, ShieldCheck, BadgeCheck, MessageCircle } from "lucide-react";
import { motion, AnimatePresence } from "framer-motion";
import portfolioBg from "@/assets/contact.jpg";

const contactInfo = [
  { icon: Mail, label: "Email", value: "hello@skymatedigital.com" },
  { icon: Phone, label: "Phone", value: "+92 300 8628415" },
  { icon: MapPin, label: "Office", value: "Serving clients worldwide" },
  { icon: Clock, label: "Hours", value: "Mon - Fri, 9AM - 6PM (PKT)" },
];

const faqs = [
  { q: "What services does Sky mate digital offer?", a: "We specialize in innovative software solutions, web development, and digital strategy consulting. For a full list of our services, please visit our Services page." },
  { q: "How can I contact Sky mate digital?", a: "You can reach us through our Contact page, where you'll find our email address, phone number, and contact form." },
  { q: "What industries do you serve?", a: "We work with a diverse range of industries, including technology, healthcare, finance, and more. Our team is equipped to handle various sector-specific needs." },
  { q: "How do I request a quote for a project?", a: "To request a quote, please fill out the form on our Quote Request page, providing as much detail as possible about your project." },
  { q: "What is your approach to project management?", a: "We follow an agile methodology to ensure flexibility and responsiveness throughout the project lifecycle. Our approach includes regular updates and client feedback sessions." },
];

const underlineInput =
  "w-full border-0 border-b border-[#d7d7d7] bg-transparent px-0 py-2.5 text-[15px] text-[#0a0a0a] placeholder:text-[#b0b0b0] focus:border-[#2A62EB] focus:ring-0 focus:outline-none transition-colors";

const Contact = () => {
  const [openFaq, setOpenFaq] = useState<number | null>(null);
  const [submitted, setSubmitted] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const calendlyRef = useRef<HTMLDivElement | null>(null);

  useEffect(() => {
    const initCalendly = () => {
      const calendly = (window as any).Calendly;
      if (calendly?.initInlineWidget && calendlyRef.current) {
        calendlyRef.current.innerHTML = "";
        calendly.initInlineWidget({
          url: "https://calendly.com/contact-skymatedigital",
          parentElement: calendlyRef.current,
        });
      }
    };

    const existingScript = document.querySelector('script[src="https://assets.calendly.com/assets/external/widget.js"]') as HTMLScriptElement | null;
    if (!existingScript) {
      const script = document.createElement("script");
      script.src = "https://assets.calendly.com/assets/external/widget.js";
      script.async = true;
      script.onload = () => initCalendly();
      document.body.appendChild(script);
    } else {
      // Re-init on SPA route changes where script already exists.
      window.setTimeout(initCalendly, 0);
    }

    // Trigger again after animation/layout settles.
    const timer = window.setTimeout(initCalendly, 350);
    return () => window.clearTimeout(timer);
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
      await submitWeb3Forms({ subject: `New Contact from ${name} — Sky Mate Digital`, name, email, phone: phone || "Not provided", message, replyto: email });

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
    <>
      {/* Hero */}
      <section className="relative w-full h-[66vh] sm:h-[72vh] min-h-[430px] overflow-hidden">
      <div className="absolute inset-0">
          <img
            src={portfolioBg}
            alt=""
            className="absolute inset-0 w-full h-full object-cover"
          />
          <div className="absolute inset-0 bg-gradient-to-r from-black/75 via-black/35 to-black/50" />
        </div>

        <div className="relative z-10 h-full w-full px-4 sm:px-6 md:px-12 lg:px-20 pb-10 sm:pb-14 flex items-end">
          <div className="max-w-[620px]">
            <h1 className="text-white font-display font-normal tracking-tight text-[42px] leading-[1.05] sm:text-[56px] sm:leading-[1.04] lg:text-[66px]">
              Contact us
            </h1>
            <p className="mt-4 text-white/90 text-[15px] sm:text-[20px] leading-[1.45] max-w-[560px]">
              Premium support with personalized solutions—tell us what you&apos;re building, and we&apos;ll respond with
              clear next steps. Based in Pakistan, trusted by clients worldwide.
            </p>
          </div>
        </div>
      </section>

      {/* Contact Form + Info */}
      <section className="relative py-20 lg:py-28 bg-[#2a62eb4a] overflow-hidden">
        <div
          className="pointer-events-none absolute inset-0 opacity-60"
          aria-hidden
          style={{
            backgroundImage:
              "radial-gradient(circle at 12% 22%, rgba(235,159,28,0.22) 0%, transparent 55%), radial-gradient(circle at 86% 8%, rgba(235,159,28,0.16) 0%, transparent 45%), radial-gradient(circle at 52% 92%, rgba(0,0,0,0.04) 0%, transparent 55%)",
          }}
        />
        <div className="container mx-auto px-6 lg:px-8">
          <div className="grid lg:grid-cols-5 gap-10 lg:gap-16 relative z-[1]">
            <div className="lg:col-span-3">
              <div className="rounded-[28px] border border-[#cbd3ef] bg-white/80 backdrop-blur shadow-card px-6 sm:px-8 py-8 sm:py-10">
                <Reveal>
                  <div className="flex items-center gap-3 mb-4">
                    <span className="inline-flex h-[10px] w-[10px] rounded-full bg-[#2A62EB]" />
                    <span className="text-xs tracking-[0.2em] font-medium text-[#9c9c9c] uppercase">
                      Contact Form
                    </span>
                  </div>
                  <h2 className="text-[34px] sm:text-[44px] leading-[1.08] font-display font-bold text-[#0a0a0a]">
                    Send us a <span className="text-[#2A62EB]">message</span>
                  </h2>
                  <p className="mt-3 text-[15px] text-[#666] leading-relaxed max-w-[520px]">
                    Share a few details and we&apos;ll route your request to the right team.
                  </p>
                </Reveal>

                <form className="mt-8 space-y-7" onSubmit={handleSubmit}>
                  <Reveal delay={0.05}>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                      <div>
                        <label className="block text-sm font-medium text-[#6b7280] mb-1">Full Name</label>
                        <input
                          id="contact-name"
                          name="name"
                          type="text"
                          placeholder="John Smith"
                          required
                          className={underlineInput}
                        />
                      </div>
                      <div>
                        <label className="block text-sm font-medium text-[#6b7280] mb-1">Email Address</label>
                        <input
                          id="contact-email"
                          name="email"
                          type="email"
                          placeholder="john@company.com"
                          required
                          className={underlineInput}
                        />
                      </div>
                    </div>
                  </Reveal>

                  <Reveal delay={0.1}>
                    <div>
                      <label className="block text-sm font-medium text-[#6b7280] mb-1">Phone Number</label>
                      <input id="contact-phone" name="phone" type="tel" placeholder="+92 300 0000000" className={underlineInput} />
                    </div>
                  </Reveal>

                  <Reveal delay={0.15}>
                    <div>
                      <label className="block text-sm font-medium text-[#6b7280] mb-1">Project Details</label>
                      <textarea
                        id="contact-message"
                        name="message"
                        rows={5}
                        required
                        placeholder="Tell us about your project, goals, and timeline..."
                        className={`${underlineInput} resize-none`}
                      />
                    </div>
                  </Reveal>

                  <Reveal delay={0.2}>
                    <AnimatedButton type="submit" size="lg" showArrow className="w-full">
                      {isSubmitting ? "Sending..." : "Send Message"}
                    </AnimatedButton>
                  </Reveal>
                  {submitted && (
                    <p className="text-sm text-[#15803d]" role="status">
                      Thanks - we&apos;ll be in touch soon.
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

            <div className="lg:col-span-2">
              <Reveal direction="right">
                <div className="bg-white/70 rounded-[28px] p-8 border border-[#d8dbff] shadow-card mb-8">
                  <div className="flex items-start justify-between gap-4 mb-6">
                    <div>
                      <p className="text-xs tracking-[0.2em] font-medium text-[#9c9c9c] uppercase flex items-center gap-3">
                        <span className="inline-flex h-[10px] w-[10px] rounded-full bg-[#2A62EB]" />
                        Authority
                      </p>
                      <h3 className="font-display font-semibold text-[#0a0a0a] text-[26px] mt-2">
                        You&apos;re in good hands
                      </h3>
                      <p className="mt-2 text-sm text-[#666] leading-relaxed">
                        We&apos;ll keep communication clear and timelines realistic from day one.
                      </p>
                    </div>
                    <div className="hidden sm:flex h-12 w-12 rounded-2xl bg-[#d8dbff] items-center justify-center border border-[#d8dbff]">
                      <MessageCircle className="w-5 h-5 text-[#2A62EB]" />
                    </div>
                  </div>

                  <div className="space-y-5">
                    {contactInfo.map((info) => (
                      <div key={info.label} className="flex items-start gap-4">
                        <div className="w-11 h-11 rounded-2xl bg-[#d8dbff] flex items-center justify-center flex-shrink-0 border border-[#d8dbff]">
                          <info.icon className="w-5 h-5 text-[#2A62EB]" />
                        </div>
                        <div>
                          <p className="text-xs text-[#9c9c9c]">{info.label}</p>
                          <p className="text-sm font-medium text-[#0a0a0a]">{info.value}</p>
                        </div>
                      </div>
                    ))}
                  </div>

                  <div className="mt-7 pt-6 border-t border-[#efe3cb] grid grid-cols-1 gap-4">
                    <div className="flex items-start gap-3">
                      <div className="mt-0.5 w-9 h-9 rounded-2xl bg-[#d8dbff] border border-[#d8dbff] flex items-center justify-center">
                        <ShieldCheck className="w-4 h-4 text-[#2A62EB]" />
                      </div>
                      <div>
                        <p className="text-sm font-medium text-[#0a0a0a]">NDA-friendly</p>
                        <p className="text-sm text-[#666] leading-relaxed">We can sign NDAs before sharing sensitive details.</p>
                      </div>
                    </div>

                    <div className="flex items-start gap-3">
                      <div className="mt-0.5 w-9 h-9 rounded-2xl bg-[#d8dbff] border border-[#d8dbff] flex items-center justify-center">
                        <BadgeCheck className="w-4 h-4 text-[#2A62EB]" />
                      </div>
                      <div>
                        <p className="text-sm font-medium text-[#0a0a0a]">Clear milestones</p>
                        <p className="text-sm text-[#666] leading-relaxed">Weekly updates, transparent scope, and measurable progress.</p>
                      </div>
                    </div>

                    <div className="flex items-start gap-3">
                      <div className="mt-0.5 w-9 h-9 rounded-2xl bg-[#d8dbff] border border-[#d8dbff] flex items-center justify-center">
                        <Clock className="w-4 h-4 text-[#2A62EB]" />
                      </div>
                      <div>
                        <p className="text-sm font-medium text-[#0a0a0a]">Response in 24h</p>
                        <p className="text-sm text-[#666] leading-relaxed">On business days, we typically reply within 24 hours.</p>
                      </div>
                    </div>
                  </div>
                </div>
              </Reveal>

              <Reveal direction="right" delay={0.1}>
                <div className="bg-[#d8dbff] rounded-[24px] p-6 border border-[#d8dbff]">
                  <div className="flex items-start gap-3">
                    <div className="w-10 h-10 rounded-2xl bg-white/70 border border-[#d8dbff] flex items-center justify-center">
                      <Clock className="w-5 h-5 text-[#2A62EB]" />
                    </div>
                    <div>
                      <p className="text-sm text-[#0a0a0a] font-medium mb-1">Kickoff starts soon</p>
                      <p className="text-xs text-[#666]">
                        We&apos;ll confirm fit, scope, and next steps right after your message.
                      </p>
                    </div>
                  </div>
                </div>
              </Reveal>
            </div>
          </div>
        </div>
      </section>

    {/* Calendly */}
      <section className="py-20 lg:py-24 bg-white">
        <div className="container mx-auto px-6 lg:px-8">
          <Reveal>
            <div className="rounded-[28px] border border-[#d8dbff] bg-[#d8dbff] p-6 sm:p-8 lg:p-10 shadow-card">
              <div className="flex items-center gap-3 mb-4">
                <span className="inline-flex h-[10px] w-[10px] rounded-full bg-[#2A62EB]" />
                <span className="text-xs tracking-[0.2em] font-medium text-[#9c9c9c] uppercase">Book a Call</span>
              </div>
              <iframe
                src="https://api.leadconnectorhq.com/widget/booking/fYHNirZKyGmhYGyY1pdu"
                style={{ width: "100%", border: "none", overflow: "hidden" }}
                scrolling="no"
                id="fYHNirZKyGmhYGyY1pdu_1756804087259"
                className="min-h-[700px]"
              />
            </div>
          </Reveal>
        </div>
      </section>

      {/* FAQ */}
      <section className="py-20 lg:py-28 bg-secondary">
        <div className="container mx-auto px-6 lg:px-8 max-w-3xl">
          <Reveal>
            <h2 className="text-3xl font-display font-bold text-foreground mb-8 text-center">Frequently Asked Questions</h2>
          </Reveal>
          <div className="space-y-4">
            {faqs.map((faq, i) => (
              <Reveal key={i} delay={i * 0.05}>
                <div className="bg-card rounded-xl border border-border overflow-hidden">
                  <button
                    onClick={() => setOpenFaq(openFaq === i ? null : i)}
                    className="w-full flex items-center justify-between p-5 text-left"
                  >
                    <span className="font-medium text-foreground text-sm">{faq.q}</span>
                    <motion.span
                      animate={{ rotate: openFaq === i ? 180 : 0 }}
                      transition={{ duration: 0.2 }}
                    >
                      <ChevronDown className="w-5 h-5 text-muted-foreground" />
                    </motion.span>
                  </button>
                  <AnimatePresence>
                    {openFaq === i && (
                      <motion.div
                        initial={{ height: 0, opacity: 0 }}
                        animate={{ height: "auto", opacity: 1 }}
                        exit={{ height: 0, opacity: 0 }}
                        transition={{ duration: 0.3 }}
                        className="overflow-hidden"
                      >
                        <p className="px-5 pb-5 text-sm text-muted-foreground leading-relaxed">{faq.a}</p>
                      </motion.div>
                    )}
                  </AnimatePresence>
                </div>
              </Reveal>
            ))}
          </div>
        </div>
      </section>

      <CTASection />
    </>
  );
};

export default Contact;
