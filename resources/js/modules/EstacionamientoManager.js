/**
 * @fileoverview EstacionamientoManager - Gestión de actualización en tiempo real del estacionamiento
 * @module EstacionamientoManager
 * @requires axios
 */

import axios from 'axios';
import { showError, showSuccess } from '@utils/notifications';

/**
 * Clase para gestionar el estado del estacionamiento
 * @class EstacionamientoState
 */
class EstacionamientoState {
    constructor() {
        this.vehiculos = [];
        this.isLoading = false;
        this.autoRefreshInterval = null;
        this.autoRefreshEnabled = false;
        this.refreshIntervalMs = 60000; // 1 minuto por defecto
    }
    
    /**
     * Actualizar lista de vehículos
     * @param {Array} vehiculos - Nueva lista de vehículos
     */
    actualizarVehiculos(vehiculos) {
        this.vehiculos = vehiculos;
    }
    
    /**
     * Habilitar/deshabilitar auto-refresh
     * @param {boolean} enabled
     */
    setAutoRefresh(enabled) {
        this.autoRefreshEnabled = enabled;
    }
}

/**
 * Clase principal para gestionar la vista de estacionamiento
 * @class EstacionamientoManager
 */
export class EstacionamientoManager {
    constructor() {
        // Verificar que estamos en la página correcta
        if (!window.location.pathname.includes('/estacionamiento')) {
            return;
        }
        
        this.state = new EstacionamientoState();
        this.init();
    }
    
    /**
     * Inicializar el manager
     */
    init() {
        console.log('🚗 EstacionamientoManager inicializado');
        
        // Setup event listeners
        this.setupEventListeners();
        
        // Iniciar actualización de tiempos cada 30 segundos
        this.iniciarActualizacionTiempos();
        
        // Opcional: Auto-refresh completo cada 5 minutos (comentado por defecto)
        // this.iniciarAutoRefresh(300000); // 5 minutos
    }
    
