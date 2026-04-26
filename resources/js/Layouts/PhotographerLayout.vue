<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';
import FlashMessages from '@/Components/FlashMessages.vue';

const props = defineProps({
    title: String,
    subtitle: String,
});

const page = usePage();

const menuItems = [
    { label: 'Meus Eventos', route: 'dashboard', active: 'dashboard', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>' },
    { label: 'Financeiro', route: 'financial.index', active: 'financial.*', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>' },
    { label: 'Suporte', route: 'photographer.support.index', active: 'photographer.support.*', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>' },
];
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex flex-col font-sans text-brand-dark">
        <Navbar />
        <FlashMessages />

        <div class="flex-grow flex max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-8 gap-8">
            <!-- Sidebar -->
            <aside class="w-64 flex-shrink-0 hidden md:block">
                <div class="bg-white border border-gray-100 rounded-xl p-6 sticky top-28 shadow-sm">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-6 px-4">Menu Fotógrafo</p>
                    <nav class="space-y-1">
                        <Link v-for="item in menuItems" :key="item.route" 
                              :href="route(item.route)"
                              :class="route().current(item.active || item.route) ? 'bg-brand-blue text-white shadow-lg shadow-brand-blue/20' : 'text-gray-500 hover:bg-gray-50 hover:text-brand-dark'"
                              class="flex items-center gap-3 px-4 py-3.5 rounded-lg font-black text-[10px] transition-all uppercase tracking-tight">
                            <span v-html="item.icon" class="w-4 h-4" :class="route().current(item.active || item.route) ? 'opacity-100' : 'opacity-40'"></span>
                            {{ item.label }}
                        </Link>
                    </nav>

                    <div class="mt-8 pt-8 border-t border-gray-50 px-4">
                        <Link :href="route('profile.edit')" class="flex items-center gap-3 text-gray-400 hover:text-brand-dark transition-all group">
                             <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center group-hover:bg-brand-orange/10 group-hover:text-brand-orange transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                             </div>
                             <span class="text-[9px] font-black uppercase tracking-widest">Configurações</span>
                        </Link>
                    </div>
                </div>
            </aside>

            <!-- Content Area -->
            <main class="flex-grow min-w-0">
                <div class="bg-white border border-gray-100 rounded-2xl p-6 md:p-10 shadow-sm min-h-[70vh]">
                    <header class="mb-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-6" v-if="title">
                        <div>
                            <h1 class="text-3xl font-black text-brand-dark uppercase tracking-tighter leading-none mb-2">{{ title }}</h1>
                            <p v-if="subtitle" class="text-sm text-gray-400 font-bold uppercase tracking-widest text-[9px]">{{ subtitle }}</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <slot name="actions" />
                        </div>
                    </header>
                    <slot />
                </div>
            </main>
        </div>

        <Footer />
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap');
.font-sans { font-family: 'Inter', sans-serif; }
</style>
