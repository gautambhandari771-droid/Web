// Shared behaviour for every page: header, colour mode, mobile menu,
// scroll reveals, count-up numbers and the enquiry forms.
(() => {
  const root = document.documentElement;
  const reduceMotion = matchMedia('(prefers-reduced-motion: reduce)').matches;

  // Header turns solid once the page scrolls
  const header = document.querySelector('[data-header]');
  if (header) {
    const onScroll = () => header.toggleAttribute('data-scrolled', scrollY > 8);
    addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }

  // Light / dark toggle
  document.querySelectorAll('[data-theme-toggle]').forEach((btn) => {
    btn.addEventListener('click', () => {
      window.setColorMode(root.classList.contains('dark') ? 'light' : 'dark');
    });
  });

  // Mobile menu
  const menuBtn = document.getElementById('menu-btn');
  const menu = document.getElementById('mobile-menu');
  if (menuBtn && menu) {
    const setMenu = (open) => {
      if (open === menu.hasAttribute('data-open')) return;
      menu.toggleAttribute('data-open', open);
      header?.toggleAttribute('data-menu', open);
      menuBtn.setAttribute('aria-expanded', String(open));
      root.style.overflow = open ? 'hidden' : ''; // keep the page behind from scrolling
      // Move focus into the menu once its fade-in has made it visible
      if (open) setTimeout(() => menu.querySelector('a')?.focus({ preventScroll: true }), 50);
    };
    menuBtn.addEventListener('click', () => setMenu(!menu.hasAttribute('data-open')));
    menu.addEventListener('click', (e) => { if (e.target.closest('a')) setMenu(false); });
    addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && menu.hasAttribute('data-open')) { setMenu(false); menuBtn.focus(); }
    });
    // The full-screen menu is for small screens only; close it if the window grows
    matchMedia('(min-width: 64rem)').addEventListener('change', (e) => { if (e.matches) setMenu(false); });
  }

  // Count-up numbers
  const countUp = (el) => {
    const target = Number(el.dataset.count);
    if (reduceMotion) { el.textContent = target; return; }
    const start = performance.now();
    const duration = 1400;
    const tick = (now) => {
      const p = Math.min((now - start) / duration, 1);
      el.textContent = Math.round(target * (1 - Math.pow(1 - p, 3)));
      if (p < 1) requestAnimationFrame(tick);
    };
    requestAnimationFrame(tick);
  };
  const counters = document.querySelectorAll('[data-count]');
  if (!reduceMotion) counters.forEach((el) => (el.textContent = '0'));

  // Reveal sections and start counters as they scroll into view
  const targets = document.querySelectorAll('.reveal, [data-count]');
  if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(({ isIntersecting, target }) => {
        if (!isIntersecting) return;
        if (target.dataset.count) countUp(target);
        else target.classList.add('is-visible');
        observer.unobserve(target);
      });
    }, { threshold: 0.15 });
    targets.forEach((el) => observer.observe(el));
  } else {
    targets.forEach((el) => (el.dataset.count ? countUp(el) : el.classList.add('is-visible')));
  }

  // Dates can't be in the past
  const today = new Date();
  today.setMinutes(today.getMinutes() - today.getTimezoneOffset());
  document.querySelectorAll('input[type="date"][data-min-today]').forEach((el) => {
    el.min = today.toISOString().slice(0, 10);
  });

  // Footer year
  document.querySelectorAll('[data-year]').forEach((el) => (el.textContent = new Date().getFullYear()));

  // Enquiry forms are emailed to the park by FormSubmit (formsubmit.co).
  // The browser validates fields first, then we send them in the background to
  // FormSubmit's AJAX endpoint, so visitors stay on the page. Without JavaScript the
  // form still posts to the normal FormSubmit address in its action attribute.
  const PHONE = '+91 97623 88871';
  document.querySelectorAll('form[data-formsubmit]').forEach((form) => {
    const status = form.querySelector('[data-form-status]');
    const submit = form.querySelector('[type="submit"]');
    const again = form.querySelector('[data-form-again]');
    const endpoint = form.action.replace('://formsubmit.co/', '://formsubmit.co/ajax/');

    form.addEventListener('submit', async (event) => {
      event.preventDefault();
      // Checkbox groups (e.g. activities) arrive as one comma-separated line
      const payload = {};
      for (const [key, value] of new FormData(form)) {
        payload[key] = key in payload ? `${payload[key]}, ${value}` : value;
      }
      form.dataset.state = 'sending';
      submit.disabled = true;
      status.textContent = '';
      try {
        let res;
        try {
          res = await fetch(endpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
            body: JSON.stringify(payload),
          });
        } catch {
          throw new Error('network');
        }
        const data = await res.json().catch(() => ({}));
        if (!res.ok || String(data.success) !== 'true') {
          // e.g. FormSubmit asking the owner to activate the form by email
          console.warn('FormSubmit:', res.status, data.message || data);
          throw new Error('failed');
        }
        form.reset();
        form.dataset.state = 'sent';
        status.textContent = form.dataset.success || 'Thanks! We’ve received your details.';
        again?.focus();
      } catch (err) {
        form.dataset.state = 'error';
        status.textContent =
          err.message === 'network'
            ? 'We couldn’t reach the server. Check your internet connection and try again.'
            : `We couldn’t send your details just now. Please try again, or call us on ${PHONE}.`;
      } finally {
        submit.disabled = false;
      }
    });

    again?.addEventListener('click', () => {
      form.dataset.state = '';
      status.textContent = '';
      form.querySelector('input:not([type="hidden"]):not([name="_honey"])')?.focus();
    });
  });
})();
