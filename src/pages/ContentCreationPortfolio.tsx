import { Play } from "lucide-react";
import { Link, useLocation } from "react-router-dom";

const BASE = "https://skymatedigital.com/wp-content/uploads/2025/07/";

// ── Brand Content grid images (container 142bc8bb, 8 flip-box background_a_image) ──
const brandContentImages = [
  `${BASE}5.jpg`,   // card 1 — no border
  `${BASE}4.jpg`,   // card 2 — border #3747A2
  `${BASE}6.jpg`,   // card 3 — border #3747A2
  `${BASE}9.jpg`,   // card 4 — no border
  `${BASE}10.jpg`,  // card 5 — no border
  `${BASE}11.jpg`,  // card 6 — border #3747A2
  `${BASE}12.jpg`,  // card 7 — border #3747A2
  `${BASE}13.jpg`,  // card 8 — no border
];

// ── CGI Content videos (container 574bc7b6, external_url per widget) ──
const cgiVideos = [
  `${BASE}1-1.mp4`,
  `${BASE}2.mp4`,
  `${BASE}3.mp4`,
  `${BASE}4.mp4`,
];

// ── Ad Creative videos (container 1a2f1677, hosted_url per widget) ──
const adCreativeVideos = [
  `${BASE}2-1.mp4`,    // no border
  `${BASE}3-1.mp4`,    // border #3747A2
  `${BASE}1-1-1.mp4`,  // no border
  `${BASE}4-1.mp4`,    // no border
];

// ── Intellectual Content images (container 64fb4a94, 4 flip-box background_a_image) ──
const intellectualImages = [
  `${BASE}1-scaled.png`,
  `${BASE}2.png`,
  `${BASE}3-scaled.png`,
  `${BASE}4-scaled.png`,
];

