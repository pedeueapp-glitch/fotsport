<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Footer from '@/Components/Footer.vue';
import Navbar from '@/Components/Navbar.vue';
import CheckoutModal from '@/Components/CheckoutModal.vue';
import FlashMessages from '@/Components/FlashMessages.vue';
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';

const props = defineProps({
    photos: Array
});

const showCheckoutModal = ref(false);
const checkoutData = ref(null);

watch(() => usePage().props.flash?.checkout_data, (newVal) => {
    if (newVal) {
        checkoutData.value = newVal;
        showCheckoutModal.value = true;
    }
}, { immediate: true });

// Recupera fotos da sessão após login e dispara checkout
watch(() => usePage().props.flash?.pending_checkout_photos, (newVal) => {
    if (newVal && newVal.length > 0) {
        selectedPhotos.value = [...newVal];
    }
}, { immediate: true });

watch(() => usePage().props.flash?.trigger_checkout, (newVal) => {
    if (newVal) {
        checkout();
    }
}, { immediate: true });

const selectedPhotos = ref([]);
const lightboxIndex = ref(null);

const togglePhoto = (id) => {
    if (selectedPhotos.value.includes(id)) {
        selectedPhotos.value = selectedPhotos.value.filter(pid => pid !== id);
    } else {
        selectedPhotos.value.push(id);
    }
};

const form = useForm({
    photo_ids: []
});

const checkout = () => {
    form.photo_ids = selectedPhotos.value;
    form.post(route('store.checkout'), {
        preserveScroll: true,
        preserveState: true,
    });
};

const openLightbox = (index) => { lightboxIndex.value = index; };
const closeLightbox = () => { lightboxIndex.value = null; };
const prevPhoto = () => { lightboxIndex.value = (lightboxIndex.value - 1 + props.photos.length) % props.photos.length; };
const nextPhoto = () => { lightboxIndex.value = (lightboxIndex.value + 1) % props.photos.length; };

const currentPhoto = computed(() => lightboxIndex.value !== null ? props.photos[lightboxIndex.value] : null);

const handleKey = (e) => {
    if (lightboxIndex.value === null) return;
    if (e.key === 'Escape')      closeLightbox();
    if (e.key === 'ArrowLeft')   prevPhoto();
    if (e.key === 'ArrowRight')  nextPhoto();
};

onMounted(() => window.addEventListener('keydown', handleKey));
onUnmounted(() => window.removeEventListener('keydown', handleKey));

const totalAmount = computed(() => {
    return props.photos
        .filter(p => selectedPhotos.value.includes(p.id))
        .reduce((sum, p) => sum + parseFloat(p.price), 0);
});

const portfolioUrl = (user) => {
    if (!user) return null;
    return user.slug ? route('store.photographer', user.slug) : null;
};
</script>

