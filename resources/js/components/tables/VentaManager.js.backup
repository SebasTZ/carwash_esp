// VentaManager.js
// Componente JS para gestionar el formulario de ventas

export const VentaManager = {
    init({ el, venta = {}, productos = [], clientes = [], old = {} }) {
        const container = document.querySelector(el);
        if (!container) return;
        container.innerHTML = '';
        // Renderizar campos básicos
        container.innerHTML += `
            <div class="mb-3">
                <label for="venta-cliente" class="form-label">Cliente</label>
                <select name="cliente_id" id="venta-cliente" class="form-control">
                    ${clientes.map(c => `<option value="${c.id}" ${old.cliente_id == c.id ? 'selected' : ''}>${c.nombre}</option>`).join('')}
                </select>
            </div>
            <div class="mb-3">
                <label for="venta-producto" class="form-label">Producto</label>
                <select name="producto_id" id="venta-producto" class="form-control">
                    ${productos.map(p => `<option value="${p.id}" ${old.producto_id == p.id ? 'selected' : ''}>${p.nombre}</option>`).join('')}
                </select>
            </div>
            <div class="mb-3">
                <label for="venta-cantidad" class="form-label">Cantidad</label>
                <input type="number" name="cantidad" id="venta-cantidad" class="form-control" value="${old.cantidad || venta.cantidad || 1}" min="1" required>
            </div>
        `;
    }
};

window.VentaManager = VentaManager;
