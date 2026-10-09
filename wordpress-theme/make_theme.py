"""Turn the website (the HTML pages one folder up) into the 'adventure-park' WordPress theme parts.

Writes token templates (parts/*.html), defaults (inc/defaults.json), structured-data templates
(schema/*.json), the llms.txt / robots.txt / sw.js templates, copies the assets and builds the
theme's styles.css. The PHP files, admin/, readme.txt and screenshot.png are written by hand; this
script only produces what is derived from the site, so the theme always matches the current design.

    npm install --no-save tailwindcss@4.3.3 @tailwindcss/cli@4.3.3   (once, in the website folder)
    python3 wordpress-theme/make_theme.py --zip                      (rebuild + adventure-park.zip)

Every replacement asserts how many times it applies, so a change in the site that would make
a setting stop showing up fails loudly here instead of silently in WordPress.
"""
import html as htmllib
import json
import re
import shutil
import subprocess
import sys
import zipfile
import urllib.parse
from pathlib import Path

HERE = Path(__file__).resolve().parent
SRC = HERE.parent
ARGS = [a for a in sys.argv[1:] if not a.startswith('--')]
OUT = Path(ARGS[0]).resolve() if ARGS else HERE / 'adventure-park'
SITE = 'https://adventureprk.netlify.app'

read = lambda name: (SRC / name).read_text(encoding='utf-8')


def sub(text, old, new, count=1, regex=False, label=''):
    """Replace exactly `count` occurrences (count=None: at least one)."""
    if regex:
        found = len(re.findall(old, text))
        ok = found >= 1 if count is None else found == count
        assert ok, f'{label or old!r}: expected {count}, found {found}'
        return re.sub(old, new, text)
    found = text.count(old)
    ok = found >= 1 if count is None else found == count
    assert ok, f'{label or old[:80]!r}: expected {count}, found {found}'
    return text.replace(old, new)


def collapse(s):
    return re.sub(r'\s+', ' ', s).strip()


def strip_classes(fragment):
    return re.sub(r' class="[^"]*"', '', fragment)


# ---------------------------------------------------------------- split the pages
pages = {n: read(n) for n in ['index.html', 'about.html', 'booking.html', 'contact.html', '404.html']}


def parts_of(page):
    body = page[page.index('<body'):]
    start = body.index('<a href="#main"') if '<a href="#main"' in body else body.index('<a href="404.html#main"')
    header = body[start:body.index('<main id="main">')]
    main = body[body.index('<main id="main">'):body.index('</main>') + len('</main>')]
    footer = body[body.index('</main>') + len('</main>'):body.index('<script src="assets/hero-fx.js')]
    return header, main, footer


header, _, footer = parts_of(pages['index.html'])
mains = {n: parts_of(p)[1] for n, p in pages.items()}
defaults = {'settings': {}, 'fields': {}, 'faq': [], 'about_story': [], 'nav': {}, 'seo': {}}

# ---------------------------------------------------------------- shared replacements
WA = re.search(r'https://wa\.me/918755542743\?text=([^"]+)', pages['index.html']).group(1)
defaults['settings'].update({
    'phone_main': '+91 97623 88871', 'phone_wa': '+91 87555 42743', 'email': 'adventurepark661@gmail.com',
    'street': 'Near Shiv Mandir, Badrinath Highway', 'town': 'Shivpuri', 'region': 'Uttarakhand',
    'address_short': 'Shivpuri, Badrinath Highway, Uttarakhand', 'map_url': 'https://maps.app.goo.gl/CKrMGT2h8CRNrcmi6',
    'instagram': 'adventure_park771', 'wa_message': urllib.parse.unquote(WA),
    'upi_id': '8755542743@ybl', 'upi_name': 'M/S Adventure Park', 'advance': 50, 'refund_hours': 36,
    'rafting_12': 520, 'rafting_16': 720, 'rafting_26': 1200, 'rafting_36': 2400, 'bungee': 4000,
    'zip_student': 1800, 'zip_adult': 2000, 'camp_shared_min': 1500, 'camp_shared_max': 1800,
    'camp_double_min': 1800, 'camp_double_max': 2200, 'room_nonac': 1200, 'room_nonac_busy_min': 1600,
    'room_nonac_busy_max': 1800, 'room_ac': 1600, 'room_ac_busy_min': 2000, 'room_ac_busy_max': 2200,
    'zip_representative': True, 'strict_csp': True,
})


def common(t, where):
    """Contact details, links and asset paths: the same everywhere."""
    t = re.sub(r'https://wa\.me/918755542743\?text=[^"]+', '{{wa_link}}', t)
    t = t.replace('https://formsubmit.co/adventurepark661@gmail.com', '{{formsubmit}}')
    t = t.replace('mailto:adventurepark661@gmail.com', 'mailto:{{email}}').replace('adventurepark661@gmail.com', '{{email}}')
    t = t.replace('tel:+919762388871', 'tel:{{tel_main}}').replace('tel:+918755542743', 'tel:{{tel_wa}}')
    t = t.replace('+91 97623 88871', '{{phone_main}}').replace('+91 87555 42743', '{{phone_wa}}')
    t = t.replace('https://maps.app.goo.gl/CKrMGT2h8CRNrcmi6', '{{map_url}}')
    t = t.replace('https://www.instagram.com/adventure_park771/', '{{instagram_url}}').replace('@adventure_park771', '@{{instagram}}')
    t = t.replace('8755542743@ybl', '{{upi_id}}').replace('M/S ADVENTURE PARK', '{{upi_name_caps}}').replace('M/S Adventure Park', '{{upi_name}}')
    t = t.replace('Near Shiv Mandir, Badrinath Highway, Shivpuri, Uttarakhand', '{{address_full}}')
    t = t.replace('Shivpuri, Badrinath Highway, Uttarakhand', '{{address_short}}')
    # Links between pages (with optional ?query and #anchor)
    for page, key in [('index.html', 'home'), ('about.html', 'about'), ('booking.html', 'booking'), ('contact.html', 'contact')]:
        t = re.sub(r'href="' + re.escape(page) + r'([?#][^"]*)?"', lambda m: f'href="{{{{url:{key}}}}}{m.group(1) or ""}"', t)
    t = t.replace('href="404.html#main"', 'href="#main"')
    # Asset paths in attributes (src, srcset lists, data-src, href)
    t = re.sub(r'(?<=["\s,])assets/', '{{asset}}/', t)
    leftovers = [v for v in ['adventurepark661', '97623', '87555', 'goo.gl', 'adventure_park771', '.html"'] if v in t]
    assert not leftovers, f'{where}: left over {leftovers}'
    return t


