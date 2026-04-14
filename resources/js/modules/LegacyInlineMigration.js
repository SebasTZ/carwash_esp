const readJsonScript = (id, fallback = null) => {
    const node = document.getElementById(id);
    if (!node) {
        return fallback;
    }

    try {
        return JSON.parse(node.textContent || 'null') ?? fallback;
    } catch (error) {
        console.error(`[LegacyInlineMigration] No se pudo parsear ${id}`, error);
        return fallback;
    }
};

const showValidationError = (message) => {
    if (window.CarWash?.showError) {
        window.CarWash.showError(message);
        return;
    }

    if (window.alert) {
        window.alert(message);
    }
};

const initSessionSuccessToasts = () => {
    const message = readJsonScript('panel-success-message', null) || readJsonScript('session-success-data', null);
    if (!message) {
        return;
    }

    if (window.CarWash?.showSuccess) {
        window.CarWash.showSuccess(message, 1500);
        return;
    }

    if (window.Swal) {
        window.Swal.fire({
            icon: 'success',
            title: message,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 1500,
            timerProgressBar: true,
        });
    }
};

const initCategoriaForms = () => {
    const FormValidator = window.CarWash?.FormValidator;
    if (!FormValidator) {
        return;
    }

    const rules = {
        nombre: {
            required: true,
            minLength: 3,
            maxLength: 100,
            pattern: /^[a-zA-Z\u00C1-\u00FF\s]+$/,
        },
        descripcion: {
            required: false,
            maxLength: 500,
        },
    };

    const messages = {
        nombre: {
            required: 'El nombre de la categoría es obligatorio',
            minLength: 'El nombre debe tener al menos 3 caracteres',
            maxLength: 'El nombre no puede superar 100 caracteres',
            pattern: 'El nombre solo puede contener letras y espacios',
        },
        descripcion: {
            maxLength: 'La descripción no puede superar 500 caracteres',
        },
    };

    const setup = (formId, buttonId, submittingText) => {
        const form = document.getElementById(formId);
        if (!form) {
            return;
        }

        const validator = new FormValidator(`#${formId}`, {
            rules,
            messages,
            validateOnBlur: true,
            validateOnInput: false,
            validateOnSubmit: true,
            scrollToError: true,
            focusOnError: true,
            disableSubmitOnInvalid: false,
            onInvalid: () => {
                showValidationError('Por favor, corrija los errores en el formulario');
            },
        });

        const submitButton = document.getElementById(buttonId);
        let isSubmitting = false;

        form.addEventListener('submit', (event) => {
            if (isSubmitting) {
                event.preventDefault();
                return;
            }

            if (!validator.validate()) {
                event.preventDefault();
                return;
            }

            isSubmitting = true;
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerHTML = submittingText;
            }
        });

        form.addEventListener('reset', () => {
            validator.clearErrors?.();
            isSubmitting = false;
            if (submitButton) {
                submitButton.disabled = false;
            }
        });
    };

    setup('form-categoria', 'btn-submit', '<i class="fas fa-spinner fa-spin"></i> Guardando...');
    setup('form-categoria-edit', 'btn-submit', '<i class="fas fa-spinner fa-spin"></i> Actualizando...');
};

const initCategoriaIndex = () => {
    const tableElement = document.getElementById('categorias-table');
    if (!tableElement || !window.CarWash?.DynamicTable) {
        return;
    }

    const config = readJsonScript('categorias-index-config', null);
    if (!config) {
        return;
    }

    const DynamicTable = window.CarWash.DynamicTable;

    new DynamicTable('#categorias-table', {
        columns: [
            { key: 'caracteristica.nombre', label: 'Nombre' },
            { key: 'caracteristica.descripcion', label: 'Descripción' },
            {
                key: 'caracteristica.estado',
                label: 'Estado',
                formatter: (value) => {
                    if (value === 1) {
                        return '<span class="badge rounded-pill text-bg-success">activo</span>';
                    }

                    return '<span class="badge rounded-pill text-bg-danger">eliminado</span>';
                },
            },
        ],
        data: Array.isArray(config.data) ? config.data : [],
        searchable: true,
        searchPlaceholder: 'Buscar categoría...',
        actions: [
            ...(config.canEdit
                ? [{
                    label: 'Editar',
                    class: 'btn-primary',
                    icon: 'fas fa-edit',
                    callback: (row) => {
                        window.location.href = `/categorias/${row.id}/edit`;
                    },
                }]
                : []),
            ...(config.canDelete
                ? [{
                    label: 'Acción',
                    class: 'btn-secondary',
                    icon: 'fas fa-ellipsis-v',
                    callback: (row) => {
                        const isActive = row?.caracteristica?.estado === 1;
                        window.CarWash.openActionModal({
                            modalId: 'confirmModal',
                            title: 'Mensaje de Confirmación',
                            message: isActive
                                ? '¿Está seguro de que desea eliminar esta categoría?'
                                : '¿Está seguro de que desea restaurar esta categoría?',
                            action: isActive
                                ? `/categorias/${row.id}`
                                : `/categorias/${row.id}/restore`,
                            method: isActive ? 'DELETE' : 'PATCH',
                            confirmText: isActive ? 'Eliminar' : 'Restaurar',
                            confirmClass: isActive ? 'btn btn-danger' : 'btn btn-warning',
                            bodyId: 'confirmModalBody',
                            formId: 'confirmForm',
                            methodInputId: 'confirmMethod',
                            confirmButtonId: 'confirmButton',
                        });
                    },
                }]
                : []),
        ],
    });
};

