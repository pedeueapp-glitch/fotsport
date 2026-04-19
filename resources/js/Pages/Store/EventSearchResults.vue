<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';

const props = defineProps({
    events: Array,
    searchTerm: String
});
</script>

<template>
    <Head :title="'Resultados para: ' + searchTerm + ' | Fotsport'" />

    <div class="min-h-screen bg-white flex flex-col font-sans text-brand-dark">
        <Navbar />

        <main class="flex-grow">
            <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 w-full">
                <!-- Search Header -->
                <div class="mb-12">
                    <h1 class="text-3xl md:text-5xl font-black uppercase tracking-tighter leading-none mb-4">
                        Resultados para: <span class="text-brand-blue">"{{ searchTerm }}"</span>
                    </h1>
                    <p class="text-gray-400 font-bold uppercase tracking-widest text-[10px]">
                        Encontramos {{ events.length }} {{ events.length === 1 ? 'evento' : 'eventos' }}
                    </p>
                </div>

                <!-- Results Grid -->
                <div v-if="events.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <Link v-for="event in events" :key="event.id" :href="route('store.event', event)" 
                        class="group bg-white border border-gray-100 rounded-xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden flex flex-col h-full">
                        
                        <div class="h-64 w-full bg-brand-dark relative overflow-hidden">
                            <template v-if="event.photos && event.photos.length > 0">
                                <img :src="'/' + event.photos[0].watermarked_path" 
                                    class="w-full h-full object-cover opacity-80 group-hover:scale-105 transition duration-500" />
                            </template>
                            <div class="absolute top-4 left-4 z-10">
                                <span class="bg-white/90 backdrop-blur-md text-brand-dark text-[9px] font-black px-3 py-1.5 rounded-lg uppercase tracking-widest shadow-lg">
                                    {{ event.date }}
                                </span>
                            </div>
                        </div>

                        <div class="p-8 flex-grow flex flex-col relative bg-white">
                            <p class="text-[9px] font-black text-brand-orange uppercase tracking-widest mb-1">{{ event.location }}</p>
                            <h3 class="font-black text-xl text-brand-dark mb-4 group-hover:text-brand-blue transition duration-300 uppercase tracking-tight line-clamp-1">
                                {{ event.name }}
                            </h3>
                            
                            <div class="pt-6 border-t border-gray-50 flex justify-between items-center text-[10px] font-black uppercase tracking-widest text-gray-400 group-hover:text-brand-dark transition-colors">
                                <span>{{ event.photos_count || 0 }} Fotos</span>
                                <span>Buscar →</span>
                            </div>
                        </div>
                    </Link>
                </div>

                <!-- Empty State -->
                <div v-else class="py-20 flex flex-col items-center text-center">
                    <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center text-gray-200 mb-6">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <h2 class="text-2xl font-black text-brand-dark uppercase tracking-tight mb-4">Nenhum evento encontrado</h2>
                    <p class="text-gray-500 max-w-md mb-8">Não encontramos nenhum evento com esse nome. Tente buscar por termos mais genéricos ou verifique a grafia.</p>
                    <Link :href="route('store.index')" class="bg-brand-blue text-white px-8 py-4 rounded-lg font-black text-[10px] transition-all uppercase tracking-widest shadow-lg hover:bg-brand-dark">
                        Voltar para a Home
                    </Link>
                </div>
            </section>
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
