/**
 * Módulo de Notificaciones
 * Maneja toasts, confirmaciones, y estados de carga usando SweetAlert2
 */

/**
 * Muestra un toast de éxito
 * @param {string} message - Mensaje a mostrar
 * @param {number} timer - Duración en ms (default: 5000)
 */
export function showSuccess(message, timer = 5000) {
    Swal.fire({
        icon: 'success',
        title: message,
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: timer,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });
}

/**
 * Muestra un toast de error
 * @param {string} message - Mensaje de error
 * @param {number} timer - Duración en ms (default: 6000)
 */
export function showError(message, timer = 6000) {
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: message,
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: timer,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });
}

/**
 * Muestra un toast de advertencia
 * @param {string} message - Mensaje de advertencia
 * @param {number} timer - Duración en ms (default: 5000)
 */
export function showWarning(message, timer = 5000) {
    Swal.fire({
        icon: 'warning',
        title: 'Advertencia',
        text: message,
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: timer,
        timerProgressBar: true,
    });
}

/**
 * Muestra un modal de información
 * @param {string} message - Mensaje informativo
 */
export function showInfo(message) {
    Swal.fire({
        icon: 'info',
        title: 'Información',
        text: message,
        confirmButtonText: 'Entendido',
        confirmButtonColor: '#3085d6'
    });
}

/**
 * Muestra un modal de confirmación
 * @param {string} title - Título del modal
 * @param {string} text - Texto descriptivo
 * @param {string} confirmText - Texto del botón confirmar (default: 'Sí, continuar')
 * @param {string} cancelText - Texto del botón cancelar (default: 'Cancelar')
 * @returns {Promise<boolean>} - true si se confirmó, false si se canceló
 */
export async function showConfirm(
    title, 
    text, 
    confirmText = 'Sí, continuar',
    cancelText = 'Cancelar'
) {
    const result = await Swal.fire({
        title: title,
        text: text,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: confirmText,
        cancelButtonText: cancelText,
        reverseButtons: true
    });
    
    return result.isConfirmed;
}

/**
 * Muestra un modal de confirmación para eliminar
 * @param {string} itemName - Nombre del item a eliminar
 * @returns {Promise<boolean>}
 */
export async function showDeleteConfirm(itemName = 'este elemento') {
    return await showConfirm(
        '¿Estás seguro?',
        `Esta acción eliminará ${itemName}. No podrás revertir esta acción.`,
        'Sí, eliminar',
        'Cancelar'
    );
}

/**
 * Muestra un estado de carga (loading)
 * @param {string} message - Mensaje durante la carga
 */
export function showLoading(message = 'Procesando...') {
    Swal.fire({
        title: message,
        html: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div>',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
}

/**
 * Cierra el modal de carga
 */
export function hideLoading() {
    Swal.close();
}

/**
 * Muestra un modal con input de texto
 * @param {string} title - Título del modal
 * @param {string} placeholder - Placeholder del input
 * @param {string} inputValue - Valor inicial (opcional)
 * @returns {Promise<string|null>} - Valor ingresado o null si se canceló
 */
export async function showInputModal(title, placeholder = '', inputValue = '') {
    const result = await Swal.fire({
        title: title,
        input: 'text',
        inputPlaceholder: placeholder,
        inputValue: inputValue,
        showCancelButton: true,
        confirmButtonText: 'Aceptar',
        cancelButtonText: 'Cancelar',
        inputValidator: (value) => {
            if (!value) {
                return 'Este campo es requerido';
            }
        }
    });
    
    return result.isConfirmed ? result.value : null;
}

/**
 * Muestra un modal con textarea
 * @param {string} title - Título del modal
 * @param {string} placeholder - Placeholder del textarea
 * @param {string} inputValue - Valor inicial (opcional)
 * @returns {Promise<string|null>}
 */
export async function showTextareaModal(title, placeholder = '', inputValue = '') {
    const result = await Swal.fire({
        title: title,
        input: 'textarea',
        inputPlaceholder: placeholder,
        inputValue: inputValue,
        showCancelButton: true,
        confirmButtonText: 'Aceptar',
        cancelButtonText: 'Cancelar',
        inputAttributes: {
            rows: 5
        }
    });
    
    return result.isConfirmed ? result.value : null;
}

/**
 * Wrapper para compatibilidad con código existente
 * @param {string} message - Mensaje a mostrar
 */
export function showModal(message) {
    showInfo(message);
}

/**
 * Muestra un botón con estado de carga
 * @param {HTMLElement} button - Elemento botón
 * @param {boolean} loading - true para mostrar loading, false para ocultar
 */
export function setButtonLoading(button, loading = true) {
    if (!button) return;
    
    if (loading) {
        button.disabled = true;
        button.dataset.originalText = button.innerHTML;
        button.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Procesando...';
    } else {
        button.disabled = false;
        button.innerHTML = button.dataset.originalText || button.innerHTML;
    }
}

/**
 * Muestra notificación de validación en un campo
 * @param {HTMLElement} field - Campo de formulario
 * @param {string} message - Mensaje de error
 */
export function showFieldError(field, message) {
    if (!field) return;
    
    field.classList.add('is-invalid');
    
    // Buscar o crear elemento de feedback
    let feedback = field.parentElement.querySelector('.invalid-feedback');
    if (!feedback) {
        feedback = document.createElement('div');
        feedback.className = 'invalid-feedback';
        field.parentElement.appendChild(feedback);
    }
    
    feedback.textContent = message;
}

/**
 * Limpia el error de un campo
 * @param {HTMLElement} field - Campo de formulario
 */
export function clearFieldError(field) {
    if (!field) return;
    
    field.classList.remove('is-invalid');
    const feedback = field.parentElement.querySelector('.invalid-feedback');
    if (feedback) {
        feedback.textContent = '';
    }
}

/**
 * Limpia todos los errores de un formulario
 * @param {HTMLElement} form - Elemento formulario
 */
export function clearFormErrors(form) {
    if (!form) return;
    
    const invalidFields = form.querySelectorAll('.is-invalid');
    invalidFields.forEach(field => clearFieldError(field));
}
