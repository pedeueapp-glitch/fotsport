<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';

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
        },
    });
};

const handleFileUpload = (e) => {
    const files = Array.from(e.target.files);
    if (files.length > 10) {
        alert('Limite Atingido', 'Por favor, selecione no máximo 10 fotos por vez.', 'warning');
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

// Slug do fotógrafo logado para o link da página pública
const authUser = usePage().props.auth.user;
const portfolioUrl = computed(() =>
    authUser?.slug ? route('store.photographer', authUser.slug) : null
);
</script>

<template>
    <Head :title="event.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-medium text-2xl text-gray-800 leading-tight uppercase tracking-widest">{{ event.name }}</h2>
                <div class="flex items-center gap-3">
                    <a v-if="portfolioUrl" :href="portfolioUrl" target="_blank"
                       class="border border-gray-300 text-gray-600 hover:bg-gray-50 py-2 px-4 rounded text-sm uppercase tracking-widest transition">
                        Meu Portfólio
                    </a>
                    <a :href="route('store.event', event)" target="_blank"
                       class="bg-gray-900 hover:bg-black text-white py-2 px-5 rounded text-sm uppercase tracking-widest transition shadow-sm">
                        Ver Página Pública
                    </a>
                </div>
            </div>
        </template>

        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

                <!-- Upload Form -->
                <div class="bg-white p-8 shadow-sm border border-gray-100 rounded-xl">
                    <h3 class="text-xl font-light text-gray-800 mb-6 uppercase tracking-wider">Upload de Fotos</h3>
                    <form @submit.prevent="submit" class="flex items-end gap-4 flex-wrap">
                        <div class="flex-1 min-w-[200px]">
                            <label class="block font-medium text-xs text-gray-500 uppercase tracking-widest mb-2">Arquivos (JPG/PNG — Máx 10)</label>
                            <input
                                type="file"
                                ref="fileInput"
                                @change="handleFileUpload"
                                class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 transition"
                                accept="image/*"
                                multiple
                                required
                            />
                            <div v-if="form.errors.photos" class="text-red-500 text-xs mt-1">{{ form.errors.photos }}</div>
                        </div>
                        <div>
                            <label class="block font-medium text-xs text-gray-500 uppercase tracking-widest mb-2">Preço (R$)</label>
                            <input
                                type="number"
                                step="0.01"
                                v-model="form.price"
                                class="mt-1 block w-32 border border-gray-200 rounded shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500 transition"
                                required
                            />
                        </div>
                        <PrimaryButton :disabled="form.processing" class="bg-black hover:bg-gray-800 tracking-widest uppercase">
                            Processar e Enviar
                        </PrimaryButton>
                    </form>
                    <div v-if="form.progress" class="w-full bg-gray-100 rounded-full h-1.5 mt-6 overflow-hidden">
                        <div class="bg-indigo-500 h-1.5 rounded-full transition-all duration-300"
                             :style="{ width: form.progress.percentage + '%' }"></div>
                    </div>
                </div>

                <!-- Galeria de Gerenciamento -->
                <div class="bg-white p-8 shadow-sm border border-gray-100 rounded-xl">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-light text-gray-800 uppercase tracking-wider">
                            Galeria do Evento
                            <span v-if="photos.length" class="ml-2 text-sm text-gray-400 font-medium normal-case tracking-normal">
                                ({{ photos.length }} foto{{ photos.length !== 1 ? 's' : '' }})
                            </span>
                        </h3>
                    </div>

                    <div v-if="photos.length === 0"
                         class="text-gray-400 py-16 text-center border-2 border-dashed border-gray-200 rounded-xl text-sm font-light uppercase tracking-widest">
                        Nenhuma foto enviada ainda.
                    </div>

                    <div v-else class="grid grid-cols-2 md:grid-cols-4 gap-5">
                        <div v-for="(photo, index) in photos" :key="photo.id"
                             class="border border-gray-100 rounded-xl shadow-sm overflow-hidden relative group bg-white hover:shadow-md transition-all duration-200">

                            <!-- Imagem com lightbox -->
                            <div class="relative cursor-pointer" @click="openLightbox(index)">
                                <img :src="'/' + photo.watermarked_path"
                                     class="w-full h-44 object-cover opacity-90 group-hover:opacity-100 group-hover:scale-105 transition-all duration-300" />

                                <!-- Overlay de lupa ao hover -->
                                <div class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                                    <div class="w-10 h-10 rounded-full bg-white/90 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <!-- Rodapé -->
                            <div class="p-3 flex justify-between items-center bg-gray-50 border-t border-gray-100">
                                <div>
                                    <span class="font-bold text-gray-800 text-sm">R$ {{ parseFloat(photo.price).toFixed(2) }}</span>
                                    <!-- Crédito: fotógrafo que enviou esta foto -->
                                    <p v-if="photo.user" class="text-xs text-indigo-500 mt-0.5 font-medium">
                                        📷 {{ photo.user.name }}
                                        <span v-if="photo.user.id === authUser?.id" class="text-gray-400 font-normal">(você)</span>
                                    </p>
                                </div>
                                <button @click="deletePhoto(photo.id)"
                                        class="text-gray-300 hover:text-red-500 transition p-1 rounded hover:bg-red-50"
                                        title="Excluir foto">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>

    <!-- ── LIGHTBOX ── -->
    <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0 scale-95"
        enter-to-class="opacity-100 scale-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100 scale-100"
        leave-to-class="opacity-0 scale-95">

        <div v-if="lightboxIndex !== null"
             class="fixed inset-0 z-[200] flex items-center justify-center bg-black/95"
             @click.self="closeLightbox">

            <!-- Contador -->
            <div class="absolute top-5 left-1/2 -translate-x-1/2 text-white/60 text-sm font-medium tracking-widest select-none">
                {{ lightboxIndex + 1 }} / {{ photos.length }}
            </div>

            <!-- Fechar -->
            <button @click="closeLightbox"
                    class="absolute top-4 right-4 w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

            <!-- Seta anterior -->
            <button @click="prevPhoto"
                    class="absolute left-4 top-1/2 -translate-y-1/2 w-12 h-12 rounded-full bg-white/10 hover:bg-white/25 flex items-center justify-center text-white transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>

            <!-- Imagem em tela cheia -->
            <div class="max-w-5xl w-full max-h-[85vh] px-20 flex flex-col items-center gap-4">
                <img v-if="currentLightboxPhoto"
                     :key="lightboxIndex"
                     :src="'/' + currentLightboxPhoto.watermarked_path"
                     class="max-h-[72vh] max-w-full object-contain rounded-lg shadow-2xl" />

                <div v-if="currentLightboxPhoto" class="flex items-center gap-5">
                    <span class="text-white/70 text-sm font-medium">
                        R$ {{ Number(currentLightboxPhoto.price).toFixed(2) }}
                    </span>
                    <!-- Deletar direto do lightbox -->
                    <button @click="() => { closeLightbox(); deletePhoto(currentLightboxPhoto.id); }"
                            class="px-4 py-2 bg-red-600/80 hover:bg-red-600 text-white text-xs font-bold rounded-full uppercase tracking-widest transition">
                        🗑 Excluir
                    </button>
                </div>
            </div>

            <!-- Seta próxima -->
            <button @click="nextPhoto"
                    class="absolute right-4 top-1/2 -translate-y-1/2 w-12 h-12 rounded-full bg-white/10 hover:bg-white/25 flex items-center justify-center text-white transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
        </div>
    </Transition>
</template>
