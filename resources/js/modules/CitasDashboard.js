/**
 * CitasDashboard — Alpine.js component
 *
 * Maneja el cambio de estado de citas (iniciar / completar / cancelar)
 * sin recarga de página vía fetch AJAX.
 *
 * Registrado como Alpine component: x-data="citasDashboard(cita)"
 * Cada tarjeta de cita recibe su propio estado reactivo.
 */

import axios from 'axios';
import { withCsrfHeader } from '@utils/csrf';

/**
 * Fábrica del componente Alpine para una tarjeta de cita.
 * @param {Object} cita  — datos iniciales { id, estado, urls: { iniciar, completar, cancelar } }
 */
function citasDashboardCard(cita) {
    return {
        estado: cita.estado,
        loading: false,
        removed: false,

        async cambiarEstado(url, nuevoEstado, mensajeConfirm = null) {
            if (mensajeConfirm) {
                const ok = await window.CarWash?.showConfirm?.('¿Confirmar acción?', mensajeConfirm, 'Confirmar');
                if (!ok) return;
            }

            this.loading = true;
            try {
                const res = await axios.post(url, {}, {
                    headers: {
                        ...withCsrfHeader(),
                        'Accept': 'application/json',
                    },
                });

                this.estado = res.data.estado;
                window.CarWash?.showSuccess?.(res.data.message);

                // Pequeño delay para que el usuario vea el badge actualizado antes de reubicar
                await new Promise(r => setTimeout(r, 800));
                this.removed = true;  // dispara x-show="!removed" → la tarjeta desaparece
                // El dashboard puede recargar la sección o simplemente ocultar la tarjeta
            } catch (err) {
                window.CarWash?.showError?.('Error al actualizar el estado. Intenta nuevamente.');
            } finally {
                this.loading = false;
            }
        },
    };
}

// Registrar en Alpine global
const register = () => {
    window.Alpine.data('citasDashboardCard', citasDashboardCard);
};

if (window.Alpine && window._alpineStarted) {
    register();
} else {
    document.addEventListener('alpine:init', register);
}

export { citasDashboardCard };
