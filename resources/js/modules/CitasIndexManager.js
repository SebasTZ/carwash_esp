/**
 * CitasIndexManager
 * Reemplaza el script inline del listado de citas.
 */
import { getCsrfToken } from '@utils/csrf';
import { readJsonScript } from '@utils/json-script';

class CitasIndexManager {
    constructor() {
        this.DynamicTable = window.CarWash?.DynamicTable;
        this.tableSelector = '#citasTable';
        this.dataElementId = 'citas-table-data';
        this.endpointsElementId = 'citas-endpoints-config';
        this.csrfToken = getCsrfToken();
        this.endpointsConfig = this.getEndpointsConfig();

        if (!this.DynamicTable) {
            console.warn('[CitasIndexManager] DynamicTable no está disponible en window.CarWash');
            return;
        }

        if (!document.querySelector(this.tableSelector)) {
            return;
        }

        this.init();
    }

    init() {
        const data = this.getData();

        new this.DynamicTable(this.tableSelector, {
            columns: this.getColumns(),
            data,
            searchPlaceholder: 'Buscar citas...',
            emptyMessage: 'No hay citas registradas',
        });
    }

    getData() {
        return readJsonScript(this.dataElementId, [], 'CitasIndexManager');
    }

    getEndpointsConfig() {
        return readJsonScript(this.endpointsElementId, {}, 'CitasIndexManager');
    }

    resolveCitaUrl(key, citaId) {
        const template = this.endpointsConfig?.[key];
        const idEncoded = encodeURIComponent(String(citaId));

        if (typeof template === 'string' && template.includes('__cita__')) {
            return template.replace('__cita__', idEncoded);
        }

        console.error(`[CitasIndexManager] Endpoint no configurado para ${key}`);
        return '#';
    }

    getColumns() {
        return [
            { key: 'id', label: '#' },
            {
                key: 'cliente.persona.razon_social',
                label: 'Cliente',
                formatter: (value, row) => {
                    const doc = row.cliente?.persona?.numero_documento || '';
                    return `${value} - ${doc}`;
                },
            },
            {
                key: 'fecha',
                label: 'Fecha',
                formatter: (value) => {
                    const date = new Date(`${value}T00:00:00`);
                    return date.toLocaleDateString('es-ES', {
                        day: '2-digit',
                        month: '2-digit',
                        year: 'numeric',
                    });
                },
            },
            {
                key: 'hora',
                label: 'Hora',
                formatter: (value) => {
                    if (!value) {
                        return '-';
                    }

                    const [hours, minutes] = value.split(':');
                    return `${hours}:${minutes}`;
                },
            },
            {
                key: 'posicion_cola',
                label: 'Posición',
                formatter: (value) => `<span class="badge bg-info">${value}</span>`,
            },
            {
                key: 'estado',
                label: 'Estado',
                formatter: (value) => {
                    const badges = {
                        pendiente: '<span class="badge bg-warning">Pendiente</span>',
                        en_proceso: '<span class="badge bg-primary">En Proceso</span>',
                        completada: '<span class="badge bg-success">Completada</span>',
                        cancelada: '<span class="badge bg-danger">Cancelada</span>',
                    };

                    return badges[value] || value;
                },
            },
            {
                key: 'actions',
                label: 'Acciones',
                formatter: (_, row) => this.renderActionButtons(row),
            },
        ];
    }

    renderActionButtons(row) {
        const showUrl = this.resolveCitaUrl('show', row.id);
        const editUrl = this.resolveCitaUrl('edit', row.id);
        const iniciarUrl = this.resolveCitaUrl('iniciar', row.id);
        const completarUrl = this.resolveCitaUrl('completar', row.id);
        const cancelarUrl = this.resolveCitaUrl('cancelar', row.id);
        const destroyUrl = this.resolveCitaUrl('destroy', row.id);

        let buttons = '<div class="btn-group" role="group">';

        buttons += `<a href="${showUrl}" class="btn btn-info btn-sm" title="Ver detalles">
            <i class="fas fa-eye"></i>
        </a>`;

        if (row.estado !== 'completada' && row.estado !== 'cancelada') {
            buttons += `<a href="${editUrl}" class="btn btn-primary btn-sm" title="Editar">
                <i class="fas fa-edit"></i>
            </a>`;
        }

        if (row.estado === 'pendiente') {
            buttons += this.buildPostActionButton({
                action: iniciarUrl,
                title: 'Iniciar Cita',
                buttonClass: 'btn btn-success btn-sm',
                iconClass: 'fas fa-play',
            });
        }

        if (row.estado === 'en_proceso') {
            buttons += this.buildPostActionButton({
                action: completarUrl,
                title: 'Completar Cita',
                buttonClass: 'btn btn-success btn-sm',
                iconClass: 'fas fa-check',
            });
        }

        if (row.estado !== 'completada' && row.estado !== 'cancelada') {
            buttons += this.buildPostActionButton({
                action: cancelarUrl,
                title: 'Cancelar Cita',
                buttonClass: 'btn btn-danger btn-sm',
                iconClass: 'fas fa-times',
                confirmText: '¿Está seguro de cancelar esta cita?',
                confirmButtonText: 'Cancelar cita',
            });
        }

        buttons += this.buildPostActionButton({
            action: destroyUrl,
            title: 'Eliminar',
            buttonClass: 'btn btn-danger btn-sm',
            iconClass: 'fas fa-trash',
            method: 'DELETE',
            confirmText: '¿Está seguro de eliminar esta cita?',
            confirmButtonText: 'Eliminar',
        });

        buttons += '</div>';

        return buttons;
    }

    buildPostActionButton({
        action,
        title,
        buttonClass,
        iconClass,
        method = 'POST',
        confirmText = null,
        confirmButtonText = null,
    }) {
        const methodInput = method !== 'POST'
            ? `<input type="hidden" name="_method" value="${method}">`
            : '';

        const confirmAttributes = confirmText
            ? `data-confirm="${confirmText}" data-confirm-confirm-text="${confirmButtonText || 'Confirmar'}"`
            : '';

        return `<form action="${action}" method="POST" style="display:inline">
            <input type="hidden" name="_token" value="${this.csrfToken}">
            ${methodInput}
            <button type="submit" class="${buttonClass}" title="${title}" ${confirmAttributes}>
                <i class="${iconClass}"></i>
            </button>
        </form>`;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    window.citasIndexManager = new CitasIndexManager();
});

export default CitasIndexManager;
