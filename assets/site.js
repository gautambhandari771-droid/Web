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
      menu.toggleAttribute('data-open', open);
      menuBtn.setAttribute('aria-expanded', String(open));
    };
    menuBtn.addEventListener('click', () => setMenu(!menu.hasAttribute('data-open')));
    menu.addEventListener('click', (e) => { if (e.target.closest('a')) setMenu(false); });
    addEventListener('keydown', (e) => { if (e.key === 'Escape') setMenu(false); });
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

  // Enquiry forms: the browser validates fields first, then we post in the background.
  // Netlify Forms receives these automatically when the site is hosted on Netlify.
  document.querySelectorAll('form[data-netlify]').forEach((form) => {
    const status = form.querySelector('[data-form-status]');
    const submit = form.querySelector('[type="submit"]');
    const again = form.querySelector('[data-form-again]');

    form.addEventListener('submit', async (event) => {
      event.preventDefault();
      const body = new URLSearchParams(new FormData(form)).toString();
      form.dataset.state = 'sending';
      submit.disabled = true;
      status.textContent = '';
      try {
        const res = await fetch('/', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body,
        });
        if (!res.ok) {
          const unavailable = [404, 405, 501].includes(res.status);
          throw new Error(unavailable ? 'unavailable' : 'failed');
        }
        form.reset();
        form.dataset.state = 'sent';
        status.textContent = form.dataset.success || 'Thanks! We’ve received your details.';
        again?.focus();
      } catch (err) {
        form.dataset.state = 'error';
        status.textContent =
          err.message === 'unavailable'
            ? 'Online sending isn’t switched on for this site yet. Please call or email us instead.'
            : err.message === 'failed'
              ? 'Something went wrong while sending. Please try again in a moment.'
              : 'We couldn’t reach the server. Check your connection and try again.';
      } finally {
        submit.disabled = false;
      }
    });

    again?.addEventListener('click', () => {
      form.dataset.state = '';
      status.textContent = '';
      form.querySelector('input:not([type="hidden"]):not([name="bot-field"])')?.focus();
    });
  });
})();
