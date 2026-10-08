# Adventure Park — website

**Where Rishikesh gets wild.**
The website for Adventure Park, an adventure-activities brand in Rishikesh, Uttarakhand, running since 2006 and founded by Jagat Singh Bhandari (JSB — 25+ years of experience, more than 5 lakh people served). The site presents the activities, stays and prices, puts safety first, and lets visitors send a booking request or get in touch.

> **Status:** not live yet — launch is on hold until all business details are final. The Netlify site is ready at **https://adventurepark-rishikesh.netlify.app** (project `adventurepark-rishikesh`, public, no login needed) and goes live with the first deploy — see [Deploying to Netlify](#deploying-to-netlify). The work is on the branch `claude/adventure-park-tailwind-site-n4xwsv`.

---

## Pages

| Page | File | What it's for |
|---|---|---|
| Home | `index.html` | Hero with the tagline, activities and prices, stay (camps & cottages, hotel rooms), food menu, safety checklist, how it works, FAQs |
| About us | `about.html` | Founder story, numbers, values and safety promise |
| Booking | `booking.html` | Booking request form (group, experience, activities, dates, health notes) |
| Contact us | `contact.html` | Contact form (name, age, gender, phone, email), office address, phone, WhatsApp, email |
| Page not found | `404.html` | Shown for any address that doesn't exist |
| Device preview | `preview.html` | Tool for checking the site on mobile, tablet and desktop (hidden from search engines) |

## What visitors can do

