/**
 * Retorna el token CSRF usando una estrategia de fallback consistente.
 */
export function getCsrfToken(root = document) {
    const meta = root.querySelector('meta[name="csrf-token"]');
    const metaToken = meta?.getAttribute('content') || meta?.content;
    if (metaToken) {
        return metaToken;
    }

    const globalToken = window.Laravel?.csrfToken;
    if (globalToken) {
        return globalToken;
    }

    return root.querySelector('input[name="_token"]')?.value || '';
}

/**
 * Agrega el header X-CSRF-TOKEN cuando exista token disponible.
 */
export function withCsrfHeader(headers = {}) {
    const token = getCsrfToken();
    if (!token) {
        return { ...headers };
    }

    return {
        ...headers,
        'X-CSRF-TOKEN': token,
    };
}
