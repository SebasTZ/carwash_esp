/**
 * @fileoverview LavadosManager - Gestión de filtros AJAX para control de lavados
 * @module LavadosManager
 * @requires axios
 * @requires bootstrap
 */

import axios from 'axios';
import { showError, showSuccess } from '@utils/notifications';

/**
 * Clase para gestionar el estado de los filtros de lavados
 * @class LavadosState
 */
class LavadosState {
    constructor() {
        this.filtros = {
            lavador_id: '',
            estado: '',
            fecha: '',
            page: 1
        };
        
        this.lavados = [];
        this.pagination = null;
        this.isLoading = false;
    }
    
    /**
     * Actualizar filtros
     * @param {Object} nuevosFiltros - Nuevos valores de filtros
     */
    actualizarFiltros(nuevosFiltros) {
        this.filtros = { ...this.filtros, ...nuevosFiltros };
        // Reset a página 1 cuando cambian los filtros (excepto si es cambio de página)
        if (!nuevosFiltros.hasOwnProperty('page')) {
            this.filtros.page = 1;
        }
    }
    
    /**
     * Obtener parámetros de URL para los filtros
     * @returns {URLSearchParams}
     */
    obtenerParametrosURL() {
        const params = new URLSearchParams();
        
        Object.keys(this.filtros).forEach(key => {
            if (this.filtros[key] !== '' && this.filtros[key] !== null) {
                params.append(key, this.filtros[key]);
            }
        });
        
        return params;
    }
    
    /**
     * Cargar filtros desde URL actual
     */
    cargarFiltrosDesdeURL() {
        const urlParams = new URLSearchParams(window.location.search);
        
        this.filtros.lavador_id = urlParams.get('lavador_id') || '';
        this.filtros.estado = urlParams.get('estado') || '';
        this.filtros.fecha = urlParams.get('fecha') || '';
        this.filtros.page = parseInt(urlParams.get('page')) || 1;
    }
    
    /**
     * Guardar estado actual en el historial del navegador
     */
    actualizarHistorial() {
        const params = this.obtenerParametrosURL();
        const newURL = `${window.location.pathname}?${params.toString()}`;
        window.history.pushState({ filtros: this.filtros }, '', newURL);
    }
}

/**
 * Clase principal para gestionar la vista de control de lavados
 * @class LavadosManager
 */
export class LavadosManager {
    constructor() {
        // Verificar que estamos en la página correcta
        if (!window.location.pathname.includes('/control/lavados')) {
            return;
        }
        
        this.state = new LavadosState();
        this.init();
    }
    
    /**
     * Inicializar el manager
     */
    init() {
        console.log('🚿 LavadosManager inicializado');
        
        // Cargar filtros de la URL
        this.state.cargarFiltrosDesdeURL();
        
        // Aplicar valores iniciales a los campos
        this.aplicarFiltrosIniciales();
        
        // Setup event listeners
        this.setupEventListeners();
        
        // Inicializar tooltips de Bootstrap
        this.initTooltips();
    }
    
    /**
     * Aplicar valores iniciales a los campos de filtro
     */
    aplicarFiltrosIniciales() {
        const lavadorSelect = document.getElementById('filtro_lavador');
        const estadoSelect = document.getElementById('filtro_estado');
        const fechaInput = document.getElementById('fecha');
        
        if (lavadorSelect && this.state.filtros.lavador_id) {
            lavadorSelect.value = this.state.filtros.lavador_id;
            // Actualizar Bootstrap Select si está inicializado
            if ($(lavadorSelect).data('selectpicker')) {
                $(lavadorSelect).selectpicker('val', this.state.filtros.lavador_id);
            }
        }
        
        if (estadoSelect && this.state.filtros.estado) {
            estadoSelect.value = this.state.filtros.estado;
        }
        
        if (fechaInput && this.state.filtros.fecha) {
            fechaInput.value = this.state.filtros.fecha;
        }
    }
    
    /**
     * Configurar event listeners
     */
    setupEventListeners() {
        // Formulario de filtros
        const formFiltros = document.querySelector('.filter-section form');
        if (formFiltros) {
            formFiltros.addEventListener('submit', (e) => {
                e.preventDefault();
                this.aplicarFiltros();
            });
        }
        
        // Cambios en filtros (aplicar automáticamente)
        const lavadorSelect = document.getElementById('filtro_lavador');
        const estadoSelect = document.getElementById('filtro_estado');
        const fechaInput = document.getElementById('fecha');
        
        if (lavadorSelect) {
            lavadorSelect.addEventListener('change', () => this.aplicarFiltros());
        }
        
        if (estadoSelect) {
            estadoSelect.addEventListener('change', () => this.aplicarFiltros());
        }
        
        if (fechaInput) {
            fechaInput.addEventListener('change', () => this.aplicarFiltros());
        }
        
        // Navegación del historial (botón atrás/adelante)
        window.addEventListener('popstate', (e) => {
            if (e.state && e.state.filtros) {
                this.state.filtros = e.state.filtros;
                this.aplicarFiltrosIniciales();
                this.cargarLavados();
            }
        });
        
        // Links de paginación
        this.setupPaginationListeners();
    }
    
