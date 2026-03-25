const apiBase = document.body.dataset.apiBase || "/api";
const storageKey = "escrow_mvp_auth";
const dashboardPath = document.body.dataset.dashboardUrl || "/dashboard";

const form = document.querySelector("[data-login-form]");
const message = document.querySelector("[data-login-message]");
const submitButton = document.querySelector("[data-login-submit]");
const toast = document.querySelector("[data-toast]");

function translateError(text) {
  const value = String(text || "").trim();

  const map = {
    "The provided credentials are incorrect.": "Неверный email или пароль.",
    "Unauthenticated.": "Необходимо войти в аккаунт.",
    "Too many login attempts. Please try again in :seconds seconds.": "Слишком много попыток входа. Попробуйте позже.",
    "The email field is required.": "Поле email обязательно.",
    "The password field is required.": "Поле пароля обязательно.",
    "The email must be a valid email address.": "Введите корректный email.",
  };

  if (map[value]) {
    return map[value];
  }

  if (value.includes("Too many login attempts")) {
    return "Слишком много попыток входа. Попробуйте позже.";
  }

  return value || "Не удалось выполнить вход.";
}

async function ensureGuest() {
  const token = localStorage.getItem(storageKey);

  if (!token) {
    return;
  }

  try {
    const response = await fetch(`${apiBase}/me`, {
      headers: {
        Accept: "application/json",
        Authorization: `Bearer ${token}`,
      },
    });

    if (!response.ok) {
      throw new Error("invalid session");
    }

    window.location.href = dashboardPath;
  } catch {
    localStorage.removeItem(storageKey);
  }
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

function showToast(text, isError = false) {
  if (!toast) return;
  toast.hidden = false;
  toast.textContent = text;
  toast.style.background = isError ? "#a33030" : "#01426a";
  window.setTimeout(() => {
    toast.hidden = true;
  }, 3200);
}

async function submitLogin() {
  const body = Object.fromEntries(new FormData(form).entries());

  submitButton.disabled = true;
  setMessage("");

  try {
    const response = await fetch(`${apiBase}/login`, {
      method: "POST",
      headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
      },
      body: JSON.stringify(body),
    });

    const payload = await response.json().catch(() => ({}));

    if (!response.ok) {
      const firstError = payload.errors
        ? Object.values(payload.errors).flat()[0]
        : payload.message;
      throw new Error(translateError(firstError || payload.message));
    }

    localStorage.setItem(storageKey, payload.token);
    showToast("Вход выполнен успешно.");
    window.location.href = dashboardPath;
  } catch (error) {
    setMessage(translateError(error.message), true);
  } finally {
    submitButton.disabled = false;
  }
}

form?.addEventListener("submit", (event) => {
  event.preventDefault();

  if (form.checkValidity()) {
    submitLogin();
  } else {
    form.reportValidity();
  }
});

submitButton?.addEventListener("click", () => {
  form?.requestSubmit();
});

ensureGuest();
