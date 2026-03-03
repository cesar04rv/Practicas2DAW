// ============================================================
// frontend/js/app.js
// Estado global, enrutado SPA, utilidades UI
// ============================================================

const App = {
  state: {
    user:          null,
    technologies:  [],
    users:         [],
    // Filtros actuales (se recuerdan al volver atrás)
    filters: { search: '', status: '', owner_id: '', technology_id: '', page: 1 },
  },

  // ---- Navegación ----
  navigate(page, data = {}) {
    document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
    const target = document.getElementById('page-' + page);
    if (target) target.classList.add('active');
    window.scrollTo(0, 0);

    // Llamar al init de la página si existe
    const handler = PageHandlers[page];
    if (handler) handler(data);
  },

  // ---- Estado de autenticación ----
  setUser(user) {
    this.state.user = user;
    if (user?.role === 'admin') {
      document.body.classList.add('is-admin');
    } else {
      document.body.classList.remove('is-admin');
    }
    document.getElementById('nav-user-name').textContent = user?.name ?? '';
    document.getElementById('navbar').style.display = user ? 'flex' : 'none';
  },

  // ---- Inicialización ----
  async init() {
    initTheme();
    // Comprobar sesión al cargar
    try {
      const res = await Auth.me();
      this.setUser(res.data);
      this.navigate('projects');
    } catch {
      this.setUser(null);
      this.navigate('login');
    }
  },
};

// ---- Toast ---- 
function toast(message, type = 'info', duration = 3500) {
  const icons = { success: '✓', error: '✕', info: 'i' };
  const el = document.createElement('div');
  el.className = `toast ${type}`;
  el.innerHTML = `<span>${icons[type] ?? 'i'}</span><span>${message}</span>`;
  document.getElementById('toast-container').appendChild(el);
  setTimeout(() => el.remove(), duration);
}

// ---- Confirmación ----
function confirm(message, onConfirm) {
  const overlay = document.getElementById('confirm-overlay');
  const msg     = document.getElementById('confirm-message');
  const yes     = document.getElementById('confirm-yes');
  const no      = document.getElementById('confirm-no');

  msg.textContent = message;
  overlay.classList.add('open');

  const cleanup = () => overlay.classList.remove('open');
  yes.onclick = () => { cleanup(); onConfirm(); };
  no.onclick  = cleanup;
}

// ---- Helpers UI ----
function statusBadge(status) {
  const labels = { production: 'Producción', dev: 'Desarrollo', stopped: 'Parado' };
  return `<span class="badge badge-status-${status}">${labels[status] ?? status}</span>`;
}

function techBadges(technologies = []) {
  return technologies.map(t =>
    `<span class="badge badge-tech" style="background:${t.color}22;color:${t.color};border:1px solid ${t.color}44">${t.name}</span>`
  ).join('');
}

function escHtml(str) {
  return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function formatDate(dateStr) {
  if (!dateStr) return '—';
  return new Date(dateStr).toLocaleDateString('es-ES', { day:'2-digit', month:'short', year:'numeric' });
}
// ---- Tema claro/oscuro ----
function initTheme() {
  const saved = localStorage.getItem('theme') || 'dark';
  document.documentElement.setAttribute('data-theme', saved);
  updateThemeBtn(saved);
}

function toggleTheme() {
  const current = document.documentElement.getAttribute('data-theme');
  const next    = current === 'dark' ? 'light' : 'dark';
  document.documentElement.setAttribute('data-theme', next);
  localStorage.setItem('theme', next);
  updateThemeBtn(next);
}

function updateThemeBtn(theme) {
  const btn = document.getElementById('theme-toggle');
  if (btn) btn.textContent = theme === 'dark' ? '☀️' : '🌙';
}
// ---- Iniciar app al cargar ----
document.addEventListener('DOMContentLoaded', () => App.init());