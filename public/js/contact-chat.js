const chatRoot = document.querySelector("[data-chat]");
const csrfToken = document.querySelector("meta[name='csrf-token']")?.getAttribute("content");

if (chatRoot && csrfToken) {
  const openButtons = document.querySelectorAll("[data-chat-open]");
  const closeButton = chatRoot.querySelector("[data-chat-close]");
  const messagesBox = chatRoot.querySelector("[data-chat-messages]");
  const replyForm = chatRoot.querySelector("[data-chat-reply-form]");
  const replyInput = replyForm?.querySelector("input[name='body']");
  const storageKey = "portfolio-chat-session";
  let session = JSON.parse(localStorage.getItem(storageKey) || "null");
  let pollTimer = null;

  function routeFromTemplate(template) {
    return template.replace("__SESSION__", session.id);
  }

  function renderMessages(messages) {
    messagesBox.innerHTML = "";

    messages.forEach((message) => {
      const item = document.createElement("p");
      item.className = `chat-message ${message.sender}`;

      const label = document.createElement("span");
      label.textContent = message.sender === "admin" ? "Josue" : message.sender === "visitor" ? "Tu" : "Asistente";

      item.append(label, document.createTextNode(message.body));
      messagesBox.append(item);
    });

    messagesBox.scrollTop = messagesBox.scrollHeight;
  }

  function syncReplyPrompt() {
    const prompts = {
      name: "Escribe tu nombre",
      email: "Escribe tu email",
      phone: "Escribe tu telefono",
      topic: "Cuentame de tu proyecto",
    };

    if (replyInput) {
      replyInput.placeholder = prompts[session?.next_field] || "Escribe un mensaje directo";
      replyInput.type = session?.next_field === "email" ? "email" : "text";
    }
  }

  async function request(url, options = {}) {
    const response = await fetch(url, {
      headers: {
        "Accept": "application/json",
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": csrfToken,
        ...(options.headers || {}),
      },
      ...options,
    });

    if (!response.ok) {
      throw new Error("No se pudo conectar el chat.");
    }

    return response.json();
  }

  async function startChat() {
    const payload = await request(chatRoot.dataset.startUrl, {
      method: "POST",
      body: JSON.stringify({ session_key: session?.session_key }),
    });

    session = payload.session;
    localStorage.setItem(storageKey, JSON.stringify(session));
    renderMessages(payload.messages);
    replyForm.hidden = false;
    syncReplyPrompt();
    startPolling();
  }

  async function refreshMessages() {
    if (!session?.id) {
      return;
    }

    const payload = await request(`${routeFromTemplate(chatRoot.dataset.messagesUrlTemplate)}?session_key=${encodeURIComponent(session.session_key)}`);
    session = payload.session;
    localStorage.setItem(storageKey, JSON.stringify(session));
    renderMessages(payload.messages);
    syncReplyPrompt();
  }

  function startPolling() {
    window.clearInterval(pollTimer);
    pollTimer = window.setInterval(refreshMessages, 6000);
  }

  function openChat() {
    chatRoot.classList.add("is-open");
    chatRoot.setAttribute("aria-hidden", "false");
    startChat().catch(() => {
      messagesBox.innerHTML = '<p class="chat-message bot"><span>Asistente</span>No pude iniciar el chat. Intenta de nuevo en un momento.</p>';
    });
  }

  function closeChat() {
    chatRoot.classList.remove("is-open");
    chatRoot.setAttribute("aria-hidden", "true");
  }

  openButtons.forEach((button) => button.addEventListener("click", openChat));
  closeButton?.addEventListener("click", closeChat);

  replyForm?.addEventListener("submit", async (event) => {
    event.preventDefault();

    const input = replyForm.querySelector("input[name='body']");
    const body = input.value.trim();

    if (!body || !session?.id) {
      return;
    }

    const data = await request(routeFromTemplate(chatRoot.dataset.messagesUrlTemplate), {
      method: "POST",
      body: JSON.stringify({ body, session_key: session.session_key }),
    });

    session = data.session;
    localStorage.setItem(storageKey, JSON.stringify(session));
    input.value = "";
    renderMessages(data.messages);
    syncReplyPrompt();
  });
}
