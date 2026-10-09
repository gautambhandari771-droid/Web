# Adventure Park – WordPress theme

The Adventure Park website as a WordPress theme. It has the same design, pages, booking and contact forms, UPI payment section, photo galleries, offline copy and search-engine data. The details you change most often (prices, phone numbers, payment rules, photos, page text) are edited in the WordPress dashboard instead of in the code.

- **To install:** `adventure-park.zip`. It isn't stored in the repository; build it with `python3 wordpress-theme/make_theme.py --zip` (see [For developers](#for-developers)), or use the copy you were sent.
- **Theme source:** the `adventure-park/` folder.
- **Requirements:**
  - WordPress 6.5 or newer.
  - PHP 7.4 or newer (8.1 or newer recommended).
  - HTTPS, which hosting plans include for free.

## Contents

- [Installing, step by step](#installing-step-by-step)
- [Everyday changes](#everyday-changes)
- [Updating the theme later](#updating-the-theme-later)
- [Good to know](#good-to-know)
- [Tests](#tests)
- [For developers](#for-developers)

## Installing, step by step

1. **Hosting and a domain.**
   - Any WordPress hosting plan works. Most hosts install WordPress for you in one click when you set up the plan.
   - Buy the domain with the hosting or separately, for example `adventureparkrishikesh.com` or `.in`.
   - Make sure the plan includes free SSL (the padlock / `https://`).
2. **Upload the theme.**
   - In the WordPress dashboard, go to **Appearance → Themes → Add New Theme → Upload Theme**.
   - Choose `adventure-park.zip`, then click **Install Now** and **Activate**.
3. **What the theme sets up by itself** (nothing is created twice if you activate it again):
   - It creates the **Home, About, Booking and Contact** pages with today's text.
   - It makes Home the front page and builds the **Main menu**.
   - It switches on readable page addresses (`/booking/` instead of `/?page_id=12`).
   - A green message confirms this and links to the settings.
4. **Set the Site Title.**
   - Go to **Settings → General**, set **Site Title** to `Adventure Park Rishikesh` and click **Save Changes**.
   - The Site Title is used in link previews (WhatsApp, Facebook) and as the app name when someone adds the site to their phone's home screen.
5. **Check your details.**
   - Go to **Appearance → Customize → Adventure Park** and check each section, then click **Publish**.
   - Everything is already filled in with the current website's details.
6. **Test both forms.**
   - Send one booking (with any image as the payment screenshot) and one contact message from the new site.
   - The forms send through FormSubmit to the email in Customize → Contact details. FormSubmit may email you an activation link for the new address: click it, or messages won't arrive.
   - Do the same again whenever you change that email address.
7. **Tell Google.**
   - In [Google Search Console](https://search.google.com/search-console), add the new domain and submit the sitemap `https://YOUR-DOMAIN/wp-sitemap.xml`.
   - In your Google Business Profile, change the website link.
8. **The old address.**
   - Once the new domain works, the Netlify address (adventureprk.netlify.app) should forward every page to it. That way old links and Google results land on the new site.
   - This is a small change in `netlify.toml` here, made when the domain is known.

## Everyday changes

| To change | Go to |
|---|---|
| Prices | Appearance → Customize → Adventure Park → **Prices (₹)** |
| Phone, WhatsApp number and greeting, email, address, Google Maps link, Instagram | Customize → Adventure Park → **Contact details** |
| UPI ID, the name shown in UPI apps, the QR code | Customize → Adventure Park → **Payment and refunds**. If you change the UPI ID, upload the new QR code too |
| Advance percentage, refund window | Customize → Adventure Park → **Payment and refunds** |
| Activity photos, the "Representative photo" label on the zip line photos | Customize → Adventure Park → **Photos**. The photo's description for blind visitors and Google comes from the photo's **Alternative text** in the Media Library |
| A page's headings, introduction, steps, numbers (years, lakh people, year founded), opening hours, and its title and description in Google | **Pages →** open the page → the **Page text** box at the bottom of the editor, then **Save** |
| The FAQ on the Home page | Pages → **Home** → in the editor, each question is a **Details** block: the summary is the question, the text inside is the answer. Add one with the **+** button (search "Details") |
| The founder's story on the About page | Pages → **About** → the text in the editor |
| The menu | Appearance → **Menus** (or Customize → Menus) |
| A new page or blog post of your own | Pages → Add New, or Posts → Add New. It uses a plain layout in the site's style |

A change in Customize shows in the preview on the right before you click **Publish**. A change of price or phone number updates every page that shows it, the FAQ, and what Google and AI assistants read.

**Placeholders.** In any Page text field or FAQ answer you can write these, and the current value is shown in their place:

- `{advance}` (the advance percentage) and `{remaining}` (the rest)
- `{refund_hours}`
- `{years}`, `{people}`, `{founded}`
- `{phone}`, `{whatsapp}`, `{email}`, `{upi_id}`
- a price, such as `{rafting_12}` or `{bungee}`

For example, "Pay {advance}% in advance" becomes "Pay 50% in advance", and changes by itself when you change the advance. Longer texts can contain links and **bold** words.

**In recent WordPress versions** (7.1, for example), the Page text box sits in a **Meta Boxes** bar at the bottom of the page editor. The theme opens that bar on its four pages and shows a note with a **Show Page text** button. You can drag the bar's edge to make it taller or shorter.

## Updating the theme later

When you get a new `adventure-park.zip` (after a design change, for example):

1. Go to **Appearance → Themes → Add New Theme → Upload Theme** and upload it.
2. WordPress says the theme is already installed. Click **Replace active with uploaded**.

Your settings, page text, FAQ, photos and menu are kept.

## Good to know

- **Logged in vs. visitors.** While you're logged in, you see the site with the WordPress admin bar and without the offline copy and the strict security policy, so the dashboard tools work. Visitors get both. To see the site exactly as visitors do, open it in a private window.
- **Plugins.**
  - An SEO plugin (Yoast SEO, Rank Math, All in One SEO, SEOPress) works: it takes over the titles, descriptions and link previews, and the theme keeps adding its structured data (prices, FAQ, business details).
  - If a plugin adds something visitors should see (a chat widget, analytics, a cookie banner) and it doesn't appear, untick **Customize → Adventure Park → Security → Strict security policy**.
  - With a caching plugin, exclude `/sw.js` from its cache.
- **Addresses the theme serves.** `/sw.js` (the offline copy), `/llms.txt` (summary for AI assistants), `/robots.txt`, `/site.webmanifest`, `/.well-known/security.txt` and WordPress's `/wp-sitemap.xml`. They are built from your settings, so a new price reaches them too.
- **Differences from the Netlify site.**
  - WordPress shows straight apostrophes as typographic ones (’) in the founder's story.
  - The sitemap is at `/wp-sitemap.xml` instead of `/sitemap.xml`.
  - The colour preview page (`preview.html`) isn't part of the theme.
  - Everything else looks the same, pixel for pixel (see [Tests](#tests)).
- **Not a "block" theme.** The designed pages come from the theme, so **Appearance → Editor** and page builders don't change them. Their text is edited as described above.

## Tests

The theme was installed from `adventure-park.zip` through **Upload Theme** on a new WordPress site each time, and checked automatically in Chromium:

| WordPress | PHP | Result |
|---|---|---|
| 7.1.3 (current) | 8.3 | 31 of 31 checks pass |
| 6.5.5 | 7.4 | 31 of 31 checks pass |

**Visitor checks:**

- **Pages:** all four pages at phone and desktop width have no script errors, nothing is blocked by the security policy, nothing sticks out sideways, and the menu marks the current page. The 404 page works.
- **Menu and Book buttons:** the menu and mobile menu link to the WordPress pages. "Book bungee" opens the booking form with bungee ticked.
- **Forms:** the booking form uploads the payment screenshot to FormSubmit and comes back to the thank-you message. The contact form sends.
- **Galleries, FAQ, payment:** the photo galleries load later photos only when needed. The FAQ links work. The UPI ID copies, and the QR code shows and downloads.
- **Offline copy:** it saves the pages and files, and pages open offline.

**Owner checks:**

- **Customize:** new price, advance percentage, phone number and "Representative photo" setting show in the preview, then on every page, in the FAQ, in Google's data and in llms.txt.
- **Page text:** the box is open on arrival, and changes to it show on the site.
- **FAQ:** a question added in the editor shows on the site and in Google's FAQ data.
- **Photos:** a photo chosen from the Media Library replaces a gallery photo, with sizes and its own description.
- **Updating:** uploading a newer zip over the installed theme works ("Theme updated successfully").

**Pixel comparison:** every page (phone and desktop, light and dark) matches the Netlify site pixel for pixel. The only exception is the typographic apostrophe in the founder's story.

**Other checks:** the special addresses and security headers (the same as on Netlify) were checked separately.

WordPress 6.5 logs a harmless notice from its own Customizer code with themes that have no widget areas. WordPress 7.1 no longer does.

## For developers

**How the theme is made.** `make_theme.py` turns the website's pages (one folder up) into the parts of the theme that follow the design. Every page change therefore reaches the theme by running it again. It writes:

- `parts/*.html`: the pages as templates, with `{{tokens}}` where the dashboard settings go.
- `inc/defaults.json`: today's text, prices and contact details, used as the starting values.
- `schema/*.json`: structured-data templates.
- `inc/llms.txt`, `inc/robots.txt`, `inc/sw.js`: templates for those addresses.
- `assets/`: copied from the site, with `site.js` patched for WordPress plus `sw-off.js` and `content.css`.
- `assets/styles.css`: built with Tailwind from the theme's own files.

Every replacement checks how often it applies. If a page changes in a way the theme no longer understands, the script stops with an error instead of producing a theme with a missing setting.

**Written by hand:**

| File | Purpose |
|---|---|
| `style.css` | Theme header |
| `functions.php` | Loads the `inc/*.php` files |
| `inc/settings.php` | Customizer |
| `inc/fields.php` | Page text box and placeholders |
| `inc/render.php` | Fills in the templates |
| `inc/setup.php` | Scripts, styles, head tags, security headers |
| `inc/seo.php` | Titles, descriptions, link previews, structured data |
| `inc/routes.php` | `/sw.js`, `/llms.txt`, manifest, `security.txt`, robots.txt |
| `inc/activate.php` | First-time setup |
| `admin/editor.js` | Opens the Page text box |
| Templates (`header.php`, `footer.php`, `front-page.php`, `page-templates/`, `page.php`, `404.php`, `index.php`) | The page templates |
| `readme.txt`, `screenshot.png` | Theme readme and dashboard thumbnail |

**Rebuilding after a website change:**

```bash
npm install --no-save tailwindcss@4.3.3 @tailwindcss/cli@4.3.3   # once, in the website folder
python3 wordpress-theme/make_theme.py --zip                      # rebuilds the theme and adventure-park.zip
```

Then install the zip on a test WordPress site (for example with [WordPress Playground](https://wordpress.org/playground/)) and check the pages before sending it on. Raise `Version:` in `adventure-park/style.css` and `ADVENTURE_PARK_VERSION` in `functions.php` for each new zip.
