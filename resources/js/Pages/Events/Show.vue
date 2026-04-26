<script setup>
import { Head, useForm, router, usePage, Link } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import PhotographerLayout from '@/Layouts/PhotographerLayout.vue';
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
            alert('Sucesso', 'Fotos enviadas e em processamento.', 'success');
        },
    });
};

const handleFileUpload = (e) => {
    const files = Array.from(e.target.files);
    if (files.length > 50) {
        alert('Limite', 'Max 50 fotos por vez.', 'warning');
        e.target.value = '';
        form.photos = [];
        return;
    }
    form.photos = files;
};

// ── Excluir foto individual ───────────────────────────────────────────────────
const authUser = usePage().props.auth.user;

const canDelete = (photo) => authUser.is_superadmin || photo.user_id === authUser.id;

const deletePhoto = async (photoId) => {
    const result = await confirm('Excluir Foto', 'Deseja excluir definitivamente?');
    if (result.isConfirmed) {
        router.delete(route('photos.destroy', { event: props.event, photo: photoId }), {
            preserveScroll: true
        });
    }
};

// ── Seleção em massa ──────────────────────────────────────────────────────────
const selectedIds = ref([]);

const deleteEvent = async () => {
    const result = await confirm('Excluir Evento', 'Tem certeza? Todas as fotos NÃO COMPRADAS serão apagadas permanentemente do servidor.');
    if (result.isConfirmed) {
        router.delete(route('events.destroy', props.event.slug));
    }
};

const photos = computed(() => props.event.photos ?? []);

const myPhotos = computed(() => photos.value.filter(p => canDelete(p)));

const allSelected = computed(() =>
    myPhotos.value.length > 0 && myPhotos.value.every(p => selectedIds.value.includes(p.id))
);

const toggleSelectAll = () => {
    if (allSelected.value) {
        selectedIds.value = [];
    } else {
        selectedIds.value = myPhotos.value.map(p => p.id);
    }
};

const toggleSelect = (id) => {
    const idx = selectedIds.value.indexOf(id);
    if (idx === -1) selectedIds.value.push(id);
    else selectedIds.value.splice(idx, 1);
};

const bulkDelete = async () => {
    if (selectedIds.value.length === 0) return;
    const result = await confirm(
        'Excluir Selecionadas',
        `Deseja excluir permanentemente ${selectedIds.value.length} foto(s)?`
    );
    if (!result.isConfirmed) return;

    router.delete(route('photos.bulk-destroy', props.event), {
        data: { photo_ids: selectedIds.value },
        preserveScroll: true,
        onSuccess: () => {
            selectedIds.value = [];
            alert('Sucesso', 'Fotos excluídas.', 'success');
        }
    });
};

// ── Lightbox ──────────────────────────────────────────────────────────────────
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

// ── Edição de Preço ───────────────────────────────────────────────────────────
const editingPriceId = ref(null);
const priceForm = useForm({ price: 0 });

const startEditingPrice = (photo) => {
    editingPriceId.value = photo.id;
    priceForm.price = photo.price;
};

const cancelEditingPrice = () => {
    editingPriceId.value = null;
};

const submitPriceUpdate = (photoId) => {
    priceForm.patch(route('photos.update-price', photoId), {
        preserveScroll: true,
        onSuccess: () => {
            editingPriceId.value = null;
            alert('Sucesso', 'Preço atualizado.', 'success');
        }
    });
};
</script>

