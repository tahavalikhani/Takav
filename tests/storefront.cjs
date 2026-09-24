const { chromium } = require("playwright");
const assert = require("node:assert/strict");
const fs = require("node:fs/promises");
const path = require("node:path");
const base = process.env.TAKAV_URL || "http://127.0.0.1:8898";
const output =
  process.env.TAKAV_QA_DIR || path.resolve(__dirname, "../test-results");
const launchOptions = {
  headless: true,
  ...(process.env.TAKAV_BROWSER_CHANNEL
    ? { channel: process.env.TAKAV_BROWSER_CHANNEL }
    : {}),
};

(async () => {
  await fs.mkdir(output, { recursive: true });
  const browser = await chromium.launch(launchOptions);
  const context = await browser.newContext({
    viewport: { width: 1440, height: 1000 },
  });
  const page = await context.newPage();
  const errors = [],
    failed = [],
    external = [];
  page.on("pageerror", (error) => errors.push(error.message));
  page.on("response", (response) => {
    if (response.status() >= 400 && !response.url().endsWith("favicon.ico"))
      failed.push(`${response.status()} ${response.url()}`);
  });
  page.on("request", (request) => {
    if (!request.url().startsWith(base) && !request.url().startsWith("data:"))
      external.push(request.url());
  });
  await page.goto(base, { waitUntil: "networkidle" });
  assert.equal(await page.locator("html").getAttribute("dir"), "rtl");
  assert.equal(await page.locator("h1").count(), 1);
  assert.match(
    await page.locator("meta[name=robots]").getAttribute("content"),
    /noindex/,
  );
  assert.equal(await page.locator(".product-card").count(), 2);
  assert.equal(
    await page.locator('form[action*="checkout"], a[href*="checkout"]').count(),
    0,
  );

  await page.locator("[data-filter=pants]").click();
  assert.equal(await page.locator(".product-card:visible").count(), 1);
  assert.equal(
    await page.locator(".product-card:visible").getAttribute("data-product"),
    "pants",
  );
  await page.locator("[data-filter=hoodie]").click();
  await page.locator(".product-card:visible .text-link").click();
  assert.equal(
    await page.locator("dialog").evaluate((dialog) => dialog.open),
    true,
  );
  assert.equal(
    await page.locator("dialog [data-detail]").getAttribute("data-detail"),
    "hoodie",
  );
  await page
    .locator('dialog [data-gallery-alt="گلدوزی سینه و بند کلاه"]')
    .click();
  assert.match(
    await page.locator("dialog .detail-photo img").getAttribute("src"),
    /takav-embroidery/,
  );
  assert.match(
    await page.locator("dialog .original-photo-link").getAttribute("href"),
    /takav-embroidery.jpg/,
  );
  await page.locator("[data-save=hoodie]").click();
  assert.equal(
    await page.locator("[data-save=hoodie]").getAttribute("aria-pressed"),
    "true",
  );
  for (let i = 0; i < 15; i++) {
    await page.keyboard.press("Tab");
    assert.equal(
      await page.evaluate(() => !!document.activeElement.closest("dialog")),
      true,
      "Focus escaped modal",
    );
  }
  await page.screenshot({ path: path.join(output, "product-desktop.png") });
  await page.keyboard.press("Escape");
  assert.equal(
    await page.locator("dialog").evaluate((dialog) => dialog.open),
    false,
  );
  assert.equal(
    await page.evaluate(() =>
      document.activeElement.matches(".product-card .text-link"),
    ),
    true,
    "Focus was not restored",
  );
  await page.reload({ waitUntil: "networkidle" });
  assert.equal(
    await page.locator("[data-save=hoodie]").getAttribute("aria-pressed"),
    "true",
    "Favourite did not persist",
  );
  await page.locator("details summary").first().click();
  assert.equal(await page.locator("details").first().getAttribute("open"), "");

  const sizes = [320, 390, 600, 768, 1024, 1440, 1920];
  const measurements = [];
  for (const width of sizes) {
    await page.setViewportSize({ width, height: 1000 });
    const dimensions = await page.evaluate(() => ({
      viewport: innerWidth,
      scroll: document.documentElement.scrollWidth,
    }));
    assert.ok(
      dimensions.scroll <= dimensions.viewport,
      `Overflow at ${width}: ${dimensions.scroll}`,
    );
    measurements.push(dimensions);
  }
  await page.setViewportSize({ width: 390, height: 844 });
  await page.locator(".menu-toggle").click();
  assert.equal(await page.locator("#main-nav").isVisible(), true);
  await page.keyboard.press("Escape");
  assert.equal(await page.locator("#main-nav").isVisible(), false);
  await page.locator(".product-card[data-product=pants] .text-link").click();
  await page
    .locator('dialog [data-gallery-alt="خط نارنجی و بند تنظیم پاچه"]')
    .click();
  await page
    .locator("dialog .detail-photo img")
    .evaluate((image) => image.decode());
  await page.screenshot({
    path: path.join(output, "product-mobile.png"),
    fullPage: false,
  });
  await page.keyboard.press("Escape");

  // Load lazy photos before capturing the entire page.
  for (const width of [390, 1440]) {
    await page.setViewportSize({ width, height: width === 390 ? 844 : 1000 });
    await page
      .locator("details")
      .first()
      .evaluate((detail) => {
        detail.open = false;
      });
    await page.evaluate(async () => {
      for (const image of document.images) image.loading = "eager";
      await Promise.all(
        [...document.images].map((image) => image.decode().catch(() => {})),
      );
      document.activeElement.blur();
      window.scrollTo({ top: 0, behavior: "instant" });
      await document.fonts.ready;
      await new Promise((resolve) =>
        requestAnimationFrame(() => requestAnimationFrame(resolve)),
      );
    });
    const missing = await page
      .locator("img")
      .evaluateAll((images) =>
        images.filter((image) => !image.naturalWidth).map((image) => image.src),
      );
    assert.deepEqual(missing, [], "Missing product images");
    await page.screenshot({
      path: path.join(output, width === 390 ? "mobile.png" : "desktop.png"),
      fullPage: true,
    });
    await page.addScriptTag({ path: require.resolve("axe-core/axe.min.js") });
    const axe = await page.evaluate(async () =>
      axe.run(document, {
        runOnly: { type: "tag", values: ["wcag2a", "wcag2aa", "wcag21aa"] },
      }),
    );
    await fs.writeFile(
      path.join(output, `accessibility-${width}.json`),
      JSON.stringify(axe.violations, null, 2),
    );
    assert.deepEqual(
      axe.violations.map((v) => ({
        id: v.id,
        nodes: v.nodes.map((n) => n.target),
      })),
      [],
      `Accessibility violations at ${width}`,
    );
  }
  assert.deepEqual(errors, [], "JavaScript errors");
  assert.deepEqual(failed, [], "Failed resource requests");
  assert.deepEqual(external, [], "Unexpected third-party requests");

  const noJs = await browser.newContext({
    javaScriptEnabled: false,
    viewport: { width: 390, height: 844 },
  });
  const fallback = await noJs.newPage();
  await fallback.goto(base);
  assert.equal(
    await fallback.locator(".product-detail:visible").count(),
    2,
    "No-JS product content missing",
  );
  assert.equal(await fallback.locator("#main-nav").isVisible(), true);
  assert.equal(await fallback.locator(".filters").isVisible(), false);
  assert.ok(
    await fallback.evaluate(
      () => document.documentElement.scrollWidth <= innerWidth,
    ),
  );

  const blocked = await browser.newContext();
  await blocked.addInitScript(() => {
    Object.defineProperty(window, "localStorage", {
      get() {
        throw new Error("Storage blocked");
      },
    });
  });
  const blockedPage = await blocked.newPage();
  await blockedPage.goto(base);
  await blockedPage
    .locator(".product-card[data-product=hoodie] .text-link")
    .click();
  await blockedPage.locator("[data-save=hoodie]").click();
  assert.equal(
    await blockedPage
      .locator("[data-save=hoodie]")
      .getAttribute("aria-pressed"),
    "false",
  );
  assert.match(await blockedPage.locator(".toast").innerText(), /دسترس نیست/);

  if (!process.env.TAKAV_STATIC) {
    const missing = await page.goto(`${base}/?p=999999`);
    assert.equal(missing.status(), 404);
    assert.match(await page.locator("h1").innerText(), /این خط/);
    const post = await page.goto(`${base}/?p=1`);
    assert.equal(post.status(), 200);
    assert.ok(await page.locator(".entry-content").count());
  }
  await fs.writeFile(
    path.join(output, "results.json"),
    JSON.stringify(
      {
        passed: true,
        widths: measurements,
        checks: [
          "RTL",
          "noindex",
          "filters",
          "gallery",
          "modal-focus",
          "escape-and-focus-return",
          "local-favourites",
          "mobile-menu",
          "all-images",
          "zero-external-requests",
          "axe-WCAG-AA",
          "no-JS",
          "storage-unavailable",
          "WordPress-404-and-post",
        ],
      },
      null,
      2,
    ),
  );
  await browser.close();
  console.log(
    "PASS: responsive layouts, interactions, accessibility, fallbacks, and WordPress templates.",
  );
})().catch((error) => {
  console.error(error);
  process.exit(1);
});
