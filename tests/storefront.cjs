const { chromium } = require("playwright");
const assert = require("node:assert/strict");
const fs = require("node:fs/promises");
const path = require("node:path");
const base = process.env.TAKAV_URL || "http://127.0.0.1:8898";
const output =
  process.env.TAKAV_QA_DIR || path.resolve(__dirname, "../test-results");
(async () => {
  const browser = await chromium.launch({
    headless: true,
    ...(process.env.TAKAV_BROWSER_CHANNEL
      ? { channel: process.env.TAKAV_BROWSER_CHANNEL }
      : {}),
  });
  const context = await browser.newContext({
    viewport: { width: 1440, height: 1000 },
  });
  const page = await context.newPage();
  const errors = [],
    external = [];
  page.on("pageerror", (error) => errors.push(error.message));
  page.on("request", (request) => {
    if (!request.url().startsWith(base) && !request.url().startsWith("data:"))
      external.push(request.url());
  });
  await fs.mkdir(output, { recursive: true });
  await page.goto(base, { waitUntil: "networkidle" });
  assert.equal(
    await page.locator("dialog, .faq-list, .hero, .signature-section").count(),
    0,
  );
  assert.equal(await page.locator("h1").count(), 1);
  const hoodie = await page
    .locator(".product-link")
    .first()
    .getAttribute("href");
  await page.locator(".product-link").first().click();
  await page.waitForURL(hoodie);
  assert.equal(await page.locator("h1").innerText(), "هودی تکاو");
  assert.match(await page.title(), /هودی تکاو/);
  await page.reload({ waitUntil: "networkidle" });
  assert.equal(await page.locator("h1").innerText(), "هودی تکاو");
  await page.locator('[data-gallery-alt="گلدوزی سینه و بند کلاه"]').click();
  await page.waitForFunction(() =>
    document.querySelector(".gallery-main-image").src.includes("embroidery"),
  );
  assert.equal(page.url(), hoodie, "Gallery must not navigate or open a popup");
  await page
    .locator('[data-gallery-alt="گلدوزی سینه و بند کلاه"]')
    .press("ArrowLeft");
  await page.waitForFunction(() =>
    document.querySelector(".gallery-main-image").src.includes("hood-detail"),
  );
  assert.equal(await page.locator(".purchase-state button").isEnabled(), true);
  const pants = await page.locator(".matching-product a").getAttribute("href");
  await page.locator(".matching-product a").click();
  await page.waitForURL(pants);
  assert.equal(await page.locator("h1").innerText(), "شلوار تکاو");
  await page.goBack();
  assert.equal(page.url(), hoodie);
  await page.goBack();
  assert.equal(new URL(page.url()).pathname, "/");
  await page.locator(".collection-invite").click();
  assert.equal(await page.locator(".campaign-piece").count(), 2);
  await page.locator(".campaign-piece").first().click();
  assert.equal(await page.locator("h1").innerText(), "هودی تکاو");
  await page.locator("[data-add-cart=hoodie]").click();
  await page.locator(".header-cart").click();
  assert.equal(await page.locator(".cart-row h2").innerText(), "هودی تکاو");
  await page.reload();
  assert.equal(await page.locator(".cart-row h2").innerText(), "هودی تکاو");
  await page.locator(".cart-row > button").click();
  assert.equal(await page.locator(".cart-row").count(), 0);
  for (const url of [base, hoodie, pants, `${base}/collection-one/`, `${base}/cart/`]) {
    await page.goto(url, { waitUntil: "networkidle" });
    assert.equal(await page.locator("html").getAttribute("dir"), "rtl");
    assert.equal(
      await page.locator("meta[name=robots][content*=noindex]").count(),
      0,
    );
    assert.equal(await page.locator("h1").count(), 1);
    for (const width of [320, 390, 768, 1440, 1920]) {
      await page.setViewportSize({ width, height: 900 });
      assert.ok(
        await page.evaluate(
          () => document.documentElement.scrollWidth <= innerWidth,
        ),
        `Overflow at ${url}, ${width}`,
      );
    }
    await page.addScriptTag({ path: require.resolve("axe-core/axe.min.js") });
    const violations = await page.evaluate(
      async () =>
        (
          await axe.run(document, {
            runOnly: { type: "tag", values: ["wcag2a", "wcag2aa", "wcag21aa"] },
          })
        ).violations,
    );
    assert.deepEqual(
      violations.map((v) => v.id),
      [],
    );
    assert.deepEqual(
      await page
        .locator("img")
        .evaluateAll((images) =>
          images
            .filter((i) => i.loading !== "lazy" && !i.naturalWidth)
            .map((i) => i.src),
        ),
      [],
    );
  }
  const noJs = await browser.newContext({ javaScriptEnabled: false });
  const fallback = await noJs.newPage();
  await fallback.goto(base);
  await fallback.locator(".product-link").first().click();
  assert.equal(await fallback.locator("h1").innerText(), "هودی تکاو");
  assert.equal(await fallback.locator(".gallery-thumbnails a").count(), 3);
  if (!process.env.TAKAV_STATIC) {
    assert.equal(
      (await page.goto(`${base}/?takav_product=missing`)).status(),
      404,
    );
    assert.equal(
      (await page.goto(`${base}/collection/missing/`)).status(),
      404,
    );
    assert.equal(
      (await page.goto(`${base}/?takav_product=hoodie`)).status(),
      200,
    );
    assert.equal(await page.locator("h1").innerText(), "هودی تکاو");
    assert.equal((await page.goto(`${base}/?p=999999`)).status(), 404);
    assert.equal((await page.goto(`${base}/?p=1`)).status(), 200);
  }
  assert.deepEqual(errors, []);
  assert.deepEqual(external, []);
  await browser.close();
  console.log(
    "PASS: real product navigation, reload/back, galleries, responsive pages, accessibility and no-JS navigation.",
  );
})().catch((error) => {
  console.error(error);
  process.exit(1);
});
