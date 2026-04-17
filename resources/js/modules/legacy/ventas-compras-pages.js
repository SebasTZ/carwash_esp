import { getCsrfToken } from '@utils/csrf';
import { readJsonScript } from '@utils/json-script';

export const initVentaPages = () => {
    const DynamicTable = window.CarWash?.DynamicTable;
    if (!DynamicTable) {
        return;
    }

    const formatCurrency = (value) => `S/ ${Number(value || 0).toFixed(2)}`;
    const csrfToken = getCsrfToken();

    const ventasIndexNode = document.getElementById('ventas-dynamic-table');
    if (ventasIndexNode) {
        const config = readJsonScript('ventas-index-config', null, 'LegacyInlineMigration');
        if (config) {
            new DynamicTable('#ventas-dynamic-table', {
                searchable: true,
                searchPlaceholder: 'Buscar ventas...',
                data: Array.isArray(config.data) ? config.data : [],
                columns: [
                    {
                        key: 'comprobante',
                        label: 'Comprobante',
                        formatter: (value, row) => {
                            const tipo = row.comprobante?.tipo_comprobante || value?.tipo_comprobante || row.comprobante || '-';
                            const numero = row.comprobante?.numero_comprobante || value?.numero_comprobante || row.numero_comprobante || '-';
                            return `<strong>${tipo}</strong><br><small class="text-muted">${numero}</small>`;
                        },
                    },
                    {
                        key: 'cliente',
                        label: 'Cliente',
                        formatter: (value, row) => row.cliente?.persona?.razon_social || value?.persona?.razon_social || value || '-',
                    },
                    {
                        key: 'fecha_hora',
                        label: 'Fecha y Hora',
                        formatter: (value, row) => {
                            const dateValue = value || row.fecha_hora || `${row.fecha || ''} ${row.hora || ''}`;
                            return dateValue || '-';
                        },
                    },
                    {
                        key: 'vendedor',
                        label: 'Vendedor',
                        formatter: (value, row) => row.vendedor?.name || value?.name || value || '-',
                    },
                    {
                        key: 'total',
                        label: 'Total',
                        formatter: (value) => formatCurrency(value),
                    },
                    {
                        key: 'medio_pago',
                        label: 'Método de Pago',
                        formatter: (value) => value || '-',
                    },
                    {
                        key: 'servicio_lavado',
                        label: 'Servicio de Lavado',
                        formatter: (value) => {
                            const enabled = value === true || value === 1 || value === '1';
                            return enabled
                                ? '<span class="badge bg-success">Sí</span>'
                                : '<span class="badge bg-secondary">No</span>';
                        },
                    },
                    {
                        key: 'acciones',
                        label: 'Acciones',
                        formatter: (_, row) => {
                            let actions = '<div class="btn-group btn-group-sm" role="group">';

                            if (config.canShow) {
                                actions += `<a href="/ventas/${row.id}" class="btn btn-primary" title="Ver"><i class="fas fa-eye"></i></a>`;
                            }

                            if (config.canDelete) {
                                actions += `
                                    <form action="/ventas/${row.id}" method="POST" style="display:inline;">
                                        <input type="hidden" name="_token" value="${csrfToken}">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button type="submit" class="btn btn-danger" data-confirm="¿Está seguro de eliminar esta venta?" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                `;
                            }

                            actions += '</div>';
                            return actions;
                        },
                    },
                ],
            });
        }
    }

    const ventasReporteNode = document.getElementById('ventas-reporte-dynamic-table');
    if (ventasReporteNode) {
        const config = readJsonScript('ventas-reporte-config', null, 'LegacyInlineMigration');
        if (!config) {
            return;
        }

        new DynamicTable('#ventas-reporte-dynamic-table', {
            searchable: true,
            searchPlaceholder: 'Buscar reporte de ventas...',
            data: Array.isArray(config.data) ? config.data : [],
            columns: [
                { key: 'comprobante', label: 'Comprobante' },
                { key: 'cliente', label: 'Cliente' },
                { key: 'fecha_hora', label: 'Fecha y Hora' },
                { key: 'vendedor', label: 'Vendedor' },
                { key: 'total', label: 'Total', formatter: (value) => formatCurrency(value) },
                { key: 'comentarios', label: 'Comentarios' },
                { key: 'medio_pago', label: 'Medio de Pago' },
                { key: 'efectivo', label: 'Efectivo' },
                { key: 'tarjeta_credito', label: 'Tarjeta Crédito' },
                { key: 'tarjeta_regalo_id', label: 'Tarjeta Regalo' },
                { key: 'lavado_gratis', label: 'Lavado Gratis' },
                { key: 'servicio_lavado', label: 'Servicio Lavado' },
                { key: 'horario_lavado', label: 'Hora Fin de Lavado' },
            ],
            onRender: (rows) => {
                let total = 0;
                rows.forEach((row) => {
                    const medioPago = row.medio_pago ? String(row.medio_pago).toLowerCase() : '';
                    if (medioPago !== 'tarjeta regalo' && medioPago !== 'lavado gratis (fidelidad)') {
                        total += parseFloat(row.total) || 0;
                    }
                });

                const totalNode = document.getElementById('ventas-reporte-total');
                if (totalNode) {
                    totalNode.innerText = `Total Ventas: S/. ${total.toFixed(2)}`;
                }
            },
        });
    }
};

