const storageKey = "portfolio-editorial-theme";
const toggle = document.querySelector("[data-theme-toggle]");
const themeLabel = document.querySelector("[data-theme-label]");

function applyTheme(theme) {
  const isDark = theme === "dark";
  document.documentElement.dataset.editorialTheme = isDark ? "dark" : "light";
  document.body.classList.toggle("theme-dark", isDark);

  if (themeLabel) {
    themeLabel.textContent = isDark ? "Modo claro" : "Modo oscuro";
  } else if (toggle) {
    toggle.textContent = isDark ? "Modo claro" : "Modo oscuro";
  }
}

const savedTheme =
  localStorage.getItem(storageKey) ||
  document.documentElement.dataset.editorialTheme ||
  "light";
applyTheme(savedTheme);

toggle?.addEventListener("click", () => {
  const nextTheme = document.body.classList.contains("theme-dark") ? "light" : "dark";
  localStorage.setItem(storageKey, nextTheme);
  document.body.classList.remove("theme-shifting");
  window.requestAnimationFrame(() => {
    document.body.classList.add("theme-shifting");
  });
  applyTheme(nextTheme);
});

document.querySelector(".theme-wash")?.addEventListener("animationend", () => {
  document.body.classList.remove("theme-shifting");
});

const modalTriggers = document.querySelectorAll("[data-modal-open]");
const modalCloseButtons = document.querySelectorAll("[data-modal-close]");
let activeModal = null;
let lastFocusedElement = null;

function openModal(modalId) {
  const modal = document.querySelector(`[data-modal="${modalId}"]`);

  if (!modal) {
    return;
  }

  lastFocusedElement = document.activeElement;
  activeModal = modal;
  modal.classList.add("is-open");
  modal.setAttribute("aria-hidden", "false");
  modal.querySelector("[role='dialog']")?.focus();
}

function closeModal() {
  if (!activeModal) {
    return;
  }

  activeModal.classList.remove("is-open");
  activeModal.setAttribute("aria-hidden", "true");
  activeModal = null;

  if (lastFocusedElement instanceof HTMLElement) {
    lastFocusedElement.focus();
  }
}

modalTriggers.forEach((trigger) => {
  trigger.addEventListener("click", () => {
    openModal(trigger.dataset.modalOpen);
  });
});

modalCloseButtons.forEach((button) => {
  button.addEventListener("click", closeModal);
});

document.addEventListener("click", (event) => {
  if (event.target === activeModal) {
    closeModal();
  }
});

document.addEventListener("keydown", (event) => {
  if (event.key === "Escape") {
    closeModal();
  }
});
