/**
 * Lee y parsea contenido JSON embebido en un <script type="application/json">.
 */
export function readJsonScript(id, fallback = null, context = 'App') {
    const node = document.getElementById(id);
    if (!node) {
        return fallback;
    }

    try {
        return JSON.parse(node.textContent || 'null') ?? fallback;
    } catch (error) {
        console.error(`[${context}] No se pudo parsear ${id}`, error);
        return fallback;
    }
}
