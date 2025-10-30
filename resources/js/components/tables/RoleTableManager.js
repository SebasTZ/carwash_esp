// RoleTableManager.js
// Componente JS para gestionar la tabla dinámica de roles

export const RoleTableManager = {
    init({ el, roles, canEdit, canDelete }) {
        const container = document.querySelector(el);
        if (!container) return;
        container.innerHTML = '';
        // Renderizar tabla de roles
        let tableHtml = `<table class="table table-bordered"><thead><tr><th>Nombre</th><th>Permisos</th><th>Acciones</th></tr></thead><tbody>`;
        roles.forEach(role => {
            tableHtml += `<tr>
                <td>${role.name}</td>
                <td>`;
            if (role.permission && role.permission.length) {
                tableHtml += role.permission.map(p => `<span class="badge bg-info text-dark m-1">${p}</span>`).join('');
            }
            tableHtml += `</td><td>`;
            if (canEdit) {
                tableHtml += `<a href="/roles/${role.id}/edit" class="btn btn-sm btn-warning me-1">Editar</a>`;
            }
            if (canDelete) {
                tableHtml += `<form method="POST" action="/roles/${role.id}" style="display:inline-block;">
                    <input type="hidden" name="_method" value="DELETE">
                    <input type="hidden" name="_token" value="${window.Laravel.csrfToken}">
                    <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                </form>`;
            }
            tableHtml += `</td></tr>`;
        });
        tableHtml += `</tbody></table>`;
        container.innerHTML = tableHtml;
    }
};

window.RoleTableManager = RoleTableManager;
