import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/js/app.js',
                'resources/themes/architect/src/init.js',
            ],
            refresh: true,
        }),
    ],
    build: {
        // Ensure fonts/images referenced from SCSS are inlined or emitted correctly
        assetsInlineLimit: 0,
    },
});
