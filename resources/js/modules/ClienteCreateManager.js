/**
 * ClienteCreateManager
 * Reemplaza el JS inline de cliente/create.blade.php sin jQuery.
 */
class ClienteCreateManager {
    constructor() {
        this.form = document.getElementById('cliente-create-form');
        this.tipoPersonaSelect = document.getElementById('tipo_persona');
        this.documentoSelect = document.getElementById('documento_id');
        this.numeroDocumentoInput = document.getElementById('numero_documento');
        this.boxRazonSocial = document.getElementById('box-razon-social');
        this.labelNatural = document.getElementById('label-natural');
        this.labelJuridica = document.getElementById('label-juridica');

        if (!this.form || !this.tipoPersonaSelect || !this.documentoSelect || !this.numeroDocumentoInput) {
            return;
        }

        this.bindEvents();
        this.syncInitialState();
    }

    bindEvents() {
        this.tipoPersonaSelect.addEventListener('change', () => this.handleTipoPersonaChange());
        this.documentoSelect.addEventListener('change', () => this.handleDocumentoChange());
    }

    syncInitialState() {
        this.handleTipoPersonaChange();
        this.handleDocumentoChange();
    }

    handleTipoPersonaChange() {
        const tipoPersona = this.tipoPersonaSelect.value;
        const esNatural = tipoPersona === 'natural';
        const esJuridica = tipoPersona === 'juridica';

        if (this.boxRazonSocial) {
            this.boxRazonSocial.style.display = esNatural || esJuridica ? 'block' : 'none';
        }

        if (this.labelNatural) {
            this.labelNatural.style.display = esNatural ? 'inline-block' : 'none';
        }

        if (this.labelJuridica) {
            this.labelJuridica.style.display = esJuridica ? 'inline-block' : 'none';
        }
    }

    handleDocumentoChange() {
        const selectedOption = this.documentoSelect.options[this.documentoSelect.selectedIndex];
        const documentoSeleccionado = (selectedOption?.textContent || '').trim().toUpperCase();

        if (documentoSeleccionado === 'DNI') {
            this.setDocumentLength(8);
            return;
        }

        if (documentoSeleccionado === 'RUC') {
            this.setDocumentLength(11);
            return;
        }

        this.clearDocumentLength();
    }

    setDocumentLength(length) {
        this.numeroDocumentoInput.setAttribute('maxlength', String(length));
        this.numeroDocumentoInput.setAttribute('minlength', String(length));
    }

    clearDocumentLength() {
        this.numeroDocumentoInput.removeAttribute('maxlength');
        this.numeroDocumentoInput.removeAttribute('minlength');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('cliente-create-form')) {
        window.clienteCreateManager = new ClienteCreateManager();
    }
});

export default ClienteCreateManager;
