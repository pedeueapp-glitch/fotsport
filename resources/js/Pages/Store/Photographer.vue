<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import Footer from '@/Components/Footer.vue';
import Navbar from '@/Components/Navbar.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import CheckoutModal from '@/Components/CheckoutModal.vue';
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';

const props = defineProps({
    photographer: Object,
    photos: Object
});

const showCheckoutModal = ref(false);
const checkoutData = ref(null);

watch(() => usePage().props.flash?.checkout_data, (newVal) => {
    if (newVal) {
        checkoutData.value = newVal;
        showCheckoutModal.value = true;
    }
}, { immediate: true });

// ── Seleção de fotos para compra ──────────────────────────────────────────────
const selectedPhotos = ref([]);

const togglePhoto = (id) => {
    if (selectedPhotos.value.includes(id)) {
        selectedPhotos.value = selectedPhotos.value.filter(pid => pid !== id);
    } else {
        selectedPhotos.value.push(id);
    }
};

const checkoutForm = useForm({ photo_ids: [] });

const checkout = () => {
    if (!usePage().props.auth.customer) {
        window.dispatchEvent(new CustomEvent('show-customer-login'));
        return;
    }
    checkoutForm.photo_ids = selectedPhotos.value;
    checkoutForm.post(route('store.checkout'));
};

// ── Lightbox ─────────────────────────────────────────────────────────────────
const lightboxIndex = ref(null);
const allPhotos = computed(() => props.photos.data ?? []);

const openLightbox = (index) => {
    lightboxIndex.value = index;
};
const closeLightbox = () => {
    lightboxIndex.value = null;
};
const prevPhoto = () => {
    if (lightboxIndex.value === null) return;
    lightboxIndex.value = (lightboxIndex.value - 1 + allPhotos.value.length) % allPhotos.value.length;
};
const nextPhoto = () => {
    if (lightboxIndex.value === null) return;
    lightboxIndex.value = (lightboxIndex.value + 1) % allPhotos.value.length;
};
const currentLightboxPhoto = computed(() =>
    lightboxIndex.value !== null ? allPhotos.value[lightboxIndex.value] : null
);

const handleKeydown = (e) => {
    if (lightboxIndex.value === null) return;
    if (e.key === 'Escape') closeLightbox();
    if (e.key === 'ArrowLeft') prevPhoto();
    if (e.key === 'ArrowRight') nextPhoto();
};

onMounted(() => window.addEventListener('keydown', handleKeydown));
onUnmounted(() => window.removeEventListener('keydown', handleKeydown));
</script>

