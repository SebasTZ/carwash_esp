import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { resolve } from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css', 
                'resources/js/app.js',
                // Módulos de páginas específicas
                'resources/js/modules/VentaManager.js',
                'resources/js/modules/CompraManager.js',
                'resources/js/modules/LavadosManager.js',
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
                manualChunks: {
                    // Separar vendors grandes
                    'vendor-core': ['axios', 'lodash'],
                    // Utilidades en su propio chunk
                    'utils': [
                        './resources/js/utils/notifications.js',
                        './resources/js/utils/validators.js',
                        './resources/js/utils/formatters.js',
                        './resources/js/utils/bootstrap-init.js',
                        './resources/js/utils/lazy-loader.js',
                    ],
                    // Módulos de páginas
                    'modules': [
                        './resources/js/modules/VentaManager.js',
                        './resources/js/modules/CompraManager.js',
                        './resources/js/modules/LavadosManager.js',
                    ],
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
            '@utils': resolve(__dirname, './resources/js/utils'),
            '@modules': resolve(__dirname, './resources/js/modules'),
            '@pages': resolve(__dirname, './resources/js/pages'),
        },
    },
    
    // Optimización de dependencias
    optimizeDeps: {
        include: ['axios', 'lodash'],
        exclude: [], // Excluir dependencias que no deben pre-bundlearse
    },
});