const initMarcaForms = () => {
    const FormValidator = window.CarWash?.FormValidator;
    const formElement = document.getElementById('marcaForm');

    if (!FormValidator || !formElement) {
        return;
    }

    const validator = new FormValidator('#marcaForm', {
        rules: {
            nombre: { required: true, maxLength: 60 },
            descripcion: { maxLength: 255 },
        },
        messages: {
            nombre: {
                required: 'El nombre es obligatorio',
                maxLength: 'El nombre no puede exceder 60 caracteres',
            },
            descripcion: {
                maxLength: 'La descripción no puede exceder 255 caracteres',
            },
        },
        validateOnSubmit: false,
    });

    formElement.addEventListener('submit', (event) => {
        if (!validator.validate()) {
            event.preventDefault();
        }
    });
};

const initMarcaIndex = () => {
    if (!document.getElementById('marcasTable') || !window.CarWash?.DynamicTable) {
        return;
    }

    const config = readJsonScript('marcas-index-config', null);
    if (!config) {
        return;
    }

    const actions = [];

    if (config.canEdit) {
        actions.push({
            label: 'Editar',
            class: 'btn-outline-primary',
            icon: 'fa-edit',
            callback: (row) => {
                window.location.href = `/marcas/${row.id}/edit`;
            },
        });
    }

    if (config.canDelete) {
        actions.push(
            {
                label: 'Eliminar',
                class: 'btn-outline-danger',
                icon: 'fa-trash-can',
                show: (row) => row?.caracteristica?.estado == 1,
                callback: (row) => {
                    window.CarWash.openActionModal({
                        modalId: 'deleteModal',
                        title: 'Mensaje de Confirmación',
                        message: '¿Estás seguro de que deseas eliminar esta marca?',
                        action: `/marcas/${row.id}`,
                        method: 'DELETE',
                        confirmText: 'Eliminar',
                        confirmClass: 'btn btn-danger',
                        bodyId: 'deleteModalBody',
                        formId: 'deleteForm',
                        confirmButtonId: 'confirmButton',
                    });
                },
            },
            {
                label: 'Restaurar',
                class: 'btn-outline-success',
                icon: 'fa-rotate',
                show: (row) => row?.caracteristica?.estado != 1,
                callback: (row) => {
                    window.CarWash.openActionModal({
                        modalId: 'deleteModal',
                        title: 'Mensaje de Confirmación',
                        message: '¿Estás seguro de que deseas restaurar esta marca?',
                        action: `/marcas/${row.id}`,
                        method: 'DELETE',
                        confirmText: 'Restaurar',
                        confirmClass: 'btn btn-success',
                        bodyId: 'deleteModalBody',
                        formId: 'deleteForm',
                        confirmButtonId: 'confirmButton',
                    });
                },
            },
        );
    }

    new window.CarWash.DynamicTable('#marcasTable', {
        searchable: true,
        searchPlaceholder: 'Buscar marcas...',
        perPage: 15,
        data: Array.isArray(config.data) ? config.data : [],
        columns: [
            { key: 'caracteristica.nombre', label: 'Nombre', searchable: true },
            { key: 'caracteristica.descripcion', label: 'Descripción', searchable: true },
            {
                key: 'caracteristica.estado',
                label: 'Estado',
                formatter: (value) => {
                    if (value === 1 || value === true || value === '1' || value === 'true') {
                        return '<span class="badge rounded-pill text-bg-success">Activo</span>';
                    }

                    return '<span class="badge rounded-pill text-bg-secondary">Inactivo</span>';
                },
            },
        ],
        actions,
    });
};

