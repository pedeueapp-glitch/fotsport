<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';

defineProps({
    events: Array
});
</script>

<template>
    <Head title="Meus Eventos" />

    <div class="min-h-screen bg-white flex flex-col font-sans text-brand-dark">
        <Navbar />

        <main class="flex-grow max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 w-full">
            <!-- Header -->
            <header class="mb-20">
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-8">
                    <div class="max-w-2xl">
                        <div class="inline-flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 bg-brand-orange/10 rounded-xl flex items-center justify-center text-brand-orange">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            </div>
                            <span class="text-[10px] font-black text-brand-orange uppercase tracking-[.4em]">Painel do Fotógrafo</span>
                        </div>
                        <h1 class="text-4xl md:text-6xl font-black text-brand-dark uppercase tracking-tighter mb-4">
                            Gerenciar <span class="text-brand-blue">Eventos</span>
                        </h1>
                        <p class="text-gray-500 text-lg font-medium">
                            Bem-vindo de volta! Aqui você pode criar novos eventos e gerenciar suas fotos.
                        </p>
                    </div>
                    
                    <Link :href="route('events.create')" class="group flex items-center gap-3 px-10 py-5 bg-brand-dark text-white font-black text-xs uppercase tracking-widest rounded-2xl transition-all hover:bg-brand-blue shadow-2xl shadow-brand-dark/10 active:scale-95">
                        <svg class="w-5 h-5 transition-transform group-hover:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                        </svg>
                        Criar Novo Evento
                    </Link>
                </div>
                <div class="mt-10 h-1.5 w-20 bg-brand-orange rounded-full"></div>
            </header>

            <!-- Empty State -->
            <div v-if="events.length === 0" class="bg-gray-50 rounded-[3rem] p-20 text-center border-2 border-dashed border-gray-200">
                <div class="w-24 h-24 bg-white rounded-3xl flex items-center justify-center mx-auto mb-8 shadow-xl rotate-3">
                    <svg class="w-12 h-12 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
                <h3 class="text-2xl font-black text-brand-dark uppercase tracking-tight mb-4">Nenhum evento criado</h3>
                <p class="text-gray-500 mb-10 max-w-md mx-auto font-medium">Você ainda não possui eventos cadastrados. Comece criando seu primeiro evento para subir fotos.</p>
                <Link :href="route('events.create')" class="inline-flex items-center px-10 py-5 bg-brand-blue text-white text-xs font-black uppercase tracking-[.2em] rounded-2xl hover:bg-brand-blue-hover transition shadow-2xl shadow-brand-blue/20 active:scale-95">
                    Criar Agora
                </Link>
            </div>

            <!-- Event Grid -->
            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
                <Link v-for="event in events" :key="event.id" :href="route('events.show', event)" class="group bg-white rounded-[2.5rem] overflow-hidden shadow-sm hover:shadow-2xl transition-all duration-500 border border-gray-100 flex flex-col transform hover:-translate-y-2">
                    
                    <div class="h-64 w-full bg-brand-dark relative overflow-hidden">
                        <template v-if="event.photos && event.photos.length > 0">
                            <img :src="'/' + event.photos[0].watermarked_path" class="w-full h-full object-cover opacity-80 group-hover:opacity-100 group-hover:scale-110 transition duration-700" />
                        </template>
                        <template v-else>
                            <div class="w-full h-full flex items-center justify-center bg-gray-100 text-gray-300">
                                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        </template>
                        <div class="absolute top-6 left-6 z-10">
                            <span class="bg-white/90 backdrop-blur-md text-brand-dark text-[10px] font-black px-4 py-2 rounded-xl uppercase tracking-widest shadow-xl">
                                {{ event.date }}
                            </span>
                        </div>
                        <div class="absolute inset-0 bg-gradient-to-t from-brand-dark/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    </div>
                    
                    <div class="p-8 flex-grow flex flex-col">
                        <div class="mb-8">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="h-1 w-4 bg-brand-orange rounded-full"></div>
                                <span class="text-[10px] font-black text-brand-orange uppercase tracking-widest">{{ event.location || 'Local não informado' }}</span>
                            </div>
                            <h3 class="text-xl font-black text-brand-dark uppercase tracking-tighter line-clamp-1 group-hover:text-brand-blue transition-colors">{{ event.name }}</h3>
                        </div>

                        <div class="mt-auto border-t border-gray-50 pt-6 flex justify-between items-center">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-brand-blue/10 rounded-xl flex items-center justify-center text-brand-blue">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <span class="text-xs font-black text-brand-dark uppercase tracking-widest">
                                    {{ event.photos_count || 0 }} Fotos
                                </span>
                            </div>
                            <div class="flex items-center gap-1 text-[10px] font-black text-brand-blue uppercase tracking-widest group-hover:gap-3 transition-all">
                                Gerenciar <span class="text-brand-orange">→</span>
                            </div>
                        </div>
                    </div>
                </Link>
            </div>
        </main>

        <Footer />
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;900&display=swap');

.font-sans {
    font-family: 'Inter', sans-serif;
}
</style>
