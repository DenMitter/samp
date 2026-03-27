const apiBase = document.body.dataset.apiBase || "/api";
const storageKey = "escrow_mvp_auth";
const homePath = document.body.dataset.homeUrl || "/";

const state = {
  token: localStorage.getItem(storageKey) || "",
  user: null,
  offer: null,
  toastTimer: null,
};

const pageRoot = document.querySelector("[data-offer-page]");
const offerId = pageRoot?.dataset.offerId || "";
const dashboardPath = pageRoot?.dataset.dashboardUrl || "/dashboard";
const transactionBasePath = `${homePath.replace(/\/$/, "")}/transactions`;
const toast = document.querySelector("[data-toast]");
const userInitial = document.querySelector("[data-user-initial]");
const userName = document.querySelector("[data-user-name]");
const adminLink = document.querySelector("[data-admin-link]");

function showToast(message, isError = false) {
  if (!toast) return;
  toast.hidden = false;
  toast.textContent = message;
  toast.style.background = isError ? "#a33030" : "#01426a";
  window.clearTimeout(state.toastTimer);
  state.toastTimer = window.setTimeout(() => {
    toast.hidden = true;
  }, 3600);
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
    throw Object.assign(new Error(payload.message || "Не удалось загрузить офер."), {
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

function formatDate(value) {
  if (!value) return "—";
  return new Intl.DateTimeFormat("ru-RU", {
    day: "numeric",
    month: "long",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  }).format(new Date(value));
}

function badgeClass(status) {
  const value = String(status).toLowerCase();
  if (value === "accepted") return "mvpAccountBadge--green";
  if (value === "draft") return "mvpAccountBadge--yellow";
  return "mvpAccountBadge--red";
}

function statusLabel(status) {
  const map = {
    draft: "Черновик",
    accepted: "Принят",
  };

  return map[String(status).toLowerCase()] || String(status);
}

function renderHistory(offer) {
  const items = [
    {
      date: offer.created_at,
      text: "Офер создан и ожидает согласования второй стороной.",
    },
  ];

  if (offer.accepted_at) {
    items.push({
      date: offer.accepted_at,
      text: "Офер принят, на его основе создана транзакция.",
    });
  }

  return items.map((item) => `
    <div class="mvpTransactionHistoryItem">
      <strong>${formatDate(item.date)}</strong>
      <p>${item.text}</p>
    </div>
  `).join("");
}

function renderOffer(offer) {
  state.offer = offer;

  if (userInitial) {
    userInitial.textContent = (state.user?.name || "U").trim().charAt(0).toUpperCase();
  }

  if (userName) {
    userName.textContent = state.user?.name || "Кабинет";
  }

  if (adminLink) {
    adminLink.hidden = !state.user?.is_admin;
  }

  document.querySelector("[data-offer-title]").textContent = offer.title || `Офер #${offer.id}`;
  document.querySelector("[data-offer-reference]").textContent = `Офер #${offer.id}`;
  document.querySelector("[data-offer-summary]").textContent = `Тип актива: ${offer.asset_type || "general"}. После принятия этого офера будет создана полноценная транзакция.`;

  const badge = document.querySelector("[data-offer-status]");
  badge.textContent = statusLabel(offer.status);
  badge.className = `mvpTransactionBadge mvpAccountBadge ${badgeClass(offer.status)}`;

  document.querySelector("[data-offer-details]").innerHTML = `
    <div><strong>${offer.title || "Офер"}</strong></div>
    <div>${offer.description || "Описание офера отсутствует."}</div>
  `;
  document.querySelector("[data-offer-subtotal]").textContent = formatMoney(offer.amount, offer.currency);
  document.querySelector("[data-offer-currency]").textContent = offer.currency || "USD";
  document.querySelector("[data-offer-asset]").textContent = offer.asset_type || "general";
  document.querySelector("[data-offer-history]").innerHTML = renderHistory(offer);

  const actionTitle = document.querySelector("[data-offer-action-title]");
  const actionText = document.querySelector("[data-offer-action-text]");
  const actionButton = document.querySelector("[data-offer-primary-action]");

  if ((offer.status || "").toLowerCase() === "accepted" && offer.transaction) {
    actionTitle.textContent = "Офер уже принят";
    actionText.textContent = "По этому оферу уже создана транзакция. Вы можете перейти сразу к сделке.";
    actionButton.textContent = "Открыть транзакцию";
    actionButton.dataset.action = "open-transaction";
    actionButton.dataset.transactionId = offer.transaction.uuid || offer.transaction.id;
    return;
  }

  actionTitle.textContent = "Проверьте условия офера";
  actionText.textContent = "Если все условия вас устраивают, примите офер и перейдите к транзакции.";
  actionButton.textContent = "Принять офер";
  actionButton.dataset.action = "accept-offer";
}

async function handlePrimaryAction() {
  const button = document.querySelector("[data-offer-primary-action]");
  if (!button || !state.offer) return;

  const action = button.dataset.action;
  if (!action) return;

  if (action === "open-transaction") {
    window.location.href = `${transactionBasePath}/${button.dataset.transactionId}`;
    return;
  }

  button.disabled = true;

  try {
    const payload = await apiRequest(`/offers/${state.offer.id}/accept`, { method: "POST" });
    showToast(payload.message || "Офер принят.");

    if (payload.transaction?.id) {
      window.location.href = `${transactionBasePath}/${payload.transaction.uuid || payload.transaction.id}`;
      return;
    }

    const refreshed = await apiRequest(`/offers/${state.offer.id}`);
    renderOffer(refreshed);
  } catch (error) {
    showToast(error.message, true);
  } finally {
    button.disabled = false;
  }
}

async function logout() {
  try {
    await apiRequest("/logout", { method: "POST" });
  } catch {
    // ignore transport errors
  }

  localStorage.removeItem(storageKey);
  window.location.href = homePath;
}

function bindUi() {
  document.querySelector("[data-offer-primary-action]")?.addEventListener("click", handlePrimaryAction);
  document.querySelector("[data-logout]")?.addEventListener("click", logout);
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

bindUi();
bootstrap();
