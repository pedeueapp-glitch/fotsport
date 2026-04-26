<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AdminSidebarLayout from '@/Layouts/AdminSidebarLayout.vue';
import { alert } from '@/utils/swal';

const props = defineProps({
    photographer: Object
});

const form = useForm({
    name: props.photographer.name,
    email: props.photographer.email,
    is_active: props.photographer.is_active,
    is_verified: props.photographer.is_verified,
    is_superadmin: props.photographer.is_superadmin,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.patch(route('admin.photographers.update', props.photographer.id), {
        onSuccess: () => alert('Sucesso', 'Informações atualizadas!', 'success')
    });
};
</script>

<template>
    <Head :title="`Editar: ${photographer.name}`" />

    <AdminSidebarLayout title="Editar Perfil" subtitle="Gestão global de acessos e permissões.">
        <template #actions>
            <div v-if="photographer.avatar" class="w-12 h-12 rounded-xl overflow-hidden border-2 border-white shadow-sm ring-1 ring-gray-100">
                <img :src="photographer.avatar.startsWith('http') ? photographer.avatar : (photographer.avatar.startsWith('storage/') ? '/' + photographer.avatar : '/storage/' + photographer.avatar)" 
                     class="w-full h-full object-cover" />
            </div>
        </template>

        <div class="max-w-2xl">
            <form @submit.prevent="submit" class="space-y-6 bg-gray-50 p-8 rounded-xl border border-gray-100 shadow-sm">
                <div class="space-y-2">
                    <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest ml-1">Nome Completo</label>
                    <input v-model="form.name" type="text" class="w-full h-12 bg-white border border-gray-200 rounded-xl px-5 font-bold text-xs focus:ring-2 focus:ring-brand-blue/10 focus:border-brand-blue" required />
                </div>

                <div class="space-y-2">
                    <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest ml-1">E-mail de Acesso</label>
                    <input v-model="form.email" type="email" class="w-full h-12 bg-white border border-gray-200 rounded-xl px-5 font-bold text-xs focus:ring-2 focus:ring-brand-blue/10 focus:border-brand-blue" required />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="p-4 bg-white rounded-xl border border-gray-100 flex items-center justify-between shadow-sm">
                        <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Conta Ativa</span>
                        <input v-model="form.is_active" type="checkbox" class="w-5 h-5 rounded text-brand-blue border-gray-200 focus:ring-brand-blue/10" />
                    </div>
                    <div class="p-4 bg-white rounded-xl border border-gray-100 flex items-center justify-between shadow-sm">
                        <span class="text-[9px] font-black text-blue-500 uppercase tracking-widest">Verificado</span>
                        <input v-model="form.is_verified" type="checkbox" class="w-5 h-5 rounded text-blue-500 border-gray-200 focus:ring-blue-500/10" />
                    </div>
                    <div class="p-4 bg-white rounded-xl border border-gray-100 flex items-center justify-between shadow-sm">
                        <span class="text-[9px] font-black text-brand-orange uppercase tracking-widest">SuperAdmin</span>
                        <input v-model="form.is_superadmin" type="checkbox" class="w-5 h-5 rounded text-brand-orange border-gray-200 focus:ring-brand-orange/10" />
                    </div>
                </div>

                <div class="pt-6 border-t border-gray-200 space-y-4">
                    <p class="text-[9px] font-black text-red-400 uppercase tracking-widest ml-1">Alterar Senha (Opcional)</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <input v-model="form.password" type="password" placeholder="Nova senha" class="w-full h-12 bg-white border border-gray-200 rounded-xl px-5 font-bold text-xs focus:ring-2 focus:ring-brand-blue/10 focus:border-brand-blue" />
                        <input v-model="form.password_confirmation" type="password" placeholder="Confirmar nova senha" class="w-full h-12 bg-white border border-gray-200 rounded-xl px-5 font-bold text-xs focus:ring-2 focus:ring-brand-blue/10 focus:border-brand-blue" />
                    </div>
                </div>

                <div class="pt-4">
                    <button :disabled="form.processing" class="w-full py-4 bg-brand-dark text-white rounded-xl font-black text-[10px] uppercase tracking-widest shadow-xl active:scale-95 transition-all">
                        {{ form.processing ? 'Salvando...' : 'Salvar Alterações' }}
                    </button>
                </div>
            </form>
        </div>
    </AdminSidebarLayout>
</template>


