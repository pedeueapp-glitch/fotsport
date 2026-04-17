<script setup>
import { Head } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';

defineProps({
    mustVerifyEmail: Boolean,
    status: String,
});
</script>

<template>
    <Head title="Meu Perfil" />

    <div class="min-h-screen bg-white flex flex-col font-sans text-brand-dark">
        <Navbar />

        <main class="flex-grow max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 w-full">
            <!-- Header -->
            <header class="mb-20">
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-8">
                    <div class="max-w-2xl">
                        <div class="inline-flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 bg-brand-orange/10 rounded-xl flex items-center justify-center text-brand-orange">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </div>
                            <span class="text-[10px] font-black text-brand-orange uppercase tracking-[.4em]">Configurações da Conta</span>
                        </div>
                        <h1 class="text-4xl md:text-6xl font-black text-brand-dark uppercase tracking-tighter mb-4">
                            Meu <span class="text-brand-blue">Perfil</span>
                        </h1>
                        <p class="text-gray-500 text-lg font-medium">
                            Mantenha seus dados atualizados para atrair mais clientes e gerenciar sua conta.
                        </p>
                    </div>
                </div>
                <div class="mt-10 h-1.5 w-20 bg-brand-orange rounded-full"></div>
            </header>

            <div class="space-y-16">
                <!-- Informações do Perfil -->
                <div class="bg-gray-50 rounded-[3rem] p-12 md:p-16 border border-gray-100 shadow-sm relative overflow-hidden group">
                     <div class="absolute -top-24 -right-24 w-64 h-64 bg-brand-blue/5 rounded-full blur-3xl group-hover:bg-brand-blue/10 transition-all duration-700"></div>
                     <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                        class="relative z-10"
                    />
                </div>

                <!-- Alterar Senha -->
                <div class="bg-gray-50 rounded-[3rem] p-12 md:p-16 border border-gray-100 shadow-sm relative overflow-hidden group">
                    <div class="absolute -bottom-24 -left-24 w-64 h-64 bg-brand-orange/5 rounded-full blur-3xl group-hover:bg-brand-orange/10 transition-all duration-700"></div>
                    <UpdatePasswordForm class="relative z-10" />
                </div>

                <!-- Perigo: Deletar Conta -->
                <div class="bg-red-50 rounded-[3rem] p-12 md:p-16 border border-red-100 shadow-sm relative overflow-hidden group">
                    <DeleteUserForm class="relative z-10" />
                </div>
            </div>
        </main>

        <Footer />
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;900&display=swap');

.font-sans {
    font-family: 'Inter', sans-serif;
}
</style>
