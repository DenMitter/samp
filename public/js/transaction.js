const apiBase = document.body.dataset.apiBase || "/api";
const homePath = document.body.dataset.homeUrl || "/";
const csrfToken = document.body.dataset.csrfToken || "";

const state = {
  user: null,
  transaction: null,
  toastTimer: null,
};

const pageRoot = document.querySelector("[data-transaction-page]");
const transactionId = pageRoot?.dataset.transactionId || "";
const transactionPaymentUrl = pageRoot?.dataset.transactionPaymentUrl || "";
const dashboardPath = pageRoot?.dataset.dashboardUrl || "/dashboard";
const toast = document.querySelector("[data-toast]");
const userInitial = document.querySelector("[data-user-initial]");
const userName = document.querySelector("[data-user-name]");
const adminLink = document.querySelector("[data-admin-link]");
const modifyModal = document.querySelector("[data-transaction-modify-modal]");
const modifyForm = document.querySelector("[data-transaction-modify-form]");
const modifyMessage = document.querySelector("[data-transaction-modify-message]");
const paymentModal = document.querySelector("[data-payment-modal]");
const paymentForm = document.querySelector("[data-payment-form]");
const paymentMessage = document.querySelector("[data-payment-message]");
const disbursementModal = document.querySelector("[data-disbursement-modal]");
const disbursementForm = document.querySelector("[data-disbursement-form]");
const disbursementMessage = document.querySelector("[data-disbursement-message]");

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
    throw Object.assign(new Error(payload.message || "Не удалось загрузить сделку."), {
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

function calculateEscrowFee(amount) {
  return Math.max(Number(amount || 0) * 0.024, 25);
}

function statusLabel(status) {
  const map = {
    pending: "Ожидает согласования",
    funded: "Оплата подтверждена",
    approved: "Ожидает оплаты",
    released: "Закрыто",
    unpaid: "Не оплачено",
    pending_confirmation: "Ожидает подтверждения оплаты",
    paid: "Оплачено",
  };

  return map[String(status).toLowerCase()] || String(status);
}

function statusBadgeClass(status) {
  const value = String(status).toLowerCase();

  if (["released", "paid", "approved", "funded"].includes(value)) {
    return "mvpAccountBadge--green";
  }

  if (["pending", "unpaid", "pending_confirmation"].includes(value)) {
    return "mvpAccountBadge--yellow";
  }

  return "mvpAccountBadge--red";
}

function getStage(status, paymentStatus) {
  if (String(status).toLowerCase() === "released") return 5;
  if (String(paymentStatus).toLowerCase() === "pending_confirmation") return 3;
  if (["funded"].includes(String(status).toLowerCase()) || String(paymentStatus).toLowerCase() === "paid") return 4;
  if (["approved"].includes(String(status).toLowerCase())) return 2;
  return 1;
}

function buildSummary(transaction) {
  const buyer = transaction.buyer?.email || "Покупатель";
  const seller = transaction.seller?.email || "Продавец";
  const asset = transaction.offer?.asset_type || transaction.meta?.asset_type || "товар";
  const inspection = transaction.inspection_period_days || 1;

  return `${buyer} покупает ${asset} у ${seller}. Период проверки по этой сделке: ${inspection} календ. дн.`;
}

function updateHeaderUser() {
  if (userInitial) {
    userInitial.textContent = (state.user?.name || "U").trim().charAt(0).toUpperCase();
  }

  if (userName) {
    userName.textContent = state.user?.name || "Кабинет";
  }

  if (adminLink) {
    adminLink.hidden = !state.user?.is_admin;
  }
}

function buildDetails(transaction) {
  const meta = transaction.meta || {};
  const offer = transaction.offer || {};
  const title = offer.title || meta.item_name || "Предмет сделки";
  const description = offer.description || meta.description || "Описание будет добавлено после согласования условий.";
  const lines = [
    `<strong>${title}</strong>`,
    description,
  ];

  if (meta.vin) lines.push(`VIN: ${meta.vin}`);
  if (meta.odometer) lines.push(`Пробег: ${meta.odometer}`);
  if (meta.notes) lines.push(meta.notes);

  return lines.map((line) => `<div>${line}</div>`).join("");
}

function clearModifyErrors() {
  document.querySelectorAll("[data-modify-error-for]").forEach((node) => {
    node.textContent = "";
  });
}

function setModifyError(name, text) {
  const field = document.querySelector(`[data-modify-error-for="${name}"]`);
  if (field) {
    field.textContent = text;
  }
}

function setModifyMessage(text, isError = false) {
  if (!modifyMessage) return;
  if (!text) {
    modifyMessage.hidden = true;
    modifyMessage.textContent = "";
    modifyMessage.classList.remove("is-error");
    return;
  }

  modifyMessage.hidden = false;
  modifyMessage.textContent = text;
  modifyMessage.classList.toggle("is-error", isError);
}

function setInlineMessage(node, text, isError = false) {
  if (!node) return;
  if (!text) {
    node.hidden = true;
    node.textContent = "";
    node.classList.remove("is-error");
    return;
  }

  node.hidden = false;
  node.textContent = text;
  node.classList.toggle("is-error", isError);
}

function openModifyModal() {
  if (!modifyModal || !modifyForm || !state.transaction) return;
  const meta = state.transaction.meta || {};

  modifyForm.elements.inspection_period_days.value = state.transaction.inspection_period_days || 1;
  modifyForm.elements.fee_paid_by.value = meta.fee_paid_by || "buyer";
  modifyForm.elements.shipping_method.value = meta.shipping_method || "cargo_shipping";
  modifyForm.elements.shipping_paid_by.value = meta.shipping_paid_by || "seller";
  modifyForm.elements.modification_reason.value = meta.modification_reason || "";

  clearModifyErrors();
  setModifyMessage("");
  modifyModal.hidden = false;
  document.body.classList.add("is-modal-open");
}

function closeModifyModal() {
  if (!modifyModal) return;
  modifyModal.hidden = true;
  document.body.classList.remove("is-modal-open");
}

function openPaymentModal() {
  if (!paymentModal || !paymentForm || !state.transaction) return;
  const meta = state.transaction.meta || {};
  const feePaidBy = String(meta.fee_paid_by || "buyer").toLowerCase();
  const amount = Number(state.transaction.amount || 0);
  const fee = calculateEscrowFee(amount);
  const total = feePaidBy === "buyer" ? amount + fee : feePaidBy === "split" ? amount + (fee / 2) : amount;

  paymentForm.elements.provider.value = "wire_transfer";
  paymentForm.elements.amount_preview.value = formatMoney(total, state.transaction.currency);
  paymentForm.elements.external_reference.value = "";
  setInlineMessage(paymentMessage, "");
  paymentModal.hidden = false;
  document.body.classList.add("is-modal-open");
}

function closePaymentModal() {
  if (!paymentModal) return;
  paymentModal.hidden = true;
  document.body.classList.remove("is-modal-open");
}

function openDisbursementModal() {
  if (!disbursementModal || !disbursementForm || !state.transaction) return;
  const meta = state.transaction.meta || {};
  disbursementForm.elements.disbursement_method.value = meta.disbursement_method || "bank_transfer";
  disbursementForm.elements.disbursement_details.value = meta.disbursement_details || "";
  setInlineMessage(disbursementMessage, "");
  disbursementModal.hidden = false;
  document.body.classList.add("is-modal-open");
}

function closeDisbursementModal() {
  if (!disbursementModal) return;
  disbursementModal.hidden = true;
  document.body.classList.remove("is-modal-open");
}

function renderHistory(transaction) {
  const history = [
    {
      date: transaction.created_at,
      text: "Сделка создана и отправлена на согласование.",
    },
  ];

  if ((transaction.payment_status || "").toLowerCase() === "paid") {
    history.push({
      date: transaction.payments?.[0]?.paid_at || transaction.updated_at,
      text: "Оплата по сделке подтверждена второй стороной.",
    });
  } else if ((transaction.payment_status || "").toLowerCase() === "pending_confirmation") {
    history.push({
      date: transaction.meta?.payment_submitted_at || transaction.payments?.[0]?.paid_at || transaction.updated_at,
      text: "Покупатель отправил оплату. Ожидается подтверждение платежа второй стороной.",
    });
  } else if (transaction.approved_at) {
    history.push({
      date: transaction.approved_at,
      text: "Обе стороны согласовали условия сделки, ожидается оплата покупателя.",
    });
  }

  if (transaction.meta?.modification_updated_at && transaction.meta?.last_modified_by_email) {
    history.push({
      date: transaction.meta.modification_updated_at,
      text: `Условия сделки были изменены пользователем ${transaction.meta.last_modified_by_email}.`,
    });
  }

  if (transaction.meta?.payment_confirmed_at) {
    history.push({
      date: transaction.meta.payment_confirmed_at,
      text: "Вторая сторона подтвердила поступление оплаты.",
    });
  }

  if (transaction.released_at) {
    history.push({
      date: transaction.released_at,
      text: "Средства переведены продавцу, сделка закрыта.",
    });
  }

  return history
    .map((item) => `
      <div class="mvpTransactionHistoryItem">
        <strong>${formatDate(item.date)}</strong>
        <p>${item.text}</p>
      </div>
    `)
    .join("");
}

function renderActionBox(transaction) {
  const stage = getStage(transaction.status, transaction.payment_status);
  const title = document.querySelector("[data-action-title]");
  const text = document.querySelector("[data-action-text]");
  const button = document.querySelector("[data-primary-action]");
  const secondaryButton = document.querySelector("[data-open-modify-modal]");
  const isCreator = Number(transaction.offer?.creator_id || 0) === Number(state.user?.id || 0);
  const isBuyer = Number(transaction.buyer_id || 0) === Number(state.user?.id || 0);
  const isSeller = Number(transaction.seller_id || 0) === Number(state.user?.id || 0);
  const awaitingConfirmationFrom = Number(transaction.meta?.awaiting_confirmation_from_user_id || 0);
  const hasPendingModification = Boolean(transaction.meta?.modification_pending) && awaitingConfirmationFrom !== 0;
  const lastModifiedByEmail = transaction.meta?.last_modified_by_email || "другой стороной";
  const hasDisbursementMethod = Boolean(transaction.meta?.disbursement_method);
  const counterpartyEmail = isCreator
    ? (transaction.seller?.email || transaction.buyer?.email || "второй стороны")
    : (transaction.buyer?.email || transaction.seller?.email || "создателя сделки");

  if (!title || !text || !button || !secondaryButton) return;

  button.hidden = false;
  button.disabled = false;
  secondaryButton.hidden = false;

  if (stage === 1) {
    if (hasPendingModification) {
      if (awaitingConfirmationFrom === Number(state.user?.id || 0)) {
        title.textContent = "Подтвердите изменения в сделке";
        text.textContent = `Пользователь ${lastModifiedByEmail} изменил условия сделки. Проверьте обновления и подтвердите их, чтобы перейти дальше.`;
        button.textContent = "Подтвердить изменения";
        button.dataset.action = "approve";
        secondaryButton.textContent = "Изменить офер";
        return;
      }

      title.textContent = "Ожидается подтверждение изменений";
      text.textContent = `Вы изменили условия сделки. Сейчас пользователь ${counterpartyEmail} должен подтвердить обновления, прежде чем сделка сможет перейти дальше.`;
      button.hidden = true;
      secondaryButton.textContent = "Изменить офер";
      return;
    }

    if (isCreator) {
      title.textContent = "Ожидается подтверждение второй стороны";
      text.textContent = `Вы уже отправили сделку пользователю ${counterpartyEmail}. Сейчас именно он должен открыть сделку и подтвердить условия.`;
      button.hidden = true;
      secondaryButton.textContent = "Изменить офер";
      return;
    }

    title.textContent = "Проверьте и согласуйте сделку";
    text.textContent = `Сделка отправлена вам пользователем ${counterpartyEmail}. Проверьте условия, описание и подтвердите согласование.`;
    button.textContent = "Согласиться со сделкой";
    button.dataset.action = "approve";
    secondaryButton.textContent = "Изменить офер";
    return;
  }

  if (stage === 2) {
    if (isBuyer) {
      title.textContent = "Оплатите сделку";
      text.textContent = "Нажмите на кнопку ниже, выберите способ оплаты и отправьте средства в Escrow. После этого продавец должен будет подтвердить оплату.";
      button.textContent = "Выбрать способ оплаты";
      button.dataset.action = "pay";
      secondaryButton.hidden = true;
      return;
    }

    if (isSeller) {
      title.textContent = "Выберите способ получения выплаты";
      text.textContent = hasDisbursementMethod
        ? "Способ выплаты уже сохранён. Теперь ожидайте оплату от покупателя."
        : "Выберите, как Escrow переведёт вам средства после завершения сделки. Затем ожидайте оплату от покупателя.";
      button.textContent = hasDisbursementMethod ? "Изменить способ выплаты" : "Выбрать способ выплаты";
      button.dataset.action = "disbursement";
      secondaryButton.hidden = true;
      return;
    }
  }

  if (stage === 3) {
    if (isBuyer) {
      title.textContent = "Ожидается подтверждение оплаты";
      text.textContent = `Вы отправили оплату. Сейчас пользователь ${transaction.seller?.email || "продавец"} должен подтвердить получение платежа, прежде чем сделка перейдёт дальше.`;
      button.hidden = true;
      secondaryButton.hidden = true;
      return;
    }

    if (isSeller) {
      title.textContent = "Подтвердите оплату";
      text.textContent = `Покупатель ${transaction.buyer?.email || "покупатель"} отправил оплату. Подтвердите платёж, чтобы перевести сделку на следующий этап.`;
      button.textContent = "Подтвердить оплату";
      button.dataset.action = "confirm-payment";
      secondaryButton.hidden = true;
      return;
    }
  }

  if (stage === 4) {
    title.textContent = isBuyer ? "Оплата подтверждена" : "Оплата подтверждена покупателем";
    text.textContent = "Платёж подтверждён второй стороной. Следующий шаг — выполнение условий сделки и проверка результата.";
    if (isBuyer) {
      button.textContent = "Выпустить средства";
      button.dataset.action = "release";
      secondaryButton.hidden = true;
      return;
    }

    button.hidden = true;
    secondaryButton.hidden = true;
    return;
  }

  title.textContent = "Сделка завершена";
  text.textContent = "Все этапы этой сделки завершены. Ниже можно просмотреть историю и детали.";
  button.hidden = true;
  secondaryButton.hidden = true;
}

function renderTransaction(transaction) {
  state.transaction = transaction;
  updateHeaderUser();

  document.querySelector("[data-transaction-title]").textContent = transaction.offer?.title || transaction.reference || `Сделка #${transaction.id}`;
  document.querySelector("[data-transaction-reference]").textContent = `Transaction #${transaction.reference || transaction.id}`;
  document.querySelector("[data-transaction-summary]").textContent = buildSummary(transaction);
  document.querySelector("[data-transaction-buyer]").textContent = transaction.buyer?.email || "Не указан";
  document.querySelector("[data-transaction-seller]").textContent = transaction.seller?.email || "Не указан";

  const badge = document.querySelector("[data-transaction-status]");
  badge.textContent = statusLabel(transaction.status);
  badge.className = `mvpTransactionBadge mvpAccountBadge ${statusBadgeClass(transaction.status)}`;

  const activeStage = getStage(transaction.status, transaction.payment_status);
  document.querySelectorAll("[data-step]").forEach((step) => {
    const value = Number(step.dataset.step || 0);
    step.classList.toggle("is-active", value === activeStage);
    step.classList.toggle("is-complete", value < activeStage);
  });

  renderActionBox(transaction);

  document.querySelector("[data-item-details]").innerHTML = buildDetails(transaction);
  document.querySelector("[data-payment-status]").textContent = statusLabel(transaction.payment_status);

  const amount = Number(transaction.amount || 0);
  const feePaidBy = String(transaction.meta?.fee_paid_by || "buyer").toLowerCase();
  const escrowFee = calculateEscrowFee(amount);
  const feeRow = document.querySelector("[data-total-fee-row]");
  const paymentFeesCard = document.querySelector("[data-payment-fees-card]");
  const isBuyer = Number(transaction.buyer_id || 0) === Number(state.user?.id || 0);
  document.querySelector("[data-total-subtotal]").textContent = formatMoney(amount, transaction.currency);
  document.querySelector("[data-total-fee]").textContent = formatMoney(escrowFee, transaction.currency);
  let grandTotal = amount;

  if (feePaidBy === "buyer") {
    grandTotal = amount + escrowFee;
    feeRow.hidden = false;
  } else if (feePaidBy === "split") {
    grandTotal = amount + (escrowFee / 2);
    feeRow.hidden = false;
  } else {
    grandTotal = amount;
    feeRow.hidden = true;
  }

  document.querySelector("[data-total-grand]").textContent = formatMoney(grandTotal, transaction.currency);
  if (paymentFeesCard) {
    paymentFeesCard.hidden = !isBuyer;
  }

  document.querySelector("[data-transaction-history]").innerHTML = renderHistory(transaction);
}

async function handleAction() {
  const button = document.querySelector("[data-primary-action]");
  if (!button || !state.transaction) return;

  const action = button.dataset.action;
  if (!action) return;

  button.disabled = true;

  try {
    let path = `/transactions/${state.transaction.id}/approve`;
    if (action === "release") {
      path = `/transactions/${state.transaction.id}/release`;
    } else if (action === "confirm-payment") {
      path = `/transactions/${state.transaction.id}/confirm-payment`;
    } else if (action === "pay") {
      window.location.href = transactionPaymentUrl || homePath;
      return;
    } else if (action === "disbursement") {
      openDisbursementModal();
      return;
    }

    const payload = await apiRequest(path, { method: "POST" });
    showToast(payload.message || "Статус сделки обновлен.");

    const refreshed = await apiRequest(`/transactions/${state.transaction.id}`);
    renderTransaction(refreshed);
  } catch (error) {
    showToast(error.message, true);
  } finally {
    button.disabled = false;
  }
}

async function handlePaymentSubmit(event) {
  event.preventDefault();
  if (!paymentForm || !state.transaction) return;

  const submitButton = paymentForm.querySelector("[data-payment-submit]");
  if (submitButton) submitButton.disabled = true;
  setInlineMessage(paymentMessage, "");

  const amount = Number(state.transaction.amount || 0);
  const fee = calculateEscrowFee(amount);
  const feePaidBy = String(state.transaction.meta?.fee_paid_by || "buyer").toLowerCase();
  const payableAmount = feePaidBy === "buyer" ? amount + fee : feePaidBy === "split" ? amount + (fee / 2) : amount;

  try {
    const payload = await apiRequest(`/transactions/${state.transaction.id}/payments`, {
      method: "POST",
      body: {
        amount: payableAmount,
        provider: String(paymentForm.elements.provider.value || "wire_transfer").trim(),
        external_reference: String(paymentForm.elements.external_reference.value || "").trim(),
        meta: {
          payment_method_label: paymentForm.elements.provider.options[paymentForm.elements.provider.selectedIndex]?.text || "Оплата",
        },
      },
    });

    renderTransaction(payload.transaction);
    closePaymentModal();
    showToast(payload.message || "Оплата зафиксирована.");
  } catch (error) {
    setInlineMessage(paymentMessage, error.message || "Не удалось зафиксировать оплату.", true);
  } finally {
    if (submitButton) submitButton.disabled = false;
  }
}

async function handleDisbursementSubmit(event) {
  event.preventDefault();
  if (!disbursementForm || !state.transaction) return;

  const submitButton = disbursementForm.querySelector("[data-disbursement-submit]");
  if (submitButton) submitButton.disabled = true;
  setInlineMessage(disbursementMessage, "");

  try {
    const payload = await apiRequest(`/transactions/${state.transaction.id}/disbursement`, {
      method: "POST",
      body: {
        disbursement_method: String(disbursementForm.elements.disbursement_method.value || "bank_transfer").trim(),
        disbursement_details: String(disbursementForm.elements.disbursement_details.value || "").trim(),
      },
    });

    renderTransaction(payload.transaction);
    closeDisbursementModal();
    showToast(payload.message || "Способ выплаты сохранён.");
  } catch (error) {
    setInlineMessage(disbursementMessage, error.message || "Не удалось сохранить способ выплаты.", true);
  } finally {
    if (submitButton) submitButton.disabled = false;
  }
}

async function handleModifySubmit(event) {
  event.preventDefault();

  if (!modifyForm || !state.transaction) return;

  clearModifyErrors();
  setModifyMessage("");

  const submitButton = modifyForm.querySelector("[data-confirm-modify]");
  if (submitButton) {
    submitButton.disabled = true;
  }

  const payload = {
    inspection_period_days: Number(modifyForm.elements.inspection_period_days.value || 1),
    meta: {
      fee_paid_by: String(modifyForm.elements.fee_paid_by.value || "buyer").trim(),
      shipping_method: String(modifyForm.elements.shipping_method.value || "cargo_shipping").trim(),
      shipping_paid_by: String(modifyForm.elements.shipping_paid_by.value || "seller").trim(),
      modification_reason: String(modifyForm.elements.modification_reason.value || "").trim(),
    },
  };

  try {
    const result = await apiRequest(`/transactions/${state.transaction.id}`, {
      method: "PATCH",
      body: payload,
    });

    renderTransaction(result.transaction);
    closeModifyModal();
    showToast(result.message || "Условия сделки обновлены.");
  } catch (error) {
    const errors = error.payload?.errors || {};
    Object.entries(errors).forEach(([key, values]) => {
      const normalizedKey = String(key || "").split(".").pop();
      setModifyError(normalizedKey, Array.isArray(values) ? values[0] : values);
    });
    setModifyMessage(error.message || "Не удалось обновить условия сделки.", true);
  } finally {
    if (submitButton) {
      submitButton.disabled = false;
    }
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
  document.querySelector("[data-primary-action]")?.addEventListener("click", handleAction);
  document.querySelector("[data-open-modify-modal]")?.addEventListener("click", openModifyModal);
  document.querySelectorAll("[data-close-modify-modal]").forEach((button) => {
    button.addEventListener("click", closeModifyModal);
  });
  modifyForm?.addEventListener("submit", handleModifySubmit);
  document.querySelectorAll("[data-close-payment-modal]").forEach((button) => {
    button.addEventListener("click", closePaymentModal);
  });
  document.querySelectorAll("[data-close-disbursement-modal]").forEach((button) => {
    button.addEventListener("click", closeDisbursementModal);
  });
  paymentForm?.addEventListener("submit", handlePaymentSubmit);
  disbursementForm?.addEventListener("submit", handleDisbursementSubmit);
  document.querySelector("[data-cancel-transaction]")?.addEventListener("click", () => {
    showToast("Функция отмены сделки будет подключена следующим шагом.", true);
  });
  document.querySelector("[data-logout]")?.addEventListener("click", logout);
  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && modifyModal && !modifyModal.hidden) {
      closeModifyModal();
    }
    if (event.key === "Escape" && paymentModal && !paymentModal.hidden) {
      closePaymentModal();
    }
    if (event.key === "Escape" && disbursementModal && !disbursementModal.hidden) {
      closeDisbursementModal();
    }
  });
}

async function bootstrap() {
  if (!transactionId) {
    window.location.href = homePath;
    return;
  }

  try {
    const [me, transaction] = await Promise.all([
      apiRequest("/me"),
      apiRequest(`/transactions/${transactionId}`),
    ]);

    state.user = me.user;
    renderTransaction(transaction);
  } catch (error) {
    showToast(error.message, true);
    if (error.status === 401) {
      window.setTimeout(() => {
        window.location.href = dashboardPath;
      }, 1200);
    }
  }
}

bindUi();
bootstrap();
