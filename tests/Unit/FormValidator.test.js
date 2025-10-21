import { describe, it, expect, beforeEach, afterEach, vi } from 'vitest';
import FormValidator from '@components/forms/FormValidator.js';

describe('FormValidator Component', () => {
    let container;
    let form;

    beforeEach(() => {
        container = document.createElement('div');
        document.body.appendChild(container);

        // Crear form de prueba
        form = document.createElement('form');
        form.id = 'test-form';
        form.innerHTML = `
            <input type="text" name="username" />
            <input type="email" name="email" />
            <input type="password" name="password" />
            <input type="number" name="age" />
            <input type="text" name="phone" />
            <input type="checkbox" name="terms" />
            <button type="submit">Submit</button>
        `;
        container.appendChild(form);
    });

    afterEach(() => {
        container.remove();
    });

    describe('Inicialización', () => {
        it('debería crear instancia correctamente', () => {
            const validator = new FormValidator('#test-form', {
                rules: {
                    username: { required: true }
                }
            });

            expect(validator).toBeInstanceOf(FormValidator);
            expect(validator.element).toBe(form);
            expect(form.getAttribute('novalidate')).toBe('novalidate');
        });

        it('debería lanzar error si no es un form', () => {
            const div = document.createElement('div');
            div.id = 'not-form';
            container.appendChild(div);

            expect(() => {
                new FormValidator('#not-form', {});
            }).toThrow('El elemento debe ser un <form>');
        });

        it('debería encontrar campos con reglas', () => {
            const validator = new FormValidator('#test-form', {
                rules: {
                    username: { required: true },
                    email: { required: true, email: true }
                }
            });

            expect(Object.keys(validator.fields)).toHaveLength(2);
            expect(validator.fields.username).toBeTruthy();
            expect(validator.fields.email).toBeTruthy();
        });

        it('debería crear elementos para errores', () => {
            const validator = new FormValidator('#test-form', {
                rules: {
                    username: { required: true }
                },
                showErrors: true
            });

            const errorElement = validator.fields.username.errorElement;
            expect(errorElement).toBeTruthy();
            expect(errorElement.className).toBe('invalid-feedback');
        });
    });

    describe('Validación Required', () => {
        it('debería validar campo required con valor', () => {
            const validator = new FormValidator('#test-form', {
                rules: {
                    username: { required: true }
                }
            });

            form.querySelector('[name="username"]').value = 'John';
            const isValid = validator.validateField('username');

            expect(isValid).toBe(true);
            expect(validator.errors.username).toBeUndefined();
        });

        it('debería fallar required sin valor', () => {
            const validator = new FormValidator('#test-form', {
                rules: {
                    username: { required: true }
                }
            });

            form.querySelector('[name="username"]').value = '';
            const isValid = validator.validateField('username');

            expect(isValid).toBe(false);
            expect(validator.errors.username).toBeTruthy();
            expect(validator.errors.username[0]).toContain('obligatorio');
        });

        it('debería validar checkbox required', () => {
            const validator = new FormValidator('#test-form', {
                rules: {
                    terms: { required: true }
                }
            });

            const checkbox = form.querySelector('[name="terms"]');
            
            // Sin marcar
            checkbox.checked = false;
            expect(validator.validateField('terms')).toBe(false);

            // Marcado
            checkbox.checked = true;
            expect(validator.validateField('terms')).toBe(true);
        });
    });

    describe('Validación Email', () => {
        it('debería validar email correcto', () => {
            const validator = new FormValidator('#test-form', {
                rules: {
                    email: { email: true }
                }
            });

            form.querySelector('[name="email"]').value = 'test@example.com';
            expect(validator.validateField('email')).toBe(true);
        });

        it('debería fallar con email inválido', () => {
            const validator = new FormValidator('#test-form', {
                rules: {
                    email: { email: true }
                }
            });

            form.querySelector('[name="email"]').value = 'invalid-email';
            expect(validator.validateField('email')).toBe(false);
            expect(validator.errors.email[0]).toContain('email válido');
        });

        it('debería permitir email vacío si no es required', () => {
            const validator = new FormValidator('#test-form', {
                rules: {
                    email: { email: true }
                }
            });

            form.querySelector('[name="email"]').value = '';
            expect(validator.validateField('email')).toBe(true);
        });
    });

    describe('Validación MinLength y MaxLength', () => {
        it('debería validar minLength', () => {
            const validator = new FormValidator('#test-form', {
                rules: {
                    password: { minLength: 8 }
                }
            });

            // Muy corto
            form.querySelector('[name="password"]').value = '123';
            expect(validator.validateField('password')).toBe(false);

            // Correcto
            form.querySelector('[name="password"]').value = '12345678';
            expect(validator.validateField('password')).toBe(true);
        });

        it('debería validar maxLength', () => {
            const validator = new FormValidator('#test-form', {
                rules: {
                    username: { maxLength: 10 }
                }
            });

            // Muy largo
            form.querySelector('[name="username"]').value = '12345678901';
            expect(validator.validateField('username')).toBe(false);

            // Correcto
            form.querySelector('[name="username"]').value = '123456789';
            expect(validator.validateField('username')).toBe(true);
        });
    });

    describe('Validación Min y Max', () => {
        it('debería validar min', () => {
            const validator = new FormValidator('#test-form', {
                rules: {
                    age: { min: 18 }
                }
            });

            form.querySelector('[name="age"]').value = '17';
            expect(validator.validateField('age')).toBe(false);

            form.querySelector('[name="age"]').value = '18';
            expect(validator.validateField('age')).toBe(true);
        });

        it('debería validar max', () => {
            const validator = new FormValidator('#test-form', {
                rules: {
                    age: { max: 100 }
                }
            });

            form.querySelector('[name="age"]').value = '101';
            expect(validator.validateField('age')).toBe(false);

            form.querySelector('[name="age"]').value = '100';
            expect(validator.validateField('age')).toBe(true);
        });
    });

    describe('Validación Pattern', () => {
        it('debería validar pattern string', () => {
            const validator = new FormValidator('#test-form', {
                rules: {
                    username: { pattern: '^[a-z]+$' }
                }
            });

            form.querySelector('[name="username"]').value = 'abc123';
            expect(validator.validateField('username')).toBe(false);

            form.querySelector('[name="username"]').value = 'abcdef';
            expect(validator.validateField('username')).toBe(true);
        });

        it('debería validar pattern RegExp', () => {
            const validator = new FormValidator('#test-form', {
                rules: {
                    username: { pattern: /^[A-Z][a-z]+$/ }
                }
            });

            form.querySelector('[name="username"]').value = 'john';
            expect(validator.validateField('username')).toBe(false);

            form.querySelector('[name="username"]').value = 'John';
            expect(validator.validateField('username')).toBe(true);
        });
    });

    describe('Validación Number y Digits', () => {
        it('debería validar number', () => {
            const validator = new FormValidator('#test-form', {
                rules: {
                    phone: { number: true }  // Usar phone en vez de age (type=text)
                }
            });

            form.querySelector('[name="phone"]').value = 'abc';
            expect(validator.validateField('phone')).toBe(false);

            form.querySelector('[name="phone"]').value = '25.5';
            expect(validator.validateField('phone')).toBe(true);
        });

        it('debería validar integer', () => {
            const validator = new FormValidator('#test-form', {
                rules: {
                    age: { integer: true }
                }
            });

            form.querySelector('[name="age"]').value = '25.5';
            expect(validator.validateField('age')).toBe(false);

            form.querySelector('[name="age"]').value = '25';
            expect(validator.validateField('age')).toBe(true);
        });

        it('debería validar digits', () => {
            const validator = new FormValidator('#test-form', {
                rules: {
                    phone: { digits: true }
                }
            });

            form.querySelector('[name="phone"]').value = '123abc';
            expect(validator.validateField('phone')).toBe(false);

            form.querySelector('[name="phone"]').value = '123456';
            expect(validator.validateField('phone')).toBe(true);
        });
    });

    describe('Validación Phone y Alpha', () => {
        it('debería validar phone', () => {
            const validator = new FormValidator('#test-form', {
                rules: {
                    phone: { phone: true }
                }
            });

            form.querySelector('[name="phone"]').value = 'abc';
            expect(validator.validateField('phone')).toBe(false);

            form.querySelector('[name="phone"]').value = '+51 999 888 777';
            expect(validator.validateField('phone')).toBe(true);

            form.querySelector('[name="phone"]').value = '999888777';
            expect(validator.validateField('phone')).toBe(true);
        });

        it('debería validar alphanumeric', () => {
            const validator = new FormValidator('#test-form', {
                rules: {
                    username: { alphanumeric: true }
                }
            });

            form.querySelector('[name="username"]').value = 'user-123';
            expect(validator.validateField('username')).toBe(false);

            form.querySelector('[name="username"]').value = 'user123';
            expect(validator.validateField('username')).toBe(true);
        });

        it('debería validar alpha', () => {
            const validator = new FormValidator('#test-form', {
                rules: {
                    username: { alpha: true }
                }
            });

            form.querySelector('[name="username"]').value = 'user123';
            expect(validator.validateField('username')).toBe(false);

            form.querySelector('[name="username"]').value = 'username';
            expect(validator.validateField('username')).toBe(true);
        });
    });

    describe('Mensajes Custom', () => {
        it('debería usar mensajes custom', () => {
            const validator = new FormValidator('#test-form', {
                rules: {
                    username: { required: true }
                },
                messages: {
                    username: {
                        required: 'El nombre de usuario es obligatorio'
                    }
                }
            });

            form.querySelector('[name="username"]').value = '';
            validator.validateField('username');

            expect(validator.errors.username[0]).toBe('El nombre de usuario es obligatorio');
        });

        it('debería reemplazar placeholders en mensajes', () => {
            const validator = new FormValidator('#test-form', {
                rules: {
                    password: { minLength: 8 }
                }
            });

            form.querySelector('[name="password"]').value = '123';
            validator.validateField('password');

            expect(validator.errors.password[0]).toContain('8');
        });
    });

    describe('Clases CSS', () => {
        it('debería aplicar clase is-invalid', () => {
            const validator = new FormValidator('#test-form', {
                rules: {
                    username: { required: true }
                }
            });

            const field = form.querySelector('[name="username"]');
            field.value = '';
            validator.validateField('username');

            expect(field.classList.contains('is-invalid')).toBe(true);
        });

        it('debería aplicar clase is-valid', () => {
            const validator = new FormValidator('#test-form', {
                rules: {
                    username: { required: true }
                }
            });

            const field = form.querySelector('[name="username"]');
            field.value = 'John';
            validator.validateField('username');

            expect(field.classList.contains('is-valid')).toBe(true);
        });

        it('debería mostrar mensaje de error', () => {
            const validator = new FormValidator('#test-form', {
                rules: {
                    username: { required: true }
                },
                showErrors: true
            });

            form.querySelector('[name="username"]').value = '';
            validator.validateField('username');

            const errorElement = validator.fields.username.errorElement;
            expect(errorElement.style.display).toBe('block');
            expect(errorElement.textContent).toContain('obligatorio');
        });
    });

    describe('Validación Completa del Form', () => {
        it('debería validar todo el formulario', () => {
            const validator = new FormValidator('#test-form', {
                rules: {
                    username: { required: true },
                    email: { required: true, email: true }
                }
            });

            form.querySelector('[name="username"]').value = 'John';
            form.querySelector('[name="email"]').value = 'john@test.com';

            const isValid = validator.validate();

            expect(isValid).toBe(true);
            expect(validator.isValid).toBe(true);
            expect(Object.keys(validator.errors)).toHaveLength(0);
        });

        it('debería fallar si algún campo es inválido', () => {
            const validator = new FormValidator('#test-form', {
                rules: {
                    username: { required: true },
                    email: { required: true, email: true }
                }
            });

            form.querySelector('[name="username"]').value = 'John';
            form.querySelector('[name="email"]').value = 'invalid';

            const isValid = validator.validate();

            expect(isValid).toBe(false);
            expect(validator.isValid).toBe(false);
            expect(validator.errors.email).toBeTruthy();
        });
    });

    describe('Callbacks', () => {
        it('debería llamar onValid cuando form válido', () => {
            const onValid = vi.fn();
            const validator = new FormValidator('#test-form', {
                rules: {
                    username: { required: true }
                },
                onValid
            });

            form.querySelector('[name="username"]').value = 'John';
            validator.validate();

            expect(onValid).toHaveBeenCalled();
            expect(onValid.mock.calls[0][0].username).toBe('John');
        });

        it('debería llamar onInvalid cuando form inválido', () => {
            const onInvalid = vi.fn();
            const validator = new FormValidator('#test-form', {
                rules: {
                    username: { required: true }
                },
                onInvalid
            });

            form.querySelector('[name="username"]').value = '';
            validator.validate();

            expect(onInvalid).toHaveBeenCalled();
            expect(onInvalid.mock.calls[0][0].username).toBeTruthy();
        });

        it('debería llamar onFieldValid', () => {
            const onFieldValid = vi.fn();
            const validator = new FormValidator('#test-form', {
                rules: {
                    username: { required: true }
                },
                onFieldValid
            });

            form.querySelector('[name="username"]').value = 'John';
            validator.validateField('username');

            expect(onFieldValid).toHaveBeenCalledWith('username', 'John');
        });

        it('debería llamar onFieldInvalid', () => {
            const onFieldInvalid = vi.fn();
            const validator = new FormValidator('#test-form', {
                rules: {
                    username: { required: true }
                },
                onFieldInvalid
            });

            form.querySelector('[name="username"]').value = '';
            validator.validateField('username');

            expect(onFieldInvalid).toHaveBeenCalled();
            expect(onFieldInvalid.mock.calls[0][0]).toBe('username');
        });
    });

    describe('Validadores Custom', () => {
        it('debería agregar validador custom', () => {
            const validator = new FormValidator('#test-form', {
                rules: {
                    username: { required: true }
                }
            });

            validator.addValidator('startsWithA', (value) => {
                return value.startsWith('A');
            });

            validator.addRule('username', 'startsWithA', true);

            form.querySelector('[name="username"]').value = 'John';
            expect(validator.validateField('username')).toBe(false);

            form.querySelector('[name="username"]').value = 'Alice';
            expect(validator.validateField('username')).toBe(true);
        });

        it('debería usar customValidators en opciones', () => {
            const validator = new FormValidator('#test-form', {
                rules: {
                    username: { custom: true }
                },
                customValidators: {
                    custom: (value) => value.length > 5
                }
            });

            form.querySelector('[name="username"]').value = 'John';
            expect(validator.validateField('username')).toBe(false);

            form.querySelector('[name="username"]').value = 'Johnny';
            expect(validator.validateField('username')).toBe(true);
        });
    });

    describe('Control del Form', () => {
        it('debería limpiar errores', () => {
            const validator = new FormValidator('#test-form', {
                rules: {
                    username: { required: true }
                }
            });

            form.querySelector('[name="username"]').value = '';
            validator.validateField('username');
            expect(validator.errors.username).toBeTruthy();

            validator.clearErrors();
            expect(Object.keys(validator.errors)).toHaveLength(0);
            expect(form.querySelector('[name="username"]').classList.contains('is-invalid')).toBe(false);
        });

        it('debería resetear formulario', () => {
            const validator = new FormValidator('#test-form', {
                rules: {
                    username: { required: true }
                }
            });

            const field = form.querySelector('[name="username"]');
            field.value = 'John';
            validator.validateField('username');

            validator.reset();

            expect(field.value).toBe('');
            expect(field.classList.contains('is-valid')).toBe(false);
            expect(validator.isValid).toBe(false);
        });

        it('debería agregar regla dinámicamente', () => {
            const validator = new FormValidator('#test-form', {
                rules: {
                    username: { required: true }
                }
            });

            validator.addRule('username', 'minLength', 5);

            form.querySelector('[name="username"]').value = 'Jo';
            expect(validator.validateField('username')).toBe(false);

            form.querySelector('[name="username"]').value = 'Johnny';
            expect(validator.validateField('username')).toBe(true);
        });

        it('debería remover regla', () => {
            const validator = new FormValidator('#test-form', {
                rules: {
                    username: { required: true, minLength: 5 }
                }
            });

            validator.removeRule('username', 'minLength');

            form.querySelector('[name="username"]').value = 'Jo';
            expect(validator.validateField('username')).toBe(true);
        });
    });

    describe('Submit del Form', () => {
        it('debería prevenir submit por defecto', () => {
            const validator = new FormValidator('#test-form', {
                rules: {
                    username: { required: true }
                },
                validateOnSubmit: true
            });

            const submitEvent = new Event('submit');
            const preventDefault = vi.spyOn(submitEvent, 'preventDefault');

            form.dispatchEvent(submitEvent);

            expect(preventDefault).toHaveBeenCalled();
        });

        it('debería deshabilitar botón submit si inválido', () => {
            const validator = new FormValidator('#test-form', {
                rules: {
                    username: { required: true }
                },
                disableSubmitOnInvalid: true
            });

            form.querySelector('[name="username"]').value = '';
            validator.validate();

            const submitBtn = form.querySelector('[type="submit"]');
            expect(submitBtn.disabled).toBe(true);
        });
    });

    describe('Validación on Blur e Input', () => {
        it('debería validar on blur', () => {
            const validator = new FormValidator('#test-form', {
                rules: {
                    username: { required: true }
                },
                validateOnBlur: true
            });

            const field = form.querySelector('[name="username"]');
            field.value = '';
            field.dispatchEvent(new Event('blur'));

            expect(validator.errors.username).toBeTruthy();
        });

        it('debería validar on input', () => {
            const validator = new FormValidator('#test-form', {
                rules: {
                    username: { required: true }
                },
                validateOnInput: true
            });

            const field = form.querySelector('[name="username"]');
            field.value = 'J';
            field.dispatchEvent(new Event('input'));

            // Con valor, debe ser válido
            expect(validator.errors.username).toBeUndefined();
        });
    });
});
