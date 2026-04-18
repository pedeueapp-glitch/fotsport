<script setup>
import Navbar from '@/Components/Navbar.vue';
</script>

<template>
    <div class="min-h-screen bg-white">
        <!-- Topo Unificado -->
        <Navbar />

        <!-- Page Heading (Opcional, usado em algumas telas de admin) -->
        <header class="bg-gray-50/50 border-b border-gray-100" v-if="$slots.header">
            <div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8 flex items-center gap-4">
                <div class="h-8 w-1.5 bg-brand-orange rounded-full"></div>
                <div class="font-black text-xl text-brand-dark uppercase tracking-tighter">
                    <slot name="header" />
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main>
            <slot />
        </main>
    </div>
</template>
