// Home hero hover effects (inspired by antigravity.google):
// a field of short dashes that gathers around the pointer, and a background glow
// that drifts toward it. Nothing runs until a mouse is over the hero, and the
// animation loop stops as soon as everything has settled after the mouse leaves.
// Skipped on touch screens and when the visitor prefers reduced motion.
(() => {
  const hero = document.querySelector('[data-hero]');
  const canvas = hero?.querySelector('[data-hero-canvas]');
  const glow = hero?.querySelector('[data-hero-glow]');
  if (!hero || !canvas) return;
  if (!matchMedia('(hover: hover) and (pointer: fine)').matches) return;
  if (matchMedia('(prefers-reduced-motion: reduce)').matches) return;

  const SPACING = 26;   // px between dashes
  const RADIUS = 170;   // how far the pointer reaches
  const PUSH = 12;      // how far dashes drift away from the pointer
  const EASE = 0.12;    // how quickly dashes follow (0–1)
  const GLOW_SHIFT = 0.05; // how far the glow follows the pointer

  const ctx = canvas.getContext('2d');
  const root = document.documentElement;
  let width = 0;
  let height = 0;
  let rect = null;
  let particles = [];
  let colors = [];
  let running = false;
  const pointer = { x: 0, y: 0, tx: 0, ty: 0, inside: false };

  const readColors = () => {
    const css = getComputedStyle(root);
    colors = ['--chart-1', '--chart-2', '--chart-3', '--chart-5'].map((name) => css.getPropertyValue(name).trim());
  };

  // Sized lazily on first hover, so visitors who never hover pay nothing
  const build = () => {
    rect = hero.getBoundingClientRect();
    const dpr = Math.min(window.devicePixelRatio || 1, 2);
    width = rect.width;
    height = rect.height;
    canvas.width = Math.round(width * dpr);
    canvas.height = Math.round(height * dpr);
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    ctx.lineCap = 'round';
    ctx.lineWidth = 1.6;
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

  const frame = () => {
    pointer.x += (pointer.tx - pointer.x) * 0.16;
    pointer.y += (pointer.ty - pointer.y) * 0.16;
    ctx.clearRect(0, 0, width, height);
    let active = false;

    for (const p of particles) {
      const dx = p.bx - pointer.x;
      const dy = p.by - pointer.y;
      const near = pointer.inside && Math.abs(dx) < RADIUS && Math.abs(dy) < RADIUS;
      if (!near && p.a < 0.01) { p.a = p.ox = p.oy = p.len = 0; continue; }

      let ta = 0, tox = 0, toy = 0, tlen = 0;
      const dist = Math.hypot(dx, dy);
      if (near && dist > 0 && dist < RADIUS) {
        const t = 1 - dist / RADIUS;
        const e = t * t * (3 - 2 * t);                          // smoothstep
        const clearCentre = Math.min(1, dist / (RADIUS * 0.3)); // keep text under the cursor readable
        ta = e * clearCentre * 0.65 * p.weight;
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
      const x = p.bx + p.ox;
      const y = p.by + p.oy;
      const hx = (Math.cos(p.angle) * p.len) / 2;
      const hy = (Math.sin(p.angle) * p.len) / 2;
      ctx.globalAlpha = p.a;
      ctx.strokeStyle = colors[p.color];
      ctx.beginPath();
      ctx.moveTo(x - hx, y - hy);
      ctx.lineTo(x + hx, y + hy);
      ctx.stroke();
    }

    if (glow && pointer.inside) {
      glow.style.setProperty('--gx', `${((pointer.x - width / 2) * GLOW_SHIFT).toFixed(1)}px`);
      glow.style.setProperty('--gy', `${((pointer.y - height / 2) * GLOW_SHIFT).toFixed(1)}px`);
    }

    if (active || pointer.inside) {
      requestAnimationFrame(frame);
    } else {
      running = false;
      ctx.clearRect(0, 0, width, height);
    }
  };

  const start = () => {
    if (running) return;
    running = true;
    requestAnimationFrame(frame);
  };

  const track = (event) => {
    pointer.tx = event.clientX - rect.left;
    pointer.ty = event.clientY - rect.top;
  };

  hero.addEventListener('pointerenter', (event) => {
    if (event.pointerType !== 'mouse') return;
    if (!particles.length) { readColors(); build(); }
    rect = hero.getBoundingClientRect();
    track(event);
    pointer.x = pointer.tx;
    pointer.y = pointer.ty;
    pointer.inside = true;
    start();
  });
  hero.addEventListener('pointermove', (event) => {
    if (event.pointerType !== 'mouse' || !rect) return;
    track(event);
    pointer.inside = true;
    start();
  }, { passive: true });
  hero.addEventListener('pointerleave', () => {
    pointer.inside = false;
    glow?.style.setProperty('--gx', '0px');
    glow?.style.setProperty('--gy', '0px');
  });

  // Keep positions right when the page scrolls or resizes mid-hover
  addEventListener('scroll', () => { if (pointer.inside) rect = hero.getBoundingClientRect(); }, { passive: true });
  new ResizeObserver(() => { if (particles.length) build(); }).observe(hero);
  // Follow light/dark switches
  new MutationObserver(readColors).observe(root, { attributes: true, attributeFilter: ['class'] });
})();
