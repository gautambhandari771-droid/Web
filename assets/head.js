// Loaded in every page's <head>, before Tailwind.
// 1. Picks light/dark mode before the page paints (saved choice, else the system setting).
// 2. Registers the shared Tailwind config, so all pages use the same theme mapping,
//    animations and component styles. Colours themselves live in assets/theme.css.
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

  const css = String.raw`
    @custom-variant dark (&:is(.dark *));

    /* tweakcn theme mapping (matches tweakcn's Tailwind v4 export) */
    @theme inline {
      --color-background: var(--background);
      --color-foreground: var(--foreground);
      --color-card: var(--card);
      --color-card-foreground: var(--card-foreground);
      --color-popover: var(--popover);
      --color-popover-foreground: var(--popover-foreground);
      --color-primary: var(--primary);
      --color-primary-foreground: var(--primary-foreground);
      --color-secondary: var(--secondary);
      --color-secondary-foreground: var(--secondary-foreground);
      --color-muted: var(--muted);
      --color-muted-foreground: var(--muted-foreground);
      --color-accent: var(--accent);
      --color-accent-foreground: var(--accent-foreground);
      --color-destructive: var(--destructive);
      --color-destructive-foreground: var(--destructive-foreground);
      --color-border: var(--border);
      --color-input: var(--input);
      --color-ring: var(--ring);
      --color-chart-1: var(--chart-1);
      --color-chart-2: var(--chart-2);
      --color-chart-3: var(--chart-3);
      --color-chart-4: var(--chart-4);
      --color-chart-5: var(--chart-5);
      --color-sidebar: var(--sidebar);
      --color-sidebar-foreground: var(--sidebar-foreground);
      --color-sidebar-primary: var(--sidebar-primary);
      --color-sidebar-primary-foreground: var(--sidebar-primary-foreground);
      --color-sidebar-accent: var(--sidebar-accent);
      --color-sidebar-accent-foreground: var(--sidebar-accent-foreground);
      --color-sidebar-border: var(--sidebar-border);
      --color-sidebar-ring: var(--sidebar-ring);

      --font-sans: var(--font-sans);
      --font-mono: var(--font-mono);
      --font-serif: var(--font-serif);

      --radius-sm: calc(var(--radius) - 4px);
      --radius-md: calc(var(--radius) - 2px);
      --radius-lg: var(--radius);
      --radius-xl: calc(var(--radius) + 4px);

      --shadow-2xs: var(--shadow-2xs);
      --shadow-xs: var(--shadow-xs);
      --shadow-sm: var(--shadow-sm);
      --shadow: var(--shadow);
      --shadow-md: var(--shadow-md);
      --shadow-lg: var(--shadow-lg);
      --shadow-xl: var(--shadow-xl);
      --shadow-2xl: var(--shadow-2xl);
    }

    /* Animations */
    @theme {
      --animate-fade-up: fade-up 0.6s cubic-bezier(0.2, 0.8, 0.2, 1) both;
      --animate-scale-in: scale-in 0.7s cubic-bezier(0.2, 0.8, 0.2, 1) both;
      --animate-marquee: marquee 45s linear infinite;
      --animate-glow: glow 7s ease-in-out infinite;
      --animate-draw: draw 0.9s ease-out both;
      --animate-spin-slow: turn 6s linear infinite;
      --animate-float: float 6s ease-in-out infinite;
      --animate-shine: shine 3.5s ease-in-out infinite;
      --animate-tick: tick 0.45s cubic-bezier(0.34, 1.56, 0.64, 1) both;
      --animate-fill: fill 2.4s cubic-bezier(0.65, 0, 0.35, 1) both;

      @keyframes fade-up {
        from { opacity: 0; transform: translateY(20px); }
        to   { opacity: 1; transform: none; }
      }
      @keyframes scale-in {
        from { opacity: 0; transform: scale(0.95); }
        to   { opacity: 1; transform: none; }
      }
      @keyframes marquee {
        to { transform: translateX(-50%); }
      }
      @keyframes glow {
        0%, 100% { opacity: 0.55; transform: scale(1); }
        50%      { opacity: 0.9;  transform: scale(1.12); }
      }
      @keyframes draw {
        from { stroke-dashoffset: 1; }
        to   { stroke-dashoffset: 0; }
      }
      @keyframes turn {
        to { transform: rotate(360deg); }
      }
      @keyframes float {
        0%, 100% { transform: translateY(0); }
        50%      { transform: translateY(-10px); }
      }
      @keyframes shine {
        0%        { transform: translateX(-150%) skewX(-20deg); }
        60%, 100% { transform: translateX(450%) skewX(-20deg); }
      }
      @keyframes tick {
        from { opacity: 0; transform: scale(0.3); }
        to   { opacity: 1; transform: none; }
      }
      @keyframes fill {
        from { transform: scaleX(0); }
        to   { transform: scaleX(1); }
      }
    }

    @layer base {
      * { @apply border-border outline-ring/50; }
      body { @apply bg-background font-sans text-foreground; }
      h1, h2, h3 { text-wrap: balance; }
      .dark { color-scheme: dark; }
    }

    /* Faint grid behind heroes and the call-to-action band */
    @utility bg-grid {
      background-image:
        linear-gradient(to right, var(--border) 1px, transparent 1px),
        linear-gradient(to bottom, var(--border) 1px, transparent 1px);
      background-size: var(--grid-size, 24px) var(--grid-size, 24px);
    }

    /* Primary colour for text: darkened in light mode so it stays readable */
    @utility text-primary-ink {
      color: color-mix(in oklab, var(--primary) 76%, black);
      &:is(.dark *) { color: var(--primary); }
    }

    /* Form inputs */
    @utility field {
      @apply mt-2 block w-full rounded-lg border border-input bg-background px-3.5 py-2.5 text-foreground shadow-xs outline-none transition placeholder:text-muted-foreground/70 hover:border-foreground/25 focus:border-ring focus:ring-4 focus:ring-ring/20 user-invalid:border-destructive user-invalid:ring-4 user-invalid:ring-destructive/15;
    }
    select.field {
      appearance: none;
      padding-right: 2.5rem;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='%2394a3b8'%3E%3Cpath fill-rule='evenodd' d='M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.17l3.71-3.94a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z' clip-rule='evenodd'/%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: right 0.75rem center;
      background-size: 1.25rem;
    }

    /* Scroll-reveal: hidden only when JS is available to reveal it */
    .js .reveal:not(.is-visible) { opacity: 0; }
    .reveal.is-visible {
      animation: var(--animate-fade-up);
      animation-delay: var(--d, 0ms);
    }

    @media (prefers-reduced-motion: reduce) {
      *, *::before, *::after {
        animation: none !important;
        transition: none !important;
        scroll-behavior: auto !important;
      }
    }
  `;

  const style = document.createElement('style');
  style.type = 'text/tailwindcss';
  style.textContent = css;
  document.head.appendChild(style);
})();
