// Service worker: keeps a copy of the site on the visitor's phone or computer, so that
// coming back, and moving between pages, is instant even on a slow connection, and the
// pages still open offline. It is registered by assets/site.js once a page has loaded.
//
// - Pages: the saved copy is shown straight away and checked against the website in the
//   background. If the page has changed, the visitor sees a "Refresh" notice. A saved copy
//   older than a day is only used if the website doesn't answer within a few seconds.
// - Styles, scripts and fonts: never change under the same address (each page links them
//   with a ?v= version), so the saved copy is always used.
// - Photos and icons: the saved copy is shown, then refreshed in the background.
// - Anything else, including the booking and contact forms (sent to FormSubmit), goes
//   straight to the network as if this file didn't exist.

// ---- Filled in when the site is built: the version and the files every page needs ----
const VERSION = '697a8f49ec';
const ASSETS = [
  "/assets/styles.css?v=330df259c6",
  "/assets/theme.css?v=f384fb99f1",
  "/assets/head.js?v=cdf2d86312",
  "/assets/site.js?v=37d24ead4c",
  "/assets/hero-fx.js?v=279d560a4e",
  "/assets/fonts/plus-jakarta-sans.woff2",
  "/assets/fonts/instrument-serif-italic-latin.woff2"
];
// ---- end of build section ----

const PAGES = ['/', '/about.html', '/booking.html', '/contact.html'];
const CORE = `core-${VERSION}`; // pages, styles, scripts and fonts of this version
const IMAGES = 'images';        // photos and icons, kept from one version to the next
const MAX_IMAGES = 60;
const FRESH_FOR = 24 * 60 * 60 * 1000; // show a saved page instantly if it's less than a day old
const NETWORK_WAIT = 4000;             // otherwise wait this long for the website before using it

// "/" and "/index.html" are the same page; ?activity=… and #… don't change the page itself
const pageKey = (url) => {
  const u = new URL(url, location.origin);
  return u.origin + (u.pathname.endsWith('/') ? u.pathname + 'index.html' : u.pathname);
};
const savable = (res) => res && res.ok && res.status === 200 && res.type === 'basic' && !res.redirected;
const wait = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

// Save the pages that aren't saved yet (skipped errors are retried on a later visit)
async function savePages() {
  const core = await caches.open(CORE);
  for (const path of PAGES) {
    if (await core.match(pageKey(path))) continue;
    try {
      const res = await fetch(path, { cache: 'no-cache' });
      if (savable(res)) await core.put(pageKey(path), res);
    } catch { /* offline */ }
  }
}

self.addEventListener('install', (event) => {
  event.waitUntil((async () => {
    await (await caches.open(CORE)).addAll(ASSETS);
    const images = await caches.open(IMAGES);
    if (!(await images.match('/assets/logo.png'))) await images.add('/assets/logo.png').catch(() => {});
    // Visitors who turned on "data saver" only keep the pages they open
    if (!self.navigator.connection?.saveData) await savePages();
    await self.skipWaiting();
  })());
});

self.addEventListener('activate', (event) => {
  event.waitUntil((async () => {
    // Start fetching a page while this worker wakes up (it doubles as the background check)
    await self.registration.navigationPreload?.enable();
    for (const name of await caches.keys()) {
      if (name.startsWith('core-') && name !== CORE) await caches.delete(name);
    }
    await self.clients.claim();
  })());
});

// An open page asks for the other pages (and the photos it showed) to be saved for later
self.addEventListener('message', (event) => {
  if (event.data?.type !== 'save-for-later') return;
  event.waitUntil((async () => {
    if (!event.data.saveData) await savePages();
    const images = await caches.open(IMAGES);
    for (const url of (event.data.images || []).slice(0, MAX_IMAGES)) {
      const u = new URL(url, location.origin);
      if (u.origin !== location.origin || !u.pathname.startsWith('/assets/')) continue;
      if (!(await images.match(u.href))) await images.add(u.href).catch(() => {});
    }
    await trimImages();
  })());
});

self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = new URL(request.url);
  if (request.method !== 'GET' || url.origin !== location.origin) return;
  if (request.mode === 'navigate') return event.respondWith(page(event));
  if (/\.(png|jpe?g|webp|svg|ico)$/.test(url.pathname)) return event.respondWith(image(event));
  if (url.pathname.startsWith('/assets/')) return event.respondWith(asset(request));
});

async function page(event) {
  const key = pageKey(event.request.url);
  const core = await caches.open(CORE);
  const saved = await core.match(key);
  const network = (async () => {
    const res = (await event.preloadResponse) || (await fetch(event.request));
    if (savable(res)) await core.put(key, res.clone());
    return res;
  })();
  network.catch(() => {}); // offline: handled below

  const age = saved ? Date.now() - new Date(saved.headers.get('date') || 0).getTime() : Infinity;
  if (saved && age < FRESH_FOR) {
    const shown = saved.clone();
    event.waitUntil(network.then((res) => reportChange(event, shown, res)).catch(() => {}));
    return saved;
  }
  try {
    // A saved copy that's more than a day old is only used if the website is slow or unreachable
    return await (saved ? Promise.race([network, wait(NETWORK_WAIT).then(() => { throw new Error('slow'); })]) : network);
  } catch (error) {
    if (!saved) throw error; // never saved and offline: the browser shows its usual "no internet" page
    event.waitUntil(network.catch(() => {}));
    return saved;
  }
}

// Tell the open page when the website has a newer version of it than the copy just shown
async function reportChange(event, shown, res) {
  if (!savable(res)) return;
  const [before, after] = await Promise.all([shown.text(), res.clone().text()]);
  if (before === after) return;
  for (let i = 0; i < 20; i++) { // the page may still be opening
    const client = await self.clients.get(event.resultingClientId || event.clientId);
    if (client) return client.postMessage({ type: 'page-updated' });
    await wait(250);
  }
}

// Styles, scripts and fonts: the address includes the version, so a saved copy is always right
async function asset(request) {
  const saved = await caches.match(request);
  if (saved) return saved;
  const res = await fetch(request);
  if (savable(res)) await (await caches.open(CORE)).put(request, res.clone());
  return res;
}

// Photos and icons: show the saved copy now, refresh it in the background
async function image(event) {
  const images = await caches.open(IMAGES);
  const saved = await images.match(event.request);
  const network = fetch(event.request).then(async (res) => {
    if (savable(res)) {
      await images.put(event.request, res.clone());
      await trimImages();
    }
    return res;
  });
  if (saved) {
    event.waitUntil(network.catch(() => {}));
    return saved;
  }
  return network;
}

// Keep the photo store small: drop the oldest entries beyond MAX_IMAGES
async function trimImages() {
  const images = await caches.open(IMAGES);
  const keys = await images.keys();
  for (const key of keys.slice(0, Math.max(0, keys.length - MAX_IMAGES))) await images.delete(key);
}