const ContentCreation = () => {
  return (
    <>
      {/* ───────── HERO ───────── */}
      <section className="pt-[100px] md:pt-[120px] px-4 sm:px-6 md:px-10 lg:px-16 xl:px-20 bg-black overflow-hidden">
        <div className="max-w-[1400px] mx-auto">
          <div className="flex flex-col items-center text-center">
            <span className="text-sm tracking-[0.25em] uppercase text-[#8d8d8d] mb-5">
              Portfolio
            </span>

            <h1 className="text-white font-black leading-[1.05] tracking-[-2px] text-[42px] sm:text-[56px] lg:text-[72px]">
              Content Creation
            </h1>
          </div>

          {/* Slider — 3 carousel images (container 317e712b, image-carousel widget) */}
         <div className="mt-16 overflow-hidden">
  <div
    className="flex gap-5 w-max animate-[scroll_18s_linear_infinite]"
  >
    {[`${BASE}1.jpg`, `${BASE}2.jpg`, `${BASE}3.jpg`].map((img, i) => (
      <div
        key={i}
        className="w-[280px] sm:w-[350px] lg:w-[420px] rounded-[26px] overflow-hidden shrink-0 border border-white/10"
      >
        <img
          src={img}
          alt="content"
          className="w-full h-full object-cover"
        />
      </div>
    ))}

    {/* Duplicate */}
    {[`${BASE}1.jpg`, `${BASE}2.jpg`, `${BASE}3.jpg`].map((img, i) => (
      <div
        key={`duplicate-${i}`}
        className="w-[280px] sm:w-[350px] lg:w-[420px] rounded-[26px] overflow-hidden shrink-0 border border-white/10"
      >
        <img
          src={img}
          alt="content"
          className="w-full h-full object-cover"
        />
      </div>
    ))}
  </div>
</div>
        </div>
      </section>

      {/* ───────── BRAND CONTENT ───────── */}
      <section className="bg-black pt-20 sm:pt-28">
        <div className="w-full px-4 sm:px-6 md:px-10 lg:px-16 xl:px-20">
          <div className="flex items-center gap-5 mb-10">
            <h2 className="text-white text-[28px] sm:text-[36px] font-bold whitespace-nowrap">
              Brand Content
            </h2>

            <div className="h-[2px] w-full bg-gradient-to-r from-[#4f46e5] via-[#7c3aed] to-transparent" />
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-2 gap-5">
            {brandContentImages.map((img, i) => (
              <div
                key={i}
                className={`group relative overflow-hidden rounded-[22px] min-h-[300px] sm:min-h-[420px] ${
                  i === 1 || i === 2 || i === 5 || i === 6
                    ? "border-4 border-[#3747A2]"
                    : "border border-white/10"
                }`}
              >
                <img
                  src={img}
                  alt="Brand Content"
                  className="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                />

                <div className="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition duration-500" />
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* ───────── CGI CONTENT ───────── */}
      <section className="bg-black pt-24">
        <div className="w-full px-4 sm:px-6 md:px-10 lg:px-16 xl:px-20">
          <div className="flex items-center gap-5 mb-10">
            <h2 className="text-white text-[28px] sm:text-[36px] font-bold whitespace-nowrap">
              CGI Content
            </h2>

            <div className="h-[2px] w-full bg-gradient-to-r from-[#4f46e5] via-[#7c3aed] to-transparent" />
          </div>

          <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
            {cgiVideos.map((video, i) => (
              <div
                key={i}
                className="relative overflow-hidden rounded-[22px] border border-white/10 bg-[#111]"
              >
                <video
                  autoPlay
                  muted
                  loop
                  playsInline
                  className="w-full h-full object-cover aspect-[9/16]"
                >
                  <source src={video} type="video/mp4" />
                </video>

                <div className="absolute top-4 right-4 w-10 h-10 rounded-full bg-white/15 backdrop-blur-md flex items-center justify-center border border-white/10">
                  <Play className="w-4 h-4 text-white fill-white" />
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* ───────── AD CREATIVE ───────── */}
      <section className="bg-black pt-24">
        <div className="w-full px-4 sm:px-6 md:px-10 lg:px-16 xl:px-20">
          <div className="flex items-center gap-5 mb-10">
            <h2 className="text-white text-[28px] sm:text-[36px] font-bold whitespace-nowrap">
              Ad Creative
            </h2>

            <div className="h-[2px] w-full bg-gradient-to-r from-[#4f46e5] via-[#7c3aed] to-transparent" />
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
  {adCreativeVideos.map((video, i) => (
    <div
      key={i}
      className={`relative overflow-hidden rounded-[18px] border bg-[#111]
      h-[240px] sm:h-[320px] lg:h-[480px]
      ${
        i === 1
          ? "border-[#3747A2]"
          : "border-white/10"
      }`}
    >
      <video
        autoPlay
        muted
        loop
        playsInline
        controls
        className="w-full h-full object-cover"
      >
        <source src={video} type="video/mp4" />
      </video>
    </div>
  ))}
</div>
        </div>
      </section>

      {/* ───────── INTELLECTUAL CONTENT ───────── */}
      <section className="bg-black pt-24 pb-28">
        <div className="w-full px-4 sm:px-6 md:px-10 lg:px-16 xl:px-20">
          <div className="flex items-center gap-5 mb-10">
            <h2 className="text-white text-[28px] sm:text-[36px] font-bold whitespace-nowrap">
              Intellectual Content
            </h2>

            <div className="h-[2px] w-full bg-gradient-to-r from-[#4f46e5] via-[#7c3aed] to-transparent" />
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            {intellectualImages.map((img, i) => (
              <div
                key={i}
                className="group overflow-hidden rounded-[22px] border border-white/10 bg-[#111]"
              >
                <img
                  src={img}
                  alt="Intellectual Content"
                  className="w-full h-full object-cover transition duration-700 group-hover:scale-105"
                />
              </div>
            ))}
          </div>
        </div>
      </section>
    </>
  );
};

export default ContentCreation;