const initPresentacionForms = () => {
    const FormValidator = window.CarWash?.FormValidator;
    const formElement = document.getElementById('presentacioneForm');

    if (!FormValidator || !formElement) {
        return;
    }

    const validator = new FormValidator('#presentacioneForm', {
        rules: {
            nombre: { required: true, maxLength: 60 },
            descripcion: { maxLength: 255 },
        },
        messages: {
            nombre: {
                required: 'El nombre es obligatorio',
                maxLength: 'El nombre no puede exceder 60 caracteres',
            },
            descripcion: {
                maxLength: 'La descripción no puede exceder 255 caracteres',
            },
        },
        validateOnSubmit: false,
    });

    formElement.addEventListener('submit', (event) => {
        if (!validator.validate()) {
            event.preventDefault();
        }
    });
};

const initPresentacionIndex = () => {
    if (!document.getElementById('presentacionesTable') || !window.CarWash?.DynamicTable) {
        return;
    }

    const config = readJsonScript('presentaciones-index-config', null);
    if (!config) {
        return;
    }

    new window.CarWash.DynamicTable('#presentacionesTable', {
        data: Array.isArray(config.data) ? config.data : [],
        columns: [
            { key: 'caracteristica.nombre', label: 'Nombre', searchable: true },
            { key: 'caracteristica.descripcion', label: 'Descripción', searchable: true },
            {
                key: 'caracteristica.estado',
                label: 'Estado',
                formatter: (value) =>
                    value == 1
                        ? '<span class="badge rounded-pill text-bg-success">activo</span>'
                        : '<span class="badge rounded-pill text-bg-danger">eliminado</span>',
            },
        ],
        actions: [
            ...(config.canEdit
                ? [{
                    label: 'Editar',
                    class: 'btn-outline-primary',
                    icon: 'fa-edit',
                    callback: (row) => {
                        window.location.href = `/presentaciones/${row.id}/edit`;
                    },
                }]
                : []),
            ...(config.canDelete
                ? [
                    {
                        label: 'Eliminar',
                        class: 'btn-outline-danger',
                        icon: 'fa-trash-can',
                        show: (row) => row?.caracteristica?.estado == 1,
                        callback: (row) => {
                            window.CarWash.openActionModal({
                                modalId: 'deleteModal',
                                title: 'Mensaje de Confirmación',
                                message: '¿Está seguro de que desea eliminar esta presentación?',
                                action: `/presentaciones/${row.id}`,
                                method: 'DELETE',
                                confirmText: 'Eliminar',
                                confirmClass: 'btn btn-danger',
                                bodyId: 'deleteModalBody',
                                formId: 'deleteForm',
                                confirmButtonId: 'confirmButton',
                            });
                        },
                    },
                    {
                        label: 'Restaurar',
                        class: 'btn-outline-success',
                        icon: 'fa-rotate',
                        show: (row) => row?.caracteristica?.estado != 1,
                        callback: (row) => {
                            window.CarWash.openActionModal({
                                modalId: 'deleteModal',
                                title: 'Mensaje de Confirmación',
                                message: '¿Está seguro de que desea restaurar esta presentación?',
                                action: `/presentaciones/${row.id}`,
                                method: 'DELETE',
                                confirmText: 'Restaurar',
                                confirmClass: 'btn btn-success',
                                bodyId: 'deleteModalBody',
                                formId: 'deleteForm',
                                confirmButtonId: 'confirmButton',
                            });
                        },
                    },
                ]
                : []),
        ],
        searchable: true,
        searchPlaceholder: 'Buscar presentaciones...',
    });
};

const initUserPages = () => {
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

            if (window.FormValidator) {
                new window.FormValidator('#user-create-form');
            }
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

            if (window.FormValidator) {
                new window.FormValidator('#user-edit-form');
            }
        }
    }
};

const initRolePages = () => {
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
            if (window.FormValidator) {
                new window.FormValidator('#role-create-form');
            }

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
            if (window.FormValidator) {
                new window.FormValidator('#role-edit-form');
            }

            window.RoleFormManager.init({
                el: '#role-edit-form-fields',
                permisos: Array.isArray(config.permisos) ? config.permisos : [],
                role: config.role || {},
                old: config.old || {},
            });
        }
    }
};

