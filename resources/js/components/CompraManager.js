// CompraManager.js
// Componente JS para gestionar el formulario de compras

export const CompraManager = {
    init({ el, compra = {}, productos = [], proveedores = [], old = {} }) {
        const container = document.querySelector(el);
        if (!container) return;
        container.innerHTML = '';
        // Renderizar campos básicos
        container.innerHTML += `
            <div class="mb-3">
                <label for="compra-proveedor" class="form-label">Proveedor</label>
                <select name="proveedor_id" id="compra-proveedor" class="form-control">
                    ${proveedores.map(p => `<option value="${p.id}" ${old.proveedor_id == p.id ? 'selected' : ''}>${p.nombre}</option>`).join('')}
                </select>
            </div>
            <div class="mb-3">
                <label for="compra-producto" class="form-label">Producto</label>
                <select name="producto_id" id="compra-producto" class="form-control">
                    ${productos.map(p => `<option value="${p.id}" ${old.producto_id == p.id ? 'selected' : ''}>${p.nombre}</option>`).join('')}
                </select>
            </div>
            <div class="mb-3">
                <label for="compra-cantidad" class="form-label">Cantidad</label>
                <input type="number" name="cantidad" id="compra-cantidad" class="form-control" value="${old.cantidad || compra.cantidad || 1}" min="1" required>
            </div>
        `;
    }
};

window.CompraManager = CompraManager;
