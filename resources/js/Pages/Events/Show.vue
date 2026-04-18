<script setup>
import { Head, useForm, router, usePage, Link } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { alert, confirm } from '@/utils/swal';

const props = defineProps({ event: Object });

// ── Upload ────────────────────────────────────────────────────────────────────
const form = useForm({ photos: [], price: 5.00 });
const fileInput = ref(null);

const submit = () => {
    form.post(route('photos.store', props.event), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('photos');
            if (fileInput.value) fileInput.value.value = '';
            alert('Sucesso', 'Suas fotos foram enviadas e estão sendo processadas.', 'success');
        },
    });
};

const handleFileUpload = (e) => {
    const files = Array.from(e.target.files);
    if (files.length > 50) {
        alert('Limite Atingido', 'Por favor, selecione no máximo 50 fotos por vez.', 'warning');
        e.target.value = '';
        form.photos = [];
        return;
    }
    form.photos = files;
};

const deletePhoto = async (photoId) => {
    const result = await confirm('Excluir Foto', 'Tem certeza que deseja excluir esta foto definitivamente?');
    if (result.isConfirmed) {
        router.delete(route('photos.destroy', { event: props.event, photo: photoId }), {
            preserveScroll: true
        });
    }
};

// ── Lightbox ──────────────────────────────────────────────────────────────────
const photos = computed(() => props.event.photos ?? []);
const lightboxIndex = ref(null);

const openLightbox  = (i) => { lightboxIndex.value = i; };
const closeLightbox = () => { lightboxIndex.value = null; };
const prevPhoto = () => { lightboxIndex.value = (lightboxIndex.value - 1 + photos.value.length) % photos.value.length; };
const nextPhoto = () => { lightboxIndex.value = (lightboxIndex.value + 1) % photos.value.length; };
const currentLightboxPhoto = computed(() => lightboxIndex.value !== null ? photos.value[lightboxIndex.value] : null);

const handleKey = (e) => {
    if (lightboxIndex.value === null) return;
    if (e.key === 'Escape')     closeLightbox();
    if (e.key === 'ArrowLeft')  prevPhoto();
    if (e.key === 'ArrowRight') nextPhoto();
};
onMounted(() => window.addEventListener('keydown', handleKey));
onUnmounted(() => window.removeEventListener('keydown', handleKey));

const authUser = usePage().props.auth.user;
</script>

