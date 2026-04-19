<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AdminSidebarLayout from '@/Layouts/AdminSidebarLayout.vue';
import { alert, confirm } from '@/utils/swal';

const props = defineProps({ event: Object });

const photos = computed(() => props.event.photos ?? []);
const selectedIds = ref([]);

const allSelected = computed(() =>
    photos.value.length > 0 && photos.value.every(p => selectedIds.value.includes(p.id))
);

const toggleSelectAll = () => {
    allSelected.value
        ? selectedIds.value = []
        : selectedIds.value = photos.value.map(p => p.id);
};

const toggleSelect = (id) => {
    const idx = selectedIds.value.indexOf(id);
    idx === -1 ? selectedIds.value.push(id) : selectedIds.value.splice(idx, 1);
};

const deletePhoto = async (photo) => {
    const result = await confirm('Excluir Foto', `Excluir foto do fotógrafo "${photo.user?.name}"? Ação irreversível.`);
    if (!result.isConfirmed) return;
    router.delete(route('admin.events.photos.destroy', { event: props.event.slug, photo: photo.id }), {
        preserveScroll: true,
        onSuccess: () => alert('Excluída', 'Foto excluída do servidor.', 'success')
    });
};

const bulkDelete = async () => {
    if (!selectedIds.value.length) return;
    const result = await confirm(
        'Excluir em Massa',
        `Excluir permanentemente ${selectedIds.value.length} foto(s)? Ação irreversível.`
    );
    if (!result.isConfirmed) return;
    router.delete(route('admin.events.photos.bulk-destroy', props.event.slug), {
        data: { photo_ids: selectedIds.value },
        preserveScroll: true,
        onSuccess: () => {
            selectedIds.value = [];
            alert('Sucesso', 'Fotos excluídas.', 'success');
        }
    });
};

const deleteEvent = async () => {
    const result = await confirm(
        '⚠️ EXCLUIR EVENTO',
        `Excluir "${props.event.name}" e TODAS as ${photos.value.length} fotos? Esta ação é IRREVERSÍVEL e apagará os arquivos do servidor.`
    );
    if (!result.isConfirmed) return;
    router.delete(route('admin.events.destroy', props.event.slug), {
        onSuccess: () => alert('Evento excluído', 'Todos os arquivos foram removidos do servidor.', 'success')
    });
};

// Lightbox
const lightboxIndex = ref(null);
const currentPhoto = computed(() => lightboxIndex.value !== null ? photos.value[lightboxIndex.value] : null);
const openLightbox = (i) => { lightboxIndex.value = i; };
const closeLightbox = () => { lightboxIndex.value = null; };
const prevPhoto = () => { lightboxIndex.value = (lightboxIndex.value - 1 + photos.value.length) % photos.value.length; };
const nextPhoto = () => { lightboxIndex.value = (lightboxIndex.value + 1) % photos.value.length; };
</script>

