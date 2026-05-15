const adminBody = document.querySelector("[data-admin-theme]");
const pageLinks = [...document.querySelectorAll("[data-admin-link]")];
const pages = [...document.querySelectorAll("[data-admin-page]")];
const tabButtons = [...document.querySelectorAll("[data-admin-tab]")];
const tabPanels = [...document.querySelectorAll("[data-admin-tab-panel]")];
const themeToggle = document.querySelector("[data-theme-toggle]");
const themeLabel = document.querySelector("[data-theme-label]");

function setTheme(theme) {
  const isLight = theme === "light";
  adminBody?.classList.toggle("admin-theme-light", isLight);
  if (themeLabel) {
    themeLabel.textContent = isLight ? "Modo oscuro" : "Modo claro";
  }
  localStorage.setItem("portfolio-signal-admin-theme", isLight ? "light" : "dark");
}

function setPage(pageId) {
  const nextPage = pages.some((page) => page.dataset.adminPage === pageId) ? pageId : "overview";

  pages.forEach((page) => {
    const isActive = page.dataset.adminPage === nextPage;
    page.classList.toggle("is-active", isActive);
    page.hidden = !isActive;
  });

  pageLinks.forEach((link) => {
    link.classList.toggle("is-active", link.dataset.adminLink === nextPage);
  });
}

function setTab(tabId) {
  tabButtons.forEach((button) => {
    button.classList.toggle("is-active", button.dataset.adminTab === tabId);
  });

  tabPanels.forEach((panel) => {
    const isActive = panel.dataset.adminTabPanel === tabId;
    panel.classList.toggle("is-active", isActive);
    panel.hidden = !isActive;
  });
}

pageLinks.forEach((link) => {
  link.addEventListener("click", () => {
    setPage(link.dataset.adminLink);
  });
});

tabButtons.forEach((button) => {
  button.addEventListener("click", () => setTab(button.dataset.adminTab));
});

themeToggle?.addEventListener("click", () => {
  const nextTheme = adminBody?.classList.contains("admin-theme-light") ? "dark" : "light";
  setTheme(nextTheme);
});

window.addEventListener("hashchange", () => {
  setPage(window.location.hash.replace("#", ""));
});

const storedTheme = localStorage.getItem("portfolio-signal-admin-theme");
const preferredTheme = window.matchMedia("(prefers-color-scheme: light)").matches ? "light" : "dark";
setTheme(storedTheme || preferredTheme);
setPage(window.location.hash.replace("#", "") || "overview");
