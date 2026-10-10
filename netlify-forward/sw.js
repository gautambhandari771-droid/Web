// The website moved to https://adventureprk.in. This replaces the old offline copy on
// adventureprk.netlify.app: it deletes the pages saved in visitors' browsers, removes itself
// and reloads open tabs, which then forward to the new address.
self.addEventListener('install', () => self.skipWaiting());
self.addEventListener('activate', (event) => event.waitUntil((async () => {
  for (const name of await caches.keys()) await caches.delete(name);
  await self.registration.unregister();
  for (const client of await self.clients.matchAll({ type: 'window' })) client.navigate(client.url);
})()));
