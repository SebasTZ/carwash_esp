// ControlLavadoManager.js
// Componente JS para gestionar el formulario de control de lavado

export const ControlLavadoManager = {
    init({ el, lavado = {}, old = {} }) {
        const container = document.querySelector(el);
        if (!container) return;
        container.innerHTML = '';
        // Renderizar campos básicos
        container.innerHTML += `
            <div class="mb-3">
                <label for="lavado-vehiculo" class="form-label">Vehículo</label>
                <input type="text" name="vehiculo" id="lavado-vehiculo" class="form-control" value="${old.vehiculo || lavado.vehiculo || ''}" required>
            </div>
            <div class="mb-3">
                <label for="lavado-estado" class="form-label">Estado</label>
                <select name="estado" id="lavado-estado" class="form-control">
                    <option value="Pendiente" ${old.estado == 'Pendiente' ? 'selected' : ''}>Pendiente</option>
                    <option value="En Proceso" ${old.estado == 'En Proceso' ? 'selected' : ''}>En Proceso</option>
                    <option value="Terminado" ${old.estado == 'Terminado' ? 'selected' : ''}>Terminado</option>
                </select>
            </div>
        `;
    }
};

window.ControlLavadoManager = ControlLavadoManager;
