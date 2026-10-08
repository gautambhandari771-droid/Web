// Device preview (preview.html): shows the site in phone, tablet and desktop frames.
(() => {
  const ROOT = document.body.dataset.siteRoot || '';
  const DEVICES = {
    mobile: { label: 'Mobile', w: 390, h: 844 },
    tablet: { label: 'Tablet', w: 820, h: 1180 },
    desktop: { label: 'Desktop', w: 1440, h: 900 },
  };
  const PAGES = ['index.html', 'about.html', 'booking.html', 'contact.html'];

  const stage = document.getElementById('stage');
  const devicesEl = document.getElementById('devices');
  const pageSelect = document.getElementById('page');
  const rotateBtn = document.getElementById('rotate');
  const zoomEl = document.getElementById('zoom');
  const openLink = document.getElementById('open');
  const blocked = document.getElementById('blocked');
  document.querySelectorAll('[data-page-link]').forEach((a) => (a.href = ROOT + a.getAttribute('href')));

  // Remembered per viewer; the page works the same without it
  const state = { device: innerWidth < 760 ? 'mobile' : 'all', page: 'index.html', landscape: false, mode: null };
  try { Object.assign(state, JSON.parse(localStorage.getItem('device-preview') || '{}')); } catch {}
  if (!PAGES.includes(state.page)) state.page = 'index.html';
  const save = () => { try { localStorage.setItem('device-preview', JSON.stringify(state)); } catch {} };

  let frames = [];

  const syncControls = () => {
    document.querySelectorAll('[data-device]').forEach((b) => b.setAttribute('aria-pressed', String(b.dataset.device === state.device)));
    document.querySelectorAll('[data-mode]').forEach((b) => b.setAttribute('aria-pressed', String(b.dataset.mode === state.mode)));
    pageSelect.value = state.page;
    rotateBtn.disabled = state.device === 'desktop';
    rotateBtn.setAttribute('aria-pressed', String(state.landscape));
    openLink.href = ROOT + state.page;
  };

  const fit = () => {
    devicesEl.style.transform = 'translate(-50%, -50%)';
    const scale = Math.min(1, (stage.clientWidth - 32) / devicesEl.offsetWidth, (stage.clientHeight - 40) / devicesEl.offsetHeight);
    devicesEl.style.setProperty('--s', scale);
    devicesEl.style.transform = `translate(-50%, -50%) scale(${scale})`;
    zoomEl.textContent = Math.round(scale * 100) + '%';
  };

  const onFrameLoad = (iframe) => {
    let win, doc;
    try { win = iframe.contentWindow; doc = iframe.contentDocument; } catch {}
    if (!doc || !doc.querySelector('[data-header]')) { blocked.hidden = false; return; }
    blocked.hidden = true;
    // Site colour mode: apply the chosen one, or adopt the site's current one
    if (state.mode) win.setColorMode?.(state.mode);
    else { state.mode = doc.documentElement.classList.contains('dark') ? 'dark' : 'light'; syncControls(); }
    const chrome = iframe.parentElement.querySelector('.chrome span');
    if (chrome) chrome.textContent = doc.title;
    // Follow links clicked inside a preview, and keep the other devices on the same page
    const file = win.location.pathname.split('/').pop() || 'index.html';
    if (PAGES.includes(file) && file !== state.page) {
      state.page = file; save(); syncControls();
      frames.forEach((f) => { if (f !== iframe) f.src = ROOT + file; });
    }
  };

  const render = () => {
    const keys = state.device === 'all' ? ['mobile', 'tablet', 'desktop'] : [state.device];
    devicesEl.replaceChildren();
    frames = keys.map((key) => {
      const d = DEVICES[key];
      const turn = state.landscape && key !== 'desktop';
      const w = turn ? d.h : d.w;
      const h = turn ? d.w : d.h;
      const figure = document.createElement('figure');
      figure.className = 'device';
      const frame = document.createElement('div');
      frame.className = 'frame ' + key;
      if (key === 'desktop') {
        const chrome = document.createElement('div');
        chrome.className = 'chrome';
        chrome.innerHTML = '<i></i><i></i><i></i><span>Adventure Park</span>';
        frame.append(chrome);
      }
      const iframe = document.createElement('iframe');
      iframe.title = `${d.label} preview`;
      iframe.style.width = w + 'px';
      iframe.style.height = h + 'px';
      iframe.addEventListener('load', () => onFrameLoad(iframe));
      iframe.src = ROOT + state.page;
      frame.append(iframe);
      const caption = document.createElement('figcaption');
      caption.className = 'label';
      caption.textContent = `${d.label} · ${w} × ${h}`;
      figure.append(frame, caption);
      devicesEl.append(figure);
      return iframe;
    });
    syncControls();
    fit();
  };

  document.querySelectorAll('[data-device]').forEach((b) => b.addEventListener('click', () => {
    state.device = b.dataset.device; save(); render();
  }));
  document.querySelectorAll('[data-mode]').forEach((b) => b.addEventListener('click', () => {
    state.mode = b.dataset.mode; save(); syncControls();
    frames.forEach((f) => { try { f.contentWindow.setColorMode?.(state.mode); } catch {} });
  }));
  pageSelect.addEventListener('change', () => {
    state.page = pageSelect.value; save(); syncControls();
    frames.forEach((f) => (f.src = ROOT + state.page));
  });
  rotateBtn.addEventListener('click', () => { state.landscape = !state.landscape; save(); render(); });
  new ResizeObserver(fit).observe(stage);

  render();
})();
