import '../css/app.css';

import { createApp, h, type DefineComponent } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createPinia } from 'pinia';
import ui from '@nuxt/ui/vue-plugin';
import UApp from '@nuxt/ui/components/App.vue';

const appName = import.meta.env.VITE_APP_NAME || 'CV Studio';

createInertiaApp({
    title: (title) => (title ? `${title} — ${appName}` : appName),

    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./pages/**/*.vue'),
        ),

    setup({ el, App, props, plugin }) {
        // `UApp` fournit les contextes dont dependent UTooltip, UModal et les
        // toasts de Nuxt UI. Sans lui, ces composants levent a l'execution.
        createApp({ render: () => h(UApp, () => h(App, props)) })
            .use(plugin)
            .use(createPinia())
            .use(ui)
            .mount(el);
    },

    progress: {
        color: '#174d94',
    },
});
