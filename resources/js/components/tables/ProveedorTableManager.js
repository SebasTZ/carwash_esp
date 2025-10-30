// ProveedorTableManager.js
// Componente JS para gestionar la tabla dinámica de proveedores

export const ProveedorTableManager = {
    init({ el, proveedores, canEdit, canDelete }) {
        const container = document.querySelector(el);
        if (!container) {
            return;
        }
        container.innerHTML = '';
        let tableHtml = `<table class="table table-bordered"><thead><tr><th>Razón Social</th><th>Documento</th><th>Teléfono</th><th>Acciones</th></tr></thead><tbody>`;
        if (!proveedores || proveedores.length === 0) {
            tableHtml += `<tr><td colspan="4" class="text-center">No hay proveedores registrados.</td></tr>`;
        } else {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            proveedores.forEach(proveedor => {
                const persona = proveedor.persona || {};
                tableHtml += `<tr>
                    <td>${persona.razon_social || ''}</td>
                    <td>${persona.numero_documento || ''}</td>
                    <td>${persona.telefono || ''}</td>
                    <td>`;
                if (canEdit) {
                    tableHtml += `<a href="/proveedores/${proveedor.id}/edit" class="btn btn-sm btn-warning me-1">Editar</a>`;
                }
                if (canDelete) {
                    tableHtml += `<form method="POST" action="/proveedores/${proveedor.id}" style="display:inline-block;" onsubmit="return confirm('¿Está seguro de eliminar este proveedor?');">
                        <input type="hidden" name="_token" value="${csrfToken}">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                    </form>`;
                }
                tableHtml += `</td></tr>`;
            });
        }
        tableHtml += `</tbody></table>`;
        container.innerHTML = tableHtml;
    }
};

window.ProveedorTableManager = ProveedorTableManager;