# ---------------------------------------------------------------- header: menu comes from WordPress
desk = re.search(r'(<nav aria-label="Main" class="[^"]*">)(.*?)(\s*</nav>)', header, re.S)
link_cls = re.search(r'<a href="index.html" aria-current="page" class="([^"]*)"', desk.group(2)).group(1)
link_cls_off = re.search(r'<a href="about.html" class="([^"]*)"', desk.group(2)).group(1)
header = header.replace(desk.group(0), desk.group(1) + '\n        {{nav_desktop}}' + desk.group(3))
mob = re.search(r'(<nav aria-label="Mobile"[^>]*>\s*<ul>)(.*?)(\s*</ul>)', header, re.S)
mob_li = re.search(r'<li><a href="index.html" aria-current="page" class="([^"]*)">(.*?)</a></li>', mob.group(2), re.S)
mob_cls = mob_li.group(1)
mob_cls_off = re.search(r'<li><a href="about.html" class="([^"]*)"', mob.group(2)).group(1)
assert '[--i:3]' in mob_cls_off
mob_inner = mob_li.group(2)  # "Home <svg…>" : the label then an arrow icon
mob_icon = mob_inner[mob_inner.index('<svg'):]
header = header.replace(mob.group(0), mob.group(1) + '\n          {{nav_mobile}}' + mob.group(3))
defaults['nav'] = {'desktop_class': link_cls, 'desktop_class_off': link_cls_off, 'mobile_class': mob_cls.replace('[--i:0]', '[--i:__I__]'), 'mobile_class_off': mob_cls_off.replace('[--i:3]', '[--i:__I__]'), 'mobile_icon': mob_icon,
                   'items': [['Home', 'home', ''], ['Activities', 'home', '#activities'], ['Stay', 'home', '#stay'],
                             ['About', 'about', ''], ['Booking', 'booking', ''], ['Contact', 'contact', '']]}
header = common(header, 'header')
footer = common(footer, 'footer')
footer = sub(footer, '<span data-year>2026</span>', '<span data-year>{{year}}</span>', label='footer year')

# ---------------------------------------------------------------- fields (editable text) helpers
FIELDS = {}


def field(page, key, text, original, label, kind='text', help=''):
    """Swap `original` (exact, once) for a field token; its default is `text`."""
    FIELDS.setdefault(page, []).append({'key': key, 'label': label, 'type': kind, 'default': text, 'help': help})
    return original, '{{f:' + key + '}}'


def placeholders(s):
    s = s.replace('50%', '{advance}%').replace('36 hours', '{refund_hours} hours')
    return s


# ---------------------------------------------------------------- home
m = mains['index.html']
m = common(m, 'home')
# Hero: badge, headline, intro
hero_badge = 'Safety-first adventures in Rishikesh'
m = sub(m, '\n          ' + hero_badge + '\n', '\n          {{f:hero_badge}}\n')
FIELDS['home'] = [{'key': 'hero_badge', 'label': 'Badge above the headline', 'type': 'text', 'default': hero_badge}]
m = sub(m, 'pb-2 text-transparent">Adventure Park</span>', 'pb-2 text-transparent">{{f:hero_title}}</span>')
FIELDS['home'].append({'key': 'hero_title', 'label': 'Headline', 'type': 'text', 'default': 'Adventure Park'})
intro = re.search(r'(<p class="mx-auto mt-8 max-w-2xl animate-fade-up[^"]*">)\s*(.*?)\s*(</p>)', m, re.S)
FIELDS['home'].append({'key': 'hero_intro', 'label': 'Intro under the headline', 'type': 'textarea', 'default': collapse(intro.group(2))})
m = m.replace(intro.group(0), intro.group(1) + '\n          {{f:hero_intro}}\n        ' + intro.group(3))
# Numbers
m = sub(m, '<span data-count="25">25</span>+', '<span data-count="{{years}}">{{years}}</span>+')
m = sub(m, '<span data-count="5">5</span> lakh+', '<span data-count="{{people}}">{{people}}</span> lakh+')
m = sub(m, '</span> 2006</p>', '</span> {{founded}}</p>')
m = sub(m, '</span>25+ years of experience</li>', '</span>{{years}}+ years of experience</li>')
m = sub(m, '</span>5 lakh+ people served</li>', '</span>{{people}} lakh+ people served</li>')
m = sub(m, 'A team backed by 25 years of adventure experience.', 'A team backed by {{years}} years of adventure experience.')
m = sub(m, 'Rishikesh, Uttarakhand · 25+ years of experience', 'Rishikesh, Uttarakhand · {{years}}+ years of experience')
# Prices: activities
for key, price in [('rafting_12', '₹520'), ('rafting_16', '₹720'), ('rafting_36', '₹2,400')]:
    m = sub(m, f'<span class="font-bold tabular-nums">{price}</span>', f'<span class="font-bold tabular-nums">{{{{p:{key}}}}}</span>')
m = sub(m, '<span class="font-bold tabular-nums">₹1,200</span><span class="block text-xs text-muted-foreground">per person</span>',
        '<span class="font-bold tabular-nums">{{p:rafting_26}}</span><span class="block text-xs text-muted-foreground">per person</span>')
m = sub(m, 'tabular-nums">₹4,000</span>', 'tabular-nums">{{p:bungee}}</span>')
m = sub(m, '<span><span class="font-bold tabular-nums">₹1,800</span> <span class="text-xs text-muted-foreground">per person</span>',
        '<span><span class="font-bold tabular-nums">{{p:zip_student}}</span> <span class="text-xs text-muted-foreground">per person</span>')
