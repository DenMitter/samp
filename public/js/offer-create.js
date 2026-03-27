const apiBase = document.body.dataset.apiBase || "/api";
const storageKey = "escrow_mvp_auth";
const homePath = document.body.dataset.homeUrl || "/";

const pageRoot = document.querySelector("[data-offer-create-page]");
const offerShowBase = pageRoot?.dataset.offerShowBase || "/offers/";
const offerStartTemplate = pageRoot?.dataset.offerStartTemplate || "/offers/__ID__/start";
const form = document.querySelector("[data-offer-create-form]");
const message = document.querySelector("[data-offer-create-message]");
const submitButton = document.querySelector("[data-offer-create-submit]");
const toast = document.querySelector("[data-toast]");
const draftKey = "escrow_offer_draft";
const assetTypeField = form?.elements.asset_type;
const dynamicFieldsRoot = document.querySelector("[data-dynamic-fields]");

const dynamicFieldGroups = {
  vehicle: {
    categories: ["cars-trucks", "motorcycles", "boats", "airplanes", "other-motor-vehicles", "trailers", "special-equipment"],
    html: `
      <div class="mvpStartSection">
        <h2>Дополнительные параметры транспорта</h2>
        <div class="mvpStartGrid mvpStartGrid--two">
          <label class="mvpStartField">
            <input type="text" name="meta_make" placeholder="Марка" data-meta-field>
          </label>
          <label class="mvpStartField">
            <input type="text" name="meta_model" placeholder="Модель" data-meta-field>
          </label>
          <label class="mvpStartField">
            <input type="number" name="meta_year" placeholder="Год" data-meta-field>
          </label>
          <label class="mvpStartField">
            <input type="number" name="meta_odometer" placeholder="Пробег" data-meta-field>
          </label>
        </div>
        <label class="mvpStartField is-full">
          <span>VIN / серийный номер</span>
          <input type="text" name="meta_vin" placeholder="VIN или серийный номер" data-meta-field>
        </label>
        <div class="mvpStartGrid mvpStartGrid--two">
          <label class="mvpStartField">
            <span>Способ доставки</span>
            <select name="meta_shipping_method" data-meta-field>
              <option value="cargo">Грузовая доставка</option>
              <option value="pickup">Самовывоз</option>
              <option value="dealer">Передача через дилера</option>
              <option value="carrier">Транспортная компания</option>
            </select>
          </label>
          <label class="mvpStartField">
            <span>Доставку оплачивает</span>
            <select name="meta_shipping_paid_by" data-meta-field>
              <option value="seller">Продавец</option>
              <option value="buyer">Покупатель</option>
              <option value="split">Поровну</option>
            </select>
          </label>
        </div>
        <label class="mvpStartAddon">
          <input type="checkbox" name="meta_title_collection_service" data-meta-field>
          <span>
            <strong>Подключить услугу проверки и передачи документов</strong>
            <small>Подходит для авто, мотоциклов, катеров и другой техники с документами собственности.</small>
          </span>
          <em>Рекомендуем</em>
        </label>
        <label class="mvpStartAddon">
          <input type="checkbox" name="meta_lien_holder_service" data-meta-field>
          <span>
            <strong>Подключить услугу работы с залогодержателем</strong>
            <small>Полезно, если у транспорта есть кредит, лизинг или иное обременение.</small>
          </span>
          <em>Рекомендуем</em>
        </label>
      </div>
    `,
  },
  domain: {
    categories: ["domain", "website", "online-business", "mobile-app", "hosting-account", "social-media-account", "digital-assets"],
    html: `
      <div class="mvpStartSection">
        <h2>Данные цифрового актива</h2>
        <div class="mvpStartGrid mvpStartGrid--two">
          <label class="mvpStartField">
            <input type="text" name="meta_domain_name" placeholder="Домен / URL" data-meta-field>
          </label>
          <label class="mvpStartField">
            <input type="text" name="meta_registrar" placeholder="Регистратор / платформа" data-meta-field>
          </label>
        </div>
        <div class="mvpStartGrid mvpStartGrid--two">
          <label class="mvpStartField">
            <span>Тип передачи</span>
            <select name="meta_transfer_type" data-meta-field>
              <option value="push">Push внутри регистратора</option>
              <option value="auth-code">Перенос по auth-code</option>
              <option value="full-business-transfer">Полная передача проекта</option>
            </select>
          </label>
          <label class="mvpStartField">
            <input type="text" name="meta_monthly_revenue" placeholder="Месячная выручка (если есть)" data-meta-field>
          </label>
        </div>
        <label class="mvpStartField is-full">
          <textarea name="meta_included_assets" rows="3" placeholder="Что входит в сделку: сайт, домен, код, база, контент, соцсети и т.д." data-meta-field></textarea>
        </label>
      </div>
    `,
  },
  service: {
    categories: ["broker-services", "freelance-services", "milestone-services", "consulting", "software-development", "marketing-services", "design-services"],
    html: `
      <div class="mvpStartSection">
        <h2>Параметры услуги</h2>
        <div class="mvpStartGrid mvpStartGrid--two">
          <label class="mvpStartField">
            <input type="text" name="meta_scope" placeholder="Объём работ / scope" data-meta-field>
          </label>
          <label class="mvpStartField">
            <input type="text" name="meta_deadline" placeholder="Срок выполнения" data-meta-field>
          </label>
        </div>
        <label class="mvpStartField is-full">
          <textarea name="meta_deliverables" rows="3" placeholder="Что именно должно быть передано по итогу работы" data-meta-field></textarea>
        </label>
        <label class="mvpStartField is-full">
          <textarea name="meta_milestones" rows="3" placeholder="Этапы и условия поэтапной оплаты" data-meta-field></textarea>
        </label>
      </div>
    `,
  },
  collectibles: {
    categories: ["antiques-collectibles", "art", "jewelry", "luxury-goods", "watches", "musical-instruments"],
    html: `
      <div class="mvpStartSection">
        <h2>Параметры предмета</h2>
        <div class="mvpStartGrid mvpStartGrid--two">
          <label class="mvpStartField">
            <input type="text" name="meta_brand" placeholder="Бренд / автор / производитель" data-meta-field>
          </label>
          <label class="mvpStartField">
            <input type="text" name="meta_condition" placeholder="Состояние" data-meta-field>
          </label>
        </div>
        <div class="mvpStartGrid mvpStartGrid--two">
          <label class="mvpStartField">
            <input type="text" name="meta_serial_number" placeholder="Серийный номер / артикул" data-meta-field>
          </label>
          <label class="mvpStartField">
            <input type="text" name="meta_provenance" placeholder="Происхождение / документы" data-meta-field>
          </label>
        </div>
      </div>
    `,
  },
};

