const apiBase = document.body.dataset.apiBase || "/api";
const dashboardPath = document.body.dataset.dashboardUrl || "/dashboard";
const signupPath = document.body.dataset.signupUrl || "/signup";
const createOfferPath = document.body.dataset.createOfferUrl || "/offers/create";
const isAuthenticated = document.body.dataset.authenticated === "1";

const state = {
  toastTimer: null,
};

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
  if (!isAuthenticated) {
    window.location.href = signupPath;
    return;
  }
  const draft = getOfferPayload();
  sessionStorage.setItem("escrow_offer_draft", JSON.stringify(draft));
  window.location.href = createOfferPath;
}

function bindEvents() {
  if (startButton) {
    startButton.addEventListener("click", createOfferFromHero);
  }
}

bindEvents();
