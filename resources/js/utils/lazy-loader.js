/**
 * Módulo de Lazy Loading
 * Utilidades para carga diferida de imágenes y componentes pesados
 */

/**
 * Inicializa lazy loading de imágenes usando Intersection Observer
 * @param {string} selector - Selector CSS para imágenes (default: 'img[data-src]')
 * @param {Object} options - Opciones para Intersection Observer
 */
export function initLazyImages(selector = 'img[data-src]', options = {}) {
    if (!('IntersectionObserver' in window)) {
        // Fallback para navegadores antiguos
        loadAllImagesImmediately(selector);
        return;
    }

    const defaultOptions = {
        root: null,
        rootMargin: '50px',
        threshold: 0.01
    };

    const finalOptions = { ...defaultOptions, ...options };

    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                loadImage(img);
                observer.unobserve(img);
            }
        });
    }, finalOptions);

    const images = document.querySelectorAll(selector);
    images.forEach(img => imageObserver.observe(img));

    return imageObserver;
}

/**
 * Carga una imagen individual
 * @param {HTMLImageElement} img - Elemento imagen
 */
export function loadImage(img) {
    const src = img.getAttribute('data-src');
    const srcset = img.getAttribute('data-srcset');

    if (!src) return;

    img.src = src;

    if (srcset) {
        img.srcset = srcset;
    }

    img.classList.add('loaded');
    img.removeAttribute('data-src');
    img.removeAttribute('data-srcset');

    // Emitir evento personalizado
    img.dispatchEvent(new CustomEvent('imageLoaded', { bubbles: true }));
}

/**
 * Carga todas las imágenes inmediatamente (fallback)
 * @param {string} selector - Selector CSS para imágenes
 */
function loadAllImagesImmediately(selector) {
    const images = document.querySelectorAll(selector);
    images.forEach(img => loadImage(img));
}

/**
 * Lazy loading para iframes (videos de YouTube, mapas, etc.)
 * @param {string} selector - Selector CSS para iframes (default: 'iframe[data-src]')
 */
export function initLazyIframes(selector = 'iframe[data-src]') {
    if (!('IntersectionObserver' in window)) {
        loadAllIframesImmediately(selector);
        return;
    }

    const iframeObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const iframe = entry.target;
                const src = iframe.getAttribute('data-src');

                if (src) {
                    iframe.src = src;
                    iframe.classList.add('loaded');
                    iframe.removeAttribute('data-src');
                }

                observer.unobserve(iframe);
            }
        });
    }, {
        rootMargin: '100px'
    });

    const iframes = document.querySelectorAll(selector);
    iframes.forEach(iframe => iframeObserver.observe(iframe));

    return iframeObserver;
}

/**
 * Carga todos los iframes inmediatamente (fallback)
 * @param {string} selector - Selector CSS para iframes
 */
function loadAllIframesImmediately(selector) {
    const iframes = document.querySelectorAll(selector);
    iframes.forEach(iframe => {
        const src = iframe.getAttribute('data-src');
        if (src) {
            iframe.src = src;
            iframe.removeAttribute('data-src');
        }
    });
}

/**
 * Carga diferida de módulos JavaScript
 * @param {Function} importFn - Función que retorna import() dinámico
 * @param {string} triggerSelector - Selector del elemento que dispara la carga
 * @param {string} event - Evento que dispara la carga (default: 'click')
 * @returns {Promise}
 */
export function lazyLoadModule(importFn, triggerSelector, event = 'click') {
    return new Promise((resolve, reject) => {
        const trigger = document.querySelector(triggerSelector);

        if (!trigger) {
            reject(new Error(`Elemento ${triggerSelector} no encontrado`));
            return;
        }

        const loadModule = async () => {
            try {
                const module = await importFn();
                resolve(module);
            } catch (error) {
                reject(error);
            }
        };

        trigger.addEventListener(event, loadModule, { once: true });
    });
}

/**
 * Precarga de recursos críticos
 * @param {Array} resources - Array de objetos {href, as, type}
 */
export function preloadResources(resources = []) {
    resources.forEach(resource => {
        const link = document.createElement('link');
        link.rel = 'preload';
        link.href = resource.href;
        link.as = resource.as || 'script';

        if (resource.type) {
            link.type = resource.type;
        }

        if (resource.crossorigin) {
            link.crossOrigin = resource.crossorigin;
        }

        document.head.appendChild(link);
    });
}

/**
 * Carga diferida de CSS
 * @param {string} href - URL del archivo CSS
 * @param {string} media - Media query (default: 'all')
 * @returns {Promise}
 */
