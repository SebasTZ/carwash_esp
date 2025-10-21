import { defineConfig } from 'vitest/config';
import { resolve } from 'path';

export default defineConfig({
    test: {
        globals: true,
        environment: 'happy-dom',
        setupFiles: ['./tests/setup.js'],
        coverage: {
            provider: 'v8',
            reporter: ['text', 'json', 'html'],
            exclude: [
                'node_modules/',
                'tests/',
                'vendor/',
                'public/',
                'storage/'
            ]
        }
    },
    resolve: {
        alias: {
            '@': resolve(__dirname, './resources/js'),
            '@components': resolve(__dirname, './resources/js/components'),
            '@core': resolve(__dirname, './resources/js/core'),
            '@utils': resolve(__dirname, './resources/js/utils')
        }
    }
});