m = sub(m, '<span><span class="font-bold tabular-nums">₹2,000</span> <span class="text-xs text-muted-foreground">per person</span>',
        '<span><span class="font-bold tabular-nums">{{p:zip_adult}}</span> <span class="text-xs text-muted-foreground">per person</span>')
# Prices: stay
m = sub(m, 'tabular-nums">₹1,500–₹1,800</span>', 'tabular-nums">{{p:camp_shared_min}}–{{p:camp_shared_max}}</span>')
m = sub(m, 'tabular-nums">₹1,800–₹2,200</span>', 'tabular-nums">{{p:camp_double_min}}–{{p:camp_double_max}}</span>')
m = sub(m, 'tabular-nums">₹1,200</span> <span class="text-xs text-muted-foreground">per room / night</span>',
        'tabular-nums">{{p:room_nonac}}</span> <span class="text-xs text-muted-foreground">per room / night</span>')
m = sub(m, 'Busy days: ₹1,600–₹1,800', 'Busy days: {{p:room_nonac_busy_min}}–{{p:room_nonac_busy_max}}')
m = sub(m, 'tabular-nums">₹1,600</span> <span class="text-xs text-muted-foreground">per room / night</span>',
        'tabular-nums">{{p:room_ac}}</span> <span class="text-xs text-muted-foreground">per room / night</span>')
m = sub(m, 'Busy days: ₹2,000–₹2,200', 'Busy days: {{p:room_ac_busy_min}}–{{p:room_ac_busy_max}}')
# Photos
GALLERY_SIZES = {}
for g in ['rafting', 'bungee', 'zipline']:
    for i in range(1, 5):
        first = re.search(r'(?<![\w-])src="\{\{asset\}\}/photos/' + g + r'-' + str(i) + r'-(\d+)\.webp"( srcset="[^"]*")?( sizes="([^"]*)")?', m)
        if first:
            GALLERY_SIZES[g] = first.group(4) or GALLERY_SIZES.get(g)
            m = m.replace(first.group(0), '{{photo:' + g + '_' + str(i) + '}}')
            continue
        later = re.search(r'data-src="\{\{asset\}\}/photos/' + g + r'-' + str(i) + r'-(\d+)\.webp"( data-srcset="[^"]*")?( data-sizes="([^"]*)")?', m)
        if later:
            GALLERY_SIZES[g] = later.group(4) or GALLERY_SIZES.get(g)
            m = m.replace(later.group(0), '{{photo_deferred:' + g + '_' + str(i) + '}}')
assert '/photos/' not in m, 'photo paths left in home'
defaults['photo_alts'] = {}


def alt_token(tag):
    key = re.search(r'\{\{photo(?:_deferred)?:(\w+)\}\}', tag.group(0)).group(1)
    alt = re.search(r' alt="([^"]*)"', tag.group(0)).group(1)
    defaults['photo_alts'][key] = htmllib.unescape(alt)
    return tag.group(0).replace(f' alt="{alt}"', ' alt="{{photo_alt:' + key + '}}"')


m = re.sub(r'<img [^>]*\{\{photo(?:_deferred)?:\w+\}\}[^>]*>', alt_token, m)
assert len(defaults['photo_alts']) == 8, defaults['photo_alts']
chip = re.search(r'\s*<span class="[^"]*">Representative photo</span>', m).group(0)
m = m.replace(chip, '{{rep_chip}}')
defaults['rep_chip'] = chip
defaults['gallery_sizes'] = GALLERY_SIZES
# Plan your adventure: three steps
steps = re.findall(r'<h3 class="mb-3 text-2xl font-bold">(.*?)</h3>\s*<p class="text-lg leading-relaxed text-muted-foreground">(.*?)</p>', m, re.S)
assert len(steps) == 3, len(steps)
for i, (title, text) in enumerate(steps, 1):
    FIELDS['home'] += [{'key': f'step{i}_title', 'label': f'"Plan your adventure", step {i}: title', 'type': 'text', 'default': title},
                       {'key': f'step{i}_text', 'label': f'"Plan your adventure", step {i}: text', 'type': 'textarea', 'default': placeholders(text)}]
    m = sub(m, f'<h3 class="mb-3 text-2xl font-bold">{title}</h3>\n            <p class="text-lg leading-relaxed text-muted-foreground">{text}</p>',
            f'<h3 class="mb-3 text-2xl font-bold">{{{{f:step{i}_title}}}}</h3>\n            <p class="text-lg leading-relaxed text-muted-foreground [&_a]:font-semibold [&_a]:text-primary-ink [&_a]:underline-offset-4 hover:[&_a]:underline">{{{{f:step{i}_text}}}}</p>')
# FAQ: questions and answers become Details blocks in the Home page
faq_block = re.search(r'(<div class="space-y-4 lg:col-span-8">)(.*?)(\n        </div>\n      </div>\n    </section>)', m, re.S)
items = re.findall(r'<details name="faq" class="([^"]*)">\s*<summary class="([^"]*)">\s*(.*?)\s*(<span class="[^"]*"><svg.*?</svg></span>)\s*</summary>\s*<p class="([^"]*)">(.*?)</p>\s*</details>', faq_block.group(2), re.S)
assert len(items) >= 8, len(items)
defaults['faq_markup'] = {'details_class': re.sub(r'\s*\[--d:\d+ms\]', '', items[0][0]), 'summary_class': items[0][1], 'icon': items[0][3], 'answer_class': items[0][4]}
for d_cls, s_cls, q, icon, a_cls, a in items:
    a = strip_classes(a)
    a = re.sub(r'href="\{\{url:(\w+)\}\}([^"]*)"', r'href="{url:\1}\2"', a)
    a = a.replace('{{phone_main}}', '{phone}').replace('{{phone_wa}}', '{whatsapp}').replace('{{email}}', '{email}')
    a = a.replace('{{upi_id}}', '{upi_id}').replace('{{upi_name}}', '{upi_name}')
    a = a.replace('₹1,800', '{zip_student}').replace('₹2,000', '{zip_adult}')
    a = placeholders(a)
    a = re.sub(r'remaining \{advance\}%', 'remaining {remaining}%', a)
    defaults['faq'].append([collapse(q), collapse(a)])
