# Performance: tame the two WebGL animation loops (exec'd by patch-live-bundle.py; uses rep/s).
# 1) Home hero "light streaks" shader (OU): it rendered a full-screen fragment shader at full devicePixelRatio
#    on every animation frame forever. Now: DPR capped at 1 (0.5 on phones; the glow is soft so this is visually equivalent), ~30fps, paused when offscreen (IntersectionObserver)
#    or tab hidden, a single static frame under prefers-reduced-motion, and the animation stops after an 8s intro
#    (pointer movement over the hero resumes it briefly).
rep('''        const _ = new P7({
          dpr: e ?? ((typeof window < "u" && window.devicePixelRatio) || 1),
          alpha: !0,
          antialias: !0,
        });''', '''        const _ = new P7({
          dpr: e ?? (typeof window < "u" && window.innerWidth < 768 && !(window.matchMedia && matchMedia("(prefers-reduced-motion: reduce)").matches) ? 0.5 : Math.min(window.devicePixelRatio || 1, 1)),
          alpha: !0,
          antialias: !1,
        });''')
rep('''        g && Y.addEventListener("pointermove", Ee);
        const Pe = (X) => {
          if (
            ((k.current = requestAnimationFrame(Pe)),
            (O.iTime.value = X * 0.001),
            v > 0)
          ) {''', '''        const smdRM = typeof matchMedia == "function" && matchMedia("(prefers-reduced-motion: reduce)").matches;
        let smdVis = !0, smdRun = !1, smdLast = -1e9, smdUntil = 0;
        const smdGo = (ms) => {
          if (smdRM) return;
          smdUntil = Math.max(smdUntil, performance.now() + ms);
          !smdRun && smdVis && !document.hidden && ((smdRun = !0), (k.current = requestAnimationFrame(Pe)));
        };
        const smdIO = typeof IntersectionObserver == "function" ? new IntersectionObserver((es) => {
          smdVis = es[es.length - 1].isIntersecting; smdVis && smdGo(0);
        }) : null;
        smdIO && smdIO.observe(L);
        const smdVC = () => { document.hidden || smdGo(0); };
        document.addEventListener("visibilitychange", smdVC);
        const smdPM = () => smdGo(3e3);
        g && Y.addEventListener("pointermove", Ee);
        g && Y.addEventListener("pointermove", smdPM, { passive: !0 });
        const Pe = (X) => {
          if (((k.current = 0), !smdVis || document.hidden)) { smdRun = !1; return; }
          if (X < smdUntil) k.current = requestAnimationFrame(Pe); else smdRun = !1;
          if (X - smdLast < 32 && smdRun) return;
          smdLast = X;
          if (
            ((O.iTime.value = X * 0.001),
            v > 0)
          ) {''')
rep('''        return (
          (k.current = requestAnimationFrame(Pe)),
          () => {
            (k.current && cancelAnimationFrame(k.current),
              g && Y.removeEventListener("pointermove", Ee),''', '''        Pe(performance.now()); (smdRun = !1), k.current && cancelAnimationFrame(k.current);
        smdGo(8e3);
        return (
          () => {
            (k.current && cancelAnimationFrame(k.current),
              smdIO && smdIO.disconnect(),
              document.removeEventListener("visibilitychange", smdVC),
              g && Y.removeEventListener("pointermove", smdPM),
              g && Y.removeEventListener("pointermove", Ee),''')

# 2) Global fluid "splash cursor" (SG): once the pointer moved it simulated forever (and could start two loops).
#    Now a single loop that idles 4s after the last pointer/touch input, stops when the tab is hidden, never runs
#    under prefers-reduced-motion, and the WebGL setup is deferred until the first pointer interaction.
rep('''      function $n() {
        const T = jr();
        (Ko() && zn(), Mr(T), Ll(), Bl(T), Ki(null), requestAnimationFrame($n));
      }''', '''      let smdRaf = 0, smdIn = 0;
      const smdRM = typeof matchMedia == "function" && matchMedia("(prefers-reduced-motion: reduce)").matches;
      function $n() {
        if (smdRM) return;
        smdIn = Date.now();
        smdRaf || document.hidden || ((jr(), (smdRaf = requestAnimationFrame(smdStep))));
      }
      function smdStep() {
        smdRaf = 0;
        const T = jr();
        (Ko() && zn(), Mr(T), Ll(), Bl(T), Ki(null));
        !document.hidden && Date.now() - smdIn < 4e3 && (smdRaf = requestAnimationFrame(smdStep));
      }
      const smdWake = () => { smdIn && $n(); };
      window.addEventListener("pointermove", smdWake, { passive: !0 });
      window.addEventListener("touchstart", smdWake, { passive: !0 });
      window.addEventListener("touchmove", smdWake, { passive: !0 });''')