export const initCompraPages = () => {
    const DynamicTable = window.CarWash?.DynamicTable;
    if (!DynamicTable) {
        return;
    }

    const formatCompraRow = (row = {}) => ({
        ...row,
        comprobante: row.comprobante || '-',
        numero_comprobante: row.numero_comprobante || '-',
        tipo_persona: row.tipo_persona || '-',
        razon_social: row.razon_social || '-',
        fecha: row.fecha || '-',
        hora: row.hora || '-',
        impuesto: Number(row.impuesto || 0),
        total: Number(row.total || 0),
    });

    const comprasIndexNode = document.getElementById('dynamicTableCompras');
    if (comprasIndexNode) {
        try {
            const config = readJsonScript('compras-index-config', null, 'LegacyInlineMigration');
            let data = [];

            if (config && Array.isArray(config.data)) {
                data = config.data.map(formatCompraRow);
            } else {
                console.warn('[LegacyInlineMigration] compras-index-config no está disponible; se renderiza tabla vacía.');
            }

            new DynamicTable('#dynamicTableCompras', {
                searchable: true,
                searchPlaceholder: 'Buscar compras...',
                data,
                columns: [
                    {
                        key: 'comprobante',
                        label: 'Comprobante',
                        formatter: (value, row) => `<p class='fw-semibold mb-1'>${row.comprobante || '-'}</p><p class='text-muted mb-0'>${row.numero_comprobante || '-'}</p>`,
                    },
                    {
                        key: 'proveedor',
                        label: 'Proveedor',
                        formatter: (value, row) => `<p class='fw-semibold mb-1'>${row.tipo_persona || '-'}</p><p class='text-muted mb-0'>${row.razon_social || '-'}</p>`,
                    },
                    {
                        key: 'fecha_hora',
                        label: 'Fecha y Hora',
                        formatter: (value, row) => `<div class='row-not-space'><p class='fw-semibold mb-1'><span class='m-1'><i class='fa-solid fa-calendar-days'></i></span>${row.fecha || '-'}</p><p class='fw-semibold mb-0'><span class='m-1'><i class='fa-solid fa-clock'></i></span>${row.hora || '-'}</p></div>`,
                    },
                    {
                        key: 'total',
                        label: 'Total',
                        formatter: (value) => formatCurrency(value),
                    },
                    {
                        key: 'acciones',
                        label: 'Acciones',
                        formatter: (_, row) => {
                            const canShow = Boolean(config?.canShow);
                            const canDelete = Boolean(config?.canDelete);

                            if (!canShow && !canDelete) {
                                return '-';
                            }

                            let actions = '<div class="btn-group btn-group-sm" role="group">';

                            if (canShow) {
                                actions += `<a href="/compras/${row.id}" class="btn btn-primary" title="Ver"><i class="fas fa-eye"></i></a>`;
                            }

                            if (canDelete) {
                                actions += `
                                    <form action="/compras/${row.id}" method="POST" style="display:inline;">
                                        <input type="hidden" name="_token" value="${csrfToken}">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button type="submit" class="btn btn-danger" data-confirm="¿Está seguro de eliminar esta compra?" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                `;
                            }

                            actions += '</div>';
                            return actions;
                        },
                    },
                ],
            });
        } catch (error) {
            console.error('[LegacyInlineMigration] Error cargando compras', error);
        }
    }

    const comprasReporteNode = document.getElementById('dynamicTableComprasReporte');
    if (comprasReporteNode) {
        const reportType = comprasReporteNode.getAttribute('data-report-type');
        if (!reportType) {
            return;
        }

        try {
            const config = readJsonScript('compras-reporte-config', null, 'LegacyInlineMigration');
            let data = [];

            if (config && Array.isArray(config.data)) {
                data = config.data.map(formatCompraRow);
            } else {
                console.warn('[LegacyInlineMigration] compras-reporte-config no está disponible; se renderiza tabla vacía.');
            }

            new DynamicTable('#dynamicTableComprasReporte', {
                searchable: true,
                searchPlaceholder: 'Buscar compras...',
                data,
                columns: [
                    {
                        key: 'comprobante',
                        label: 'Comprobante',
                        formatter: (value, row) => `<p class='fw-semibold mb-1'>${row.comprobante || '-'}</p><p class='text-muted mb-0'>${row.numero_comprobante || '-'}</p>`,
                    },
                    {
                        key: 'proveedor',
                        label: 'Proveedor',
                        formatter: (value, row) => `<p class='fw-semibold mb-1'>${row.tipo_persona || '-'}</p><p class='text-muted mb-0'>${row.razon_social || '-'}</p>`,
                    },
                    {
                        key: 'fecha_hora',
                        label: 'Fecha y Hora',
                        formatter: (value, row) => `<div class='row-not-space'><p class='fw-semibold mb-1'><span class='m-1'><i class='fa-solid fa-calendar-days'></i></span>${row.fecha || '-'}</p><p class='fw-semibold mb-0'><span class='m-1'><i class='fa-solid fa-clock'></i></span>${row.hora || '-'}</p></div>`,
                    },
                    {
                        key: 'impuesto',
                        label: 'IGV',
                        formatter: (value) => `${Number(value || 0).toFixed(2)}%`,
                    },
                    {
                        key: 'total',
                        label: 'Total',
                        formatter: (value) => formatCurrency(value),
                    },
                ],
            });
        } catch (error) {
            console.error('[LegacyInlineMigration] Error cargando reporte de compras', error);
        }
    }
};
