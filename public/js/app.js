const apiBase = document.body.dataset.apiBase || "/api";
const storageKey = "escrow_mvp_auth";
const dashboardPath = document.body.dataset.dashboardUrl || "/dashboard";
const signupPath = document.body.dataset.signupUrl || "/signup";
const createOfferPath = document.body.dataset.createOfferUrl || "/offers/create";

const state = {
  token: localStorage.getItem(storageKey) || "",
  user: null,
  toastTimer: null,
};

const guestAuth = document.querySelector("[data-auth-guest]");
const userAuth = document.querySelector("[data-auth-user]");
const userName = document.querySelector("[data-user-name]");
const userInitials = document.querySelectorAll("[data-user-initial]");
const adminLinks = document.querySelectorAll("[data-admin-link]");
const headerToggle = document.querySelector("[data-header-toggle]");
const headerPanel = document.querySelector("[data-header-panel]");
const toast = document.querySelector("[data-toast]");
const startButton = document.querySelector("[data-start-transaction]");
const calculatorForm = document.querySelector(".calculator-form");

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

function redirectToDashboard() {
  window.location.href = dashboardPath;
}

function closeHeaderMenu() {
  if (!headerToggle || !headerPanel) return;
  headerToggle.setAttribute("aria-expanded", "false");
  document.body.classList.remove("is-header-open");
}

function openHeaderMenu() {
  if (!headerToggle || !headerPanel) return;
  headerToggle.setAttribute("aria-expanded", "true");
  document.body.classList.add("is-header-open");
}

function toggleHeaderMenu() {
  if (!headerToggle) return;
  const isExpanded = headerToggle.getAttribute("aria-expanded") === "true";
  if (isExpanded) {
    closeHeaderMenu();
    return;
  }

  openHeaderMenu();
}

async function apiRequest(path, options = {}) {
  const headers = {
    Accept: "application/json",
    ...(options.body ? { "Content-Type": "application/json" } : {}),
    ...(options.headers || {}),
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
    const firstError = payload.errors
      ? Object.values(payload.errors).flat()[0]
      : payload.message;
    throw new Error(firstError || "Произошла ошибка запроса.");
  }

  return payload;
}

function updateAuthView() {
  const isLoggedIn = Boolean(state.user && state.token);
  if (guestAuth) guestAuth.hidden = isLoggedIn;
  if (userAuth) userAuth.hidden = !isLoggedIn;
  if (userName) userName.textContent = isLoggedIn ? state.user.name : "";
  adminLinks.forEach((node) => {
    node.hidden = !(isLoggedIn && state.user?.is_admin);
  });
  userInitials.forEach((node) => {
    node.textContent = isLoggedIn ? (state.user.name || "U").trim().charAt(0).toUpperCase() : "U";
  });
}

async function loadCurrentUser() {
  if (!state.token) {
    state.user = null;
    updateAuthView();
    return;
  }

  try {
    const payload = await apiRequest("/me");
    state.user = payload.user;
  } catch (error) {
    state.token = "";
    state.user = null;
    localStorage.removeItem(storageKey);
  }

  updateAuthView();
}

function getOfferPayload() {
  const formData = new FormData(calculatorForm);
  const role = formData.get("role");
  const service = String(formData.get("service") || "").trim();
  const price = Number(formData.get("price") || 0);
  const currency = String(formData.get("currency") || "USD").trim();

  return {
    title: service ? `Сделка: ${service}` : "Новая escrow-сделка",
    description: `Роль: ${role || "Продать"}`,
    asset_type: service || "general",
    currency,
    amount: price > 0 ? price : 800,
  };
}

async function createOfferFromHero() {
  if (!state.token) {
    window.location.href = signupPath;
    return;
  }
  const draft = getOfferPayload();
  sessionStorage.setItem("escrow_offer_draft", JSON.stringify(draft));
  window.location.href = createOfferPath;
}

async function logout() {
  try {
    await apiRequest("/logout", { method: "POST" });
  } catch (error) {
    // Ignore logout transport errors and clear local session anyway.
  }

  state.token = "";
  state.user = null;
  localStorage.removeItem(storageKey);
  updateAuthView();
  showToast("Вы вышли из кабинета.");
}

function bindEvents() {
  headerToggle?.addEventListener("click", toggleHeaderMenu);
  headerPanel?.querySelectorAll("a").forEach((link) => {
    link.addEventListener("click", closeHeaderMenu);
  });

  if (startButton) {
    startButton.addEventListener("click", createOfferFromHero);
  }

  document.querySelector("[data-logout]")?.addEventListener("click", logout);

  document.querySelector("[data-open-dashboard]")?.addEventListener("click", (event) => {
    event.preventDefault();
    redirectToDashboard();
  });

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape") {
      closeHeaderMenu();
    }
  });

  document.addEventListener("click", (event) => {
    if (!headerToggle || !headerPanel) return;
    const target = event.target;
    if (!(target instanceof Node)) return;
    if (headerToggle.contains(target) || headerPanel.contains(target)) return;
    closeHeaderMenu();
  });

  window.addEventListener("resize", () => {
    if (window.innerWidth > 640) {
      closeHeaderMenu();
    }
  });
}

bindEvents();
loadCurrentUser();