<template>
    <Head :title="`Admin: ${event.name}`" />

    <AdminSidebarLayout :title="event.name" :subtitle="`${event.photos.length} fotos • ${event.location} • ${event.date}`">
        <template #actions>
            <div class="flex items-center gap-3 flex-wrap">
                <!-- Toolbar de seleção -->
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
                            class="px-3 py-2 bg-gray-100 text-gray-500 font-black text-[9px] uppercase tracking-widest rounded-lg hover:bg-gray-200 transition-all">
                            Cancelar
                        </button>
                    </div>
                </Transition>

                <Link :href="route('admin.events.index')"
                      class="px-4 py-2.5 bg-white border border-gray-200 text-gray-500 font-black text-[9px] uppercase tracking-widest rounded-lg hover:border-brand-dark transition-all">
                    ← Voltar
                </Link>
                <a :href="route('store.event', event)" target="_blank"
                   class="px-4 py-2.5 bg-white border border-gray-200 text-gray-400 hover:text-brand-blue hover:border-brand-blue font-black text-[9px] uppercase tracking-widest rounded-lg transition-all">
                    Página Pública ↗
                </a>
                <button @click="deleteEvent"
                        class="px-5 py-2.5 bg-red-500 text-white font-black text-[9px] uppercase tracking-widest rounded-lg hover:bg-red-600 transition-all active:scale-95 shadow-lg">
                    🗑 Excluir Evento
                </button>
            </div>
        </template>

        <!-- Select All bar -->
        <div v-if="photos.length > 0" class="flex items-center justify-between mb-4">
            <label class="flex items-center gap-2 cursor-pointer group">
                <div @click="toggleSelectAll"
                     :class="allSelected ? 'bg-brand-blue border-brand-blue' : 'border-gray-300 hover:border-brand-blue'"
                     class="w-4 h-4 border-2 rounded flex items-center justify-center transition-all">
                    <svg v-if="allSelected" class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
                    </svg>
                </div>
                <span class="text-[9px] font-black uppercase tracking-widest text-gray-400 group-hover:text-brand-dark transition-colors">
                    Selecionar todas ({{ photos.length }})
                </span>
            </label>
            <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest">
                Criado por: <span class="text-brand-dark">{{ event.user?.name || 'Sistema' }}</span>
            </span>
        </div>

        <!-- Empty state -->
        <div v-if="photos.length === 0" class="py-20 text-center bg-gray-50 rounded-xl border border-dashed border-gray-200">
            <p class="text-gray-300 font-black uppercase tracking-widest text-xs">Nenhuma foto neste evento.</p>
        </div>

        <!-- Grid de fotos -->
        <div v-else class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
            <div v-for="(photo, index) in photos" :key="photo.id"
                 :class="selectedIds.includes(photo.id) ? 'ring-2 ring-brand-blue' : ''"
                 class="group relative bg-white rounded-xl overflow-hidden border border-gray-100 shadow-sm hover:shadow-lg transition-all duration-300">

                <!-- Checkbox -->
                <div @click.stop="toggleSelect(photo.id)" class="absolute top-2 left-2 z-10">
                    <div :class="selectedIds.includes(photo.id) ? 'bg-brand-blue border-brand-blue' : 'bg-white/80 border-gray-300 group-hover:border-brand-blue'"
                         class="w-5 h-5 border-2 rounded flex items-center justify-center cursor-pointer transition-all shadow">
                        <svg v-if="selectedIds.includes(photo.id)" class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
                        </svg>
                    </div>
                </div>

                <!-- Imagem -->
                <div class="aspect-square overflow-hidden cursor-pointer" @click="openLightbox(index)">
                    <img :src="'/' + photo.watermarked_path" class="w-full h-full object-cover group-hover:scale-105 transition duration-500" />
                </div>

                <!-- Info + Delete -->
                <div class="p-3 flex justify-between items-center border-t border-gray-50">
                    <div>
                        <p class="text-[7px] text-brand-orange font-black uppercase tracking-widest truncate max-w-[80px]">
                            {{ photo.user?.name || 'Fotógrafo' }}
                        </p>
                        <p class="text-xs font-black text-brand-dark">R$ {{ Number(photo.price).toFixed(2) }}</p>
                    </div>
                    <button @click.stop="deletePhoto(photo)"
                            class="w-7 h-7 bg-red-50 text-red-300 hover:bg-red-500 hover:text-white rounded-lg flex items-center justify-center transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Lightbox -->
        <Transition enter-active-class="duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100">
            <div v-if="lightboxIndex !== null"
                 class="fixed inset-0 z-[200] flex items-center justify-center bg-brand-dark/95 backdrop-blur-md p-4"
                 @click.self="closeLightbox">
                <button @click="closeLightbox" class="absolute top-6 right-6 text-white/40 hover:text-white">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
                <button @click="prevPhoto" class="absolute left-4 text-white/40 hover:text-white hidden md:block">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <button @click="nextPhoto" class="absolute right-4 text-white/40 hover:text-white hidden md:block">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>

                <div class="max-w-2xl w-full flex flex-col items-center gap-6">
                    <img v-if="currentPhoto" :src="'/' + currentPhoto.watermarked_path" class="max-h-[78vh] rounded-xl shadow-2xl border-4 border-white/10" />
                    <div class="bg-white/10 backdrop-blur-md px-8 py-4 rounded-xl flex items-center gap-8 text-white">
                        <div class="text-center">
                            <p class="text-[8px] text-brand-orange uppercase font-black tracking-widest mb-1">Fotógrafo</p>
                            <p class="text-sm font-black">{{ currentPhoto?.user?.name || '—' }}</p>
                        </div>
                        <div class="text-center">
                            <p class="text-[8px] text-brand-orange uppercase font-black tracking-widest mb-1">Preço</p>
                            <p class="text-xl font-black">R$ {{ Number(currentPhoto?.price).toFixed(2) }}</p>
                        </div>
                        <button @click="deletePhoto(currentPhoto); closeLightbox()"
                                class="bg-red-500 hover:bg-red-600 text-white px-5 py-2.5 rounded-lg font-black text-[9px] uppercase tracking-widest transition-all">
                            Excluir
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </AdminSidebarLayout>
</template>
