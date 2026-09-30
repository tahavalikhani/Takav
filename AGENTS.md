# Rules for AI agents working on this repository

This repository is a **classic WordPress theme** (PHP templates, not a block theme). The installable theme is the folder `theme/takav/`. Everything else (tests, tools, docs) is development-only and must never be shipped inside the theme.

## 1. The theme ZIP must be installable through WordPress

- WordPress requires `style.css` exactly **one folder deep** in the ZIP: `takav.zip → takav/style.css`. Never `style.css` at the ZIP root, never deeper (e.g. `Takav-main/theme/takav/style.css`).
- Build it only from inside `theme/`: `cd theme && zip -rq -X ../dist/takav.zip takav -x '*.DS_Store'` (or `python3 tools/package.py`).
- GitHub's "Code → Download ZIP" is **not** an installable theme. Never tell the owner to upload it.
- Do not commit the ZIP. `dist/` is ignored.
- Keep the header comment at the top of `theme/takav/style.css` (`Theme Name`, `Version`, `Requires at least`, `Requires PHP`, `Text Domain`). Bump `Version` whenever the theme changes.
- The theme must contain `style.css`, `index.php` and `functions.php`. Keep `screenshot.png` (1200×900) in sync with the real homepage.

## 2. The homepage must work under every WordPress setting

- The collection homepage lives in `front-page.php`. Do not rely on WordPress picking that file by itself. The owner's site may use any **Settings → Reading** option, including "A static page" with no homepage selected. In that case WordPress treats the root URL as the blog index and would render `index.php` ("Hello world!").
- Use `takav_is_home_view()` (in `theme/takav/inc/routes.php`) instead of `is_front_page()` for anything homepage-related. The `template_include` filter routes that case to `front-page.php`. Do not remove it.
- A real blog page (`page_for_posts`) must keep showing posts through `index.php`.
- Custom routes (`/collection/<id>/`, `/collection-one/`, `/cart/`) must work with both pretty and plain permalinks, and without the owner visiting Settings → Permalinks.
- The site runs **WooCommerce**. Never break with it active, and never assume a page slug like `cart` is free.

## 3. Things the owner decided. Do not change them without being asked

