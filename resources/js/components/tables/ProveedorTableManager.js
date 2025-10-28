// ProveedorTableManager.js
// Componente JS para gestionar la tabla dinámica de proveedores

export const ProveedorTableManager = {
    init({ el, proveedores, canEdit, canDelete }) {
        const container = document.querySelector(el);
        if (!container) return;
        container.innerHTML = '';
        let tableHtml = `<table class="table table-bordered"><thead><tr><th>Nombre</th><th>RUC</th><th>Teléfono</th><th>Acciones</th></tr></thead><tbody>`;
        proveedores.forEach(proveedor => {
            tableHtml += `<tr>
                <td>${proveedor.nombre}</td>
                <td>${proveedor.ruc}</td>
                <td>${proveedor.telefono}</td>
                <td>`;
            if (canEdit) {
                tableHtml += `<a href="/proveedores/${proveedor.id}/edit" class="btn btn-sm btn-warning me-1">Editar</a>`;
            }
            if (canDelete) {
                tableHtml += `<form method="POST" action="/proveedores/${proveedor.id}" style="display:inline-block;">
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

window.ProveedorTableManager = ProveedorTableManager;
