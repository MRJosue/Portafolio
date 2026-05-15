const canvas = document.querySelector("#ember-canvas, .motion-lines-canvas");
const siteThemeToggle = document.querySelector("[data-site-theme-toggle]");
const siteThemeLabel = document.querySelector("[data-site-theme-label]");
const siteThemeKey = "portfolio-signal-theme";

function applySiteTheme(theme) {
  const nextTheme = theme === "light" ? "light" : "dark";
  document.documentElement.dataset.signalTheme = nextTheme;

  if (siteThemeLabel) {
    siteThemeLabel.textContent = nextTheme === "light" ? "Modo oscuro" : "Modo claro";
  }
}

if (siteThemeToggle) {
  const storedTheme = localStorage.getItem(siteThemeKey);
  const preferredTheme = window.matchMedia("(prefers-color-scheme: light)").matches ? "light" : "dark";

  applySiteTheme(storedTheme || document.documentElement.dataset.signalTheme || preferredTheme);

  siteThemeToggle.addEventListener("click", () => {
    const nextTheme = document.documentElement.dataset.signalTheme === "light" ? "dark" : "light";
    localStorage.setItem(siteThemeKey, nextTheme);
    applySiteTheme(nextTheme);
  });
}

if (canvas) {
  const ctx = canvas.getContext("2d");
  let width = 0;
  let height = 0;
  let dpr = 1;
  let lines = [];
  const prefersReducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)");

  function resize() {
    dpr = Math.min(window.devicePixelRatio || 1, 2);
    width = window.innerWidth;
    height = window.innerHeight;
    canvas.width = Math.floor(width * dpr);
    canvas.height = Math.floor(height * dpr);
    canvas.style.width = `${width}px`;
    canvas.style.height = `${height}px`;
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

    const count = Math.max(24, Math.floor((width * height) / 36000));
    const diagonal = Math.hypot(width, height);
    const directions = [
      0,
      Math.PI / 2,
      Math.PI / 4,
      -Math.PI / 4,
      Math.PI * 0.16,
      -Math.PI * 0.64,
    ];

    lines = Array.from({ length: count }, (_, index) => {
      const angle = directions[index % directions.length] + (Math.random() - 0.5) * 0.18;
      const length = Math.min(diagonal * (0.18 + Math.random() * 0.22), 520);
      const centerX = Math.random() * width;
      const centerY = Math.random() * height;
      const dx = Math.cos(angle) * length * 0.5;
      const dy = Math.sin(angle) * length * 0.5;

      return {
        startX: centerX - dx,
        startY: centerY - dy,
        endX: centerX + dx,
        endY: centerY + dy,
        length,
        phase: Math.random(),
        speed: 0.045 + Math.random() * 0.095,
        hue: index % 3 === 0 ? "255,112,74" : "234,217,189",
        reverse: Math.random() > 0.5,
      };
    });
  }

  function draw(time = 0) {
    ctx.clearRect(0, 0, width, height);

    lines.forEach((line) => {
      const progressBase = (time * line.speed * 0.00022 + line.phase) % 1;
      const progress = line.reverse ? 1 - progressBase : progressBase;
      const headX = line.startX + (line.endX - line.startX) * progress;
      const headY = line.startY + (line.endY - line.startY) * progress;
      const tailProgress = Math.max(0, progress - 0.16);
      const tailX = line.startX + (line.endX - line.startX) * tailProgress;
      const tailY = line.startY + (line.endY - line.startY) * tailProgress;
      const glow = 0.35 + Math.sin(time * 0.0011 + line.phase * 8) * 0.18;

      ctx.lineWidth = 1;
      ctx.strokeStyle = `rgba(${line.hue},0.075)`;
      ctx.beginPath();
      ctx.moveTo(line.startX, line.startY);
      ctx.lineTo(line.endX, line.endY);
      ctx.stroke();

      const gradient = ctx.createLinearGradient(tailX, tailY, headX, headY);
      gradient.addColorStop(0, `rgba(${line.hue},0)`);
      gradient.addColorStop(0.65, `rgba(${line.hue},${0.18 + glow * 0.34})`);
      gradient.addColorStop(1, "rgba(255,255,255,0.82)");

      ctx.lineWidth = 1.35;
      ctx.strokeStyle = gradient;
      ctx.shadowColor = `rgba(${line.hue},0.54)`;
      ctx.shadowBlur = 12;
      ctx.beginPath();
      ctx.moveTo(tailX, tailY);
      ctx.lineTo(headX, headY);
      ctx.stroke();

      ctx.shadowBlur = 0;
      ctx.fillStyle = `rgba(255,246,229,${0.42 + glow * 0.24})`;
      ctx.beginPath();
      ctx.arc(headX, headY, 1.5, 0, Math.PI * 2);
      ctx.fill();
    });

    if (!prefersReducedMotion.matches) {
      requestAnimationFrame(draw);
    }
  }

  window.addEventListener("resize", resize);
  resize();
  requestAnimationFrame(draw);
}
