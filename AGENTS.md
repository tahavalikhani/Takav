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

- **Search engines must be allowed.** Never add `noindex`, a `wp_robots` filter that blocks indexing, or `blog_public = 0`.
- **Font: Peyda** (Light 300, Regular 400, Medium 500) from `theme/takav/assets/fonts/Peyda-*.ttf`, with Vazirmatn as the fallback. The Peyda files are licensed. This repository is **public**, so never commit them (they are in `.gitignore`). They are added locally only when building the ZIP for the owner.
- **Do not change the design** (layout, colors, spacing, copy, images) unless the task explicitly asks for it. A compatibility fix must look identical before and after.
- Persian, RTL (`lang="fa-IR" dir="rtl"`). Black with orange accents.

## 4. WordPress coding rules

- Support PHP 7.4+ and WordPress 6.5+. No PHP 8-only syntax (no `match`, no named arguments, no `str_contains`, no union types).
- Start every PHP file with `defined('ABSPATH') || exit;`.
- Load CSS and JS only with `wp_enqueue_style` / `wp_enqueue_script`. Build URLs with `get_template_directory_uri()` / `home_url()`. Never hardcode a domain or `/wp-content/...`.
- Escape all output (`esc_html`, `esc_attr`, `esc_url`).
- Templates must call `get_header()` / `get_footer()`, and `header.php` / `footer.php` must call `wp_head()`, `wp_body_open()` and `wp_footer()`.
- No external CDNs. Everything the theme needs lives inside `theme/takav/`.
- Nothing may require Node, a build step or Composer on the host. The theme must run straight from the ZIP.

## 5. Check before you say it is done

1. `php -l` on every changed PHP file.
2. `unzip -l dist/takav.zip | head` shows `takav/style.css` near the top.
3. Install the ZIP on a real WordPress (the WordPress Playground CLI in `devDependencies` works) using the normal theme upload. Then check `/`, `/collection/hoodie/`, `/collection/pants/`, `/collection-one/` and `/cart/`. Test with Settings → Reading on "Your latest posts" **and** on "A static page" with no homepage chosen. No "Hello world!", no PHP errors, no `noindex`.
4. Run `corepack pnpm preview` and `corepack pnpm test`. Both must pass.
5. Report honestly what you tested and what you could not test.
