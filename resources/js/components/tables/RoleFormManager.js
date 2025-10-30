// RoleFormManager.js
// Componente JS para gestionar el formulario de roles

export const RoleFormManager = {
    init({ el, permisos, role = {}, old = {} }) {
        const container = document.querySelector(el);
        if (!container) return;
        container.innerHTML = '';
        // Renderizar campo nombre
        container.innerHTML += `
            <div class="mb-3">
                <label for="role-name" class="form-label">Nombre del Rol</label>
                <input type="text" name="name" id="role-name" class="form-control" value="${old.name || role.name || ''}" required>
            </div>
        `;
        // Renderizar permisos
        if (permisos && Array.isArray(permisos)) {
            container.innerHTML += `<div class="mb-3"><label class="form-label">Permisos</label><div id="permisos-list" class="row"></div></div>`;
            const permisosList = container.querySelector('#permisos-list');
            // Mostrar los permisos en 3 columnas
            const cols = 3;
            const perCol = Math.ceil(permisos.length / cols);
            for (let c = 0; c < cols; c++) {
                const colPerms = permisos.slice(c * perCol, (c + 1) * perCol);
                const colDiv = document.createElement('div');
                colDiv.className = 'col-md-4';
                colPerms.forEach(permiso => {
                    const checked = (old.permission && old.permission.includes(permiso)) || (role.permission && role.permission.includes(permiso)) ? 'checked' : '';
                    colDiv.innerHTML += `
                        <div class="form-check mb-1">
                            <input class="form-check-input" type="checkbox" name="permission[]" value="${permiso}" id="permiso-${permiso}" ${checked}>
                            <label class="form-check-label badge bg-info text-dark" for="permiso-${permiso}">${permiso}</label>
                        </div>
                    `;
                });
                permisosList.appendChild(colDiv);
            }
        }
    }
};

window.RoleFormManager = RoleFormManager;
