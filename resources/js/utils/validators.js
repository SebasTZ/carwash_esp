/**
 * Módulo de Validaciones
 * Validaciones reutilizables para formularios del sistema
 */

/**
 * Valida que un valor no esté vacío
 * @param {any} value - Valor a validar
 * @returns {boolean}
 */
export function isNotEmpty(value) {
    if (value === null || value === undefined) return false;
    if (typeof value === 'string') return value.trim().length > 0;
    if (Array.isArray(value)) return value.length > 0;
    return true;
}

/**
 * Valida que un número sea positivo
 * @param {number|string} value - Valor a validar
 * @returns {boolean}
 */
export function isPositive(value) {
    const num = parseFloat(value);
    return !isNaN(num) && num > 0;
}

/**
 * Valida que un número sea no negativo (>= 0)
 * @param {number|string} value - Valor a validar
 * @returns {boolean}
 */
export function isNonNegative(value) {
    const num = parseFloat(value);
    return !isNaN(num) && num >= 0;
}

/**
 * Valida que un número esté dentro de un rango
 * @param {number|string} value - Valor a validar
 * @param {number} min - Valor mínimo (inclusivo)
 * @param {number} max - Valor máximo (inclusivo)
 * @returns {boolean}
 */
export function isInRange(value, min, max) {
    const num = parseFloat(value);
    return !isNaN(num) && num >= min && num <= max;
}

/**
 * Valida cantidad contra stock disponible
 * @param {number|string} cantidad - Cantidad solicitada
 * @param {number|string} stock - Stock disponible
 * @param {boolean} esServicio - Si es un servicio (sin validar stock)
 * @returns {Object} {valid: boolean, message: string}
 */
export function validateStock(cantidad, stock, esServicio = false) {
    // Los servicios no necesitan validación de stock
    if (esServicio) {
        return { valid: true, message: '' };
    }

    const cant = parseInt(cantidad);
    const stockNum = parseInt(stock);

    if (isNaN(cant) || cant <= 0) {
        return { 
            valid: false, 
            message: 'La cantidad debe ser un número mayor a 0' 
        };
    }

    if (cant > stockNum) {
        return { 
            valid: false, 
            message: `Stock insuficiente. Disponible: ${stockNum}` 
        };
    }

    return { valid: true, message: '' };
}

/**
 * Valida que un precio sea válido
 * @param {number|string} precio - Precio a validar
 * @param {number} minimo - Precio mínimo permitido (default: 0)
 * @returns {Object}
 */
export function validatePrecio(precio, minimo = 0) {
    const precioNum = parseFloat(precio);

    if (isNaN(precioNum)) {
        return { 
            valid: false, 
            message: 'El precio debe ser un número válido' 
        };
    }

    if (precioNum < minimo) {
        return { 
            valid: false, 
            message: `El precio mínimo es S/ ${minimo.toFixed(2)}` 
        };
    }

    return { valid: true, message: '' };
}

/**
 * Valida un descuento
 * @param {number|string} descuento - Descuento a validar
 * @param {number} precioUnitario - Precio unitario del producto
 * @param {number} cantidad - Cantidad de productos
 * @returns {Object}
 */
export function validateDescuento(descuento, precioUnitario, cantidad = 1) {
    const descuentoNum = parseFloat(descuento);
    const precioNum = parseFloat(precioUnitario);
    const cantNum = parseInt(cantidad);

    if (isNaN(descuentoNum)) {
        return { 
            valid: false, 
            message: 'El descuento debe ser un número válido' 
        };
    }

    if (descuentoNum < 0) {
        return { 
            valid: false, 
            message: 'El descuento no puede ser negativo' 
        };
    }

    const subtotal = precioNum * cantNum;
    if (descuentoNum > subtotal) {
        return { 
            valid: false, 
            message: `El descuento no puede superar el subtotal (S/ ${subtotal.toFixed(2)})` 
        };
    }

    return { valid: true, message: '' };
}

/**
 * Valida una fecha
 * @param {string} fecha - Fecha en formato YYYY-MM-DD o Date
 * @returns {Object}
 */
