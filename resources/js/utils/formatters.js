/**
 * Módulo de Formateo
 * Utilidades para formatear moneda, fechas, números, etc.
 */

/**
 * Formatea un número como moneda en soles (S/)
 * @param {number|string} valor - Valor a formatear
 * @param {number} decimales - Número de decimales (default: 2)
 * @returns {string}
 */
export function formatCurrency(valor, decimales = 2) {
    const num = parseFloat(valor);
    
    if (isNaN(num)) return 'S/ 0.00';
    
    return new Intl.NumberFormat('es-PE', {
        style: 'currency',
        currency: 'PEN',
        minimumFractionDigits: decimales,
        maximumFractionDigits: decimales
    }).format(num);
}

/**
 * Formatea un número sin símbolo de moneda
 * @param {number|string} valor - Valor a formatear
 * @param {number} decimales - Número de decimales (default: 2)
 * @returns {string}
 */
export function formatNumber(valor, decimales = 2) {
    const num = parseFloat(valor);
    
    if (isNaN(num)) return '0.00';
    
    return new Intl.NumberFormat('es-PE', {
        minimumFractionDigits: decimales,
        maximumFractionDigits: decimales
    }).format(num);
}

/**
 * Formatea una fecha al formato DD/MM/YYYY
 * @param {Date|string} fecha - Fecha a formatear
 * @returns {string}
 */
export function formatDate(fecha) {
    if (!fecha) return '';
    
    const fechaObj = typeof fecha === 'string' ? new Date(fecha) : fecha;
    
    if (isNaN(fechaObj.getTime())) return '';
    
    const dia = String(fechaObj.getDate()).padStart(2, '0');
    const mes = String(fechaObj.getMonth() + 1).padStart(2, '0');
    const anio = fechaObj.getFullYear();
    
    return `${dia}/${mes}/${anio}`;
}

/**
 * Formatea una fecha al formato DD/MM/YYYY HH:mm
 * @param {Date|string} fecha - Fecha a formatear
 * @returns {string}
 */
export function formatDateTime(fecha) {
    if (!fecha) return '';
    
    const fechaObj = typeof fecha === 'string' ? new Date(fecha) : fecha;
    
    if (isNaN(fechaObj.getTime())) return '';
    
    const fechaParte = formatDate(fechaObj);
    const horas = String(fechaObj.getHours()).padStart(2, '0');
    const minutos = String(fechaObj.getMinutes()).padStart(2, '0');
    
    return `${fechaParte} ${horas}:${minutos}`;
}

/**
 * Formatea una fecha al formato YYYY-MM-DD (para inputs type="date")
 * @param {Date|string} fecha - Fecha a formatear
 * @returns {string}
 */
export function formatDateInput(fecha) {
    if (!fecha) return '';
    
    const fechaObj = typeof fecha === 'string' ? new Date(fecha) : fecha;
    
    if (isNaN(fechaObj.getTime())) return '';
    
    const anio = fechaObj.getFullYear();
    const mes = String(fechaObj.getMonth() + 1).padStart(2, '0');
    const dia = String(fechaObj.getDate()).padStart(2, '0');
    
    return `${anio}-${mes}-${dia}`;
}

/**
 * Formatea una fecha de manera relativa (hace 2 horas, ayer, etc.)
 * @param {Date|string} fecha - Fecha a formatear
 * @returns {string}
 */
export function formatRelativeTime(fecha) {
    if (!fecha) return '';
    
    const fechaObj = typeof fecha === 'string' ? new Date(fecha) : fecha;
    
    if (isNaN(fechaObj.getTime())) return '';
    
    const ahora = new Date();
    const diffMs = ahora - fechaObj;
    const diffSecs = Math.floor(diffMs / 1000);
    const diffMins = Math.floor(diffSecs / 60);
    const diffHours = Math.floor(diffMins / 60);
    const diffDays = Math.floor(diffHours / 24);
    
    if (diffSecs < 60) return 'Hace unos segundos';
    if (diffMins < 60) return `Hace ${diffMins} minuto${diffMins > 1 ? 's' : ''}`;
    if (diffHours < 24) return `Hace ${diffHours} hora${diffHours > 1 ? 's' : ''}`;
    if (diffDays < 7) return `Hace ${diffDays} día${diffDays > 1 ? 's' : ''}`;
    
    return formatDate(fechaObj);
}

/**
 * Formatea un porcentaje
 * @param {number|string} valor - Valor a formatear
 * @param {number} decimales - Número de decimales (default: 2)
 * @returns {string}
 */
export function formatPercentage(valor, decimales = 2) {
    const num = parseFloat(valor);
    
    if (isNaN(num)) return '0%';
    
    return `${num.toFixed(decimales)}%`;
}

/**
 * Formatea un RUC con guiones
 * @param {string} ruc - RUC a formatear
 * @returns {string}
 */
export function formatRUC(ruc) {
    if (!ruc) return '';
    
    const rucClean = ruc.toString().replace(/\D/g, '');
    
    if (rucClean.length !== 11) return ruc;
    
    // Formato: 20-12345678-9
    return `${rucClean.substring(0, 2)}-${rucClean.substring(2, 10)}-${rucClean.substring(10)}`;
}

/**
 * Formatea un teléfono
 * @param {string} telefono - Teléfono a formatear
 * @returns {string}
 */
export function formatTelefono(telefono) {
    if (!telefono) return '';
    
    const telClean = telefono.toString().replace(/\D/g, '');
    
    // Celular: 999 999 999
    if (telClean.length === 9) {
        return `${telClean.substring(0, 3)} ${telClean.substring(3, 6)} ${telClean.substring(6)}`;
    }
    
    // Fijo: 123 4567
    if (telClean.length === 7) {
        return `${telClean.substring(0, 3)} ${telClean.substring(3)}`;
    }
    
    return telefono;
}

