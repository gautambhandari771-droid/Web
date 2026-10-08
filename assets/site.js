// Shared behaviour for every page: header, colour mode, mobile menu,
// scroll reveals, count-up numbers, Book buttons, copy buttons and the forms.
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

  // Looping animations that speed up while their area is hovered (e.g. the climbing emoji).
  // updatePlaybackRate keeps the current position, so the change never jumps.
  document.querySelectorAll('[data-hover-speed]').forEach((el) => {
    const scope = el.closest('[data-speed-scope]') || el;
    const rate = Number(el.dataset.hoverSpeed) || 2;
    const setRate = (r) => el.getAnimations().forEach((anim) => anim.updatePlaybackRate(r));
    scope.addEventListener('pointerenter', () => setRate(rate));
    scope.addEventListener('pointerleave', () => setRate(1));
  });

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

  // "Book" buttons link to booking.html?activity=… : tick that activity in the booking form.
  // Only these known names are accepted, so nothing from the address ends up on the page.
  // (Rafting has four trips, so "Book rafting" leaves the choice of trip to the visitor.)
  const ACTIVITIES = new Map([
    ['bungee', 'Bungee jumping'], ['zipline', 'Zip line'],
    ['camps', 'Camps & cottages'], ['hotel', 'Hotel room'], ['other', 'Other activities'],
  ]);
  const preset = ACTIVITIES.get(new URLSearchParams(location.search).get('activity'));
  if (preset) {
    document.querySelectorAll('input[name="activities"]').forEach((box) => {
      if (box.value === preset) box.checked = true;
    });
  }

  // Copy buttons (e.g. the UPI ID); the text stays selectable if copying isn't allowed
  document.querySelectorAll('[data-copy]').forEach((btn) => {
    const label = btn.querySelector('[data-copy-label]');
    const original = label?.textContent;
    btn.addEventListener('click', async () => {
      try { await navigator.clipboard.writeText(btn.dataset.copy); } catch { return; }
      if (!label) return;
      label.textContent = 'Copied!';
      setTimeout(() => (label.textContent = original), 2000);
    });
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

  // The booking form carries the payment screenshot, so it is sent as a normal upload:
  // FormSubmit emails files only from multipart form posts. Afterwards FormSubmit sends
  // the visitor back here with ?sent=1, which shows the thank-you panel.
  document.querySelectorAll('form[data-upload]').forEach((form) => {
    const submit = form.querySelector('[type="submit"]');
    const file = form.querySelector('input[type="file"]');
    const next = form.querySelector('input[name="_next"]');
    const again = form.querySelector('[data-form-again]');
    const boxes = [...form.querySelectorAll('input[type="checkbox"][name="activities"]')];
    const MAX_BYTES = 10 * 1024 * 1024; // FormSubmit's limit for all files together

    const checkFile = () => {
      const f = file?.files[0];
      let problem = '';
      if (f && !/^image\/|^application\/pdf$/.test(f.type)) problem = 'Please attach the payment screenshot as an image (JPG or PNG) or a PDF.';
      else if (f && f.size > MAX_BYTES) problem = 'This file is larger than 10 MB. Please attach a smaller screenshot.';
      file?.setCustomValidity(problem);
    };
    file?.addEventListener('change', checkFile);

    // Ticked activities arrive as one comma-separated line (sent instead of the separate boxes)
    const joined = document.createElement('input');
    joined.type = 'hidden';
    joined.name = 'activities';

    form.addEventListener('submit', () => {
      // Return to this page at whatever address it is served from (live site or a preview)
      if (next) next.value = `${location.origin}${location.pathname}?sent=1#booking-form`;
      joined.value = boxes.filter((b) => b.checked).map((b) => b.value).join(', ');
      form.append(joined);
      boxes.forEach((b) => (b.disabled = true));
      form.dataset.state = 'sending';
      submit.disabled = true;
    });

    // Coming back with the browser's Back button after sending: make the form usable again
    addEventListener('pageshow', (event) => {
      if (!event.persisted || form.dataset.state !== 'sending') return;
      boxes.forEach((b) => (b.disabled = false));
      joined.remove();
      form.dataset.state = '';
      submit.disabled = false;
    });

    if (new URLSearchParams(location.search).get('sent') === '1') form.dataset.state = 'sent';

    again?.addEventListener('click', () => {
      form.reset();
      form.dataset.state = '';
      history.replaceState(null, '', `${location.pathname}#booking-form`);
      form.querySelector('input:not([type="hidden"]):not([name="_honey"])')?.focus();
    });
  });
})();
