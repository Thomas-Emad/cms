// resources/js/app.ts
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue, route } from 'ziggy-js';
import '../css/app.css';

// Login.vue and others call route(...) as a bare global (script setup,
// not Options API), so it needs to exist on `window` explicitly - the
// ZiggyVue Vue plugin alone only wires up `this.route()`/`this.$route`.
window.route = route;

createInertiaApp({
    title: (title) => `${title} - Grand Horizon`,
    resolve: (name) =>
        resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
});
