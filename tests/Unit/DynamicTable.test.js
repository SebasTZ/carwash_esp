/**
 * Tests unitarios para DynamicTable Component
 * 
 * @vitest-environment happy-dom
 */

import { describe, it, expect, beforeEach, afterEach, vi } from 'vitest';
import DynamicTable from '../../resources/js/components/tables/DynamicTable.js';

describe('DynamicTable Component', () => {
    beforeEach(() => {
        // Setup DOM
        document.body.innerHTML = '<div id="container"><table id="test-table"></table></div>';
    });

    describe('Inicialización', () => {
        it('debería crear una instancia correctamente', () => {
            const table = new DynamicTable('#test-table', {
                columns: [
                    { key: 'id', label: '#' },
                    { key: 'name', label: 'Nombre' }
                ]
            });

            expect(table).toBeInstanceOf(DynamicTable);
            expect(table.element).not.toBeNull();
        });

        it('debería aplicar clases Bootstrap por defecto', () => {
            new DynamicTable('#test-table', {
                columns: [{ key: 'id', label: '#' }]
            });

            const tableElement = document.querySelector('#test-table');
            expect(tableElement.classList.contains('table')).toBe(true);
            expect(tableElement.classList.contains('table-striped')).toBe(true);
            expect(tableElement.classList.contains('table-hover')).toBe(true);
        });
    });

    describe('Renderizado de datos', () => {
        it('debería renderizar filas correctamente', () => {
            new DynamicTable('#test-table', {
                columns: [
                    { key: 'id', label: '#' },
                    { key: 'name', label: 'Nombre' }
                ],
                data: [
                    { id: 1, name: 'Test 1' },
                    { id: 2, name: 'Test 2' }
                ]
            });

            const rows = document.querySelectorAll('#test-table tbody tr');
            expect(rows.length).toBe(2);
        });

        it('debería mostrar mensaje cuando no hay datos', () => {
            new DynamicTable('#test-table', {
                columns: [{ key: 'id', label: '#' }],
                data: [],
                emptyMessage: 'Sin datos'
            });

            const tbody = document.querySelector('#test-table tbody');
            expect(tbody.textContent).toContain('Sin datos');
        });
    });

    describe('Búsqueda', () => {
        it('debería crear barra de búsqueda si searchable es true', () => {
            new DynamicTable('#test-table', {
                columns: [{ key: 'name', label: 'Nombre' }],
                searchable: true
            });

            const searchInput = document.querySelector('[data-table-search]');
            expect(searchInput).not.toBeNull();
        });

        it('debería filtrar datos correctamente', () => {
            const table = new DynamicTable('#test-table', {
                columns: [{ key: 'name', label: 'Nombre' }],
                data: [
                    { id: 1, name: 'Juan' },
                    { id: 2, name: 'Pedro' },
                    { id: 3, name: 'Maria' }
                ],
                searchable: true
            });

            table.handleSearch('juan');
            
            expect(table.getFilteredData().length).toBe(1);
            expect(table.getFilteredData()[0].name).toBe('Juan');
        });
    });

    describe('Formatters', () => {
        it('debería formatear moneda correctamente', () => {
            new DynamicTable('#test-table', {
                columns: [
                    { key: 'price', label: 'Precio', formatter: 'currency' }
                ],
                data: [{ id: 1, price: 100 }]
            });

            const cell = document.querySelector('#test-table tbody td');
            expect(cell.textContent).toBe('S/ 100.00');
        });

        it('debería formatear fecha correctamente', () => {
            new DynamicTable('#test-table', {
                columns: [
                    { key: 'date', label: 'Fecha', formatter: 'date' }
                ],
                data: [{ id: 1, date: '2025-10-21' }]
            });

            const cell = document.querySelector('#test-table tbody td');
            // La fecha puede variar según el locale, solo verificar que tiene formato de fecha
            expect(cell.textContent).toMatch(/\d{2}\/\d{2}\/\d{4}/);
        });
    });

    describe('Acciones CRUD', () => {
        it('debería agregar fila correctamente', () => {
            const table = new DynamicTable('#test-table', {
                columns: [{ key: 'name', label: 'Nombre' }],
                data: [{ id: 1, name: 'Test 1' }]
            });

            table.addRow({ id: 2, name: 'Test 2' });

            expect(table.getData().length).toBe(2);
            const rows = document.querySelectorAll('#test-table tbody tr');
            expect(rows.length).toBe(2);
        });

        it('debería eliminar fila correctamente', () => {
            const table = new DynamicTable('#test-table', {
                columns: [{ key: 'name', label: 'Nombre' }],
                data: [
                    { id: 1, name: 'Test 1' },
                    { id: 2, name: 'Test 2' }
                ]
            });

            table.removeRow(1);

            expect(table.getData().length).toBe(1);
            expect(table.getData()[0].id).toBe(2);
        });

        it('debería actualizar fila correctamente', () => {
            const table = new DynamicTable('#test-table', {
                columns: [{ key: 'name', label: 'Nombre' }],
                data: [{ id: 1, name: 'Test 1' }]
            });

            table.updateRow(1, { name: 'Updated' });

            const row = table.getRowById(1);
            expect(row.name).toBe('Updated');
        });

        it('debería limpiar toda la tabla', () => {
            const table = new DynamicTable('#test-table', {
                columns: [{ key: 'name', label: 'Nombre' }],
                data: [
                    { id: 1, name: 'Test 1' },
                    { id: 2, name: 'Test 2' }
                ]
            });

            table.clearTable();

            expect(table.getData().length).toBe(0);
        });
    });

    describe('Eventos', () => {
        it('debería llamar onRowAdded al agregar fila', () => {
            const onRowAddedMock = vi.fn();
            
            const table = new DynamicTable('#test-table', {
                columns: [{ key: 'name', label: 'Nombre' }],
                data: [],
                onRowAdded: onRowAddedMock
            });

            const newRow = { id: 1, name: 'Test' };
            table.addRow(newRow);

            expect(onRowAddedMock).toHaveBeenCalledWith(newRow);
        });
    });
});
