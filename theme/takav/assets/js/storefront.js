(() => {
  "use strict";
  const $ = (selector, root = document) => root.querySelector(selector);
  const $$ = (selector, root = document) => [
    ...root.querySelectorAll(selector),
  ];
  const nav = $("#main-nav");
  const toggle = $(".menu-toggle");
  const mobile = matchMedia("(max-width: 600px)");
  document.documentElement.classList.add("js-enhanced");

  function closeMenu() {
    if (!nav || !toggle) return;
    nav.hidden = mobile.matches;
    toggle.setAttribute("aria-expanded", "false");
    toggle.setAttribute("aria-label", "باز کردن منو");
  }
  if (nav && toggle) {
    toggle.hidden = false;
    closeMenu();
    mobile.addEventListener("change", closeMenu);
    toggle.addEventListener("click", () => {
      const open = toggle.getAttribute("aria-expanded") !== "true";
      toggle.setAttribute("aria-expanded", String(open));
      toggle.setAttribute("aria-label", open ? "بستن منو" : "باز کردن منو");
      nav.hidden = !open;
    });
    nav.addEventListener("click", (event) => {
      if (event.target.closest("a")) closeMenu();
    });
    document.addEventListener("keydown", (event) => {
      if (
        event.key === "Escape" &&
        toggle.getAttribute("aria-expanded") === "true"
      ) {
        closeMenu();
        toggle.focus();
      }
    });
  }

  const cards = $$(".product-card");
  const filters = $(".filters");
  function filterProducts(value) {
    cards.forEach((card) => {
      card.hidden = value !== "all" && card.dataset.product !== value;
    });
    $$(".filter").forEach((button) => {
      const selected = button.dataset.filter === value;
      button.classList.toggle("is-active", selected);
      button.setAttribute("aria-pressed", String(selected));
    });
    const status = $("#filter-status");
    if (status)
      status.textContent =
        value === "all"
          ? "هر دو محصول نمایش داده می‌شوند."
          : value === "hoodie"
            ? "هودی تکاو نمایش داده می‌شود."
            : "شلوار تکاو نمایش داده می‌شود.";
  }
  if (filters) {
    filters.hidden = false;
    filters.addEventListener("click", (event) => {
      const button = event.target.closest("[data-filter]");
      if (button) filterProducts(button.dataset.filter);
    });
    $$('a[href$="#collection"]').forEach((link) =>
      link.addEventListener("click", () => filterProducts("all")),
    );
  }

  const dialog = $(".product-dialog");
  const details = $$(".product-detail");
  const detailSection = $(".product-details");
  let opener = null;
  let activeDetail = null;
  // Enhance the actual in-page product content; no duplicate product markup.
  if (dialog && typeof dialog.showModal === "function") {
    if (detailSection) detailSection.hidden = true;
    details.forEach((detail) => {
      const close = $(".detail-close", detail);
      close.hidden = false;
      close.addEventListener("click", () => dialog.close());
    });
    function openProduct(id, source) {
      const detail = details.find((item) => item.dataset.detail === id);
      if (!detail) return;
      if (dialog.open) dialog.close();
      opener = source;
      activeDetail = detail;
      dialog.append(detail);
      dialog.setAttribute("aria-labelledby", `title-${id}`);
      dialog.showModal();
      document.body.classList.add("modal-open");
      dialog.scrollTop = 0;
      $(".detail-close", detail).focus({ preventScroll: true });
    }
    $$("[data-open-product]").forEach((link) =>
      link.addEventListener("click", (event) => {
        event.preventDefault();
        openProduct(link.dataset.openProduct, link);
      }),
    );
    dialog.addEventListener("close", () => {
      document.body.classList.remove("modal-open");
      if (activeDetail && detailSection) detailSection.append(activeDetail);
      activeDetail = null;
      if (opener && document.contains(opener))
        opener.focus({ preventScroll: true });
    });
    dialog.addEventListener("keydown", (event) => {
      if (event.key !== "Tab") return;
      const focusable = $$(
        "a[href], button:not([disabled]), [tabindex='0']",
        dialog,
      ).filter((element) => element.getClientRects().length > 0);
      const first = focusable[0];
      const last = focusable[focusable.length - 1];
      if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last.focus();
      } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
      }
    });
    dialog.addEventListener("click", (event) => {
      if (event.target !== dialog) return;
      const rect = dialog.getBoundingClientRect();
      if (
        event.clientX < rect.left ||
        event.clientX > rect.right ||
        event.clientY < rect.top ||
        event.clientY > rect.bottom
      )
        dialog.close();
    });
    if (location.hash.startsWith("#detail-"))
      openProduct(location.hash.replace("#detail-", ""), null);
  }

  $$("[data-gallery-image]").forEach((link) =>
    link.addEventListener("click", (event) => {
      event.preventDefault();
      const gallery = link.closest(".detail-gallery");
      const image = $(".detail-photo img", gallery);
      image.removeAttribute("srcset");
      image.src = link.dataset.galleryImage;
      image.alt = link.dataset.galleryAlt;
      $(".original-photo-link", gallery).href = link.href;
      $(".gallery-caption", gallery).textContent = link.dataset.galleryAlt;
      $$("[data-gallery-image]", gallery).forEach((item) =>
        item.removeAttribute("aria-current"),
      );
      link.setAttribute("aria-current", "true");
    }),
  );

  // Local favourites only. Never creates a reservation, account, or order.
  const storageKey = "takav-favourites-v1";
  let saved = [];
  try {
    const parsed = JSON.parse(localStorage.getItem(storageKey) || "[]");
    if (Array.isArray(parsed))
      saved = parsed.filter((id) => id === "hoodie" || id === "pants");
  } catch (_) {
    /* Browsers may disable storage; the page still works. */
  }
  let toastTimer;
  function notify(message) {
    const toast = $(".toast");
    if (!toast) return;
    clearTimeout(toastTimer);
    toast.textContent = message;
    toast.hidden = false;
    toastTimer = setTimeout(() => {
      toast.hidden = true;
    }, 4000);
  }
  function updateSave(button) {
    const selected = saved.includes(button.dataset.save);
    button.setAttribute("aria-pressed", String(selected));
    $("span", button).textContent = selected
      ? button.dataset.labelSaved
      : button.dataset.labelDefault;
  }
  $$("[data-save]").forEach((button) => {
    button.hidden = false;
    const note = $(".save-note", button.parentElement);
    if (note) note.hidden = false;
    updateSave(button);
    button.addEventListener("click", () => {
      const id = button.dataset.save;
      const next = saved.includes(id)
        ? saved.filter((item) => item !== id)
        : [...saved, id];
      try {
        localStorage.setItem(storageKey, JSON.stringify(next));
        saved = next;
        updateSave(button);
        notify(
          saved.includes(id)
            ? "در همین مرورگر ذخیره شد."
            : "از علاقه‌مندی‌ها حذف شد.",
        );
      } catch (_) {
        notify("ذخیره‌سازی در این مرورگر در دسترس نیست.");
      }
    });
  });
})();