m = m.replace(faq_block.group(0), faq_block.group(1) + '\n          {{faq_items}}' + faq_block.group(3))
leftover = [v for v in ['₹', '50%', '36 hours'] if v in re.sub(r'<[^>]+>', '', m)]
assert leftover == ['50%'] and m.count('Children aged 6–11 pay 50% of the adult price.') == 1, leftover  # the children's discount stays fixed
mains['index.html'] = m

# ---------------------------------------------------------------- about
m = common(mains['about.html'], 'about')
h1 = re.search(r'(<h1 class="[^"]*">)\s*(.*?) <span class="font-serif font-normal italic text-secondary-foreground">(.*?)</span>\s*(</h1>)', m, re.S)
FIELDS['about'] = [{'key': 'hero_title', 'label': 'Headline', 'type': 'text', 'default': collapse(h1.group(2)).replace('25 years', '{years} years')},
                   {'key': 'hero_title_accent', 'label': 'Headline, last words (in italics)', 'type': 'text', 'default': h1.group(3)}]
m = m.replace(h1.group(0), h1.group(1) + '\n          {{f:hero_title}} <span class="font-serif font-normal italic text-secondary-foreground">{{f:hero_title_accent}}</span>\n        ' + h1.group(4))
intro = re.search(r'(<p class="mx-auto mt-6 max-w-2xl animate-fade-up[^"]*">)\s*(.*?)\s*(</p>)', m, re.S)
FIELDS['about'].append({'key': 'hero_intro', 'label': 'Intro under the headline', 'type': 'textarea', 'default': collapse(intro.group(2))})
m = m.replace(intro.group(0), intro.group(1) + '\n          {{f:hero_intro}}\n        ' + intro.group(3))
FIELDS['about'] += [{'key': 'years', 'label': 'Years of experience', 'type': 'number', 'default': '25', 'help': 'Shown as "25+ years" on the Home and About pages.'},
                    {'key': 'people', 'label': 'People served (in lakh)', 'type': 'number', 'default': '5', 'help': 'Shown as "5 lakh+ people served".'},
                    {'key': 'founded', 'label': 'Year founded', 'type': 'number', 'default': '2006'}]
m = sub(m, '<p class="text-2xl font-extrabold text-primary-ink">25+ years</p>', '<p class="text-2xl font-extrabold text-primary-ink">{{years}}+ years</p>')
m = sub(m, '<p class="text-2xl font-extrabold text-secondary-foreground">5 lakh+</p>', '<p class="text-2xl font-extrabold text-secondary-foreground">{{people}} lakh+</p>')
m = sub(m, '<span data-count="25">25</span>+', '<span data-count="{{years}}">{{years}}</span>+')
m = sub(m, '<span data-count="5">5</span> lakh+', '<span data-count="{{people}}">{{people}}</span> lakh+')
m = sub(m, 'Come and see what 25 years of experience feels like', 'Come and see what {{years}} years of experience feels like')
story = re.search(r'(<div class="mt-8 space-y-5 text-lg leading-relaxed text-muted-foreground)(">)(.*?)(\n          </div>)', m, re.S)
paras = re.findall(r'<p>\s*(.*?)\s*</p>', story.group(3), re.S)
for p in paras:
    p = collapse(strip_classes(p)).replace('2006', '{founded}').replace('25 years', '{years} years').replace('5 lakh', '{people} lakh')
    defaults['about_story'].append(p)
m = m.replace(story.group(0), story.group(1) + ' [&_strong]:font-semibold [&_strong]:text-foreground [&_a]:font-semibold [&_a]:text-primary-ink [&_a]:underline-offset-4 hover:[&_a]:underline' + story.group(2) + '\n            {{about_story}}' + story.group(4))
assert not re.search(r'25 years|5 lakh|2006', re.sub(r'<[^>]+>', '', m)), 'numbers left in about'
mains['about.html'] = m

# ---------------------------------------------------------------- booking
m = common(mains['booking.html'], 'booking')
h1 = re.search(r'(<h1 class="[^"]*">)\s*(.*?) <span class="font-serif font-normal italic text-secondary-foreground">(.*?)</span>\s*(</h1>)', m, re.S)
FIELDS['booking'] = [{'key': 'hero_title', 'label': 'Headline', 'type': 'text', 'default': collapse(h1.group(2))},
                     {'key': 'hero_title_accent', 'label': 'Headline, last word (in italics)', 'type': 'text', 'default': h1.group(3)}]
m = m.replace(h1.group(0), h1.group(1) + '\n          {{f:hero_title}} <span class="font-serif font-normal italic text-secondary-foreground">{{f:hero_title_accent}}</span>\n        ' + h1.group(4))
intro = re.search(r'(<p class="mx-auto mt-6 max-w-2xl animate-fade-up[^"]*">)\s*(.*?)\s*(</p>)', m, re.S)
FIELDS['booking'].append({'key': 'hero_intro', 'label': 'Intro under the headline', 'type': 'textarea', 'default': collapse(intro.group(2))})
m = m.replace(intro.group(0), intro.group(1) + '\n          {{f:hero_intro}}\n        ' + intro.group(3))
how = re.search(r'(<h2 class="text-2xl font-bold tracking-tight md:text-3xl">How it works</h2>\s*<ol[^>]*>)(.*?)(</ol>)', m, re.S)
hsteps = re.findall(r'<div><h3 class="font-semibold">(.*?)</h3><p class="mt-1 text-sm text-muted-foreground">(.*?)</p></div>', how.group(2), re.S)
assert len(hsteps) == 4, len(hsteps)
new_ol = how.group(2)
for i, (title, text) in enumerate(hsteps, 1):
    FIELDS['booking'] += [{'key': f'step{i}_title', 'label': f'"How it works", step {i}: title', 'type': 'text', 'default': placeholders(title)},
                          {'key': f'step{i}_text', 'label': f'"How it works", step {i}: text', 'type': 'textarea', 'default': placeholders(strip_classes(text)).replace('remaining {advance}%', 'remaining {remaining}%')}]
    new_ol = new_ol.replace(f'<div><h3 class="font-semibold">{title}</h3><p class="mt-1 text-sm text-muted-foreground">{text}</p></div>',
                            f'<div><h3 class="font-semibold">{{{{f:step{i}_title}}}}</h3><p class="mt-1 text-sm text-muted-foreground [&_a]:font-semibold [&_a]:text-primary-ink [&_a]:underline-offset-4 hover:[&_a]:underline">{{{{f:step{i}_text}}}}</p></div>')
