<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';

const props = defineProps({
    photographer: Object,
    photos: Object
});

// Helper para garantir que o caminho da imagem esteja correto
const getImageUrl = (path) => {
    if (!path) return null;
    if (path.startsWith('http')) return path;
    // Se o path já começa com storage/, removemos para não duplicar na concatenação se necessário
    // Mas geralmente o link correto é /storage/ + path (se o path não tiver storage/)
    // Ou simplesmente / + path se o path já tiver storage/
    if (path.startsWith('storage/')) return '/' + path;
    return '/storage/' + path;
};

const coverStyle = computed(() => {
    const url = getImageUrl(props.photographer?.cover_photo);
    return url ? { backgroundImage: `url(${url})` } : {};
});

</script>

<template>
    <Head :title="photographer.name + ' | Portfólio Profissional'" />

    <div class="min-h-screen bg-[#fafafa] font-sans text-slate-900">
        <Navbar />
        
        <main class="pb-24">
            <!-- Header Minimalista e Elegante -->
            <div class="relative w-full h-[40vh] md:h-[50vh] overflow-hidden bg-slate-200">
                <div v-if="photographer.cover_photo" 
                     class="absolute inset-0 bg-cover bg-center bg-no-sync transition-transform duration-1000 hover:scale-105"
                     :style="coverStyle">
                </div>
                <div class="absolute inset-0 bg-black/20"></div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="relative -mt-32 md:-mt-40 z-10">
                    <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/50 p-8 md:p-12">
                        <div class="flex flex-col md:flex-row items-center md:items-start gap-8 md:gap-12">
                            <!-- Avatar com Moldura Elegante -->
                            <div class="shrink-0">
                                <div class="w-40 h-40 md:w-56 md:h-56 rounded-[2.5rem] p-1.5 bg-gradient-to-tr from-brand-blue to-indigo-400 shadow-2xl">
                                    <div class="w-full h-full rounded-[2.2rem] overflow-hidden bg-slate-100 border-4 border-white">
                                        <img v-if="photographer.avatar" 
                                             :src="getImageUrl(photographer.avatar)" 
                                             class="w-full h-full object-cover" 
                                             :alt="photographer.name">
                                        <div v-else class="w-full h-full flex items-center justify-center bg-slate-50 text-slate-300">
                                            <i class="fas fa-user text-6xl"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Perfil e Bio -->
                            <div class="flex-1 text-center md:text-left pt-4">
                                <div class="inline-block px-3 py-1 bg-slate-100 rounded-full text-[10px] font-bold uppercase tracking-widest text-slate-500 mb-4">
                                    Fotógrafo Parceiro
                                </div>
                                <h1 class="text-4xl md:text-5xl font-black text-slate-900 tracking-tight mb-2 uppercase">
                                    {{ photographer.name }}
                                </h1>
                                <p class="text-slate-400 font-medium italic text-lg mb-6">Expertise em capturar momentos inesquecíveis</p>
                                
                                <p v-if="photographer.bio" class="text-slate-600 leading-relaxed text-base max-w-2xl mb-8">
                                    {{ photographer.bio }}
                                </p>

                                <!-- Contatos Integrados -->
                                <div class="flex flex-wrap justify-center md:justify-start gap-3">
                                    <a v-if="photographer.whatsapp" 
                                       :href="'https://wa.me/55' + photographer.whatsapp.replace(/\D/g, '')" 
                                       target="_blank"
                                       class="flex items-center gap-2 px-6 py-3 bg-[#25D366] hover:bg-[#128C7E] text-white rounded-2xl transition-all duration-300 shadow-lg shadow-green-200 font-bold text-sm uppercase tracking-wider">
                                        <i class="fab fa-whatsapp text-lg"></i> WhatsApp
                                    </a>
                                    <a v-if="photographer.instagram" 
                                       :href="'https://instagram.com/' + photographer.instagram.replace('@', '')" 
                                       target="_blank"
                                       class="flex items-center gap-2 px-6 py-3 bg-white border border-slate-100 text-slate-900 hover:bg-slate-50 rounded-2xl transition-all duration-300 shadow-sm font-bold text-sm uppercase tracking-wider">
                                        <i class="fab fa-instagram text-lg text-pink-500"></i> Instagram
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Galeria Reformulada -->
                <div class="mt-20">
                    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-12">
                        <div>
                            <h2 class="text-3xl font-black text-slate-900 uppercase tracking-tighter">Portfólio Recente</h2>
                            <div class="h-1.5 w-20 bg-brand-blue mt-2 rounded-full"></div>
                        </div>
                        <div class="text-slate-400 font-bold text-xs uppercase tracking-[0.2em]">
                            Exibindo {{ photos.data.length }} de {{ photos.total }} obras
                        </div>
                    </div>

                    <div v-if="photos.data.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                        <div v-for="photo in photos.data" :key="photo.id" 
                             class="group relative bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-2xl hover:-translate-y-2 transition-all duration-500">
                            
                            <div class="aspect-[4/5] overflow-hidden">
                                <img :src="'/' + photo.watermarked_path" 
                                     class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" 
                                     loading="lazy"
                                     alt="Fotografia esportiva">
                            </div>

                            <!-- Overlay de Informação -->
                            <div class="absolute inset-x-0 bottom-0 p-6 translate-y-4 opacity-0 group-hover:translate-y-0 group-hover:opacity-100 transition-all duration-300">
                                <div class="bg-white/95 backdrop-blur-sm p-4 rounded-xl shadow-xl">
                                    <div v-if="photo.event" class="mb-3">
                                        <span class="text-[9px] font-black text-brand-blue uppercase tracking-widest block mb-1">Evento</span>
                                        <h3 class="text-sm font-bold text-slate-900 line-clamp-1 uppercase">{{ photo.event.name }}</h3>
                                    </div>
                                    <Link :href="route('store.event', photo.event?.slug)" 
                                          class="block w-full text-center py-2.5 bg-slate-900 text-white text-[10px] font-black uppercase tracking-widest rounded-lg hover:bg-brand-blue transition-colors">
                                        Ver Coleção Completa
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Estado Vazio -->
                    <div v-else class="text-center py-32 bg-white rounded-[3rem] border border-dashed border-slate-200 shadow-inner">
                        <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i class="fas fa-camera-retro text-4xl text-slate-200"></i>
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 uppercase mb-2">Galeria em Curadoria</h3>
                        <p class="text-slate-400 max-w-xs mx-auto text-sm">Este fotógrafo está preparando novos registros incríveis para você.</p>
                    </div>

                    <!-- Paginação Design Moderno -->
                    <div v-if="photos.links && photos.links.length > 3" class="mt-20 flex justify-center">
                        <div class="inline-flex gap-2 p-2 bg-white rounded-2xl shadow-sm border border-slate-100">
                            <template v-for="(link, k) in photos.links" :key="k">
                                <div v-if="link.url === null" 
                                     class="w-10 h-10 flex items-center justify-center text-slate-300 pointer-events-none" 
                                     v-html="link.label"></div>
                                <Link v-else 
                                      :href="link.url" 
                                      class="w-10 h-10 flex items-center justify-center rounded-xl text-xs font-black transition-all" 
                                      :class="link.active ? 'bg-brand-blue text-white shadow-lg shadow-brand-blue/30' : 'text-slate-500 hover:bg-slate-50'" 
                                      v-html="link.label"></Link>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <Footer />
    </div>
</template>

<style scoped>
@import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css');

.bg-no-sync {
    background-size: cover;
    background-position: center;
}
</style>
