const apiBase = document.body.dataset.apiBase || "/api";
const homePath = document.body.dataset.homeUrl || "/";

const state = {
  transaction: null,
  toastTimer: null,
};

const pageRoot = document.querySelector("[data-transaction-created-page]");
const transactionId = pageRoot?.dataset.transactionId || "";
const transactionKey = pageRoot?.dataset.transactionKey || "";
const transactionLookupKey = transactionKey || transactionId;
const transactionBase = pageRoot?.dataset.transactionBase || "/transactions/";
const dashboardPath = pageRoot?.dataset.dashboardUrl || "/dashboard";
const toast = document.querySelector("[data-toast]");

function buildTransactionUrl(key) {
  return `${transactionBase}${key}`;
}

function showToast(text, isError = false) {
  if (!toast) return;
  toast.hidden = false;
  toast.textContent = text;
  toast.style.background = isError ? "#a33030" : "#01426a";
  window.clearTimeout(state.toastTimer);
  state.toastTimer = window.setTimeout(() => {
    toast.hidden = true;
  }, 3200);
}

async function apiRequest(path) {
  const headers = {
    Accept: "application/json",
  };

  const response = await fetch(`${apiBase}${path}`, {
    headers,
    credentials: "same-origin",
  });

  const payload = await response.json().catch(() => ({}));

  if (!response.ok) {
    throw Object.assign(new Error(payload.message || "Не удалось загрузить транзакцию."), {
      status: response.status,
      payload,
    });
  }

  return payload;
}

function bindShareLinks(url) {
  const encodedUrl = encodeURIComponent(url);
  const encodedText = encodeURIComponent(`Пожалуйста, откройте эту Escrow-транзакцию: ${url}`);

  const whatsapp = document.querySelector("[data-share-whatsapp]");
  const email = document.querySelector("[data-share-email]");
  const messenger = document.querySelector("[data-share-messenger]");
  const twitter = document.querySelector("[data-share-twitter]");
  const viewLink = document.querySelector("[data-view-transaction]");
  const linkText = document.querySelector("[data-created-link-text]");
  const qr = document.querySelector("[data-created-qr]");

  if (whatsapp) whatsapp.href = `https://wa.me/?text=${encodedText}`;
  if (email) email.href = `mailto:?subject=${encodeURIComponent("Создана новая Escrow-транзакция")}&body=${encodedText}`;
  if (messenger) messenger.href = `https://www.facebook.com/dialog/send?link=${encodedUrl}&app_id=291494419107518&redirect_uri=${encodedUrl}`;
  if (twitter) twitter.href = `https://twitter.com/intent/tweet?text=${encodedText}`;
  if (viewLink) viewLink.href = url;
  if (linkText) linkText.textContent = url;
  if (qr) qr.src = `https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=${encodedUrl}`;
}

async function copyLink() {
  const url = buildTransactionUrl(state.transaction?.uuid || transactionLookupKey);

  try {
    await navigator.clipboard.writeText(url);
    showToast("Ссылка скопирована.");
  } catch {
    showToast("Не удалось скопировать ссылку.", true);
  }
}

async function bootstrap() {
  if (!transactionLookupKey) {
    window.location.href = homePath;
    return;
  }

  try {
    state.transaction = await apiRequest(`/transactions/${transactionLookupKey}`);
    bindShareLinks(buildTransactionUrl(state.transaction.uuid || state.transaction.id));
  } catch (error) {
    showToast(error.message, true);
    if (error.status === 401) {
      window.setTimeout(() => {
        window.location.href = dashboardPath;
      }, 1200);
    }
  }
}

document.querySelector("[data-copy-link]")?.addEventListener("click", copyLink);

bootstrap();