m = m.replace(how.group(0), how.group(1) + new_ol + how.group(3))
# Prices in the activity checkboxes
for key, price in [('rafting_12', '₹520'), ('rafting_16', '₹720'), ('rafting_26', '₹1,200'), ('rafting_36', '₹2,400')]:
    m = sub(m, f'<span class="tabular-nums">{price}</span>', f'<span class="tabular-nums">{{{{p:{key}}}}}</span>')
# Payment rules
m = sub(m, 'For example, for a ₹4,000 bungee jump you pay ₹2,000 now.', 'For example, for a {{p:bungee}} bungee jump you pay {{advance_example}} now.')
m = sub(m, 'The remaining 50% is paid', 'The remaining {{remaining}}% is paid', count=None)
m = sub(m, '50% advance', '{{advance}}% advance', count=None)
m = sub(m, 'Pay 50% in advance', 'Pay {{advance}}% in advance', count=None)
m = sub(m, 'pay 50% of the price', 'pay {{advance}}% of the price', count=None)
m = sub(m, 'text-primary-foreground">50%</span>', 'text-primary-foreground">{{advance}}%</span>')
m = sub(m, '36 hours', '{{refund_hours}} hours', count=None)
m = sub(m, 'src="{{asset}}/upi-qr.svg"', 'src="{{qr_image}}"')
m = sub(m, 'href="{{asset}}/upi-qr.png"', 'href="{{qr_download}}"')
text_only = re.sub(r'<[^>]+>', '', m)
assert not re.search(r'₹|50%|36 hours|8755542743', text_only), [x for x in ['₹', '50%', '36 hours'] if x in text_only]
mains['booking.html'] = m

# ---------------------------------------------------------------- contact
m = common(mains['contact.html'], 'contact')
h1 = re.search(r'(<h1 class="[^"]*">)\s*(.*?) <span class="font-serif font-normal italic text-secondary-foreground">(.*?)</span>\s*(</h1>)', m, re.S)
FIELDS['contact'] = [{'key': 'hero_title', 'label': 'Headline', 'type': 'text', 'default': collapse(h1.group(2))},
                     {'key': 'hero_title_accent', 'label': 'Headline, last words (in italics)', 'type': 'text', 'default': h1.group(3)}]
m = m.replace(h1.group(0), h1.group(1) + '\n          {{f:hero_title}} <span class="font-serif font-normal italic text-secondary-foreground">{{f:hero_title_accent}}</span>\n        ' + h1.group(4))
intro = re.search(r'(<p class="mx-auto mt-6 max-w-2xl animate-fade-up[^"]*">)\s*(.*?)\s*(</p>)', m, re.S)
FIELDS['contact'].append({'key': 'hero_intro', 'label': 'Intro under the headline', 'type': 'textarea', 'default': collapse(intro.group(2))})
m = m.replace(intro.group(0), intro.group(1) + '\n          {{f:hero_intro}}\n        ' + intro.group(3))
FIELDS['contact'].append({'key': 'opening_hours', 'label': 'Opening hours', 'type': 'textarea', 'default': '',
                          'help': 'Optional. When filled in, shown as "Opening hours" with your address and phone numbers, e.g. "Every day, 8 AM – 8 PM".'})
# Opening hours card goes right after the "Visit our office" card, built the same way
m = sub(m, 'Near Shiv Mandir, Badrinath Highway,<br />Shivpuri, Uttarakhand', '{{street}},<br />{{town}}, {{region}}')
visit = re.search(r'\n\s*<div class="(reveal flex gap-4 rounded-2xl[^"]*)">\s*<span class="([^"]*)"><svg.*?</svg></span>\s*<div>\s*<h2 class="font-semibold">Visit our office</h2>.*?</div>\s*</div>', m, re.S)
assert visit, 'visit card'
defaults['contact_card'] = {'card': visit.group(1), 'icon_wrap': visit.group(2)}
m = m.replace(visit.group(0), visit.group(0) + '{{opening_hours_card}}')
mains['contact.html'] = m

# ---------------------------------------------------------------- 404
mains['404.html'] = common(mains['404.html'], '404')

# ---------------------------------------------------------------- SEO titles and descriptions (editable per page)
for name, key in [('index.html', 'home'), ('about.html', 'about'), ('booking.html', 'booking'), ('contact.html', 'contact')]:
    p = pages[name]
    title = htmllib.unescape(re.search(r'<title>(.*?)</title>', p).group(1))
    desc = htmllib.unescape(re.search(r'<meta name="description" content="(.*?)"', p).group(1))
    desc = desc.replace('+91 97623 88871', '{phone}').replace('+91 87555 42743', '{whatsapp}').replace('adventurepark661@gmail.com', '{email}')
    desc = desc.replace('₹520', '{rafting_12}').replace('50%', '{advance}%').replace('36 hours', '{refund_hours} hours').replace('2006', '{founded}').replace('25+', '{years}+').replace('5 lakh', '{people} lakh')
    FIELDS[key] += [{'key': 'seo_title', 'label': 'Title in Google and browser tabs', 'type': 'text', 'default': title, 'group': 'search'},
                    {'key': 'seo_description', 'label': 'Description in Google and link previews', 'type': 'textarea', 'default': desc, 'group': 'search'}]
    defaults['seo'][key] = {'og_image_alt': htmllib.unescape(re.search(r'<meta property="og:image:alt" content="(.*?)"', p).group(1))}