    /**
     * Configurar event listeners
     */
    setupEventListeners() {
        // Confirmación para registrar salida
        const formsRegistrarSalida = document.querySelectorAll('form[action*="registrar-salida"]');
        formsRegistrarSalida.forEach(form => {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                await this.confirmarRegistrarSalida(form);
            });
        });
        
        // Confirmación para eliminar
        const formsEliminar = document.querySelectorAll('form[method="POST"] button[onclick*="eliminar"]');
        formsEliminar.forEach(button => {
            const form = button.closest('form');
            if (form) {
                form.addEventListener('submit', async (e) => {
                    if (button.contains(e.submitter)) {
                        e.preventDefault();
                        await this.confirmarEliminar(form);
                    }
                });
            }
        });
    }
    
    /**
     * Confirmar registro de salida con cálculo de monto
     */
    async confirmarRegistrarSalida(form) {
        const row = form.closest('tr');
        if (!row) {
            form.submit();
            return;
        }
        
        // Extraer datos de la fila
        const placa = row.querySelector('td:nth-child(1)')?.textContent.trim() || '';
        const tiempoTexto = row.querySelector('td:nth-child(6)')?.textContent.trim() || '';
        const tarifaTexto = row.querySelector('td:nth-child(7)')?.textContent.trim() || '';
        
        // Calcular monto estimado (simplificado - el backend hará el cálculo real)
        const tarifa = parseFloat(tarifaTexto.replace('S/.', '').replace(',', '').trim()) || 0;
        
        const mensaje = `
            <div class="text-start">
                <p><strong>Placa:</strong> ${placa}</p>
                <p><strong>Tiempo estacionado:</strong> ${tiempoTexto}</p>
                <p><strong>Tarifa/hora:</strong> S/. ${tarifa.toFixed(2)}</p>
                <hr>
                <p class="text-muted mb-0">El sistema calculará el monto exacto al registrar la salida.</p>
            </div>
        `;
        
        const confirmado = await this.mostrarConfirmacionHTML(
            '¿Registrar salida del vehículo?',
            mensaje
        );
        
        if (confirmado) {
            form.submit();
        }
    }
    
    /**
     * Confirmar eliminación
     */
    async confirmarEliminar(form) {
        const row = form.closest('tr');
        const placa = row?.querySelector('td:nth-child(1)')?.textContent.trim() || '';
        
        const confirmado = await this.mostrarConfirmacion(
            '¿Eliminar este registro?',
            `Se eliminará el registro del vehículo con placa: <strong>${placa}</strong>`
        );
        
        if (confirmado) {
            form.submit();
        }
    }
    
    /**
     * Actualizar tiempos transcurridos sin recargar página
     */
    iniciarActualizacionTiempos() {
        // Actualizar cada 30 segundos
        this.tiempoInterval = setInterval(() => {
            this.actualizarTiemposEnPagina();
        }, 30000);
        
        console.log('⏱️ Actualización automática de tiempos iniciada (cada 30s)');
    }
    
    /**
     * Actualizar los textos de tiempo en la página actual
     * (sin hacer petición al servidor)
     */
    actualizarTiemposEnPagina() {
        const rows = document.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const horaEntradaCell = row.querySelector('td:nth-child(5)');
            const tiempoCell = row.querySelector('td:nth-child(6)');
            
            if (!horaEntradaCell || !tiempoCell) return;
            
            try {
                // Parsear la fecha de entrada (formato: dd/mm/yyyy HH:mm)
                const horaEntradaTexto = horaEntradaCell.textContent.trim();
                const [fecha, hora] = horaEntradaTexto.split(' ');
                const [dia, mes, año] = fecha.split('/');
                const [horas, minutos] = hora.split(':');
                
                const horaEntrada = new Date(año, mes - 1, dia, horas, minutos);
                const ahora = new Date();
                
                // Calcular diferencia en minutos
                const diffMinutos = Math.floor((ahora - horaEntrada) / 60000);
                
                // Formatear tiempo transcurrido
                const tiempoTexto = this.formatearTiempoTranscurrido(diffMinutos);
                
                // Actualizar solo si cambió
                if (tiempoCell.textContent !== tiempoTexto) {
                    tiempoCell.textContent = tiempoTexto;
                    
                    // Agregar efecto visual sutil
                    tiempoCell.style.transition = 'background-color 0.3s';
                    tiempoCell.style.backgroundColor = '#fff3cd';
                    setTimeout(() => {
                        tiempoCell.style.backgroundColor = '';
                    }, 1000);
                }
            } catch (error) {
                console.warn('Error al actualizar tiempo:', error);
            }
        });
    }
    
    /**
     * Formatear tiempo transcurrido de manera legible
     * @param {number} minutos - Minutos transcurridos
     * @returns {string}
     */
    formatearTiempoTranscurrido(minutos) {
        if (minutos < 1) {
            return 'menos de 1 minuto';
        } else if (minutos < 60) {
            return `${minutos} minuto${minutos !== 1 ? 's' : ''}`;
        } else if (minutos < 1440) { // menos de 24 horas
            const horas = Math.floor(minutos / 60);
            const minutosRestantes = minutos % 60;
            if (minutosRestantes === 0) {
                return `${horas} hora${horas !== 1 ? 's' : ''}`;
            }
            return `${horas} hora${horas !== 1 ? 's' : ''} ${minutosRestantes} minuto${minutosRestantes !== 1 ? 's' : ''}`;
        } else {
            const dias = Math.floor(minutos / 1440);
            const horasRestantes = Math.floor((minutos % 1440) / 60);
            if (horasRestantes === 0) {
                return `${dias} día${dias !== 1 ? 's' : ''}`;
            }
            return `${dias} día${dias !== 1 ? 's' : ''} ${horasRestantes} hora${horasRestantes !== 1 ? 's' : ''}`;
        }
    }
    
    /**
     * Iniciar auto-refresh completo de la tabla (opcional)
     * @param {number} intervalMs - Intervalo en milisegundos
     */
    iniciarAutoRefresh(intervalMs = 300000) {
        this.state.refreshIntervalMs = intervalMs;
        this.state.setAutoRefresh(true);
        
        this.state.autoRefreshInterval = setInterval(async () => {
            if (!this.state.isLoading) {
                await this.refrescarTabla();
            }
        }, intervalMs);
        
        console.log(`🔄 Auto-refresh completo iniciado (cada ${intervalMs / 1000}s)`);
    }
    
    /**
     * Detener auto-refresh
     */
    detenerAutoRefresh() {
        if (this.state.autoRefreshInterval) {
            clearInterval(this.state.autoRefreshInterval);
            this.state.autoRefreshInterval = null;
            this.state.setAutoRefresh(false);
            console.log('⏸️ Auto-refresh detenido');
        }
    }
    
    /**
     * Refrescar tabla completa con AJAX (opcional - requiere backend)
     */
    async refrescarTabla() {
        if (this.state.isLoading) return;
        
        this.state.isLoading = true;
        
        try {
            const response = await axios.get(window.location.pathname, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (response.data.html) {
                // Actualizar tabla con nuevo HTML
                const tabla = document.querySelector('.table-striped');
                if (tabla) {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(response.data.html, 'text/html');
                    const nuevaTabla = doc.querySelector('.table-striped');
                    
                    if (nuevaTabla) {
                        tabla.innerHTML = nuevaTabla.innerHTML;
                        this.setupEventListeners(); // Re-setup listeners
                    }
                }
                
                console.log('✅ Tabla actualizada');
            }
        } catch (error) {
            console.error('Error al refrescar tabla:', error);
            // No mostrar error al usuario para no interrumpir
        } finally {
            this.state.isLoading = false;
        }
    }
    
    /**
     * Mostrar confirmación con SweetAlert2
     */
    async mostrarConfirmacion(titulo, mensaje) {
        const Swal = window.Swal;
        if (!Swal) {
            return confirm(mensaje);
        }
        
        const result = await Swal.fire({
            title: titulo,
            html: mensaje,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, continuar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33'
        });
        
        return result.isConfirmed;
    }
    
    /**
     * Mostrar confirmación con HTML personalizado
     */
    async mostrarConfirmacionHTML(titulo, htmlContenido) {
        const Swal = window.Swal;
        if (!Swal) {
            return confirm(titulo);
        }
        
        const result = await Swal.fire({
            title: titulo,
            html: htmlContenido,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Registrar Salida',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#ffc107',
            cancelButtonColor: '#6c757d',
            width: '500px'
        });
        
        return result.isConfirmed;
    }
    
    /**
     * Cleanup al destruir
     */
    destroy() {
        if (this.tiempoInterval) {
            clearInterval(this.tiempoInterval);
        }
        
        if (this.state.autoRefreshInterval) {
            clearInterval(this.state.autoRefreshInterval);
        }
    }
}

// Auto-inicialización
document.addEventListener('DOMContentLoaded', () => {
    window.estacionamientoManager = new EstacionamientoManager();
});