<template>
    <Head :title="`Gerenciar: ${event.name}`" />

    <PhotographerLayout :title="event.name" :subtitle="`${event.category ? event.category + ' • ' : ''}${event.date} • ${event.location}`">
        <template #actions>
            <div class="flex items-center gap-3">
                <!-- Bulk delete toolbar -->
                <Transition enter-active-class="duration-200" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100">
                    <div v-if="selectedIds.length > 0" class="flex items-center gap-2">
                        <span class="text-[9px] font-black uppercase tracking-widest text-brand-orange">
                            {{ selectedIds.length }} selecionada(s)
                        </span>
                        <button @click="bulkDelete"
                            class="px-4 py-2 bg-red-500 text-white font-black text-[9px] uppercase tracking-widest rounded-lg hover:bg-red-600 transition-all active:scale-95 shadow">
                            Excluir Selecionadas
                        </button>
                        <button @click="selectedIds = []"
                            class="px-4 py-2 bg-gray-100 text-gray-500 font-black text-[9px] uppercase tracking-widest rounded-lg hover:bg-gray-200 transition-all active:scale-95">
                            Cancelar
                        </button>
                    </div>
                </Transition>
                <a :href="route('store.event', event)" target="_blank"
                   class="px-5 py-2.5 bg-white border border-gray-200 text-gray-400 hover:text-brand-blue hover:border-brand-blue font-black text-[9px] uppercase tracking-widest rounded-lg transition-all active:scale-95 shadow-sm">
                    Página Pública ↗
                </a>

                <button v-if="authUser.is_superadmin" @click="deleteEvent"
                    class="px-5 py-2.5 bg-red-50 text-red-600 border border-red-100 hover:bg-red-100 font-black text-[9px] uppercase tracking-widest rounded-lg transition-all active:scale-95 shadow-sm">
                    Excluir Evento
                </button>
            </div>
        </template>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

            <!-- Upload Area -->
            <div class="lg:col-span-4 order-2 lg:order-1">
                <div class="bg-gray-50 rounded-xl p-8 border border-gray-100 sticky top-4">
                    <h3 class="text-sm font-black text-brand-dark uppercase tracking-widest mb-6">Subir Fotos</h3>

                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="space-y-2">
                            <label class="block text-[8px] font-black text-gray-400 uppercase tracking-widest ml-1">Preço por Foto (R$)</label>
                            <input type="number" step="0.01" v-model="form.price" class="w-full h-11 bg-white border border-gray-200 rounded-xl px-4 font-black text-brand-blue text-xs focus:ring-2 focus:ring-brand-blue/10" required />
                        </div>

                        <div class="space-y-2">
                            <label class="block text-[8px] font-black text-gray-400 uppercase tracking-widest ml-1">Imagens (JPG)</label>
                            <div @click="fileInput.click()"
                                 class="w-full py-10 bg-white border border-dashed border-gray-200 rounded-xl flex flex-col items-center justify-center cursor-pointer hover:border-brand-orange transition-all group">
                                <template v-if="form.photos.length === 0">
                                    <svg class="w-8 h-8 text-gray-200 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    <span class="text-[8px] font-black text-gray-300 uppercase tracking-widest">Selecionar Arquivos</span>
                                </template>
                                <template v-else>
                                    <span class="text-2xl font-black text-brand-orange mb-1">{{ form.photos.length }}</span>
                                    <span class="text-[8px] font-black text-gray-400 uppercase tracking-widest">Fotos</span>
                                </template>
                            </div>
                            <input type="file" ref="fileInput" @change="handleFileUpload" class="hidden" accept="image/jpeg,image/jpg" multiple />
                        </div>

                        <button :disabled="form.processing || form.photos.length === 0"
                                class="w-full py-4 bg-brand-dark text-white rounded-xl font-black text-[9px] uppercase tracking-widest shadow-lg active:scale-95 transition-all disabled:opacity-30">
                            {{ form.processing ? 'Processando Upload...' : 'Iniciar Upload' }}
                        </button>
                    </form>

                    <div v-if="form.progress" class="mt-6">
                        <div class="w-full bg-white rounded-full h-1.5 overflow-hidden ring-1 ring-gray-100">
                            <div class="bg-brand-blue h-full transition-all" :style="{ width: form.progress.percentage + '%' }"></div>
                        </div>
                        <p class="text-center text-[8px] font-black text-brand-blue mt-2 uppercase tracking-widest">{{ form.progress.percentage }}%</p>
                    </div>
                </div>
            </div>

            <!-- Gallery Area -->
            <div class="lg:col-span-8 order-1 lg:order-2">
                <!-- Select All bar -->
                <div v-if="myPhotos.length > 0" class="flex items-center justify-between mb-4 px-1">
                    <label class="flex items-center gap-2 cursor-pointer group">
                        <div @click="toggleSelectAll"
                             :class="allSelected ? 'bg-brand-blue border-brand-blue' : 'border-gray-300 hover:border-brand-blue'"
                             class="w-4 h-4 border-2 rounded flex items-center justify-center transition-all">
                            <svg v-if="allSelected" class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/></svg>
                        </div>
                        <span class="text-[9px] font-black uppercase tracking-widest text-gray-400 group-hover:text-brand-dark transition-colors">
                            Selecionar todas as minhas fotos
                        </span>
                    </label>
                    <span class="text-[9px] font-black text-gray-300 uppercase tracking-widest">
                        {{ photos.length }} foto(s) no evento
                    </span>
                </div>

                <div v-if="photos.length === 0" class="bg-gray-50 rounded-xl p-16 text-center border border-gray-100">
                    <p class="text-gray-300 font-black uppercase tracking-widest text-[10px]">Nenhuma foto neste evento ainda.</p>
                </div>

                <div v-else class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                    <div v-for="(photo, index) in photos" :key="photo.id"
                         :class="selectedIds.includes(photo.id) ? 'ring-2 ring-brand-blue' : ''"
                         class="group relative bg-white rounded-xl overflow-hidden border border-gray-100 shadow-sm hover:shadow-lg transition-all duration-300">

                        <!-- Checkbox de seleção (só aparece para fotos que o user pode deletar) -->
                        <div v-if="canDelete(photo)"
                             @click.stop="toggleSelect(photo.id)"
                             class="absolute top-2 left-2 z-10">
                            <div :class="selectedIds.includes(photo.id) ? 'bg-brand-blue border-brand-blue' : 'bg-white/80 border-gray-300'"
                                 class="w-5 h-5 border-2 rounded flex items-center justify-center cursor-pointer transition-all shadow">
                                <svg v-if="selectedIds.includes(photo.id)" class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
                                </svg>
                            </div>
                        </div>

                        <div class="aspect-square overflow-hidden cursor-pointer relative" @click="openLightbox(index)">
                            <img :src="'/' + photo.watermarked_path" class="w-full h-full object-cover group-hover:scale-105 transition duration-500" />
                            <div class="absolute inset-0 bg-brand-dark/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                <div class="w-10 h-10 bg-white/90 rounded-full flex items-center justify-center text-brand-dark shadow-xl scale-75 group-hover:scale-100 transition-transform">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </div>
                            </div>
                        </div>

                        <div class="p-3 bg-white border-t border-gray-50">
                            <div class="flex justify-between items-center mb-2">
                                <p class="text-[7px] text-brand-orange font-black uppercase tracking-widest">
                                    {{ photo.user?.name || 'Fotógrafo' }}
                                </p>
                                <div class="flex items-center gap-1">
                                    <button v-if="canDelete(photo) && editingPriceId !== photo.id" @click.stop="startEditingPrice(photo)"
                                            class="w-6 h-6 bg-gray-50 text-gray-400 hover:bg-brand-blue hover:text-white rounded-lg flex items-center justify-center transition-all">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                    </button>
                                    <button v-if="canDelete(photo)" @click.stop="deletePhoto(photo.id)"
                                            class="w-6 h-6 bg-red-50 text-red-300 hover:bg-red-500 hover:text-white rounded-lg flex items-center justify-center transition-all">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </div>

                            <div v-if="editingPriceId === photo.id" class="flex items-center gap-1">
                                <input type="number" step="0.01" v-model="priceForm.price" 
                                       class="w-full h-8 bg-gray-50 border-none rounded-lg px-2 font-black text-brand-dark text-[10px] focus:ring-1 focus:ring-brand-blue/20" />
                                <button @click.stop="submitPriceUpdate(photo.id)" :disabled="priceForm.processing"
                                        class="bg-brand-blue text-white p-1.5 rounded-lg hover:bg-brand-dark transition-all">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                                </button>
                                <button @click.stop="cancelEditingPrice"
                                        class="bg-gray-100 text-gray-400 p-1.5 rounded-lg hover:bg-gray-200 transition-all">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>
                            <p v-else class="text-xs font-black text-brand-dark leading-none">R$ {{ Number(photo.price).toFixed(2) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lightbox -->
        <Transition enter-active-class="duration-300 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="duration-200 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="lightboxIndex !== null" class="fixed inset-0 z-[200] flex items-center justify-center bg-brand-dark/95 backdrop-blur-md p-4" @click.self="closeLightbox">
                <button @click="closeLightbox" class="absolute top-6 right-6 text-white/40 hover:text-white">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>

                <button @click="prevPhoto" class="absolute left-6 text-white/40 hover:text-white hidden md:block">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <button @click="nextPhoto" class="absolute right-6 text-white/40 hover:text-white hidden md:block">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>

                <div class="max-w-2xl w-full flex flex-col items-center gap-6">
                    <img v-if="currentLightboxPhoto" :src="'/' + currentLightboxPhoto.watermarked_path" class="max-h-[80vh] rounded-xl shadow-2xl border-4 border-white/10" />

                    <div class="bg-white/10 backdrop-blur-md px-8 py-4 rounded-xl flex items-center gap-8 text-white">
                        <div class="text-center">
                            <p class="text-[8px] text-brand-orange uppercase font-black tracking-widest mb-1">Fotógrafo</p>
                            <p class="text-sm font-black">{{ currentLightboxPhoto?.user?.name || '—' }}</p>
                        </div>
                        <div class="text-center">
                            <p class="text-[8px] text-brand-orange uppercase font-black tracking-widest mb-1">Preço</p>
                            <p class="text-xl font-black">R$ {{ Number(currentLightboxPhoto?.price).toFixed(2) }}</p>
                        </div>
                        <button v-if="currentLightboxPhoto && canDelete(currentLightboxPhoto)"
                                @click="() => { deletePhoto(currentLightboxPhoto.id); closeLightbox(); }"
                                class="bg-red-500/90 hover:bg-red-500 text-white px-6 py-2.5 rounded-lg font-black text-[9px] uppercase tracking-widest transition-all">
                            Excluir
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </PhotographerLayout>
</template>
