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
| Booking | `booking.html` | Booking request form (group, experience, activities, dates, health notes). Every "Book now" / "Book your adventure" button leads here |
| Contact us | `contact.html` | Contact form (name, age, gender, phone, email), office address, phone, WhatsApp, email |
| Page not found | `404.html` | Shown for any address that doesn't exist |
| Device preview | `preview.html` | Tool for checking the site on mobile, tablet and desktop (hidden from search engines) |

## What visitors can do

- **See activities and prices** — rafting (12, 16, 26 and 36 km, 1 to 3 hours), bungee jumping (109 m), zip line, luxury camps & cottages (200 m up from the river, 100 m up from the national highway) and hotel rooms, all in ₹.
- **See the food menu** — breakfast, buffet lunch (pure veg), evening snacks and buffet dinner (veg & non-veg), included with camp and cottage stays.
- **Send a booking request** or **a contact message** — both forms email the details to the park (see [Forms](#forms-formsubmit)).
- **Chat on WhatsApp** with the floating button on every page (+91 87555 42743) — the chat opens with a greeting already typed.
- **Call or email** — numbers and email are on the Contact page and in every footer.
- **Find the office** — Near Shiv Mandir, Badrinath Highway, Shivpuri, Uttarakhand, with the [Google Maps location](https://maps.app.goo.gl/yNx38Quouv5dsdkH8) linked from the Contact page, every footer and the mobile menu.
- **Follow on Instagram** — [@adventure_park771](https://www.instagram.com/adventure_park771/), linked in the footer, the mobile menu and the Contact page.
- **Use it on a phone** — a full-screen menu with big links, a "Book now" button and tap-to-call, WhatsApp, email and map links.
- **Switch light/dark mode** — follows the visitor's device setting by default.
- **Find the park on Google and in AI assistants** — every page carries search, link-preview and structured data, and the site is open to search engines and AI crawlers (see [Search engines and AI assistants](#search-engines-and-ai-assistants-seo-geo-aio)).

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
├── robots.txt            Tells search engines and AI crawlers they may read the site
├── sitemap.xml           List of pages for search engines
├── llms.txt              Plain-text summary of the business for AI assistants
├── site.webmanifest      App name and icons (for "Add to Home screen")
├── netlify.toml          Netlify settings
└── assets/
    ├── theme.css         Colours, fonts, radius, shadows (tweakcn format)
    ├── head.js           Light/dark mode + shared Tailwind setup and animations
    ├── site.js           Header, mobile menu, scroll reveals, counters, forms
    ├── hero-fx.js        Animated dash ring and glow in the page headers
    ├── logo.png          ADVENTURE PARK wordmark (header and footer)
    ├── icon-192.png      App icon
    ├── apple-touch-icon.png  Home-screen icon for iPhone/iPad
    └── og-image.png      Link-preview picture (WhatsApp, Facebook, Google…), 1200×630
```

---

## Business details used on the site

Every fact the site states, in one place — check these before launch.

| Detail | Value |
|---|---|
| Founder | Jagat Singh Bhandari (JSB) |
| Experience / people served / running since | 25+ years · 5 lakh+ people · since 2006 |
| Office | Near Shiv Mandir, Badrinath Highway, Shivpuri, Uttarakhand · [Google Maps](https://maps.app.goo.gl/yNx38Quouv5dsdkH8) |
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

**Search and AI copies of the facts.** Prices, phone numbers, the address and the FAQ also appear in the structured data at the top of each page and in `llms.txt`. When a fact changes on the page, change it there too — see [Keeping it accurate](#keeping-it-accurate).

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
| Copies for search engines and AI assistants | the `<script type="application/ld+json">` block in the `<head>` of `index.html` (`"price"`, `"minPrice"`, `"maxPrice"`, `"priceRange"`), the description `<meta>` tags of `index.html` ("from ₹520"), and `llms.txt` |

The activity choices in the booking form are in `booking.html` (search for `name="activities"`).

### Contact details — where they appear

| Detail | Where |
|---|---|
| Phone numbers, email, address | `contact.html` (cards), footer and mobile menu in every page |
| Google Maps location (https://maps.app.goo.gl/yNx38Quouv5dsdkH8) | "Open in Google Maps" on `contact.html`; the address in every footer and the mobile menu |
| WhatsApp number (+91 87555 42743) | Floating button and footer in every page, WhatsApp card in `contact.html` — links look like `https://wa.me/918755542743` |
| Instagram (@adventure_park771) | Footer and mobile menu in every page, Instagram card in `contact.html` |
| Phone number in form error messages | `PHONE` at the top of the forms section in `assets/site.js` |
| Form email address | the `action` of both forms (see [Forms](#forms-formsubmit)) |
| Copies for search engines and AI assistants | the `<script type="application/ld+json">` block in the `<head>` of `index.html`, `about.html`, `booking.html` and `contact.html`; the description `<meta>` tags of `contact.html`; `llms.txt` |

### Colours and fonts (tweakcn)

1. Design a theme at [tweakcn.com](https://tweakcn.com) and open **Code**.
2. Copy the `:root { … }` and `.dark { … }` blocks and paste them over the same blocks in `assets/theme.css`.
3. If the theme uses different fonts, update the Google Fonts `<link>` in each page's `<head>`.

The current theme: "life-jacket" orange for buttons, Ganga jade accents, river-navy text; dark mode is "the river at night".

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
| `llms.txt` | site root | A plain-text fact sheet in the [llms.txt](https://llmstxt.org) format: the business, every price, the stays, the food menu, safety and the FAQ, so an AI assistant can quote them accurately |
| `site.webmanifest` | site root | App name, colours and icons |

### Structured data (schema.org JSON-LD)

| Page | What it describes |
|---|---|
| Home | **The business** (`SportsActivityLocation` + `TouristAttraction`): name, tagline, founder, founded in 2006, address, map link, phones, WhatsApp, email, Instagram, price range ₹520–₹4,000 and **a catalogue of all 11 prices** (4 rafting stretches, bungee, zip line ×2, camps ×2, hotel rooms ×2, each in INR). **The FAQ** (all 7 questions and answers), **the camps & cottages** (`Campground` with every amenity) and **the food menu** (4 meals and their dishes, vegetarian dishes marked) |
| About | The business, the About page and **the founder**, Jagat Singh Bhandari (JSB) |
| Booking | The business, the Booking page and a "send a booking request" action |
| Contact | The business and the Contact page |

About, Booking and Contact also have a breadcrumb trail (Home › page). The 404 page has no structured data.

### Crawlers allowed in `robots.txt`

All crawlers are allowed (`User-agent: *`). These are also named one by one:

| Company | Crawlers |
|---|---|
| OpenAI (ChatGPT) | GPTBot, OAI-SearchBot, ChatGPT-User |
| Anthropic (Claude) | ClaudeBot, Claude-SearchBot, Claude-User, anthropic-ai |
| Perplexity | PerplexityBot, Perplexity-User |
| Google (Search, Gemini, AI Overviews) | Googlebot, Google-Extended |
| Microsoft (Bing, Copilot) | Bingbot |
| Apple (Siri, Apple Intelligence) | Applebot, Applebot-Extended |
| Others | DuckAssistBot, Amazonbot, meta-externalagent, CCBot (Common Crawl), cohere-ai, MistralAI-User, YouBot |

Only `preview.html` (the device-preview tool) is blocked. To block one of the crawlers above instead, move its `User-agent:` line into a new group of its own with `Disallow: /`.

### Keeping it accurate

AI assistants and search engines trust a business more when the same facts appear everywhere. When something changes:

- **A price or a fact on the page** → update the same value in the structured data of `index.html` (search the `<head>` for the old value — prices are written without commas, e.g. `"price": "4000"`) and in `llms.txt`.
- **Anything on the site** → update `<lastmod>` in `sitemap.xml` (and `"dateModified"` in the structured data) to the date of the change.
- **The FAQ** → the questions and answers are copied into the structured data of `index.html` and into `llms.txt`.
- **Moving to your own domain** (e.g. `adventurepark.in`) → find-and-replace `https://adventurepark-rishikesh.netlify.app` with the new address in every `.html` file, `robots.txt`, `sitemap.xml` and `llms.txt`.
- **Name, address and phone** should be written exactly the same here, on Google Business Profile, Instagram and any listing sites.
- To check the structured data, paste a page address into Google's [Rich Results Test](https://search.google.com/test/rich-results) or the [Schema Markup Validator](https://validator.schema.org).

### After launch

These steps are done outside the website and make the biggest difference to being found:

1. **[Google Business Profile](https://business.google.com)** — create or claim the listing for Adventure Park with the same name, address, phones and website. This drives Google Maps, "near me" searches and many AI answers about local businesses. Add photos and opening hours, and ask happy guests for Google reviews.
2. **[Google Search Console](https://search.google.com/search-console)** — add the site, then submit `sitemap.xml` under **Sitemaps**.
3. **[Bing Webmaster Tools](https://www.bing.com/webmasters)** — add the site (it can import from Search Console) and submit the sitemap. Bing's index also feeds Copilot and other AI search tools.
4. Optional: send the office's exact map coordinates (latitude, longitude) to be added to the structured data.

No website can guarantee a place in AI answers. These steps make sure that when AI assistants do look, they can read every page and find correct, consistent facts.

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

**After the first deploy:** activate the forms (see above), do the [search and AI steps](#after-launch), and run through the launch checklist.

**Own domain later:** buy a domain (e.g. `adventurepark.in`), then in Netlify go to **Domain management → Add a domain** and follow the steps. Then replace the Netlify address in the pages and crawler files (see [Keeping it accurate](#keeping-it-accurate)).

---

## Launch checklist

- [x] Real activities and prices (rafting with durations, bungee, zip line), stay options (camps & cottages, hotel rooms) and the food menu.
- [x] Instagram link in the footer, mobile menu and Contact page.
- [ ] Confirm whether AC cottages cost the same per person as non-AC camps (the site shows one per-person range for both).
- [ ] Check every safety statement matches what the team actually does.
- [x] Netlify site created and set to public (`adventurepark-rishikesh`).
- [ ] First deploy (link the GitHub repository or drag and drop the folder).
- [ ] Submit both forms once and click FormSubmit's activation emails.
- [x] Google Maps location link from the owner, on the Contact page, footer and mobile menu.
- [ ] Test on a phone: menu, WhatsApp button, tap-to-call, forms.
- [x] Search and AI setup: titles, descriptions, link previews, structured data, `robots.txt`, `sitemap.xml`, `llms.txt`.
- [ ] Create or claim the Google Business Profile with the same name, address and phones.
- [ ] Add the site to Google Search Console and Bing Webmaster Tools and submit `sitemap.xml`.
- [ ] Share a link on WhatsApp to check the preview picture shows.

---

## Credits

- [Tailwind CSS](https://tailwindcss.com) (MIT) for styling.
- Theme format from [tweakcn](https://tweakcn.com) / shadcn/ui.
- WhatsApp and Instagram icons from [Simple Icons](https://simpleicons.org) (CC0); other icons drawn in the style of [Lucide](https://lucide.dev).
- Header animation inspired by [antigravity.google](https://antigravity.google).
- Fonts: Plus Jakarta Sans and Instrument Serif via Google Fonts.
