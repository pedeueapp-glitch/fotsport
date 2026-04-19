<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import AdminSidebarLayout from '@/Layouts/AdminSidebarLayout.vue';
import { ref } from 'vue';
import { alert, confirm } from '@/utils/swal';

const props = defineProps({
    items: Array
});

const showModal = ref(false);
const editingItem = ref(null);
const imagePreview = ref(null);

const form = useForm({
    title: '',
    subtitle: '',
    image: null,
    is_active: true
});

const openCreate = () => {
    editingItem.value = null;
    form.reset();
    imagePreview.value = null;
    showModal.value = true;
};

const openEdit = (item) => {
    editingItem.value = item;
    form.title = item.title;
    form.subtitle = item.subtitle;
    form.is_active = !!item.is_active;
    form.image = null;
    imagePreview.value = item.image_path;
    showModal.value = true;
};

const onFileChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.image = file;
        imagePreview.value = URL.createObjectURL(file);
    }
};

const submit = () => {
    if (editingItem.value) {
        form.post(route('admin.hero.update', editingItem.value.id), {
            onSuccess: () => {
                showModal.value = false;
                alert('Sucesso', 'Item atualizado.', 'success');
            }
        });
    } else {
        form.post(route('admin.hero.store'), {
            onSuccess: () => {
                showModal.value = false;
                form.reset();
                imagePreview.value = null;
                alert('Sucesso', 'Item adicionado.', 'success');
            }
        });
    }
};

const deleteItem = async (item) => {
    const result = await confirm('Excluir Item', `Deseja realmente excluir este banner?`);
    if (result.isConfirmed) {
        router.delete(route('admin.hero.destroy', item.id), {
            onSuccess: () => alert('Sucesso', 'Banner excluído.', 'success')
        });
    }
};
</script>

<template>
    <Head title="Gerenciar Carrossel" />

    <AdminSidebarLayout title="Banner Principal" subtitle="Imagens que aparecem no topo da página inicial.">
        <template #actions>
            <button @click="openCreate" class="px-5 py-2.5 bg-brand-dark text-white font-black uppercase tracking-widest text-[9px] rounded-lg hover:bg-brand-blue transition-all active:scale-95 shadow-sm">
                Novo Banner
            </button>
        </template>

        <div class="space-y-4">
            <div v-for="item in items" :key="item.id" 
                 class="bg-white border rounded-xl overflow-hidden flex items-center gap-6 p-4 group hover:border-brand-blue transition-all">
                
                <div class="w-48 h-24 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                    <img :src="item.image_path" class="w-full h-full object-cover">
                </div>

                <div class="flex-grow">
                    <h4 class="text-xs font-black text-brand-dark uppercase tracking-wide truncate mb-1">{{ item.title || 'Sem título' }}</h4>
                    <p class="text-[10px] text-gray-400 font-medium truncate">{{ item.subtitle || 'Sem subtítulo' }}</p>
                </div>

                <div class="flex items-center gap-3 pr-4">
                     <span :class="item.is_active ? 'bg-green-50 text-green-600 border-green-100' : 'bg-gray-50 text-gray-400 border-gray-100'"
                           class="px-3 py-1 rounded-full text-[7px] font-black uppercase tracking-widest border">
                        {{ item.is_active ? 'Ativo' : 'Inativo' }}
                    </span>

                    <button @click="openEdit(item)" class="p-2 bg-gray-50 text-gray-400 hover:bg-brand-blue hover:text-white rounded-lg transition-all border border-gray-100">
                         <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </button>
                    
                    <button @click="deleteItem(item)" class="p-2 bg-red-50 text-red-300 hover:bg-red-500 hover:text-white rounded-lg transition-all border border-red-100">
                         <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                    </button>
                </div>
            </div>

            <div v-if="items.length === 0" class="py-20 text-center bg-gray-50 rounded-xl border border-dashed border-gray-200 text-gray-400 font-black uppercase tracking-widest text-xs">
                Nenhum banner cadastrado.
            </div>
        </div>

        <!-- Modal Banner -->
        <Transition enter-active-class="duration-300 ease-out" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="duration-200 ease-in" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <div v-if="showModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-brand-dark/40 backdrop-blur-sm">
                <div class="bg-white w-full max-w-lg rounded-2xl p-8 shadow-2xl relative border border-gray-100">
                    <button @click="showModal = false" class="absolute top-6 right-6 text-gray-300 hover:text-brand-dark">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>

                    <h3 class="text-xl font-black text-brand-dark uppercase tracking-tighter mb-8">
                        {{ editingItem ? 'Editar' : 'Novo' }} <span class="text-brand-orange">Banner</span>
                    </h3>

                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="col-span-2">
                                <label class="block text-[8px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Imagem do Banner</label>
                                <div class="flex flex-col gap-4">
                                    <div v-if="imagePreview" class="w-full h-40 border rounded-xl overflow-hidden bg-gray-50">
                                        <img :src="imagePreview" class="w-full h-full object-cover">
                                    </div>
                                    <label class="cursor-pointer">
                                        <input type="file" @change="onFileChange" class="hidden" accept="image/*">
                                        <div class="w-full py-4 bg-gray-50 border border-dashed border-gray-300 rounded-xl text-[9px] font-black text-gray-400 uppercase tracking-widest text-center hover:bg-gray-100 transition-all">
                                            {{ form.image ? 'Trocar Imagem' : 'Selecionar Imagem do Banner' }}
                                        </div>
                                    </label>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-[8px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Título (Opcional)</label>
                                <input v-model="form.title" type="text" class="w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-xl text-xs font-bold" placeholder="Ex: Performance!">
                            </div>

                            <div>
                                <label class="block text-[8px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Subtítulo (Opcional)</label>
                                <input v-model="form.subtitle" type="text" class="w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-xl text-xs font-bold" placeholder="Ex: Encontre suas fotos">
                            </div>

                            <div class="col-span-2 flex items-center gap-3 ml-1">
                                <input type="checkbox" v-model="form.is_active" id="is_active" class="w-4 h-4 text-brand-blue border-gray-300 rounded focus:ring-brand-blue">
                                <label for="is_active" class="text-[9px] font-black text-gray-500 uppercase tracking-widest cursor-pointer">Visível no site</label>
                            </div>
                        </div>

                        <button :disabled="form.processing" class="w-full py-4 bg-brand-blue text-white font-black uppercase tracking-widest text-[9px] rounded-xl shadow-lg shadow-brand-blue/10 active:scale-95 transition-all disabled:opacity-50">
                            {{ form.processing ? 'Salvando...' : 'Salvar Banner' }}
                        </button>
                    </form>
                </div>
            </div>
        </Transition>
    </AdminSidebarLayout>
</template>