rep('''  Sse = new pO(),''', '''  smdSG = (t) => {
    const [e, n] = S.useState(!1);
    return (
      S.useEffect(() => {
        if (typeof matchMedia == "function" && matchMedia("(prefers-reduced-motion: reduce)").matches) return;
        const r = () => { (n(!0), s()); },
          s = () => ["pointermove", "pointerdown", "touchstart"].forEach((i) => window.removeEventListener(i, r));
        return (["pointermove", "pointerdown", "touchstart"].forEach((i) => window.addEventListener(i, r, { passive: !0 })), s);
      }, []),
      e ? c.jsx(SG, t) : null
    );
  },
  Sse = new pO(),''')
rep('''            c.jsx(SG, {
              DENSITY_DISSIPATION: 3.5,''', '''            c.jsx(smdSG, {
              DENSITY_DISSIPATION: 3.5,''')
# 1b) Defer the hero WebGL setup (context + shader compile) until the page has loaded and the main thread is idle,
#     so it no longer competes with the first render / LCP text.
rep('''      I = S.useRef(0);
    return (
      S.useEffect(() => {
        const L = w.current;
        if (!L) return;
        const _ = new P7({''', '''      I = S.useRef(0),
      [smdR, smdSR] = S.useState(!1);
    return (
      S.useEffect(() => {
        let t0 = 0, t1 = 0;
        const go = () => { t1 = (window.requestIdleCallback || ((f) => setTimeout(f, 200)))(() => smdSR(!0), { timeout: 3e3 }); },
          ld = () => { t0 = setTimeout(go, 300); };
        document.readyState === "complete" ? ld() : window.addEventListener("load", ld, { once: !0 });
        return () => { (window.removeEventListener("load", ld), clearTimeout(t0), window.cancelIdleCallback && window.cancelIdleCallback(t1)); };
      }, []),
      S.useEffect(() => {
        const L = w.current;
        if (!L || !smdR) return;
        const _ = new P7({''')
rep('''      }, [e, n, r, s, i, o, a, l, u, d, f, h, p, m, g, x, y, v]),''', '''      }, [smdR, e, n, r, s, i, o, a, l, u, d, f, h, p, m, g, x, y, v]),''')
# 1c) Adaptive: if frames are slow (software GL / weak GPU: rAF gaps > 90ms three times), stop animating and keep
#     the last rendered frame instead of blocking the main thread.
rep('''          if (X - smdLast < 32 && smdRun) return;
          smdLast = X;''', '''          if (X - smdLast < 32 && smdRun) return;
          smdRun && smdLast > 0 && X - smdLast > 90 && ++smdSlow >= 3 && ((smdUntil = 0), (smdRM2 = !0));
          smdLast = X;''')
rep('''        let smdVis = !0, smdRun = !1, smdLast = -1e9, smdUntil = 0;
        const smdGo = (ms) => {
          if (smdRM) return;''', '''        let smdVis = !0, smdRun = !1, smdLast = -1e9, smdUntil = 0, smdSlow = 0, smdRM2 = !1;
        const smdGo = (ms) => {
          if (smdRM || smdRM2) return;''')
# 1d) When the intro ends, re-render the final (frozen) frame once at full resolution (DPR<=1) so the static hero
#     looks identical to the original on phones.
rep('''          if (X < smdUntil) k.current = requestAnimationFrame(Pe); else smdRun = !1;''', '''          if (X < smdUntil) k.current = requestAnimationFrame(Pe);
          else if (((smdRun = !1), _.dpr < 1 && smdUntil > 0)) {
            ((_.dpr = Math.min(window.devicePixelRatio || 1, 1)), ce());
            try { _.render({ scene: C.current }); } catch (ue) {}
            return;
          }''')
