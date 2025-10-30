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
                <input type="text" name="name" id="user-name" class="form-control" value="${old.name || user.name || ''}" required aria-labelledby="nameHelpBlock">
                <div class="form-text" id="nameHelpBlock">Ingresa un solo nombre</div>
            </div>
        `;
        // Campo email
        container.innerHTML += `
            <div class="mb-3">
                <label for="user-email" class="form-label">Correo electrónico</label>
                <input type="email" name="email" id="user-email" class="form-control" value="${old.email || user.email || ''}" required aria-labelledby="emailHelpBlock">
                <div class="form-text" id="emailHelpBlock">Dirección de correo</div>
            </div>
        `;
        // Campo contraseña
        container.innerHTML += `
            <div class="mb-3">
                <label for="user-password" class="form-label">Contraseña</label>
                <input type="password" name="password" id="user-password" class="form-control" aria-labelledby="passwordHelpBlock">
                <div class="form-text" id="passwordHelpBlock">Ingresa una contraseña segura. Debe incluir números.</div>
            </div>
        `;
        // Campo confirmar contraseña
        container.innerHTML += `
            <div class="mb-3">
                <label for="user-password-confirm" class="form-label">Confirmar</label>
                <input type="password" name="password_confirm" id="user-password-confirm" class="form-control" aria-labelledby="passwordConfirmHelpBlock">
                <div class="form-text" id="passwordConfirmHelpBlock">Vuelve a ingresar tu contraseña.</div>
            </div>
        `;
        // Campo rol
        if (roles.length) {
            container.innerHTML += `<div class="mb-3"><label for="user-role" class="form-label">Rol</label><select name="role" id="user-role" class="form-select" aria-labelledby="rolHelpBlock">
                <option value="" disabled ${(old.role || user.role) ? '' : 'selected'}>Selecciona:</option>
                ${roles.map(r => `<option value='${r}' ${(user.role === r || old.role === r) ? 'selected' : ''}>${r}</option>`).join('')}
            </select>
            <div class="form-text" id="rolHelpBlock">Elige un rol para el usuario.</div>
            </div>`;
        }
        // Campo estado
        container.innerHTML += `
            <div class="mb-3">
                <label for="user-status" class="form-label">Estado</label>
                <select name="status" id="user-status" class="form-select">
                    <option value="Activo" ${(old.status === 'Activo' || user.status === 'Activo') ? 'selected' : ''}>Activo</option>
                    <option value="Inactivo" ${(old.status === 'Inactivo' || user.status === 'Inactivo') ? 'selected' : ''}>Inactivo</option>
                </select>
            </div>
        `;
    }
};

window.UserFormManager = UserFormManager;
