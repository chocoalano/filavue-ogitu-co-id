import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import ui from '@nuxt/ui/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.ts',
                'resources/css/filament/control-panel/theme.css',
            ],
            ssr: 'resources/js/ssr.ts',
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        ui({
            router: 'inertia',
            ui: {
                colors: {
                    primary: 'zinc',
                },
            },
        }),
    ],
    ssr: {
        // @nuxt/ui menggunakan '#imports' (Nuxt subpath) yang hanya dikenali bundler.
        // Dengan noExternal, Vite mem-bundle @nuxt/ui ke output SSR sehingga
        // plugin @nuxt/ui/vite dapat me-resolve '#imports' → 'vue' saat build,
        // menghindari ERR_PACKAGE_IMPORT_NOT_DEFINED di Node.js runtime.
        noExternal: ['@nuxt/ui'],
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
