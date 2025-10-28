// UserTableManager.js
// Componente JS para gestionar la tabla dinámica de usuarios

export const UserTableManager = {
    init({ el, users, canEdit, canDelete }) {
        const container = document.querySelector(el);
        if (!container) return;
        container.innerHTML = '';
        let tableHtml = `<table class="table table-bordered"><thead><tr><th>Usuario</th><th>Email</th><th>Rol</th><th>Estado</th><th>Acciones</th></tr></thead><tbody>`;
        users.forEach(user => {
            tableHtml += `<tr>
                <td>${user.name}</td>
                <td>${user.email}</td>
                <td>${user.role || ''}</td>
                <td>${user.status || ''}</td>
                <td>`;
            if (canEdit) {
                tableHtml += `<a href="/users/${user.id}/edit" class="btn btn-sm btn-warning me-1">Editar</a>`;
            }
            if (canDelete) {
                tableHtml += `<form method="POST" action="/users/${user.id}" style="display:inline-block;">
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

window.UserTableManager = UserTableManager;
