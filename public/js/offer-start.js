const apiBase = document.body.dataset.apiBase || "/api";
const storageKey = "escrow_mvp_auth";
const homePath = document.body.dataset.homeUrl || "/";

const state = {
  token: localStorage.getItem(storageKey) || "",
  user: null,
  offer: null,
  fee: 0,
  toastTimer: null,
};

const pageRoot = document.querySelector("[data-offer-start-page]");
const offerId = pageRoot?.dataset.offerId || "";
const dashboardPath = pageRoot?.dataset.dashboardUrl || "/dashboard";
const transactionBase = pageRoot?.dataset.transactionBase || "/transactions/";
const transactionCreatedBase = transactionBase.endsWith("/") ? `${transactionBase}__KEY__/created` : `${transactionBase}/__KEY__/created`;
const form = document.querySelector("[data-offer-start-form]");
const toast = document.querySelector("[data-toast]");
const message = document.querySelector("[data-offer-start-message]");
const submitButton = document.querySelector("[data-offer-start-submit]");
const feePaidByField = document.querySelector("[data-fee-paid-by]");
const termsField = document.querySelector("[data-terms-checkbox]");
const sellerPhoneField = document.querySelector("[data-seller-phone]");
const summaryModal = document.querySelector("[data-summary-modal]");
const openSummaryModalButton = document.querySelector("[data-open-summary-modal]");
const closeSummaryModalButtons = document.querySelectorAll("[data-close-summary-modal]");

function buildTransactionUrl(key) {
  return `${transactionBase}${key}`;
}

function buildTransactionCreatedUrl(key) {
  return transactionCreatedBase.replace("__KEY__", String(key));
}

function showToast(text, isError = false) {
  if (!toast) return;
  toast.hidden = false;
  toast.textContent = text;
  toast.style.background = isError ? "#a33030" : "#01426a";
  window.clearTimeout(state.toastTimer);
  state.toastTimer = window.setTimeout(() => {
    toast.hidden = true;
  }, 3600);
}

function setMessage(text, isError = false) {
  if (!message) return;
  if (!text) {
    message.hidden = true;
    message.textContent = "";
    message.classList.remove("is-error");
    return;
  }

  message.hidden = false;
  message.textContent = text;
  message.classList.toggle("is-error", isError);
}

function clearFieldErrors() {
  document.querySelectorAll("[data-error-for]").forEach((node) => {
    node.textContent = "";
  });
}

function setFieldError(name, text) {
  const normalizedName = String(name || "").split(".").pop();
  const field = document.querySelector(`[data-error-for="${normalizedName}"]`)
    || document.querySelector(`[data-error-for="${name}"]`);
  if (field) {
    field.textContent = text;
  }
}

async function apiRequest(path, options = {}) {
  const headers = {
    Accept: "application/json",
    ...(options.body ? { "Content-Type": "application/json" } : {}),
  };

  if (state.token) {
    headers.Authorization = `Bearer ${state.token}`;
  }

  const response = await fetch(`${apiBase}${path}`, {
    method: options.method || "GET",
    headers,
    body: options.body ? JSON.stringify(options.body) : undefined,
  });

  const payload = await response.json().catch(() => ({}));

  if (!response.ok) {
    throw Object.assign(new Error(payload.message || "Не удалось обработать запрос."), {
      status: response.status,
      payload,
    });
  }

  return payload;
}