export function validateFecha(fecha) {
    if (!fecha) {
        return { valid: false, message: 'La fecha es requerida' };
    }

    const fechaObj = new Date(fecha);
    
    if (isNaN(fechaObj.getTime())) {
        return { valid: false, message: 'La fecha no es válida' };
    }

    return { valid: true, message: '' };
}

/**
 * Valida que fecha_fin sea mayor a fecha_inicio
 * @param {string} fechaInicio - Fecha inicio
 * @param {string} fechaFin - Fecha fin
 * @returns {Object}
 */
export function validateRangoFechas(fechaInicio, fechaFin) {
    const inicio = new Date(fechaInicio);
    const fin = new Date(fechaFin);

    if (isNaN(inicio.getTime())) {
        return { valid: false, message: 'Fecha de inicio no válida' };
    }

    if (isNaN(fin.getTime())) {
        return { valid: false, message: 'Fecha de fin no válida' };
    }

    if (fin < inicio) {
        return { 
            valid: false, 
            message: 'La fecha de fin debe ser mayor a la fecha de inicio' 
        };
    }

    return { valid: true, message: '' };
}

/**
 * Valida un email
 * @param {string} email - Email a validar
 * @returns {Object}
 */
export function validateEmail(email) {
    if (!email) {
        return { valid: false, message: 'El email es requerido' };
    }

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    
    if (!emailRegex.test(email)) {
        return { valid: false, message: 'Email no válido' };
    }

    return { valid: true, message: '' };
}

/**
 * Valida un RUC (Perú - 11 dígitos)
 * @param {string} ruc - RUC a validar
 * @returns {Object}
 */
export function validateRUC(ruc) {
    if (!ruc) {
        return { valid: false, message: 'El RUC es requerido' };
    }

    const rucClean = ruc.toString().trim();
    
    if (!/^\d{11}$/.test(rucClean)) {
        return { 
            valid: false, 
            message: 'El RUC debe tener 11 dígitos' 
        };
    }

    // Validación básica: debe empezar con 10, 15, 17 o 20
    const prefijo = rucClean.substring(0, 2);
    if (!['10', '15', '17', '20'].includes(prefijo)) {
        return { 
            valid: false, 
            message: 'RUC no válido' 
        };
    }

    return { valid: true, message: '' };
}

/**
 * Valida un DNI (Perú - 8 dígitos)
 * @param {string} dni - DNI a validar
 * @returns {Object}
 */
export function validateDNI(dni) {
    if (!dni) {
        return { valid: false, message: 'El DNI es requerido' };
    }

    const dniClean = dni.toString().trim();
    
    if (!/^\d{8}$/.test(dniClean)) {
        return { 
            valid: false, 
            message: 'El DNI debe tener 8 dígitos' 
        };
    }

    return { valid: true, message: '' };
}

/**
 * Valida una placa vehicular (Perú)
 * @param {string} placa - Placa a validar
 * @returns {Object}
 */
export function validatePlaca(placa) {
    if (!placa) {
        return { valid: false, message: 'La placa es requerida' };
    }

    const placaClean = placa.toString().trim().toUpperCase();
    
    // Formato antiguo: ABC-123 o nuevo: ABC-1234
    const formatoAntiguo = /^[A-Z]{3}-\d{3}$/;
    const formatoNuevo = /^[A-Z]{3}-\d{4}$/;
    
    if (!formatoAntiguo.test(placaClean) && !formatoNuevo.test(placaClean)) {
        return { 
            valid: false, 
            message: 'Formato de placa no válido (Ej: ABC-123 o ABC-1234)' 
        };
    }

    return { valid: true, message: '' };
}

/**
 * Valida un teléfono (Perú - 9 dígitos celular)
 * @param {string} telefono - Teléfono a validar
 * @returns {Object}
 */
