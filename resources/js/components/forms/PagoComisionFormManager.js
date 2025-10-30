// PagoComisionFormManager.js
// Componente JS para gestionar el formulario de pago de comisión

export const PagoComisionFormManager = {
    init({ el, lavadores = [], old = {}, errors = {} }) {
        const container = document.querySelector(el);
        if (!container) return;
        container.innerHTML = '';
        // Renderizar formulario
        container.innerHTML = `
            <form id="pagoForm" action="/pagos_comisiones" method="POST" novalidate>
                <input type="hidden" name="_token" value="${window.Laravel.csrfToken}">
                <div class="mb-3">
                    <label for="lavador_id" class="form-label">Lavador</label>
                    <select name="lavador_id" id="lavador_id" class="form-control" required>
                        <option value="">Seleccione un lavador</option>
                        ${lavadores.map(l => `<option value='${l.id}' ${old.lavador_id == l.id ? 'selected' : ''}>${l.nombre}</option>`).join('')}
                    </select>
                    <div class="invalid-feedback">${errors.lavador_id || ''}</div>
                </div>
                <div class="mb-3">
                    <label for="monto_pagado" class="form-label">Monto Pagado</label>
                    <input type="number" step="0.01" name="monto_pagado" id="monto_pagado" class="form-control" value="${old.monto_pagado || ''}" required>
                    <div class="invalid-feedback">${errors.monto_pagado || ''}</div>
                </div>
                <div class="mb-3">
                    <label for="desde" class="form-label">Desde</label>
                    <input type="date" name="desde" id="desde" class="form-control" value="${old.desde || ''}" required>
                    <div class="invalid-feedback">${errors.desde || ''}</div>
                </div>
                <div class="mb-3">
                    <label for="hasta" class="form-label">Hasta</label>
                    <input type="date" name="hasta" id="hasta" class="form-control" value="${old.hasta || ''}" required>
                    <div class="invalid-feedback">${errors.hasta || ''}</div>
                </div>
                <div class="mb-3">
                    <label for="fecha_pago" class="form-label">Fecha de Pago</label>
                    <input type="date" name="fecha_pago" id="fecha_pago" class="form-control" value="${old.fecha_pago || ''}" required>
                    <div class="invalid-feedback">${errors.fecha_pago || ''}</div>
                </div>
                <div class="mb-3">
                    <label for="observacion" class="form-label">Observación</label>
                    <textarea name="observacion" id="observacion" class="form-control" rows="3">${old.observacion || ''}</textarea>
                    <div class="invalid-feedback">${errors.observacion || ''}</div>
                </div>
                <button type="submit" class="btn btn-success">Guardar</button>
                <a href="/pagos_comisiones" class="btn btn-secondary">Cancelar</a>
            </form>
        `;
        // Inicializar validador si existe
        if (window.CarWash && window.CarWash.FormValidator) {
            const validator = new window.CarWash.FormValidator('#pagoForm', {
                lavador_id: {
                    required: { message: 'Debe seleccionar un lavador' }
                },
                monto_pagado: {
                    required: { message: 'El monto es obligatorio' },
                    number: { message: 'Debe ser un número válido' },
                    min: { value: 0.01, message: 'El monto debe ser mayor a 0' }
                },
                desde: {
                    required: { message: 'La fecha inicial es obligatoria' }
                },
                hasta: {
                    required: { message: 'La fecha final es obligatoria' }
                },
                fecha_pago: {
                    required: { message: 'La fecha de pago es obligatoria' }
                }
            });
            validator.init();
        }
    }
};

window.PagoComisionFormManager = PagoComisionFormManager;
