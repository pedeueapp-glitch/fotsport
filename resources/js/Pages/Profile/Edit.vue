<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import PhotographerLayout from '@/Layouts/PhotographerLayout.vue';
import AdminSidebarLayout from '@/Layouts/AdminSidebarLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { computed } from 'vue';

defineProps({
    mustVerifyEmail: Boolean,
    status: String,
});

const { auth } = usePage().props;
const isSuperAdmin = computed(() => auth.user.is_superadmin);
const Layout = computed(() => isSuperAdmin.value ? AdminSidebarLayout : PhotographerLayout);
</script>

<template>
    <Head title="Meu Perfil" />

    <component :is="Layout" title="Meu Perfil" subtitle="Gerencie suas informações pessoais e segurança da conta.">
        <div class="space-y-12">
            <!-- Informações do Perfil -->
            <div class="bg-gray-50 rounded-3xl p-8 md:p-12 border border-gray-100 shadow-sm relative overflow-hidden group">
                 <div class="absolute -top-24 -right-24 w-64 h-64 bg-brand-blue/5 rounded-full blur-3xl group-hover:bg-brand-blue/10 transition-all duration-700"></div>
                 <UpdateProfileInformationForm
                    :must-verify-email="mustVerifyEmail"
                    :status="status"
                    class="relative z-10"
                />
            </div>

            <!-- Alterar Senha -->
            <div class="bg-gray-50 rounded-3xl p-8 md:p-12 border border-gray-100 shadow-sm relative overflow-hidden group">
                <div class="absolute -bottom-24 -left-24 w-64 h-64 bg-brand-orange/5 rounded-full blur-3xl group-hover:bg-brand-orange/10 transition-all duration-700"></div>
                <UpdatePasswordForm class="relative z-10" />
            </div>

            <!-- Perigo: Deletar Conta -->
            <div class="bg-red-50 rounded-3xl p-8 md:p-12 border border-red-100 shadow-sm relative overflow-hidden group">
                <DeleteUserForm class="relative z-10" />
            </div>
        </div>
    </component>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;900&display=swap');

.font-sans {
    font-family: 'Inter', sans-serif;
}
</style>
