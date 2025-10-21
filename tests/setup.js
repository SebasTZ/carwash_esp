/**
 * Setup file para Vitest
 * Configuración global de tests
 */

import { expect, afterEach, vi } from 'vitest';

// Cleanup automático después de cada test
afterEach(() => {
    document.body.innerHTML = '';
});

// Mock de localStorage para tests
const localStorageMock = {
    getItem: vi.fn(),
    setItem: vi.fn(),
    removeItem: vi.fn(),
    clear: vi.fn(),
};
global.localStorage = localStorageMock;

// Mock de fetch para tests
global.fetch = vi.fn();
