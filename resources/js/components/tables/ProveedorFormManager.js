// ProveedorFormManager.js
// Componente JS para gestionar el formulario de proveedores

export const ProveedorFormManager = {
    init({ el, documentos = [], persona = {}, old = {} }) {
        const container = document.querySelector(el);
        if (!container) return;
        container.innerHTML = '';
        // Renderizar campos dinámicos replicando el antiguo create
        let documentosOptions = '';
        if (Array.isArray(documentos) && documentos.length > 0) {
            documentosOptions = documentos.map(d => {
                let selected = '';
                if ((old.documento_id && old.documento_id == d.id) || (!old.documento_id && persona.documento_id == d.id)) {
                    selected = 'selected';
                }
                return `<option value='${d.id}' ${selected}>${d.tipo_documento}</option>`;
            }).join('');
        } else {
            documentosOptions = `<option value="">No hay documentos disponibles</option>`;
        }
        container.innerHTML += `
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="tipo_persona" class="form-label">Tipo de proveedor:</label>
                    <select class="form-select" name="tipo_persona" id="tipo_persona">
                        <option value="" disabled ${!(old.tipo_persona || persona.tipo_persona) ? 'selected' : ''}>Seleccione una opción</option>
                        <option value="natural" ${(old.tipo_persona === 'natural' || (!old.tipo_persona && persona.tipo_persona === 'natural')) ? 'selected' : ''}>Persona natural</option>
                        <option value="juridica" ${(old.tipo_persona === 'juridica' || (!old.tipo_persona && persona.tipo_persona === 'juridica')) ? 'selected' : ''}>Empresa</option>
                    </select>
                </div>
                <div class="col-12" id="box-razon-social" style="display:block;">
                    <label id="label-natural" for="razon_social" class="form-label" style="${(old.tipo_persona === 'natural' || (!old.tipo_persona && persona.tipo_persona === 'natural')) ? 'display:block;' : 'display:none;'}">Nombre completo:</label>
                    <label id="label-juridica" for="razon_social" class="form-label" style="${(old.tipo_persona === 'juridica' || (!old.tipo_persona && persona.tipo_persona === 'juridica')) ? 'display:block;' : 'display:none;'}">Razón social:</label>
                    <input required type="text" name="razon_social" id="razon_social" class="form-control" value="${old.razon_social || persona.razon_social || ''}">
                </div>
                <div class="col-12">
                    <label for="direccion" class="form-label">Dirección:</label>
                    <input required type="text" name="direccion" id="direccion" class="form-control" value="${old.direccion || persona.direccion || ''}">
                </div>
                <div class="col-12">
                    <label for="telefono" class="form-label">Teléfono:</label>
                    <input type="text" name="telefono" id="telefono" class="form-control" value="${old.telefono || persona.telefono || ''}">
                </div>
                <div class="col-md-6">
                    <label for="documento_id" class="form-label">Tipo de documento:</label>
                    <select class="form-select" name="documento_id" id="documento_id">
                        ${documentosOptions}
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="numero_documento" class="form-label">Número de documento:</label>
                    <input required type="text" name="numero_documento" id="numero_documento" class="form-control" value="${old.numero_documento || persona.numero_documento || ''}">
                </div>
            </div>
        `;
        // Lógica de mostrar/ocultar razón social y validaciones como el clásico
        setTimeout(() => {
            const tipoPersona = container.querySelector('#tipo_persona');
            const boxRazon = container.querySelector('#box-razon-social');
            const labelNatural = container.querySelector('#label-natural');
            const labelJuridica = container.querySelector('#label-juridica');
            tipoPersona?.addEventListener('change', function() {
                if (this.value === 'natural') {
                    labelJuridica.style.display = 'none';
                    labelNatural.style.display = 'block';
                } else {
                    labelNatural.style.display = 'none';
                    labelJuridica.style.display = 'block';
                }
                boxRazon.style.display = 'block';
            });
            const documentoId = container.querySelector('#documento_id');
            const numeroDocumento = container.querySelector('#numero_documento');
            documentoId?.addEventListener('change', function() {
                const texto = documentoId.options[documentoId.selectedIndex]?.text;
                if (texto === 'DNI') {
                    numeroDocumento.setAttribute('maxlength', 8);
                    numeroDocumento.setAttribute('minlength', 8);
                } else if (texto === 'RUC') {
                    numeroDocumento.setAttribute('maxlength', 11);
                    numeroDocumento.setAttribute('minlength', 11);
                } else {
                    numeroDocumento.removeAttribute('maxlength');
                    numeroDocumento.removeAttribute('minlength');
                }
            });
        }, 100);
    }
};

window.ProveedorFormManager = ProveedorFormManager;
