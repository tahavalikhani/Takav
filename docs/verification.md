# Verification — 24 September 2026

Passed against an actual local WordPress **7.1.2** installation in WordPress Playground, PHP **8.3**, using installed Chrome through Playwright. The owner's live WordPress host has not been accessed or changed.

- No horizontal overflow at 320, 390, 600, 768, 1024, 1440 or 1920 pixels.
- Product filters, galleries, original-image links, mobile menu and native FAQ interactions.
- Product modal focus wrapping, Escape dismissal and return to the opening link.
- Local favourites persisted across reload; blocked storage reports failure without pretending to save.
- All image resources load, no JavaScript errors, no failed theme requests, no third-party network requests.
- axe-core WCAG 2 A/AA and 2.1 AA scans: zero detected violations at 390px and 1440px. Automated scanning is not a full accessibility certification.
- Without JavaScript: navigation, both products, detailed content and FAQ remain accessible; enhancement-only controls stay hidden.
- Front page is RTL and noindex; normal WordPress post and 404 templates render with the correct HTTP status.
- Peyda Light/Regular/Medium rendered locally; screenshots visually reviewed on desktop and mobile.

Machine-readable results are in `verification-results.json`. Re-run `tests/storefront.cjs` with the commands in the README. There is no payment or order integration to test in this phase.
