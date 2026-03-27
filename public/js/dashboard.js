const apiBase = document.body.dataset.apiBase || "/api";
const homePath = document.body.dataset.homeUrl || "/";
const transactionBasePath = `${homePath.replace(/\/$/, "")}/transactions`;
const offerBasePath = `${homePath.replace(/\/$/, "")}/offers`;
const csrfToken = document.body.dataset.csrfToken || "";

const state = {
  toastTimer: null,
  user: null,
  records: [],
  activeFilter: "action",
  query: "",
};

const toast = document.querySelector("[data-toast]");
const userInitial = document.querySelector("[data-user-initial]");
const adminLink = document.querySelector("[data-admin-link]");
const tableBody = document.querySelector("[data-transactions-table]");
const countLabel = document.querySelector("[data-records-count]");
const searchInput = document.querySelector("[data-search-input]");

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
    throw Object.assign(new Error(payload.message || "Не удалось загрузить данные кабинета."), {
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
  return new Intl.DateTimeFormat("en-US", {
    month: "short",
    day: "numeric",
    year: "numeric",
  }).format(new Date(value));
}

function escapeHtml(value) {
  return String(value ?? "")
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;")
    .replaceAll("'", "&#39;");
}

function normalizeRecords(dashboard) {
  const transactions = (dashboard.transactions || []).map((item) => ({
    type: "transaction",
    id: item.id,
    key: item.uuid || item.id,
    title: item.reference || `Transaction #${item.id}`,
    subtitle: item.offer?.asset_type || "Escrow Transaction",
    createdAt: item.created_at,
    amount: item.amount,
    currency: item.currency,
    role: state.user?.id === item.buyer_id ? "Buyer" : "Seller",
    primaryStatus: item.status,
    secondaryStatus: item.payment_status,
    actionRequired: ["pending", "funded", "approved"].includes(item.status),
    closed: item.status === "released",
    href: `${transactionBasePath}/${item.uuid || item.id}`,
  }));

  const offers = (dashboard.offers || []).map((item) => ({
    type: "offer",
    id: item.id,
    title: item.title || `Offer #${item.id}`,
    subtitle: item.asset_type || "Offer",
    createdAt: item.created_at,
    amount: item.amount,
    currency: item.currency,
    role: state.user?.id === item.creator_id ? "Creator" : "Participant",
    primaryStatus: item.status,
    secondaryStatus: item.status === "accepted" ? "Accepted" : "Requires your Action",
    actionRequired: item.status !== "accepted" && item.status !== "released",
    closed: item.status === "accepted",
    href: `${offerBasePath}/${item.id}`,
  }));

  return [...transactions, ...offers].sort((a, b) => new Date(b.createdAt) - new Date(a.createdAt));
}

function badgeClass(status) {
  if (["released", "paid", "accepted", "approved"].includes(status)) {
    return "mvpAccountBadge--green";
  }

  if (["requires your action", "pending", "draft", "unpaid"].includes(String(status).toLowerCase())) {
    return "mvpAccountBadge--red";
  }

  return "mvpAccountBadge--yellow";
}

function statusLabel(status) {
  const map = {
    draft: "Awaiting Agreement",
    pending: "Awaiting Agreement",
    funded: "Funded",
    approved: "Approved",
    released: "Released",
    unpaid: "Requires your Action",
    paid: "Paid",
    accepted: "Accepted",
  };

  return map[String(status).toLowerCase()] || String(status);
}

function filterRecords() {
  const query = state.query.trim().toLowerCase();

  return state.records.filter((record) => {
    if (state.activeFilter === "action" && !record.actionRequired) return false;
    if (state.activeFilter === "open" && record.closed) return false;
    if (state.activeFilter === "closed" && !record.closed) return false;
    if (!query) return true;

    return [
      record.title,
      record.subtitle,
      record.role,
      record.primaryStatus,
      record.secondaryStatus,
      String(record.id),
    ].some((value) => String(value).toLowerCase().includes(query));
  });
}

function renderTable() {
  if (!tableBody) return;

  const records = filterRecords();

  if (countLabel) {
    countLabel.textContent = `Вы просматриваете ${records.length} ${records.length === 1 ? "запись" : "записей"}`;
  }

  if (!records.length) {
    tableBody.innerHTML = `
      <tr class="mvpAccountEmptyRow">
        <td colspan="6">По этому фильтру ничего не найдено.</td>
      </tr>
    `;
    return;
  }

  tableBody.innerHTML = records.map((record) => `
    <tr ${record.href ? `class="mvpAccountRowLink" data-href="${escapeHtml(record.href)}"` : ""}>
      <td class="mvpAccountId">${escapeHtml(record.id)}</td>
      <td>
        <p class="mvpAccountName">${escapeHtml(record.title)}</p>
        <p class="mvpAccountMeta">${escapeHtml(record.subtitle)}</p>
      </td>
      <td>${escapeHtml(formatDate(record.createdAt))}</td>
      <td>
        <div class="mvpAccountAmount">
          <strong>${escapeHtml(formatMoney(record.amount, record.currency))}</strong>
          <span class="mvpAccountAmountCurrency">${escapeHtml(record.currency || "USD")}</span>
        </div>
      </td>
      <td>${escapeHtml(record.role)}</td>
      <td>
        <div class="mvpAccountBadgeStack">
          <span class="mvpAccountBadge ${badgeClass(record.primaryStatus)}">${escapeHtml(statusLabel(record.primaryStatus))}</span>
          <span class="mvpAccountBadge ${badgeClass(record.secondaryStatus)}">${escapeHtml(statusLabel(record.secondaryStatus))}</span>
        </div>
      </td>
    </tr>
  `).join("");
}

async function logout() {
  await fetch("/logout", {
    method: "POST",
    headers: {
      "X-CSRF-TOKEN": csrfToken,
      Accept: "text/html,application/xhtml+xml",
    },
    credentials: "same-origin",
  });
  window.location.href = homePath;
}

function bindUi() {
  document.querySelectorAll("[data-filter-tab]").forEach((button) => {
    button.addEventListener("click", () => {
      state.activeFilter = button.dataset.filterTab || "all";

      document.querySelectorAll("[data-filter-tab]").forEach((item) => {
        item.classList.toggle("is-active", item === button);
      });

      renderTable();
    });
  });

  searchInput?.addEventListener("input", () => {
    state.query = searchInput.value;
    renderTable();
  });

  tableBody?.addEventListener("click", (event) => {
    const row = event.target.closest("[data-href]");
    if (!row) return;
    window.location.href = row.dataset.href;
  });

  document.querySelector("[data-logout]")?.addEventListener("click", logout);
}

async function bootstrapDashboard() {
  try {
    const [me, dashboard] = await Promise.all([
      apiRequest("/me"),
      apiRequest("/dashboard"),
    ]);

    state.user = me.user;
    state.records = normalizeRecords(dashboard);

    if (userInitial) {
      userInitial.textContent = (me.user.name || "U").trim().charAt(0).toUpperCase();
    }

    if (adminLink) {
      adminLink.hidden = !me.user?.is_admin;
    }

    Object.entries(dashboard.stats || {}).forEach(([key, value]) => {
      const stat = document.querySelector(`[data-stat="${key}"]`);
      if (stat) stat.textContent = String(value);
    });

    renderTable();
  } catch (error) {
    showToast(error.message, true);
    if (error.status === 401) {
      window.setTimeout(() => {
        window.location.href = homePath;
      }, 1200);
    }
  }
}

bindUi();
bootstrapDashboard();