<template>
    <Head title="Minhas Fotos Encontradas | Fotsport" />

    <div class="min-h-screen bg-white flex flex-col font-sans text-brand-dark">
        <Navbar />
        <FlashMessages />

        <CheckoutModal
            v-if="checkoutData"
            :show="showCheckoutModal"
            :pix-qrcode="checkoutData.pix_qrcode"
            :pix-copy-paste="checkoutData.pix_copy_paste"
            :txid="checkoutData.txid"
            :total="checkoutData.total"
            :items-count="checkoutData.itemsCount"
            @close="() => { showCheckoutModal = false; checkoutData = null; }"
        />

        <main class="flex-grow w-full py-20 px-4 sm:px-6 lg:px-8 max-w-[1600px] mx-auto">
            
            <header class="mb-20">
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-8 text-center md:text-left">
                    <div class="max-w-2xl">
                        <div class="inline-flex items-center gap-2 mb-4">
                            <div class="h-px w-8 bg-brand-orange"></div>
                            <span class="text-[10px] font-black text-brand-orange uppercase tracking-[.4em]">Busca Concluída</span>
                        </div>
                        <h1 class="text-4xl md:text-6xl font-black text-brand-dark uppercase tracking-tighter mb-4">
                            Suas <span class="text-brand-blue">Fotos</span>
                        </h1>
                        <p class="text-gray-500 text-lg font-medium">
                            Nossa inteligência artificial identificou estas fotos para você. Selecione as que mais gostar.
                        </p>
                    </div>
                    
                    <Link :href="route('store.index')" class="px-8 py-4 border-2 border-gray-100 text-gray-400 hover:text-brand-orange hover:border-brand-orange font-black text-xs uppercase tracking-widest rounded-2xl transition-all active:scale-95">
                        Fazer Nova Busca
                    </Link>
                </div>
            </header>
            
            <!-- Estado Vazio -->
            <div v-if="photos.length === 0" class="bg-gray-50 rounded-[3rem] p-20 text-center border-2 border-dashed border-gray-200">
                <div class="p-6 bg-white rounded-full w-24 h-24 flex items-center justify-center mx-auto mb-8 shadow-xl">
                    <svg class="w-10 h-10 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <h3 class="text-xl font-black text-brand-dark uppercase tracking-tighter mb-4">Poxa, nenhuma foto por aqui...</h3>
                <p class="text-gray-400 text-sm font-medium mb-10 max-w-md mx-auto">Tente uma selfie com melhor iluminação, sem óculos de sol ou acessórios que cubram o rosto.</p>
                <Link :href="route('store.index')">
                    <PrimaryButton class="bg-brand-blue hover:bg-brand-blue-hover text-white px-10 py-4 rounded-2xl">
                        Tentar Novamente
                    </PrimaryButton>
                </Link>
            </div>

            <!-- Grid de Fotos (Estilo Imersivo) -->
            <div v-else class="lg:-mx-8 sm:-mx-6 -mx-4">
                <div class="grid grid-cols-2 md:grid-cols-5 gap-0 border-t border-l border-gray-100">
                    <div v-for="(photo, index) in photos" :key="photo.id" 
                         class="group relative aspect-square overflow-hidden border-r border-b border-gray-100 bg-gray-50">
                        
                        <img :src="'/' + photo.watermarked_path" 
                             class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" />
                        
                        <!-- Overlay de Seleção/Info (Clique aqui abre o Lightbox) -->
                        <div @click.self="openLightbox(index)"
                             class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition-all duration-300 flex flex-col justify-between p-4 cursor-zoom-in">
                            
                            <div class="flex justify-between items-start pointer-events-none">
                                <span class="bg-black/50 backdrop-blur-md text-[8px] text-white px-2 py-1 rounded font-black uppercase">#{{ photo.id }}</span>
                                <div @click.stop="togglePhoto(photo.id)" 
                                     class="w-8 h-8 rounded-full flex items-center justify-center cursor-pointer transition-all pointer-events-auto shadow-lg"
                                     :class="selectedPhotos.includes(photo.id) ? 'bg-green-500 text-white scale-110' : 'bg-white/20 text-white hover:bg-white hover:text-brand-dark'">
                                    <svg v-if="selectedPhotos.includes(photo.id)" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                    <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                                </div>
                            </div>

                            <div @click.self="openLightbox(index)" class="flex justify-between items-end pointer-events-none">
                                <p class="text-white font-black text-sm drop-shadow-lg">R$ {{ Number(photo.price).toFixed(2) }}</p>
                                <button class="text-[8px] text-white/70 hover:text-white uppercase font-black tracking-widest transition-colors">Ampliar</button>
                            </div>
                        </div>

                        <!-- Indicador de Selecionada (Sempre visível se selecionada) -->
                        <div v-if="selectedPhotos.includes(photo.id)" 
                             class="absolute top-0 right-0 w-8 h-8 bg-green-500 text-white flex items-center justify-center shadow-lg pointer-events-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <Footer />
        
        <!-- Barra de Checkout Flutuante (Sincronizada com Eventos) -->
        <Transition
            enter-active-class="translate-y-full transition-transform duration-500"
            enter-to-class="translate-y-0"
            leave-active-class="translate-y-0"
            leave-to-class="translate-y-full">

            <div v-if="selectedPhotos.length > 0"
                 class="fixed bottom-10 left-1/2 -translate-x-1/2 w-[calc(100%-2rem)] max-w-4xl bg-brand-dark/90 backdrop-blur-2xl border border-white/10 rounded-[2.5rem] shadow-[0_30px_100px_rgba(0,0,0,0.3)] p-6 z-[100]">
                <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                    <div class="flex items-center gap-6">
                        <div class="relative">
                            <div class="absolute -top-3 -right-3 w-8 h-8 bg-brand-orange text-white text-[10px] font-black rounded-full flex items-center justify-center shadow-lg border-2 border-brand-dark ring-4 ring-white/10">
                                {{ selectedPhotos.length }}
                            </div>
                            <div class="p-4 bg-white/5 rounded-2xl border border-white/10">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            </div>
                        </div>
                        <div>
                            <p class="text-white font-black text-xl uppercase tracking-tighter">Fotos Selecionadas</p>
                            <button @click="selectedPhotos = []" class="text-[10px] text-white/40 hover:text-white uppercase font-black tracking-widest transition">Limpar Tudo</button>
                        </div>
                    </div>

                    <div class="flex items-center gap-10">
                        <div class="text-center md:text-right">
                            <template v-if="selectedPhotos.length >= 2">
                                <p class="text-[10px] text-green-400 uppercase font-black tracking-[.3em] mb-1">
                                    Economia de {{ selectedPhotos.length >= 10 ? '20%' : (selectedPhotos.length >= 5 ? '10%' : '5%') }}
                                </p>
                                <div class="flex items-center gap-3 justify-center md:justify-end">
                                    <span class="text-white/30 line-through text-sm font-bold">R$ {{ photos.filter(p => selectedPhotos.includes(p.id)).reduce((acc, p) => acc + Number(p.price), 0).toFixed(2) }}</span>
                                    <p class="text-white font-black text-3xl">
                                        R$ {{ (photos.filter(p => selectedPhotos.includes(p.id)).reduce((acc, p) => acc + Number(p.price), 0) * (selectedPhotos.length >= 10 ? 0.8 : (selectedPhotos.length >= 5 ? 0.9 : 0.95))).toFixed(2) }}
                                    </p>
                                </div>
                            </template>
                            <template v-else>
                                <p class="text-[10px] text-brand-orange uppercase font-black tracking-[.3em]">Total</p>
                                <p class="text-white font-black text-3xl">R$ {{ photos.filter(p => selectedPhotos.includes(p.id)).reduce((acc, p) => acc + Number(p.price), 0).toFixed(2) }}</p>
                            </template>
                        </div>
                        <button @click="checkout" :disabled="form.processing"
                                class="bg-brand-blue hover:bg-brand-blue-hover text-white px-10 py-5 rounded-3xl font-black text-[11px] uppercase tracking-[.2em] shadow-2xl transition-all active:scale-95 border-none disabled:opacity-50">
                            {{ form.processing ? 'Processando...' : 'Finalizar Pedido →' }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- LIGHTBOX (Estilo Dashboard) -->
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95">

            <div v-if="lightboxIndex !== null"
                 class="fixed inset-0 z-[200] flex items-center justify-center bg-brand-dark/98 backdrop-blur-2xl"
                 @click.self="closeLightbox">

                <!-- UI Topo -->
                <div class="absolute top-8 inset-x-8 flex justify-between items-center z-50">
                    <div class="bg-white/5 border border-white/10 backdrop-blur-md px-6 py-2 rounded-full text-white/50 text-xs font-black tracking-[0.3em] uppercase">
                        {{ lightboxIndex + 1 }} <span class="text-brand-orange px-2">/</span> {{ photos.length }}
                    </div>
                    <button @click="closeLightbox"
                            class="w-12 h-12 rounded-2xl bg-white/5 hover:bg-brand-orange border border-white/10 flex items-center justify-center text-white transition-all duration-300 group">
                        <svg class="w-6 h-6 group-hover:rotate-90 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Navegação -->
                <button @click="prevPhoto"
                        class="absolute left-8 w-16 h-16 rounded-[2rem] bg-white/5 hover:bg-brand-blue border border-white/10 flex items-center justify-center text-white transition-all duration-300 hidden md:flex">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <button @click="nextPhoto"
                        class="absolute right-8 w-16 h-16 rounded-[2rem] bg-white/5 hover:bg-brand-blue border border-white/10 flex items-center justify-center text-white transition-all duration-300 hidden md:flex">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/></svg>
                </button>

                <!-- Imagem + Ações -->
                <div class="max-w-5xl w-full flex flex-col items-center gap-10 px-4">
                    <div class="relative">
                        <img v-if="currentPhoto"
                             :key="lightboxIndex"
                             :src="'/' + currentPhoto.watermarked_path"
                             class="max-h-[70vh] w-auto object-contain rounded-[2.5rem] shadow-[0_0_100px_rgba(0,0,0,0.5)] border-4 border-white/5" />
                    </div>

                    <div v-if="currentPhoto" class="flex flex-col items-center gap-6">
                        <div class="text-center">
                            <p class="text-brand-orange text-[10px] font-black uppercase tracking-[.3em] mb-2">Valor Unitário</p>
                            <h3 class="text-white font-black text-4xl uppercase tracking-tighter">R$ {{ Number(currentPhoto.price).toFixed(2) }}</h3>
                        </div>
                        
                        <div class="flex items-center gap-6 bg-white/5 backdrop-blur-xl border border-white/10 p-2 rounded-[2rem]">
                            <button @click="togglePhoto(currentPhoto.id)"
                                    class="px-12 py-4 rounded-[1.5rem] text-[10px] font-black uppercase tracking-widest shadow-xl transition-all active:scale-95"
                                    :class="selectedPhotos.includes(currentPhoto.id) ? 'bg-green-500 text-white' : 'bg-brand-orange text-white hover:bg-brand-orange-hover'">
                                {{ selectedPhotos.includes(currentPhoto.id) ? '✓ Selecionada' : 'Selecionar Para Compra' }}
                            </button>

                            <div v-if="currentPhoto.user" class="pr-6 pl-4 border-l border-white/10 hidden md:block">
                                <p class="text-[8px] text-white/40 uppercase font-black tracking-widest mb-1">Fotógrafo</p>
                                <a :href="portfolioUrl(currentPhoto.user)" target="_blank" class="text-white text-xs font-bold hover:text-brand-orange transition flex items-center gap-1.5">
                                    {{ currentPhoto.user.name }}
                                    <svg v-if="currentPhoto.user.is_verified" class="w-3.5 h-3.5 text-blue-500 fill-current" viewBox="0 0 24 24">
                                        <path fill-rule="evenodd" d="M8.603 3.799A4.49 4.49 0 0 1 12 2.25c1.357 0 2.573.6 3.397 1.549a4.49 4.49 0 0 1 3.498 1.307 4.491 4.491 0 0 1 1.307 3.497A4.49 4.49 0 0 1 21.75 12a4.49 4.49 0 0 1-1.549 3.397 4.491 4.491 0 0 1-1.307 3.497 4.491 4.491 0 0 1-3.497 1.307A4.49 4.49 0 0 1 12 21.75a4.49 4.49 0 0 1-3.397-1.549 4.49 4.49 0 0 1-3.498-1.306 4.491 4.491 0 0 1-1.307-3.498A4.49 4.49 0 0 1 2.25 12c0-1.357.6-2.573 1.549-3.397a4.49 4.49 0 0 1 1.307-3.497 4.49 4.49 0 0 1 3.497-1.307Zm7.007 6.387a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule="evenodd" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
