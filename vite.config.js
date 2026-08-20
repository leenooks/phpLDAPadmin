import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

// Rewrites all url() references inside the compiled CSS from absolute paths
// (e.g. /build/assets/fa-solid-900.woff2) to relative ones (./fa-solid-900.woff2).
// This means fonts and images resolve correctly regardless of the sub-path the
// app is served from (e.g. https://example.com/samba/build/assets/... works
// because ./fa-solid-900.woff2 is relative to the CSS file, not the origin root).
// The <link> and <script> tags emitted by @vite still use absolute paths via
// Laravel's asset() helper, which respects APP_URL / ASSET_URL as normal.
function relativeCssAssetsPlugin() {
    return {
        name: 'relative-css-assets',
        enforce: 'post',
        generateBundle(_, bundle) {
            for (const file of Object.values(bundle)) {
                if (file.type === 'asset' && file.fileName.endsWith('.css')) {
                    // Replace absolute /build/assets/... URLs with relative ./...
                    file.source = file.source.replace(
                        /url\(\/build\/assets\//g,
                        'url(./'
                    );
                }
            }
        },
    };
}

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
        relativeCssAssetsPlugin(),
    ],
    build: {
        // Ensure fonts/images referenced from SCSS are inlined or emitted correctly
        assetsInlineLimit: 0,
    },
});
