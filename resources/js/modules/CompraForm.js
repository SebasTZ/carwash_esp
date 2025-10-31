export default class CompraForm {
    constructor({ elementId, productos = [], proveedores = [], comprobantes = [], impuesto = 18, old = {}, errors = [], action = '', method = 'POST', onFormReady = null }) {
        console.log('[CompraForm] Data recibida:', { productos, proveedores, comprobantes, impuesto, old, errors, action, method });
        const container = document.getElementById(elementId);
        if (!container) {
            console.warn('[CompraForm] No se encontró el contenedor:', elementId);
            return;
        }
        container.innerHTML = `
            <form action="${action}" method="${method}">
                <div class="mb-3">
                    <label for="proveedor_id" class="form-label">Proveedor</label>
                    <select name="proveedor_id" id="proveedor_id" class="form-control" required>
                        <option value="">Seleccione...</option>
                        ${proveedores.map(p => `<option value='${p.id}' ${old.proveedor_id == p.id ? 'selected' : ''}>${p.nombre}</option>`).join('')}
                    </select>
                </div>
                <div class="mb-3">
                    <label for="producto_id" class="form-label">Producto</label>
                    <select name="producto_id" id="producto_id" class="form-control" required>
                        <option value="">Seleccione...</option>
                        ${productos.map(pr => `<option value='${pr.id}' ${old.producto_id == pr.id ? 'selected' : ''}>${pr.nombre}</option>`).join('')}
                    </select>
                </div>
                <div class="mb-3">
                    <label for="cantidad" class="form-label">Cantidad</label>
                    <input type="number" name="cantidad" id="cantidad" class="form-control" value="${old.cantidad || ''}" required min="1">
                </div>
                <div class="mb-3">
                    <label for="precio_unitario" class="form-label">Precio Unitario</label>
                    <input type="number" name="precio_unitario" id="precio_unitario" class="form-control" value="${old.precio_unitario || ''}" required step="0.01">
                </div>
                <div class="mb-3">
                    <label for="comprobante_id" class="form-label">Comprobante</label>
                    <select name="comprobante_id" id="comprobante_id" class="form-control" required>
                        <option value="">Seleccione...</option>
                        ${comprobantes.map(c => `<option value='${c.id}' ${old.comprobante_id == c.id ? 'selected' : ''}>${c.tipo_comprobante}</option>`).join('')}
                    </select>
                </div>
                <div class="mb-3">
                    <label for="impuesto" class="form-label">Impuesto (%)</label>
                    <input type="number" name="impuesto" id="impuesto" class="form-control" value="${impuesto}" required min="0" max="100">
                </div>
                <button type="submit" class="btn btn-primary">Registrar compra</button>
            </form>
        `;
        if (typeof onFormReady === 'function') {
            const form = container.querySelector('form');
            if (form) {
                onFormReady(form);
            }
        }
    }
}