    /**
     * Configurar listeners para paginación
     */
    setupPaginationListeners() {
        document.addEventListener('click', (e) => {
            const paginationLink = e.target.closest('.pagination a');
            
            if (paginationLink && !paginationLink.classList.contains('disabled')) {
                e.preventDefault();
                
                const url = new URL(paginationLink.href);
                const page = url.searchParams.get('page');
                
                if (page) {
                    this.state.actualizarFiltros({ page: parseInt(page) });
                    this.cargarLavados();
                }
            }
        });
    }
    
    /**
     * Aplicar filtros y recargar datos
     */
    async aplicarFiltros() {
        const lavadorSelect = document.getElementById('filtro_lavador');
        const estadoSelect = document.getElementById('filtro_estado');
        const fechaInput = document.getElementById('fecha');
        
        this.state.actualizarFiltros({
            lavador_id: lavadorSelect ? lavadorSelect.value : '',
            estado: estadoSelect ? estadoSelect.value : '',
            fecha: fechaInput ? fechaInput.value : ''
        });
        
        await this.cargarLavados();
    }
    
    /**
     * Cargar lavados desde el servidor con AJAX
     */
    async cargarLavados() {
        if (this.state.isLoading) return;
        
        this.state.isLoading = true;
        this.mostrarCargando(true);
        
        try {
            const params = this.state.obtenerParametrosURL();
            const response = await axios.get(`${window.location.pathname}?${params.toString()}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            // Actualizar historial del navegador
            this.state.actualizarHistorial();
            
            // Si el servidor retorna HTML, reemplazar la tabla
            if (typeof response.data === 'string') {
                this.actualizarTabla(response.data);
            } else if (response.data.html) {
                // Si el servidor retorna JSON con HTML
                this.actualizarTabla(response.data.html);
            }
            
            // Re-inicializar listeners de paginación
            this.setupPaginationListeners();
            
            // Re-inicializar tooltips
            this.initTooltips();
            
        } catch (error) {
            console.error('Error al cargar lavados:', error);
            showError('Error al cargar los datos. Recargando página...');
            
            // Fallback: recargar página completa
            setTimeout(() => {
                window.location.href = `${window.location.pathname}?${this.state.obtenerParametrosURL().toString()}`;
            }, 1500);
        } finally {
            this.state.isLoading = false;
            this.mostrarCargando(false);
        }
    }
    
    /**
     * Actualizar tabla con nuevo HTML
     * @param {string} html - HTML de la tabla
     */
    actualizarTabla(html) {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        
        // Extraer tabla del HTML recibido
        const nuevaTabla = doc.querySelector('.table-responsive');
        const tablasActual = document.querySelector('.control-card .table-responsive');
        
        if (nuevaTabla && tablasActual) {
            tablasActual.innerHTML = nuevaTabla.innerHTML;
        }
        
        // Extraer paginación
        const nuevaPaginacion = doc.querySelector('.pagination');
        const paginacionActual = document.querySelector('.pagination');
        
        if (nuevaPaginacion && paginacionActual) {
            paginacionActual.parentElement.innerHTML = nuevaPaginacion.parentElement.innerHTML;
        }
        
        // Extraer alertas si existen
        const alertas = doc.querySelectorAll('.alert');
        const cardBody = document.querySelector('.control-card .card-body');
        
        if (alertas.length > 0 && cardBody) {
            // Remover alertas anteriores
            const alertasViejas = cardBody.querySelectorAll('.alert');
            alertasViejas.forEach(alerta => alerta.remove());
            
            // Insertar nuevas alertas al inicio
            alertas.forEach(alerta => {
                cardBody.insertBefore(alerta, cardBody.firstChild);
            });
        }
    }
    
    /**
     * Mostrar/ocultar indicador de carga
     * @param {boolean} mostrar
     */
    mostrarCargando(mostrar) {
        const tabla = document.querySelector('.table-responsive');
        
        if (!tabla) return;
        
        if (mostrar) {
            tabla.style.opacity = '0.5';
            tabla.style.pointerEvents = 'none';
            
            // Agregar spinner si no existe
            if (!document.querySelector('.loading-spinner')) {
                const spinner = document.createElement('div');
                spinner.className = 'loading-spinner text-center my-4';
                spinner.innerHTML = `
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2 text-muted">Actualizando datos...</p>
                `;
                tabla.parentElement.insertBefore(spinner, tabla);
            }
        } else {
            tabla.style.opacity = '1';
            tabla.style.pointerEvents = 'auto';
            
            // Remover spinner
            const spinner = document.querySelector('.loading-spinner');
            if (spinner) {
                spinner.remove();
            }
        }
    }
    
    /**
     * Inicializar tooltips de Bootstrap
     */
    initTooltips() {
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltipTriggerList.forEach(tooltipTriggerEl => {
            // Destruir tooltip anterior si existe
            const existingTooltip = bootstrap.Tooltip.getInstance(tooltipTriggerEl);
            if (existingTooltip) {
                existingTooltip.dispose();
            }
            
            // Crear nuevo tooltip
            new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
}

// Auto-inicialización
document.addEventListener('DOMContentLoaded', () => {
    window.lavadosManager = new LavadosManager();
});
