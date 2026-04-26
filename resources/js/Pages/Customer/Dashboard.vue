<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import Footer from '@/Components/Footer.vue';
import Navbar from '@/Components/Navbar.vue';
import CheckoutModal from '@/Components/CheckoutModal.vue';
import FlashMessages from '@/Components/FlashMessages.vue';

import { confirm } from '@/utils/swal';

const props = defineProps({
    purchases: Array,
    customer: Object
});

const showCheckoutModal = ref(false);
const checkoutData = ref(null);

watch(() => usePage().props.flash?.checkout_data, (newVal) => {
    if (newVal) {
        checkoutData.value = newVal;
        showCheckoutModal.value = true;
    }
}, { immediate: true });

const logoutForm = useForm({});
const logout = () => {
    logoutForm.post(route('store.customer.logout'));
};

const cancelForm = useForm({});
const cancelPurchase = async (id) => {
    const result = await confirm('Tem certeza?', 'Deseja desistir desta foto?');
    if (result.isConfirmed) {
        cancelForm.delete(route('customer.cancel', id));
    }
};

const repayForm = useForm({});
const repayPurchase = (id) => {
    repayForm.post(route('customer.repay', id));
};

const getStatusLabel = (status) => {
    const labels = {
        'approved': 'Aprovado',
        'pending': 'Pendente',
        'rejected': 'Recusado'
    };
    return labels[status] || status;
};

const getStatusClass = (status) => {
    const classes = {
        'approved': 'bg-green-100 text-green-700',
        'pending': 'bg-yellow-100 text-yellow-700',
        'rejected': 'bg-red-100 text-red-700'
    };
    return classes[status] || 'bg-gray-100 text-gray-700';
};

// ── Lightbox ─────────────────────────────────────────────────────────────────
const lightboxIndex = ref(null);
const openLightbox  = (i) => { lightboxIndex.value = i; };
const closeLightbox = () => { lightboxIndex.value = null; };
const prevPhoto = () => { lightboxIndex.value = (lightboxIndex.value - 1 + props.purchases.length) % props.purchases.length; };
const nextPhoto = () => { lightboxIndex.value = (lightboxIndex.value + 1) % props.purchases.length; };
const currentPurchase = computed(() => lightboxIndex.value !== null ? props.purchases[lightboxIndex.value] : null);

const handleKey = (e) => {
    if (lightboxIndex.value === null) return;
    if (e.key === 'Escape')      closeLightbox();
    if (e.key === 'ArrowLeft')   prevPhoto();
    if (e.key === 'ArrowRight')  nextPhoto();
};
onMounted(() => window.addEventListener('keydown', handleKey));
onUnmounted(() => window.removeEventListener('keydown', handleKey));

</script>

