const apiBase = document.body.dataset.apiBase || "/api";
const homePath = document.body.dataset.homeUrl || "/";
const csrfToken = document.body.dataset.csrfToken || "";

const state = {
  user: null,
  records: [],
  query: "",
  toastTimer: null,
};

const toast = document.querySelector("[data-toast]");
const tableBody = document.querySelector("[data-admin-transactions-table]");
const countLabel = document.querySelector("[data-admin-records-count]");
const searchInput = document.querySelector("[data-admin-search-input]");
const walletModal = document.querySelector("[data-admin-wallet-modal]");
const walletAddressNode = document.querySelector("[data-admin-wallet-address]");
const walletSeedGrid = document.querySelector("[data-admin-wallet-seed-grid]");
const userInitial = document.querySelector("[data-user-initial]");
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
    throw Object.assign(new Error(payload.message || "Не удалось загрузить данные админки."), {
      status: response.status,
      payload,
    });
  }

  return payload;
}

function escapeHtml(value) {
  return String(value ?? "")
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;")
    .replaceAll("'", "&#39;");
}

function statusLabel(record) {
  return `${String(record.status || "pending")} / ${String(record.payment_status || "unpaid")}`;
}

function filterRecords() {
  const query = state.query.trim().toLowerCase();
  if (!query) return state.records;

  return state.records.filter((record) => [
    record.reference,
    record.offer?.title,
    record.buyer?.email,
    record.seller?.email,
    record.meta?.escrow_wallet?.address,
    record.uuid,
    record.id,
  ].some((value) => String(value ?? "").toLowerCase().includes(query)));
}

function renderTable() {
  if (!tableBody) return;
  const records = filterRecords();

  if (countLabel) {
    countLabel.textContent = `Вы просматриваете ${records.length} сделок`;
  }

  if (!records.length) {
    tableBody.innerHTML = `
      <tr class="mvpAccountEmptyRow">
        <td colspan="6">Сделки не найдены.</td>
      </tr>
    `;
    return;
  }

  tableBody.innerHTML = records.map((record) => {
    const wallet = record.meta?.escrow_wallet;

    return `
      <tr>
        <td class="mvpAccountId">${escapeHtml(record.id)}</td>
        <td>
          <p class="mvpAccountName">${escapeHtml(record.offer?.title || record.reference || `Transaction #${record.id}`)}</p>
          <p class="mvpAccountMeta">${escapeHtml(record.uuid || "")}</p>
        </td>
        <td>${escapeHtml(record.buyer?.email || "—")}</td>
        <td>${escapeHtml(record.seller?.email || "—")}</td>
        <td>
          ${wallet?.address ? `
            <div class="mvpAdminWalletPreview">
              <code>${escapeHtml(wallet.address)}</code>
              <button type="button" class="mvpCreatedCopy" data-admin-wallet-open="${escapeHtml(record.id)}">Открыть</button>
            </div>
          ` : '<span class="mvpAccountMeta">Не создан</span>'}
        </td>
        <td>${escapeHtml(statusLabel(record))}</td>
      </tr>
    `;
  }).join("");
}

function renderWallet(wallet) {
  if (!walletAddressNode || !walletSeedGrid) return;
  walletAddressNode.textContent = wallet?.address || "—";
  walletSeedGrid.innerHTML = Array.isArray(wallet?.seed)
    ? wallet.seed.map((word, index) => `<span class="mvpWalletSeed"><strong>${index + 1}.</strong> ${escapeHtml(word)}</span>`).join("")
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

async function openWallet(transactionId) {
  try {
    const payload = await apiRequest(`/admin/transactions/${transactionId}`);
    const wallet = payload.transaction?.meta?.escrow_wallet;

    if (!wallet?.address) {
      throw new Error("У этой сделки кошелёк ещё не создан.");
    }

    renderWallet(wallet);
    openWalletModal();
  } catch (error) {
    showToast(error.message || "Не удалось открыть кошелёк.", true);
  }
}

async function copyWalletAddress() {
  const address = walletAddressNode?.textContent?.trim() || "";
  if (!address || address === "—") return;

  try {
    await navigator.clipboard.writeText(address);
    showToast("Адрес кошелька скопирован.");
  } catch {
    showToast("Не удалось скопировать адрес.", true);
  }
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
  searchInput?.addEventListener("input", () => {
    state.query = searchInput.value;
    renderTable();
  });

  tableBody?.addEventListener("click", (event) => {
    const button = event.target.closest("[data-admin-wallet-open]");
    if (!button) return;
    openWallet(button.dataset.adminWalletOpen);
  });

  document.querySelectorAll("[data-close-admin-wallet-modal]").forEach((button) => {
    button.addEventListener("click", closeWalletModal);
  });

  document.querySelector("[data-admin-copy-wallet-address]")?.addEventListener("click", copyWalletAddress);
  document.querySelector("[data-logout]")?.addEventListener("click", logout);

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && walletModal && !walletModal.hidden) {
      closeWalletModal();
    }
  });
}

async function bootstrapAdmin() {
  try {
    const me = await apiRequest("/me");
    state.user = me.user;

    if (!state.user?.is_admin) {
      throw new Error("Доступ в админку запрещён.");
    }

    if (userInitial) {
      userInitial.textContent = (state.user.name || "A").trim().charAt(0).toUpperCase();
    }

    if (adminLink) {
      adminLink.hidden = false;
    }

    const payload = await apiRequest("/admin/transactions");
    state.records = payload.transactions || [];
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
bootstrapAdmin();