<template>
    <Head :title="`Gerenciar: ${event.name}`" />

    <div class="min-h-screen bg-white flex flex-col font-sans text-brand-dark">
        <Navbar />

        <main class="flex-grow max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 w-full">
            
            <!-- Header do Evento -->
            <header class="mb-20">
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-8">
                    <div class="max-w-2xl">
                        <div class="inline-flex items-center gap-3 mb-4">
                            <div class="h-px w-8 bg-brand-orange"></div>
                            <span class="text-[10px] font-black text-brand-orange uppercase tracking-[.4em]">Gerenciamento de Evento</span>
                        </div>
                        <h1 class="text-4xl md:text-6xl font-black text-brand-dark uppercase tracking-tighter mb-4">
                            {{ event.name }}
                        </h1>
                        <div class="flex items-center gap-6 text-gray-400 font-medium italic">
                            <span>{{ event.date }}</span>
                            <div class="w-1.5 h-1.5 bg-gray-200 rounded-full"></div>
                            <span>{{ event.location }}</span>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-4">
                        <a :href="route('store.event', event)" target="_blank"
                           class="px-8 py-4 border-2 border-gray-100 text-gray-400 hover:text-brand-blue hover:border-brand-blue font-black text-xs uppercase tracking-widest rounded-2xl transition-all active:scale-95">
                            Ver Página Pública
                        </a>
                    </div>
                </div>
                <div class="mt-10 h-1.5 w-20 bg-brand-blue rounded-full"></div>
            </header>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
                
                <!-- Coluna de Upload (Sticky) -->
                <div class="lg:col-span-4">
                    <div class="bg-gray-50 rounded-[2.5rem] p-10 border border-gray-100 sticky top-32 shadow-sm shadow-gray-100/50">
                        <h3 class="text-xl font-black text-brand-dark uppercase tracking-tighter mb-8">Enviar Fotos</h3>
                        
                        <form @submit.prevent="submit" class="space-y-8">
                            <div class="space-y-4">
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest">Preço por Foto (R$)</label>
                                <input
                                    type="number"
                                    step="0.01"
                                    v-model="form.price"
                                    class="w-full h-14 bg-white border-none rounded-2xl px-6 font-black text-brand-blue focus:ring-2 focus:ring-brand-blue transition-all shadow-sm"
                                    required
                                />
                            </div>

                            <div class="space-y-4">
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest">Selecionar Imagens (JPG)</label>
                                <div @click="fileInput.click()" 
                                     class="w-full aspect-square bg-white border-2 border-dashed border-gray-200 rounded-[2.5rem] flex flex-col items-center justify-center cursor-pointer hover:border-brand-orange hover:bg-orange-50/10 transition-all group">
                                    <template v-if="form.photos.length === 0">
                                        <svg class="w-12 h-12 text-gray-200 group-hover:text-brand-orange transition-colors mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"/></svg>
                                        <span class="text-[10px] font-black text-gray-300 uppercase tracking-widest group-hover:text-brand-orange">Clique para escolher</span>
                                    </template>
                                    <template v-else>
                                        <span class="text-4xl font-black text-brand-orange mb-2">{{ form.photos.length }}</span>
                                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Fotos Selecionadas</span>
                                        <button @click.stop="form.photos = []" class="mt-4 text-[9px] font-bold text-red-400 uppercase tracking-widest hover:text-red-600 transition">Trocar Seleção</button>
                                    </template>
                                </div>
                                <input type="file" ref="fileInput" @change="handleFileUpload" class="hidden" accept="image/jpeg,image/jpg" multiple />
                                <div v-if="form.errors.photos" class="text-red-500 text-[10px] font-bold uppercase mt-2">{{ form.errors.photos }}</div>
                            </div>

                            <button :disabled="form.processing || form.photos.length === 0" 
                                    class="w-full py-5 bg-brand-dark text-white rounded-2xl font-black text-xs uppercase tracking-[0.2em] shadow-2xl transition-all active:scale-95 disabled:opacity-20 flex items-center justify-center gap-4 group">
                                <span v-if="form.processing">Processando...</span>
                                <template v-else>
                                    Subir Fotos 
                                    <svg class="w-4 h-4 group-hover:translate-x-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                                </template>
                            </button>
                        </form>

                        <!-- Progress Bar -->
                        <div v-if="form.progress" class="mt-8">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Progresso do Upload</span>
                                <span class="text-[10px] font-black text-brand-blue">{{ form.progress.percentage }}%</span>
                            </div>
                            <div class="w-full bg-white rounded-full h-2 overflow-hidden shadow-inner">
                                <div class="bg-brand-blue h-full transition-all duration-300" :style="{ width: form.progress.percentage + '%' }"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Galeria de Fotos -->
                <div class="lg:col-span-8">
                    <div class="flex items-center justify-between mb-12">
                        <div class="flex items-center gap-4">
                            <h2 class="text-2xl font-black text-brand-dark uppercase tracking-tighter italic">Galeria Detalhada</h2>
                            <span class="bg-gray-100 text-gray-400 px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest">{{ photos.length }} Registros</span>
                        </div>
                    </div>

                    <div v-if="photos.length === 0" 
                         class="bg-gray-50 rounded-[3rem] p-24 text-center border-2 border-dashed border-gray-100">
                        <p class="text-gray-300 font-medium uppercase tracking-[0.3em] text-sm italic">Nenhum registro encontrado para este evento.</p>
                    </div>

                    <div v-else class="grid grid-cols-2 lg:grid-cols-3 gap-6">
                        <div v-for="(photo, index) in photos" :key="photo.id"
                             class="group relative bg-white rounded-[2rem] overflow-hidden border border-gray-50 hover:shadow-2xl transition-all duration-500 flex flex-col transform hover:-translate-y-1">
                            
                            <!-- Imagem -->
                            <div class="aspect-[4/5] overflow-hidden cursor-pointer" @click="openLightbox(index)">
                                <img :src="'/' + photo.watermarked_path" class="w-full h-full object-cover group-hover:scale-110 transition duration-700" />
                                <div class="absolute inset-0 bg-brand-dark/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center backdrop-blur-sm">
                                    <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center text-brand-dark transform scale-50 group-hover:scale-100 transition-transform duration-300 shadow-2xl">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer Card -->
                            <div class="p-6 mt-auto border-t border-gray-50 flex justify-between items-center bg-white">
                                <div>
                                    <p class="text-[9px] text-gray-400 font-bold uppercase tracking-widest mb-1">Registro #{{ photo.id }}</p>
                                    <p class="text-lg font-black text-brand-blue leading-none">R$ {{ Number(photo.price).toFixed(2) }}</p>
                                </div>
                                <button @click.stop="deletePhoto(photo.id)" 
                                        class="relative z-10 w-10 h-10 bg-gray-50 text-gray-300 hover:bg-red-50 hover:text-red-500 rounded-xl flex items-center justify-center transition-all shadow-sm">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <Footer />

        <!-- Lightbox Simplificado -->
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div v-if="lightboxIndex !== null" class="fixed inset-0 z-[200] flex items-center justify-center bg-brand-dark/98 backdrop-blur-2xl" @click.self="closeLightbox">
                <button @click="closeLightbox" class="absolute top-10 right-10 w-12 h-12 bg-white/5 hover:bg-brand-orange rounded-2xl flex items-center justify-center text-white transition-all">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>

                <div class="max-w-4xl w-full px-6 flex flex-col items-center gap-10">
                    <img v-if="currentLightboxPhoto" :src="'/' + currentLightboxPhoto.watermarked_path" class="max-h-[70vh] w-auto object-contain rounded-[2.5rem] shadow-2xl border-4 border-white/5" />
                    
                    <div class="absolute bottom-10 inset-x-0 flex justify-center">
                        <div class="bg-white/5 backdrop-blur-xl border border-white/10 p-6 rounded-[2rem] flex items-center gap-12 text-white">
                            <div class="text-center">
                                <p class="text-[10px] text-brand-orange uppercase font-black tracking-widest mb-1">Preço Atual</p>
                                <p class="text-2xl font-black">R$ {{ Number(currentLightboxPhoto?.price).toFixed(2) }}</p>
                            </div>
                            <div class="h-10 w-px bg-white/10"></div>
                            <button @click="() => { deletePhoto(currentLightboxPhoto.id); closeLightbox(); }" class="bg-red-500/80 hover:bg-red-500 text-white px-8 py-3 rounded-xl font-black text-[10px] uppercase tracking-widest transition shadow-xl">Excluir Foto permanentemente</button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>
