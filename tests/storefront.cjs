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
    // ZarinPal's plugin loads its own trust badge on the payment step; everything else must be local.
    const host = request.url().startsWith("http") ? new URL(request.url()).hostname : "";
    if (!request.url().startsWith(base) && !request.url().startsWith("data:") && !/(^|\.)zarinpal\.com$/.test(host))
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
  await page.waitForURL(/collection-one/);
  assert.equal(await page.locator(".campaign-piece").count(), 2);
  await page.locator(".campaign-piece").first().click();
  assert.equal(await page.locator("h1").innerText(), "هودی تکاو");
  // Guest order: product → cart → three short steps → tick with tracking number → tracking page.
  assert.match(await page.locator(".preorder-note").innerText(), /پیش‌فروش[\s\S]*۲۰ روز[\s\S]*تلگرام/);
  await page.locator(".add-form button").click();
  await page.waitForURL(/\/cart\//);
  assert.equal(await page.locator(".cart-row h2").innerText(), "هودی تکاو");
  assert.equal(await page.locator("[data-cart-count]").innerText(), "۱");
  await page.locator('.cart-actions button[aria-label^="اضافه"]').click();
  await page.waitForLoadState();
  assert.equal(await page.locator(".cart-actions span").innerText(), "۲");
  await page.locator('.cart-actions button[aria-label^="کم"]').click();
  await page.waitForLoadState();
  assert.equal(await page.locator(".cart-actions span").innerText(), "۱");
  await page.screenshot({ path: path.join(output, "order-1-cart.png"), fullPage: true });
  await page.locator(".cart-checkout").click();
  await page.waitForSelector(".takav-checkout.is-stepped");
  const visibleStep = () =>
    page.locator(".checkout-step:not([hidden])").getAttribute("data-step");
  assert.equal(await visibleStep(), "0");
  await page.screenshot({ path: path.join(output, "order-2-details.png"), fullPage: true });
  await page.locator("[data-step='0'] [data-next]").click();
  assert.equal(await visibleStep(), "0", "Empty name and phone must not continue");
  await page.fill("#billing_first_name", "سارا نمونه");
  await page.fill("#billing_phone", "۰۹۱۲ ۳۴۵ ۶۷۸۹");
  await page.locator("[data-step='0'] [data-next]").click();
  assert.equal(await visibleStep(), "1");
  assert.equal(await page.inputValue("#billing_phone"), "09123456789");
  await page.selectOption("#billing_state", "THR");
  await page.fill("#billing_city", "تهران");
  await page.fill("#billing_address_1", "خیابان نمونه، کوچهٔ دوم، پلاک ۱۲");
  await page.fill("#billing_postcode", "123");
  await page.locator("[data-step='1'] [data-next]").click();
  assert.equal(await visibleStep(), "1", "A short postcode must not continue");
  await page.fill("#billing_postcode", "۱۲۳۴۵۶۷۸۹۰");
  await page.screenshot({ path: path.join(output, "order-3-address.png"), fullPage: true });
  await page.locator("[data-step='1'] [data-next]").click();
  assert.equal(await visibleStep(), "2");
  await page.waitForSelector(".checkout-review:not([aria-busy])");
  assert.equal(await page.locator("[data-shipping-method]").count(), 1, "Tipax is the only delivery option");
  assert.match(await page.locator(".checkout-review .choice").innerText(), /تیپاکس · پس‌کرایه/);
  assert.match(await page.locator(".checkout-totals").innerText(), /پس‌کرایه/);
  assert.match(await page.locator(".checkout-total dd").innerText(), /۲,۴۰۰,۰۰۰/);
  const gateways = await page.locator("input[name=payment_method]").evaluateAll((inputs) => inputs.map((i) => i.value));
  assert.ok(gateways.includes("WC_ZPal"), "ZarinPal is offered");
  assert.ok(!gateways.includes("cod") && !gateways.includes("bacs"), "No pay-on-delivery or card-to-card");
  await page.locator(".choice label", { hasText: "درگاه آزمایشی" }).click();
  await page.screenshot({ path: path.join(output, "order-4-payment.png"), fullPage: true });
  await Promise.all([
    page.waitForURL(/order-received/),
    page.locator(".step-submit").click(),
  ]);
  const trackingCode = (await page.locator("[data-copy-source]").innerText()).trim();
  assert.match(trackingCode, /^\d+$/);
  assert.equal(await page.locator("h1").innerText(), "سفارشت ثبت شد");
  assert.equal(await page.locator("[data-cart-count]").innerText(), "۰");
  assert.equal(await page.locator(".done-telegram").getAttribute("href"), "https://t.me/takav_test");
  assert.match(await page.locator(".done-card").innerText(), /TEST-\d+/);
  await page.waitForTimeout(1300);
  await page.screenshot({ path: path.join(output, "order-5-done.png"), fullPage: true });
  await page.locator(".done-card .done-primary").click();
  assert.equal(await page.inputValue("#track-order"), trackingCode);
  await page.fill("#track-phone", "09120000000");
  await page.locator(".track-form button").click();
  assert.match(await page.locator(".shop-notice").innerText(), /پیدا نشد/);
  await page.fill("#track-phone", "+98 912 345 6789");
  await page.locator(".track-form button").click();
  assert.match(await page.locator(".track-result h2").innerText(), new RegExp(trackingCode));
  assert.match(await page.locator(".track-status").innerText(), /.+/);
  await page.screenshot({ path: path.join(output, "order-6-track.png"), fullPage: true });
  for (const url of [base, hoodie, pants, `${base}/collection-one/`, `${base}/cart/`]) {
    await page.goto(url, { waitUntil: "networkidle" });
    assert.equal(await page.locator("html").getAttribute("dir"), "rtl");
    // WooCommerce keeps its cart out of search results; every other page must be indexable.
    if (!url.endsWith("/cart/"))
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
  // Without JavaScript the checkout shows every step at once and still places the order.
  if (!process.env.TAKAV_STATIC) {
    await fallback.locator(".add-form button").click();
    await fallback.locator(".cart-checkout").click();
    assert.equal(await fallback.locator(".checkout-step:not([hidden])").count(), 3);
    await fallback.fill("#billing_first_name", "علی نمونه");
    await fallback.fill("#billing_phone", "09351234567");
    await fallback.selectOption("#billing_state", "ESF");
    await fallback.fill("#billing_city", "اصفهان");
    await fallback.fill("#billing_address_1", "خیابان نمونه");
    await fallback.locator(".choice label", { hasText: "درگاه آزمایشی" }).click();
    await Promise.all([
      fallback.waitForURL(/order-received/),
      fallback.locator(".step-submit").click(),
    ]);
    assert.match((await fallback.locator("[data-copy-source]").innerText()).trim(), /^\d+$/);
  }
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