<template>
    <Head title="Minhas Fotos" />

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

        <main class="flex-grow max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 w-full">
            <header class="mb-20">
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-8">
                    <div class="max-w-2xl">
                        <div class="inline-flex items-center gap-2 mb-4">
                            <div class="h-px w-8 bg-brand-orange"></div>
                            <span class="text-[10px] font-black text-brand-orange uppercase tracking-[.4em]">Área do Cliente</span>
                        </div>
                        <h1 class="text-4xl md:text-6xl font-black text-brand-dark uppercase tracking-tighter mb-4">
                            Minhas <span class="text-brand-blue">Fotos</span>
                        </h1>
                        <p class="text-gray-500 text-lg font-medium">
                            Olá, <span class="text-brand-dark font-bold">{{ customer.name }}</span>. Aqui estão todas as memórias que você adquiriu conosco.
                        </p>
                    </div>
                    
                    <button @click="logout" class="group flex items-center gap-3 px-8 py-4 border-2 border-red-100 text-red-500 hover:bg-red-50 hover:border-red-200 font-black text-xs uppercase tracking-widest rounded-2xl transition-all active:scale-95">
                        <svg class="w-5 h-5 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        Encerrar Sessão
                    </button>
                </div>
                <div class="mt-10 h-1.5 w-20 bg-brand-orange rounded-full"></div>
            </header>

            <div v-if="purchases.length === 0" class="bg-gray-50 rounded-[3rem] p-20 text-center border-2 border-dashed border-gray-200">
                <div class="w-24 h-24 bg-white rounded-3xl flex items-center justify-center mx-auto mb-8 shadow-xl rotate-3">
                    <svg class="w-12 h-12 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3 class="text-2xl font-black text-brand-dark uppercase tracking-tight mb-4">Sua galeria está vazia</h3>
                <p class="text-gray-500 mb-10 max-w-md mx-auto font-medium">Você ainda não possui fotos aprovadas. Explore nossos eventos e encontre seus melhores momentos.</p>
                <Link :href="route('store.index')" class="inline-flex items-center px-10 py-5 bg-brand-blue text-white text-xs font-black uppercase tracking-[.2em] rounded-2xl hover:bg-brand-blue-hover transition shadow-2xl shadow-brand-blue/20 active:scale-95">
                    Descobrir Eventos
                </Link>
            </div>

            <div v-else class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4 sm:gap-6">
                <div v-for="(purchase, index) in purchases" :key="purchase.id" class="group bg-white rounded-3xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-500 border border-gray-100 flex flex-col relative">
                    <div class="aspect-square overflow-hidden relative cursor-pointer" @click="openLightbox(index)">
                        <img 
                            :src="'/' + purchase.photo.watermarked_path" 
                            class="w-full h-full object-cover group-hover:scale-110 transition duration-700"
                        />
                        <div class="absolute inset-0 bg-brand-dark/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center backdrop-blur-[2px]">
                            <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-brand-dark shadow-2xl transform scale-50 group-hover:scale-100 transition-transform duration-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                                </svg>
                            </div>
                        </div>
                        <div class="absolute top-3 right-3">
                            <span :class="getStatusClass(purchase.status)" class="px-2.5 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest shadow-xl backdrop-blur-md">
                                {{ getStatusLabel(purchase.status) }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="p-4 flex-grow flex flex-col justify-between">
                        <div class="mb-3">
                            <p class="text-[9px] font-black text-brand-orange uppercase tracking-widest mb-1">{{ purchase.photo.event ? purchase.photo.event.date : 'Registro' }}</p>
                            <h3 class="text-xs font-black text-brand-dark uppercase tracking-tighter line-clamp-1">{{ purchase.photo.event ? purchase.photo.event.name : 'Evento' }}</h3>
                        </div>

                        <div class="mt-auto space-y-2">
                            <template v-if="purchase.status === 'approved'">
                                <a 
                                    :href="route('customer.download', purchase.id)" 
                                    class="w-full flex items-center justify-center gap-2 py-2.5 bg-brand-blue text-white text-[9px] font-black uppercase tracking-widest rounded-xl hover:bg-brand-blue-hover transition-all shadow-lg shadow-brand-blue/10 active:scale-95"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                    Download HD
                                </a>
                            </template>
                            <template v-else-if="purchase.status === 'pending'">
                                <button 
                                    @click="repayPurchase(purchase.id)"
                                    :disabled="repayForm.processing"
                                    class="w-full flex items-center justify-center gap-2 py-2.5 bg-brand-orange text-white text-[9px] font-black uppercase tracking-widest rounded-xl hover:bg-brand-orange-hover transition-all shadow-lg shadow-brand-orange/10 active:scale-95"
                                >
                                    Pagar Agora
                                </button>
                                <button 
                                    @click="cancelPurchase(purchase.id)"
                                    :disabled="cancelForm.processing"
                                    class="w-full py-2 text-red-500 hover:bg-red-50 text-[9px] font-black uppercase tracking-widest rounded-xl transition-all"
                                >
                                    Desistir
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </main>

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
             class="fixed inset-0 z-[200] flex items-center justify-center bg-brand-dark/98 backdrop-blur-2xl"
             @click.self="closeLightbox">

            <!-- UI Topo -->
            <div class="absolute top-8 inset-x-8 flex justify-between items-center z-50">
                <div class="bg-white/5 border border-white/10 backdrop-blur-md px-6 py-2 rounded-full text-white/50 text-xs font-black tracking-[0.3em] uppercase">
                    {{ lightboxIndex + 1 }} <span class="text-brand-orange px-2">/</span> {{ purchases.length }}
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
                    <img v-if="currentPurchase"
                         :key="lightboxIndex"
                         :src="'/' + currentPurchase.photo.watermarked_path"
                         class="max-h-[75vh] w-auto object-contain rounded-[2.5rem] shadow-[0_0_100px_rgba(0,0,0,0.5)] border-4 border-white/5" />
                </div>

                <div v-if="currentPurchase" class="flex flex-col items-center gap-6">
                    <div class="text-center">
                        <p class="text-brand-orange text-[10px] font-black uppercase tracking-[.3em] mb-2">Evento Selecionado</p>
                        <h3 class="text-white font-black text-3xl uppercase tracking-tighter">{{ currentPurchase.photo.event ? currentPurchase.photo.event.name : 'Registro Único' }}</h3>
                    </div>
                    
                    <div class="flex items-center gap-6 bg-white/5 backdrop-blur-xl border border-white/10 p-2 rounded-[2rem]">
                         <span :class="getStatusClass(currentPurchase.status)" class="px-6 py-3 rounded-[1.5rem] text-[10px] font-black uppercase tracking-widest shadow-xl">
                            {{ getStatusLabel(currentPurchase.status) }}
                        </span>
                        
                        <a v-if="currentPurchase.status === 'approved'"
                            :href="route('customer.download', currentPurchase.id)" 
                            class="px-10 py-3 bg-brand-blue text-white text-[10px] font-black uppercase tracking-[.2em] rounded-[1.5rem] hover:bg-brand-blue-hover transition-all active:scale-95 shadow-xl shadow-brand-blue/20"
                        >
                            Download HD
                        </a>

                        <div v-else-if="currentPurchase.status === 'pending'" class="flex items-center gap-4">
                            <button 
                                @click="repayPurchase(currentPurchase.id)"
                                :disabled="repayForm.processing"
                                class="px-10 py-3 bg-brand-orange text-white text-[10px] font-black uppercase tracking-[.2em] rounded-[1.5rem] hover:bg-brand-orange-hover transition-all active:scale-95 shadow-xl shadow-brand-orange/20 disabled:opacity-50"
                            >
                                Pagar Agora
                            </button>
                            <button 
                                @click="cancelPurchase(currentPurchase.id)"
                                :disabled="cancelForm.processing"
                                class="px-10 py-3 bg-red-500 text-white text-[10px] font-black uppercase tracking-[.2em] rounded-[1.5rem] hover:bg-red-600 transition-all active:scale-95 shadow-xl shadow-red-500/20 disabled:opacity-50"
                            >
                                Desistir
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>