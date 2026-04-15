export default class DraftStorage {
    constructor(storageKey) {
        this.storageKey = storageKey;
    }

    save(payload) {
        try {
            const data = {
                ...payload,
                timestamp: new Date().toISOString(),
            };
            localStorage.setItem(this.storageKey, JSON.stringify(data));
            return true;
        } catch (error) {
            console.warn(`No se pudo guardar el borrador ${this.storageKey}:`, error);
            return false;
        }
    }

    load() {
        try {
            const raw = localStorage.getItem(this.storageKey);
            if (!raw) {
                return null;
            }

            return JSON.parse(raw);
        } catch (error) {
            console.warn(`No se pudo cargar el borrador ${this.storageKey}:`, error);
            return null;
        }
    }

    clear() {
        try {
            localStorage.removeItem(this.storageKey);
            return true;
        } catch (error) {
            console.warn(`No se pudo limpiar el borrador ${this.storageKey}:`, error);
            return false;
        }
    }
}
