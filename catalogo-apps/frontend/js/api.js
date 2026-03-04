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
  const data = await res.json().catch(() => ({ exito: false, mensaje: 'Respuesta no válida del servidor' }));

  if (endpoint.includes('/auth/login')) return data;

  if (res.status === 401) {
    App.navigate('login');
    throw new Error('Sesión expirada');
  }

  if (!res.ok && !data.exito) {
    throw new Error(data.mensaje || `Error ${res.status}`);
  }

  return data;
}

const Auth = {
  login:  (correo, contrasena) => apiFetch('/auth/login',  { method: 'POST', body: { correo, contrasena } }),
  logout: ()                   => apiFetch('/auth/logout', { method: 'POST' }),
  yo:     ()                   => apiFetch('/auth/me'),
};

const Proyectos = {
  listar:    (params = {}) => apiFetch('/proyectos',        { params }),
  obtener:   (id)          => apiFetch(`/proyectos/${id}`),
  crear:     (datos)       => apiFetch('/proyectos',        { method: 'POST',   body: datos }),
  actualizar:(id, datos)   => apiFetch(`/proyectos/${id}`,  { method: 'PUT',    body: datos }),
  eliminar:  (id)          => apiFetch(`/proyectos/${id}`,  { method: 'DELETE' }),
};

const Tecnologias = {
  listar:    ()            => apiFetch('/tecnologias'),
  crear:     (datos)       => apiFetch('/tecnologias',       { method: 'POST',   body: datos }),
  actualizar:(id, datos)   => apiFetch(`/tecnologias/${id}`, { method: 'PUT',    body: datos }),
  eliminar:  (id)          => apiFetch(`/tecnologias/${id}`, { method: 'DELETE' }),
};

const Usuarios = {
  listar:    ()            => apiFetch('/usuarios'),
  crear:     (datos)       => apiFetch('/usuarios',          { method: 'POST',   body: datos }),
  actualizar:(id, datos)   => apiFetch(`/usuarios/${id}`,    { method: 'PUT',    body: datos }),
  eliminar:  (id)          => apiFetch(`/usuarios/${id}`,    { method: 'DELETE' }),
};