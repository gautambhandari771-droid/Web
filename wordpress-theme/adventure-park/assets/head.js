// Loaded in every page's <head>: picks light/dark mode before the page paints
// (saved choice, else the system setting). Styles live in assets/styles.css,
// built from assets/tailwind.css; colours live in assets/theme.css.
(() => {
  const root = document.documentElement;
  root.classList.add('js');

  const media = matchMedia('(prefers-color-scheme: dark)');
  let saved = null;
  try { saved = localStorage.getItem('theme'); } catch {}

  const applyTheme = () => {
    const mode = saved || root.getAttribute('data-theme') || (media.matches ? 'dark' : 'light');
    root.classList.toggle('dark', mode === 'dark');
  };
  applyTheme();
  media.addEventListener('change', applyTheme);
  new MutationObserver(applyTheme).observe(root, { attributes: true, attributeFilter: ['data-theme'] });

  window.setColorMode = (mode) => {
    saved = mode;
    try { localStorage.setItem('theme', mode); } catch {}
    applyTheme();
  };

})();
