// ============================================================
// frontend/js/app.js
// ============================================================

const App = {
  state: {
    user:         null,
    technologies: [],
    users:        [],
    filters: { search: '', status: '', owner_id: '', technology_id: '', page: 1 },
  },

  navigate(page, data = {}) {
    document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
    const target = document.getElementById('page-' + page);
    if (target) target.classList.add('active');
    window.scrollTo(0, 0);
    const handler = PageHandlers[page];
    if (handler) handler(data);
  },

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

  async init() {
    initTheme();
    this.navigate('login');
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

// ---- Login / Logout ----
async function doLogin() {
  const errEl   = document.getElementById('login-error');
  const btnText = document.getElementById('login-btn-text');
  errEl.textContent = '';
  const email    = document.getElementById('l-email').value.trim();
  const password = document.getElementById('l-pass').value;
  if (!email || !password) { errEl.textContent = 'Introduce email y contraseña'; return; }
  btnText.textContent = 'Entrando…';
  try {
    const res = await Auth.login(email, password);
    if (res.success) {
      App.setUser(res.data);
      App.navigate('projects');
    } else {
      errEl.textContent = res.message ?? 'Credenciales incorrectas';
      btnText.textContent = 'Entrar';
    }
  } catch (err) {
    errEl.textContent = err.message;
    btnText.textContent = 'Entrar';
  }
}

async function doLogout() {
  try { await Auth.logout(); } catch {}
  App.setUser(null);
  App.navigate('login');
}

// ---- Iniciar app ----
document.addEventListener('DOMContentLoaded', () => App.init());