defaults['fields'] = FIELDS

# ---------------------------------------------------------------- structured data templates
schema_dir = OUT / 'schema'; schema_dir.mkdir(parents=True, exist_ok=True)
OFFER_KEYS = {'River rafting – 12 km': 'rafting_12', 'River rafting – 16 km': 'rafting_16', 'River rafting – 26 km': 'rafting_26',
              'River rafting – 36 km': 'rafting_36', 'Bungee jumping': 'bungee', 'Zip line – students': 'zip_student',
              'Zip line – adults': 'zip_adult', 'Hotel room – non-AC': 'room_nonac', 'Hotel room – AC': 'room_ac'}
RANGE_KEYS = {'quad or triple': 'camp_shared', 'double sharing': 'camp_double'}


def tokenize_json(node, path=''):
    if isinstance(node, dict):
        if node.get('@type') == 'Offer':
            key = next((v for k, v in OFFER_KEYS.items() if node['name'].startswith(k)), None)
            rkey = next((v for k, v in RANGE_KEYS.items() if k in node['name']), None)
            if key:
                node['price'] = '{{n:' + key + '}}'
                node['priceSpecification']['price'] = '{{n:' + key + '}}'
            elif rkey:
                node['priceSpecification']['minPrice'] = '{{n:' + rkey + '_min}}'
                node['priceSpecification']['maxPrice'] = '{{n:' + rkey + '_max}}'
            else:
                raise AssertionError('offer without a setting: ' + node['name'])
        if 'FAQPage' in (node.get('@type') if isinstance(node.get('@type'), list) else [node.get('@type')]):
            node['mainEntity'] = '__FAQ__'
        for k, v in list(node.items()):
            node[k] = tokenize_json(v, path + '/' + k)
        return node
    if isinstance(node, list):
        return [tokenize_json(v, path) for v in node]
    if isinstance(node, str):
        s = node
        for page, key in [('about.html', 'about'), ('booking.html', 'booking'), ('contact.html', 'contact')]:
            s = s.replace(SITE + '/' + page, '{{url:' + key + '}}')
        s = s.replace(SITE + '/assets/', '{{asset}}/').replace(SITE + '/', '{{url:home}}').replace(SITE, '{{url:home}}')
        s = s.replace('+91-97623-88871', '{{tel_dash_main}}').replace('+91-87555-42743', '{{tel_dash_wa}}')
        s = s.replace('+91 97623 88871', '{{phone_main}}').replace('+91 87555 42743', '{{phone_wa}}')
        s = s.replace('https://wa.me/918755542743', '{{wa_plain}}').replace('adventurepark661@gmail.com', '{{email}}')
        s = s.replace('https://maps.app.goo.gl/CKrMGT2h8CRNrcmi6', '{{map_url}}').replace('https://www.instagram.com/adventure_park771/', '{{instagram_url}}')
        s = s.replace('Near Shiv Mandir, Badrinath Highway', '{{street}}')
        if path.endswith('/priceRange') and s == '₹520–₹4,000':
            s = '{{price_range}}'
        s = s.replace('₹1,500–₹2,200 per person', '{{p:camp_shared_min}}–{{p:camp_double_max}} per person')
        s = s.replace('Busy days: ₹1,600–₹1,800', 'Busy days: {{p:room_nonac_busy_min}}–{{p:room_nonac_busy_max}}')
        s = s.replace('Busy days: ₹2,000–₹2,200', 'Busy days: {{p:room_ac_busy_min}}–{{p:room_ac_busy_max}}')
        s = s.replace('from ₹520', 'from {{p:rafting_12}}').replace('since 2006', 'since {{founded}}')
        assert '₹' not in s, s
        if path.endswith('/foundingDate'):
            s = '{{founded}}'
        s = s.replace('25 years', '{{years}} years').replace('25+ years', '{{years}}+ years').replace('5 lakh', '{{people}} lakh').replace('in 2006', 'in {{founded}}')
        s = s.replace('50% advance', '{{advance}}% advance').replace('36 hours', '{{refund_hours}} hours')
        return s
    return node


for name, key in [('index.html', 'home'), ('about.html', 'about'), ('booking.html', 'booking'), ('contact.html', 'contact')]:
    data = json.loads(re.search(r'<script type="application/ld\+json">(.*?)</script>', pages[name], re.S).group(1))
    data = tokenize_json(data)
    text = json.dumps(data, ensure_ascii=False, indent=1)
    for v in ['adventureprk', '97623', '87555', 'adventurepark661', 'goo.gl', '₹520']:
        assert v not in text, f'schema {key}: {v} left'
    (schema_dir / f'{key}.json').write_text(text + '\n')

# ---------------------------------------------------------------- write parts and defaults
parts = OUT / 'parts'; parts.mkdir(parents=True, exist_ok=True)
(parts / 'header.html').write_text(header)
(parts / 'footer.html').write_text(footer)
for name, key in [('index.html', 'home'), ('about.html', 'about'), ('booking.html', 'booking'), ('contact.html', 'contact'), ('404.html', '404')]:
    (parts / f'{key}.html').write_text(mains[name] + '\n')
(OUT / 'inc').mkdir(exist_ok=True)
(OUT / 'inc' / 'defaults.json').write_text(json.dumps(defaults, ensure_ascii=False, indent=1) + '\n')

# Every token used, for the PHP side to check against
tokens = sorted(set(re.findall(r'\{\{([^}]+)\}\}', header + footer + ''.join(mains.values()))))
print('tokens:', ', '.join(t for t in tokens if not t.startswith(('f:', 'p:', 'photo'))))
print('fields:', {k: len(v) for k, v in FIELDS.items()}, '| faq', len(defaults['faq']), '| story paragraphs', len(defaults['about_story']))
print('gallery sizes:', GALLERY_SIZES)

