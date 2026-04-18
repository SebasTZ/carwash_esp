import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { resolve } from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/public.js',
                // Componentes principales
                'resources/js/components/DetalleVentaTable.js',
                'resources/js/components/PanelDashboard.js',
                // Componentes de tablas
                'resources/js/components/tables/UserTableManager.js',
                'resources/js/components/tables/UserFormManager.js',
                'resources/js/components/tables/RoleTableManager.js',
                'resources/js/components/tables/RoleFormManager.js',
                'resources/js/components/tables/ProveedorTableManager.js',
                'resources/js/components/tables/ProveedorFormManager.js',
                'resources/js/components/tables/PagoComisionHistorialTableManager.js',
                'resources/js/components/tables/PagoComisionReporteTableManager.js',
                // Componentes de formularios
                'resources/js/components/forms/PagoComisionFormManager.js',
                // Módulos de páginas específicas
                'resources/js/modules/VentaManager.js',
                'resources/js/modules/LavadosManager.js',
                'resources/js/modules/EstacionamientoManager.js',
                'resources/js/modules/CitasFormManager.js',
                'resources/js/modules/CitasIndexManager.js',
                'resources/js/modules/ClienteCreateManager.js',
                'resources/js/modules/CocheraReportesManager.js',
                'resources/js/modules/VentaShowManager.js',
            ],
            refresh: true,
        }),
    ],
    
    // Optimización de build
    build: {
        // Reducir el límite de advertencia de tamaño de chunk
        chunkSizeWarningLimit: 1000,
        
        // Code splitting avanzado
        rollupOptions: {
            output: {
                manualChunks(id) {
                    const normalizedId = id.replace(/\\/g, '/');

                    // Separar vendors grandes
                    if (
                        normalizedId.includes('/node_modules/axios/') ||
                        normalizedId.includes('/node_modules/lodash/') ||
                        normalizedId.includes('/node_modules/alpinejs/')
                    ) {
                        return 'vendor-core';
                    }

                    // Utilidades en su propio chunk
                    if (
                        normalizedId.includes('/resources/js/utils/notifications.js') ||
                        normalizedId.includes('/resources/js/utils/validators.js') ||
                        normalizedId.includes('/resources/js/utils/formatters.js') ||
                        normalizedId.includes('/resources/js/utils/bootstrap-init.js') ||
                        normalizedId.includes('/resources/js/utils/lazy-loader.js')
                    ) {
                        return 'utils';
                    }

                    // Módulos de páginas
                    if (
                        normalizedId.includes('/resources/js/modules/VentaManager.js') ||
                        normalizedId.includes('/resources/js/modules/LavadosManager.js') ||
                        normalizedId.includes('/resources/js/modules/EstacionamientoManager.js')
                    ) {
                        return 'modules';
                    }

                    return undefined;
                },
                // Nombres de archivo con hash para cache busting
                entryFileNames: 'assets/[name].[hash].js',
                chunkFileNames: 'assets/[name].[hash].js',
                assetFileNames: 'assets/[name].[hash].[ext]',
            },
        },
        
        // Minificación
        minify: 'terser',
        terserOptions: {
            compress: {
                drop_console: true, // Eliminar console.log en producción
                drop_debugger: true,
                pure_funcs: ['console.log', 'console.info'],
            },
            format: {
                comments: false, // Eliminar comentarios
            },
        },
        
        // Source maps solo en desarrollo
        sourcemap: false,
        
        // CSS code splitting
        cssCodeSplit: true,
        
        // Optimización de assets
        assetsInlineLimit: 4096, // Inline assets < 4kb
    },
    
    // Optimización para desarrollo
    server: {
        hmr: {
            overlay: true, // Mostrar errores en overlay
        },
        // Puerto por defecto de Vite
        port: 5173,
        strictPort: false,
    },
    
    // Resolución de módulos
    resolve: {
        alias: {
            '@': resolve(__dirname, './resources/js'),
            '@core': resolve(__dirname, './resources/js/core'),
            '@utils': resolve(__dirname, './resources/js/utils'),
            '@modules': resolve(__dirname, './resources/js/modules'),
            '@pages': resolve(__dirname, './resources/js/pages'),
            '@components': resolve(__dirname, './resources/js/components'),
        },
    },
    
    // Optimización de dependencias
    optimizeDeps: {
        include: ['axios', 'lodash', 'alpinejs'],
        exclude: [], // Excluir dependencias que no deben pre-bundlearse
    },
});
