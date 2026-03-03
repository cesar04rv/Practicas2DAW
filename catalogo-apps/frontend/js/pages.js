// ============================================================
// frontend/js/pages.js
// ============================================================

const PageHandlers = {

  login() {
    const form    = document.getElementById('login-form');
    const errEl   = document.getElementById('login-error');
    const btnText = document.getElementById('login-btn-text');

    form.onsubmit = async (e) => {
      e.preventDefault();
      errEl.textContent = '';
      const email    = form.querySelector('[name=email]').value.trim();
      const password = form.querySelector('[name=password]').value;
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
    };
  },

  async projects() {
    if (!App.state.technologies.length || !App.state.users.length) {
      const [techRes, userRes] = await Promise.all([Technologies.list(), Users.list()]);
      App.state.technologies = techRes.data ?? [];
      App.state.users        = userRes.data ?? [];
    }

    const f = App.state.filters;
    populateFilterSelects();
    restoreFilters();
    attachFilterListeners();
    await loadProjects();

    async function loadProjects() {
      const container = document.getElementById('projects-container');
      container.innerHTML = '<div class="loading">Cargando proyectos</div>';
      try {
        const res = await Projects.list({
          search: f.search, status: f.status,
          user_id: f.user_id, technology_id: f.technology_id, page: f.page,
        });
        renderProjects(res.data ?? []);
        renderPagination(res.meta);
      } catch (err) {
        container.innerHTML = `<div class="empty-state"><p>${escHtml(err.message)}</p></div>`;
      }
    }

    function renderProjects(projects) {
      const container = document.getElementById('projects-container');
      if (!projects.length) {
        container.innerHTML = `
          <div class="empty-state">
            <svg width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7h18M3 12h18M3 17h10"/></svg>
            <p>No se encontraron proyectos con estos filtros.</p>
          </div>`;
        return;
      }
      container.innerHTML = `<div class="projects-grid">${
        projects.map(p => {
          const owners = (p.users ?? []).filter(u => u.role === 'owner').map(u => escHtml(u.name)).join(', ');
          return `
            <div class="project-card" data-id="${p.id}">
              <div class="project-card-main">
                <h3>${escHtml(p.name)}</h3>
                ${p.subtitle ? `<p style="font-size:11px;color:var(--text-muted);margin-bottom:4px">${escHtml(p.subtitle)}</p>` : ''}
                <p>${escHtml(p.description ?? '')}</p>
                <div class="project-card-meta">
                  ${statusBadge(p.status)}
                  ${techBadges(p.technologies)}
                  ${owners ? `<span style="font-size:11px;color:var(--text-muted);margin-left:4px">${owners}</span>` : ''}
                </div>
              </div>
              <div class="project-card-actions">
                <button class="btn btn-ghost btn-sm" onclick="event.stopPropagation();App.navigate('project-detail',{id:${p.id}})">Ver</button>
                <button class="btn btn-ghost btn-sm admin-only" onclick="event.stopPropagation();openProjectModal(${p.id})">Editar</button>
                <button class="btn btn-danger btn-sm admin-only" onclick="event.stopPropagation();deleteProject(${p.id},'${escHtml(p.name)}')">Eliminar</button>
              </div>
            </div>
          `;
        }).join('')
      }</div>`;
      container.querySelectorAll('.project-card').forEach(card => {
        card.addEventListener('click', () => App.navigate('project-detail', { id: +card.dataset.id }));
      });
    }

    function renderPagination(meta) {
      const el = document.getElementById('pagination');
      if (!meta || meta.total_pages <= 1) { el.innerHTML = ''; return; }
      const { page, total_pages, total } = meta;
      let html = `<button ${page <= 1 ? 'disabled' : ''} onclick="changePage(${page-1})">‹</button>`;
      for (let i = Math.max(1, page-2); i <= Math.min(total_pages, page+2); i++) {
        html += `<button class="${i === page ? 'active' : ''}" onclick="changePage(${i})">${i}</button>`;
      }
      html += `<button ${page >= total_pages ? 'disabled' : ''} onclick="changePage(${page+1})">›</button>`;
      html += `<span class="page-info">${total} proyectos</span>`;
      el.innerHTML = html;
    }

    function populateFilterSelects() {
      const ownerSel = document.getElementById('filter-owner');
      ownerSel.innerHTML = '<option value="">Todos los responsables</option>' +
        App.state.users.map(u => `<option value="${u.id}">${escHtml(u.name)}</option>`).join('');
      const techSel = document.getElementById('filter-tech');
      techSel.innerHTML = '<option value="">Todas las tecnologías</option>' +
        App.state.technologies.map(t => `<option value="${t.id}">${escHtml(t.name)}</option>`).join('');
    }

    function restoreFilters() {
      document.getElementById('filter-search').value = f.search;
      document.getElementById('filter-status').value = f.status;
      document.getElementById('filter-owner').value  = f.user_id ?? '';
      document.getElementById('filter-tech').value   = f.technology_id;
    }

    function attachFilterListeners() {
      let searchTimer;
      document.getElementById('filter-search').oninput = (e) => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => { f.search = e.target.value.trim(); f.page = 1; loadProjects(); }, 350);
      };
      document.getElementById('filter-status').onchange = (e) => { f.status = e.target.value; f.page = 1; loadProjects(); };
      document.getElementById('filter-owner').onchange  = (e) => { f.user_id = e.target.value; f.page = 1; loadProjects(); };
      document.getElementById('filter-tech').onchange   = (e) => { f.technology_id = e.target.value; f.page = 1; loadProjects(); };
    }
  },

  async 'project-detail'({ id }) {
    const container = document.getElementById('project-detail-content');
    container.innerHTML = '<div class="loading">Cargando</div>';
    try {
      const res = await Projects.get(id);
      const p   = res.data;

      const owners       = (p.users ?? []).filter(u => u.role === 'owner');
      const collaborators = (p.users ?? []).filter(u => u.role === 'collaborator');

      const userListHtml = (users) => users.length
        ? users.map(u => `<span style="display:inline-flex;align-items:center;gap:6px;margin-bottom:4px">
            <span style="font-family:var(--font-sans)">${escHtml(u.name)}</span>
            <span style="font-size:11px;color:var(--text-muted)">${escHtml(u.email)}</span>
          </span>`).join('<br>')
        : '—';

      container.innerHTML = `
        <div class="detail-header">
          <div>
            <h2>${escHtml(p.name)}</h2>
            ${p.subtitle ? `<p style="color:var(--text-secondary);margin-top:4px;font-size:15px">${escHtml(p.subtitle)}</p>` : ''}
            <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:10px">
              ${statusBadge(p.status)}
              ${techBadges(p.technologies)}
            </div>
          </div>
          <div class="detail-actions">
            <button class="btn btn-ghost btn-sm admin-only" onclick="openProjectModal(${p.id})">Editar</button>
            <button class="btn btn-danger btn-sm admin-only" onclick="deleteProject(${p.id},'${escHtml(p.name)}',true)">Eliminar</button>
          </div>
        </div>

        <div class="detail-grid">
          <div>
            <div class="detail-section" style="margin-bottom:16px">
              <h4>Descripción</h4>
              <p style="font-size:13px;line-height:1.7;color:var(--text-secondary)">${escHtml(p.description ?? '—')}</p>
            </div>
            <div class="detail-section">
              <h4>Responsables</h4>
              <dl>
                <div class="detail-row">
                  <dt>Propietarios</dt>
                  <dd class="plain">${userListHtml(owners)}</dd>
                </div>
                <div class="detail-row">
                  <dt>Colaboradores</dt>
                  <dd class="plain">${userListHtml(collaborators)}</dd>
                </div>
              </dl>
            </div>
          </div>
          <div>
            <div class="detail-section">
              <h4>Información técnica</h4>
              <dl>
                <div class="detail-row">
                  <dt>Ubicación</dt>
                  <dd>${escHtml(p.location ?? '—')}</dd>
                </div>
                <div class="detail-row">
                  <dt>Entornos de desarrollo</dt>
                  <dd>${escHtml(p.dev_environment ?? '—')}</dd>
                </div>
                <div class="detail-row">
                  <dt>URL</dt>
                  <dd>${p.url ? `<a href="${escHtml(p.url)}" target="_blank">${escHtml(p.url)}</a>` : '—'}</dd>
                </div>
                <div class="detail-row">
                  <dt>Credenciales</dt>
                  <dd>${escHtml(p.credentials_location ?? '—')}</dd>
                </div>
              </dl>
            </div>
          </div>
        </div>
      `;
    } catch (err) {
      container.innerHTML = `<div class="empty-state"><p>${escHtml(err.message)}</p></div>`;
    }
  },

  async technologies() {
    requireAdmin();
    loadTechs();

    async function loadTechs() {
      const tbody = document.getElementById('tech-tbody');
      tbody.innerHTML = '<tr><td colspan="3" class="loading">Cargando</td></tr>';
      try {
        const res = await Technologies.list();
        App.state.technologies = res.data ?? [];
        tbody.innerHTML = res.data.map(t => `
          <tr>
            <td><span class="badge badge-tech" style="background:${t.color}22;color:${t.color};border:1px solid ${t.color}44">${escHtml(t.name)}</span></td>
            <td><span style="font-family:var(--font-mono);font-size:12px">${escHtml(t.color)}</span></td>
            <td style="text-align:right;display:flex;gap:6px;justify-content:flex-end">
              <button class="btn btn-ghost btn-sm" onclick="openTechModal(${t.id},'${escHtml(t.name)}','${escHtml(t.color)}')">Editar</button>
              <button class="btn btn-danger btn-sm" onclick="deleteTech(${t.id},'${escHtml(t.name)}')">Eliminar</button>
            </td>
          </tr>
        `).join('');
      } catch (err) {
        tbody.innerHTML = `<tr><td colspan="3" style="color:var(--red)">${escHtml(err.message)}</td></tr>`;
      }
    }

    window.openTechModal = function(id = null, name = '', color = '#6366f1') {
      document.getElementById('tech-modal-title').textContent = id ? 'Editar Tecnología' : 'Nueva Tecnología';
      document.getElementById('tech-form-id').value    = id ?? '';
      document.getElementById('tech-form-name').value  = name;
      document.getElementById('tech-form-color').value = color;
      document.getElementById('tech-modal').classList.add('open');
    };
    document.getElementById('tech-modal-close').onclick = () =>
      document.getElementById('tech-modal').classList.remove('open');

    document.getElementById('tech-form').onsubmit = async (e) => {
      e.preventDefault();
      const id   = document.getElementById('tech-form-id').value;
      const data = { name: document.getElementById('tech-form-name').value.trim(), color: document.getElementById('tech-form-color').value };
      try {
        if (id) await Technologies.update(+id, data);
        else    await Technologies.create(data);
        document.getElementById('tech-modal').classList.remove('open');
        toast(id ? 'Tecnología actualizada' : 'Tecnología creada', 'success');
        loadTechs();
      } catch (err) { toast(err.message, 'error'); }
    };

    window.deleteTech = (id, name) => confirm(`¿Eliminar tecnología "${name}"?`, async () => {
      try { await Technologies.remove(id); toast('Tecnología eliminada', 'success'); loadTechs(); }
      catch (err) { toast(err.message, 'error'); }
    });
  },

  async users() {
    requireAdmin();
    loadUsers();

    async function loadUsers() {
      const tbody = document.getElementById('users-tbody');
      tbody.innerHTML = '<tr><td colspan="4" class="loading">Cargando</td></tr>';
      try {
        const res = await Users.list();
        tbody.innerHTML = res.data.map(u => `
          <tr>
            <td>${escHtml(u.name)}</td>
            <td style="font-family:var(--font-mono);font-size:12px">${escHtml(u.email)}</td>
            <td><span class="badge ${u.role === 'admin' ? 'badge-status-production' : 'badge-status-dev'}">${u.role}</span></td>
            <td style="text-align:right;display:flex;gap:6px;justify-content:flex-end">
              <button class="btn btn-ghost btn-sm" onclick="openUserModal(${u.id})">Editar</button>
              <button class="btn btn-danger btn-sm" onclick="deleteUser(${u.id},'${escHtml(u.name)}')">Eliminar</button>
            </td>
          </tr>
        `).join('');
      } catch (err) {
        tbody.innerHTML = `<tr><td colspan="4" style="color:var(--red)">${escHtml(err.message)}</td></tr>`;
      }
    }

    window.openUserModal = function(id = null) {
      document.getElementById('user-modal-title').textContent = id ? 'Editar Usuario' : 'Nuevo Usuario';
      document.getElementById('user-form-id').value = id ?? '';
      document.getElementById('user-form').reset();
      if (id) document.getElementById('user-form-id').value = id;
      document.getElementById('user-pass-note').style.display = id ? 'block' : 'none';
      document.getElementById('user-modal').classList.add('open');
    };
    document.getElementById('user-modal-close').onclick = () =>
      document.getElementById('user-modal').classList.remove('open');

    document.getElementById('user-form').onsubmit = async (e) => {
      e.preventDefault();
      const id   = document.getElementById('user-form-id').value;
      const data = {
        name:     document.getElementById('user-form-name').value.trim(),
        email:    document.getElementById('user-form-email').value.trim(),
        password: document.getElementById('user-form-pass').value,
        role:     document.getElementById('user-form-role').value,
      };
      if (!data.password) delete data.password;
      try {
        if (id) await Users.update(+id, data);
        else    await Users.create(data);
        document.getElementById('user-modal').classList.remove('open');
        toast(id ? 'Usuario actualizado' : 'Usuario creado', 'success');
        loadUsers();
      } catch (err) { toast(err.message, 'error'); }
    };

    window.deleteUser = (id, name) => confirm(`¿Eliminar usuario "${name}"?`, async () => {
      try { await Users.remove(id); toast('Usuario eliminado', 'success'); loadUsers(); }
      catch (err) { toast(err.message, 'error'); }
    });
  },
};

