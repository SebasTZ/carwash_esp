// UserFormManager.js
// Componente JS para gestionar el formulario de usuarios

export const UserFormManager = {
    init({ el, user = {}, old = {}, roles = [] }) {
        const container = document.querySelector(el);
        if (!container) return;
        container.innerHTML = '';
        // Campo nombre
        container.innerHTML += `
            <div class="mb-3">
                <label for="user-name" class="form-label">Nombre(s)</label>
                <input type="text" name="name" id="user-name" class="form-control" value="${old.name || user.name || ''}" required>
            </div>
        `;
        // Campo email
        container.innerHTML += `
            <div class="mb-3">
                <label for="user-email" class="form-label">Correo electrónico</label>
                <input type="email" name="email" id="user-email" class="form-control" value="${old.email || user.email || ''}" required>
            </div>
        `;
        // Campo rol
        if (roles.length) {
            container.innerHTML += `<div class="mb-3"><label for="user-role" class="form-label">Rol</label><select name="role" id="user-role" class="form-control">${roles.map(r => `<option value='${r}' ${(user.role === r || old.role === r) ? 'selected' : ''}>${r}</option>`).join('')}</select></div>`;
        }
        // Campo estado
        container.innerHTML += `
            <div class="mb-3">
                <label for="user-status" class="form-label">Estado</label>
                <select name="status" id="user-status" class="form-control">
                    <option value="Activo" ${(old.status === 'Activo' || user.status === 'Activo') ? 'selected' : ''}>Activo</option>
                    <option value="Inactivo" ${(old.status === 'Inactivo' || user.status === 'Inactivo') ? 'selected' : ''}>Inactivo</option>
                </select>
            </div>
        `;
    }
};

window.UserFormManager = UserFormManager;
