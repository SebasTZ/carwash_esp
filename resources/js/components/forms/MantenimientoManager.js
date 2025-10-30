// MantenimientoManager.js
// Componente JS para gestionar el formulario de mantenimiento

export const MantenimientoManager = {
    init({ el, mantenimiento = {}, old = {} }) {
        const container = document.querySelector(el);
        if (!container) return;
        container.innerHTML = '';
        // Renderizar campos básicos
        container.innerHTML += `
            <div class="mb-3">
                <label for="mantenimiento-nombre" class="form-label">Nombre</label>
                <input type="text" name="nombre" id="mantenimiento-nombre" class="form-control" value="${old.nombre || mantenimiento.nombre || ''}" required>
            </div>
            <div class="mb-3">
                <label for="mantenimiento-descripcion" class="form-label">Descripción</label>
                <textarea name="descripcion" id="mantenimiento-descripcion" class="form-control">${old.descripcion || mantenimiento.descripcion || ''}</textarea>
            </div>
        `;
    }
};

window.MantenimientoManager = MantenimientoManager;
