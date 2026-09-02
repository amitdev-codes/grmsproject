import inertia from '@inertiajs/vite';
import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import { defineConfig } from 'vite';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.tsx'],
            refresh: [
                'resources/views/**',
                'Modules/*/resources/views/**',
                'Modules/*/resources/js/**',
            ],
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
            ],
        }),
        inertia(),
        react({
            babel: {
                plugins: ['babel-plugin-react-compiler'],
            },
        }),
        tailwindcss(),
        wayfinder({
            formVariants: true,
        }),
    ],
    resolve: {
        alias: {
            '@': path.resolve(import.meta.dirname, 'resources/js'),
            '@modules/Notification': path.resolve(
                import.meta.dirname,
                'Modules/Notification/resources/js',
            ),
            '@modules/UserManagement': path.resolve(
                import.meta.dirname,
                'Modules/UserManagement/resources/js',
            ),
            '@modules/Frontend': path.resolve(
                import.meta.dirname,
                'Modules/Frontend/resources/js',
            ),
            '@modules/Setting': path.resolve(
                import.meta.dirname,
                'Modules/Setting/resources/js',
            ),
        },
    },
    build: {
        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (
                        id.includes('node_modules/@inertiajs') ||
                        id.includes('node_modules/react/') ||
                        id.includes('node_modules/react-dom/') ||
                        id.includes('node_modules/react-router')
                    ) {
                        return 'vendor';
                    }

                    if (id.includes('/Modules/')) {
                        const match = id.match(/\/Modules\/(\w+)\//);
                        if (match) {
                            return `module-${match[1].toLowerCase()}`;
                        }
                    }
                },
            },
        },
    },
});
