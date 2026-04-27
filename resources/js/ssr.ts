import { createSSRApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import createServer from '@inertiajs/vue3/server';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { renderToString } from 'vue/server-renderer';

// NuxtUIPlugin tidak di-mount di SSR karena @nuxt/ui menggunakan '#imports'
// (Nuxt-specific subpath) yang tidak kompatibel dengan Node.js ESM standar.
// Plugin tetap aktif di sisi client via app.ts.
createServer((page) =>
    createInertiaApp({
        page,
        render: renderToString,
        title: (title) => `${title} — ${import.meta.env.VITE_APP_NAME ?? 'App'}`,
        resolve: (name) =>
            resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
        setup({ App, props, plugin }) {
            return createSSRApp({ render: () => h(App, props) })
                .use(plugin);
        },
    }),
);