<template>
    <Head :title="`${photographer.name} | Portfólio Fotsport`" />

    <div class="min-h-screen bg-white flex flex-col text-brand-dark font-sans">
        <Navbar />

        <CheckoutModal
            v-if="checkoutData"
            :show="showCheckoutModal"
            :preference-id="checkoutData.preferenceId"
            :public-key="checkoutData.publicKey"
            :total="checkoutData.total"
            :items-count="checkoutData.itemsCount"
            @close="showCheckoutModal = false"
        />

        <!-- Capa -->
        <div class="w-full h-[50vh] relative flex-shrink-0 bg-brand-dark">
            <img v-if="photographer.cover_photo"
                 :src="'/' + photographer.cover_photo"
                 class="w-full h-full object-cover opacity-60 scale-105"
                 alt="Capa" />
            <div v-else class="w-full h-full bg-gradient-to-br from-brand-blue to-brand-dark opacity-80"></div>
            
            <div class="absolute inset-0 bg-gradient-to-t from-white via-transparent to-transparent"></div>

            <!-- Avatar flutuante -->
            <div class="absolute -bottom-20 left-1/2 transform -translate-x-1/2">
                <div class="w-40 h-40 md:w-48 md:h-48 rounded-[2.5rem] border-[6px] border-white shadow-2xl overflow-hidden bg-white rotate-3 group">
                    <img v-if="photographer.avatar"
                         :src="'/' + photographer.avatar"
                         class="w-full h-full object-cover group-hover:scale-110 transition duration-700"
                         alt="Perfil">
                    <div v-else class="w-full h-full bg-brand-orange flex items-center justify-center text-white font-black text-6xl uppercase transform -rotate-3">
                        {{ photographer.name.charAt(0) }}
                    </div>
                </div>
            </div>
        </div>

        <div class="flex-grow max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 mt-32 mb-40">

            <!-- Informações do Fotógrafo -->
            <div class="max-w-4xl mx-auto text-center mb-24">
                <div class="inline-flex items-center gap-2 mb-6">
                    <div class="h-px w-8 bg-brand-orange"></div>
                    <span class="text-[10px] font-black text-brand-orange uppercase tracking-[.4em]">Proprietário do Portfólio</span>
                    <div class="h-px w-8 bg-brand-orange"></div>
                </div>
                
                <h1 class="text-4xl md:text-7xl font-black tracking-tighter text-brand-dark mb-6 uppercase">{{ photographer.name }}</h1>
                
                <p v-if="photographer.bio" class="text-gray-400 text-lg md:text-xl font-medium mb-12 max-w-2xl mx-auto leading-relaxed italic">
                    "{{ photographer.bio }}"
                </p>

                <!-- Redes Sociais Premium -->
                <div class="flex flex-wrap justify-center gap-6">
                    <a v-if="photographer.instagram"
                       :href="photographer.instagram.startsWith('http') ? photographer.instagram : `https://instagram.com/` + photographer.instagram.replace('@','')"
                       target="_blank"
                       class="group p-1 rounded-[1.5rem] bg-gradient-to-tr from-brand-orange to-pink-500 shadow-xl hover:shadow-brand-orange/30 transition-all duration-300">
                       <div class="bg-white px-8 py-4 rounded-[1.4rem] flex items-center gap-3">
                           <svg class="w-6 h-6 text-brand-orange group-hover:scale-110 transition-transform" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                           <span class="font-black text-xs uppercase tracking-widest text-brand-dark">Instagram</span>
                       </div>
                    </a>

                    <a v-if="photographer.whatsapp"
                       :href="`https://wa.me/` + photographer.whatsapp.replace(/\D/g,'')"
                       target="_blank"
                       class="group p-1 rounded-[1.5rem] bg-green-500 shadow-xl hover:shadow-green-500/30 transition-all duration-300">
                       <div class="bg-white px-8 py-4 rounded-[1.4rem] flex items-center gap-3">
                           <svg class="w-6 h-6 text-green-500 group-hover:scale-110 transition-transform" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 21.365h-.006c-1.579 0-3.118-.426-4.475-1.233l-.321-.192-3.325.87.893-3.238-.21-.334A9.458 9.458 0 013.14 12c0-5.23 4.256-9.486 9.485-9.486s9.485 4.256 9.485 9.486-4.255 9.487-9.485 9.487z"/></svg>
                           <span class="font-black text-xs uppercase tracking-widest text-brand-dark">WhatsApp</span>
                       </div>
                    </a>
                </div>
            </div>

            <!-- Galeria de Fotos -->
            <div class="pt-24">
                <div class="flex flex-col items-center mb-20">
                    <h2 class="text-3xl md:text-5xl font-black text-brand-dark uppercase tracking-tighter mb-4 text-center">
                        Coleção <span class="text-brand-orange">Exclusiva</span>
                    </h2>
                    <div class="h-1.5 w-20 bg-brand-blue rounded-full"></div>
                </div>

                <div v-if="allPhotos.length > 0" class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-10">
                    <div v-for="(photo, index) in allPhotos" :key="photo.id"
                         class="group relative aspect-[3/4] overflow-hidden rounded-[2.5rem] bg-white transition-all duration-500 cursor-pointer border border-transparent hover:shadow-2xl"
                         :class="selectedPhotos.includes(photo.id) ? 'ring-4 ring-brand-orange border-brand-orange shadow-brand-orange/20' : 'hover:border-brand-blue/20'"
                         @click="togglePhoto(photo.id)">

                        <img :src="'/' + photo.watermarked_path"
                             class="w-full h-full object-cover group-hover:scale-110 transition duration-700"
                             alt="Foto">

                        <!-- Identificador de Seleção -->
                        <div v-if="selectedPhotos.includes(photo.id)"
                             class="absolute top-4 right-4 w-10 h-10 bg-brand-orange rounded-2xl flex items-center justify-center text-white shadow-xl z-20 animate-bounce">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"/></svg>
                        </div>

                        <!-- Botão Lightbox -->
                        <button @click.stop="openLightbox(index)"
                                class="absolute top-4 left-4 w-10 h-10 bg-white/90 backdrop-blur-md rounded-2xl flex items-center justify-center text-brand-dark hover:text-brand-orange shadow-xl transition-all opacity-0 group-hover:opacity-100 z-20">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
                        </button>

                        <!-- Info Hover -->
                        <div class="absolute inset-x-0 bottom-0 p-6 bg-gradient-to-t from-brand-dark via-brand-dark/80 to-transparent pt-12 transform translate-y-full group-hover:translate-y-0 transition-transform duration-500 z-10">
                            <p class="text-white text-[10px] font-black uppercase tracking-widest leading-tight mb-2 opacity-60">{{ photo.event ? photo.event.name : 'Série Limitada' }}</p>
                            <p class="text-brand-orange text-xl font-black leading-none">R$ {{ Number(photo.price).toFixed(2) }}</p>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center py-40 bg-gray-50 rounded-[3rem] border-2 border-dashed border-gray-200">
                    <svg class="mx-auto h-20 w-20 text-gray-200 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <h3 class="text-2xl font-black text-gray-300 uppercase tracking-widest">Galeria em Construção</h3>
                    <p class="mt-4 text-gray-400 font-medium">Este artista ainda está preparando suas criações.</p>
                </div>

                <!-- Paginação Premium -->
                <div v-if="photos.links && photos.links.length > 3" class="mt-24 flex justify-center flex-wrap gap-2">
                    <template v-for="(link, key) in photos.links" :key="key">
                        <Link v-if="link.url" :href="link.url" 
                               v-html="link.label.replace('&laquo; Previous', '←').replace('Next &raquo;', '→')"
                               class="min-w-[48px] h-12 flex items-center justify-center rounded-2xl border-2 transition-all duration-300 font-black text-[10px] uppercase px-4"
                               :class="link.active 
                                 ? 'bg-brand-orange border-brand-orange text-white shadow-xl shadow-brand-orange/20 scale-105 z-10' 
                                 : 'bg-white border-gray-100 text-gray-400 hover:border-brand-blue hover:text-brand-blue'">
                        </Link>
                    </template>
                </div>
            </div>

        </div>

        <Footer />
    </div>

    <!-- ── LIGHTBOX ── -->
    <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="opacity-0 scale-95"
        enter-to-class="opacity-100 scale-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100 scale-100"
        leave-to-class="opacity-0 scale-95">

        <div v-if="lightboxIndex !== null"
             class="fixed inset-0 z-[200] flex items-center justify-center bg-brand-dark/98 backdrop-blur-3xl"
             @click.self="closeLightbox">

            <!-- UI Topo -->
            <div class="absolute top-8 inset-x-8 flex justify-between items-center z-50">
                <div class="bg-white/5 border border-white/10 backdrop-blur-md px-6 py-2 rounded-full text-white/50 text-xs font-black tracking-[0.3em] uppercase">
                    {{ lightboxIndex + 1 }} <span class="text-brand-orange px-2">/</span> {{ allPhotos.length }}
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
                    <img v-if="currentLightboxPhoto"
                         :key="lightboxIndex"
                         :src="'/' + currentLightboxPhoto.watermarked_path"
                         class="max-h-[75vh] w-auto object-contain rounded-[2.5rem] shadow-[0_0_100px_rgba(0,0,0,0.5)] border-4 border-white/5" />
                    
                    <div class="absolute -bottom-6 left-1/2 -translate-x-1/2 flex items-center gap-8 bg-brand-dark/80 backdrop-blur-xl border border-white/10 px-10 py-5 rounded-[2rem] shadow-2xl">
                        <div class="text-center">
                            <p class="text-[10px] text-brand-orange uppercase font-black tracking-widest leading-none mb-1">Preço Atual</p>
                            <p class="text-white font-black text-3xl">R$ {{ Number(currentLightboxPhoto.price).toFixed(2) }}</p>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button @click="togglePhoto(currentLightboxPhoto.id)"
                            class="px-14 py-5 rounded-full font-black text-sm uppercase tracking-[.25em] transition-all duration-300 shadow-2xl active:scale-95"
                            :class="selectedPhotos.includes(currentLightboxPhoto.id)
                                ? 'bg-green-500 text-white hover:bg-green-600'
                                : 'bg-brand-orange text-white hover:bg-brand-orange-hover'">
                        {{ selectedPhotos.includes(currentLightboxPhoto.id) ? '✓ Foto Selecionada' : 'Selecionar Para Compra' }}
                    </button>
                </div>
            </div>
        </div>
    </Transition>

    <!-- ── BARRA DE CHECKOUT FLUTUANTE ── -->
    <Transition
        enter-active-class="translate-y-full transition-transform duration-500"
        enter-to-class="translate-y-0"
        leave-active-class="translate-y-0"
        leave-to-class="translate-y-full">

        <div v-if="selectedPhotos.length > 0"
             class="fixed bottom-10 left-1/2 -translate-x-1/2 w-[calc(100%-2rem)] max-w-4xl bg-brand-blue/90 backdrop-blur-2xl border border-white/10 rounded-[2.5rem] shadow-[0_30px_100px_rgba(0,0,0,0.3)] p-6 z-[100]">
            <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                <div class="flex items-center gap-6">
                    <div class="relative">
                        <div class="absolute -top-3 -right-3 w-8 h-8 bg-brand-orange text-white text-[10px] font-black rounded-full flex items-center justify-center shadow-lg border-2 border-brand-blue ring-4 ring-brand-blue/50">
                            {{ selectedPhotos.length }}
                        </div>
                        <div class="p-4 bg-white/10 rounded-2xl border border-white/10">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </div>
                    </div>
                    <div>
                        <p class="text-white font-black text-xl uppercase tracking-tighter">Pedido Selecionado</p>
                        <button @click="selectedPhotos = []" class="text-[10px] text-white/40 hover:text-white uppercase font-black tracking-widest transition">Limpar Galeria</button>
                    </div>
                </div>

                <div class="flex items-center gap-10">
                    <div class="text-center md:text-right">
                        <p class="text-[10px] text-brand-orange uppercase font-black tracking-[.3em]">Total</p>
                        <p class="text-white font-black text-3xl">R$ {{ allPhotos.filter(p => selectedPhotos.includes(p.id)).reduce((acc, p) => acc + Number(p.price), 0).toFixed(2) }}</p>
                    </div>
                    <PrimaryButton @click="checkout" :disabled="checkoutForm.processing"
                                   class="bg-white hover:bg-brand-orange text-brand-blue hover:text-white px-10 py-5 rounded-3xl font-black text-sm uppercase tracking-[.25em] shadow-2xl transition-all active:scale-95 border-none">
                        Comprar agora →
                    </PrimaryButton>
                </div>
            </div>
        </div>
    </Transition>
</template>