const initProveedorPages = () => {
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
            if (window.FormValidator) {
                new window.FormValidator('#proveedor-form');
            }

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
            if (window.FormValidator) {
                new window.FormValidator('#proveedor-edit-form');
            }

            window.ProveedorFormManager.init({
                el: '#proveedor-edit-form-fields',
                documentos: Array.isArray(config.documentos) ? config.documentos : [],
                persona: config.persona || {},
                old: config.old || {},
            });
        }
    }
};

const initVentaPages = () => {
    if (document.getElementById('ventas-dynamic-table') && window.DynamicTable?.init) {
        const config = readJsonScript('ventas-index-config', null);
        if (config) {
            window.DynamicTable.init({
                el: '#ventas-dynamic-table',
                data: Array.isArray(config.data) ? config.data : [],
                columns: [
                    { label: 'Comprobante', field: 'comprobante' },
                    { label: 'Cliente', field: 'cliente' },
                    { label: 'Fecha y Hora', field: 'fecha_hora' },
                    { label: 'Vendedor', field: 'vendedor' },
                    { label: 'Total', field: 'total' },
                    { label: 'Método de Pago', field: 'medio_pago' },
                    { label: 'Servicio de Lavado', field: 'servicio_lavado' },
                    { label: 'Acciones', field: 'acciones' },
                ],
                actions: {
                    show: {
                        label: 'Ver',
                        url: (row) => `/ventas/${row.id}`,
                        can: Boolean(config.canShow),
                    },
                    delete: {
                        label: 'Eliminar',
                        url: (row) => `/ventas/${row.id}`,
                        can: Boolean(config.canDelete),
                    },
                },
                pagination: true,
            });
        }
    }

    if (document.getElementById('ventas-reporte-dynamic-table') && window.DynamicTable?.init) {
        const config = readJsonScript('ventas-reporte-config', null);
        if (!config) {
            return;
        }

        window.DynamicTable.init({
            el: '#ventas-reporte-dynamic-table',
            data: Array.isArray(config.data) ? config.data : [],
            columns: [
                { label: 'Comprobante', field: 'comprobante' },
                { label: 'Cliente', field: 'cliente' },
                { label: 'Fecha y Hora', field: 'fecha_hora' },
                { label: 'Vendedor', field: 'vendedor' },
                { label: 'Total', field: 'total' },
                { label: 'Comentarios', field: 'comentarios' },
                { label: 'Medio de Pago', field: 'medio_pago' },
                { label: 'Efectivo', field: 'efectivo' },
                { label: 'Tarjeta Crédito', field: 'tarjeta_credito' },
                { label: 'Tarjeta Regalo', field: 'tarjeta_regalo_id' },
                { label: 'Lavado Gratis', field: 'lavado_gratis' },
                { label: 'Servicio Lavado', field: 'servicio_lavado' },
                { label: 'Hora Fin de Lavado', field: 'horario_lavado' },
            ],
            pagination: true,
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

const fetchTableData = async (url) => {
    const response = await fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            Accept: 'application/json',
        },
    });

    if (!response.ok) {
        throw new Error(`HTTP ${response.status}`);
    }

    const payload = await response.json();
    if (Array.isArray(payload)) {
        return payload;
    }

    if (Array.isArray(payload?.data)) {
        return payload.data;
    }

    return [];
};

const initCompraPages = async () => {
    const DynamicTable = window.CarWash?.DynamicTable;
    if (!DynamicTable) {
        return;
    }

    const comprasIndexNode = document.getElementById('dynamicTableCompras');
    if (comprasIndexNode) {
        try {
            const data = await fetchTableData('/api/compras');
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
                    { key: 'total', label: 'Total' },
                    { key: 'acciones', label: 'Acciones' },
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
            const data = await fetchTableData(`/api/compras/reporte?tipo=${encodeURIComponent(reportType)}`);
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
                    { key: 'impuesto', label: 'IGV' },
                    { key: 'total', label: 'Total' },
                ],
            });
        } catch (error) {
            console.error('[LegacyInlineMigration] Error cargando reporte de compras', error);
        }
    }
};

