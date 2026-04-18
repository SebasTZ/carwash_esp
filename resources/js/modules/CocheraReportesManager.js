/**
 * CocheraReportesManager
 * Inicializa DataTable, gráficos y exportación Excel en reportes de cochera.
 */

import { DataTable } from 'simple-datatables';
import 'simple-datatables/dist/style.css';
import { Chart } from 'chart.js/auto';

class CocheraReportesManager {
    constructor() {
        this.table = document.getElementById('tabla-reportes');
        if (!this.table) {
            return;
        }

        this.initialize();
    }

    initialize() {
        this.initializeDataTable();
        this.initializeCharts();
        this.bindExportButton();
    }

    parseJsonPayload(elementId, fallback = []) {
        const element = document.getElementById(elementId);
        if (!element) {
            return fallback;
        }

        try {
            return JSON.parse(element.textContent || '[]');
        } catch (error) {
            console.error(`[CocheraReportesManager] No se pudo parsear ${elementId}:`, error);
            return fallback;
        }
    }

    initializeDataTable() {
        const dataTable = new DataTable(this.table, {
            perPage: 10,
            searchable: true,
            perPageSelect: [5, 10, 25, 50],
            labels: {
                placeholder: 'Buscar...',
                perPage: 'registros por página',
                noRows: 'No se encontraron registros',
                info: 'Mostrando {start} a {end} de {rows} registros',
            },
            layout: {
                top: '{select}{search}',
                bottom: '{info}{pager}',
            },
        });

        if (dataTable?.columns?.sort) {
            dataTable.columns.sort(0, 'desc');
        }
    }

    initializeCharts() {
        const ingresosLabels = this.parseJsonPayload('cochera-ingresos-labels');
        const ingresosData = this.parseJsonPayload('cochera-ingresos-data');
        const tiposLabels = this.parseJsonPayload('cochera-tipos-labels');
        const tiposData = this.parseJsonPayload('cochera-tipos-data');

        const ingresosCanvas = document.getElementById('ingresosPorDia');
        if (ingresosCanvas) {
            new Chart(ingresosCanvas.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: ingresosLabels,
                    datasets: [
                        {
                            label: 'Ingresos Diarios (S/)',
                            data: ingresosData,
                            backgroundColor: 'rgba(60, 141, 188, 0.8)',
                            borderWidth: 1,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback(value) {
                                    return `S/ ${value}`;
                                },
                            },
                        },
                    },
                },
            });
        }

        const tipoVehiculoCanvas = document.getElementById('vehiculosPorTipo');
        if (tipoVehiculoCanvas) {
            const colors = [
                'rgba(60, 141, 188, 0.8)',
                'rgba(255, 193, 7, 0.8)',
                'rgba(40, 167, 69, 0.8)',
                'rgba(220, 53, 69, 0.8)',
                'rgba(108, 117, 125, 0.8)',
                'rgba(23, 162, 184, 0.8)',
                'rgba(111, 66, 193, 0.8)',
            ];

            new Chart(tipoVehiculoCanvas.getContext('2d'), {
                type: 'pie',
                data: {
                    labels: tiposLabels,
                    datasets: [
                        {
                            data: tiposData,
                            backgroundColor: colors.slice(0, tiposLabels.length),
                            hoverOffset: 4,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                        },
                    },
                },
            });
        }
    }

    bindExportButton() {
        const button = document.getElementById('btnExportCochera');
        if (!button) {
            return;
        }

        button.addEventListener('click', () => this.exportToCsv());
    }

    escapeCsvCell(cellValue) {
        const normalizedValue = String(cellValue ?? '').replace(/\r?\n|\r/g, ' ').trim();
        const escapedValue = normalizedValue.replace(/"/g, '""');
        return `"${escapedValue}"`;
    }

    exportToCsv() {
        const clonedTable = this.table.cloneNode(true);
        Array.from(clonedTable.querySelectorAll('tr')).forEach((row) => {
            if (row.lastElementChild) {
                row.removeChild(row.lastElementChild);
            }
        });

        const headers = Array.from(clonedTable.querySelectorAll('thead th')).map((th) => th.innerText);
        const rows = Array.from(clonedTable.querySelectorAll('tbody tr')).map((row) =>
            Array.from(row.querySelectorAll('td')).map((td) => td.innerText)
        );

        const csvContent = [headers, ...rows]
            .map((row) => row.map((value) => this.escapeCsvCell(value)).join(','))
            .join('\r\n');

        const csvBlob = new Blob([`\uFEFF${csvContent}`], {
            type: 'text/csv;charset=utf-8;',
        });
        const currentDate = new Date().toISOString().slice(0, 10);
        const downloadUrl = URL.createObjectURL(csvBlob);
        const link = document.createElement('a');
        link.href = downloadUrl;
        link.download = `Reporte_Cochera_${currentDate}.csv`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(downloadUrl);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    window.cocheraReportesManager = new CocheraReportesManager();
});

export default CocheraReportesManager;