// Ambient + hover effects for sections marked [data-hero] (inspired by antigravity.google):
// - [data-hero-canvas]: a ring of short dashes. On its own it drifts slowly around the
//   section; when a mouse comes in it glides over and follows the pointer, then drifts
//   off again when the mouse leaves.
// - [data-hero-glow]: background glow that follows the ring (or the pointer).
// Sections without a dash field only react to a mouse. Everything pauses while the
// section is off screen, and nothing runs for visitors who prefer reduced motion.
(() => {
  if (matchMedia('(prefers-reduced-motion: reduce)').matches) return;

  const canHover = matchMedia('(hover: hover) and (pointer: fine)').matches;
  const SPACING = 26;      // px between dashes
  const PUSH = 12;         // how far dashes drift away from the ring's centre
  const EASE = 0.12;       // how quickly dashes respond (0–1)
  const IDLE_LEVEL = 0.6;  // ring strength when drifting on its own (hover = 1)
  const GLOW_SHIFT = 0.05; // how far the glow follows
  const LEVELS = 12;       // opacity steps used to batch drawing
  const MAX_PIXELS = 3e6;  // cap canvas resolution on large screens

  const root = document.documentElement;
  let colors = [];
  const readColors = () => {
    const css = getComputedStyle(root);
    colors = ['--chart-1', '--chart-2', '--chart-3', '--chart-5'].map((name) => css.getPropertyValue(name).trim());
  };
  // Follow light/dark switches
  new MutationObserver(() => { if (colors.length) readColors(); }).observe(root, { attributes: true, attributeFilter: ['class'] });

  const setup = (section) => {
    const canvas = section.querySelector('[data-hero-canvas]');
    const glow = section.querySelector('[data-hero-glow]');
    const ctx = canvas?.getContext('2d');
    const ambient = Boolean(ctx);
    if (!ambient && !canHover) return; // glow-only sections need a mouse

    let width = 0;
    let height = 0;
    let radius = 170;
    let rect = null;
    let particles = [];
    let running = false;
    let visible = false;
    let frameNo = 0;
    let level = 0;           // current ring strength
    let dirty = null;        // area drawn last frame, cleared next frame
    const buckets = Array.from({ length: 4 * LEVELS }, () => []);
    const pointer = { x: 0, y: 0, mx: 0, my: 0, mouse: false };

    const measure = () => {
      rect = section.getBoundingClientRect();
      width = rect.width;
      height = rect.height;
      radius = Math.max(110, Math.min(170, width * 0.14));
    };

    // A slow, looping path for the ring when nobody is hovering
    const drift = (t) => ({
      x: width * (0.5 + 0.34 * Math.sin(t * 0.00019 + 1.3)),
      y: height * (0.45 + 0.28 * Math.sin(t * 0.00031)),
    });

    const build = () => {
      if (!ctx) return;
      measure();
      const dpr = Math.min(window.devicePixelRatio || 1, 2, Math.sqrt(MAX_PIXELS / Math.max(1, width * height)));
      canvas.width = Math.round(width * dpr);
      canvas.height = Math.round(height * dpr);
      ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
      ctx.lineCap = 'round';
      ctx.lineWidth = 1.6;
      dirty = null;
      particles = [];
      for (let y = SPACING / 2; y < height; y += SPACING) {
        for (let x = SPACING / 2; x < width; x += SPACING) {
          particles.push({
            bx: x + (Math.random() - 0.5) * SPACING * 0.6,
            by: y + (Math.random() - 0.5) * SPACING * 0.6,
            ox: 0, oy: 0, a: 0, len: 0, angle: 0,
            color: (Math.random() * 4) | 0,
            weight: 0.5 + Math.random() * 0.7,
          });
        }
      }
    };

    const drawDashes = () => {
      if (dirty) ctx.clearRect(dirty.x0, dirty.y0, dirty.x1 - dirty.x0, dirty.y1 - dirty.y0);
      let x0 = Infinity, y0 = Infinity, x1 = -Infinity, y1 = -Infinity;
      let active = false;

      for (const p of particles) {
        const dx = p.bx - pointer.x;
        const dy = p.by - pointer.y;
        const near = Math.abs(dx) < radius && Math.abs(dy) < radius;
        if (!near && p.a < 0.01) { p.a = p.ox = p.oy = p.len = 0; continue; }

        let ta = 0, tox = 0, toy = 0, tlen = 0;
        const dist = Math.hypot(dx, dy);
        if (near && dist > 0 && dist < radius) {
          const t = 1 - dist / radius;
          const e = t * t * (3 - 2 * t);                          // smoothstep
          const clearCentre = Math.min(1, dist / (radius * 0.3)); // keep text under the ring's centre readable
          ta = e * clearCentre * 0.65 * p.weight * level;
          tox = (dx / dist) * e * PUSH;
          toy = (dy / dist) * e * PUSH;
          tlen = 2 + e * 6;
          p.angle = Math.atan2(dy, dx);
        }
        p.a += (ta - p.a) * EASE;
        p.ox += (tox - p.ox) * EASE;
        p.oy += (toy - p.oy) * EASE;
        p.len += (tlen - p.len) * EASE;
        if (p.a < 0.01) continue;

        active = true;
        const step = Math.min(LEVELS - 1, Math.round(p.a * LEVELS));
        if (!step) continue;
        const x = p.bx + p.ox;
        const y = p.by + p.oy;
        const hx = (Math.cos(p.angle) * p.len) / 2;
        const hy = (Math.sin(p.angle) * p.len) / 2;
        buckets[p.color * LEVELS + step].push(x - hx, y - hy, x + hx, y + hy);
        if (x < x0) x0 = x; if (x > x1) x1 = x;
        if (y < y0) y0 = y; if (y > y1) y1 = y;
      }

      // One stroke per colour and opacity step instead of one per dash
      for (let c = 0; c < 4; c++) {
        ctx.strokeStyle = colors[c];
        for (let s = 1; s < LEVELS; s++) {
          const b = buckets[c * LEVELS + s];
          if (!b.length) continue;
          ctx.globalAlpha = s / LEVELS;
          ctx.beginPath();
          for (let i = 0; i < b.length; i += 4) { ctx.moveTo(b[i], b[i + 1]); ctx.lineTo(b[i + 2], b[i + 3]); }
          ctx.stroke();
          b.length = 0;
        }
      }
      const pad = 8; // half a dash plus line width
      dirty = x1 >= x0 ? { x0: x0 - pad, y0: y0 - pad, x1: x1 + pad, y1: y1 + pad } : null;
      return active;
    };

    const frame = (now) => {
      if (!visible) { running = false; return; }
      // Phones and tablets: half frame rate to save battery
      if (!canHover && (frameNo++ & 1)) { requestAnimationFrame(frame); return; }

      const target = pointer.mouse ? { x: pointer.mx, y: pointer.my } : drift(now);
      const follow = pointer.mouse ? 0.16 : 0.05;
      pointer.x += (target.x - pointer.x) * follow;
      pointer.y += (target.y - pointer.y) * follow;
      level += ((pointer.mouse ? 1 : ambient ? IDLE_LEVEL : 0) - level) * 0.05;

      const active = ctx ? drawDashes() : false;

      if (glow && (ambient || pointer.mouse)) {
        glow.style.setProperty('--gx', `${((pointer.x - width / 2) * GLOW_SHIFT).toFixed(1)}px`);
        glow.style.setProperty('--gy', `${((pointer.y - height / 2) * GLOW_SHIFT).toFixed(1)}px`);
      }

      if (ambient || pointer.mouse || active) {
        requestAnimationFrame(frame);
      } else {
        running = false;
      }
    };

    const start = () => {
      if (running || !visible) return;
      running = true;
      requestAnimationFrame(frame);
    };

    // Run only while the section is on screen
    new IntersectionObserver(([entry]) => {
      visible = entry.isIntersecting;
      if (!visible) return;
      if (!colors.length) readColors();
      if (ctx && !particles.length) {
        build();
        const p = drift(performance.now());
        pointer.x = p.x;
        pointer.y = p.y;
      } else {
        measure();
      }
      if (ambient) start();
    }).observe(section);

    if (canHover) {
      const track = (event) => {
        pointer.mx = event.clientX - rect.left;
        pointer.my = event.clientY - rect.top;
      };
      section.addEventListener('pointerenter', (event) => {
        if (event.pointerType !== 'mouse') return;
        if (!colors.length) readColors();
        measure();
        track(event);
        if (!ambient) { pointer.x = pointer.mx; pointer.y = pointer.my; }
        pointer.mouse = true;
        start();
      });
      section.addEventListener('pointermove', (event) => {
        if (event.pointerType !== 'mouse' || !rect) return;
        track(event);
        pointer.mouse = true;
        start();
      }, { passive: true });
      section.addEventListener('pointerleave', () => {
        pointer.mouse = false;
        if (!ambient) {
          glow?.style.setProperty('--gx', '0px');
          glow?.style.setProperty('--gy', '0px');
        }
      });
      // Keep positions right when the page scrolls mid-hover
      addEventListener('scroll', () => { if (pointer.mouse) measure(); }, { passive: true });
    }

    new ResizeObserver(() => { if (particles.length) build(); else if (rect) measure(); }).observe(section);
  };

  document.querySelectorAll('[data-hero]').forEach(setup);
})();
