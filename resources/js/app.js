import './bootstrap';
import '../css/app.css';
import '../css/protection.css';

// Bloqueio de Segurança Anti-Cópia
if (typeof window !== 'undefined') {
    // Bloqueia clique direito
    document.addEventListener('contextmenu', e => e.preventDefault());

    // Bloqueia atalhos de teclado (F12, Ctrl+U, Ctrl+S, Ctrl+Shift+I)
    document.addEventListener('keydown', (e) => {
        if (
            e.keyCode === 123 || // F12
            (e.ctrlKey && e.shiftKey && (e.keyCode === 73 || e.keyCode === 74)) || // Ctrl+Shift+I/J
            (e.ctrlKey && e.keyCode === 85) || // Ctrl+U (Fonte)
            (e.ctrlKey && e.keyCode === 83)    // Ctrl+S (Salvar)
        ) {
            e.preventDefault();
            return false;
        }
    });
}

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
