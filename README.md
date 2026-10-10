# Adventure Park — website

**Where Rishikesh gets wild.**
The website for Adventure Park, an adventure-activities brand in Rishikesh, Uttarakhand, running since 2006 and founded by Jagat Singh Bhandari (JSB — 25+ years of experience, more than 1 lakh people served). The site presents the activities, stays and prices, puts safety first, and lets visitors book online — pay a 50% advance by UPI and send a booking request with the payment screenshot — or get in touch.

> **Status:** moving from Netlify to **Cloudflare**, which will publish the `main` branch of this repository automatically. See [Publishing on Cloudflare](#publishing-on-cloudflare). Until the Cloudflare project is set up, the live address is still https://adventureprk.netlify.app.

---

## Pages

| Page | File | What it's for |
|---|---|---|
| Home | `index.html` | Hero with the tagline, activities and prices with a **Book** button on each, stay (camps & cottages, hotel rooms), food menu, safety checklist, how booking works, FAQs |
| About us | `about.html` | Founder story, numbers, values and safety promise |
| Booking | `booking.html` | Booking request form (group, experience, activities incl. the four rafting trips, dates, health notes, **required payment screenshot**), the **Pay by UPI** section with the QR code and the 50% advance rule, and the **refund policy**. Every "Book" button leads here |
| Contact us | `contact.html` | Contact form (name, age, gender, phone, email), office address, phone, WhatsApp, email |
| Page not found | `404.html` | Shown for any address that doesn't exist |
| Device preview | `preview.html` | Tool for checking the site on mobile, tablet and desktop (hidden from search engines) |

## What visitors can do

- **See activities and prices** — rafting (12, 16, 26 and 36 km, 1 to 3 hours), bungee jumping (109 m), zip line, luxury camps & cottages (200 m up from the river, 100 m up from the national highway) and hotel rooms, all in ₹.
- **See real photos** — the rafting card opens with four of Adventure Park's own rafting photos, the bungee card with two jump photos (used with Splash Bungy's permission), and the zip line card with two photos marked "Representative photo". Swipe through them on a phone, or use the arrow buttons; the dots show which photo is in view (see [Photos](#photos)).
- **See the food menu** — breakfast, buffet lunch (pure veg), evening snacks and buffet dinner (veg & non-veg), included with camp and cottage stays.
- **Book any activity or stay** — every activity card (rafting, bungee, zip line, more adventures) and both stays (camps & cottages, hotel rooms) has its own **Book** button, which opens the booking form with that activity already ticked. In the form, rafting offers all four trips (12, 16, 26 and 36 km) with route, duration and price.
- **Send a booking request** or **a contact message** — both forms email the details to the park (see [Forms](#forms-formsubmit)).
- **Pay by UPI, then book** — the Booking page shows the park's UPI QR code (M/S ADVENTURE PARK, `8755542743@ybl`) with buttons to download the QR and copy the UPI ID. Visitors pay a 50% advance, then send the booking form with the **payment screenshot attached** — the form can't be sent without it. Cancelling within 36 hours of booking gets the advance refunded (see [Payment](#payment-upi-qr-code)).
- **Chat on WhatsApp** with the floating button on every page (+91 87555 42743) — the chat opens with a greeting already typed.
- **Call or email** — numbers and email are on the Contact page and in every footer.
- **Find the office** — Near Shiv Mandir, Badrinath Highway, Shivpuri, Uttarakhand, with the [Google Maps location](https://maps.app.goo.gl/CKrMGT2h8CRNrcmi6) linked from the Contact page, every footer and the mobile menu.
- **Follow on Instagram** — [@adventure_park771](https://www.instagram.com/adventure_park771/), linked in the footer, the mobile menu and the Contact page.
- **Use it on a phone** — a full-screen menu with big links, a "Book now" button and tap-to-call, WhatsApp, email and map links.
- **Use it on slow mobile data, or offline** — after the first visit, pages open instantly from a copy saved on the phone, even with no signal (see [Caching and offline](#caching-and-offline)).
- **Switch light/dark mode** — follows the visitor's device setting by default.
- **Find the park on Google and in AI assistants** — every page carries search, link-preview and structured data, and the site is open to search engines and AI crawlers (see [Search engines and AI assistants](#search-engines-and-ai-assistants-seo-geo-aio)).

---

## How it's built

A static website: plain HTML, CSS and a little JavaScript. **Nothing is compiled on deploy.** Before each deploy, Cloudflare runs `build-cloudflare.mjs`, which only copies the website's own files into `dist/` and publishes that folder.

- **Tailwind CSS v4** styles the site. The styles are built ahead of time into `assets/styles.css`, so pages load fast and nothing is compiled in the visitor's browser. Changing words needs no rebuild; changing the design does (see [Changing the design](#changing-the-design-rebuilding-the-styles)).
- **Theme** in the [tweakcn](https://tweakcn.com) / shadcn format, so colours can be swapped in one file.
- **Fonts** (Plus Jakarta Sans and Instrument Serif) are stored on the site itself in `assets/fonts/`, so no request goes to Google when a page loads. Each page fetches just two font files, both requested first thing (see [Fonts](#fonts)).
- **Forms** are delivered by [FormSubmit](https://formsubmit.co) — no server of our own.
- **Nothing else is loaded from outside the site**, and a security policy in `_headers` enforces that (see [Security](#security)).

```
.
├── index.html            Home
├── about.html            About us
├── booking.html          Booking request form, UPI payment, refund policy
├── contact.html          Contact form and details
├── 404.html              Page not found
├── preview.html          Device preview tool
├── favicon.ico           Browser-tab icon (AP monogram)
├── robots.txt            Tells search engines and AI crawlers they may read the site
├── sitemap.xml           List of pages for search engines
├── llms.txt              Plain-text summary of the business for AI assistants
├── site.webmanifest      App name and icons (for "Add to Home screen")
├── sw.js                 Service worker: saved copy of the site, for instant repeat visits and offline (see "Caching and offline")
├── 9ee9e31962946c13c7e1b3c5848a1f03.txt  IndexNow key (lets Bing know the site is ours when we announce updates)
├── .well-known/
│   └── security.txt      Where to report a security problem (renew "Expires" every year)
├── _headers              Security headers and caching rules (Cloudflare; Netlify reads it too)
├── _redirects            Address rules: / shows index.html, /about leads to about.html…
├── wrangler.jsonc        Cloudflare settings: run the build script, publish dist/, 404 page
├── build-cloudflare.mjs  Copies the website's files into dist/ for Cloudflare (nothing else)
├── netlify.toml          Settings for the old host, Netlify
├── .gitignore            Keeps the temporary node_modules folder out of git
└── assets/
    ├── theme.css         Colours, fonts, radius, shadows (tweakcn format)
    ├── tailwind.css      Source of the styles: theme mapping, animations, form fields
    ├── styles.css        Built styles used by every page (made from tailwind.css — don't edit by hand)
    ├── fonts.css         Font definitions (included in styles.css; also used by preview.html)
    ├── fonts/            Font files (woff2) — see "Fonts" below
    ├── head.js           Sets light/dark mode before the page paints
    ├── site.js           Header, mobile menu, scroll reveals, counters, Book buttons, copy button, photo galleries, forms (incl. the screenshot upload)
    ├── hero-fx.js        Animated dash ring and glow in the page headers
    ├── preview.js        Device preview tool
    ├── preview.css       Device preview tool styles
    ├── logo.png          ADVENTURE PARK wordmark (header and footer)
    ├── icon-192.png      App icon
    ├── apple-touch-icon.png  Home-screen icon for iPhone/iPad
    ├── og-image.png      Link-preview picture (WhatsApp, Facebook, Google…), 1200×630
    ├── photos/           Activity photos (WebP): rafting-1…4 and bungee-1…2 at 640, 960 and 1440 px wide, zipline-1 and zipline-2
    ├── upi-qr.svg        UPI payment QR code shown on the Booking page (sharp at any size)
    └── upi-qr.png        Same QR code as an image, for the "Download QR code" button
```

---

## Business details used on the site

Every fact the site states, in one place — keep these up to date.

| Detail | Value |
|---|---|
| Founder | Jagat Singh Bhandari (JSB) |
| Experience / people served / running since | 25+ years · 1 lakh+ people · since 2006 |
| Office | Near Shiv Mandir, Badrinath Highway, Shivpuri, Uttarakhand · [Google Maps](https://maps.app.goo.gl/CKrMGT2h8CRNrcmi6) |
| Phone / WhatsApp | +91 97623 88871, +91 87555 42743 (WhatsApp) |
| Email / Instagram | adventurepark661@gmail.com · [@adventure_park771](https://www.instagram.com/adventure_park771/) |
| Rafting (per person) | 12 km Marine Drive → Shivpuri, 1–1.5 hrs, ₹520 · 16 km Shivpuri → Nim Beach, 1.5–2 hrs, ₹720 · 26 km Marine Drive → Nim Beach, 2–2.5 hrs, ₹1,200 · 36 km Kaudiyala → Nim Beach, 2.5–3 hrs, ₹2,400 · peak season (March–June) weekends and crowded days +15–20% |
| Bungee jumping | 109 m / 360 ft, ₹4,000 per person incl. DSLR video and jump certificate |
| Zip line | ₹1,800 students, ₹2,000 adults (per person) |
| Luxury camps & cottages | 200 m up from the river, 100 m up from the national highway · quad/triple ₹1,500–₹1,800, double ₹1,800–₹2,200 per person/night · children 6–11 at 50% · includes three meals, swimming pool, DJ party till 10 pm, attached washroom, fan, charging points · non-AC camps have an air cooler, AC cottages have AC |
| Hotel rooms (room only) | Non-AC ₹1,200, AC ₹1,600 per room/night · busy days ₹1,600–₹1,800 / ₹2,000–₹2,200 |
| Booking & payment | Visitors pay a **50% advance** by UPI, then send the booking form with the **payment screenshot attached** (required); the team checks the payment and confirms; the remaining 50% is paid at Adventure Park · M/S ADVENTURE PARK · UPI ID `8755542743@ybl` (PhonePe) · QR code on the Booking page · **Refunds:** only if cancelled within 36 hours of booking (advance refunded); after that the advance is non-refundable |
| Meals (camps & cottages) | Breakfast 8:30–10:00 AM · buffet lunch 1:30–3:00 PM (pure veg) · evening snacks 6:00–7:30 PM · buffet dinner 8:30–10:30 PM (veg & non-veg) |

---

## Editing the site

All content is in the HTML files — open one in any text editor, change the words, save.

**The header, footer, mobile menu and WhatsApp button are repeated in every page.** If you change one of them (for example a phone number), make the same change in `index.html`, `about.html`, `booking.html`, `contact.html` and `404.html`.

**Search and AI copies of the facts.** Prices, phone numbers, the address and the FAQ also appear in the structured data at the top of each page and in `llms.txt`. When a fact changes on the page, change it there too — see [Keeping it accurate](#keeping-it-accurate).

### Headline numbers

The band under the Home hero shows **25+** years of experience, **1 lakh+** people served and **Since 2006 · Founded by JSB**. Edit them in the "NUMBERS" section of `index.html` (the About page has its own band in `about.html`). The `data-count` value is the number that counts up.

### Prices — where they appear

All prices are in Indian rupees (₹). Update them in these places in `index.html`:

| What | Where in `index.html` |
|---|---|
| Rafting stretches, durations and prices, peak-season note | **Activities** section, River rafting card |
| Bungee (₹4,000 incl. DSLR video and certificate), zip line (students/adults) | **Activities** section, Bungee and Zip line cards |
| Camps & cottages (per person / night, children 6–11 at 50%, what's included, non-AC vs AC) and hotel rooms (per room / night, busy-day prices, room only) | **Stay** section |
| Meal times and dishes | **Food** section (`id="food"`) |
| Peak-season and children/student prices in words | **FAQ** section ("Do prices change in peak season?", "Are there lower prices for children or students?") |
| Copies for search engines and AI assistants | the `<script type="application/ld+json">` block in the `<head>` of `index.html` (`"price"`, `"minPrice"`, `"maxPrice"`, `"priceRange"`), the description `<meta>` tags of `index.html` ("from ₹520"), and `llms.txt` |

The activity choices in the booking form are in `booking.html` (search for `name="activities"`). The four rafting trips there (12, 16, 26 and 36 km) show their route, duration and price — update them together with the rafting card on the home page.

### Contact details — where they appear

| Detail | Where |
|---|---|
| Phone numbers, email, address | `contact.html` (cards), footer and mobile menu in every page; the price question and refund policy in the payment section of `booking.html`; the refund FAQ in `index.html` |
| Google Maps location (https://maps.app.goo.gl/CKrMGT2h8CRNrcmi6) | "Open in Google Maps" on `contact.html`; the address in every footer and the mobile menu |
| WhatsApp number (+91 87555 42743) | Floating button and footer in every page, WhatsApp card in `contact.html` — links look like `https://wa.me/918755542743` |
| Instagram (@adventure_park771) | Footer and mobile menu in every page, Instagram card in `contact.html` |
| Phone number in form error messages | `PHONE` at the top of the forms section in `assets/site.js` |
| Form email address | the `action` of both forms (see [Forms](#forms-formsubmit)) |
| Copies for search engines and AI assistants | the `<script type="application/ld+json">` block in the `<head>` of `index.html`, `about.html`, `booking.html` and `contact.html`; the description `<meta>` tags of `contact.html`; `llms.txt` |

### Colours and fonts (tweakcn)

1. Design a theme at [tweakcn.com](https://tweakcn.com) and open **Code**.
2. Copy the `:root { … }` and `.dark { … }` blocks and paste them over the same blocks in `assets/theme.css`. No rebuild is needed: just change the `?v=` value after `theme.css` in every page's `<head>` so returning visitors get the new colours (see [Caching and offline](#caching-and-offline)).
3. If the theme uses different fonts, download them as `.woff2` files into `assets/fonts/` (for example from [Google Fonts](https://fonts.google.com) or [Fontsource](https://fontsource.org)), update `assets/fonts.css` and the two font `preload` lines in each page's `<head>`, then [rebuild the styles](#changing-the-design-rebuilding-the-styles).

The current theme: "life-jacket" orange for buttons, Ganga jade accents, river-navy text; dark mode is "the river at night".

### Fonts

Every page shows text in two fonts on its first screen: **Plus Jakarta Sans** (all body text and headings) and **Instrument Serif italic** (the accent words in the page titles). Both files are requested at the very top of each page (the two `<link rel="preload" …>` lines in the `<head>`), so the first screen shows the right fonts as soon as possible. This is what keeps the speed index low: when a font is only found later, inside the styles, the browser waits a whole extra round trip before it can draw text in it.

- `plus-jakarta-sans.woff2` (20 KB) holds English letters, accents, punctuation and the **₹ sign**, at weights 400–800. It's made from the official font file with [fontTools](https://github.com/fonttools/fonttools):
  ```bash
  pip install fonttools brotli
  fonttools varLib.instancer "PlusJakartaSans[wght].ttf" wght=400:800 -o pjs.ttf
  pyftsubset pjs.ttf --flavor=woff2 --output-file=plus-jakarta-sans.woff2 \
    --layout-features=calt,ccmp,dnom,frac,liga,locl,numr,pnum,tnum,kern,mark \
    --unicodes="U+0000-00FF,U+0102,U+0131,U+0152-0153,U+02BB-02BC,U+02C6,U+02DA,U+02DC,U+0300-0301,U+0303-0304,U+0308-0309,U+0323,U+0329,U+2000-206F,U+20AC,U+20B9,U+2122,U+2191,U+2193,U+2212,U+2215,U+FEFF,U+FFFD"
  ```
  (`PlusJakartaSans[wght].ttf` is in [Google's font repository](https://github.com/google/fonts/tree/main/ofl/plusjakartasans).) Before, ₹ lived in a second 22 KB file, so every page with prices downloaded two body-font files.
- `instrument-serif-italic-latin.woff2` (22 KB) is the Google Fonts file for English text.
- The `…-latin-ext.woff2` files cover rarer accented letters. A page downloads one only if its text uses such a letter, and none does today. The upright (not italic) Instrument Serif files are not used by any page.

**If you add a character that isn't in these files** (for example another currency sign), it shows in a fallback font. Add its code to `--unicodes` above and rebuild the file, then add the same code to the `unicode-range` of the "Plus Jakarta Sans normal – latin" block in `assets/fonts.css`.

### Payment (UPI QR code)

The **Pay by UPI** section is at the bottom of `booking.html` (`id="pay"`). The QR code was re-created from the owner's PhonePe QR with exactly the same payment details (`upi://pay?pa=8755542743@ybl&pn=M/S ADVENTURE PARK…`) as a standard dark-on-white code, which every UPI app can scan; it was checked with two independent QR readers, on screen at three sharpness levels, in light and dark mode.

How booking and payment work on the site: the visitor pays a **50% advance** by scanning the QR code (or by UPI ID), then sends the booking form with the **payment screenshot attached** — the form can't be sent without a screenshot (an image or PDF of up to 10 MB). The team checks the payment and confirms the booking; the remaining 50% is paid at Adventure Park. Visitors unsure of the exact price (peak season, busy days) are asked to call or WhatsApp before paying.

The 50% rule and the screenshot requirement appear in: the highlighted note, price reminder and three steps of the payment section, the four "How it works" steps, the payment-screenshot field of the booking form and the thank-you message (all in `booking.html`); steps 1–2 of "Plan your adventure" and the "How do I book and pay?" answer in `index.html`; and `llms.txt`. Change them together if the rule changes.

**Refund policy** — cancel within 36 hours of booking (from when the booking request is sent) for a refund of the advance; after 36 hours the advance is non-refundable. It appears in the "Refund policy" note of the payment section (`id="refund-policy"`, linked from every footer), under the payment-screenshot field and in the thank-you message (`booking.html`), in the FAQ "What is your refund policy?" (`index.html`) and in `llms.txt`. Change them together if the policy changes.

**To change the UPI account:** replace `assets/upi-qr.svg` and `assets/upi-qr.png` with QR codes for the new account (dark on white), and replace `8755542743@ybl` everywhere it appears: `booking.html` (the caption, the image description and the `data-copy` value of the "Copy UPI ID" button), the FAQ answer in `index.html`, and `llms.txt`.

The "Book" buttons link to `booking.html?activity=…` (`bungee`, `zipline`, `camps`, `hotel`, `other`); the matching box is ticked by `assets/site.js`. "Book rafting" opens the form without a preset, because the visitor picks one of the four rafting trips there.

### Changing the design (rebuilding the styles)

Pages use the ready-made `assets/styles.css`. It contains the styles for every Tailwind class used in the HTML and JavaScript files.

- **Changing words, prices, links or images** → no rebuild needed. Returning visitors first see their saved copy, then a "This page has been updated" notice with a Refresh button.
- **Changing colours in `theme.css`** → no rebuild needed; change the `?v=` value after `theme.css` in every page.
- **Adding or changing Tailwind classes** in the HTML (e.g. `text-xl`, `bg-primary`), or editing `assets/tailwind.css` → rebuild `styles.css`, otherwise the new classes have no effect.

To rebuild, install [Node.js](https://nodejs.org), open a terminal in this folder and run:

```bash
npm install --no-save tailwindcss@4.3.3 @tailwindcss/cli@4.3.3
npx tailwindcss -i assets/tailwind.css -o assets/styles.css --minify
```

Then change the `?v=` value after `styles.css` in every page (see [Caching and offline](#caching-and-offline)), and commit the updated `assets/styles.css` along with the HTML. (Add `--watch` to the second command to rebuild automatically while you edit.)

**Don't use `style="…"` attributes or `<style>` blocks in the pages** — the security policy blocks them. Use a Tailwind class instead (for example `[--d:150ms]` rather than `style="--d:150ms"`), then rebuild.

### Page labels (emoji)

Each page opens with a small label in a pill above the heading, with its own emoji:

| Page | Label |
|---|---|
| Home | 🧗 Safety-first adventures in Rishikesh |
| About | 🏔️ About us |
| Booking | 📅 Booking |
| Contact | 📞 Contact us |
| Page not found | 🗺️ Error 404 |
| "Get in touch" bands (Home, About) | 👋 Get in touch |

Change the emoji or text inside that pill at the top of each page's first section.

### Logo and icons

- `assets/logo.png` — the wordmark (transparent background; shown on a light plate in dark mode).
- `favicon.ico`, `assets/icon-192.png`, `assets/apple-touch-icon.png` — made from the AP monogram.
- `assets/og-image.png` — the 1200×630 picture shown when a link to the site is shared (logo, tagline, activities, "Since 2006").

Replace a file with one of the same name and size to update it.

### Photos

The photos are in `assets/photos/`, in the WebP format (about a third of the size of a JPG at the same quality). Each sits at the top of its activity card on the home page (`index.html`):

| Card | Photos | Files |
|---|---|---|
| River rafting | 4 of Adventure Park's own rafting photos | `rafting-1` … `rafting-4`, each at 640, 960 and 1440 px wide (phones download a smaller size, big screens the 1440 px one) |
| Zip line | 2 photos marked **"Representative photo"** — they were not taken at Adventure Park | `zipline-1-640.webp`, `zipline-2-629.webp` |
| Bungee jumping | 2 jump photos from Splash Bungy, used with their permission (their logo was removed with that permission) | `bungee-1`, `bungee-2`, each at 640, 960 and 1440 px wide |

All the photos are cropped to a wide 16:9 shape and turned the right way up. Camera details, including the GPS location stored by the phone, were removed. Only the first photo of each card loads with the page. The second loads as the visitor scrolls near the card, and the rest as soon as they start browsing, so the home page stays fast on phones.

**To replace a photo:** make a landscape picture in the 16:9 shape, save it as WebP at the same width and with the same file name, and upload it over the old one. On GitHub, open `assets/photos`, choose **Add file → Upload files**, and commit. Cloudflare publishes the change within a minute or two. People who visited before may keep seeing the old photo for a day or two (browsers keep photos that long); to show it to everyone at once, give the new photo a new file name instead and update the names in `index.html`. [Squoosh](https://squoosh.app) can resize, crop and convert a photo to WebP in the browser, and removes the location data. Also update the photo's description (the `alt="…"` text in `index.html`) so it matches the new picture: search engines, AI assistants and screen readers use it.

**To add a photo to a card:** in `index.html`, copy one of the card's later photos (an `<img>` line whose `src` starts with `data:image/svg+xml`), change the file names in `data-src` and `data-srcset` and the `alt` text, add one more dot (a `<span data-gallery-dot …></span>`), and update the number in `aria-label="… photos, N pictures"`. To give another card photos (for example the camps), copy the bungee card's `<div data-gallery …>` block into it and change the photos.

**When the zip line photos are your own:** delete the line `<span …>Representative photo</span>` from that card.

**Photo rights:** only put up photos Adventure Park owns or has written permission to use. Guests can be recognised in the rafting photos, so check that they are happy to appear on the website. Photos taken at another company's site (like the bungee photos from Splash Bungy) need that company's permission, and so does removing their logo or watermark. Keep their written OK (an email or WhatsApp message is enough) in case anyone asks.

### Animation

Animation is kept subtle and fast:

- **Page headers:** a ring of short dashes drifts slowly on its own and follows the mouse on hover; the background glow follows it. Tune the numbers at the top of `assets/hero-fx.js` (`RADIUS`, `IDLE_LEVEL`, …).
- **The 🧗 on the Home label** climbs on a gentle loop and climbs faster while hovered.
- **Everything else** (founder portrait, safety checklist, "Get in touch" bands and their 👋, button shine) moves only on hover.
- The header animation pauses when it's off screen, runs at half speed on phones, and costs about 0.2 ms per frame.
- **Visitors who turn on "reduce motion"** in their device settings see a completely still site.

---

## Forms (FormSubmit)

Both forms send their details by email through FormSubmit to **adventurepark661@gmail.com**.

| Form | Email subject | Fields |
|---|---|---|
| Contact (`contact.html`) | New contact form message – Adventure Park website | name, age, gender, phone, email |
| Booking (`booking.html`) | New booking request – Adventure Park website | name, phone, email, group size, experience, preferred date, contact by, activities (incl. the rafting trip), health and notes, and the **payment screenshot as an email attachment** |

**One-time activation (after the site is live):**

1. Submit the contact form once on the live site, and the booking form once (with any image as the screenshot).
2. FormSubmit emails an **"Activate Form"** link to adventurepark661@gmail.com — click it. (Check spam. If an activation email arrives for each form, click both.) Until then, the booking form shows FormSubmit's own "activate" page instead of the site's thank-you message.
3. From then on, every submission arrives as a neat table in the inbox.

Good to know:

- **Contact form:** visitors stay on the page and see a "Thanks" message; if sending fails they're asked to try again or call.
- **Booking form:** because it carries the payment screenshot, it is sent as a normal file upload (FormSubmit only emails attachments from such uploads). FormSubmit then sends the visitor back to the Booking page, which shows the thank-you message. Files can be up to 10 MB in total; FormSubmit emails them and does not keep a copy, so keep the email.
- A hidden `_honey` field catches spam bots; FormSubmit's captcha page is switched off for a smoother experience.
- Forms **don't send** from a file opened directly on your computer or from the claude.ai preview — they need the live website.
- **To change the receiving email**, replace `adventurepark661@gmail.com` in the `action="https://formsubmit.co/…"` of both forms, then activate again. After activation FormSubmit may also send a private code that can replace the email address in the form.

---

## Search engines and AI assistants (SEO, GEO, AIO)

The site is set up so that Google and Bing (SEO), and AI assistants and answer engines such as ChatGPT, Claude, Perplexity, Gemini and Copilot (GEO / AIO — generative-engine and AI optimisation), can find it, read it and describe the park correctly. Nothing here changes how the pages look.

### What's in place

| What | Where | What it does |
|---|---|---|
| Page titles and descriptions | `<title>` and `<meta name="description">` in each page | The headline and snippet shown in search results, written around what people search for (rafting, bungee jumping, camps in Rishikesh) |
| Canonical address | `<link rel="canonical">` in each page | Tells search engines the one official address of each page |
| Robots tags | `<meta name="robots">` in each page | Lets search engines index the four main pages and show large image previews and full snippets; the 404 page and the device preview are kept out of search |
| Link previews | `og:` and `twitter:` `<meta>` tags, `assets/og-image.png` | The title, text and picture shown when a link is shared on WhatsApp, Instagram, Facebook, X or LinkedIn |
| Location tags | `geo.region` (IN-UT) and `geo.placename` `<meta>` tags; page language `en-IN` | Ties the site to Shivpuri, Rishikesh, Uttarakhand, India |
| Structured data | `<script type="application/ld+json">` in the `<head>` of each main page | Machine-readable facts in the [schema.org](https://schema.org) format (below) |
| `robots.txt` | site root | Allows every crawler, and names the main AI crawlers one by one so they know they're welcome (below). Points to the sitemap and `llms.txt` |
| `sitemap.xml` | site root | Lists the four main pages with their last-updated date |
| `llms.txt` | site root | A plain-text fact sheet in the [llms.txt](https://llmstxt.org) format: the business, every price, the booking, payment and refund rules, the stays, the food menu, safety and the FAQ, so an AI assistant can quote them accurately |
| `site.webmanifest` | site root | App name, colours and icons |

### Structured data (schema.org JSON-LD)

| Page | What it describes |
|---|---|
| Home | **The business** (`SportsActivityLocation` + `TouristAttraction`): name, tagline, founder, founded in 2006, address, map link, phones, WhatsApp, email, Instagram, price range ₹520–₹4,000, accepted payment (UPI), pictures (two rafting photos, the link-preview picture and the logo) and **a catalogue of all 11 prices** (4 rafting stretches, bungee, zip line ×2, camps ×2, hotel rooms ×2, each in INR). **The FAQ** (all 9 questions and answers, including booking, payment and refunds), **the camps & cottages** (`Campground` with every amenity) and **the food menu** (4 meals and their dishes, vegetarian dishes marked) |
| About | The business, the About page and **the founder**, Jagat Singh Bhandari (JSB) |
| Booking | The business, the Booking page and a "send a booking request" action |
| Contact | The business and the Contact page |

About, Booking and Contact also have a breadcrumb trail (Home › page). The 404 page has no structured data.

### Crawlers allowed in `robots.txt`

All crawlers are allowed (`User-agent: *`). These are also named one by one:

| Company | Crawlers |
|---|---|
| OpenAI (ChatGPT) | **OAI-SearchBot** (ChatGPT search results), **ChatGPT-User** (pages ChatGPT opens while answering), **GPTBot** (training) |
| Anthropic (Claude) | ClaudeBot, Claude-SearchBot, Claude-User, anthropic-ai |
| Perplexity | PerplexityBot, Perplexity-User |
| Google (Search, Gemini, AI Overviews) | Googlebot, Google-Extended, GoogleOther, Google-CloudVertexBot |
| Microsoft (Bing, Copilot) | Bingbot |
| Apple (Siri, Apple Intelligence) | Applebot, Applebot-Extended |
| Meta (Meta AI) | meta-externalagent, meta-externalfetcher, FacebookBot |
| Others | DuckDuckBot, DuckAssistBot, Amazonbot, CCBot (Common Crawl), cohere-ai, MistralAI-User, YouBot, Bytespider, PetalBot, YandexBot, Diffbot |

Only `preview.html` (the device-preview tool) is blocked. To block one of the crawlers above instead, move its `User-agent:` line into a new group of its own with `Disallow: /`.

`robots.txt` also carries a **content signal** ([contentsignals.org](https://contentsignals.org)): `search=yes, ai-input=yes, ai-train=yes` — the site may be shown in search results, used to answer questions in AI assistants and AI search, and used for AI training. To keep the site out of AI training but still in AI answers, change it to `ai-train=no` and move `GPTBot`, `Google-Extended`, `Applebot-Extended` and `CCBot` into a group with `Disallow: /` (ChatGPT search uses OAI-SearchBot, not GPTBot).

**Being listed in ChatGPT search:** OpenAI's OAI-SearchBot is allowed, and ChatGPT search also draws on Bing's index — so the Bing steps below (Webmaster Tools and IndexNow) matter as much as Google's.

### Keeping it accurate

AI assistants and search engines trust a business more when the same facts appear everywhere. When something changes:

- **A price or a fact on the page** → update the same value in the structured data of `index.html` (search the `<head>` for the old value — prices are written without commas, e.g. `"price": "4000"`) and in `llms.txt`.
- **Anything on the site** → update `<lastmod>` in `sitemap.xml` (and `"dateModified"` in the structured data) to the date of the change.
- **The FAQ** → the questions and answers are copied into the structured data of `index.html` and into `llms.txt`.
- **Moving to your own domain** (e.g. `adventurepark.in`) → find-and-replace `https://adventureprk.netlify.app` with the new address in every `.html` file, `robots.txt`, `sitemap.xml` and `llms.txt`.
- **Name, address and phone** should be written exactly the same here, on Google Business Profile, Instagram and any listing sites.
- To check the structured data, paste a page address into Google's [Rich Results Test](https://search.google.com/test/rich-results) or the [Schema Markup Validator](https://validator.schema.org).

### After launch

These steps are done outside the website and make the biggest difference to being found:

1. **[Google Business Profile](https://business.google.com)** — create or claim the listing for Adventure Park with the same name, address, phones and website. This drives Google Maps, "near me" searches and many AI answers about local businesses. Add photos and opening hours, and ask happy guests for Google reviews.
2. **[Google Search Console](https://search.google.com/search-console)** — add the site, then submit `sitemap.xml` under **Sitemaps**.
3. **[Bing Webmaster Tools](https://www.bing.com/webmasters)** — add the site (it can import from Search Console) and submit the sitemap. Bing's index also feeds ChatGPT search, Copilot and other AI search tools.
4. **IndexNow** — tell Bing (and Yandex, Seznam, Naver) straight away about new or changed pages. Open this address in a browser after the first deploy, and again after any big update:
   `https://www.bing.com/indexnow?url=https://adventureprk.netlify.app/&key=9ee9e31962946c13c7e1b3c5848a1f03`
   (Replace the page address to announce another page. The key file `9ee9e31962946c13c7e1b3c5848a1f03.txt` proves the site is yours — keep it.)
5. Optional: send the office's exact map coordinates (latitude, longitude) and opening hours to be added to the structured data.

No website can guarantee a place in AI answers. These steps make sure that when AI assistants do look, they can read every page and find correct, consistent facts.

---

## Previewing

**On your computer** — run a small local web server from this folder, then open the address it prints:

```bash
python3 -m http.server 8000
# then open http://localhost:8000
```

To see the site exactly as Cloudflare serves it (same headers, addresses and 404 page), run `npx wrangler dev` instead and open http://localhost:8787.

Opening the HTML files directly also works for looking at the design, but the forms won't send.

**On different devices** — open `preview.html` (e.g. http://localhost:8000/preview.html, or `/preview.html` on the live site). It shows the site inside phone, tablet and desktop frames, side by side, with page, light/dark and rotate controls.

## Caching and offline

Many visitors arrive on slow mobile data. The first visit has to come over the network, but after that the site doesn't need to:

| What | How long browsers keep it | Why it's safe |
|---|---|---|
| Styles and scripts (`assets/*.css`, `assets/*.js`) | A year | Each page links them with a version, e.g. `assets/styles.css?v=330df259c6`. A changed file gets a new `?v=`, so browsers fetch it straight away |
| Fonts (`assets/fonts/`) | A year | They never change under the same name |
| Photos, logo and icons | A day, then shown at once while a newer copy is checked for (up to a week) | A replaced photo shows up within a day or two |
| Pages (`*.html`) | Always checked with the website | — |

**The offline copy (`sw.js`, a "service worker").** Once a page has finished loading, the browser installs `sw.js`. It saves the four pages, the styles, scripts, fonts and logo, and the photos the visitor has seen, so that:

- **coming back, and moving between pages, is instant** (on slow 3G, 0.3 s instead of about 2.6 s, with nothing downloaded);
- **the site still opens with no signal**, including the Booking page and its "Book" links. Only sending a form needs a connection;
- **visitors still get updates.** The saved page is shown at once and checked against the website in the background. If it has changed, a small notice says "This page has been updated" with a Refresh button. A saved page more than a day old isn't shown straight away: the website is asked first, and the saved copy is used only if it doesn't answer within 4 seconds;
- **data saver is respected.** Visitors who turned on "data saver" only keep the pages they actually open.

`sw.js` only handles this site's own pages and files. The booking and contact forms, FormSubmit, WhatsApp, Maps and Instagram all go straight to the network, and nothing a visitor types is ever saved.

**When you change something:**

- **Words, prices, links in a page:** nothing extra to do.
- **A style or script file** (`assets/theme.css`, `styles.css`, `site.js`, `head.js`, `hero-fx.js`): change the `?v=` value after its name in **every** page (any new value, the same in each page, e.g. `?v=2026-10-20`). Otherwise returning visitors keep the old file.
- **`sw.js` itself:** change the `VERSION` value at the top so browsers install the new one. The list of files under it uses the same `?v=` values as the pages; keep them in step.

**To switch the offline copy off** (if it ever causes trouble): replace everything in `sw.js` with the lines below and publish. Each visitor's browser then deletes its saved copy and removes the service worker on their next visit. Then delete the "Saved copy for slow connections" block from `assets/site.js`.

```js
self.addEventListener('install', () => self.skipWaiting());
self.addEventListener('activate', (event) => event.waitUntil((async () => {
  for (const name of await caches.keys()) await caches.delete(name);
  await self.registration.unregister();
})()));
```

---

## Security

The site has **no server, database, login or payment of its own**. The "backend" is Cloudflare (hosting) and FormSubmit (emails the two forms), so there is very little to attack: no passwords to steal, no database to break into, no code running on a server.

### Headers (`_headers`)

| Header | What it does |
|---|---|
| `Content-Security-Policy` | Only the site's own scripts, styles, fonts and images may load — no inline scripts or styles; forms may only send to FormSubmit. Blocks injected scripts and most cross-site attacks |
| `Strict-Transport-Security` | Browsers always use HTTPS for the site |
| `X-Frame-Options`, `frame-ancestors` | Other websites can't show these pages inside a frame (stops click-jacking) |
| `Cross-Origin-Opener-Policy` | Tabs opened from the site (WhatsApp, Instagram, Maps) can't reach back into it |
| `X-Content-Type-Options` | Browsers don't guess file types |
| `Referrer-Policy` | Other sites only see the site's address, not the full page address, when a visitor follows a link |
| `Permissions-Policy` | Camera, microphone, location, payment and USB access are switched off |

**If you add an outside service later** (analytics, an embedded map or video, a chat widget), add its address to `Content-Security-Policy` in `_headers` — otherwise the browser will block it.

### Other protections

- **Project files are never published:** Cloudflare only receives the website's own files (`build-cloudflare.mjs` copies them into `dist/`), so `README.md`, the settings files, `.git/` and `assets/tailwind.css` answer "not found" on the live site.
- **Forms:** a hidden spam trap (`_honey`), length limits on every field, and validation before sending; the payment screenshot must be an image or PDF of up to 10 MB. What visitors type is only ever shown as text, never run as code.
- **No cookies and no tracking.** The browser stores only the light/dark choice and the saved copy of the site's own pages and files (see [Caching and offline](#caching-and-offline)).
- **The service worker (`sw.js`)** only answers requests for this site's own pages and files. Forms, FormSubmit and other websites always go straight to the network.
- **No secrets in the code:** every commit was scanned for passwords, API keys and tokens — none.
- **`/.well-known/security.txt`** tells security researchers how to report a problem (email and phone). It expires each year — update the `Expires` date by then.
- **Recommended after activating FormSubmit:** FormSubmit emails you a private random address for the form. Replace `adventurepark661@gmail.com` in the two form `action`s with it, so spam bots can't harvest the email from the form (the email stays visible on the Contact page by choice).
- **Privacy:** the forms collect name, age, gender, phone, email and (for bookings) a payment screenshot, and send them through FormSubmit to your inbox. India's Digital Personal Data Protection Act expects you to tell people what you collect and why — consider adding a short privacy note under the forms or a privacy page.

---

## Test results

Tested on a local server that behaves like Netlify (same headers, Brotli compression and 404 handling), with Lighthouse's standard slow-phone and desktop settings. Last run after adding caching and the offline copy (Lighthouse figures: three runs per page, the middle result is shown).

**After the move to Cloudflare** (on Cloudflare's own local server, `wrangler dev`):
- **Headers and addresses:** 30 addresses give the same status and the same security, caching and file-type headers as the Netlify setup.
- **Browser tests:** all 82 pass.
- **Offline copy:** 16 of its 19 checks pass. The 3 "website changed" checks can't run on the local server, because it sends no `Date` header and keeps old file tags after an edit. They need checking on the live site.

| Check | Result |
|---|---|
| **Lighthouse — mobile** (performance / accessibility / best practices / SEO) | 100 / 100 / 100 / 100 on all four pages (performance was 78–84 before the styles were pre-built) |
| **Lighthouse — desktop** | 100 / 100 / 100 / 100 on all four pages |
| **Speed index** (how quickly the first screen looks complete) | Slow phone: Home 1.17 s (was 1.51 s), Booking 0.97 s (was 1.37 s), About and Contact 1.00 s (were 1.0–1.4 s, varying from run to run). Desktop: 0.30–0.41 s (was 0.37–0.45 s). The gain comes from fetching both fonts first thing and from the one-file body font with ₹ (see [Fonts](#fonts)); every page was checked pixel by pixel and looks exactly the same |
| Speed on a slow phone | First text after 1.0–1.1 s (was 1.0–1.5 s before the font speed-up, 2.6–3.0 s before the styles were pre-built), largest content after 1.5–1.8 s (was 3.0–3.3 s), no layout shift, no blocking time |
| **Slow mobile data** (3G: 300 ms delay, 0.7 Mbps, slow phone) | First visit: first text after about 3.2 s. **Coming back, or opening another page: 0.3 s** (was 2.6 s), with nothing downloaded (before, all 13 files were re-checked with the website, each costing a slow round trip). After the first visit, pages open offline too |
| Offline copy (service worker) | 19 automated checks pass: the pages and files are saved after the first visit; repeat visits come entirely from the saved copy; every page opens offline with its styles, fonts and logo; "Book" links work offline; both forms still reach FormSubmit; a changed page shows the update notice (without covering the WhatsApp button) and Refresh shows the new version; day-old copies give way to the website; a new site version replaces the old saved copy; no script errors |
| Page weight | Home 293 KB on a phone in 15 requests, with the first rafting photo; 508 KB on a desktop, where the first bungee and zip line photos and the second rafting photo are also on screen. The other photos load only when the visitor browses the galleries; loading them all up front made the phone page 561 KB. Booking 168 KB, About and Contact 142 KB. Logo 126 KB → 25 KB with no visible change |
| HTML and CSS — W3C validator | No errors or warnings on any page |
| Accessibility — axe-core (WCAG 2.2 AA), light and dark, phone and desktop, menu open | No issues, apart from a report on the faded "01 02 03" step numbers: they are decoration (hidden from screen readers), which WCAG exempts from contrast rules |
| Structured data — checked against the schema.org vocabulary | No errors |
| Links | Every internal link and `#section` link works; phone, email, WhatsApp, Instagram and Maps links are correct |
| Crawlers | `robots.txt` lets every search engine and AI crawler read every page except the preview tool; all key facts (prices, phones, address, founder) are in the page HTML, so crawlers that don't run JavaScript see them too |
| Forms, menu, dark mode, keyboard, 404, reduced motion, booking, payment and photos | 82 automated checks pass (forms tested with a simulated FormSubmit), under the security policy — including every Book button ticking the right activity, all four rafting trips, the booking form refusing to send without a payment screenshot (or with a non-image or over-10 MB file), the screenshot arriving as an attachment and the visitor returning to the thank-you message, the UPI QR code decoding to the right account, "Copy UPI ID" copying exactly `8755542743@ybl`, the 50% advance and 36-hour refund rules shown everywhere, and all three photo galleries: every photo loads with its description, the arrows and swiping step through and wrap around, the dots follow, later photos wait until needed, and without JavaScript each card still shows its first photo |
| **Security — OWASP ZAP** (crawl + active attack scan) | No high-risk findings. Fixed: inline styles allowed by the policy, project files reachable. Remaining reports don't apply: "anti-CSRF tokens" (no logins or sessions to protect), "HTTP to HTTPS form post" and "server version" (only on the plain-HTTP test server; the live site is HTTPS on Netlify), "suspicious comments" (ordinary code comments containing the word "from") |
| **Security — attack tests** | 41 checks pass: script injection through every form field (including with an uploaded screenshot) and the page address, with normal and hostile form-service replies; framing by another website; tab-hijacking through outside links; cookies/storage |
| **Security — code and history** | No unsafe HTML insertion in the site's JavaScript; no passwords, keys or tokens in any commit. The Tailwind build tool has a reported denial-of-service issue in a file-watching library it uses; it only runs on a computer when rebuilding the styles and is never part of the website |
| **Lighthouse security audits** | Pass: policy effective against script injection, HTTPS enforced, window isolation, click-jacking protection |
| **AI and search crawlers** | 30 crawlers named and allowed (OpenAI OAI-SearchBot, ChatGPT-User and GPTBot included) with an explicit content signal; every page, `llms.txt`, the sitemap and images can be fetched; only the preview tool is blocked |
| Layout | Nothing overflows sideways at 320, 360, 390, 768, 1024 and 1440 px wide |
| Look | After the speed and security changes, every page was compared pixel by pixel with the version before them (phone and desktop, light and dark): identical, apart from 1 pixel of the compressed logo and the new words "in 2006 … (JSB)" on the About page. New features (booking, payment, refunds) were checked visually on phone and desktop in both modes |

**Still to check on the live site:** Safari on iPhone and Firefox (only Chrome was available here), the real FormSubmit service (activate both forms and send a test booking with a screenshot), a ₹1 test payment by scanning the QR code, and live-network speed. On the Cloudflare site, also check that the "This page has been updated. Refresh" notice appears after a change goes live: Cloudflare's local test server can't show it. Run the live address through [PageSpeed Insights](https://pagespeed.web.dev), [Security Headers](https://securityheaders.com), [Mozilla Observatory](https://developer.mozilla.org/en-US/observatory), [SSL Labs](https://www.ssllabs.com/ssltest/) and the [Rich Results Test](https://search.google.com/test/rich-results), and try the site on an iPhone and an Android phone.

---

## Publishing on Cloudflare

The site is published by **Cloudflare** as a Worker that only serves files (free plan):

- **`wrangler.jsonc`** sets this up. Before each deploy Cloudflare runs `node build-cloudflare.mjs`, then publishes the `dist/` folder.
- **`_headers`** adds the security headers and caching rules.
- **`_redirects`** shows `index.html` at `/` and sends short addresses such as `/about` to `about.html`.
- **Addresses:** pages keep their `about.html`-style addresses, exactly as every link uses them, with no extra redirect.
- **Unknown addresses** show `404.html` with a "not found" status.

**Setting it up (once):**
1. Sign in at [dash.cloudflare.com](https://dash.cloudflare.com) (a free account is enough).
2. Go to **Workers & Pages → Create application → Import a repository**.
3. Connect GitHub and choose this repository (`gautambhandari771-droid/Web`).
4. Set the **project name** to `adventure-park`. It must match `name` in `wrangler.jsonc`.
5. Leave the build settings as they are: the deploy command is `npx wrangler deploy`, and the build step comes from `wrangler.jsonc`.
6. Click **Deploy**. After a minute the site is live at `https://adventure-park.<your-subdomain>.workers.dev`.

**After that:**
- Every change merged into `main` goes live within a minute or two.
- To undo a change, open the Worker's **Deployments** page and roll back to an earlier version.
- If a change doesn't go live, open the Worker's **Deployments** page (or **Builds** for the build log) and read the message there.

**When the site address changes** (the first Cloudflare address, or your own domain later): find-and-replace the old address with the new one in every `.html` file, `sitemap.xml`, `robots.txt`, `llms.txt` and `.well-known/security.txt`, and in the IndexNow link above (see [Keeping it accurate](#keeping-it-accurate)).

**Own domain later:** buy a domain (for example through Cloudflare: **Domain Registration**), then add it to the Worker under **Settings → Domains & Routes → Add → Custom domain**. Then change the address in the pages and crawler files as above.

**The old host, Netlify:** the Netlify project `adventureprk` still shows an older version at https://adventureprk.netlify.app. Once the Cloudflare site is live, delete the Netlify projects (`adventureprk`, `advenpark` and `adventurepark-rishikesh`) under **Project configuration → General → Danger zone → Delete project**, so nobody finds the old version. `netlify.toml` stays only in case you ever go back.

---

## Launch checklist

- [x] Real activities and prices (rafting with durations, bungee, zip line), stay options (camps & cottages, hotel rooms) and the food menu.
- [x] Instagram link in the footer, mobile menu and Contact page.
- [ ] Confirm whether AC cottages cost the same per person as non-AC camps (the site shows one per-person range for both).
- [ ] Check every safety statement matches what the team actually does.
- [ ] Cloudflare project `adventure-park` set up (see [Publishing on Cloudflare](#publishing-on-cloudflare)), then the site address changed in the pages and crawler files.
- [ ] Delete the Netlify projects `adventureprk`, `advenpark` and `adventurepark-rishikesh` once the Cloudflare site is live.
- [ ] Submit both forms once on the live site (the booking form with any image as the screenshot) and click FormSubmit's activation emails.
- [x] Book buttons on every activity and stay; four rafting trips in the booking form.
- [x] UPI payment QR code (M/S ADVENTURE PARK, `8755542743@ybl`), 50% advance rule, required payment screenshot, 36-hour refund policy.
- [ ] Make a ₹1 test payment by scanning the QR code on the live Booking page, and check the name shows as M/S ADVENTURE PARK.
- [x] Google Maps location link from the owner, on the Contact page, footer and mobile menu.
- [ ] Test on a phone: menu, WhatsApp button, tap-to-call, forms.
- [x] Search and AI setup: titles, descriptions, link previews, structured data, `robots.txt`, `sitemap.xml`, `llms.txt`.
- [ ] Create or claim the Google Business Profile with the same name, address and phones.
- [ ] Add the site to Google Search Console and Bing Webmaster Tools and submit `sitemap.xml`.
- [ ] Share a link on WhatsApp to check the preview picture shows.
- [x] Pre-launch tests: speed, accessibility, HTML, structured data, links, forms, security headers (see [Test results](#test-results)).
- [ ] After launch: run [PageSpeed Insights](https://pagespeed.web.dev), [Security Headers](https://securityheaders.com), [Mozilla Observatory](https://developer.mozilla.org/en-US/observatory), [SSL Labs](https://www.ssllabs.com/ssltest/) and the [Rich Results Test](https://search.google.com/test/rich-results) on the live address.
- [x] Security tests (OWASP ZAP, attack tests, secrets scan) and hardening (strict security policy, HTTPS-only, hidden project files, form limits, `security.txt`).
- [ ] After launch: announce the site to Bing with [IndexNow](#after-launch) (helps ChatGPT search and Copilot).
- [ ] After activating FormSubmit: replace the email in the form `action`s with FormSubmit's private address.
- [ ] Consider a short privacy note for the forms.
- [ ] Before October 2027: update `Expires` in `.well-known/security.txt`.

---

## Credits

- [Tailwind CSS](https://tailwindcss.com) (MIT) for styling.
- Theme format from [tweakcn](https://tweakcn.com) / shadcn/ui.
- WhatsApp and Instagram icons from [Simple Icons](https://simpleicons.org) (CC0); other icons drawn in the style of [Lucide](https://lucide.dev).
- Header animation inspired by [antigravity.google](https://antigravity.google).
- Fonts: Plus Jakarta Sans and Instrument Serif (SIL Open Font License), from Google Fonts, stored on the site.