// ---- Helpers ----
function requireAdmin() {
  if (App.state.user?.role !== 'admin') {
    App.navigate('projects');
    toast('Acceso solo para administradores', 'error');
  }
}

function changePage(page) {
  App.state.filters.page = page;
  App.navigate('projects');
}

// ---- Project Modal ----
window.openProjectModal = async function(id = null) {
  const modal   = document.getElementById('project-modal');
  const titleEl = document.getElementById('project-modal-title');
  const form    = document.getElementById('project-form');

  titleEl.textContent = id ? 'Editar Proyecto' : 'Nuevo Proyecto';
  form.reset();
  document.getElementById('project-form-id').value = id ?? '';

  // Picker de tecnologías
  const picker = document.getElementById('pf-tech-picker');
  picker.innerHTML = App.state.technologies.map(t => `
    <span class="tech-tag" data-id="${t.id}" style="background:${t.color}22;color:${t.color};border:1px solid ${t.color}44">${escHtml(t.name)}</span>
  `).join('');
  picker.querySelectorAll('.tech-tag').forEach(tag => {
    tag.onclick = () => tag.classList.toggle('selected');
  });

  // Picker de usuarios — se renderiza en #pf-user-picker
  renderUserPicker([]);

  if (id) {
    try {
      const res = await Projects.get(id);
      const p   = res.data;
      document.getElementById('pf-name').value        = p.name ?? '';
      document.getElementById('pf-subtitle').value    = p.subtitle ?? '';
      document.getElementById('pf-description').value = p.description ?? '';
      document.getElementById('pf-status').value      = p.status ?? 'dev';
      document.getElementById('pf-location').value    = p.location ?? '';
      document.getElementById('pf-devenv').value      = p.dev_environment ?? '';
      document.getElementById('pf-url').value         = p.url ?? '';
      document.getElementById('pf-creds').value       = p.credentials_location ?? '';

      // Seleccionar tecnologías
      const selectedTechIds = (p.technologies ?? []).map(t => t.id);
      picker.querySelectorAll('.tech-tag').forEach(tag => {
        if (selectedTechIds.includes(+tag.dataset.id)) tag.classList.add('selected');
      });

      // Cargar usuarios asignados
      renderUserPicker(p.users ?? []);
    } catch (err) { toast(err.message, 'error'); return; }
  }

  modal.classList.add('open');
};

