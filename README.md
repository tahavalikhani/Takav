# TAKAV — First collection

A Persian, RTL WordPress theme for Takav's first hoodie-and-pants collection. Predominantly black with restrained orange accents taken from the actual garments. Customers order through **WooCommerce** as guests: no sign-up, a short checkout in steps, and a tracking number at the end.

The storefront is a compact two-product collection. Each product opens a dedicated page with an inline gallery. A separate animated `/collection-one/` presentation uses the owner's real product photos.

## Ordering (WooCommerce)

- **Products:** on the first wp-admin visit with WooCommerce active, the theme creates «هودی تکاو» and «شلوار تکاو» as **draft** WooCommerce products with SKUs `takav-hoodie` and `takav-pants` and the product photo. The theme finds them by SKU. A product shows «به‌زودی» until it is **published with a price**. Sizes work when the product is made *variable* (e.g. a «سایز» attribute).
- **Cart** (`/cart/` → WooCommerce's cart page): theme design, quantity and remove work without JavaScript.
- **Checkout** (WooCommerce's checkout page, rendered by `checkout.php`): ۱ name + mobile (email optional) → ۲ province, city, address, postcode (optional) → ۳ delivery method, payment method, total → ۴ tick page with the tracking number (`order-received.php`). No account is ever created, whatever WooCommerce's guest-checkout setting says. The form posts WooCommerce's normal checkout fields, so stock, order emails and any payment plugin (Zarinpal, card-to-card, pay on delivery) work. Mobile numbers and postcodes typed with Persian digits are normalised. Without JavaScript all steps show at once and the form still works.
- **Payment:** online through **ZarinPal** only (its official plugin). The theme hides pay-on-delivery, bank transfer and cheque even if they are enabled in WooCommerce. A payment cancelled at the bank brings the customer back to step ۳ with the message and a full cart.
- **Delivery:** the theme adds a «تیپاکس (پس‌کرایه)» shipping method and, on the first wp-admin visit, puts it in an «ایران» zone. When Tipax is available it is the only option; the delivery cost shows as «پس‌کرایه» and adds nothing to the total.
- **Pre-order:** product, cart, checkout, done page, tracking page and the customer email say the order is ready in about N days (default 20). The done page, tracking page and email show a Telegram button when a link is set.
- **wp-admin → «سفارشات»:** every order with name, phone, products and sizes, amount, payment result and ZarinPal reference, status and address. Filters for paid / unpaid-or-failed / preparing / shipped, search by number, name, phone or city, date range, one-click «ارسال شد», and a CSV export for Excel. Its «تنظیمات پیش‌فروش و تلگرام» tab holds the Telegram link and the number of days.
- **First-purchase discount:** about 3 seconds after a first visit a popup offers «تخفیف N٪ برای اولین خرید» with «اعمال کن» and «می‌خوام با قیمت کامل خرید کنم». Applying it lowers every product price by N% for that visitor (full price crossed out, in cart, checkout and the order). Either answer hides the popup for good. A mobile number with an earlier paid order is refused the discount at checkout. On/off and the percent (default 5) are in wp-admin → «سفارشات» → «تنظیمات فروشگاه»; discounted orders are marked in «سفارشات» and the CSV.
- **Store pages** for ZarinPal and eNamad: `/about/` «درباره ما», `/contact/` «تماس با ما» and `/terms/` «قوانین و مقررات» (`info-page.php`), linked from the footer and from checkout step ۳. Takav is online-only, so there is no shop address. The phone (09390709672) and Instagram (takavbrand) are built in and can be changed, with optional email and hours, in wp-admin → «سفارشات» → «تنظیمات فروشگاه»; empty fields are hidden. The terms page only states decided facts (ordering, ZarinPal, pre-order, Tipax, tracking); it has no return or privacy policy. A published WordPress page with the same slug replaces the theme's version.
- **eNamad** seal in the footer on every page (`takav_enamad_seal()` in `inc/pages.php`), printed exactly as issued.
- **The step before the bank** (`order-pay.php`): the payment plugin's redirect to ZarinPal runs before any HTML, so it always works; if ZarinPal refuses (e.g. an inactive merchant code) the customer sees the reason with «تلاش دوباره برای پرداخت», «پیگیری سفارش» and Instagram.
- **Tick page**: large, with three buttons (track order, Instagram, Telegram or back to shop). **Tracking page**: a «چطور کار می‌کند؟» note and a status that refreshes itself every minute while open.
- **Phones** (≤700px): menu button · TAKAV · cart, with a full-width menu (a `<details>`, works without JavaScript). Discounted prices are big and orange with the full price struck through, stacked on narrow cards.
- **Failed payment** gets its own page with a single «پرداخت دوباره» button.
- **Tracking** (`/track/`, footer link «پیگیری سفارش»): tracking number (the WooCommerce order number) + the order's mobile number → status, a three-step progress bar and any «یادداشت به مشتری» the owner adds (e.g. the post tracking code). Rate-limited.
- Without WooCommerce the theme still renders; products show «به‌زودی».

The supplied theme ZIP and local preview include the owner's Peyda files. Font binaries are excluded from this public repository. For a fresh checkout, place your licensed `Peyda-Light.ttf`, `Peyda-Regular.ttf` and `Peyda-Medium.ttf` in `theme/takav/assets/fonts/`. The theme detects these files and loads them together; otherwise it uses bundled Vazirmatn without broken font requests.

## Install

Upload the `takav.zip` deliverable (do **not** upload GitHub's **Code → Download ZIP** archive — it wraps the theme in extra folders and WordPress reports a missing `style.css`) through **نمایش ← پوسته‌ها ← افزودن پوسته تازه ← بارگذاری پوسته**, then activate Takav. Alternatively copy `theme/takav` to `wp-content/themes/takav`.

The homepage uses `front-page.php` with either WordPress reading setting. No WooCommerce, page builder, account, merchant key or eNamad is needed to review this design. This theme does not disable checkout provided by unrelated installed plugins: use a staging site for review.

The theme lets search engines index the site. Make sure **تنظیمات ← خواندن ← از موتورهای جستجو درخواست کن تا محتوای سایت را بررسی نکنند** is unchecked. The generated static preview is still marked `noindex`.

## Local development and checks

Node 22+ and Python 3 are used for development tools only. The test site (`corepack pnpm preview`) installs WooCommerce from WordPress.org and sets up a sample Iranian store (Toman, an Iran shipping zone, pay-on-delivery and card-to-card), so it needs internet access. WordPress hosting needs PHP 7.4+ and WordPress 6.5+; no Node process or asset build is required on the host.

```sh
corepack pnpm install --frozen-lockfile
corepack pnpm exec playwright install chromium
corepack pnpm preview
# In another terminal:
corepack pnpm test
python3 tools/package.py http://127.0.0.1:8898 ./dist
```

Use `TAKAV_BROWSER_CHANNEL=chrome corepack pnpm test` to use installed Chrome. `TAKAV_URL` and `TAKAV_QA_DIR` override the test URL and screenshot directory. Testing does not access a production WordPress instance. `corepack pnpm images` regenerates responsive WebP sizes from preserved originals.

The ZIP contains the `takav/` theme at its root. The portable preview uses the actual WordPress-rendered homepage, product, campaign and cart pages with the same styles, scripts and assets; open its `index.html` or serve its folder with `python3 -m http.server 8899`. The static preview cannot place orders; use WordPress for that.

## Content and next phase

Products and confirmed visible details live in `theme/takav/inc/catalog.php`; section copy lives in `front-page.php`. The site wordmark is a typographic treatment of TAKAV, not a reconstruction of the embroidered logo. Original photos remain available from each product gallery.

Prices, sizes, stock, shipping costs and payment methods are set by the owner in WooCommerce. Size charts, fabric composition, returns and support details still need the owner's input. No values, badges, ratings, promises or contact details have been invented.

See [design decisions](docs/design-decisions.md), [asset provenance](docs/assets.md) and [backend handoff](docs/backend-handoff.md).

The Persian web playbook supplied by the owner was used as background reading, not as the site's specification. Relevant implementation references: [WordPress theme handbook](https://developer.wordpress.org/themes/) and [Vazirmatn](https://github.com/rastikerdar/vazirmatn). The font's SIL Open Font License is bundled beside the font.
