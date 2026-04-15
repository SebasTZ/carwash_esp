import { getCsrfToken } from '@utils/csrf';
import { readJsonScript } from '@utils/json-script';
import FormValidator from '../../components/forms/FormValidator';

export const initUserPages = () => {
    if (document.getElementById('users-dynamic-table') && window.UserTableManager) {
        const config = readJsonScript('users-index-config', null);
        if (config) {
            const rawUsers = Array.isArray(config.users) ? config.users : [];
            const usersData = rawUsers.map((user) => {
                const roleName = Array.isArray(user.roles) && user.roles.length > 0 ? user.roles[0].name : '';
                return {
                    id: user.id,
                    name: user.name,
                    email: user.email,
                    status: user.status_text,
                    role: roleName,
                };
            });

            window.UserTableManager.init({
                el: '#users-dynamic-table',
                users: usersData,
                canEdit: Boolean(config.canEdit),
                canDelete: Boolean(config.canDelete),
            });
        }
    }

    if (document.getElementById('user-create-form-fields') && window.UserFormManager) {
        const config = readJsonScript('user-create-config', null);
        if (config) {
            window.UserFormManager.init({
                el: '#user-create-form-fields',
                roles: Array.isArray(config.roles) ? config.roles : [],
                old: config.old || {},
            });

            new FormValidator('#user-create-form');
        }
    }

    if (document.getElementById('user-edit-form-fields') && window.UserFormManager) {
        const config = readJsonScript('user-edit-config', null);
        if (config) {
            window.UserFormManager.init({
                el: '#user-edit-form-fields',
                user: config.user || {},
                roles: Array.isArray(config.roles) ? config.roles : [],
                old: config.old || {},
            });

            new FormValidator('#user-edit-form');
        }
    }
};

export const initRolePages = () => {
    if (document.getElementById('roles-dynamic-table') && window.RoleTableManager) {
        const config = readJsonScript('roles-index-config', null);
        if (config) {
            window.RoleTableManager.init({
                el: '#roles-dynamic-table',
                roles: Array.isArray(config.roles) ? config.roles : [],
                canEdit: Boolean(config.canEdit),
                canDelete: Boolean(config.canDelete),
            });
        }
    }

    if (document.getElementById('role-create-form-fields') && window.RoleFormManager) {
        const config = readJsonScript('role-create-config', null);
        if (config) {
            new FormValidator('#role-create-form');

            window.RoleFormManager.init({
                el: '#role-create-form-fields',
                permisos: Array.isArray(config.permisos) ? config.permisos : [],
                old: config.old || {},
            });
        }
    }

    if (document.getElementById('role-edit-form-fields') && window.RoleFormManager) {
        const config = readJsonScript('role-edit-config', null);
        if (config) {
            new FormValidator('#role-edit-form');

            window.RoleFormManager.init({
                el: '#role-edit-form-fields',
                permisos: Array.isArray(config.permisos) ? config.permisos : [],
                role: config.role || {},
                old: config.old || {},
            });
        }
    }
};

export const initProveedorPages = () => {
    if (document.getElementById('proveedores-dynamic-table') && window.ProveedorTableManager) {
        const config = readJsonScript('proveedores-index-config', null);
        if (config) {
            window.ProveedorTableManager.init({
                el: '#proveedores-dynamic-table',
                proveedores: Array.isArray(config.proveedores) ? config.proveedores : [],
                canEdit: Boolean(config.canEdit),
                canDelete: Boolean(config.canDelete),
            });
        }
    }

    if (document.getElementById('proveedor-form-fields') && window.ProveedorFormManager) {
        const config = readJsonScript('proveedor-create-config', null);
        if (config) {
            new FormValidator('#proveedor-form');

            window.ProveedorFormManager.init({
                el: '#proveedor-form-fields',
                documentos: Array.isArray(config.documentos) ? config.documentos : [],
                old: config.old || {},
            });
        }
    }

    if (document.getElementById('proveedor-edit-form-fields') && window.ProveedorFormManager) {
        const config = readJsonScript('proveedor-edit-config', null);
        if (config) {
            new FormValidator('#proveedor-edit-form');

            window.ProveedorFormManager.init({
                el: '#proveedor-edit-form-fields',
                documentos: Array.isArray(config.documentos) ? config.documentos : [],
                persona: config.persona || {},
                old: config.old || {},
            });
        }
    }
};