- **See activities and prices** — rafting (12, 16, 26 and 36 km, 1 to 3 hours), bungee jumping (109 m), zip line, luxury camps & cottages (200 m up from the river, 100 m up from the national highway) and hotel rooms, all in ₹.
- **See the food menu** — breakfast, buffet lunch (pure veg), evening snacks and buffet dinner (veg & non-veg), included with camp and cottage stays.
- **Send a booking request** or **a contact message** — both forms email the details to the park (see [Forms](#forms-formsubmit)).
- **Chat on WhatsApp** with the floating button on every page (+91 87555 42743).
- **Call or email** — numbers and email are on the Contact page and in every footer.
- **Find the office** — Near Shiv Mandir, Badrinath Highway, Shivpuri, Uttarakhand (with a Google Maps link).
- **Follow on Instagram** — [@adventure_park771](https://www.instagram.com/adventure_park771/), linked in the footer, the mobile menu and the Contact page.
- **Switch light/dark mode** — follows the visitor's device setting by default.

---

## How it's built

A static website: plain HTML, CSS and a little JavaScript. **There is no build step and nothing to install.**

- **Tailwind CSS v4** (browser build from jsDelivr) does the styling directly in the page.
- **Theme** in the [tweakcn](https://tweakcn.com) / shadcn format, so colours and fonts can be swapped in one file.
- **Fonts** from Google Fonts: Plus Jakarta Sans and Instrument Serif.
- **Forms** are delivered by [FormSubmit](https://formsubmit.co) — no server of our own.

```
.
├── index.html            Home
├── about.html            About us
├── booking.html          Booking request form
├── contact.html          Contact form and details
├── 404.html              Page not found
├── preview.html          Device preview tool
├── favicon.ico           Browser-tab icon (AP monogram)
├── netlify.toml          Netlify settings
└── assets/
    ├── theme.css         Colours, fonts, radius, shadows (tweakcn format)
    ├── head.js           Light/dark mode + shared Tailwind setup and animations
    ├── site.js           Header, mobile menu, scroll reveals, counters, forms
    ├── hero-fx.js        Animated dash ring and glow in the page headers
    ├── logo.png          ADVENTURE PARK wordmark (header and footer)
    ├── icon-192.png      App icon
    └── apple-touch-icon.png  Home-screen icon for iPhone/iPad
```

---

## Business details used on the site

Every fact the site states, in one place — check these before launch.

| Detail | Value |
|---|---|
| Founder | Jagat Singh Bhandari (JSB) |
| Experience / people served / running since | 25+ years · 5 lakh+ people · since 2006 |
| Office | Near Shiv Mandir, Badrinath Highway, Shivpuri, Uttarakhand |
| Phone / WhatsApp | +91 97623 88871, +91 87555 42743 (WhatsApp) |
| Email / Instagram | adventurepark661@gmail.com · [@adventure_park771](https://www.instagram.com/adventure_park771/) |
| Rafting (per person) | 12 km Marine Drive → Shivpuri, 1–1.5 hrs, ₹520 · 16 km Shivpuri → Nim Beach, 1.5–2 hrs, ₹720 · 26 km Marine Drive → Nim Beach, 2–2.5 hrs, ₹1,200 · 36 km Kaudiyala → Nim Beach, 2.5–3 hrs, ₹2,400 · peak season (March–June) weekends and crowded days +15–20% |
| Bungee jumping | 109 m / 360 ft, ₹4,000 per person incl. DSLR video and jump certificate |
| Zip line | ₹1,800 students, ₹2,000 adults (per person) |
| Luxury camps & cottages | 200 m up from the river, 100 m up from the national highway · quad/triple ₹1,500–₹1,800, double ₹1,800–₹2,200 per person/night · children 6–11 at 50% · includes three meals, swimming pool, DJ party till 10 pm, attached washroom, fan, charging points · non-AC camps have an air cooler, AC cottages have AC |
| Hotel rooms (room only) | Non-AC ₹1,200, AC ₹1,600 per room/night · busy days ₹1,600–₹1,800 / ₹2,000–₹2,200 |
| Meals (camps & cottages) | Breakfast 8:30–10:00 AM · buffet lunch 1:30–3:00 PM (pure veg) · evening snacks 6:00–7:30 PM · buffet dinner 8:30–10:30 PM (veg & non-veg) |

---

## Editing the site

All content is in the HTML files — open one in any text editor, change the words, save.

**The header, footer, mobile menu and WhatsApp button are repeated in every page.** If you change one of them (for example a phone number), make the same change in `index.html`, `about.html`, `booking.html`, `contact.html` and `404.html`.

### Headline numbers

The band under the Home hero shows **25+** years of experience, **5 lakh+** people served and **Since 2006 · Founded by JSB**. Edit them in the "NUMBERS" section of `index.html` (the About page has its own band in `about.html`). The `data-count` value is the number that counts up.

### Prices — where they appear

All prices are in Indian rupees (₹). Update them in these places in `index.html`:

| What | Where in `index.html` |
|---|---|
| Rafting stretches, durations and prices, peak-season note | **Activities** section, River rafting card |
| Bungee (₹4,000 incl. DSLR video and certificate), zip line (students/adults) | **Activities** section, Bungee and Zip line cards |
| Camps & cottages (per person / night, children 6–11 at 50%, what's included, non-AC vs AC) and hotel rooms (per room / night, busy-day prices, room only) | **Stay** section |
| Meal times and dishes | **Food** section (`id="food"`) |
| Peak-season and children/student prices in words | **FAQ** section ("Do prices change in peak season?", "Are there lower prices for children or students?") |

The activity choices in the booking form are in `booking.html` (search for `name="activities"`).

### Contact details — where they appear

| Detail | Where |
|---|---|
| Phone numbers, email, address | `contact.html` (cards), footer and mobile menu in every page |
| WhatsApp number (+91 87555 42743) | Floating button and footer in every page, WhatsApp card in `contact.html` — links look like `https://wa.me/918755542743` |
| Instagram (@adventure_park771) | Footer and mobile menu in every page, Instagram card in `contact.html` |
| Phone number in form error messages | `PHONE` at the top of the forms section in `assets/site.js` |
| Form email address | the `action` of both forms (see [Forms](#forms-formsubmit)) |

### Colours and fonts (tweakcn)

1. Design a theme at [tweakcn.com](https://tweakcn.com) and open **Code**.
2. Copy the `:root { … }` and `.dark { … }` blocks and paste them over the same blocks in `assets/theme.css`.
3. If the theme uses different fonts, update the Google Fonts `<link>` in each page's `<head>`.

The current theme: "life-jacket" orange for buttons, Ganga jade accents, river-navy text; dark mode is "the river at night".

### Logo and icons

- `assets/logo.png` — the wordmark (transparent background; shown on a light plate in dark mode).
- `favicon.ico`, `assets/icon-192.png`, `assets/apple-touch-icon.png` — made from the AP monogram.

Replace a file with one of the same name and size to update it.

### Animation

Animation is kept subtle and fast:

- **Page headers:** a ring of short dashes drifts slowly on its own and follows the mouse on hover; the background glow follows it. Tune the numbers at the top of `assets/hero-fx.js` (`RADIUS`, `IDLE_LEVEL`, …).
- **Everything else** (founder portrait, safety checklist, "Get in touch" bands, button shine) moves only on hover.
- The header animation pauses when it's off screen, runs at half speed on phones, and costs about 0.2 ms per frame.
- **Visitors who turn on "reduce motion"** in their device settings see a completely still site.

---

## Forms (FormSubmit)

Both forms send their details by email through FormSubmit to **adventurepark661@gmail.com**.

| Form | Email subject | Fields |
|---|---|---|
| Contact (`contact.html`) | New contact form message – Adventure Park website | name, age, gender, phone, email |
| Booking (`booking.html`) | New booking request – Adventure Park website | name, phone, email, group size, experience, preferred date, contact by, activities, health and notes |

**One-time activation (after the site is live):**

1. Submit the contact form once on the live site.
2. FormSubmit emails an **"Activate Form"** link to adventurepark661@gmail.com — click it. (Check spam. If an activation email arrives for each form, click both.)
3. From then on, every submission arrives as a neat table in the inbox.

Good to know:

- Visitors stay on the page and see a "Thanks" message; if sending fails they're asked to try again or call.
- A hidden `_honey` field catches spam bots; FormSubmit's captcha page is switched off for a smoother experience.
- Forms **don't send** from a file opened directly on your computer or from the claude.ai preview — they need the live website.
- **To change the receiving email**, replace `adventurepark661@gmail.com` in the `action="https://formsubmit.co/…"` of both forms, then activate again. After activation FormSubmit may also send a private code that can replace the email address in the form.

---

## Previewing

**On your computer** — run a small local web server from this folder, then open the address it prints:

```bash
python3 -m http.server 8000
# then open http://localhost:8000
```

Opening the HTML files directly also works for looking at the design, but the forms won't send.

**On different devices** — open `preview.html` (e.g. http://localhost:8000/preview.html, or `/preview.html` on the live site). It shows the site inside phone, tablet and desktop frames, side by side, with page, light/dark and rotate controls.

---

## Deploying to Netlify

The site is set up for Netlify (`netlify.toml`): no build command, publish directory is the project root.

**Recommended — connect the GitHub repository** (updates go live automatically on every push):

1. In Netlify, open the `adventurepark-rishikesh` project → **Site configuration → Build & deploy → Link repository → GitHub**, and choose this repository.
2. Branch to deploy: `main` (merge the work into `main` first). Leave the build command empty; the publish directory comes from `netlify.toml`.
3. The site is already named `adventurepark-rishikesh` → https://adventurepark-rishikesh.netlify.app (`adventurepark` and `adventure-park` were taken). Rename it any time under **Site configuration → Change site name**.

**Quick alternative — drag and drop:** download this repository as a ZIP from GitHub (**Code → Download ZIP**), unzip it, open the `adventurepark-rishikesh` project in Netlify, go to **Deploys**, and drop the folder onto the upload area.

**After the first deploy:** activate the forms (see above) and run through the launch checklist.

**Own domain later:** buy a domain (e.g. `adventurepark.in`), then in Netlify go to **Domain management → Add a domain** and follow the steps. Nothing in the site needs to change.

---

## Launch checklist

- [x] Real activities and prices (rafting with durations, bungee, zip line), stay options (camps & cottages, hotel rooms) and the food menu.
- [x] Instagram link in the footer, mobile menu and Contact page.
- [ ] Confirm whether AC cottages cost the same per person as non-AC camps (the site shows one per-person range for both).
- [ ] Check every safety statement matches what the team actually does.
- [x] Netlify site created and set to public (`adventurepark-rishikesh`).
- [ ] First deploy (link the GitHub repository or drag and drop the folder).
- [ ] Submit both forms once and click FormSubmit's activation emails.
- [ ] Check the Google Maps link on the Contact page opens the right place.
- [ ] Test on a phone: menu, WhatsApp button, tap-to-call, forms.

---

## Credits

- [Tailwind CSS](https://tailwindcss.com) (MIT) for styling.
- Theme format from [tweakcn](https://tweakcn.com) / shadcn/ui.
- WhatsApp and Instagram icons from [Simple Icons](https://simpleicons.org) (CC0); other icons drawn in the style of [Lucide](https://lucide.dev).
- Header animation inspired by [antigravity.google](https://antigravity.google).
- Fonts: Plus Jakarta Sans and Instrument Serif via Google Fonts.
