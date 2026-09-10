import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    css: {
        preprocessorOptions: {
            scss: {
                api: 'modern-compiler',
                // Allow partials in resources/sass (design system) to be
                // referenced by short name from any entry point, including
                // the storefront theme (resources/views/themes/xylo/sass).
                loadPaths: ['resources/sass'],
            },
        },
    },
    build: {
        cssCodeSplit: true,
        minify: 'esbuild',
        chunkSizeWarningLimit: 1000,
    },
    plugins: [
        laravel({
            input: [
                './resources/sass/app.scss',
                './resources/sass/admin.scss',
                './resources/js/app.js',
                './resources/views/themes/xylo/sass/app.scss',
                './resources/views/themes/xylo/js/app.js',
                './resources/views/themes/xylo/css/animate.min.css',
                './resources/views/themes/xylo/css/slick.css',
                './resources/views/themes/xylo/js/main.js',
                './resources/views/themes/xylo/js/slick.min.js',
            ],
            refresh: true,
        }),
    ],
});
