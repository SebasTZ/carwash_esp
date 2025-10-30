export default class ProductoForm {
    constructor({ elementId, marcas = [], presentaciones = [], categorias = [], old = {} }) {
        const container = document.getElementById(elementId);
        if (!container) return;
        container.innerHTML = `
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" name="nombre" id="nombre" class="form-control" value="${old.nombre || ''}" required>
            </div>
            <div class="mb-3">
                <label for="descripcion" class="form-label">Descripción</label>
                <textarea name="descripcion" id="descripcion" class="form-control">${old.descripcion || ''}</textarea>
            </div>
            <div class="mb-3">
                <label for="marca_id" class="form-label">Marca</label>
                <select name="marca_id" id="marca_id" class="form-control">
                    <option value="">Seleccione...</option>
                    ${marcas.map(m => `<option value='${m.id}' ${old.marca_id == m.id ? 'selected' : ''}>${m.nombre}</option>`).join('')}
                </select>
            </div>
            <div class="mb-3">
                <label for="presentacion_id" class="form-label">Presentación</label>
                <select name="presentacion_id" id="presentacion_id" class="form-control">
                    <option value="">Seleccione...</option>
                    ${presentaciones.map(p => `<option value='${p.id}' ${old.presentacion_id == p.id ? 'selected' : ''}>${p.nombre}</option>`).join('')}
                </select>
            </div>
            <div class="mb-3">
                <label for="categoria_id" class="form-label">Categoría</label>
                <select name="categoria_id" id="categoria_id" class="form-control">
                    <option value="">Seleccione...</option>
                    ${categorias.map(c => `<option value='${c.id}' ${old.categoria_id == c.id ? 'selected' : ''}>${c.nombre}</option>`).join('')}
                </select>
            </div>
            <div class="mb-3">
                <label for="precio" class="form-label">Precio</label>
                <input type="number" name="precio" id="precio" class="form-control" value="${old.precio || ''}" required step="0.01">
            </div>
        `;
    }
}