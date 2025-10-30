// FidelidadManager.js
// Componente JS para gestionar el formulario de fidelidad

export const FidelidadManager = {
    init({ el, fidelidad = {}, clientes = [], old = {} }) {
        const container = document.querySelector(el);
        if (!container) return;
        container.innerHTML = '';
        // Renderizar campos básicos
        container.innerHTML += `
            <div class="mb-3">
                <label for="fidelidad-cliente" class="form-label">Cliente</label>
                <select name="cliente_id" id="fidelidad-cliente" class="form-control">
                    ${clientes.map(c => `<option value="${c.id}" ${old.cliente_id == c.id ? 'selected' : ''}>${c.nombre}</option>`).join('')}
                </select>
            </div>
            <div class="mb-3">
                <label for="fidelidad-puntos" class="form-label">Puntos</label>
                <input type="number" name="puntos" id="fidelidad-puntos" class="form-control" value="${old.puntos || fidelidad.puntos || 0}" min="0" required>
            </div>
        `;
    }
};

window.FidelidadManager = FidelidadManager;