function renderUserPicker(assignedUsers) {
  const container = document.getElementById('pf-user-picker');
  // assignedUsers: [{id, name, email, role}]
  // App.state.users: todos los usuarios disponibles

  const assigned = assignedUsers.map(u => ({ ...u })); // copia mutable

  function render() {
    const assignedIds = assigned.map(u => u.id);
    const available   = App.state.users.filter(u => !assignedIds.includes(u.id));

    container.innerHTML = `
      <div id="pf-user-list" style="display:flex;flex-direction:column;gap:6px;margin-bottom:8px">
        ${assigned.map(u => `
          <div style="display:flex;align-items:center;gap:8px;background:var(--bg-raised);padding:6px 10px;border-radius:var(--radius-sm);border:1px solid var(--border)">
            <span style="flex:1;font-size:13px">${escHtml(u.name)}</span>
            <select data-uid="${u.id}" class="form-control" style="width:140px;padding:4px 8px;font-size:12px">
              <option value="owner"        ${u.role === 'owner'        ? 'selected' : ''}>Propietario</option>
              <option value="collaborator" ${u.role === 'collaborator' ? 'selected' : ''}>Colaborador</option>
            </select>
            <button type="button" onclick="removeProjectUser(${u.id})" class="btn btn-ghost btn-icon" style="padding:4px 6px;font-size:12px">✕</button>
          </div>
        `).join('')}
      </div>
      ${available.length ? `
        <div style="display:flex;gap:8px;align-items:center">
          <select id="pf-user-add-select" class="form-control" style="flex:1">
            <option value="">— Añadir usuario —</option>
            ${available.map(u => `<option value="${u.id}">${escHtml(u.name)}</option>`).join('')}
          </select>
          <button type="button" onclick="addProjectUser()" class="btn btn-ghost btn-sm">+ Añadir</button>
        </div>
      ` : '<p style="font-size:12px;color:var(--text-muted)">Todos los usuarios están asignados.</p>'}
    `;

    // Listeners de cambio de rol
    container.querySelectorAll('select[data-uid]').forEach(sel => {
      sel.onchange = () => {
        const u = assigned.find(x => x.id === +sel.dataset.uid);
        if (u) u.role = sel.value;
      };
    });
  }

  window.addProjectUser = function() {
    const sel = document.getElementById('pf-user-add-select');
    const uid = +sel.value;
    if (!uid) return;
    const user = App.state.users.find(u => u.id === uid);
    if (user && !assigned.find(u => u.id === uid)) {
      assigned.push({ id: user.id, name: user.name, email: user.email, role: 'collaborator' });
      render();
    }
  };

  window.removeProjectUser = function(uid) {
    const idx = assigned.findIndex(u => u.id === uid);
    if (idx !== -1) { assigned.splice(idx, 1); render(); }
  };

  window.getProjectUsers = function() {
    // Leer roles actuales de los selects antes de devolver
    container.querySelectorAll('select[data-uid]').forEach(sel => {
      const u = assigned.find(x => x.id === +sel.dataset.uid);
      if (u) u.role = sel.value;
    });
    return assigned.map(u => ({ user_id: u.id, role: u.role }));
  };

  render();
}

