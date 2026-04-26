<script setup>
import { Head, useForm, usePage, Link } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Footer from '@/Components/Footer.vue';
import Navbar from '@/Components/Navbar.vue';
import CheckoutModal from '@/Components/CheckoutModal.vue';
import FlashMessages from '@/Components/FlashMessages.vue';

const props = defineProps({
    event: Object,
    photos: Object // Agora recebe o objeto de paginação
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

// ── Busca por face ────────────────────────────────────────────────────────────
const searchForm = useForm({
    selfie: null,
    event_id: props.event.id
});

const isResizing = ref(false);

const handleFileUpload = async (e) => {
    const file = e.target.files[0];
    if (!file) return;

    isResizing.value = true;
    try {
        const resizedFile = await resizeImage(file, 1000, 1000);
        searchForm.selfie = resizedFile;
    } catch (error) {
        console.error('Erro ao redimensionar imagem:', error);
        // Fallback para o arquivo original se falhar
        searchForm.selfie = file;
    } finally {
        isResizing.value = false;
    }
};

const resizeImage = (file, maxWidth, maxHeight) => {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onload = (event) => {
            const img = new Image();
            img.src = event.target.result;
            img.onload = () => {
                const canvas = document.createElement('canvas');
                let width = img.width;
                let height = img.height;

                if (width > height) {
                    if (width > maxWidth) {
                        height *= maxWidth / width;
                        width = maxWidth;
                    }
                } else {
                    if (height > maxHeight) {
                        width *= maxHeight / height;
                        height = maxHeight;
                    }
                }

                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);

                canvas.toBlob((blob) => {
                    const resizedFile = new File([blob], file.name, {
                        type: 'image/jpeg',
                        lastModified: Date.now(),
                    });
                    resolve(resizedFile);
                }, 'image/jpeg', 0.85);
            };
            img.onerror = (err) => reject(err);
        };
        reader.onerror = (err) => reject(err);
    });
};

const searchFaces = () => { searchForm.post(route('store.search')); };

// ── Seleção e checkout ────────────────────────────────────────────────────────
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
    if (checkoutForm.processing) return;

    checkoutForm.photo_ids = selectedPhotos.value;
    checkoutForm.post(route('store.checkout'), {
        preserveScroll: true,
        preserveState: true,
    });
};

// ── Lightbox (Estilo Minhas Fotos) ──────────────────────────────────────────
const photoList = computed(() => props.photos.data ?? []);
const lightboxIndex = ref(null);

const openLightbox  = (i) => { lightboxIndex.value = i; };
const closeLightbox = () => { lightboxIndex.value = null; };
const prevPhoto = () => { lightboxIndex.value = (lightboxIndex.value - 1 + photoList.value.length) % photoList.value.length; };
const nextPhoto = () => { lightboxIndex.value = (lightboxIndex.value + 1) % photoList.value.length; };
const currentPhoto = computed(() => lightboxIndex.value !== null ? photoList.value[lightboxIndex.value] : null);

const handleKey = (e) => {
    if (lightboxIndex.value === null) return;
    if (e.key === 'Escape')      closeLightbox();
    if (e.key === 'ArrowLeft')   prevPhoto();
    if (e.key === 'ArrowRight')  nextPhoto();
};
onMounted(() => window.addEventListener('keydown', handleKey));
onUnmounted(() => window.removeEventListener('keydown', handleKey));

// ── Helpers ───────────────────────────────────────────────────────────────────
const portfolioUrl = (user) => {
    if (!user) return null;
    return user.slug ? route('store.photographer', user.slug) : null;
};
</script>

