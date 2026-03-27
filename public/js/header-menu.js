const headerToggle = document.querySelector("[data-header-toggle]");
const headerPanel = document.querySelector("[data-header-panel]");

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

headerToggle?.addEventListener("click", toggleHeaderMenu);

headerPanel?.querySelectorAll("a").forEach((link) => {
  link.addEventListener("click", closeHeaderMenu);
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
