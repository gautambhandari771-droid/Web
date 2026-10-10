// Prepares the website for Cloudflare: copies the public files into dist/, which Cloudflare publishes
// (see wrangler.jsonc). Only the files listed here are copied, so project files such as README.md,
// netlify.toml and assets/tailwind.css never reach the website. Cloudflare runs this before every
// deploy; to check it yourself, run:  node build-cloudflare.mjs
import { cpSync, existsSync, mkdirSync, readdirSync, rmSync, statSync } from 'node:fs';
import { join } from 'node:path';

const OUT = 'dist';
const top = readdirSync('.');
const files = [
  // pages, the crawler and AI files, the offline copy, the icon and the IndexNow key
  ...top.filter((f) => /\.(html|txt|xml|ico|webmanifest)$/.test(f)),
  'sw.js',
  // address and header rules for Cloudflare
  '_redirects',
  '_headers',
];
const folders = ['assets', '.well-known'];
const skip = new Set(['assets/tailwind.css']); // the source of styles.css, not part of the website

rmSync(OUT, { recursive: true, force: true });
mkdirSync(OUT);
for (const f of files) {
  if (!existsSync(f) || !statSync(f).isFile()) throw new Error(`Missing file: ${f}`);
  cpSync(f, join(OUT, f));
}
for (const d of folders) {
  cpSync(d, join(OUT, d), { recursive: true, filter: (src) => !skip.has(src.replaceAll('\\', '/')) });
}
for (const page of ['index.html', 'about.html', 'booking.html', 'contact.html', '404.html']) {
  if (!existsSync(join(OUT, page))) throw new Error(`Missing page: ${page}`);
}

let count = 0;
const walk = (d) => readdirSync(d).forEach((f) => (statSync(join(d, f)).isDirectory() ? walk(join(d, f)) : count++));
walk(OUT);
console.log(`Website ready in ${OUT}/: ${count} files.`);