function formatMoney(amount, currency) {
  return new Intl.NumberFormat("en-US", {
    style: "currency",
    currency: currency || "USD",
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(Number(amount || 0));
}

function normalizeAssetType(value) {
  const map = {
    domain: "Домен",
    website: "Сайт",
    "online-business": "Онлайн-бизнес",
    "mobile-app": "Мобильное приложение",
    "hosting-account": "Хостинг-аккаунт",
    "social-media-account": "Аккаунт в соцсетях",
    "digital-assets": "Цифровые активы",
    "cars-trucks": "Автомобили и грузовики",
    boats: "Лодки и катера",
    motorcycles: "Мотоциклы",
    airplanes: "Самолеты",
    "other-motor-vehicles": "Другой мототранспорт",
    "special-equipment": "Спецтехника",
    trailers: "Прицепы",
    "antiques-collectibles": "Антиквариат и коллекционные предметы",
    appliances: "Бытовая техника",
    art: "Искусство",
    cameras: "Камеры и фотооборудование",
    electronics: "Электроника",
    jewelry: "Ювелирные изделия",
    "luxury-goods": "Предметы роскоши",
    "musical-instruments": "Музыкальные инструменты",
    "sports-equipment": "Спортивное оборудование",
    watches: "Часы",
    "broker-services": "Брокерские услуги",
    "freelance-services": "Фриланс-услуги",
    "milestone-services": "Услуги с поэтапной оплатой",
    consulting: "Консалтинг",
    "software-development": "Разработка ПО",
    "marketing-services": "Маркетинговые услуги",
    "design-services": "Дизайн-услуги",
    inventory: "Товарные остатки",
    "industrial-equipment": "Промышленное оборудование",
    "medical-equipment": "Медицинское оборудование",
    "property-rent": "Арендные сделки",
    other: "Другое",
  };

  return map[String(value || "").trim()] || String(value || "Категория не указана");
}

function calculateFee(amount) {
  return Math.max(Number(amount || 0) * 0.024, 25);
}

function updateSummary() {
  if (!state.offer || !form) return;

  const currency = form.elements.currency.value || state.offer.currency || "USD";
  const amount = Number(state.offer.amount || 0);
  const feePaidBy = feePaidByField?.value || "buyer";
  const fee = calculateFee(amount);
  state.fee = fee;

  document.querySelector("[data-summary-subtotal]").textContent = formatMoney(amount, currency);
  document.querySelector("[data-summary-fee]").textContent = formatMoney(fee, currency);
  document.querySelector("[data-summary-currency]").textContent = currency;

  let buyerTotal = amount;
  let sellerTotal = amount;

  if (feePaidBy === "buyer") {
    buyerTotal += fee;
  } else if (feePaidBy === "seller") {
    sellerTotal -= fee;
  } else {
    buyerTotal += fee / 2;
    sellerTotal -= fee / 2;
  }

  document.querySelector("[data-summary-buyer]").textContent = formatMoney(buyerTotal, currency);
  document.querySelector("[data-summary-seller]").textContent = formatMoney(Math.max(sellerTotal, 0), currency);
}

function updateSubmitState() {
  if (!submitButton || !form) return;
  const sellerEmail = String(form.elements.seller_email?.value || "").trim();
  const sellerPhone = String(form.elements.seller_phone?.value || "").trim();
  submitButton.disabled = !(termsField?.checked && sellerEmail && sellerPhone);
}

function formatRuPhone(value) {
  const digits = String(value || "").replace(/\D/g, "");
  if (!digits) return "";

  let normalized = digits;
  if (normalized.startsWith("8")) {
    normalized = `7${normalized.slice(1)}`;
  } else if (!normalized.startsWith("7")) {
    normalized = `7${normalized}`;
  }

  normalized = normalized.slice(0, 11);

  const country = normalized.slice(0, 1);
  const part1 = normalized.slice(1, 4);
  const part2 = normalized.slice(4, 7);
  const part3 = normalized.slice(7, 9);
  const part4 = normalized.slice(9, 11);

  let result = `+${country}`;
  if (part1) result += ` (${part1}`;
  if (part1.length === 3) result += ")";
  if (part2) result += ` ${part2}`;
  if (part3) result += `-${part3}`;
  if (part4) result += `-${part4}`;

  return result;
}

function handleRuPhoneInput(event) {
  const field = event.currentTarget;
  if (!field) return;
  field.value = formatRuPhone(field.value);
  updateSubmitState();
}

function openSummaryModal() {
  if (!summaryModal) return;
  summaryModal.hidden = false;
  document.body.classList.add("is-modal-open");
}

function closeSummaryModal() {
  if (!summaryModal) return;
  summaryModal.hidden = true;
  document.body.classList.remove("is-modal-open");
}

function renderOffer(offer) {
  state.offer = offer;
  const meta = offer.meta || {};

  form.elements.title.value = offer.title || "";
  form.elements.role.value = meta.role || "buyer";
  form.elements.currency.value = offer.currency || "USD";
  form.elements.inspection_period_days.value = meta.inspection_period_days || 1;
  form.elements.seller_email.value = meta.seller_email || "";
  form.elements.seller_phone.value = formatRuPhone(meta.seller_phone || "");
  feePaidByField.value = meta.fee_paid_by || "buyer";

  const itemName = meta.item_name || offer.title || "Товар без названия";
  const description = offer.description || "Описание отсутствует";
  const assetType = normalizeAssetType(offer.asset_type);
  const inspectionDays = Number(meta.inspection_period_days || 1);

  document.querySelector("[data-offer-item-name]").textContent = itemName;
  document.querySelector("[data-offer-asset-type]").textContent = assetType;
  document.querySelector("[data-offer-item-description]").textContent = description;
  document.querySelector("[data-offer-inspection-text]").textContent = `Период проверки: ${inspectionDays} ${inspectionDays === 1 ? "день" : inspectionDays < 5 ? "дня" : "дней"}`;
  document.querySelector("[data-offer-amount]").textContent = formatMoney(offer.amount, offer.currency);

  updateSummary();
  updateSubmitState();
}

async function bootstrap() {
  if (!offerId) {
    window.location.href = homePath;
    return;
  }

  try {
    const [me, offer] = await Promise.all([
      apiRequest("/me"),
      apiRequest(`/offers/${offerId}`),
    ]);

    state.user = me.user;

    if (String(offer.status || "").toLowerCase() === "accepted" && offer.transaction?.id) {
      window.location.href = buildTransactionUrl(offer.transaction.uuid || offer.transaction.id);
      return;
    }

    renderOffer(offer);
  } catch (error) {
    showToast(error.message, true);
    if (error.status === 401) {
      localStorage.removeItem(storageKey);
      window.setTimeout(() => {
        window.location.href = dashboardPath;
      }, 1200);
    }
  }
}

function normalizeError(text) {
  const map = {
    "The title field is required.": "Укажите название сделки.",
    "The seller email field is required.": "Укажите email продавца.",
    "The seller email field must be a valid email address.": "Укажите корректный email продавца.",
    "The seller phone field is required.": "Укажите телефон продавца.",
  };

  const value = String(text || "").trim();
  return map[value] || value || "Не удалось запустить сделку.";
}

async function handleSubmit(event) {
  event.preventDefault();
  clearFieldErrors();
  setMessage("");
  updateSubmitState();

  if (submitButton.disabled || !state.offer || !form) return;

  submitButton.disabled = true;

  const payload = {
    title: String(form.elements.title.value || "").trim(),
    currency: String(form.elements.currency.value || "USD").trim(),
    meta: {
      ...(state.offer.meta || {}),
      role: String(form.elements.role.value || "buyer").trim(),
      inspection_period_days: Number(form.elements.inspection_period_days.value || 1),
      seller_email: String(form.elements.seller_email.value || "").trim(),
      seller_phone: String(form.elements.seller_phone.value || "").trim(),
      fee_paid_by: String(feePaidByField.value || "buyer").trim(),
      terms_agreed: Boolean(termsField.checked),
    },
  };

  try {
    await apiRequest(`/offers/${state.offer.id}`, {
      method: "PATCH",
      body: payload,
    });

    const accepted = await apiRequest(`/offers/${state.offer.id}/accept`, {
      method: "POST",
    });

    showToast("Сделка запущена.");

    if (accepted.transaction?.id) {
      window.location.href = buildTransactionCreatedUrl(accepted.transaction.uuid || accepted.transaction.id);
      return;
    }

    throw new Error("Не удалось перейти к сделке.");
  } catch (error) {
    const errors = error.payload?.errors || {};
    Object.entries(errors).forEach(([key, values]) => {
      setFieldError(key, normalizeError(Array.isArray(values) ? values[0] : values));
    });
    setMessage(normalizeError(error.message), true);
    submitButton.disabled = false;
  }
}

form?.addEventListener("submit", handleSubmit);
feePaidByField?.addEventListener("change", updateSummary);
termsField?.addEventListener("change", updateSubmitState);
form?.elements.seller_email?.addEventListener("input", updateSubmitState);
sellerPhoneField?.addEventListener("input", handleRuPhoneInput);
sellerPhoneField?.addEventListener("blur", handleRuPhoneInput);
form?.elements.currency?.addEventListener("change", updateSummary);
openSummaryModalButton?.addEventListener("click", openSummaryModal);
closeSummaryModalButtons.forEach((button) => {
  button.addEventListener("click", closeSummaryModal);
});
document.addEventListener("keydown", (event) => {
  if (event.key === "Escape" && summaryModal && !summaryModal.hidden) {
    closeSummaryModal();
  }
});

bootstrap();