# ---------------------------------------------------------------- assets
dst = OUT / 'assets'
if dst.exists(): shutil.rmtree(dst)
shutil.copytree(SRC / 'assets', dst, ignore=shutil.ignore_patterns('tailwind.css', 'preview.css', 'preview.js', 'og-image.png'))
shutil.copy(SRC / 'assets' / 'og-image.png', dst / 'og-image.png')
shutil.copy(SRC / 'favicon.ico', dst / 'favicon.ico')
print('copied assets')

# ---------------------------------------------------------------- llms.txt and robots.txt templates ({placeholders} as in page text)
llms = read('llms.txt')
faq_start = llms.index('## FAQ')
llms = llms[:faq_start] + '## FAQ\n\n{faq}\n'
reps = [
    (SITE + '/about.html', '{url:about}'), (SITE + '/booking.html', '{url:booking}'), (SITE + '/contact.html', '{url:contact}'),
    ('](' + SITE + '/)', ']({home})'),
    ('https://wa.me/918755542743', '{whatsapp_link}'), ('+91 97623 88871', '{phone}'), ('+91 87555 42743', '{whatsapp}'),
    ('adventurepark661@gmail.com', '{email}'), ('8755542743@ybl', '{upi_id}'), ('M/S ADVENTURE PARK', '{upi_name_caps}'),
    ('https://maps.app.goo.gl/CKrMGT2h8CRNrcmi6', '{map}'), ('https://www.instagram.com/adventure_park771/', '{instagram_url}'), ('@adventure_park771', '@{instagram}'),
    ('25+ years', '{years}+ years'), ('5 lakh', '{people} lakh'), ('since 2006', 'since {founded}'),
    ('about 1–1.5 hours: ₹520', 'about 1–1.5 hours: {rafting_12}'), ('about 1.5–2 hours: ₹720', 'about 1.5–2 hours: {rafting_16}'),
    ('about 2–2.5 hours: ₹1,200', 'about 2–2.5 hours: {rafting_26}'), ('about 2.5–3 hours: ₹2,400', 'about 2.5–3 hours: {rafting_36}'),
    ('109 m (360 ft), ₹4,000', '109 m (360 ft), {bungee}'), ('₹1,800 per person for students, ₹2,000 per person for adults', '{zip_student} per person for students, {zip_adult} per person for adults'),
    ('for a ₹4,000 bungee jump, the advance is ₹2,000', 'for a {bungee} bungee jump, the advance is {advance_example}'),
    ('Quad or triple sharing: ₹1,500–₹1,800', 'Quad or triple sharing: {camp_shared_min}–{camp_shared_max}'), ('Double sharing: ₹1,800–₹2,200', 'Double sharing: {camp_double_min}–{camp_double_max}'),
    ('₹1,200 per room per night (busy days ₹1,600–₹1,800)', '{room_nonac} per room per night (busy days {room_nonac_busy_min}–{room_nonac_busy_max})'),
    ('₹1,600 per room per night (busy days ₹2,000–₹2,200)', '{room_ac} per room per night (busy days {room_ac_busy_min}–{room_ac_busy_max})'),
    ('a 50% advance', 'a {advance}% advance'), ('remaining 50%', 'remaining {remaining}%'), ('36 hours', '{refund_hours} hours'),
]
for old, new in reps:
    llms = llms.replace(old, new)
llms = llms.replace(SITE + '/', '{home}')
left = [v for v in ['₹', '97623', '87555', 'adventureprk', '36 hours', 'adventure_park771', '8755542743', 'gmail'] if v in llms]
assert left == ['₹'] and llms.count('₹') == 1, (left, [l for l in llms.splitlines() if '₹' in l])  # "INR, ₹" stays
assert '50%' in llms and llms.count('50%') == 1  # children pay 50%
(OUT / 'inc' / 'llms.txt').write_text(llms)
robots = read('robots.txt').replace(SITE + '/llms.txt', '{home}llms.txt').replace('Sitemap: ' + SITE + '/sitemap.xml', 'Sitemap: {home}wp-sitemap.xml').replace('Disallow: /preview.html\n', 'Disallow: /wp-admin/\nAllow: /wp-admin/admin-ajax.php\n')
assert 'adventureprk' not in robots and '{home}wp-sitemap.xml' in robots
(OUT / 'inc' / 'robots.txt').write_text(robots)

# ---------------------------------------------------------------- sw.js for WordPress (filled in by inc/routes.php)
sw = read('sw.js')
sw = re.sub(r"(// ---- Filled in when the site is built[^\n]*\n).*?(// ---- end of build section ----)",
            lambda m: m.group(1) + "const VERSION = '__VERSION__';\nconst ASSETS = __ASSETS__;\n" + m.group(2), sw, flags=re.S)
sw = sub(sw, "const PAGES = ['/', '/about.html', '/booking.html', '/contact.html'];",
         "const PAGES = __PAGES__;\nconst ASSET_PATH = '__ASSET_PATH__'; // the theme's assets folder\nconst LOGO = '__LOGO__';")
sw = sub(sw, "await images.match('/assets/logo.png')", 'await images.match(LOGO)')
sw = sub(sw, "await images.add('/assets/logo.png')", 'await images.add(LOGO)')
sw = sub(sw, "if (u.origin !== location.origin || !u.pathname.startsWith('/assets/')) continue;",
         "if (u.origin !== location.origin || !/\\.(png|jpe?g|webp|svg)$/.test(u.pathname)) continue;")
sw = sub(sw, """  if (request.method !== 'GET' || url.origin !== location.origin) return;""",
         """  if (request.method !== 'GET' || url.origin !== location.origin) return;
  // WordPress itself: the dashboard, logins, the Customizer preview, feeds and searches always go to the website
  if (/^\\/(wp-admin|wp-login\\.php|wp-json|xmlrpc\\.php|wp-cron\\.php)|\\/feed\\/?$/.test(url.pathname.replace(SCOPE, '/'))) return;
  if (/(^|&)(customize_|preview|s=|p=|page_id=|doing_wp_cron)/.test(url.search.slice(1))) return;""")
