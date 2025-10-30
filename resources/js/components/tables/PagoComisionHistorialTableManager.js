// PagoComisionHistorialTableManager.js
// Componente JS para mostrar el historial de pagos de comisión de un lavador

export const PagoComisionHistorialTableManager = {
    init({ el, pagos = [], lavador = {}, reporteUrl = '', fechaInicio = null, fechaFin = null }) {
        const container = document.querySelector(el);
        if (!container) return;
        container.innerHTML = '';
        // Renderizar encabezado y botón de reporte
        container.innerHTML += `
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Historial de Pagos de Comisión de <span class="badge bg-light text-primary">${lavador.nombre}</span></h4>
                    <a href="${reporteUrl}" class="btn btn-outline-light btn-sm">
                        <i class="fas fa-chart-line me-1"></i> Ver Reporte de Comisiones
                    </a>
                </div>
                <div class="card-body">
                    ${pagos.length === 0 ? `<div class='alert alert-info'>No hay pagos de comisión registrados para este lavador.</div>` : `
                        <div class='table-responsive'>
                            <table class='table table-bordered align-middle'>
                                <thead class='table-light'>
                                    <tr>
                                        <th>Monto Pagado</th>
                                        <th>Desde</th>
                                        <th>Hasta</th>
                                        <th>Fecha de Pago</th>
                                        <th>Observación</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${pagos.map(pago => `
                                        <tr>
                                            <td><span class='badge bg-success'>S/ ${parseFloat(pago.monto_pagado).toFixed(2)}</span></td>
                                            <td>${pago.desde ? new Date(pago.desde).toLocaleDateString('es-PE') : '-'}</td>
                                            <td>${pago.hasta ? new Date(pago.hasta).toLocaleDateString('es-PE') : '-'}</td>
                                            <td>${pago.fecha_pago ? new Date(pago.fecha_pago).toLocaleDateString('es-PE') : '-'}</td>
                                            <td>${pago.observacion || '-'}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    `}
                </div>
                <div class="card-footer text-end">
                    ${fechaInicio && fechaFin ? `
                        <a href="/pagos_comisiones/reporte?fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Volver al Reporte
                        </a>
                    ` : `
                        <a href="/pagos_comisiones" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Volver a Pagos
                        </a>
                    `}
                </div>
            </div>
        `;
    }
};

window.PagoComisionHistorialTableManager = PagoComisionHistorialTableManager;
