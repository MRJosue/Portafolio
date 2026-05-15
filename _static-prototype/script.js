const canvas = document.querySelector("#signal-canvas");
const ctx = canvas.getContext("2d");
const form = document.querySelector("#newsletter-form");
const statusText = document.querySelector("#form-status");

let width = 0;
let height = 0;
let dpr = 1;
let nodes = [];

function resizeCanvas() {
  dpr = Math.min(window.devicePixelRatio || 1, 2);
  width = window.innerWidth;
  height = window.innerHeight;
  canvas.width = Math.floor(width * dpr);
  canvas.height = Math.floor(height * dpr);
  canvas.style.width = `${width}px`;
  canvas.style.height = `${height}px`;
  ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

  const count = Math.max(18, Math.floor(width / 70));
  nodes = Array.from({ length: count }, (_, index) => ({
    x: (index / Math.max(count - 1, 1)) * width,
    y: Math.random() * height,
    speed: 0.18 + Math.random() * 0.42,
    phase: Math.random() * Math.PI * 2,
    length: 90 + Math.random() * 180,
  }));
}

function drawSignal(time = 0) {
  ctx.clearRect(0, 0, width, height);
  ctx.lineWidth = 1;

  nodes.forEach((node, index) => {
    const breath = (Math.sin(time * 0.0012 + node.phase) + 1) / 2;
    const x = node.x + Math.sin(time * 0.0005 + index) * 24;
    const y = (node.y + time * node.speed * 0.04) % (height + node.length);
    const alpha = 0.08 + breath * 0.34;

    const gradient = ctx.createLinearGradient(x, y - node.length, x, y);
    gradient.addColorStop(0, "rgba(201,255,74,0)");
    gradient.addColorStop(0.5, `rgba(201,255,74,${alpha})`);
    gradient.addColorStop(1, "rgba(69,231,212,0)");

    ctx.strokeStyle = gradient;
    ctx.beginPath();
    ctx.moveTo(x, y - node.length);
    ctx.lineTo(x, y);
    ctx.stroke();

    if (index % 3 === 0) {
      ctx.strokeStyle = `rgba(229,231,217,${0.03 + breath * 0.05})`;
      ctx.beginPath();
      ctx.moveTo(Math.max(0, x - 140), y - node.length * 0.38);
      ctx.lineTo(Math.min(width, x + 180), y - node.length * 0.38);
      ctx.stroke();
    }
  });

  requestAnimationFrame(drawSignal);
}

form.addEventListener("submit", (event) => {
  event.preventDefault();
  const email = new FormData(form).get("email");
  const subscribers = JSON.parse(localStorage.getItem("portfolio-signal-subs") || "[]");

  if (!subscribers.includes(email)) {
    subscribers.push(email);
    localStorage.setItem("portfolio-signal-subs", JSON.stringify(subscribers));
  }

  statusText.textContent = "SIGNAL ACCEPTED / contacto guardado en este navegador";
  form.reset();
});

window.addEventListener("resize", resizeCanvas);
resizeCanvas();
requestAnimationFrame(drawSignal);
