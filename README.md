# TAKAV — First collection

A Persian, RTL WordPress theme for Takav's first hoodie-and-pants collection. Predominantly black with restrained orange accents taken from the actual garments. **This is the design phase: ordering and payments are not connected.**

The storefront is a compact two-product collection. Each product opens a dedicated page with an inline gallery and a local preview cart. A separate animated `/collection-one/` presentation uses the owner's real product photos. Ordering and payment remain inactive.

The supplied theme ZIP and local preview include the owner's Peyda files. Font binaries are excluded from this public repository. For a fresh checkout, place your licensed `Peyda-Light.ttf`, `Peyda-Regular.ttf` and `Peyda-Medium.ttf` in `theme/takav/assets/fonts/`. The theme detects these files and loads them together; otherwise it uses bundled Vazirmatn without broken font requests.

## Install on a WordPress staging site

Upload the `takav-wordpress-theme.zip` deliverable (do **not** upload GitHub's **Code → Download ZIP** archive — it wraps the theme in extra folders and WordPress reports a missing `style.css`) through **نمایش ← پوسته‌ها ← افزودن پوسته تازه ← بارگذاری پوسته**, then activate Takav. Alternatively copy `theme/takav` to `wp-content/themes/takav`.

The homepage uses `front-page.php` with either WordPress reading setting. No WooCommerce, page builder, account, merchant key or eNamad is needed to review this design. This theme does not disable checkout provided by unrelated installed plugins: use a staging site for review.

The theme lets search engines index the site. Make sure **تنظیمات ← خواندن ← از موتورهای جستجو درخواست کن تا محتوای سایت را بررسی نکنند** is unchecked. The generated static preview is still marked `noindex`.

## Local development and checks

Node 22+ and Python 3 are used for development tools only. WordPress hosting needs PHP 7.4+ and WordPress 6.5+; no Node process or asset build is required on the host.

```sh
corepack pnpm install --frozen-lockfile
corepack pnpm exec playwright install chromium
corepack pnpm preview
# In another terminal:
corepack pnpm test
python3 tools/package.py http://127.0.0.1:8898 ./dist
```

Use `TAKAV_BROWSER_CHANNEL=chrome corepack pnpm test` to use installed Chrome. `TAKAV_URL` and `TAKAV_QA_DIR` override the test URL and screenshot directory. Testing does not access a production WordPress instance. `corepack pnpm images` regenerates responsive WebP sizes from preserved originals.

The ZIP contains the `takav/` theme at its root. The portable preview uses the actual WordPress-rendered homepage, product, campaign and cart pages with the same styles, scripts and assets; open its `index.html` or serve its folder with `python3 -m http.server 8899`. The preview cart uses browser storage, so serve the static preview over HTTP to test persistence.

## Content and next phase

Products and confirmed visible details live in `theme/takav/inc/catalog.php`; section copy lives in `front-page.php`. The site wordmark is a typographic treatment of TAKAV, not a reconstruction of the embroidered logo. Original photos remain available from each product gallery.

Prices, size charts, fabric composition, stock, shipping, returns and support details still need the owner's input. No values, badges, ratings, promises or contact details have been invented. The preview cart stays in the browser and creates no reservation or order.

See [design decisions](docs/design-decisions.md), [asset provenance](docs/assets.md) and [backend handoff](docs/backend-handoff.md).

The Persian web playbook supplied by the owner was used as background reading, not as the site's specification. Relevant implementation references: [WordPress theme handbook](https://developer.wordpress.org/themes/) and [Vazirmatn](https://github.com/rastikerdar/vazirmatn). The font's SIL Open Font License is bundled beside the font.