export const initLavadoresPages = () => {
    const tableElement = document.getElementById('lavadoresTable');
    if (!tableElement || !window.CarWash?.DynamicTable) {
        return;
    }

    const config = readJsonScript('lavadores-index-config', null);
    if (!config) {
        return;
    }

    const actions = [];

    if (config.canEdit) {
        actions.push({
            label: 'Editar',
            class: 'btn-info',
            callback: (row) => {
                window.location.href = `/lavadores/${row.id}/edit`;
            },
        });
    }

    if (config.canDelete) {
        actions.push({
            label: 'Desactivar',
            class: 'btn-danger',
            callback: (row) => {
                const form = document.createElement('form');
                form.action = `/lavadores/${row.id}`;
                form.method = 'POST';
                form.style.display = 'none';
                form.innerHTML = `
                    <input type='hidden' name='_token' value='${getCsrfToken()}'>
                    <input type='hidden' name='_method' value='DELETE'>
                `;
                document.body.appendChild(form);

                if (window.confirm('¿Estás seguro?')) {
                    form.submit();
                } else {
                    form.remove();
                }
            },
        });
    }

    new window.CarWash.DynamicTable(tableElement, {
        searchable: true,
        searchPlaceholder: 'Buscar lavador...',
        sortable: true,
        perPage: 15,
        data: Array.isArray(config.data) ? config.data : [],
        columns: [
            { key: 'nombre', label: 'Nombre', sortable: true, searchable: true },
            { key: 'dni', label: 'DNI', sortable: true, searchable: true },
            {
                key: 'telefono',
                label: 'Teléfono',
                sortable: true,
                searchable: true,
                formatter: (value) => value || '<span class="text-muted">-</span>',
            },
            {
                key: 'estado',
                label: 'Estado',
                sortable: true,
                searchable: true,
                formatter: (value) => {
                    const estado = String(value || '').toLowerCase();
                    const badgeClass = estado === 'activo' ? 'bg-success' : 'bg-secondary';
                    return `<span class='badge ${badgeClass}'>${estado.charAt(0).toUpperCase() + estado.slice(1)}</span>`;
                },
            },
        ],
        actions,
    });

    const lavadorCreateForm = document.getElementById('lavadorForm');
    if (lavadorCreateForm && window.CarWash?.LavadorFormManager) {
        new window.CarWash.LavadorFormManager(lavadorCreateForm);
    }

    const lavadorEditForm = document.getElementById('lavadorEditForm');
    if (lavadorEditForm && window.CarWash?.LavadorEditFormManager) {
        new window.CarWash.LavadorEditFormManager(lavadorEditForm);
    }
};

export const initPagosComisionesPages = () => {
    if (document.getElementById('pagosTable') && window.CarWash?.DynamicTable) {
        const config = readJsonScript('pagos-comisiones-index-config', null);
        if (config) {
            new window.CarWash.DynamicTable('#pagosTable', {
                columns: [
                    { key: 'lavador.nombre', label: 'Lavador' },
                    {
                        key: 'monto_pagado',
                        label: 'Monto Pagado',
                        formatter: (value) => `<span class='badge bg-success'>S/ ${parseFloat(value || 0).toFixed(2)}</span>`,
                    },
                    {
                        key: 'desde',
                        label: 'Desde',
                        formatter: (value) => (value ? new Date(value).toLocaleDateString('es-PE') : ''),
                    },
                    {
                        key: 'hasta',
                        label: 'Hasta',
                        formatter: (value) => (value ? new Date(value).toLocaleDateString('es-PE') : ''),
                    },
                    {
                        key: 'fecha_pago',
                        label: 'Fecha de Pago',
                        formatter: (value) => (value ? new Date(value).toLocaleDateString('es-PE') : ''),
                    },
                    {
                        key: 'actions',
                        label: 'Acciones',
                        formatter: (value, row) => (config.canHistorial
                            ? `<a href="/pagos_comisiones/lavador/${row.lavador_id}" class="btn btn-sm btn-info">Historial</a>`
                            : '-'),
                    },
                ],
                data: Array.isArray(config.data) ? config.data : [],
                searchPlaceholder: 'Buscar pagos...',
                emptyMessage: 'No hay pagos registrados',
            });
        }
    }

    if (document.getElementById('pago-comision-form-container') && window.PagoComisionFormManager) {
        const config = readJsonScript('pago-comision-create-config', null);
        if (config) {
            window.PagoComisionFormManager.init(config);
        }
    }

    if (document.getElementById('pago-comision-historial-table-container') && window.PagoComisionHistorialTableManager) {
        const config = readJsonScript('pago-comision-show-config', null);
        if (config) {
            window.PagoComisionHistorialTableManager.init(config);
        }
    }

    if (document.getElementById('pago-comision-reporte-table-container') && window.PagoComisionReporteTableManager) {
        const config = readJsonScript('pago-comision-reporte-config', null);
        if (config) {
            window.PagoComisionReporteTableManager.init(config);
        }
    }
};
