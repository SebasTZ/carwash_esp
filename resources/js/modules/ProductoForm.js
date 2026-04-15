/**
 * ProductoForm.js
 * Módulo para manejar la inicialización y lógica de formularios de productos
 * Gestiona: bootstrap-select, show/hide de precio, validación básica
 */

import { refreshBootstrapSelect } from '@utils/bootstrap-init';

export default class ProductoForm {
    constructor({
        elementId,
        marcas = [],
        presentaciones = [],
        categorias = [],
        producto = null,
        old = {},
        errors = {},
        action = '',
        method = 'POST'
    }) {
        this.elementId = elementId;
        this.marcas = marcas;
        this.presentaciones = presentaciones;
        this.categorias = categorias;
        this.producto = producto;
        this.old = old;
        this.errors = errors;
        this.action = action;
        this.method = method;

        this.init();
    }

    /**
     * Inicializar el componente
     */
    init() {
        // Esperar a que exista el contenedor
        setTimeout(() => {
            this.setupBootstrapSelect();
            this.attachEventListeners();
        }, 100);
    }

    /**
     * Inicializar bootstrap-select
     */
    setupBootstrapSelect() {
        refreshBootstrapSelect('select.selectpicker');
    }

    /**
     * Agregar event listeners
     */
    attachEventListeners() {
        const checkbox = document.getElementById('es_servicio_lavado');
        const precioDiv = document.getElementById('precio_servicio_div');
        const precioInput = document.getElementById('precio_venta');

        if (checkbox && precioDiv && precioInput) {
            // Listener al cambiar checkbox
            checkbox.addEventListener('change', (e) => {
                if (e.target.checked) {
                    precioDiv.style.display = 'block';
                } else {
                    precioDiv.style.display = 'none';
                    precioInput.value = '';
                }
            });

            // Inicializar estado en carga de página
            if (checkbox.checked) {
                precioDiv.style.display = 'block';
            } else {
                precioDiv.style.display = 'none';
            }
        }
    }
}
