import inertia from '@inertiajs/vite';
import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import path from 'node:path';
import { defineConfig } from 'vite';

const modules = ['Notification', 'UserManagement','Grievance','Report','Log','Master','Frontend', 'Setting'];

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.tsx'],
            // Blade only. JS/TSX changes are handled by React Fast Refresh.
            refresh: ['resources/views/**', 'Modules/*/resources/views/**'],
            fonts: [
                bunny('IBM Plex Sans', { weights: [400, 500, 600] }),
                bunny('IBM Plex Mono', { weights: [400, 500] }),
                bunny('Source Serif 4', { weights: [400, 500, 600] }),
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
            // adds a new module alias by extending the `modules` array above
            ...Object.fromEntries(
                modules.map((name) => [
                    `@modules/${name}`,
                    path.resolve(
                        import.meta.dirname,
                        `Modules/${name}/resources/js`,
                    ),
                ]),
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
                        id.includes('node_modules/react-dom/')
                    ) {
                        return 'vendor';
                    }
                },
            },
        },
    },
});