export function validateTelefono(telefono) {
    if (!telefono) {
        return { valid: false, message: 'El teléfono es requerido' };
    }

    const telefonoClean = telefono.toString().trim();
    
    // Celular: 9 dígitos empezando con 9
    const celular = /^9\d{8}$/;
    // Fijo: 7 dígitos
    const fijo = /^\d{7}$/;
    
    if (!celular.test(telefonoClean) && !fijo.test(telefonoClean)) {
        return { 
            valid: false, 
            message: 'Teléfono no válido (9 dígitos para celular, 7 para fijo)' 
        };
    }

    return { valid: true, message: '' };
}

/**
 * Valida que una tabla tenga al menos una fila
 * @param {string} tableId - ID de la tabla (sin #)
 * @returns {Object}
 */
export function validateTableNotEmpty(tableId) {
    const table = document.getElementById(tableId);
    
    if (!table) {
        return { 
            valid: false, 
            message: 'Tabla no encontrada' 
        };
    }

    const tbody = table.querySelector('tbody');
    const rows = tbody ? tbody.querySelectorAll('tr').length : 0;

    if (rows === 0) {
        return { 
            valid: false, 
            message: 'Debe agregar al menos un producto' 
        };
    }

    return { valid: true, message: '' };
}

/**
 * Valida un formulario completo
 * @param {HTMLFormElement} form - Formulario a validar
 * @returns {Object} {valid: boolean, errors: Array}
 */
export function validateForm(form) {
    if (!form) {
        return { valid: false, errors: ['Formulario no encontrado'] };
    }

    const errors = [];
    
    // Validar campos requeridos
    const requiredFields = form.querySelectorAll('[required]');
    requiredFields.forEach(field => {
        if (!field.value || field.value.trim() === '') {
            const label = field.labels?.[0]?.textContent || field.name || 'Campo';
            errors.push(`${label} es requerido`);
        }
    });

    // Validar campos con patrón
    const patternFields = form.querySelectorAll('[pattern]');
    patternFields.forEach(field => {
        if (field.value && !new RegExp(field.pattern).test(field.value)) {
            const label = field.labels?.[0]?.textContent || field.name || 'Campo';
            errors.push(`${label} no tiene el formato correcto`);
        }
    });

    // Validar campos numéricos
    const numberFields = form.querySelectorAll('input[type="number"]');
    numberFields.forEach(field => {
        if (field.value) {
            const num = parseFloat(field.value);
            const min = field.min ? parseFloat(field.min) : null;
            const max = field.max ? parseFloat(field.max) : null;

            if (isNaN(num)) {
                const label = field.labels?.[0]?.textContent || field.name || 'Campo';
                errors.push(`${label} debe ser un número válido`);
            } else {
                if (min !== null && num < min) {
                    const label = field.labels?.[0]?.textContent || field.name || 'Campo';
                    errors.push(`${label} debe ser mayor o igual a ${min}`);
                }
                if (max !== null && num > max) {
                    const label = field.labels?.[0]?.textContent || field.name || 'Campo';
                    errors.push(`${label} debe ser menor o igual a ${max}`);
                }
            }
        }
    });

    return {
        valid: errors.length === 0,
        errors: errors
    };
}

/**
 * Sanitiza un string para prevenir XSS
 * @param {string} str - String a sanitizar
 * @returns {string}
 */
export function sanitizeString(str) {
    if (!str) return '';
    
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

/**
 * Valida que un valor sea un número entero
 * @param {any} value - Valor a validar
 * @returns {boolean}
 */
export function isInteger(value) {
    const num = parseInt(value);
    return !isNaN(num) && num === parseFloat(value);
}

/**
 * Valida que un porcentaje esté entre 0 y 100
 * @param {number|string} porcentaje - Porcentaje a validar
 * @returns {Object}
 */
export function validatePorcentaje(porcentaje) {
    const num = parseFloat(porcentaje);

    if (isNaN(num)) {
        return { 
            valid: false, 
            message: 'El porcentaje debe ser un número válido' 
        };
    }

    if (num < 0 || num > 100) {
        return { 
            valid: false, 
            message: 'El porcentaje debe estar entre 0 y 100' 
        };
    }

    return { valid: true, message: '' };
}
