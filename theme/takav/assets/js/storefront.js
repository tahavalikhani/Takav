// Links navigate normally. Only the inline product gallery needs JavaScript.
(() => {
  "use strict";
  document.querySelectorAll(".product-gallery").forEach((gallery) => {
    const links = [...gallery.querySelectorAll("[data-gallery-image]")];
    const image = gallery.querySelector(".gallery-main-image");
    const status = gallery.querySelector(".gallery-status");
    const count = gallery.querySelector(".gallery-count");
    const digits = (value) =>
      String(value)
        .padStart(2, "0")
        .replace(/\d/g, (digit) => "۰۱۲۳۴۵۶۷۸۹"[digit]);
    let request = 0;
    async function select(link) {
      const currentRequest = ++request;
      const next = new Image();
      next.src = link.dataset.galleryImage;
      try {
        await next.decode();
      } catch (_) {
        if (currentRequest === request)
          status.textContent = "عکس بارگذاری نشد. دوباره انتخاب کن.";
        return;
      }
      if (currentRequest !== request) return;
      image.removeAttribute("srcset");
      image.src = next.src;
      image.alt = link.dataset.galleryAlt;
      links.forEach((item) => item.removeAttribute("aria-current"));
      link.setAttribute("aria-current", "true");
      count.textContent = `${digits(links.indexOf(link) + 1)} / ${digits(links.length)}`;
      status.textContent = link.dataset.galleryAlt;
    }
    links.forEach((link, index) => {
      link.addEventListener("click", (event) => {
        event.preventDefault();
        select(link);
      });
      link.addEventListener("keydown", (event) => {
        let next;
        if (event.key === "ArrowLeft") next = (index + 1) % links.length;
        if (event.key === "ArrowRight")
          next = (index - 1 + links.length) % links.length;
        if (event.key === "Home") next = 0;
        if (event.key === "End") next = links.length - 1;
        if (next === undefined) return;
        event.preventDefault();
        links[next].focus();
        select(links[next]);
      });
    });
  });
})();

// Local, clearly labelled preview cart. It never creates an order.
(() => {
  "use strict";
  const key = "takav-preview-cart-v1";
  const labels = { hoodie: "هودی تکاو", pants: "شلوار تکاو" };
  const persian = (number) => String(number).replace(/\d/g, (digit) => "۰۱۲۳۴۵۶۷۸۹"[digit]);
  function read() {
    try {
      const saved = JSON.parse(localStorage.getItem(key) || "{}");
      return Object.fromEntries(Object.keys(labels).map((id) => [id, Math.min(9, Math.max(0, Number(saved[id]) || 0))]));
    } catch (_) { return { hoodie: 0, pants: 0 }; }
  }
  function write(cart) {
    try { localStorage.setItem(key, JSON.stringify(cart)); return true; }
    catch (_) { return false; }
  }
  function updateCount(cart) {
    document.querySelectorAll("[data-cart-count]").forEach((count) => {
      count.textContent = persian(cart.hoodie + cart.pants);
    });
  }
  function productUrl(id) {
    return document.querySelector(`.main-nav a[href*="${id}"]`)?.href || "#";
  }
  const assetScript = document.querySelector('script[src*="/assets/js/storefront"]');
  const imageUrl = (id) => assetScript ? new URL(`../images/takav-${id}-640.webp`, assetScript.src).href : "";
  function render(cart) {
    const container = document.getElementById("cart-items");
    if (!container) return;
    container.replaceChildren();
    const ids = Object.keys(labels).filter((id) => cart[id]);
    if (!ids.length) {
      const empty = document.createElement("p"); empty.className = "cart-empty";
      empty.textContent = "سبد هنوز خالی است."; container.append(empty); return;
    }
    ids.forEach((id) => {
      const row = document.createElement("div"); row.className = "cart-row";
      const link = document.createElement("a"); link.href = productUrl(id);
      const image = document.createElement("img"); image.src = imageUrl(id); image.alt = labels[id]; image.width = 110; image.height = 136;
      link.append(image);
      const info = document.createElement("div");
      const title = document.createElement("h2"); title.textContent = labels[id];
      const color = document.createElement("p"); color.textContent = "مشکی / نارنجی";
      const actions = document.createElement("div"); actions.className = "cart-actions";
      const decrement = document.createElement("button"); decrement.type = "button"; decrement.textContent = "−"; decrement.setAttribute("aria-label", `کم کردن ${labels[id]}`);
      const quantity = document.createElement("span"); quantity.textContent = persian(cart[id]);
      const increment = document.createElement("button"); increment.type = "button"; increment.textContent = "+"; increment.setAttribute("aria-label", `اضافه کردن ${labels[id]}`);
      const remove = document.createElement("button"); remove.type = "button"; remove.textContent = "حذف";
      const change = (number) => { const next = { ...cart, [id]: Math.min(9, Math.max(0, number)) }; if (write(next)) { updateCount(next); render(next); } };
      decrement.addEventListener("click", () => change(cart[id] - 1));
      increment.addEventListener("click", () => change(cart[id] + 1));
      remove.addEventListener("click", () => change(0));
      actions.append(decrement, quantity, increment); info.append(title, color, actions); row.append(link, info, remove); container.append(row);
    });
    const note = document.createElement("p"); note.className = "cart-note"; note.textContent = "این یک سبد نمایشی است؛ قیمت و پرداخت بعداً اضافه می‌شوند."; container.append(note);
  }
  const cart = read(); updateCount(cart); render(cart);
  document.querySelectorAll("[data-add-cart]").forEach((button) => {
    button.addEventListener("click", () => {
      const id = button.dataset.addCart;
      if (!Object.hasOwn(labels, id)) return;
      const next = read(); next[id] = Math.min(9, next[id] + 1);
      const feedback = button.parentElement.querySelector(".cart-feedback");
      if (!write(next)) { feedback.textContent = "مرورگر سبد را ذخیره نکرد."; return; }
      updateCount(next);
      feedback.replaceChildren();
      const message = document.createTextNode("به سبد اضافه شد. ");
      const link = document.createElement("a");
      link.href = document.querySelector(".header-cart")?.href || "#";
      link.textContent = "دیدن سبد ↗";
      feedback.append(message, link);
    });
  });
})();