<template>
    <Head>
        <title>{{ `${event.name} | Fotsport` }}</title>
        <meta name="description" content="Confira as fotos do evento {{ event.name }} realizado em {{ event.location }}. Busque seus cliques por número de peito ou reconhecimento facial.">
        <meta name="keywords" content="fotos {{ event.name }}, {{ event.location }}, fotos de corrida {{ event.date }}, fotsport {{ event.name }}">
        
        <meta property="og:title" content="{{ `${event.name} | Fotsport` }}" />
        <meta property="og:description" content="Veja suas fotos do evento {{ event.name }}. Garanta sua memória agora!" />
        <meta v-if="photos.data && photos.data.length > 0" property="og:image" :content="'/' + photos.data[0].watermarked_path" />
    </Head>

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
            <!-- Header do Evento (Estilo Hero) -->
            <header class="mb-24 text-center">
                <div class="inline-flex items-center gap-3 mb-6 bg-brand-orange/10 px-4 py-2 rounded-full">
                    <span class="w-2 h-2 bg-brand-orange rounded-full animate-pulse"></span>
                    <span class="text-[10px] font-black text-brand-orange uppercase tracking-[.4em]">Evento Esportivo</span>
                </div>
                
                <h1 class="text-5xl md:text-8xl font-black text-brand-dark uppercase tracking-tighter mb-8 leading-[0.9]">
                    {{ event.name }}
                </h1>

                <div class="flex flex-wrap justify-center items-center gap-8 text-gray-400 font-bold text-xs uppercase tracking-[.2em]">
                    <span class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-brand-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        {{ event.date }}
                    </span>
                    <span class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-brand-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        {{ event.location }}
                    </span>
                    <span class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-brand-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        {{ photos.total }} fotos distribuídas
                    </span>
                </div>
            </header>

            <!-- Sessão de Busca Facial Elegante (Glassmorphism) -->
            <section class="max-w-4xl mx-auto mb-32 relative">
                <div class="absolute inset-0 bg-gradient-to-r from-brand-orange/20 via-brand-blue/20 to-brand-orange/20 blur-[100px] opacity-30"></div>
                
                <div class="relative bg-white/40 backdrop-blur-3xl border border-white/50 rounded-[3rem] p-10 md:p-16 shadow-[0_30px_100px_rgba(0,0,0,0.05)] overflow-hidden group">
                    <div class="absolute top-0 right-0 w-64 h-64 bg-brand-orange/5 rounded-full blur-3xl -mr-32 -mt-32 transition-all group-hover:bg-brand-orange/10"></div>
                    
                    <div class="flex flex-col lg:flex-row items-center gap-12 relative z-10">
                        <div class="lg:w-1/2 text-center lg:text-left">
                            <h2 class="text-3xl md:text-4xl font-black text-brand-dark uppercase tracking-tighter mb-6">
                                Encontre suas fotos <br/> <span class="text-brand-orange">num piscar de olhos</span>
                            </h2>
                            <p class="text-gray-500 font-medium leading-relaxed mb-8">
                                Nossa inteligência artificial analisa milhares de registros para encontrar você. Basta enviar uma selfie clara.
                            </p>
                            <div class="flex items-center gap-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                <span class="flex items-center gap-2"><div class="w-1.5 h-1.5 bg-green-500 rounded-full"></div> Rápido</span>
                                <span class="flex items-center gap-2"><div class="w-1.5 h-1.5 bg-green-500 rounded-full"></div> Seguro</span>
                                <span class="flex items-center gap-2"><div class="w-1.5 h-1.5 bg-green-500 rounded-full"></div> Preciso</span>
                            </div>
                        </div>

                        <div class="lg:w-1/2 w-full">
                            <form @submit.prevent="searchFaces" class="bg-white rounded-[2rem] p-8 shadow-2xl shadow-brand-dark/5 border border-gray-100">
                                <label class="block mb-6">
                                    <div class="w-full h-32 border-2 border-dashed border-gray-200 rounded-2xl flex flex-col items-center justify-center gap-3 hover:border-brand-orange hover:bg-brand-orange/[0.02] transition-all cursor-pointer group/upload relative overflow-hidden">
                                        <div v-if="!searchForm.selfie" class="text-center">
                                            <svg class="w-8 h-8 text-gray-300 mx-auto group-hover/upload:text-brand-orange group-hover/upload:scale-110 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mt-2 group-hover/upload:text-brand-dark transition-colors">Arraste ou clique para enviar sua Selfie</p>
                                        </div>
                                        <div v-else class="flex items-center gap-4 p-4 w-full">
                                            <div class="w-20 h-20 bg-gray-100 rounded-xl overflow-hidden shadow-inner">
                                                <div class="w-full h-full flex items-center justify-center text-brand-orange font-black text-lg">✓</div>
                                            </div>
                                            <div class="text-left">
                                                <p class="text-xs font-black text-brand-dark uppercase truncate max-w-[150px]">{{ searchForm.selfie.name }}</p>
                                                <p class="text-[10px] font-bold text-green-500 uppercase tracking-widest mt-1">Arquivo Pronto</p>
                                            </div>
                                        </div>
                                        <input type="file" @change="handleFileUpload" accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer" />
                                    </div>
                                </label>
                                
                                <PrimaryButton :disabled="searchForm.processing || !searchForm.selfie || isResizing" class="w-full justify-center h-16 rounded-2xl text-[11px] font-black uppercase tracking-[.3em] bg-brand-dark hover:bg-black shadow-2xl shadow-brand-dark/20 transition-all">
                                    <span v-if="isResizing" class="flex items-center gap-3">
                                        <div class="w-4 h-4 border-2 border-white/20 border-t-white rounded-full animate-spin"></div>
                                        Otimizando Imagem...
                                    </span>
                                    <span v-else-if="searchForm.processing" class="flex items-center gap-3">
                                        <div class="w-4 h-4 border-2 border-white/20 border-t-white rounded-full animate-spin"></div>
                                        Analisando Características...
                                    </span>
                                    <span v-else class="flex items-center gap-3">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                        Iniciar Reconhecimento Facial
                                    </span>
                                </PrimaryButton>
                            </form>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Grid de Fotos (fotos menores: 4+ colunas) -->
            <div v-if="photoList.length === 0" class="bg-gray-50 rounded-[3rem] p-20 text-center border-2 border-dashed border-gray-200">
                <p class="text-gray-500 font-medium">Nenhuma foto disponível para este evento ainda.</p>
            </div>

            <div v-else>
                <div class="flex items-end justify-between mb-8 px-4 md:px-0">
                    <div>
                        <h3 class="text-2xl font-black text-brand-dark uppercase tracking-tighter">Galeria do Evento</h3>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-1">Exibindo {{ photoList.length }} de {{ photos.total }} fotos</p>
                    </div>

                    <!-- Paginação Superior Minimalista -->
                    <div class="flex items-center gap-1.5 bg-gray-50 p-1.5 rounded-2xl border border-gray-100 hidden md:flex">
                        <Link v-for="link in photos.links" :key="link.label"
                              :href="link.url ?? '#'"
                              v-html="link.label.toLowerCase().includes('prev') ? '←' : (link.label.toLowerCase().includes('next') ? '→' : link.label)"
                              :class="{
                                  'min-w-[40px] h-10 flex items-center justify-center rounded-xl text-[10px] font-black uppercase transition-all': true,
                                  'bg-brand-dark text-white shadow-lg': link.active,
                                  'text-gray-400 hover:bg-white hover:text-brand-orange': !link.active && link.url,
                                  'opacity-20 pointer-events-none': !link.url
                              }"
                        />
                    </div>
                </div>

                <!-- Galeria Full Width -->
                <div class="lg:-mx-8 sm:-mx-6 -mx-4">
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-0 border-t border-l border-gray-200">
                    <div v-for="(photo, index) in photoList" :key="photo.id" 
                         class="group relative aspect-square overflow-hidden border-r border-b border-gray-200 bg-gray-100">
                        
                        <!-- Estado: Em Processamento -->
                        <div v-if="photo.watermarked_path === 'processing.jpg'" 
                             class="absolute inset-0 flex flex-col items-center justify-center p-4 text-center">
                            <div class="w-8 h-8 border-3 border-brand-orange/20 border-t-brand-orange rounded-full animate-spin mb-3"></div>
                            <p class="text-[8px] font-black text-gray-400 uppercase tracking-widest leading-tight">Processando...</p>
                        </div>

                        <!-- Estado: Foto Pronta -->
                        <template v-else>
                            <img :src="'/' + photo.watermarked_path" 
                                 @click="openLightbox(index)"
                                 class="w-full h-full object-cover cursor-zoom-in transition-transform duration-700 group-hover:scale-110" />
                            
                            <!-- Overlay de Seleção/Info -->
                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-all duration-300 flex flex-col justify-between p-4 pointer-events-none">
                                <div class="flex justify-between items-start pointer-events-auto">
                                    <span class="bg-black/50 backdrop-blur-md text-[8px] text-white px-2 py-1 rounded font-black uppercase">#{{ photo.id }}</span>
                                    <div @click="togglePhoto(photo.id)" 
                                         class="w-8 h-8 rounded-full flex items-center justify-center cursor-pointer transition-all"
                                         :class="selectedPhotos.includes(photo.id) ? 'bg-green-500 text-white scale-110' : 'bg-white/20 text-white hover:bg-white hover:text-brand-dark'">
                                        <svg v-if="selectedPhotos.includes(photo.id)" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                        <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                                    </div>
                                </div>
                                <div class="pointer-events-auto flex justify-between items-end">
                                    <p class="text-white font-black text-sm">R$ {{ Number(photo.price).toFixed(2) }}</p>
                                    <button @click="openLightbox(index)" class="text-[8px] text-white/70 hover:text-white uppercase font-black tracking-widest">Ampliar</button>
                                </div>
                            </div>

                            <!-- Indicador de Selecionada (Sempre visível se selecionada) -->
                            <div v-if="selectedPhotos.includes(photo.id)" 
                                 class="absolute top-0 right-0 w-8 h-8 bg-green-500 text-white flex items-center justify-center shadow-lg">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                        </template>
                    </div>
                    </div>
                </div>

                <!-- Paginação Inferior Robusta -->
                <div class="mt-20 flex flex-col items-center gap-8">
                    <div class="h-px w-full bg-gray-100 relative">
                        <div class="absolute left-1/2 -translate-x-1/2 top-1/2 -translate-y-1/2 bg-white px-8 text-[10px] text-gray-300 font-black uppercase tracking-[0.4em]">Navegação Galeria</div>
                    </div>
                    
                    <div class="flex flex-wrap justify-center items-center gap-2 px-3 py-3 bg-white border border-gray-100 rounded-[2rem] shadow-xl">
                        <Link v-for="link in photos.links" :key="link.label"
                              :href="link.url ?? '#'"
                              v-html="link.label.toLowerCase().includes('prev') ? 'Anterior' : (link.label.toLowerCase().includes('next') ? 'Próxima' : link.label)"
                              :class="{
                                  'px-6 h-12 flex items-center justify-center rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all duration-300': true,
                                  'bg-brand-orange text-white shadow-xl shadow-brand-orange/20 scale-105 z-10': link.active,
                                  'text-gray-400 hover:bg-gray-50 hover:text-brand-dark': !link.active && link.url,
                                  'opacity-20 pointer-events-none': !link.url
                              }"
                        />
                    </div>
                    
                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Página {{ photos.current_page }} de {{ photos.last_page }}</p>
                </div>
            </div>
        </main>

        <!-- ── LIGHTBOX (Estilo Dashboard) ── -->
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
                        {{ lightboxIndex + 1 }} <span class="text-brand-orange px-2">/</span> {{ photoList.length }}
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

        <!-- Barra de Checkout Flutuante (Estilo Premium) -->
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
                            <p class="text-[10px] text-brand-orange uppercase font-black tracking-[.3em]">Total</p>
                            <p class="text-white font-black text-3xl">R$ {{ photoList.filter(p => selectedPhotos.includes(p.id)).reduce((acc, p) => acc + Number(p.price), 0).toFixed(2) }}</p>
                        </div>
                        <button @click="checkout" :disabled="checkoutForm.processing"
                                class="bg-brand-blue hover:bg-brand-blue-hover text-white px-10 py-5 rounded-3xl font-black text-[11px] uppercase tracking-[.2em] shadow-2xl transition-all active:scale-95 border-none disabled:opacity-50">
                            {{ checkoutForm.processing ? 'Processando...' : 'Finalizar Pedido →' }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <Footer />
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
