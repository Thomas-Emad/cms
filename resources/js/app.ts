// resources/js/app.ts
import { createApp, DefineComponent, h, Transition } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue, route } from 'ziggy-js';
import { vReveal, vRevealMount } from '@/lib/motion';
import { router } from '@inertiajs/vue3';
import { t } from '@/i18n';
import '../css/app.css';

// Login.vue and others call route(...) as a bare global (script setup,
// not Options API), so it needs to exist on `window` explicitly - the
// ZiggyVue Vue plugin alone only wires up `this.route()`/`this.$route`.
window.route = route;
const pages = import.meta.glob('./Pages/**/*.vue');

function syncDocumentLocale(page: any) {
    const locale = (page?.props?.locale as string) || 'en';
    const dir = (page?.props?.direction as string) || (locale === 'ar' ? 'rtl' : 'ltr');
    document.documentElement.lang = locale;
    document.documentElement.dir = dir;
}
createInertiaApp({
    title: (title) => `${title} - Grand Horizon`,

    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            pages,
        ) as Promise<DefineComponent>,

    setup({ el, App, props, plugin }) {
        syncDocumentLocale(props.initialPage);

        router.on('navigate', (event) => {
            syncDocumentLocale(event.detail.page);
        });

        const app = createApp({
            render: () =>
                h(
                    Transition,
                    {
                        name: 'page-fade',
                        mode: 'out-in',
                    },
                    () =>
                        h(App, {
                            ...props,
                            key: props.initialPage?.component,
                        }),
                ),
        });

        app.config.globalProperties.$t = t;

        app
            .use(plugin)
            .use(ZiggyVue)
            .directive('reveal', vReveal)
            .directive('reveal-mount', vRevealMount)
            .mount(el);
    },
});