const initLavadoresPages = () => {
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
                    <input type='hidden' name='_token' value='${document.querySelector('meta[name="csrf-token"]')?.content || ''}'>
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

const initControlLavadosPartial = () => {
    const tableContainer = document.getElementById('dynamicTableLavados');
    if (!tableContainer || !window.CarWash?.DynamicTable) {
        return;
    }

    const config = readJsonScript('lavados-tabla-config', null);
    if (!config) {
        return;
    }

    new window.CarWash.DynamicTable('#dynamicTableLavados', {
        columns: [
            { key: 'comprobante', label: 'Comprobante' },
            { key: 'cliente', label: 'Cliente' },
            { key: 'lavador_tipo', label: 'Lavador / Tipo Vehículo' },
            { key: 'hora_llegada', label: 'Hora Llegada' },
            { key: 'inicio_lavado', label: 'Inicio Lavado' },
            { key: 'fin_lavado', label: 'Fin Lavado' },
            { key: 'inicio_interior', label: 'Inicio Interior' },
            { key: 'fin_interior', label: 'Fin Interior' },
            { key: 'hora_final', label: 'Hora Final' },
            { key: 'tiempo_total', label: 'Tiempo Total' },
            { key: 'estado', label: 'Estado' },
            { key: 'acciones', label: 'Acciones' },
        ],
        data: Array.isArray(config.data) ? config.data : [],
    });
};

const initPagosComisionesPages = () => {
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

const initTarjetaRegaloCreate = () => {
    const generateButton = document.getElementById('generarCodigoBtn');
    const codeInput = document.getElementById('codigo');

    if (!generateButton || !codeInput) {
        return;
    }

    const generarCodigoSerial = () => {
        const fecha = new Date();
        const anio = fecha.getFullYear();
        const mes = String(fecha.getMonth() + 1).padStart(2, '0');
        const dia = String(fecha.getDate()).padStart(2, '0');
        const aleatorio = String(Math.floor(Math.random() * 100000)).padStart(5, '0');

        return `TRG-${anio}${mes}${dia}-${aleatorio}`;
    };

    generateButton.addEventListener('click', (event) => {
        event.preventDefault();
        codeInput.value = generarCodigoSerial();
        codeInput.focus();
    });
};

const initEstacionamientoCreate = () => {
    const checkbox = document.getElementById('pagado_adelantado');
    const amountWrapper = document.getElementById('monto-div');

    if (!checkbox || !amountWrapper) {
        return;
    }

    const syncVisibility = () => {
        amountWrapper.style.display = checkbox.checked ? 'block' : 'none';
    };

    checkbox.addEventListener('change', syncVisibility);
    syncVisibility();
};

const initVehiculoHelpers = () => {
    const plateInput = document.getElementById('placa');
    if (plateInput) {
        plateInput.addEventListener('input', function onInput() {
            this.value = String(this.value || '').toUpperCase();
        });
    }

    const cocheraState = document.getElementById('estado');
    const cocheraExitDate = document.getElementById('fecha_salida');
    const cocheraTotal = document.getElementById('monto_total');

    if (cocheraState && cocheraExitDate && cocheraTotal) {
        cocheraState.addEventListener('change', () => {
            if (cocheraState.value === 'finalizado' && !cocheraExitDate.value) {
                const now = new Date();
                cocheraExitDate.value = now.toISOString().slice(0, 16);
            }

            cocheraTotal.readOnly = cocheraState.value === 'activo';
        });
    }

    const mantenimientoEntryDate = document.getElementById('fecha_ingreso');
    const mantenimientoEstimatedDate = document.getElementById('fecha_entrega_estimada');

    if (mantenimientoEntryDate && mantenimientoEstimatedDate) {
        mantenimientoEntryDate.addEventListener('change', () => {
            if (mantenimientoEstimatedDate.value) {
                return;
            }

            const base = new Date(`${mantenimientoEntryDate.value}T00:00:00`);
            if (Number.isNaN(base.getTime())) {
                return;
            }

            base.setDate(base.getDate() + 2);
            mantenimientoEstimatedDate.value = base.toISOString().slice(0, 10);
        });
    }

    const mantenimientoState = document.getElementById('estado');
    const maintenanceRealDate = document.getElementById('fecha_entrega_real');

    if (mantenimientoState && maintenanceRealDate) {
        mantenimientoState.addEventListener('change', () => {
            if (mantenimientoState.value === 'entregado' && !maintenanceRealDate.value) {
                const today = new Date();
                maintenanceRealDate.value = today.toISOString().slice(0, 10);
            }
        });
    }
};

const initProductoForms = () => {
    const form = document.getElementById('productoForm') || document.getElementById('productoEditForm');
    const serviceCheckbox = document.getElementById('es_servicio_lavado');
    const servicePriceWrapper = document.getElementById('precio_servicio_div');
    const servicePriceInput = document.getElementById('precio_venta');

    if (!form || !serviceCheckbox || !servicePriceWrapper) {
        return;
    }

    const togglePriceField = () => {
        const isChecked = serviceCheckbox.checked;
        servicePriceWrapper.style.display = isChecked ? 'block' : 'none';
        if (!isChecked && servicePriceInput) {
            servicePriceInput.value = '';
        }
    };

    serviceCheckbox.addEventListener('change', togglePriceField);
    togglePriceField();

    form.addEventListener('submit', (event) => {
        const name = document.getElementById('nombre');
        const brand = document.getElementById('marca_id');
        const presentation = document.getElementById('presentacione_id');

        const validations = [
            {
                input: name,
                valid: Boolean(name?.value?.trim()),
                message: 'El nombre es obligatorio',
            },
            {
                input: brand,
                valid: Boolean(brand?.value),
                message: 'Debe seleccionar una marca',
            },
            {
                input: presentation,
                valid: Boolean(presentation?.value),
                message: 'Debe seleccionar una presentación',
            },
        ];

        if (serviceCheckbox.checked) {
            validations.push({
                input: servicePriceInput,
                valid: Boolean(servicePriceInput?.value),
                message: 'El precio del servicio es obligatorio',
            });
        }

        const invalid = validations.find((item) => !item.valid);
        if (invalid) {
            event.preventDefault();
            invalid.input?.classList.add('is-invalid');
            showValidationError(invalid.message);
            return;
        }

        validations.forEach((item) => {
            item.input?.classList.remove('is-invalid');
        });
    });
};

const initTiposVehiculoPages = () => {
    const DynamicTable = window.CarWash?.DynamicTable;
    const FormValidator = window.CarWash?.FormValidator;

    const tableElement = document.getElementById('tiposVehiculoTable');
    if (tableElement && DynamicTable) {
        const config = readJsonScript('tipos-vehiculo-index-config', null);
        if (config) {
            const actions = [];
            if (config.canEdit) {
                actions.push({
                    label: 'Editar',
                    class: 'btn-info',
                    callback: (row) => {
                        window.location.href = `/tipos_vehiculo/${row.id}/edit`;
                    },
                });
            }

            new DynamicTable(tableElement, {
                searchable: true,
                searchPlaceholder: 'Buscar tipo de vehículo...',
                sortable: true,
                perPage: 15,
                data: Array.isArray(config.data) ? config.data : [],
                columns: [
                    { key: 'nombre', label: 'Nombre', sortable: true, searchable: true },
                    {
                        key: 'comision',
                        label: 'Comisión',
                        sortable: true,
                        searchable: true,
                        formatter: (value) => {
                            const num = parseFloat(value);
                            return Number.isNaN(num) ? value : `S/ ${num.toFixed(2)}`;
                        },
                    },
                    {
                        key: 'estado',
                        label: 'Estado',
                        sortable: true,
                        searchable: true,
                        formatter: (value) => {
                            const estado = String(value || '').toLowerCase();
                            const badgeClass = estado === 'activo' ? 'bg-success' : 'bg-secondary';
                            return `<span class="badge ${badgeClass}">${estado.charAt(0).toUpperCase() + estado.slice(1)}</span>`;
                        },
                    },
                ],
                actions,
            });
        }
    }

    const tipoVehiculoCreateForm = document.getElementById('tipoVehiculoForm');
    if (tipoVehiculoCreateForm && FormValidator) {
        const validator = new FormValidator(tipoVehiculoCreateForm, {
            rules: {
                nombre: { required: true, minLength: 2, maxLength: 100 },
                comision: { required: true, number: true, min: 0, max: 999.99 },
                estado: { required: true },
            },
            messages: {
                nombre: {
                    required: 'El nombre es obligatorio',
                    minLength: 'El nombre debe tener al menos 2 caracteres',
                    maxLength: 'El nombre no puede exceder 100 caracteres',
                },
                comision: {
                    required: 'La comisión es obligatoria',
                    number: 'La comisión debe ser un número válido',
                    min: 'La comisión no puede ser negativa',
                    max: 'La comisión no puede exceder 999.99',
                },
                estado: {
                    required: 'Debe seleccionar un estado',
                },
            },
            validateOnBlur: true,
            validateOnInput: false,
            showErrors: true,
            validateOnSubmit: false,
        });

        tipoVehiculoCreateForm.addEventListener('submit', (event) => {
            if (!validator.validate()) {
                event.preventDefault();
            }
        });
    }

    const tipoVehiculoEditForm = document.getElementById('tipoVehiculoEditForm');
    if (tipoVehiculoEditForm && FormValidator) {
        const validator = new FormValidator(tipoVehiculoEditForm, {
            rules: {
                nombre: { required: true, minLength: 2, maxLength: 100 },
                comision: { required: true, number: true, min: 0, max: 999.99 },
                estado: { required: true },
            },
            messages: {
                nombre: {
                    required: 'El nombre es obligatorio',
                    minLength: 'El nombre debe tener al menos 2 caracteres',
                    maxLength: 'El nombre no puede exceder 100 caracteres',
                },
                comision: {
                    required: 'La comisión es obligatoria',
                    number: 'La comisión debe ser un número válido',
                    min: 'La comisión no puede ser negativa',
                    max: 'La comisión no puede exceder 999.99',
                },
                estado: {
                    required: 'Debe seleccionar un estado',
                },
            },
            validateOnBlur: true,
            validateOnInput: false,
            showErrors: true,
            validateOnSubmit: false,
        });

        tipoVehiculoEditForm.addEventListener('submit', (event) => {
            if (!validator.validate()) {
                event.preventDefault();
            }
        });
    }
};

const initCompraShowPage = () => {
    const container = document.getElementById('compraShowContainer');
    if (!container) {
        return;
    }

    const config = readJsonScript('compra-show-config', null);
    if (!config) {
        return;
    }

    const compra = config.compra || {};
    const productos = Array.isArray(config.productos) ? config.productos : [];

    const formatMoney = (value) => `S/ ${Number(value || 0).toFixed(2)}`;
    const formatDate = (value) => {
        if (!value) {
            return '-';
        }

        const date = new Date(value);
        if (Number.isNaN(date.getTime())) {
            return String(value);
        }

        return date.toLocaleString('es-PE', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    };

    const normalizedItems = productos.map((item) => {
        const pivot = item.pivot || {};
        const cantidad = Number(pivot.cantidad ?? item.cantidad ?? 0);
        const precioCompra = Number(pivot.precio_compra ?? item.precio_compra ?? 0);
        const precioVenta = Number(pivot.precio_venta ?? item.precio_venta ?? item.precio_venta_sugerido ?? 0);
        const subtotal = cantidad * precioCompra;

        return {
            nombre: item.nombre || item.name || 'Producto',
            cantidad,
            precioCompra,
            precioVenta,
            subtotal,
        };
    });

    const suma = normalizedItems.reduce((acc, item) => acc + item.subtotal, 0);
    const impuesto = Number(compra.impuesto ?? compra.igv ?? 0);
    const total = suma + impuesto;

    container.innerHTML = `
        <div class="container-fluid px-4">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-file-invoice-dollar me-2"></i>Detalle de Compra</span>
                    <span class="text-muted">${formatDate(compra.fecha_hora || compra.created_at)}</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Precio Compra</th>
                                    <th>Precio Venta</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${normalizedItems
                                    .map(
                                        (item) => `
                                            <tr>
                                                <td>${item.nombre}</td>
                                                <td>${item.cantidad}</td>
                                                <td>${formatMoney(item.precioCompra)}</td>
                                                <td>${formatMoney(item.precioVenta)}</td>
                                                <td>${formatMoney(item.subtotal)}</td>
                                            </tr>
                                        `,
                                    )
                                    .join('')}
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="4" class="text-end">Suma:</th>
                                    <th>${formatMoney(suma)}</th>
                                </tr>
                                <tr>
                                    <th colspan="4" class="text-end">IGV:</th>
                                    <th>${formatMoney(impuesto)}</th>
                                </tr>
                                <tr>
                                    <th colspan="4" class="text-end">Total:</th>
                                    <th>${formatMoney(total)}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    `;
};

const initProductoShowPage = () => {
    const container = document.getElementById('productoShowContainer');
    if (!container) {
        return;
    }

    const config = readJsonScript('producto-show-config', null);
    if (!config) {
        return;
    }

    const producto = config.producto || {};
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const isActive = Number(producto.estado) === 1;
    const estadoClass = isActive ? 'bg-success' : 'bg-secondary';
    const estadoLabel = isActive ? 'Activo' : 'Inactivo';
    const stock = Number(producto.stock || 0);
    const stockClass = stock <= 0 ? 'bg-danger' : stock <= 10 ? 'bg-warning' : 'bg-success';
    const categorias = Array.isArray(producto.categorias) ? producto.categorias : [];
    const categoriasHtml = categorias.length > 0
        ? categorias.map((categoria) => `<span class="badge bg-info me-1">${categoria.nombre}</span>`).join('')
        : '<span class="text-muted">Sin categorías</span>';

    container.innerHTML = `
        <div class="container-fluid px-4">
            <div class="row g-4">
                <div class="col-md-8">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="mb-0">${producto.nombre || 'Producto'}</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    ${producto.img_path
                                        ? `<img src="${producto.img_path}" alt="${producto.nombre || 'Producto'}" class="img-fluid rounded">`
                                        : '<div class="alert alert-light mb-0">Sin imagen</div>'}
                                </div>
                                <div class="col-md-8">
                                    <table class="table table-sm">
                                        <tbody>
                                            <tr><th>Código:</th><td>${producto.codigo || '-'}</td></tr>
                                            <tr><th>Marca:</th><td>${producto.marca?.nombre || '-'}</td></tr>
                                            <tr><th>Presentación:</th><td>${producto.presentacione?.nombre || '-'}</td></tr>
                                            <tr><th>Estado:</th><td><span class="badge ${estadoClass}">${estadoLabel}</span></td></tr>
                                            <tr><th>Stock:</th><td><span class="badge ${stockClass}">${stock}</span></td></tr>
                                            <tr><th>Precio venta:</th><td>S/ ${Number(producto.precio_venta || 0).toFixed(2)}</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            ${producto.descripcion ? `<div class="mt-3"><strong>Descripción:</strong><p class="mb-0 text-muted">${producto.descripcion}</p></div>` : ''}
                            <div class="mt-3"><strong>Categorías:</strong><div class="mt-1">${categoriasHtml}</div></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-header">Acciones</div>
                        <div class="card-body d-grid gap-2">
                            <a href="/productos/${producto.id}/edit" class="btn btn-primary"><i class="fas fa-edit me-1"></i>Editar</a>
                            <a href="/productos" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i>Volver</a>
                            <form action="/productos/${producto.id}" method="POST" onsubmit="return confirm('¿Está seguro de eliminar este producto?');">
                                <input type="hidden" name="_token" value="${csrfToken}">
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" class="btn btn-danger w-100"><i class="fas fa-trash me-1"></i>Eliminar</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
};

const initProfilePage = () => {
    const form = document.getElementById('profileForm');
    const editButton = document.getElementById('editProfileBtn');

    if (!form || !editButton) {
        return;
    }

    editButton.addEventListener('click', () => {
        ['name', 'email', 'password'].forEach((id) => {
            const input = form.querySelector(`#${id}`);
            if (input) {
                input.removeAttribute('disabled');
            }
        });

        const submit = form.querySelector('input[type="submit"]');
        if (submit) {
            submit.removeAttribute('disabled');
        }
    });
};