function buildOfferUrl(id) {
  return `${offerShowBase}${id}`;
}

function buildOfferStartUrl(id) {
  return offerStartTemplate.replace("__ID__", String(id));
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
  const field = document.querySelector(`[data-error-for="${name}"]`);
  if (field) {
    field.textContent = text;
  }
}

async function ensureAuth() {
  const token = localStorage.getItem(storageKey);
  if (!token) {
    window.location.href = document.body.dataset.signupUrl || "/signup";
    return null;
  }

  const response = await fetch(`${apiBase}/me`, {
    headers: {
      Accept: "application/json",
      Authorization: `Bearer ${token}`,
    },
  });

  if (!response.ok) {
    localStorage.removeItem(storageKey);
    window.location.href = document.body.dataset.loginUrl || "/login";
    return null;
  }

  return token;
}

function normalizeError(text) {
  const value = String(text || "").trim();
  const map = {
    "The title field is required.": "Укажите название сделки.",
    "The asset type field is required.": "Укажите категорию товара.",
    "The amount field is required.": "Укажите сумму сделки.",
    "The amount field must be at least 1.": "Сумма должна быть больше нуля.",
    "The currency field is required.": "Укажите валюту.",
  };

  return map[value] || value || "Не удалось создать офер.";
}

function preloadDraft() {
  if (!form) return;

  try {
    const raw = sessionStorage.getItem(draftKey);
    if (!raw) return;

    const draft = JSON.parse(raw);
    if (draft.title) form.elements.title.value = draft.title;
    if (draft.asset_type) form.elements.asset_type.value = draft.asset_type;
    if (draft.currency) form.elements.currency.value = draft.currency;
    if (draft.amount) form.elements.amount.value = draft.amount;
    if (draft.description) form.elements.description.value = draft.description;
  } catch {
    // ignore invalid draft payload
  }
}


function renderDynamicFields() {
  if (!dynamicFieldsRoot || !assetTypeField) return;

  const selectedCategory = String(assetTypeField.value || "");
  const matched = Object.values(dynamicFieldGroups).find((group) => group.categories.includes(selectedCategory));

  if (!matched) {
    dynamicFieldsRoot.hidden = true;
    dynamicFieldsRoot.innerHTML = "";
    return;
  }

  dynamicFieldsRoot.hidden = false;
  dynamicFieldsRoot.innerHTML = matched.html;
}

async function submitForm(event) {
  event.preventDefault();
  clearFieldErrors();
  setMessage("");

  const token = await ensureAuth();
  if (!token) return;

  submitButton.disabled = true;

  const formData = new FormData(form);
  const body = {
    title: String(formData.get("title") || "").trim(),
    asset_type: String(formData.get("asset_type") || "").trim(),
    currency: String(formData.get("currency") || "USD").trim(),
    amount: Number(formData.get("amount") || 0),
    description: String(formData.get("description") || "").trim(),
    meta: {
      role: String(formData.get("role") || "buyer").trim(),
      inspection_period_days: Number(formData.get("inspection_period_days") || 1),
      item_name: String(formData.get("item_name") || "").trim(),
    },
  };

  form.querySelectorAll("[data-meta-field]").forEach((field) => {
    if (!field.name) return;

    const key = field.name.replace(/^meta_/, "");
    if (field.type === "checkbox") {
      body.meta[key] = field.checked;
      return;
    }

    const value = String(formData.get(field.name) || "").trim();
    if (value !== "") {
      body.meta[key] = value;
    }
  });

  try {
    const response = await fetch(`${apiBase}/offers`, {
      method: "POST",
      headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
        Authorization: `Bearer ${token}`,
      },
      body: JSON.stringify(body),
    });

    const payload = await response.json().catch(() => ({}));

    if (!response.ok) {
      if (payload.errors) {
        Object.entries(payload.errors).forEach(([key, value]) => {
          setFieldError(key, normalizeError(Array.isArray(value) ? value[0] : value));
        });
      }

      const firstError = payload.errors
        ? Object.values(payload.errors).flat()[0]
        : payload.message;

      throw new Error(normalizeError(firstError));
    }

    showToast("Офер создан.");
    sessionStorage.removeItem(draftKey);
    window.location.href = buildOfferStartUrl(payload.id);
  } catch (error) {
    setMessage(normalizeError(error.message), true);
  } finally {
    submitButton.disabled = false;
  }
}

form?.addEventListener("submit", submitForm);
assetTypeField?.addEventListener("change", renderDynamicFields);
preloadDraft();
renderDynamicFields();
ensureAuth().catch(() => {
  localStorage.removeItem(storageKey);
  window.location.href = document.body.dataset.loginUrl || "/login";
});
