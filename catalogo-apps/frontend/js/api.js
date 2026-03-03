// ============================================================
// frontend/js/api.js
// ============================================================

const API_BASE = '/catalogo-apps/backend';

async function apiFetch(endpoint, { method = 'GET', body = null, params = {} } = {}) {
  let url = API_BASE + endpoint;

  const qs = new URLSearchParams(
    Object.fromEntries(Object.entries(params).filter(([, v]) => v !== '' && v !== null && v !== undefined))
  ).toString();
  if (qs) url += '?' + qs;

  const opts = {
    method,
    credentials: 'same-origin',
    headers: { 'Content-Type': 'application/json' },
  };
  if (body) opts.body = JSON.stringify(body);

  const res  = await fetch(url, opts);
  const data = await res.json().catch(() => ({ success: false, message: 'Respuesta no válida del servidor' }));

  // Login: siempre devolver la respuesta, sea éxito o error
  if (endpoint.includes('/auth/login')) {
    return data;
  }

  // Otras rutas: redirigir al login si sesión expirada
  if (res.status === 401) {
    App.navigate('login');
    throw new Error('Sesión expirada');
  }

  if (!res.ok && !data.success) {
    throw new Error(data.message || `Error ${res.status}`);
  }

  return data;
}

const Auth = {
  login:  (email, password) => apiFetch('/auth/login',  { method: 'POST', body: { email, password } }),
  logout: ()               => apiFetch('/auth/logout', { method: 'POST' }),
  me:     ()               => apiFetch('/auth/me'),
};

const Projects = {
  list:   (params = {}) => apiFetch('/projects',        { params }),
  get:    (id)          => apiFetch(`/projects/${id}`),
  create: (data)        => apiFetch('/projects',        { method: 'POST',   body: data }),
  update: (id, data)    => apiFetch(`/projects/${id}`,  { method: 'PUT',    body: data }),
  remove: (id)          => apiFetch(`/projects/${id}`,  { method: 'DELETE' }),
};

const Technologies = {
  list:   ()         => apiFetch('/technologies'),
  create: (data)     => apiFetch('/technologies',        { method: 'POST',   body: data }),
  update: (id, data) => apiFetch(`/technologies/${id}`,  { method: 'PUT',    body: data }),
  remove: (id)       => apiFetch(`/technologies/${id}`,  { method: 'DELETE' }),
};

const Users = {
  list:   ()         => apiFetch('/users'),
  create: (data)     => apiFetch('/users',        { method: 'POST',   body: data }),
  update: (id, data) => apiFetch(`/users/${id}`,  { method: 'PUT',    body: data }),
  remove: (id)       => apiFetch(`/users/${id}`,  { method: 'DELETE' }),
};