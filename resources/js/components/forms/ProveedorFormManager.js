// ProveedorFormManager.js
// Componente JS para gestionar el formulario de proveedores

export const ProveedorFormManager = {
    init({ el, proveedor = {}, old = {} }) {
        const container = document.querySelector(el);
        if (!container) return;
        container.innerHTML = '';
        // Renderizar campos básicos
        container.innerHTML += `
            <div class="mb-3">
                <label for="proveedor-nombre" class="form-label">Nombre</label>
                <input type="text" name="nombre" id="proveedor-nombre" class="form-control" value="${old.nombre || proveedor.nombre || ''}" required>
            </div>
            <div class="mb-3">
                <label for="proveedor-ruc" class="form-label">RUC</label>
                <input type="text" name="ruc" id="proveedor-ruc" class="form-control" value="${old.ruc || proveedor.ruc || ''}">
            </div>
            <div class="mb-3">
                <label for="proveedor-telefono" class="form-label">Teléfono</label>
                <input type="text" name="telefono" id="proveedor-telefono" class="form-control" value="${old.telefono || proveedor.telefono || ''}">
            </div>
        `;
    }
};

window.ProveedorFormManager = ProveedorFormManager;
