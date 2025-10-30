// LavadorTableManager.js
import DynamicTable from './DynamicTable';

export default class LavadorTableManager {
    constructor(tableElement, lavadores, canEdit, canDelete) {
        const data = (lavadores || []).map(lavador => {
            let acciones = '';
            if (canEdit) {
                acciones += `<a href='/lavadores/${lavador.id}/edit' class='btn btn-sm btn-info'>Editar</a> `;
            }
            if (canDelete) {
                acciones += `<form action='/lavadores/${lavador.id}' method='POST' style='display:inline-block'>`
                    + `<input type='hidden' name='_token' value='${document.querySelector('meta[name="csrf-token"]').content}'>`
                    + `<input type='hidden' name='_method' value='DELETE'>`
                    + `<button type='submit' class='btn btn-sm btn-danger' onclick='return confirm("¿Estás seguro?")'>Desactivar</button>`
                    + `</form>`;
            }
            return { ...lavador, acciones };
        });

        const config = {
            searchable: true,
            searchPlaceholder: 'Buscar lavador...',
            sortable: true,
            perPage: 15,
            data,
            columns: [
                {
                    key: 'nombre',
                    label: 'Nombre',
                    sortable: true,
                    searchable: true
                },
                {
                    key: 'dni',
                    label: 'DNI',
                    sortable: true,
                    searchable: true
                },
                {
                    key: 'telefono',
                    label: 'Teléfono',
                    sortable: true,
                    searchable: true,
                    formatter: (value) => {
                        return value || '<span class="text-muted">-</span>';
                    }
                },
                {
                    key: 'estado',
                    label: 'Estado',
                    sortable: true,
                    searchable: true,
                    formatter: (value) => {
                        const estado = String(value).toLowerCase();
                        const badgeClass = estado === 'activo' ? 'bg-success' : 'bg-secondary';
                        return `<span class='badge ${badgeClass}'>${estado.charAt(0).toUpperCase() + estado.slice(1)}</span>`;
                    }
                },
                {
                    key: 'acciones',
                    label: 'Acciones',
                    sortable: false,
                    searchable: false
                }
            ]
        };

        new DynamicTable(tableElement, config);
    }
}
