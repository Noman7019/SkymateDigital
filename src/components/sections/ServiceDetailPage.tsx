import { ArrowRight, CheckCircle2, Sparkles, Layers3, Rocket } from "lucide-react";
import { useLocation } from "react-router-dom";
import { Link } from "react-router-dom";
import CTASection from "@/components/sections/CTASection";
import { ServiceDetail, ServiceSlug, howItWorks, serviceOrder } from "@/data/serviceDetails";

type ServiceDetailPageProps = {
  service: ServiceDetail;
};

const ServiceDetailPage = ({ service }: ServiceDetailPageProps) => {
  const isActive = (slug: ServiceSlug) => slug === service.slug;

  return (
    <>
      <section className="relative w-full h-[66vh] sm:h-[72vh] min-h-[430px] overflow-hidden">
        <div className="absolute inset-0">
          <img src={service.heroImage} alt={`${service.title} service`} className="absolute inset-0 w-full h-full object-cover" />
          <div className="absolute inset-0 bg-gradient-to-r from-black/75 via-black/35 to-black/50" />
        </div>

        <div className="relative z-10 h-full w-full px-4 sm:px-6 md:px-12 lg:px-20 pb-10 sm:pb-14 flex items-end">
          <div className="max-w-[820px]">
            <h1 className="text-white font-display font-normal tracking-tight text-[42px] leading-[1.05] sm:text-[56px] sm:leading-[1.04] lg:text-[66px]">
              {service.title}
            </h1>
            <p className="mt-4 text-white/90 text-[15px] sm:text-[20px] leading-[1.45]">{service.heroSubtitle}</p>
            <p className="mt-4 text-white/85 text-sm sm:text-base leading-[1.7]">{service.heroIntro}</p>
          </div>
        </div>
      </section>

      <section className="py-16 sm:py-20 lg:py-24 bg-[#fffaf0]">
        <div className="w-full px-4 sm:px-6 md:px-12 lg:px-20">
          <div className="mx-auto max-w-[1320px] grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_320px] gap-8 lg:gap-10 items-start">
            <div className="space-y-8">
              <div className="rounded-[28px] bg-white p-6 sm:p-10 lg:p-12 shadow-[0_24px_80px_rgba(15,23,42,0.06)] border border-[#f2e8dc]">
                <p className="text-xs tracking-[0.2em] font-medium text-[#9c9c9c] flex items-center gap-3 mb-4">
                  <span className="inline-flex h-[10px] w-[10px] rounded-full bg-[#2A62EB]" />
                  DESCRIPTIONS
                </p>

                <h2 className="text-[34px] sm:text-[44px] leading-[1.08] font-display font-bold text-[#0a0a0a] mb-6">
                  Building measurable outcomes
                </h2>

                <div className="space-y-5">
                  {service.descriptions.map((paragraph) => (
                    <p key={paragraph} className="text-[16px] leading-[1.8] text-[#555]">
                      {paragraph}
                    </p>
                  ))}
                </div>

                <div className="mt-8 grid grid-cols-1 sm:grid-cols-2 gap-3">
                  {service.comprehensiveItems.map((item) => (
                    <div
                      key={item}
                      className="flex items-center gap-3 rounded-xl border border-[#efe3cb] bg-[#fffdfa] px-4 py-3"
                    >
                      <CheckCircle2 className="h-4 w-4 text-[#2A62EB] shrink-0" />
                      <p className="text-sm text-[#222]">{item}</p>
                    </div>
                  ))}
                </div>
                {service.slug === "seo-content-writing" && (
  <div className="mt-10">
    <Link
      to="/content-creation"
      className="group inline-flex items-center gap-3 rounded-full bg-[#2A62EB] px-7 py-4 text-white font-medium text-sm transition-all duration-300 hover:bg-[#1746b8] hover:scale-[1.02]"
    >
      View Portfolio

      <span className="transition-transform duration-300 group-hover:translate-x-1">
        →
      </span>
    </Link>
  </div>
)}

              
              
              </div>

              <div className="rounded-[28px] bg-white p-6 sm:p-10 lg:p-12 border border-[#e9e9e9]">
                <p className="text-xs tracking-[0.2em] font-medium text-[#9c9c9c] flex items-center gap-3 mb-4">
                  <span className="inline-flex h-[10px] w-[10px] rounded-full bg-[#2A62EB]" />
                  FEATURE
                </p>
                <h2 className="text-[34px] sm:text-[44px] leading-[1.08] font-display font-bold text-[#0a0a0a] mb-8">
                  What you get
                </h2>
                <div className="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4">
                  {service.features.map((feature) => (
                    <div key={feature} className="rounded-2xl border border-[#e9e9e9] p-5 bg-[#fffdfa]">
                      <p className="text-sm font-medium text-[#0a0a0a]">{feature}</p>
                    </div>
                  ))}
                </div>
              </div>

              <div className="rounded-[28px] bg-white p-6 sm:p-10 lg:p-12 border border-[#efe3cb]">
                <p className="text-xs tracking-[0.2em] font-medium text-[#9c9c9c] flex items-center gap-3 mb-4">
                  <span className="inline-flex h-[10px] w-[10px] rounded-full bg-[#2A62EB]" />
                  CREATIVE HIGHLIGHTS
                </p>
                <h2 className="text-[30px] sm:text-[38px] leading-[1.12] font-display font-bold text-[#0a0a0a] mb-6">
                  Crafted visuals aligned with {service.title}
                </h2>
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                  {service.features.slice(0, 3).map((item, index) => {
                    const icon = index === 0 ? Sparkles : index === 1 ? Layers3 : Rocket;
                    const Icon = icon;
                    return (
                      <div
                        key={item}
                        className="rounded-2xl border border-[#efe3cb] p-5 bg-[linear-gradient(145deg,#fffdf8_0%,#fff3dc_100%)]"
                      >
                        <Icon className="h-5 w-5 text-[#2A62EB]" />
                        <h3 className="mt-3 text-base font-semibold text-[#0a0a0a]">{item}</h3>
                        <p className="mt-2 text-sm text-[#666] leading-relaxed">
                          Design and delivery tailored to this capability for clearer outcomes and stronger conversion.
                        </p>
                      </div>
                    );
                  })}
                </div>
              </div>

              {service.toolHighlights && service.toolHighlights.length > 0 && (
                <div className="rounded-[28px] bg-white p-6 sm:p-10 lg:p-12 border border-[#efe3cb]">
                  <h2 className="text-[30px] sm:text-[38px] leading-[1.12] font-display font-bold text-[#0a0a0a] mb-6">
                    Integration Tools
                  </h2>
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {service.toolHighlights.map((tool) => (
                      <div key={tool.name} className="rounded-2xl border border-[#efe3cb] bg-[#fffdfa] p-5">
                        <div className="flex items-center gap-3">
                          <img src={tool.logoUrl} alt={tool.name} className="h-9 w-9 rounded-md object-contain bg-white border border-[#eee]" />
                          <h3 className="text-lg font-semibold text-[#0a0a0a]">{tool.name}</h3>
                        </div>
                        <p className="mt-3 text-sm leading-relaxed text-[#666]">{tool.description}</p>
                      </div>
                    ))}
                  </div>
                </div>
              )}

              {service.extraSections?.map((section) => (
                <div key={section.title} className="rounded-[28px] bg-white p-6 sm:p-10 lg:p-12 border border-[#efe3cb]">
                  <h2 className="text-[30px] sm:text-[38px] leading-[1.12] font-display font-bold text-[#0a0a0a] mb-5">{section.title}</h2>
                  {section.intro && <p className="text-[#666] mb-6">{section.intro}</p>}
                  {section.paragraphs?.map((paragraph) => (
                    <p key={paragraph} className="text-[16px] leading-[1.8] text-[#555] mb-4">
                      {paragraph}
                    </p>
                  ))}
                  {section.cards && (
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                      {section.cards.map((card) => (
                        <div key={card.title} className="rounded-2xl border border-[#efe3cb] bg-[#fffdfa] p-5">
                          <h3 className="text-base font-semibold text-[#0a0a0a]">{card.title}</h3>
                          <p className="mt-2 text-sm leading-relaxed text-[#666]">{card.description}</p>
                        </div>
                      ))}
                    </div>
                  )}
                  {section.points && (
                    <div className="space-y-3">
                      {section.points.map((point) => (
                        <p key={point} className="text-[16px] leading-[1.8] text-[#555]">
                          {point}
                        </p>
                      ))}
                    </div>
                  )}
                </div>
              ))}

              {service.faqs && service.faqs.length > 0 && (
                <div className="rounded-[28px] bg-white p-6 sm:p-10 lg:p-12 border border-[#efe3cb]">
                  <h2 className="text-[30px] sm:text-[38px] leading-[1.12] font-display font-bold text-[#0a0a0a] mb-6">
                    Questions?
                  </h2>
                  <div className="space-y-4">
                    {service.faqs.map((faq) => (
                      <div key={faq.question} className="rounded-2xl border border-[#efe3cb] bg-[#fffdfa] p-5">
                        <h3 className="text-lg font-semibold text-[#0a0a0a]">{faq.question}</h3>
                        <p className="mt-2 text-sm leading-relaxed text-[#666]">{faq.answer}</p>
                      </div>
                    ))}
                  </div>
                </div>
              )}

              <div className="rounded-[28px] bg-white p-6 sm:p-10 lg:p-12 border border-[#efe3cb]">
                <p className="text-xs tracking-[0.2em] font-medium text-[#9c9c9c] flex items-center gap-3 mb-4">
                  <span className="inline-flex h-[10px] w-[10px] rounded-full bg-[#2A62EB]" />
                  {howItWorks.badge}
                </p>
                <h2 className="text-[34px] sm:text-[44px] leading-[1.08] font-display font-bold text-[#0a0a0a] mb-2">
                  {howItWorks.title}
                </h2>
                <p className="text-[#666] mb-8">{howItWorks.subtitle}</p>

                <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
                  {howItWorks.steps.map((step, index) => (
                    <div key={step.title} className="rounded-2xl border border-[#efe3cb] bg-white p-6">
                      <div className="inline-flex items-center justify-center h-9 w-9 rounded-full bg-[#fff0d8] text-[#2A62EB] text-sm font-semibold">
                        {index + 1}
                      </div>
                      <h3 className="mt-3 text-xl font-semibold text-[#0a0a0a]">{step.title}</h3>
                      <p className="mt-2 text-sm text-[#666] leading-relaxed">{step.description}</p>
                      <button type="button" className="mt-4 text-sm font-medium text-[#2A62EB] hover:opacity-80 transition-opacity">
                        {step.cta}
                      </button>
                    </div>
                  ))}
                </div>
              </div>

              <div className="rounded-[28px] border border-[#efe3cb] bg-[#fff8ea] p-6 sm:p-10">
                <h2 className="text-[30px] sm:text-[38px] leading-[1.1] font-display font-bold text-[#0a0a0a]">
                  {service.advantageTitle}
                </h2>
                <p className="mt-4 text-[16px] leading-[1.8] text-[#555] max-w-[850px]">{service.advantageDescription}</p>
                <div className="mt-6">
                  <Link
                    to="/contact-us"
                    className="inline-flex items-center gap-2 px-5 py-2 rounded-full bg-[#2A62EB] text-white text-sm font-medium hover:opacity-90 transition-opacity"
                  >
                    Start your project
                  </Link>
                </div>
              </div>
            </div>

            <aside className="hidden lg:block sticky top-28">
              <div className="rounded-[18px] border border-[#dfddd5] bg-[#f5f3ed] p-6">
                <h3 className="text-[38px] leading-[1.1] font-display font-semibold text-[#121212] mb-5">All Services</h3>
                <div className="space-y-3">
                  {serviceOrder.map((serviceLink) => (
                    <Link
                      key={serviceLink.slug}
                      to={`/services/${serviceLink.slug}`}
                      className={`group flex items-center justify-between rounded-full px-5 py-4 text-[20px] leading-none transition-colors ${isActive(serviceLink.slug) ? "bg-[#2A62EB] text-white" : "bg-[#ece9e2] text-[#1e1e1e] hover:bg-[#e3dfd6]"
                        }`}
                    >
                      <span>{serviceLink.label}</span>
                      <ArrowRight className={`h-5 w-5 ${isActive(serviceLink.slug) ? "text-white" : "text-[#1e1e1e]"}`} />
                    </Link>
                  ))}
                </div>
              </div>
            </aside>
          </div>
        </div>
      </section>

      <CTASection />
    </>
  );
};

export default ServiceDetailPage;