document.addEventListener('DOMContentLoaded', () => {
  document.getElementById('project-modal-close').onclick = () =>
    document.getElementById('project-modal').classList.remove('open');

  document.getElementById('project-form').onsubmit = async (e) => {
    e.preventDefault();
    const id = document.getElementById('project-form-id').value;
    const selectedTechs = [...document.querySelectorAll('#pf-tech-picker .tech-tag.selected')].map(t => +t.dataset.id);
    const data = {
      name:                 document.getElementById('pf-name').value.trim(),
      subtitle:             document.getElementById('pf-subtitle').value.trim(),
      description:          document.getElementById('pf-description').value.trim(),
      status:               document.getElementById('pf-status').value,
      location:             document.getElementById('pf-location').value.trim(),
      dev_environment:      document.getElementById('pf-devenv').value.trim(),
      url:                  document.getElementById('pf-url').value.trim(),
      credentials_location: document.getElementById('pf-creds').value.trim(),
      technology_ids:       selectedTechs,
      project_users:        window.getProjectUsers ? window.getProjectUsers() : [],
    };
    try {
      if (id) await Projects.update(+id, data);
      else    await Projects.create(data);
      document.getElementById('project-modal').classList.remove('open');
      toast(id ? 'Proyecto actualizado' : 'Proyecto creado', 'success');
      App.navigate('projects');
    } catch (err) { toast(err.message, 'error'); }
  };
});

window.deleteProject = function(id, name, goBack = false) {
  confirm(`¿Eliminar el proyecto "${name}"? Esta acción no se puede deshacer.`, async () => {
    try {
      await Projects.remove(id);
      toast('Proyecto eliminado', 'success');
      App.navigate('projects');
    } catch (err) { toast(err.message, 'error'); }
  });
};