- **Search engines must be allowed.** Never add `noindex`, a `wp_robots` filter that blocks indexing, or `blog_public = 0`. (WooCommerce itself marks its cart and checkout pages `noindex`; that is expected. Leave it.)
- **Ordering is guest-only, in short steps.** No sign-up, no account creation, no login wall. The checkout is: ۱ name + mobile (email optional) → ۲ address → ۳ delivery + payment → ۴ tick page with the tracking number. Do not turn it back into one long form, and do not add fields the owner did not ask for.
- **Font: Peyda** (Light 300, Regular 400, Medium 500) from `theme/takav/assets/fonts/Peyda-*.ttf`, with Vazirmatn as the fallback. The Peyda files are licensed. This repository is **public**, so never commit them (they are in `.gitignore`). They are added locally only when building the ZIP for the owner.
- **Payment is ZarinPal only** (official plugin, gateway id `WC_ZPal`). Never offer pay on delivery, bank transfer or cheque; the theme filters them out.
- **Delivery is Tipax, paid on delivery (پس‌کرایه).** The theme's `takav_tipax` shipping method has cost 0 and is labelled «تیپاکس · پس‌کرایه»; when it is available it is the only option.
- **Pre-order:** about 20 days (setting), progress posted on Telegram. The link and days live in wp-admin → «سفارشات» → «تنظیمات پیش‌فروش و تلگرام» (options `takav_telegram_url`, `takav_preorder_days`).
- **wp-admin → «سفارشات»** (`inc/admin-orders.php`) is the owner's order screen. Keep its paid/unpaid filters, CSV export and status buttons working, and keep reading orders through `wc_get_orders()` (HPOS-safe).
- **eNamad seal** (`takav_enamad_seal()` in `inc/pages.php`) is printed in the footer exactly as eNamad issued it. Never edit, reformat, escape, re-quote, lazy-load, self-host or move that markup into editor content: eNamad treats changes as tampering. Keep `referrerpolicy='origin'` and the `code` attribute. Tests skip it in axe and the broken-image check because trustseal.enamad.ir is unreachable from outside Iran.
- **Store pages** `/about/`, `/contact/`, `/terms/` (`info-page.php`) are required by ZarinPal and eNamad. Contact details come only from the owner (`takav_contact_*` options; defaults: phone 09390709672, Instagram https://www.instagram.com/takavbrand). Takav is **online-only: never show a shop address or postcode**. Never write policies (returns, privacy, refunds) the owner has not given; the terms page states only decided facts. A real WordPress page with the same slug wins.
- **Do not change the design** (layout, colors, spacing, copy, images) unless the task explicitly asks for it. A compatibility fix must look identical before and after.
- Persian, RTL (`lang="fa-IR" dir="rtl"`). Black with orange accents.

## 4. How ordering is built (keep it this way)

- All shop code lives in `theme/takav/inc/shop.php` (admin screen: `inc/admin-orders.php`); templates are `page-cart.php`, `checkout.php`, `order-received.php` and `page-track.php`.
- Orders go through **WooCommerce's own checkout** (`WC_Checkout::process_checkout`, triggered by posting `woocommerce_checkout_place_order` with the `woocommerce-process_checkout` nonce and standard `billing_*` field names). Never create orders by hand and never bypass WooCommerce's payment step: payment plugins (Zarinpal etc.), stock and emails depend on it.
- The theme renders WooCommerce's cart and checkout pages itself (`template_include`), so it works whether those pages contain blocks or shortcodes. Leave `order-pay` to WooCommerce, and never redirect `order-received` or `order-pay`: payment gateways return there.
- Products are matched by SKU `takav-<catalog id>` (`takav_wc_product()`). A product is only for sale when it is **published**, has a price and is in stock.
- WooCommerce 11 needs `variation_id` for sized products; the theme looks it up from the chosen attributes before WooCommerce handles the request. Keep that.
- Normalise phone numbers and postcodes (Persian and Arabic digits → Latin) before validating or storing. Mobile numbers are stored as `09XXXXXXXXX`.
- Persian digits are for storefront display only (`formatted_woocommerce_price`, `takav_fa_digits()`), never in wp-admin, and never by running a replace over HTML.
- Every WooCommerce call must be guarded (`takav_shop_ready()`): the theme must still render with WooCommerce deactivated.
- Before using a WooCommerce function, check that it exists in the installed WooCommerce version's source. Do not guess function names.

## 5. WordPress coding rules

- Support PHP 7.4+ and WordPress 6.5+. No PHP 8-only syntax (no `match`, no named arguments, no `str_contains`, no union types).
- Start every PHP file with `defined('ABSPATH') || exit;`.
- Load CSS and JS only with `wp_enqueue_style` / `wp_enqueue_script`. Build URLs with `get_template_directory_uri()` / `home_url()`. Never hardcode a domain or `/wp-content/...`.
- Escape all output (`esc_html`, `esc_attr`, `esc_url`).
- Templates must call `get_header()` / `get_footer()`, and `header.php` / `footer.php` must call `wp_head()`, `wp_body_open()` and `wp_footer()`.
- No external CDNs. Everything the theme needs lives inside `theme/takav/`.
- Nothing may require Node, a build step or Composer on the host. The theme must run straight from the ZIP.

## 6. Check before you say it is done

1. `php -l` on every changed PHP file.
2. `unzip -l dist/takav.zip | head` shows `takav/style.css` near the top.
3. Install the ZIP on a real WordPress (the WordPress Playground CLI in `devDependencies` works) using the normal theme upload. Then check `/`, `/collection/hoodie/`, `/collection/pants/`, `/collection-one/`, `/cart/`, `/track/`, `/about/`, `/contact/` and `/terms/`. Test with Settings → Reading on "Your latest posts" **and** on "A static page" with no homepage chosen. No "Hello world!", no PHP errors, no `noindex`.
4. Run `corepack pnpm preview` and `corepack pnpm test`. Both must pass. The test places real guest orders (with and without JavaScript) on a WooCommerce test store and checks the tick page and the tracking page.
5. Also check: a product with sizes, a failed payment page, and the whole site with WooCommerce **deactivated** (no PHP errors).
6. Report honestly what you tested and what you could not test.
