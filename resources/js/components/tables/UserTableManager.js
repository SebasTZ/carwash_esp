// UserTableManager.js
// Componente JS para gestionar la tabla dinámica de usuarios

export const UserTableManager = {
    init({ el, users, canEdit, canDelete }) {
        const container = document.querySelector(el);
        if (!container) {
            return;
        }
        container.innerHTML = '';
        let tableHtml = `<table class="table table-bordered"><thead><tr><th>Usuario</th><th>Email</th><th>Rol</th><th>Estado</th><th>Acciones</th></tr></thead><tbody>`;
        users.forEach(user => {
            // Badge de estado
            let statusBadge = '<span class="badge bg-light text-dark">-</span>';
            if (user.status) {
                const status = String(user.status).toLowerCase();
                if (status === 'activo') {
                    statusBadge = '<span class="badge bg-success">Activo</span>';
                } else if (status === 'inactivo') {
                    statusBadge = '<span class="badge bg-secondary">Inactivo</span>';
                } else {
                    statusBadge = `<span class="badge bg-light text-dark">${this.escapeHtml(user.status)}</span>`;
                }
            }
            // Rol
            let roleName = user.role ? this.escapeHtml(user.role) : '-';
            tableHtml += `<tr>
                <td>${this.escapeHtml(user.name || '')}</td>
                <td>${this.escapeHtml(user.email || '')}</td>
                <td>${roleName}</td>
                <td>${statusBadge}</td>
                <td>`;
            if (canEdit) {
                tableHtml += `<a href="/users/${user.id}/edit" class="btn btn-sm btn-warning me-1">Editar</a>`;
            }
            if (canDelete) {
                // Obtener el token CSRF desde la meta tag
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                tableHtml += `<form method="POST" action="/users/${user.id}" style="display:inline-block;">
                    <input type="hidden" name="_method" value="DELETE">
                    <input type="hidden" name="_token" value="${csrfToken}">
                    <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                </form>`;
            }
            tableHtml += `</td></tr>`;
        });
        tableHtml += `</tbody></table>`;
    container.innerHTML = tableHtml;
    },
    escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = String(text);
        return div.innerHTML;
    }
};

window.UserTableManager = UserTableManager;
