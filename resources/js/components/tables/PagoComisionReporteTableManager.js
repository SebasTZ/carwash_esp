// PagoComisionReporteTableManager.js
// Componente JS para mostrar el reporte de comisiones por lavador y el historial de pagos

export const PagoComisionReporteTableManager = {
    init({ el, reporte = [], historial = [], fechaInicio = '', fechaFin = '' }) {
        const container = document.querySelector(el);
        if (!container) return;
        container.innerHTML = '';
        // Renderizar filtro y exportar
        container.innerHTML += `
            <h1>Reporte de Comisiones por Lavador</h1>
            <form method="GET" action="/pagos_comisiones/reporte" class="row g-3 mb-3">
                <div class="col-md-4">
                    <label for="fecha_inicio" class="form-label">Desde</label>
                    <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control" value="${fechaInicio}">
                </div>
                <div class="col-md-4">
                    <label for="fecha_fin" class="form-label">Hasta</label>
                    <input type="date" name="fecha_fin" id="fecha_fin" class="form-control" value="${fechaFin}">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                    <a href="/pagos_comisiones/reporte/export?fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}" class="btn btn-success ms-2">Exportar a Excel</a>
                </div>
            </form>
        `;
        // Renderizar tabla de reporte
        container.innerHTML += `
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Lavador</th>
                        <th>Número de Lavados</th>
                        <th>Comisión Total</th>
                        <th>Total Pagado</th>
                        <th>Pendiente</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    ${reporte.map(row => `
                        <tr>
                            <td>${row.lavador.nombre}</td>
                            <td>${row.cantidad}</td>
                            <td>${parseFloat(row.comision_total).toFixed(2)}</td>
                            <td>${parseFloat(row.pagado).toFixed(2)}</td>
                            <td>${parseFloat(row.saldo).toFixed(2)}</td>
                            <td>
                                <a href="/pagos_comisiones/lavador/${row.lavador.id}?fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}" class="btn btn-sm btn-info">Historial</a>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        `;
        // Renderizar historial de pagos
        container.innerHTML += `
            <h2 class="mt-5">Historial de Pagos de Comisión</h2>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Lavador</th>
                        <th>Monto Pagado</th>
                        <th>Desde</th>
                        <th>Hasta</th>
                        <th>Fecha de Pago</th>
                        <th>Observación</th>
                    </tr>
                </thead>
                <tbody>
                    ${historial.map(pago => `
                        <tr>
                            <td>${pago.lavador_nombre}</td>
                            <td>${parseFloat(pago.monto_pagado).toFixed(2)}</td>
                            <td>${pago.desde ? new Date(pago.desde).toLocaleDateString('es-PE') : '-'}</td>
                            <td>${pago.hasta ? new Date(pago.hasta).toLocaleDateString('es-PE') : '-'}</td>
                            <td>${pago.fecha_pago ? new Date(pago.fecha_pago).toLocaleDateString('es-PE') : '-'}</td>
                            <td>${pago.observacion || '-'}</td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        `;
    }
};

window.PagoComisionReporteTableManager = PagoComisionReporteTableManager;
