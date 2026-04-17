import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy/dist/vue.m';
import { toast } from '@/utils/swal';

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue, Ziggy);

        return app.mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

router.on('navigate', (event) => {
    const flash = event.detail.page.props.flash;
    if (flash?.success) {
        toast.fire({ icon: 'success', title: flash.success });
    }
    if (flash?.error) {
        toast.fire({ icon: 'error', title: flash.error });
    }
});
