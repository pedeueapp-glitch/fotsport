<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';

defineProps({
    events: Array
});
</script>

<template>
    <Head title="Vitrine de Fotos | Fotsport" />

    <div class="min-h-screen bg-white flex flex-col font-sans text-brand-dark">
        <Navbar />

        <!-- Header -->
        <main class="flex-grow max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 w-full">
            <header class="mb-20">
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-8">
                    <div class="max-w-2xl">
                        <div class="inline-flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 bg-brand-orange/10 rounded-xl flex items-center justify-center text-brand-orange">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            </div>
                            <span class="text-[10px] font-black text-brand-orange uppercase tracking-[.4em]">Vitrine de Fotos</span>
                        </div>
                        <h1 class="text-4xl md:text-6xl font-black text-brand-dark uppercase tracking-tighter mb-4">
                            Encontre sua <span class="text-brand-blue">Performance</span>
                        </h1>
                        <p class="text-gray-500 text-lg font-medium">
                            Explore os eventos, utilize nossa busca inteligente por IA e reviva seus melhores momentos no esporte.
                        </p>
                    </div>
                </div>
                <div class="mt-10 h-1.5 w-20 bg-brand-orange rounded-full"></div>
            </header>

            <!-- Event Section Header -->
            <div class="flex flex-col items-center mb-16 text-center">
                <h3 class="text-2xl md:text-4xl font-black text-brand-dark uppercase tracking-tighter mb-4">
                    Eventos <span class="text-brand-orange">Em Destaque</span>
                </h3>
                <div class="h-1 w-16 bg-brand-blue rounded-full"></div>
            </div>
                
            <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
                <Link v-for="event in events" :key="event.id" :href="route('store.event', event)" 
                      class="group bg-white border border-gray-100 rounded-3xl shadow-xl hover:shadow-2xl transition-all duration-500 overflow-hidden flex flex-col h-full transform hover:-translate-y-2">
                    
                    <div class="h-64 w-full bg-brand-dark relative overflow-hidden">
                        <template v-if="event.photos && event.photos.length > 0">
                            <img :src="'/' + event.photos[0].watermarked_path" 
                                 class="w-full h-full object-cover opacity-80 group-hover:opacity-100 group-hover:scale-110 transition duration-700" />
                        </template>
                        <div class="absolute top-6 left-6 z-10">
                            <span class="bg-white/90 backdrop-blur-md text-brand-dark text-[10px] font-black px-4 py-2 rounded-xl uppercase tracking-widest shadow-xl">
                                {{ event.date }}
                            </span>
                        </div>
                        <div class="absolute inset-0 bg-gradient-to-t from-brand-dark/80 to-transparent opacity-60"></div>
                    </div>

                    <div class="p-8 flex-grow flex flex-col relative bg-white">
                        <h3 class="font-black text-xl text-brand-dark mb-3 group-hover:text-brand-orange transition duration-300 uppercase tracking-tight leading-tight">
                            {{ event.name }}
                        </h3>
                        <p class="text-sm text-gray-400 mb-8 line-clamp-2 leading-relaxed flex-grow font-medium">{{ event.location }}</p>
                        
                        <div class="pt-6 border-t border-gray-50 flex justify-between items-center">
                            <div class="flex items-center gap-2">
                                <div class="p-2 bg-brand-orange/10 rounded-lg">
                                    <svg class="w-4 h-4 text-brand-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <span class="text-xs font-black text-brand-dark uppercase tracking-widest">
                                    {{ event.photos_count || 0 }} Fotos
                                </span>
                            </div>
                            <div class="flex items-center gap-1 text-[10px] font-black text-brand-blue uppercase tracking-widest group-hover:gap-3 transition-all duration-300">
                                Buscar <span class="text-brand-orange">→</span>
                            </div>
                        </div>
                    </div>
                </Link>
            </div>
            
            <!-- Call to Action Section -->
            <div class="mt-32 bg-brand-blue rounded-[3rem] p-12 md:p-24 relative overflow-hidden shadow-2xl">
                <div class="absolute -right-24 -top-24 w-96 h-96 bg-brand-orange/10 rounded-full blur-3xl"></div>
                <div class="absolute -left-24 -bottom-24 w-96 h-96 bg-white/5 rounded-full blur-3xl"></div>
                
                <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-12 text-white">
                    <div class="max-w-xl text-center md:text-left">
                        <h2 class="text-4xl md:text-6xl font-black uppercase tracking-tighter leading-none mb-6">
                            É Fotógrafo <br/> <span class="text-brand-orange">Esportivo?</span>
                        </h2>
                        <p class="text-lg md:text-xl font-medium text-white/70 mb-10 leading-relaxed">
                            Junte-se a maior plataforma de fotos esportivas. Suba suas fotos, use nossa tecnologia de IA e venda em minutos.
                        </p>
                        <Link :href="route('login')" class="inline-block bg-white text-brand-blue hover:bg-brand-orange hover:text-white px-10 py-5 rounded-full font-black text-sm transition-all uppercase tracking-[0.2em] shadow-xl hover:shadow-brand-orange/30">
                            Começar Agora
                        </Link>
                    </div>
                    <div class="h-1 bg-white/10 w-full md:w-1 md:h-64 rounded-full"></div>
                    <div class="flex flex-col items-center gap-4 text-center">
                        <div class="text-6xl font-black text-brand-orange">100%</div>
                        <div class="text-xs font-bold uppercase tracking-[0.3em]">IA Integrada</div>
                    </div>
                </div>
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