const initPanelDashboard = () => {
    const root = document.getElementById('panel-dashboard-root');
    if (!root || !window.PanelDashboard) {
        return;
    }

    const dashboardData = readJsonScript('panel-dashboard-data', null);
    const userPermissions = readJsonScript('panel-user-permissions', null);
    if (!dashboardData || !userPermissions) {
        return;
    }

    window.PanelDashboard.init({
        el: '#panel-dashboard-root',
        data: dashboardData,
        userPermissions,
    });
};

const init = async () => {
    initSessionSuccessToasts();
    initPanelDashboard();
    initCategoriaForms();
    initCategoriaIndex();
    initMarcaForms();
    initMarcaIndex();
    initPresentacionForms();
    initPresentacionIndex();
    initUserPages();
    initRolePages();
    initProveedorPages();
    initVentaPages();
    await initCompraPages();
    initLavadoresPages();
    initControlLavadosPartial();
    initPagosComisionesPages();
    initTarjetaRegaloCreate();
    initEstacionamientoCreate();
    initVehiculoHelpers();
    initProductoForms();
    initTiposVehiculoPages();
    initCompraShowPage();
    initProductoShowPage();
    initProfilePage();
};

document.addEventListener('DOMContentLoaded', () => {
    init().catch((error) => {
        console.error('[LegacyInlineMigration] Error de inicialización', error);
    });
});