/**
 * Capitaliza la primera letra de cada palabra
 * @param {string} str - String a formatear
 * @returns {string}
 */
export function capitalize(str) {
    if (!str) return '';
    
    return str
        .toLowerCase()
        .split(' ')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
}

/**
 * Formatea un tamaño de archivo en bytes a formato legible
 * @param {number} bytes - Tamaño en bytes
 * @param {number} decimales - Número de decimales (default: 2)
 * @returns {string}
 */
export function formatFileSize(bytes, decimales = 2) {
    if (bytes === 0) return '0 Bytes';
    
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return parseFloat((bytes / Math.pow(k, i)).toFixed(decimales)) + ' ' + sizes[i];
}

/**
 * Trunca un texto a una longitud específica
 * @param {string} text - Texto a truncar
 * @param {number} length - Longitud máxima
 * @param {string} suffix - Sufijo a agregar (default: '...')
 * @returns {string}
 */
export function truncateText(text, length, suffix = '...') {
    if (!text) return '';
    
    if (text.length <= length) return text;
    
    return text.substring(0, length) + suffix;
}

/**
 * Formatea un número de documento (DNI o RUC)
 * @param {string} documento - Documento a formatear
 * @returns {string}
 */
export function formatDocumento(documento) {
    if (!documento) return '';
    
    const docClean = documento.toString().replace(/\D/g, '');
    
    if (docClean.length === 8) {
        // DNI: 12345678
        return docClean;
    }
    
    if (docClean.length === 11) {
        // RUC con formato
        return formatRUC(docClean);
    }
    
    return documento;
}

/**
 * Formatea una placa vehicular
 * @param {string} placa - Placa a formatear
 * @returns {string}
 */
export function formatPlaca(placa) {
    if (!placa) return '';
    
    const placaClean = placa.toString().toUpperCase().replace(/[^A-Z0-9]/g, '');
    
    // ABC123 -> ABC-123
    if (placaClean.length === 6) {
        return `${placaClean.substring(0, 3)}-${placaClean.substring(3)}`;
    }
    
    // ABC1234 -> ABC-1234
    if (placaClean.length === 7) {
        return `${placaClean.substring(0, 3)}-${placaClean.substring(3)}`;
    }
    
    return placa;
}

/**
 * Formatea un número con separador de miles
 * @param {number|string} valor - Valor a formatear
 * @returns {string}
 */
export function formatThousands(valor) {
    const num = parseFloat(valor);
    
    if (isNaN(num)) return '0';
    
    return new Intl.NumberFormat('es-PE').format(num);
}

/**
 * Convierte un número a palabras (para montos en comprobantes)
 * @param {number} num - Número a convertir
 * @returns {string}
 */
export function numberToWords(num) {
    const unidades = ['', 'UNO', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE'];
    const decenas = ['', '', 'VEINTE', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'];
    const especiales = ['DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 'DIECISÉIS', 'DIECISIETE', 'DIECIOCHO', 'DIECINUEVE'];
    
    if (num === 0) return 'CERO';
    
    let entero = Math.floor(num);
    const decimal = Math.round((num - entero) * 100);
    
    let palabras = '';
    
    // Millones
    if (entero >= 1000000) {
        const millones = Math.floor(entero / 1000000);
        palabras += (millones === 1 ? 'UN MILLÓN ' : numberToWords(millones) + ' MILLONES ');
        entero %= 1000000;
    }
    
    // Miles
    if (entero >= 1000) {
        const miles = Math.floor(entero / 1000);
        palabras += (miles === 1 ? 'MIL ' : numberToWords(miles) + ' MIL ');
        entero %= 1000;
    }
    
    // Centenas
    if (entero >= 100) {
        const centenas = Math.floor(entero / 100);
        if (centenas === 1 && entero % 100 === 0) {
            palabras += 'CIEN ';
        } else {
            const centenasTexto = ['', 'CIENTO', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINIENTOS', 'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS'];
            palabras += centenasTexto[centenas] + ' ';
        }
        entero %= 100;
    }
    
    // Decenas y unidades
    if (entero >= 20) {
        palabras += decenas[Math.floor(entero / 10)];
        if (entero % 10 > 0) {
            palabras += ' Y ' + unidades[entero % 10];
        }
    } else if (entero >= 10) {
        palabras += especiales[entero - 10];
    } else if (entero > 0) {
        palabras += unidades[entero];
    }
    
    palabras = palabras.trim();
    
    if (decimal > 0) {
        return `${palabras} CON ${decimal}/100 SOLES`;
    }
    
    return `${palabras} SOLES`;
}

/**
 * Formatea un tiempo en segundos a formato HH:MM:SS
 * @param {number} segundos - Segundos totales
 * @returns {string}
 */
export function formatDuration(segundos) {
    const horas = Math.floor(segundos / 3600);
    const minutos = Math.floor((segundos % 3600) / 60);
    const segs = segundos % 60;
    
    return [horas, minutos, segs]
        .map(v => String(v).padStart(2, '0'))
        .join(':');
}

/**
 * Parsea un string de moneda a número
 * @param {string} currencyString - String con formato de moneda
 * @returns {number}
 */
export function parseCurrency(currencyString) {
    if (!currencyString) return 0;
    
    // Remover símbolo de moneda y espacios
    const cleaned = currencyString
        .toString()
        .replace(/[S/.$\s]/g, '')
        .replace(',', '.');
    
    return parseFloat(cleaned) || 0;
}
