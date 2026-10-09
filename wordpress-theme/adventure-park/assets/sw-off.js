// In the WordPress dashboard: remove the visitors' offline copy (sw.js) from this browser, so the
// site owner always sees the live pages and the Customizer preview.
if ('serviceWorker' in navigator) {
  navigator.serviceWorker.getRegistrations().then((regs) => regs.forEach((reg) => reg.unregister()));
  if (window.caches) caches.keys().then((names) => names.forEach((name) => caches.delete(name)));
}
