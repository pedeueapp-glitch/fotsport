<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import AdminSidebarLayout from '@/Layouts/AdminSidebarLayout.vue';
import { ref } from 'vue';
import { alert, confirm } from '@/utils/swal';

const props = defineProps({
    brands: Array
});

const showModal = ref(false);

const editingBrand = ref(null);
const logoPreview = ref(null);

const form = useForm({
    name: '',
    logo: null
});

const openCreate = () => {
    editingBrand.value = null;
    form.reset();
    logoPreview.value = null;
    showModal.value = true;
};

const openEdit = (brand) => {
    editingBrand.value = brand;
    form.name = brand.name;
    form.logo = null;
    logoPreview.value = brand.logo_path;
    showModal.value = true;
};

const onFileChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.logo = file;
        logoPreview.value = URL.createObjectURL(file);
    }
};

const submit = () => {
    if (editingBrand.value) {
        form.post(route('admin.brands.update', editingBrand.value.id), {
            onSuccess: () => {
                showModal.value = false;
                alert('Sucesso', 'Marca atualizada.', 'success');
            }
        });
    } else {
        form.post(route('admin.brands.store'), {
            onSuccess: () => {
                showModal.value = false;
                form.reset();
                logoPreview.value = null;
                alert('Sucesso', 'Marca adicionada.', 'success');
            }
        });
    }
};

const toggleStatus = (brand) => {
    router.patch(route('admin.brands.toggle', brand.id));
};

const deleteBrand = async (brand) => {
    const result = await confirm('Excluir Marca', `Deseja realmente excluir "${brand.name}"?`);
    if (result.isConfirmed) {
        router.delete(route('admin.brands.destroy', brand.id), {
            onSuccess: () => alert('Sucesso', 'Marca excluída.', 'success')
        });
    }
};
</script>

<template>
    <Head title="Gerenciar Marcas" />

    <AdminSidebarLayout title="Gestão de Marcas" subtitle="Configure as marcas que aparecem no carrossel da home.">
        <template #actions>
            <button @click="openCreate" class="px-5 py-2.5 bg-brand-dark text-white font-black uppercase tracking-widest text-[9px] rounded-lg hover:bg-brand-blue transition-all active:scale-95 shadow-sm">
                Nova Marca
            </button>
        </template>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div v-for="brand in brands" :key="brand.id" 
                 class="bg-white border p-6 rounded-xl relative group transition-all hover:border-brand-blue hover:shadow-lg">
                
                <div class="h-20 flex items-center justify-center mb-6">
                    <img :src="brand.logo_path" :alt="brand.name" class="max-h-full object-contain filter grayscale group-hover:grayscale-0 transition-all duration-500">
                </div>

                <div class="space-y-3">
                    <h4 class="text-[10px] font-black text-brand-dark uppercase tracking-widest text-center truncate">{{ brand.name }}</h4>
                    
                    <div class="flex items-center justify-center gap-2">
                        <button @click="openEdit(brand)"
                                class="p-1.5 bg-gray-50 text-gray-400 hover:bg-brand-blue hover:text-white rounded-lg border border-gray-100 transition-all">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>
                        <button @click="toggleStatus(brand)"
                                :class="brand.is_active ? 'bg-green-50 text-green-600 border-green-100' : 'bg-gray-50 text-gray-400 border-gray-100'"
                                class="px-3 py-1 rounded text-[7px] font-black uppercase tracking-widest border transition-all">
                            {{ brand.is_active ? 'Ativa' : 'Pausada' }}
                        </button>
                        <button @click="deleteBrand(brand)"
                                class="p-1.5 bg-red-50 text-red-300 hover:bg-red-500 hover:text-white rounded-lg border border-red-100 transition-all">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </button>
                    </div>
                </div>
            </div>
            
            <div v-if="brands.length === 0" class="col-span-full py-12 text-center bg-gray-50 rounded-xl border border-dashed border-gray-200 text-gray-400 text-xs font-black uppercase tracking-widest">
                Nenhuma marca cadastrada ainda.
            </div>
        </div>

        <!-- Modal Marca -->
        <Transition enter-active-class="duration-300 ease-out" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="duration-200 ease-in" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <div v-if="showModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-brand-dark/40 backdrop-blur-sm">
                <div class="bg-white w-full max-w-md rounded-2xl p-8 shadow-2xl relative border border-gray-100">
                    <button @click="showModal = false" class="absolute top-6 right-6 text-gray-300 hover:text-brand-dark">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>

                    <h3 class="text-xl font-black text-brand-dark uppercase tracking-tighter mb-6">
                        {{ editingBrand ? 'Editar' : 'Adicionar' }} <span class="text-brand-orange">Marca</span>
                    </h3>

                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <label class="block text-[8px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2 ml-1">Nome da Marca</label>
                            <input v-model="form.name" type="text" class="w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-xl text-xs font-bold" placeholder="Ex: Nike, Adidas..." required>
                        </div>
                        
                        <div>
                            <label class="block text-[8px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2 ml-1">Logo (PNG transparente recomendado)</label>
                            <div class="flex items-center gap-4">
                                <div v-if="logoPreview" class="w-20 h-20 border rounded-xl flex items-center justify-center p-2 bg-gray-50">
                                    <img :src="logoPreview" class="max-h-full object-contain">
                                </div>
                                <label class="flex-grow">
                                    <input type="file" @change="onFileChange" class="hidden" accept="image/*">
                                    <div class="w-full py-3.5 bg-gray-50 border border-dashed border-gray-300 rounded-xl text-[9px] font-black text-gray-400 uppercase tracking-widest text-center cursor-pointer hover:bg-gray-100 transition-all">
                                        {{ form.logo ? 'Trocar Arquivo' : 'Selecionar Logo' }}
                                    </div>
                                </label>
                            </div>
                        </div>

                        <button :disabled="form.processing" class="w-full py-4 bg-brand-blue text-white font-black uppercase tracking-widest text-[9px] rounded-xl shadow-lg shadow-brand-blue/10 active:scale-95 transition-all disabled:opacity-50">
                            {{ form.processing ? (editingBrand ? 'Atualizando...' : 'Cadastrando...') : 'Salvar Marca' }}
                        </button>
                    </form>
                </div>
            </div>
        </Transition>
    </AdminSidebarLayout>
</template>
