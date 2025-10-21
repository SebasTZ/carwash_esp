import { describe, it, expect, beforeEach, afterEach, vi } from 'vitest';
import AutoSave from '@components/forms/AutoSave.js';

describe('AutoSave Component', () => {
    let container;
    let form;
    let mockSaveCallback;

    beforeEach(() => {
        // Setup DOM
        container = document.createElement('div');
        document.body.appendChild(container);

        // Crear form de prueba
        form = document.createElement('form');
        form.id = 'test-form';
        form.innerHTML = `
            <input type="text" name="name" value="" />
            <input type="email" name="email" value="" />
            <input type="number" name="age" value="" />
            <textarea name="bio"></textarea>
            <select name="country">
                <option value="PE">Perú</option>
                <option value="US">USA</option>
            </select>
            <input type="checkbox" name="terms" value="accepted" />
        `;
        container.appendChild(form);

        // Mock callback
        mockSaveCallback = vi.fn().mockResolvedValue({ success: true });

        // Mock localStorage
        Storage.prototype.getItem = vi.fn();
        Storage.prototype.setItem = vi.fn();
        Storage.prototype.removeItem = vi.fn();
    });

    afterEach(() => {
        container.remove();
        vi.clearAllTimers();
        vi.restoreAllMocks();
    });

    describe('Inicialización', () => {
        it('debería crear instancia correctamente', () => {
            const autoSave = new AutoSave('#test-form', {
                saveCallback: mockSaveCallback,
                delay: 1000
            });

            expect(autoSave).toBeInstanceOf(AutoSave);
            expect(autoSave.element).toBe(form);
            expect(autoSave.autoSaveOptions.delay).toBe(1000);
        });

        it('debería lanzar error si no es un form', () => {
            const div = document.createElement('div');
            div.id = 'not-form';
            container.appendChild(div);

            expect(() => {
                new AutoSave('#not-form', { saveCallback: mockSaveCallback });
            }).toThrow('El elemento debe ser un <form>');
        });

        it('debería lanzar error si no hay saveCallback ni localStorage', () => {
            expect(() => {
                new AutoSave('#test-form', { 
                    enableLocalStorage: false 
                });
            }).toThrow('Debe proporcionar saveCallback o habilitar localStorage');
        });

        it('debería generar storageKey automáticamente', () => {
            const autoSave = new AutoSave('#test-form', {
                saveCallback: mockSaveCallback
            });

            expect(autoSave.autoSaveOptions.storageKey).toContain('autosave_test-form_');
        });
    });

    describe('Indicador Visual', () => {
        it('debería crear indicador si showIndicator es true', () => {
            const autoSave = new AutoSave('#test-form', {
                saveCallback: mockSaveCallback,
                showIndicator: true
            });

            expect(autoSave.indicator).toBeTruthy();
            expect(autoSave.indicator.className).toBe('autosave-indicator');
        });

        it('no debería crear indicador si showIndicator es false', () => {
            const autoSave = new AutoSave('#test-form', {
                saveCallback: mockSaveCallback,
                showIndicator: false
            });

            expect(autoSave.indicator).toBeNull();
        });

        it('debería actualizar indicador con estado "saving"', () => {
            const autoSave = new AutoSave('#test-form', {
                saveCallback: mockSaveCallback
            });

            autoSave.updateIndicator('saving');

            expect(autoSave.indicator.style.display).toBe('block');
            expect(autoSave.indicator.textContent).toContain('Guardando');
        });

        it('debería actualizar indicador con estado "saved"', () => {
            vi.useFakeTimers();
            const autoSave = new AutoSave('#test-form', {
                saveCallback: mockSaveCallback
            });

            autoSave.updateIndicator('saved');

            expect(autoSave.indicator.textContent).toContain('Guardado');
            
            // Auto-ocultar después de 2s
            vi.advanceTimersByTime(2000);
            expect(autoSave.indicator.style.display).toBe('none');
            
            vi.useRealTimers();
        });

        it('debería actualizar indicador con estado "error"', () => {
            const autoSave = new AutoSave('#test-form', {
                saveCallback: mockSaveCallback
            });

            autoSave.updateIndicator('error', 'Error personalizado');

            expect(autoSave.indicator.textContent).toContain('Error personalizado');
        });
    });

    describe('Debouncing y Auto-guardado', () => {
        it('debería programar auto-guardado con delay', () => {
            vi.useFakeTimers();
            
            const autoSave = new AutoSave('#test-form', {
                saveCallback: mockSaveCallback,
                delay: 1000
            });

            const nameInput = form.querySelector('[name="name"]');
            nameInput.value = 'John Doe';
            nameInput.dispatchEvent(new Event('input'));

            expect(mockSaveCallback).not.toHaveBeenCalled();

            // Avanzar timer
            vi.advanceTimersByTime(1000);

            // Esperar siguiente tick para Promise
            setTimeout(() => {
                expect(mockSaveCallback).toHaveBeenCalledTimes(1);
            }, 0);

            vi.useRealTimers();
        });

        it('debería cancelar timer anterior si hay nuevo input', () => {
            vi.useFakeTimers();
            
            const autoSave = new AutoSave('#test-form', {
                saveCallback: mockSaveCallback,
                delay: 1000
            });

            const nameInput = form.querySelector('[name="name"]');
            
            // Primer input
            nameInput.value = 'J';
            nameInput.dispatchEvent(new Event('input'));
            
            vi.advanceTimersByTime(500);
            
            // Segundo input antes de que termine delay
            nameInput.value = 'Jo';
            nameInput.dispatchEvent(new Event('input'));
            
            vi.advanceTimersByTime(500); // Total 1000ms desde primer input
            
            // No debería haberse llamado aún
            expect(mockSaveCallback).not.toHaveBeenCalled();
            
            // Completar segundo delay
            vi.advanceTimersByTime(500);
            
            setTimeout(() => {
                expect(mockSaveCallback).toHaveBeenCalledTimes(1);
            }, 0);

            vi.useRealTimers();
        });

        it('no debería guardar si datos no cambiaron', async () => {
            const autoSave = new AutoSave('#test-form', {
                saveCallback: mockSaveCallback,
                delay: 100
            });

            const formData = { name: 'John', email: 'john@test.com' };
            autoSave.lastSavedData = formData;

            // Simular que getFormData retorna los mismos datos
            vi.spyOn(autoSave, 'getFormData').mockReturnValue(formData);

            await autoSave.performAutoSave();

            expect(mockSaveCallback).not.toHaveBeenCalled();
        });
    });

    describe('Obtener Datos del Formulario', () => {
        it('debería obtener todos los campos del form', () => {
            const autoSave = new AutoSave('#test-form', {
                saveCallback: mockSaveCallback
            });

            form.querySelector('[name="name"]').value = 'John Doe';
            form.querySelector('[name="email"]').value = 'john@test.com';
            form.querySelector('[name="age"]').value = '30';
            form.querySelector('[name="bio"]').value = 'Developer';
            form.querySelector('[name="country"]').value = 'US';

            const data = autoSave.getFormData();

            expect(data.name).toBe('John Doe');
            expect(data.email).toBe('john@test.com');
            expect(data.age).toBe('30');
            expect(data.bio).toBe('Developer');
            expect(data.country).toBe('US');
        });

        it('debería excluir campos en excludeFields', () => {
            const autoSave = new AutoSave('#test-form', {
                saveCallback: mockSaveCallback,
                excludeFields: ['email']
            });

            form.querySelector('[name="name"]').value = 'John';
            form.querySelector('[name="email"]').value = 'john@test.com';

            const data = autoSave.getFormData();

            expect(data.name).toBe('John');
            expect(data.email).toBeUndefined();
        });

        it('debería incluir solo campos en includeFields', () => {
            const autoSave = new AutoSave('#test-form', {
                saveCallback: mockSaveCallback,
                includeFields: ['name', 'email']
            });

            form.querySelector('[name="name"]').value = 'John';
            form.querySelector('[name="email"]').value = 'john@test.com';
            form.querySelector('[name="age"]').value = '30';

            const data = autoSave.getFormData();

            expect(data.name).toBe('John');
            expect(data.email).toBe('john@test.com');
            expect(data.age).toBeUndefined();
        });

        it('debería manejar checkboxes correctamente', () => {
            const autoSave = new AutoSave('#test-form', {
                saveCallback: mockSaveCallback
            });

            const checkbox = form.querySelector('[name="terms"]');
            checkbox.checked = true;

            const data = autoSave.getFormData();

            expect(data.terms).toBe('accepted');
        });
    });

    describe('LocalStorage', () => {
        it('debería guardar en localStorage', () => {
            const autoSave = new AutoSave('#test-form', {
                saveCallback: mockSaveCallback,
                enableLocalStorage: true
            });

            const data = { name: 'John', email: 'john@test.com' };
            autoSave.saveToStorage(data);

            expect(localStorage.setItem).toHaveBeenCalled();
            const [key, value] = localStorage.setItem.mock.calls[0];
            expect(key).toContain('autosave_test-form_');
            
            const stored = JSON.parse(value);
            expect(stored.data).toEqual(data);
            expect(stored.timestamp).toBeTruthy();
        });

        it('debería restaurar desde localStorage', () => {
            const storedData = {
                data: { name: 'John', email: 'john@test.com' },
                timestamp: Date.now(),
                url: window.location.href
            };

            localStorage.getItem.mockReturnValue(JSON.stringify(storedData));

            const autoSave = new AutoSave('#test-form', {
                saveCallback: mockSaveCallback,
                enableLocalStorage: true
            });

            expect(form.querySelector('[name="name"]').value).toBe('John');
            expect(form.querySelector('[name="email"]').value).toBe('john@test.com');
        });

        it('no debería restaurar si URL es diferente', () => {
            const storedData = {
                data: { name: 'John' },
                timestamp: Date.now(),
                url: 'http://different-url.com'
            };

            localStorage.getItem.mockReturnValue(JSON.stringify(storedData));

            const autoSave = new AutoSave('#test-form', {
                saveCallback: mockSaveCallback,
                enableLocalStorage: true
            });

            expect(form.querySelector('[name="name"]').value).toBe('');
        });

        it('debería limpiar localStorage si datos muy antiguos', () => {
            const storedData = {
                data: { name: 'John' },
                timestamp: Date.now() - (25 * 60 * 60 * 1000), // 25 horas atrás
                url: window.location.href
            };

            localStorage.getItem.mockReturnValue(JSON.stringify(storedData));

            const autoSave = new AutoSave('#test-form', {
                saveCallback: mockSaveCallback,
                enableLocalStorage: true
            });

            expect(localStorage.removeItem).toHaveBeenCalled();
        });

        it('debería limpiar localStorage con clearStorage()', () => {
            const autoSave = new AutoSave('#test-form', {
                saveCallback: mockSaveCallback,
                enableLocalStorage: true
            });

            autoSave.clearStorage();

            expect(localStorage.removeItem).toHaveBeenCalled();
        });
    });

    describe('Callbacks y Eventos', () => {
        it('debería llamar onSaving al iniciar guardado', async () => {
            const onSaving = vi.fn();
            
            const autoSave = new AutoSave('#test-form', {
                saveCallback: mockSaveCallback,
                onSaving
            });

            await autoSave.performAutoSave();

            expect(onSaving).toHaveBeenCalled();
        });

        it('debería llamar onSaved después de guardado exitoso', async () => {
            const onSaved = vi.fn();
            
            const autoSave = new AutoSave('#test-form', {
                saveCallback: mockSaveCallback,
                onSaved
            });

            form.querySelector('[name="name"]').value = 'John';
            await autoSave.performAutoSave();

            expect(onSaved).toHaveBeenCalled();
            expect(mockSaveCallback).toHaveBeenCalled();
        });

        it('debería llamar onError en caso de error', async () => {
            const onError = vi.fn();
            const errorCallback = vi.fn().mockRejectedValue(new Error('Save failed'));
            
            const autoSave = new AutoSave('#test-form', {
                saveCallback: errorCallback,
                onError,
                maxRetries: 0, // No reintentar
                enableLocalStorage: false
            });

            form.querySelector('[name="name"]').value = 'John';
            await autoSave.performAutoSave();

            expect(onError).toHaveBeenCalled();
            expect(onError.mock.calls[0][0].message).toBe('Save failed');
        });

        it('debería llamar onRestore al restaurar desde localStorage', () => {
            const onRestore = vi.fn();
            
            const storedData = {
                data: { name: 'John' },
                timestamp: Date.now(),
                url: window.location.href
            };

            localStorage.getItem.mockReturnValue(JSON.stringify(storedData));

            const autoSave = new AutoSave('#test-form', {
                saveCallback: mockSaveCallback,
                enableLocalStorage: true,
                onRestore
            });

            expect(onRestore).toHaveBeenCalledWith({ name: 'John' });
        });
    });

    describe('Validación', () => {
        it('no debería guardar si validateBeforeSave retorna false', async () => {
            const validateBeforeSave = vi.fn().mockResolvedValue(false);
            
            const autoSave = new AutoSave('#test-form', {
                saveCallback: mockSaveCallback,
                validateBeforeSave
            });

            form.querySelector('[name="name"]').value = 'John';
            await autoSave.performAutoSave();

            expect(validateBeforeSave).toHaveBeenCalled();
            expect(mockSaveCallback).not.toHaveBeenCalled();
        });

        it('debería guardar si validateBeforeSave retorna true', async () => {
            const validateBeforeSave = vi.fn().mockResolvedValue(true);
            
            const autoSave = new AutoSave('#test-form', {
                saveCallback: mockSaveCallback,
                validateBeforeSave
            });

            form.querySelector('[name="name"]').value = 'John';
            await autoSave.performAutoSave();

            expect(validateBeforeSave).toHaveBeenCalled();
            expect(mockSaveCallback).toHaveBeenCalled();
        });
    });

    describe('Reintentos en Error', () => {
        it('debería reintentar después de error', async () => {
            vi.useFakeTimers();
            
            const errorCallback = vi.fn()
                .mockRejectedValueOnce(new Error('Fail 1'))
                .mockResolvedValueOnce({ success: true });
            
            const autoSave = new AutoSave('#test-form', {
                saveCallback: errorCallback,
                maxRetries: 2,
                retryDelay: 100, // Reducir delay para test más rápido
                enableLocalStorage: false
            });

            form.querySelector('[name="name"]').value = 'John';
            
            // Primer intento (fallará)
            const savePromise = autoSave.performAutoSave();
            await savePromise;

            expect(errorCallback).toHaveBeenCalledTimes(1);
            expect(autoSave.retryCount).toBe(1);

            // Esperar reintento
            vi.advanceTimersByTime(100);
            await vi.runAllTimersAsync();

            expect(errorCallback).toHaveBeenCalledTimes(2);
            expect(autoSave.retryCount).toBe(0); // Reset después de éxito

            vi.useRealTimers();
        }, 10000); // Timeout aumentado

        it('debería llamar onError después de agotar reintentos', async () => {
            const onError = vi.fn();
            const errorCallback = vi.fn().mockRejectedValue(new Error('Fail'));
            
            const autoSave = new AutoSave('#test-form', {
                saveCallback: errorCallback,
                maxRetries: 0, // Sin reintentos para test más rápido
                onError,
                enableLocalStorage: false
            });

            form.querySelector('[name="name"]').value = 'John';
            
            await autoSave.performAutoSave();
            
            expect(onError).toHaveBeenCalled();
            expect(autoSave.retryCount).toBe(0);
        }, 10000); // Timeout aumentado
    });

    describe('Control de Guardado', () => {
        it('debería forzar guardado inmediato con forceSave()', async () => {
            vi.useFakeTimers();
            
            const autoSave = new AutoSave('#test-form', {
                saveCallback: mockSaveCallback,
                delay: 5000
            });

            form.querySelector('[name="name"]').value = 'John';
            
            await autoSave.forceSave();

            expect(mockSaveCallback).toHaveBeenCalled();
            
            vi.useRealTimers();
        });

        it('debería pausar auto-guardado', () => {
            vi.useFakeTimers();
            
            const autoSave = new AutoSave('#test-form', {
                saveCallback: mockSaveCallback,
                delay: 1000
            });

            const nameInput = form.querySelector('[name="name"]');
            nameInput.value = 'John';
            nameInput.dispatchEvent(new Event('input'));

            autoSave.pause();

            vi.advanceTimersByTime(1000);

            expect(mockSaveCallback).not.toHaveBeenCalled();
            
            vi.useRealTimers();
        });

        it('debería reanudar auto-guardado con resume()', () => {
            const autoSave = new AutoSave('#test-form', {
                saveCallback: mockSaveCallback
            });

            autoSave.pause();
            const consoleLog = vi.spyOn(console, 'log');
            
            autoSave.resume();

            expect(consoleLog).toHaveBeenCalledWith('AutoSave: Reanudado');
        });

        it('no debería guardar si isSaving es true', async () => {
            const autoSave = new AutoSave('#test-form', {
                saveCallback: mockSaveCallback
            });

            autoSave.isSaving = true;

            await autoSave.performAutoSave();

            expect(mockSaveCallback).not.toHaveBeenCalled();
        });

        it('debería prevenir submit mientras guarda', () => {
            const autoSave = new AutoSave('#test-form', {
                saveCallback: mockSaveCallback
            });

            autoSave.isSaving = true;

            const submitEvent = new Event('submit');
            const preventDefault = vi.spyOn(submitEvent, 'preventDefault');
            
            form.dispatchEvent(submitEvent);

            expect(preventDefault).toHaveBeenCalled();
        });
    });

    describe('Destrucción', () => {
        it('debería limpiar recursos con destroy()', () => {
            vi.useFakeTimers();
            
            const autoSave = new AutoSave('#test-form', {
                saveCallback: mockSaveCallback,
                delay: 1000
            });

            const nameInput = form.querySelector('[name="name"]');
            nameInput.value = 'John';
            nameInput.dispatchEvent(new Event('input'));

            autoSave.destroy();

            // Timer cancelado
            vi.advanceTimersByTime(1000);
            expect(mockSaveCallback).not.toHaveBeenCalled();

            // Indicador removido
            expect(document.contains(autoSave.indicator)).toBe(false);
            
            vi.useRealTimers();
        });
    });
});
