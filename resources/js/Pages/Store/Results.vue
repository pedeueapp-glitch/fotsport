<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Footer from '@/Components/Footer.vue';
import Navbar from '@/Components/Navbar.vue';
import CheckoutModal from '@/Components/CheckoutModal.vue';
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

        <CheckoutModal
            v-if="checkoutData"
            :show="showCheckoutModal"
            :preference-id="checkoutData.preferenceId"
            :public-key="checkoutData.publicKey"
            :total="checkoutData.total"
            :items-count="checkoutData.itemsCount"
            :init-point="checkoutData.initPoint"
            @close="() => { showCheckoutModal = false; checkoutData = null; }"
        />

        <main class="flex-grow max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 w-full mb-32">
            
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
                <div class="mt-10 h-1.5 w-20 bg-brand-blue rounded-full"></div>
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

            <!-- Grid de Fotos (Estilo Dashboard) -->
            <div v-else class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
                <div v-for="(photo, index) in photos" :key="photo.id" 
                     class="group bg-white rounded-[2.5rem] overflow-hidden shadow-sm hover:shadow-2xl transition-all duration-500 border border-gray-100 flex flex-col">
                    
                    <div class="aspect-square overflow-hidden relative cursor-pointer" @click="openLightbox(index)">
                        <img :src="'/' + photo.watermarked_path" class="w-full h-full object-cover group-hover:scale-110 transition duration-700" />
                        
                        <div class="absolute inset-0 bg-brand-dark/40 transition-opacity flex items-center justify-center backdrop-blur-sm"
                             :class="selectedPhotos.includes(photo.id) ? 'opacity-100' : 'opacity-0 group-hover:opacity-100'">
                            <div class="w-16 h-16 rounded-2xl flex items-center justify-center shadow-2xl transform transition-transform duration-500"
                                 :class="selectedPhotos.includes(photo.id) ? 'bg-green-500 text-white scale-100' : 'bg-white text-brand-dark scale-50 group-hover:scale-100'">
                                <svg v-if="selectedPhotos.includes(photo.id)" class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                <svg v-else class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" /></svg>
                            </div>
                        </div>

                        <div v-if="selectedPhotos.includes(photo.id)" class="absolute top-6 right-6">
                            <span class="bg-green-500 text-white px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest shadow-xl">
                                Selecionado
                            </span>
                        </div>
                    </div>

                    <div class="p-8 flex-grow flex flex-col">
                        <div class="mb-8">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="h-1 w-4 bg-brand-orange rounded-full"></div>
                                <span class="text-[10px] font-black text-brand-orange uppercase tracking-widest">{{ photo.event ? photo.event.name : 'Evento' }}</span>
                            </div>
                            <h3 class="text-xl font-black text-brand-dark uppercase tracking-tighter line-clamp-1">Registro #{{ photo.id }}</h3>
                            <p class="text-2xl font-black text-brand-blue mt-2">R$ {{ Number(photo.price).toFixed(2) }}</p>
                        </div>

                        <div class="mt-auto">
                            <button @click="togglePhoto(photo.id)"
                                    class="w-full flex items-center justify-center gap-3 px-6 py-4 text-[11px] font-black uppercase tracking-[.15em] rounded-2xl transition-all shadow-xl active:scale-95"
                                    :class="selectedPhotos.includes(photo.id) 
                                        ? 'bg-gray-100 text-gray-400 border-2 border-transparent' 
                                        : 'bg-brand-dark text-white hover:bg-black shadow-brand-dark/10'">
                                <svg v-if="selectedPhotos.includes(photo.id)" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
                                {{ selectedPhotos.includes(photo.id) ? 'Remover' : 'Selecionar' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <Footer />
        
        <!-- Barra de Checkout Flutuante -->
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
                            <p class="text-white font-black text-xl uppercase tracking-tighter">Fotos Encontradas</p>
                            <button @click="selectedPhotos = []" class="text-[10px] text-white/40 hover:text-white uppercase font-black tracking-widest transition">Limpar Tudo</button>
                        </div>
                    </div>

                    <div class="flex items-center gap-10">
                        <div class="text-center md:text-right">
                            <p class="text-[10px] text-brand-orange uppercase font-black tracking-[.3em]">Total</p>
                            <p class="text-white font-black text-3xl">R$ {{ totalAmount.toFixed(2) }}</p>
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