sw = sub(sw, "  if (url.pathname.startsWith('/assets/')) return event.respondWith(asset(request));",
         "  if (url.pathname.startsWith(ASSET_PATH)) return event.respondWith(asset(request));")
sw = sub(sw, "const PAGES = __PAGES__;", "const PAGES = __PAGES__;\nconst SCOPE = new URL(self.registration.scope).pathname; // where WordPress is installed, usually /")
sw = sub(sw, "// Service worker: keeps a copy of the site", "// Service worker (served by the Adventure Park WordPress theme at /sw.js): keeps a copy of the site")
(OUT / 'inc' / 'sw.js').write_text(sw)
print('wrote llms.txt, robots.txt and sw.js templates')

# ---------------------------------------------------------------- WordPress versions of a few assets
site_js = (OUT / 'assets' / 'site.js').read_text()
old = """  if ('serviceWorker' in navigator && window.isSecureContext) {"""
new = """  // On WordPress: only for visitors (logged-in users see the live pages), and sw.js lives at the site's root
  if ('serviceWorker' in navigator && window.isSecureContext && document.body.classList.contains('logged-in')) {
    navigator.serviceWorker.getRegistrations().then((regs) => regs.forEach((reg) => reg.unregister()));
  } else if ('serviceWorker' in navigator && window.isSecureContext) {"""
site_js = sub(site_js, old, new)
site_js = sub(site_js, "navigator.serviceWorker.register('sw.js')", "navigator.serviceWorker.register(document.body.dataset.sw || 'sw.js')")
(OUT / 'assets' / 'site.js').write_text(site_js)
(OUT / 'assets' / 'sw-off.js').write_text("""// In the WordPress dashboard: remove the visitors' offline copy (sw.js) from this browser, so the
// site owner always sees the live pages and the Customizer preview.
if ('serviceWorker' in navigator) {
  navigator.serviceWorker.getRegistrations().then((regs) => regs.forEach((reg) => reg.unregister()));
  if (window.caches) caches.keys().then((names) => names.forEach((name) => caches.delete(name)));
}
""")
print('patched site.js, wrote sw-off.js')
(OUT / 'assets' / 'content.css').write_text("""/* Text of pages and posts you add yourself in WordPress (page.php, index.php), in the site's style */
.ap-content > * + * { margin-top: 1.1em; }
.ap-content h2 { margin-top: 1.8em; font-size: 1.6rem; line-height: 1.25; font-weight: 700; letter-spacing: -0.01em; color: var(--foreground); }
.ap-content h3 { margin-top: 1.5em; font-size: 1.25rem; line-height: 1.3; font-weight: 700; color: var(--foreground); }
.ap-content a { color: color-mix(in oklab, var(--primary) 76%, black); font-weight: 600; text-underline-offset: 4px; }
.dark .ap-content a { color: var(--primary); }
.ap-content strong { font-weight: 600; color: var(--foreground); }
.ap-content ul { list-style: disc; padding-left: 1.4em; }
.ap-content ol { list-style: decimal; padding-left: 1.4em; }
.ap-content li + li { margin-top: 0.4em; }
.ap-content img { border-radius: 12px; height: auto; }
.ap-content blockquote { border-left: 3px solid var(--border); padding-left: 1em; font-style: italic; }
.ap-content table { width: 100%; border-collapse: collapse; font-size: 0.95em; }
.ap-content th, .ap-content td { border-bottom: 1px solid var(--border); padding: 0.5em 0.75em 0.5em 0; text-align: left; }
""")
print('wrote content.css')

# ---------------------------------------------------------------- the theme's own stylesheet (Tailwind, scanning the theme's files)
tw = read('assets/tailwind.css')
A = str(OUT)
tw = sub(tw, '@source "../*.html";\n@source "./*.js";', f'@source "{A}/parts/*.html";\n@source "{A}/*.php";\n@source "{A}/page-templates/*.php";\n@source "{A}/inc/*.php";\n@source "{A}/inc/defaults.json";\n@source "{A}/assets/*.js";')
tw = sub(tw, '@import "./fonts.css";', f'@import "{A}/assets/fonts.css";')
# Classes the theme builds in PHP (menu item and FAQ delays) that no file spells out in full
safe = ' '.join([f'[--i:{i}]' for i in range(6)] + [f'[--d:{d}ms]' for d in range(50, 1001, 50)])
tw = tw.replace(f'@source "{A}/assets/*.js";', f'@source "{A}/assets/*.js";\n@source inline("{safe}");')
TAILWIND = SRC / 'node_modules' / '.bin' / 'tailwindcss'
assert TAILWIND.exists(), 'Tailwind is missing: run  npm install --no-save tailwindcss@4.3.3 @tailwindcss/cli@4.3.3  in ' + str(SRC)
src_file = SRC / '.theme-tailwind.css'  # next to node_modules, so @import "tailwindcss" is found
src_file.write_text(tw)
try:
    r = subprocess.run([str(TAILWIND), '-i', str(src_file), '-o', str(OUT / 'assets' / 'styles.css'), '--minify'], capture_output=True, text=True, cwd=str(SRC))
finally:
    src_file.unlink()
assert r.returncode == 0, r.stderr
print('built theme styles.css', (OUT / 'assets' / 'styles.css').stat().st_size // 1024, 'KB')

# ---------------------------------------------------------------- the .zip to upload in WordPress (Appearance → Themes → Add New Theme → Upload Theme)
if '--zip' in sys.argv:
    target = OUT.parent / (OUT.name + '.zip')
    with zipfile.ZipFile(target, 'w', zipfile.ZIP_DEFLATED) as z:
        for f in sorted(OUT.rglob('*')):
            if f.is_file() and f.name != '.DS_Store':
                z.write(f, Path(OUT.name) / f.relative_to(OUT))
    print('wrote', target, target.stat().st_size // 1024, 'KB')
