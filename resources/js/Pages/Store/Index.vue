<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';
import BrandCarousel from '@/Components/BrandCarousel.vue';

const props = defineProps({
    todayEvents: Array,
    otherEvents: Array,
    brands: Array,
    heroItems: Array
});

const todayScrollRef = ref(null);

const scrollToday = (direction) => {
    if (todayScrollRef.value) {
        const scrollAmount = todayScrollRef.value.clientWidth * 0.8;
        todayScrollRef.value.scrollBy({
            left: direction === 'left' ? -scrollAmount : scrollAmount,
            behavior: 'smooth'
        });
    }
};

const openPhotographerRegistration = () => {
    window.dispatchEvent(new CustomEvent('show-photographer-modal'));
};
</script>

<template>
    <Head>
        <title>Vitrine de Fotos | Fotsport</title>
        <meta name="description" content="Explore os melhores eventos esportivos e encontre suas fotos. Use nosso reconhecimento facial para achar seus momentos na corrida, ciclismo e muito mais.">
        <meta name="keywords" content="fotos de eventos, galeria esportiva, fotsport eventos, fotos de corridas brasil">
        
        <meta property="og:title" content="Vitrine de Fotos | Fotsport" />
        <meta property="og:description" content="Busque suas fotos nos maiores eventos esportivos do país." />
    </Head>

    <div class="min-h-screen bg-white flex flex-col font-sans text-brand-dark">
        <Navbar />

        <main class="flex-grow">
            <!-- Brand Carousel Section -->
            <section class="bg-white py-4 border-b border-gray-50">
                <div class="max-w-7xl mx-auto">
                    <BrandCarousel :brands="brands" />
                </div>
            </section>

            <!-- Seção de Eventos de HOJE (Carrossel) -->
            <section v-if="todayEvents.length > 0" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 w-full overflow-hidden">
                <div class="flex items-center justify-between mb-8">
                    <div class="flex flex-col">
                        <h3 class="text-xl md:text-2xl font-black text-brand-dark uppercase tracking-tighter mb-1">
                            Acontecendo <span class="text-brand-orange">Hoje</span>
                        </h3>
                        <div class="h-1.5 w-12 bg-brand-blue rounded-full"></div>
                    </div>
                    
                    <!-- Navegação do Carrossel -->
                    <div class="flex gap-2">
                        <button @click="scrollToday('left')" class="p-3 rounded-full bg-gray-50 hover:bg-brand-blue hover:text-white transition-all shadow-sm active:scale-95">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" /></svg>
                        </button>
                        <button @click="scrollToday('right')" class="p-3 rounded-full bg-gray-50 hover:bg-brand-blue hover:text-white transition-all shadow-sm active:scale-95">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" /></svg>
                        </button>
                    </div>
                </div>

                <div 
                    ref="todayScrollRef"
                    class="flex gap-4 md:gap-6 overflow-x-auto snap-x snap-mandatory no-scrollbar pb-8 scroll-smooth"
                >
                    <Link 
                        v-for="event in todayEvents" 
                        :key="event.id" 
                        :href="route('store.event', event)" 
                        class="flex-shrink-0 w-[85%] md:w-[calc(33.333%-16px)] snap-start group bg-white border border-gray-100 rounded-2xl shadow-sm hover:shadow-xl transition-all duration-500 overflow-hidden flex flex-col h-full"
                    >
                        <div class="h-56 md:h-64 w-full bg-brand-dark relative overflow-hidden shrink-0">
                            <template v-if="event.photos && event.photos.length > 0">
                                <img :src="'/' + event.photos[0].watermarked_path" 
                                    class="w-full h-full object-cover opacity-80 group-hover:scale-110 transition duration-700 blur-[0px] group-hover:blur-0" />
                            </template>
                            <div class="absolute top-4 left-4 z-10">
                                <span class="bg-brand-orange text-white text-[9px] font-black px-3 py-1.5 rounded-full uppercase tracking-widest shadow-lg animate-pulse">
                                    Ao Vivo
                                </span>
                            </div>
                        </div>

                        <div class="p-6 md:p-8 flex-grow flex flex-col bg-white">
                            <p class="text-[9px] font-black text-brand-blue uppercase tracking-[0.2em] mb-2">{{ event.location }}</p>
                            <div class="h-10 md:h-12 flex items-start mb-4"> <!-- Altura fixa para o título -->
                                <h3 class="font-black text-lg md:text-xl text-brand-dark group-hover:text-brand-blue transition duration-300 uppercase tracking-tighter line-clamp-2 leading-tight">
                                    {{ event.name }}
                                </h3>
                            </div>
                            
                            <div class="mt-auto pt-6 border-t border-gray-50 flex justify-between items-center text-[10px] font-black uppercase tracking-widest text-gray-400 group-hover:text-brand-dark transition-colors">
                                <span>{{ event.photos_count || 0 }} Fotos</span>
                                <span class="bg-brand-blue/5 text-brand-blue px-3 py-1 rounded-lg group-hover:bg-brand-blue group-hover:text-white transition-all">Buscar →</span>
                            </div>
                        </div>
                    </Link>
                </div>
            </section>

            <!-- Seção de Outros Eventos (Grade) -->
            <section id="eventos" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 w-full border-t border-gray-50">
                <div class="flex flex-col items-center mb-12 text-center">
                    <h3 class="text-xl md:text-2xl font-black text-brand-dark uppercase tracking-tighter mb-2">
                        Explorar <span class="text-brand-blue">Galeria</span>
                    </h3>
                    <p class="text-gray-400 text-[10px] uppercase font-bold tracking-widest">Encontre suas fotos em eventos passados e futuros</p>
                </div>
                    
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <Link v-for="event in otherEvents" :key="event.id" :href="route('store.event', event)" 
                        class="group bg-white border border-gray-100 rounded-2xl shadow-sm hover:shadow-lg transition-all duration-300 overflow-hidden flex flex-col h-full">
                        
                        <div class="h-48 w-full bg-brand-dark relative overflow-hidden shrink-0">
                            <template v-if="event.photos && event.photos.length > 0">
                                <img :src="'/' + event.photos[0].watermarked_path" 
                                    class="w-full h-full object-cover opacity-80 group-hover:scale-105 transition duration-500" />
                            </template>
                            <div class="absolute top-3 left-3 z-10">
                                <span class="bg-white/90 backdrop-blur-md text-brand-dark text-[8px] font-black px-2 py-1 rounded-lg uppercase tracking-widest shadow-lg">
                                    {{ event.date }}
                                </span>
                            </div>
                        </div>

                        <div class="p-6 flex-grow flex flex-col bg-white">
                            <p class="text-[8px] font-black text-brand-orange uppercase tracking-widest mb-1">{{ event.location }}</p>
                            <div class="h-10 flex items-start mb-3"> <!-- Altura fixa para o título -->
                                <h3 class="font-black text-base md:text-lg text-brand-dark group-hover:text-brand-blue transition duration-300 uppercase tracking-tight line-clamp-2 leading-tight">
                                    {{ event.name }}
                                </h3>
                            </div>
                            
                            <div class="mt-auto pt-4 border-t border-gray-50 flex justify-between items-center text-[9px] font-black uppercase tracking-widest text-gray-400 group-hover:text-brand-dark transition-colors">
                                <span>{{ event.photos_count || 0 }} Fotos</span>
                                <span>Buscar →</span>
                            </div>
                        </div>
                    </Link>
                </div>
            </section>

            <!-- Seção de Incentivo ao Fotógrafo -->
            <section class="bg-brand-dark py-24 relative overflow-hidden">
                <!-- Background Pattern -->
                <div class="absolute inset-0 opacity-5">
                    <div class="absolute inset-0" style="background-image: radial-gradient(#fff 1px, transparent 1px); background-size: 40px 40px;"></div>
                </div>

                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                        <div>
                            <h2 class="text-4xl md:text-5xl font-black text-white uppercase tracking-tighter leading-none mb-8">
                                Transforme seus clicks em <span class="text-brand-blue">Lucro Real</span>
                            </h2>
                            <p class="text-gray-400 text-lg mb-10 leading-relaxed font-medium">
                                A Fotsport é a parceira ideal para o fotógrafo esportivo que busca escala, tecnologia e agilidade no recebimento.
                            </p>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 mb-12">
                                <div class="flex items-start gap-4">
                                    <div class="w-12 h-12 bg-brand-blue/10 rounded-xl flex items-center justify-center shrink-0">
                                        <svg class="w-6 h-6 text-brand-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                    </div>
                                    <div>
                                        <h4 class="text-white font-black uppercase tracking-widest text-xs mb-2">IA de Reconhecimento</h4>
                                        <p class="text-gray-500 text-[10px] leading-relaxed uppercase font-bold tracking-widest">Suas fotos encontradas em segundos por rosto ou numeral.</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-4">
                                    <div class="w-12 h-12 bg-brand-orange/10 rounded-xl flex items-center justify-center shrink-0">
                                        <svg class="w-6 h-6 text-brand-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                    <div>
                                        <h4 class="text-white font-black uppercase tracking-widest text-xs mb-2">Taxas Competitivas</h4>
                                        <p class="text-gray-500 text-[10px] leading-relaxed uppercase font-bold tracking-widest">A menor comissão do mercado para você lucrar mais.</p>
                                    </div>
                                </div>
                            </div>

                            <button @click="openPhotographerRegistration" 
                                    class="bg-brand-blue hover:bg-brand-orange text-white px-10 py-5 rounded-2xl font-black text-xs uppercase tracking-[0.2em] transition-all duration-300 shadow-2xl shadow-brand-blue/20 hover:scale-105 active:scale-95">
                                Começar a Vender Agora
                            </button>
                        </div>

                        <div class="relative">
                            <div class="aspect-square bg-gradient-to-tr from-brand-blue/20 to-brand-orange/20 rounded-3xl overflow-hidden border border-white/10 p-8 flex items-center justify-center">
                                <div class="text-center">
                                    <div class="text-7xl font-black text-white mb-2 uppercase tracking-tighter">Venda +</div>
                                    <div class="text-brand-orange text-sm font-black uppercase tracking-[0.4em]">Automatize seu fluxo</div>
                                </div>
                            </div>
                            <!-- Floating badges -->
                            <div class="absolute -top-6 -right-6 bg-white p-4 rounded-2xl shadow-2xl">
                                <div class="text-brand-dark font-black text-xl leading-none">R$ 0,00</div>
                                <div class="text-gray-400 text-[8px] font-bold uppercase tracking-widest mt-1 text-center">Taxa de Adesão</div>
                            </div>
                        </div>
                    </div>
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


<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;900&display=swap');

.font-sans {
    font-family: 'Inter', sans-serif;
}
</style>
