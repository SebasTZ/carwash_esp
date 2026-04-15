import { showError } from './notifications';

/**
 * Envuelve callbacks sync/async con manejo de errores uniforme para UI.
 */
export function safeHandler(handler, options = {}) {
    const {
        message = 'Ocurrió un error inesperado. Intenta nuevamente.',
        silent = false,
        onError = null,
    } = options;

    return async (...args) => {
        try {
            return await handler(...args);
        } catch (error) {
            console.error(error);

            if (typeof onError === 'function') {
                onError(error);
            }

            if (!silent) {
                showError(message);
            }

            return undefined;
        }
    };
}
