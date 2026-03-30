const apiBase = document.body.dataset.apiBase || "/api";
const homePath = document.body.dataset.homeUrl || "/";
const csrfToken = document.body.dataset.csrfToken || "";

const state = {
  transaction: null,
  toastTimer: null,
  payableTotal: 0,
};

const pageRoot = document.querySelector("[data-transaction-payment-page]");
const transactionId = pageRoot?.dataset.transactionId || "";
const transactionKey = pageRoot?.dataset.transactionKey || "";
const transactionLookupKey = transactionKey || transactionId;
const transactionUrl = pageRoot?.dataset.transactionUrl || "/dashboard";
const toast = document.querySelector("[data-toast]");
const form = document.querySelector("[data-payment-page-form]");
const message = document.querySelector("[data-payment-page-message]");
const walletModal = document.querySelector("[data-wallet-modal]");
const walletAddressNode = document.querySelector("[data-wallet-address]");
const walletSeedGrid = document.querySelector("[data-wallet-seed-grid]");

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

async function apiRequest(path, options = {}) {
  const method = options.method || "GET";
  const headers = {
    Accept: "application/json",
    ...(options.body ? { "Content-Type": "application/json" } : {}),
  };

  if (method !== "GET" && method !== "HEAD" && csrfToken) {
    headers["X-CSRF-TOKEN"] = csrfToken;
  }

  const response = await fetch(`${apiBase}${path}`, {
    method,
    headers,
    body: options.body ? JSON.stringify(options.body) : undefined,
    credentials: "same-origin",
  });

  const payload = await response.json().catch(() => ({}));

  if (!response.ok) {
    throw Object.assign(new Error(payload.message || "Не удалось выполнить запрос."), {
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

function calculateEscrowFee(amount) {
  return Math.max(Number(amount || 0) * 0.024, 25);
}

function renderTransaction(transaction) {
  state.transaction = transaction;
  const meta = transaction.meta || {};
  const offer = transaction.offer || {};
  const amount = Number(transaction.amount || 0);
  const fee = calculateEscrowFee(amount);
  const processingFee = 25;
  const feePaidBy = String(meta.fee_paid_by || "buyer").toLowerCase();

  let total = amount + processingFee;
  if (feePaidBy === "buyer") {
    total += fee;
  } else if (feePaidBy === "split") {
    total += fee / 2;
  }
  state.payableTotal = total;

  document.querySelector("[data-pay-item-name]").textContent = offer.title || meta.item_name || "Предмет сделки";
  document.querySelector("[data-pay-item-description]").textContent = offer.description || meta.description || "Описание отсутствует.";
  document.querySelector("[data-pay-inspection]").textContent = `Период проверки: ${transaction.inspection_period_days || 1} дн.`;
  document.querySelector("[data-pay-fee-payer]").textContent = `Комиссию Escrow оплачивает: ${feePaidBy === "seller" ? "продавец" : feePaidBy === "split" ? "обе стороны" : "покупатель"}`;
  document.querySelector("[data-pay-subtotal]").textContent = `${formatMoney(amount, transaction.currency)} ${transaction.currency}`;
  document.querySelector("[data-pay-fee]").textContent = `${formatMoney(fee, transaction.currency)} ${transaction.currency}`;
  document.querySelector("[data-pay-processing]").textContent = `${formatMoney(processingFee, transaction.currency)} ${transaction.currency}`;
  document.querySelector("[data-pay-total]").textContent = `${formatMoney(total, transaction.currency)} ${transaction.currency}`;

  const vin = document.querySelector("[data-pay-vin]");
  const odometer = document.querySelector("[data-pay-odometer]");
  if (meta.vin) {
    vin.hidden = false;
    vin.textContent = `VIN: ${meta.vin}`;
  }
  if (meta.odometer) {
    odometer.hidden = false;
    odometer.textContent = `Пробег: ${meta.odometer}`;
  }

  renderWallet(meta.escrow_wallet || null);
}

function renderWallet(wallet) {
  if (!walletAddressNode || !walletSeedGrid) return;
  walletAddressNode.textContent = wallet?.address || "—";
  walletSeedGrid.innerHTML = Array.isArray(wallet?.seed)
    ? wallet.seed.map((word, index) => `<span class="mvpWalletSeed"><strong>${index + 1}.</strong> ${word}</span>`).join("")
    : "";
}

function openWalletModal() {
  if (!walletModal) return;
  walletModal.hidden = false;
  document.body.classList.add("is-modal-open");
}

function closeWalletModal() {
  if (!walletModal) return;
  walletModal.hidden = true;
  document.body.classList.remove("is-modal-open");
}

async function createWallet() {
  if (!state.transaction) return;

  try {
    const payload = await apiRequest(`/transactions/${state.transaction.id}/wallet`, {
      method: "POST",
    });

    renderTransaction(payload.transaction);
    openWalletModal();
    showToast(payload.message || "Кошелёк создан.");
  } catch (error) {
    setMessage(error.message || "Не удалось создать кошелёк.", true);
  }
}

async function copyWalletAddress() {
  const address = state.transaction?.meta?.escrow_wallet?.address || "";
  if (!address) return;

  try {
    await navigator.clipboard.writeText(address);
    showToast("Адрес кошелька скопирован.");
  } catch {
    showToast("Не удалось скопировать адрес.", true);
  }
}

async function handleSubmit(event) {
  event.preventDefault();
  if (!form || !state.transaction) return;

  const submitButton = document.querySelector("[data-payment-page-submit]");
  if (submitButton) submitButton.disabled = true;
  setMessage("");

  const meta = state.transaction.meta || {};
  try {
    const provider = String(form.elements.provider.value || "escrow_wallet").trim();

    if (provider === "escrow_wallet" && !state.transaction?.meta?.escrow_wallet?.address) {
      throw new Error("Сначала создайте кошелёк Escrow.");
    }

    const payload = await apiRequest(`/transactions/${state.transaction.id}/payments`, {
      method: "POST",
      body: {
        amount: Number(state.payableTotal || 0),
        provider,
        external_reference: provider === "escrow_wallet"
          ? String(state.transaction.meta.escrow_wallet.address || "").trim()
          : "",
        meta: {
          payment_method_label: form.querySelector('input[name="provider"]:checked')?.value || "escrow_wallet",
        },
      },
    });

    showToast(payload.message || "Поступление средств подтверждено.");
    window.location.href = transactionUrl;
  } catch (error) {
    setMessage(error.message || "Не удалось подтвердить оплату.", true);
  } finally {
    if (submitButton) submitButton.disabled = false;
  }
}

async function bootstrap() {
  if (!transactionLookupKey) {
    window.location.href = homePath;
    return;
  }

  try {
    const transaction = await apiRequest(`/transactions/${transactionLookupKey}`);
    renderTransaction(transaction);
  } catch (error) {
    showToast(error.message, true);
    if (error.status === 401) {
      window.setTimeout(() => {
        window.location.href = homePath;
      }, 1200);
    }
  }
}

form?.addEventListener("submit", handleSubmit);
document.querySelector("[data-create-wallet]")?.addEventListener("click", createWallet);
document.querySelector("[data-copy-wallet-address]")?.addEventListener("click", copyWalletAddress);
document.querySelectorAll("[data-close-wallet-modal]").forEach((button) => {
  button.addEventListener("click", closeWalletModal);
});
document.addEventListener("keydown", (event) => {
  if (event.key === "Escape" && walletModal && !walletModal.hidden) {
    closeWalletModal();
  }
});

bootstrap();
