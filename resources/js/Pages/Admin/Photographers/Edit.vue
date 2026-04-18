<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';
import { alert } from '@/utils/swal';

const props = defineProps({
    photographer: Object
});

const form = useForm({
    name: props.photographer.name,
    email: props.photographer.email,
    is_active: props.photographer.is_active,
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

    <div class="min-h-screen bg-white flex flex-col font-sans text-brand-dark">
        <Navbar />

        <main class="flex-grow max-w-2xl mx-auto px-4 py-20 w-full">
            <header class="mb-12 flex items-center gap-6">
                <div v-if="photographer.avatar" class="w-20 h-20 rounded-3xl overflow-hidden shadow-2xl border-4 border-white shrink-0">
                    <img :src="photographer.avatar.startsWith('http') ? photographer.avatar : (photographer.avatar.startsWith('storage/') ? '/' + photographer.avatar : '/storage/' + photographer.avatar)" 
                         class="w-full h-full object-cover" />
                </div>
                <div>
                    <h1 class="text-3xl font-black text-brand-dark uppercase tracking-tighter">Editar <span class="text-brand-orange">Fotógrafo</span></h1>
                    <p class="text-gray-400 font-medium italic">Edição global de perfil e permissões.</p>
                </div>
            </header>

            <form @submit.prevent="submit" class="space-y-8 bg-gray-50 p-10 rounded-[2.5rem] border border-gray-100 shadow-sm">
                <div class="space-y-4">
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest">Nome Completo</label>
                    <input v-model="form.name" type="text" class="w-full h-14 bg-white border-none rounded-2xl px-6 font-bold shadow-sm focus:ring-2 focus:ring-brand-blue" required />
                </div>

                <div class="space-y-4">
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest">E-mail de Acesso</label>
                    <input v-model="form.email" type="email" class="w-full h-14 bg-white border-none rounded-2xl px-6 font-bold shadow-sm focus:ring-2 focus:ring-brand-blue" required />
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div class="p-6 bg-white rounded-2xl flex items-center justify-between">
                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Conta Ativa</span>
                        <input v-model="form.is_active" type="checkbox" class="w-6 h-6 rounded-lg text-brand-blue border-gray-100 focus:ring-brand-blue" />
                    </div>
                    <div class="p-6 bg-white rounded-2xl flex items-center justify-between">
                        <span class="text-[10px] font-black text-brand-orange uppercase tracking-widest">SuperAdmin</span>
                        <input v-model="form.is_superadmin" type="checkbox" class="w-6 h-6 rounded-lg text-brand-orange border-gray-100 focus:ring-brand-orange" />
                    </div>
                </div>

                <div class="pt-6 border-t border-gray-200 space-y-6">
                    <p class="text-[10px] font-black text-red-400 uppercase tracking-widest">Alterar Senha (Opcional)</p>
                    <input v-model="form.password" type="password" placeholder="Nova senha" class="w-full h-14 bg-white border-none rounded-2xl px-6 font-bold shadow-sm focus:ring-2 focus:ring-brand-blue" />
                    <input v-model="form.password_confirmation" type="password" placeholder="Confirmar nova senha" class="w-full h-14 bg-white border-none rounded-2xl px-6 font-bold shadow-sm focus:ring-2 focus:ring-brand-blue" />
                </div>

                <button :disabled="form.processing" class="w-full py-5 bg-brand-dark text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-xl active:scale-95 transition-all">
                    Salvar Alterações
                </button>
            </form>
        </main>

        <Footer />
    </div>
</template>
