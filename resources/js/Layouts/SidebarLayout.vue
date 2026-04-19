<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';

const props = defineProps({
    title: String,
    subtitle: String,
    menuItems: Array
});

const page = usePage();
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex flex-col font-sans text-brand-dark">
        <Navbar />

        <div class="flex-grow flex max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-8 gap-8">
            <!-- Sidebar -->
            <aside class="w-64 flex-shrink-0 hidden md:block">
                <div class="bg-white border border-gray-100 rounded-xl p-6 sticky top-36 shadow-sm">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-6 px-4">Menu de Gestão</p>
                    <nav class="space-y-1">
                        <Link v-for="item in menuItems" :key="item.route" 
                              :href="route(item.route)"
                              :class="route().current(item.active || item.route) ? 'bg-brand-blue text-white shadow-lg' : 'text-gray-500 hover:bg-gray-50 hover:text-brand-dark'"
                              class="flex items-center gap-3 px-4 py-3.5 rounded-lg font-bold text-xs transition-all uppercase tracking-tight">
                            <span v-html="item.icon" class="w-4 h-4 opacity-70"></span>
                            {{ item.label }}
                        </Link>
                    </nav>
                </div>
            </aside>

            <!-- Content Area -->
            <main class="flex-grow">
                <div class="bg-white border border-gray-100 rounded-xl p-8 md:p-12 shadow-sm min-h-[600px]">
                    <header class="mb-10" v-if="title">
                        <h1 class="text-3xl font-black text-brand-dark uppercase tracking-tighter">{{ title }}</h1>
                        <p v-if="subtitle" class="text-sm text-gray-400 font-medium">{{ subtitle }}</p>
                    </header>
                    <slot />
                </div>
            </main>
        </div>

        <Footer />
    </div>
</template>
