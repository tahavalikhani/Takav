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

// Checkout in steps. Without JavaScript every step shows and the form posts as usual.
(() => {
  "use strict";
  const latin = (value) =>
    value
      .replace(/[۰-۹]/g, (digit) => "۰۱۲۳۴۵۶۷۸۹".indexOf(digit))
      .replace(/[٠-٩]/g, (digit) => "٠١٢٣٤٥٦٧٨٩".indexOf(digit));
  const phone = (value) => {
    let digits = latin(value).replace(/\D+/g, "");
    if (digits.startsWith("00")) digits = digits.slice(2);
    if (digits.length === 12 && digits.startsWith("98")) digits = "0" + digits.slice(2);
    if (digits.length === 10 && digits.startsWith("9")) digits = "0" + digits;
    return digits;
  };
  document.querySelectorAll("[data-phone], [data-postcode], [data-digits]").forEach((input) => {
    input.addEventListener("input", () => {
      const value = latin(input.value);
      if (value !== input.value) input.value = value;
      input.setCustomValidity("");
    });
  });
  function checkField(input) {
    input.setCustomValidity("");
    if (input.matches("[data-phone]") && input.value.trim()) {
      input.value = phone(input.value);
      if (!/^09\d{9}$/.test(input.value))
        input.setCustomValidity("شماره موبایل را کامل وارد کن؛ مثل ۰۹۱۲۳۴۵۶۷۸۹.");
    }
    if (input.matches("[data-postcode]") && input.value.trim()) {
      input.value = latin(input.value).replace(/\D+/g, "");
      if (!/^\d{10}$/.test(input.value)) input.setCustomValidity("کد پستی باید ۱۰ رقم باشد.");
    }
    return input.checkValidity();
  }
  document.querySelectorAll(".track-form").forEach((form) => {
    form.addEventListener("submit", (event) => {
      const fields = [...form.querySelectorAll("input:not([type=hidden])")];
      if (!fields.every(checkField)) {
        event.preventDefault();
        fields.find((field) => !field.checkValidity())?.reportValidity();
      }
    });
  });
  document.querySelector(".track-result")?.focus();

  const form = document.querySelector(".takav-checkout");
  if (!form) return;
  const steps = [...form.querySelectorAll(".checkout-step")];
  const markers = [...document.querySelectorAll("[data-step-marker]")];
  const review = form.querySelector(".checkout-review");
  const submit = form.querySelector(".step-submit");
  let current = 0;
  form.noValidate = true;
  form.classList.add("is-stepped");
  const fieldsOf = (step) =>
    [...step.querySelectorAll("input, select, textarea")].filter(
      (field) => field.type !== "hidden" && !field.closest(".choice-detail"),
    );
  function validate(step) {
    const fields = fieldsOf(step);
    const invalid = fields.find((field) => !checkField(field));
    if (invalid) invalid.reportValidity();
    return !invalid;
  }
  function show(index, focus = true) {
    current = index;
    steps.forEach((step, n) => (step.hidden = n !== index));
    markers.forEach((marker, n) => {
      marker.classList.toggle("is-done", n < index);
      if (n === index) marker.setAttribute("aria-current", "step");
      else marker.removeAttribute("aria-current");
    });
    if (focus) {
      steps[index].querySelector("[data-step-title]").focus({ preventScroll: true });
      form.closest(".checkout-page").scrollIntoView({ behavior: "smooth", block: "start" });
    }
  }
  async function refreshReview(extra = {}) {
    const data = new FormData();
    data.append("nonce", form.dataset.reviewNonce);
    ["billing_state", "billing_city", "billing_postcode"].forEach((name) =>
      data.append(name.replace("billing_", ""), form.elements[name].value),
    );
    Object.entries(extra).forEach(([name, value]) => data.append(name, value));
    review.setAttribute("aria-busy", "true");
    submit.disabled = true;
    try {
      const response = await fetch(form.dataset.reviewUrl, { method: "POST", body: data, credentials: "same-origin" });
      const result = await response.json();
      if (result.success) review.innerHTML = result.data.html;
    } catch (_) {
      // Keep the options already on the page; WooCommerce re-checks them when the order is placed.
    } finally {
      review.removeAttribute("aria-busy");
      submit.disabled = false;
    }
  }
  form.addEventListener("click", (event) => {
    if (event.target.closest("[data-next]")) {
      if (!validate(steps[current])) return;
      if (current === 1) refreshReview();
      show(current + 1);
    }
    if (event.target.closest("[data-back]")) show(current - 1);
  });
  form.addEventListener("change", (event) => {
    if (event.target.matches("[data-shipping-method]"))
      refreshReview({ [event.target.name]: event.target.value });
  });
  form.addEventListener("keydown", (event) => {
    if (event.key !== "Enter" || event.target.tagName === "TEXTAREA" || current === steps.length - 1) return;
    event.preventDefault();
    steps[current].querySelector("[data-next]")?.click();
  });
  form.addEventListener("submit", (event) => {
    const invalid = steps.findIndex((step) => !fieldsOf(step).every(checkField));
    if (invalid !== -1) {
      event.preventDefault();
      show(invalid);
      validate(steps[invalid]);
      return;
    }
    if (submit.disabled) {
      event.preventDefault();
      return;
    }
    submit.disabled = true;
    submit.textContent = "در حال ثبت سفارش…";
  });
  // Coming back from the bank with the browser's Back button restores a disabled button.
  window.addEventListener("pageshow", () => {
    submit.disabled = false;
    submit.textContent = "ثبت سفارش";
  });
  // After a server-side error, open the step with the field WooCommerce flagged.
  const flagged = document.querySelector(".shop-notices [data-field]");
  const flaggedField = flagged && document.getElementById(flagged.dataset.field);
  const errorStep = flaggedField ? steps.findIndex((step) => step.contains(flaggedField)) : document.querySelector(".shop-notices .is-error") ? steps.length - 1 : 0;
  show(Math.max(0, errorStep), false);
})();

// Copy the tracking number.
(() => {
  "use strict";
  const button = document.querySelector("[data-copy]");
  const source = document.querySelector("[data-copy-source]");
  if (!button || !source || !navigator.clipboard) return;
  button.hidden = false;
  button.addEventListener("click", async () => {
    try {
      await navigator.clipboard.writeText(source.textContent.trim());
      button.textContent = "کپی شد";
    } catch (_) {
      button.textContent = "کپی نشد";
    }
  });
})();
