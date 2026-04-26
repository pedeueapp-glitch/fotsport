<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';

defineProps({
    photographers: Array
});
</script>

<template>
    <Head>
        <title>Encontre um Fotógrafo | Fotsport</title>
        <meta name="description" content="Conheça os melhores fotógrafos esportivos do Brasil. Veja portfólios, contrate profissionais para seu evento e eternize seus momentos.">
        <meta name="keywords" content="contratar fotógrafo esportivo, profissionais fotografia, fotsport fotógrafos, portfólio fotógrafo corrida">
    </Head>

    <div class="min-h-screen bg-white flex flex-col font-sans text-brand-dark">
        <Navbar />

        <main class="flex-grow pt-32">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="flex flex-col items-center mb-16 text-center">
                    <span class="text-[9px] font-black text-brand-orange uppercase tracking-[.4em] mb-4">Profissionais</span>
                    <h1 class="text-4xl md:text-6xl font-black uppercase tracking-tighter leading-none mb-6">
                        Preciso de um <span class="text-brand-blue">Fotógrafo</span>
                    </h1>
                    <p class="text-gray-400 text-lg font-medium max-w-2xl">
                        Conheça os profissionais que eternizam os melhores momentos do esporte.
                    </p>
                    <div class="h-1 w-12 bg-brand-blue rounded-full mt-8"></div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
                    <Link v-for="photographer in photographers" :key="photographer.id" :href="route('store.photographer', photographer.slug)" 
                          class="group flex flex-col items-center text-center p-6 rounded-3xl hover:bg-gray-50 transition-all duration-500">
                        
                        <div class="w-32 h-32 md:w-40 md:h-40 rounded-full overflow-hidden mb-6 border-4 border-white shadow-xl group-hover:shadow-2xl group-hover:scale-105 transition-all duration-500 relative bg-gray-100">
                            <img v-if="photographer.avatar" :src="'/' + photographer.avatar" class="w-full h-full object-cover">
                            <div v-else class="w-full h-full bg-brand-dark flex items-center justify-center text-white font-black text-3xl">
                                {{ photographer.name.charAt(0) }}
                            </div>
                            <div class="absolute inset-0 bg-brand-blue/20 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        </div>

                        <h3 class="font-black text-lg text-brand-dark uppercase tracking-tight group-hover:text-brand-blue transition-colors flex items-center gap-1.5">
                            {{ photographer.name }}
                            <svg v-if="photographer.is_verified" class="w-4 h-4 text-blue-500 fill-current" viewBox="0 0 24 24">
                                <path fill-rule="evenodd" d="M8.603 3.799A4.49 4.49 0 0 1 12 2.25c1.357 0 2.573.6 3.397 1.549a4.49 4.49 0 0 1 3.498 1.307 4.491 4.491 0 0 1 1.307 3.497A4.49 4.49 0 0 1 21.75 12a4.49 4.49 0 0 1-1.549 3.397 4.491 4.491 0 0 1-1.307 3.497 4.491 4.491 0 0 1-3.497 1.307A4.49 4.49 0 0 1 12 21.75a4.49 4.49 0 0 1-3.397-1.549 4.49 4.49 0 0 1-3.498-1.306 4.491 4.491 0 0 1-1.307-3.498A4.49 4.49 0 0 1 2.25 12c0-1.357.6-2.573 1.549-3.397a4.49 4.49 0 0 1 1.307-3.497 4.49 4.49 0 0 1 3.497-1.307Zm7.007 6.387a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule="evenodd" />
                            </svg>
                        </h3>
                        <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest mt-1">Ver Portfólio →</span>
                    </Link>
                </div>

                <div v-if="photographers.length === 0" class="py-24 text-center">
                    <p class="text-[10px] font-black text-gray-300 uppercase tracking-[0.4em]">Nenhum fotógrafo encontrado no momento.</p>
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
