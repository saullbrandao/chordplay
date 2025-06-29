import '../css/app.css';
import './bootstrap';

import { createInertiaApp, Head } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { initializeTheme } from './Composables/useTheme';

const appName = import.meta.env.VITE_APP_NAME || 'ChordPlay';

initializeTheme();

createInertiaApp({
    title: (title) => `${title} | ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .component('Head', Head)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

console.log(sbf);
