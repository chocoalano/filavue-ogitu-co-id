import './bootstrap';
import { createApp, createSSRApp, h, type DefineComponent } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import NuxtUIPlugin from '@nuxt/ui/vue-plugin';

createInertiaApp({
    title: (title) => `${title} — ${import.meta.env.VITE_APP_NAME ?? 'App'}`,
    resolve: (name) =>
        resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob<DefineComponent>('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const createVueApp = el.innerHTML ? createSSRApp : createApp;
        createVueApp({ render: () => h(App, props) })
            .use(plugin)
            .use(NuxtUIPlugin)
            .mount(el);
    },
    progress: {
        color: '#71717a',
    },
});
