// resources/js/app.ts
import { createApp, h, Transition } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue, route } from 'ziggy-js';
import { vReveal, vRevealMount } from '@/lib/motion';
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
        createApp({
            // Subtle cross-page fade/settle (see .page-fade-* in app.css).
            // Keyed on the Inertia component name so switching pages (not
            // just prop updates within a page) triggers the transition.
            render: () =>
                h(Transition, { name: 'page-fade', mode: 'out-in' }, () => h(App, { ...props, key: props.initialPage?.component })),
        })
            .use(plugin)
            .use(ZiggyVue)
            .directive('reveal', vReveal)
            .directive('reveal-mount', vRevealMount)
            .mount(el);
    },
});
