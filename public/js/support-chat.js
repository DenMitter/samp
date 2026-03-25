const supportChatRoot = document.querySelector("[data-support-chat]");
const supportChatMessages = document.querySelector("[data-support-chat-messages]");
const supportChatForm = document.querySelector("[data-support-chat-form]");
const supportChatInput = document.querySelector("[data-support-chat-input]");
const supportChatEmail = document.querySelector("[data-support-chat-email]");
const supportChatPhone = document.querySelector("[data-support-chat-phone]");
const supportChatStorageKey = "escrow_support_chat_messages";

const defaultMessages = [
  {
    role: "support",
    text: "Здравствуйте. Это техподдержка Escrow. Напишите, пожалуйста, номер сделки или кратко опишите вопрос.",
  },
];

function getStoredMessages() {
  try {
    const value = JSON.parse(localStorage.getItem(supportChatStorageKey) || "[]");
    return Array.isArray(value) && value.length ? value : defaultMessages;
  } catch {
    return defaultMessages;
  }
}

function saveMessages(messages) {
  localStorage.setItem(supportChatStorageKey, JSON.stringify(messages));
}

function renderMessages() {
  if (!supportChatMessages) return;
  const messages = getStoredMessages();
  supportChatMessages.innerHTML = messages
    .map(
      (message) => `
        <div class="mvpSupportChat-message mvpSupportChat-message--${message.role}">
          <span>${message.text}</span>
        </div>
      `
    )
    .join("");
  supportChatMessages.scrollTop = supportChatMessages.scrollHeight;
}

function openSupportChat() {
  if (!supportChatRoot) return;
  supportChatRoot.hidden = false;
  document.body.classList.add("is-modal-open");
  renderMessages();
  window.setTimeout(() => {
    supportChatInput?.focus();
  }, 50);
}

function closeSupportChat() {
  if (!supportChatRoot) return;
  supportChatRoot.hidden = true;
  document.body.classList.remove("is-modal-open");
}

function buildSupportReply(text) {
  const normalized = String(text || "").toLowerCase();

  if (normalized.includes("кошел") || normalized.includes("usdt") || normalized.includes("оплат")) {
    return "Проверьте адрес escrow-кошелька, сеть USDT и сумму «Итого». Если средства уже отправлены, нажмите «Продолжить оплату» для повторной on-chain проверки.";
  }

  if (normalized.includes("сделк") || normalized.includes("офер") || normalized.includes("услов")) {
    return "Если условия сделки менялись, вторая сторона должна подтвердить изменения. После этого этап будет обновлён автоматически.";
  }

  if (normalized.includes("сид") || normalized.includes("seed") || normalized.includes("wallet")) {
    return "Seed-фраза отображается только после создания escrow-кошелька. Также её можно посмотреть в админке сделки, если у вас есть доступ администратора.";
  }

  return "Сообщение получено. Если вопрос требует ручной проверки, техподдержка свяжется с вами по email. Пока можете указать номер сделки и кратко описать проблему.";
}

function appendMessage(role, text) {
  const messages = getStoredMessages();
  messages.push({ role, text });
  saveMessages(messages);
  renderMessages();
}

supportChatForm?.addEventListener("submit", (event) => {
  event.preventDefault();
  const text = String(supportChatInput?.value || "").trim();
  if (!text) return;

  appendMessage("user", text);
  supportChatInput.value = "";

  window.setTimeout(() => {
    appendMessage("support", buildSupportReply(text));
  }, 450);
});

document.querySelectorAll("[data-support-chat-open]").forEach((link) => {
  link.addEventListener("click", (event) => {
    event.preventDefault();
    openSupportChat();
  });
});

document.querySelectorAll("[data-support-chat-close]").forEach((button) => {
  button.addEventListener("click", closeSupportChat);
});

document.addEventListener("keydown", (event) => {
  if (event.key === "Escape" && supportChatRoot && !supportChatRoot.hidden) {
    closeSupportChat();
  }
});

if (supportChatEmail && document.body.dataset.supportEmail) {
  supportChatEmail.href = `mailto:${document.body.dataset.supportEmail}`;
  supportChatEmail.textContent = document.body.dataset.supportEmail;
}

if (supportChatPhone && document.body.dataset.supportPhone) {
  const digits = document.body.dataset.supportPhone.replace(/[^\d+]/g, "");
  supportChatPhone.href = `tel:${digits}`;
  supportChatPhone.textContent = document.body.dataset.supportPhone;
}

renderMessages();