export function lazyLoadCSS(href, media = 'all') {
    return new Promise((resolve, reject) => {
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = href;
        link.media = 'print'; // Carga sin bloquear

        link.onload = () => {
            link.media = media;
            resolve();
        };

        link.onerror = () => {
            reject(new Error(`Error cargando CSS: ${href}`));
        };

        document.head.appendChild(link);
    });
}

/**
 * Carga diferida de JavaScript
 * @param {string} src - URL del archivo JS
 * @param {Object} options - Opciones (async, defer, etc.)
 * @returns {Promise}
 */
export function lazyLoadScript(src, options = {}) {
    return new Promise((resolve, reject) => {
        // Verificar si ya está cargado
        const existingScript = document.querySelector(`script[src="${src}"]`);
        if (existingScript) {
            resolve();
            return;
        }

        const script = document.createElement('script');
        script.src = src;

        if (options.async) script.async = true;
        if (options.defer) script.defer = true;
        if (options.type) script.type = options.type;
        if (options.crossorigin) script.crossOrigin = options.crossorigin;

        script.onload = () => resolve();
        script.onerror = () => reject(new Error(`Error cargando script: ${src}`));

        document.body.appendChild(script);
    });
}

/**
 * Carga componentes al hacer scroll (infinite scroll)
 * @param {Function} loadMoreFn - Función que carga más contenido
 * @param {string} triggerSelector - Selector del elemento trigger
 * @param {Object} options - Opciones para Intersection Observer
 */
export function initInfiniteScroll(loadMoreFn, triggerSelector, options = {}) {
    if (!('IntersectionObserver' in window)) {
        console.warn('IntersectionObserver no soportado');
        return;
    }

    const trigger = document.querySelector(triggerSelector);
    if (!trigger) return;

    const defaultOptions = {
        root: null,
        rootMargin: '100px',
        threshold: 0
    };

    const finalOptions = { ...defaultOptions, ...options };

    const observer = new IntersectionObserver(async (entries) => {
        entries.forEach(async entry => {
            if (entry.isIntersecting) {
                observer.unobserve(trigger);
                await loadMoreFn();
                observer.observe(trigger);
            }
        });
    }, finalOptions);

    observer.observe(trigger);

    return observer;
}

/**
 * Debounce para lazy loading de búsquedas
 * @param {Function} func - Función a ejecutar
 * @param {number} wait - Tiempo de espera en ms
 * @returns {Function}
 */
export function debounce(func, wait = 300) {
    let timeout;

    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };

        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Throttle para lazy loading en scroll
 * @param {Function} func - Función a ejecutar
 * @param {number} limit - Límite en ms
 * @returns {Function}
 */
export function throttle(func, limit = 100) {
    let inThrottle;

    return function executedFunction(...args) {
        if (!inThrottle) {
            func(...args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

/**
 * Carga condicional basada en conexión de red
 * @param {Function} highQualityFn - Función para conexión rápida
 * @param {Function} lowQualityFn - Función para conexión lenta
 */
export function loadBasedOnConnection(highQualityFn, lowQualityFn) {
    if (!('connection' in navigator)) {
        // Si no hay API de Network Information, usar alta calidad
        highQualityFn();
        return;
    }

    const connection = navigator.connection;
    const effectiveType = connection.effectiveType;

    // 4g, 3g, 2g, slow-2g
    if (effectiveType === '4g' || effectiveType === '3g') {
        highQualityFn();
    } else {
        lowQualityFn();
    }
}

/**
 * Lazy loading basado en idle time del navegador
 * @param {Function} func - Función a ejecutar
 * @param {Object} options - Opciones para requestIdleCallback
 */
export function runOnIdle(func, options = {}) {
    if ('requestIdleCallback' in window) {
        requestIdleCallback(func, options);
    } else {
        // Fallback para navegadores sin soporte
        setTimeout(func, 1);
    }
}

/**
 * Precarga de imagen en background
 * @param {string} src - URL de la imagen
 * @returns {Promise}
 */
export function preloadImage(src) {
    return new Promise((resolve, reject) => {
        const img = new Image();
        img.onload = () => resolve(img);
        img.onerror = reject;
        img.src = src;
    });
}

/**
 * Precarga múltiples imágenes
 * @param {Array<string>} sources - Array de URLs
 * @returns {Promise}
 */
export function preloadImages(sources) {
    return Promise.all(sources.map(src => preloadImage(src)));
}

/**
 * Inicialización automática al cargar el DOM
 */
document.addEventListener('DOMContentLoaded', () => {
    // Auto-inicializar lazy loading de imágenes
    if (document.querySelector('img[data-src]')) {
        initLazyImages();
    }

    // Auto-inicializar lazy loading de iframes
    if (document.querySelector('iframe[data-src]')) {
        initLazyIframes();
    }
});
