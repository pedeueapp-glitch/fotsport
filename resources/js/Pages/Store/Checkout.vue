<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import Footer from '@/Components/Footer.vue';
import Navbar from '@/Components/Navbar.vue';

const props = defineProps({
    pix_qrcode: String,
    pix_copy_paste: String,
    photos: Array,
    total: Number
});

const copied = ref(false);

const copyPix = () => {
    navigator.clipboard.writeText(props.pix_copy_paste);
    copied.value = true;
    setTimeout(() => copied.value = false, 2000);
};
</script>

<template>
    <Head title="Finalizar Compra | Fotsport" />

    <div class="min-h-screen bg-white flex flex-col font-sans text-brand-dark">
        <Navbar />

        <div class="flex-grow max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 w-full">
            <div class="max-w-4xl mx-auto">

                <div class="flex flex-col items-center mb-16 text-center">
                    <h2 class="text-3xl md:text-5xl font-black text-brand-dark uppercase tracking-tighter mb-4">
                        Resumo do <span class="text-brand-orange">Pedido</span>
                    </h2>
                    <div class="h-1.5 w-20 bg-brand-blue rounded-full"></div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                    <!-- Detalhes dos Itens -->
                    <div class="space-y-6">
                        <div v-for="photo in photos" :key="photo.id"
                             class="flex items-center gap-6 p-6 bg-gray-50 rounded-[2rem] border border-gray-100 group hover:border-brand-orange/20 transition-all">
                            <div class="relative shrink-0">
                                <img :src="'/' + photo.watermarked_path"
                                     class="w-24 h-24 object-cover rounded-2xl shadow-lg group-hover:scale-105 transition-transform" />
                                <div class="absolute -top-2 -right-2 bg-brand-orange text-white text-[10px] font-black px-2 py-1 rounded-lg">
                                    📷
                                </div>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[10px] text-brand-orange uppercase font-black tracking-widest mb-1">{{ photo.event ? photo.event.name : 'Série Exclusiva' }}</p>
                                <p class="text-brand-dark font-black text-lg truncate mb-2 leading-none flex items-center gap-1.5">
                                    {{ photo.user ? photo.user.name : 'Fotógrafo Oficial' }}
                                    <svg v-if="photo.user?.is_verified" class="w-4 h-4 text-blue-500 fill-current shrink-0" viewBox="0 0 24 24">
                                        <path fill-rule="evenodd" d="M8.603 3.799A4.49 4.49 0 0 1 12 2.25c1.357 0 2.573.6 3.397 1.549a4.49 4.49 0 0 1 3.498 1.307 4.491 4.491 0 0 1 1.307 3.497A4.49 4.49 0 0 1 21.75 12a4.49 4.49 0 0 1-1.549 3.397 4.491 4.491 0 0 1-1.307 3.497 4.491 4.491 0 0 1-3.497 1.307A4.49 4.49 0 0 1 12 21.75a4.49 4.49 0 0 1-3.397-1.549 4.49 4.49 0 0 1-3.498-1.306 4.491 4.491 0 0 1-1.307-3.498A4.49 4.49 0 0 1 2.25 12c0-1.357.6-2.573 1.549-3.397a4.49 4.49 0 0 1 1.307-3.497 4.49 4.49 0 0 1 3.497-1.307Zm7.007 6.387a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule="evenodd" />
                                    </svg>
                                </p>
                                <p class="text-brand-blue font-black">R$ {{ Number(photo.price).toFixed(2) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Checkout e Total -->
                    <div class="space-y-8">
                        <div class="bg-brand-dark rounded-[2.5rem] p-10 text-white shadow-2xl relative overflow-hidden">
                            <div class="absolute right-0 top-0 w-32 h-32 bg-brand-orange/10 rounded-full blur-3xl -mr-16 -mt-16"></div>
                            
                            <h3 class="text-xs font-black uppercase tracking-[0.4em] text-brand-orange mb-10">Total da Compra</h3>
                            
                            <div class="flex justify-between items-end mb-12">
                                <div>
                                    <p class="text-white/40 text-[10px] font-black uppercase tracking-widest mb-1">Itens Selecionados</p>
                                    <p class="text-3xl font-black">{{ photos.length }} <span class="text-sm font-medium text-white/60">un.</span></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-white/40 text-[10px] font-black uppercase tracking-widest mb-1">Valor Total</p>
                                    <p class="text-5xl font-black text-brand-orange">R$ {{ total.toFixed(2) }}</p>
                                </div>
                            </div>

                            <div class="pt-8 border-t border-white/10">
                                <p class="text-[10px] text-center text-white/30 mb-6 uppercase font-black tracking-widest">
                                    Pague com Pix (Efi Pay)
                                </p>

                                <div class="flex flex-col items-center gap-6">
                                    <div class="bg-white p-4 rounded-3xl shadow-xl">
                                        <img :src="pix_qrcode" class="w-48 h-48" alt="Pix QR Code" />
                                    </div>

                                    <div class="w-full space-y-3">
                                        <button 
                                            @click="copyPix"
                                            class="w-full bg-brand-orange hover:bg-white hover:text-brand-dark text-white font-black py-4 rounded-2xl transition-all uppercase text-[10px] tracking-widest shadow-lg active:scale-95 flex items-center justify-center gap-2"
                                        >
                                            <template v-if="!copied">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                                                Copiar Código Pix
                                            </template>
                                            <template v-else>
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                Copiado!
                                            </template>
                                        </button>
                                        <p class="text-[9px] text-center text-white/20 uppercase font-black tracking-[0.2em]">Após pagar, você será redirecionado automaticamente</p>
                                    </div>
                                </div>

                                <div class="mt-8 flex justify-center items-center gap-4 opacity-30 group">
                                    <span class="h-px bg-white flex-grow"></span>
                                    <div class="text-[10px] font-black uppercase tracking-widest text-white">Fotsport Safe</div>
                                    <span class="h-px bg-white flex-grow"></span>
                                </div>
                            </div>
                        </div>

                        <Link :href="route('store.index')" class="flex items-center justify-center gap-2 text-gray-400 hover:text-brand-orange font-black text-xs uppercase tracking-widest transition-all">
                            ← Continuar Escolhendo fotos
                        </Link>
                    </div>
                </div>

            </div>
        </div>

        <Footer />
    </div>
</template>
