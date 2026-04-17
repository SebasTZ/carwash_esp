import { describe, it, expect, beforeEach, afterEach, vi } from 'vitest';

vi.mock('@utils/csrf', () => ({
    getCsrfToken: () => 'csrf-test-token',
}));

describe('CitasIndexManager', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        vi.resetModules();

        document.body.innerHTML = `
            <script type="application/json" id="citas-table-data">[{"id":7,"estado":"pendiente","cliente":{"persona":{"razon_social":"Ana","numero_documento":"123"}}}]</script>
            <script type="application/json" id="citas-endpoints-config">{"show":"/citas/__cita__","edit":"/citas/__cita__/edit","iniciar":"/citas/__cita__/iniciar","completar":"/citas/__cita__/completar","cancelar":"/citas/__cita__/cancelar","destroy":"/citas/__cita__"}</script>
            <table id="citasTable"></table>
        `;

        window.CarWash = {
            DynamicTable: vi.fn(),
        };
    });

    afterEach(() => {
        delete window.CarWash;
        delete window.citasIndexManager;
        document.body.innerHTML = '';
    });

    it('inicializa DynamicTable con datos embebidos del backend', async () => {
        const { default: CitasIndexManager } = await import('../../resources/js/modules/CitasIndexManager.js');

        new CitasIndexManager();

        expect(window.CarWash.DynamicTable).toHaveBeenCalledTimes(1);

        const [selector, options] = window.CarWash.DynamicTable.mock.calls[0];
        expect(selector).toBe('#citasTable');
        expect(options).toEqual(expect.objectContaining({
            searchPlaceholder: 'Buscar citas...',
            emptyMessage: 'No hay citas registradas',
        }));
        expect(options.data).toHaveLength(1);
        expect(options.data[0].id).toBe(7);
    });

    it('construye acciones usando endpoints configurados sin hardcodes', async () => {
        const { default: CitasIndexManager } = await import('../../resources/js/modules/CitasIndexManager.js');
        const manager = new CitasIndexManager();

        const html = manager.renderActionButtons({
            id: 'A B',
            estado: 'pendiente',
        });

        expect(html).toContain('href="/citas/A%20B"');
        expect(html).toContain('href="/citas/A%20B/edit"');
        expect(html).toContain('action="/citas/A%20B/iniciar"');
        expect(html).toContain('action="/citas/A%20B/cancelar"');
        expect(html).toContain('action="/citas/A%20B"');
        expect(html).toContain('name="_method" value="DELETE"');
        expect(html).toContain('name="_token" value="csrf-test-token"');
    });

    it('para citas en proceso incluye completar y excluye iniciar', async () => {
        const { default: CitasIndexManager } = await import('../../resources/js/modules/CitasIndexManager.js');
        const manager = new CitasIndexManager();

        const html = manager.renderActionButtons({
            id: 12,
            estado: 'en_proceso',
        });

        expect(html).toContain('action="/citas/12/completar"');
        expect(html).toContain('action="/citas/12/cancelar"');
        expect(html).not.toContain('action="/citas/12/iniciar"');
    });

    it('para citas completadas excluye edit/iniciar/completar/cancelar', async () => {
        const { default: CitasIndexManager } = await import('../../resources/js/modules/CitasIndexManager.js');
        const manager = new CitasIndexManager();

        const html = manager.renderActionButtons({
            id: 21,
            estado: 'completada',
        });

        expect(html).toContain('href="/citas/21"');
        expect(html).toContain('action="/citas/21"');
        expect(html).not.toContain('/citas/21/edit');
        expect(html).not.toContain('/citas/21/iniciar');
        expect(html).not.toContain('/citas/21/completar');
        expect(html).not.toContain('/citas/21/cancelar');
    });

    it('retorna # y registra error cuando falta endpoint configurado', async () => {
        const { default: CitasIndexManager } = await import('../../resources/js/modules/CitasIndexManager.js');
        const manager = new CitasIndexManager();
        manager.endpointsConfig = {};

        const consoleSpy = vi.spyOn(console, 'error').mockImplementation(() => {});

        const url = manager.resolveCitaUrl('show', 9);

        expect(url).toBe('#');
        expect(consoleSpy).toHaveBeenCalledWith(expect.stringContaining('Endpoint no configurado para show'));

        consoleSpy.mockRestore();
    });

    it('no inicializa cuando DynamicTable no esta disponible', async () => {
        window.CarWash = {};
        const consoleWarnSpy = vi.spyOn(console, 'warn').mockImplementation(() => {});
        const { default: CitasIndexManager } = await import('../../resources/js/modules/CitasIndexManager.js');

        new CitasIndexManager();

        expect(consoleWarnSpy).toHaveBeenCalledWith('[CitasIndexManager] DynamicTable no está disponible en window.CarWash');

        consoleWarnSpy.mockRestore();
    });
});
