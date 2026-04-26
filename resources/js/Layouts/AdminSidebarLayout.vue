<script setup>
import { Link } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';

defineProps({
    title: String,
    subtitle: String,
});

const menuItems = [
    {
        label: 'Fotógrafos',
        route: 'admin.photographers.index',
        active: 'admin.photographers.*',
        icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>'
    },
    {
        label: 'Clientes',
        route: 'admin.customers.index',
        active: 'admin.customers.*',
        icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>'
    },
    {
        label: 'Eventos',
        route: 'admin.events.index',
        active: 'admin.events.*',
        icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>'
    },
    {
        label: 'Saques',
        route: 'admin.withdrawals.index',
        active: 'admin.withdrawals.*',
        icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
    },
    {
        label: 'Faturamento',
        route: 'admin.billing.index',
        active: 'admin.billing.*',
        icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>'
    },
    {
        label: 'Marcas',
        route: 'admin.brands.index',
        active: 'admin.brands.*',
        icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/></svg>'
    },
    {
        label: 'Carrossel Hero',
        route: 'admin.hero.index',
        active: 'admin.hero.*',
        icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>'
    },
    {
        label: 'Suporte',
        route: 'admin.support.index',
        active: 'admin.support.*',
        icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>'
    },
    {
        label: 'Configurações',
        route: 'admin.settings.index',
        active: 'admin.settings.*',
        icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>'
    },
];
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex flex-col font-sans text-brand-dark">
        <Navbar />

        <div class="flex-grow flex max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-8 gap-8">
            <!-- Sidebar fixa -->
            <aside class="w-64 flex-shrink-0 hidden md:block">
                <div class="bg-white border border-gray-100 rounded-xl p-6 sticky top-36 shadow-sm">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-6 px-4">SuperAdmin</p>
                    <nav class="space-y-1">
                        <Link
                            v-for="item in menuItems"
                            :key="item.route"
                            :href="route(item.route)"
                            :class="route().current(item.active || item.route)
                                ? 'bg-brand-blue text-white shadow-lg shadow-brand-blue/20'
                                : 'text-gray-500 hover:bg-gray-50 hover:text-brand-dark'"
                            class="flex items-center gap-3 px-4 py-3.5 rounded-lg font-bold text-xs transition-all uppercase tracking-tight"
                        >
                            <span v-html="item.icon" class="w-4 h-4 opacity-70 flex-shrink-0"></span>
                            {{ item.label }}
                        </Link>
                    </nav>

                    <div class="mt-8 pt-8 border-t border-gray-50 px-4">
                        <Link :href="route('profile.edit')" class="flex items-center gap-3 text-gray-400 hover:text-brand-dark transition-all group">
                             <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center group-hover:bg-brand-orange/10 group-hover:text-brand-orange transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                             </div>
                             <span class="text-[9px] font-black uppercase tracking-widest">Meu Perfil</span>
                        </Link>
                    </div>
                </div>
            </aside>

            <!-- Content Area -->
            <main class="flex-grow min-w-0">
                <!-- Mobile Admin Navigation (Quick links) -->
                <div class="md:hidden w-full mb-6">
                    <div class="bg-white border border-gray-100 rounded-xl p-2 flex overflow-x-auto no-scrollbar gap-2 shadow-sm">
                        <Link
                            v-for="item in menuItems.slice(0, 5)"
                            :key="'mob-' + item.route"
                            :href="route(item.route)"
                            :class="route().current(item.active || item.route)
                                ? 'bg-brand-blue text-white shadow-md'
                                : 'text-gray-500 bg-gray-50'"
                            class="flex-shrink-0 flex items-center gap-2 px-4 py-3 rounded-lg font-bold text-[9px] uppercase tracking-tighter transition-all"
                        >
                            <span v-html="item.icon" class="w-3 h-3 opacity-70"></span>
                            {{ item.label }}
                        </Link>
                    </div>
                </div>

                <div class="bg-white border border-gray-100 rounded-xl p-8 md:p-10 shadow-sm min-h-[600px]">
                    <header v-if="title" class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <h1 class="text-2xl font-black text-brand-dark uppercase tracking-tighter">{{ title }}</h1>
                            <p v-if="subtitle" class="text-sm text-gray-400 font-medium mt-1">{{ subtitle }}</p>
                        </div>
                        <!-- Slot para botões de ação no header -->
                        <slot name="actions" />
                    </header>
                    <slot />
                </div>
            </main>
        </div>

        <Footer />
    </div>
</template>
