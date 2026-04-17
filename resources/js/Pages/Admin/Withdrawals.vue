<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

import { confirm } from '@/utils/swal';

const props = defineProps({
    withdrawals: Array
});

const payForm = useForm({});

const authorizePayment = async (id) => {
    const result = await confirm('Autorizar Pagamento', 'Tem certeza que deseja enviar o PIX para o fotógrafo agora?');
    if (result.isConfirmed) {
        payForm.post(route('admin.withdrawals.authorize', id), {
            preserveScroll: true
        });
    }
};
</script>

<template>
    <Head title="Admin - Solicitações de Saque" />

    <div class="min-h-screen bg-white flex flex-col font-sans text-brand-dark">
        <Navbar />

        <main class="flex-grow max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 w-full">
            <!-- Header -->
            <header class="mb-20">
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-8">
                    <div class="max-w-2xl">
                        <div class="inline-flex items-center gap-2 mb-4">
                            <div class="h-px w-8 bg-brand-orange"></div>
                            <span class="text-[10px] font-black text-brand-orange uppercase tracking-[.4em]">Painel de Controle</span>
                        </div>
                        <h1 class="text-4xl md:text-6xl font-black text-brand-dark uppercase tracking-tighter mb-4">
                            Gestão de <span class="text-brand-blue">Saques</span>
                        </h1>
                        <p class="text-gray-500 text-lg font-medium">
                            Analise as solicitações de pagamento pendentes e autorize as transferências via PIX.
                        </p>
                    </div>
                    
                    <div class="px-6 py-3 bg-brand-blue/10 border border-brand-blue/20 rounded-2xl flex items-center gap-3">
                        <div class="w-3 h-3 bg-brand-blue rounded-full animate-pulse"></div>
                        <span class="text-[10px] font-black text-brand-blue uppercase tracking-widest">Nível: Super Admin</span>
                    </div>
                </div>
                <div class="mt-10 h-1.5 w-20 bg-brand-orange rounded-full"></div>
            </header>

            <!-- Tabela de Saques Admin -->
            <div class="bg-gray-50 rounded-[3.5rem] p-12 md:p-20 border border-gray-100 overflow-hidden shadow-sm">
                <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-8 mb-12">
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <div class="h-1 w-4 bg-brand-blue rounded-full"></div>
                            <span class="text-[10px] font-black text-brand-blue uppercase tracking-widest">Aguardando Aprovação</span>
                        </div>
                        <h3 class="text-3xl font-black text-brand-dark uppercase tracking-tighter">Pedidos <span class="text-brand-blue">Pendentes</span></h3>
                    </div>
                </header>

                <div v-if="withdrawals.length === 0" class="py-20 text-center flex flex-col items-center">
                    <div class="w-20 h-20 bg-white rounded-3xl shadow-xl flex items-center justify-center text-gray-200 mb-6">
                         <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.3em]">Tudo em dia! Nenhuma solicitação pendente.</p>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-200">
                                <th class="pb-6 pl-4">Fotógrafo</th>
                                <th class="pb-6">Chave PIX / Documento</th>
                                <th class="pb-6">Valor Bruto</th>
                                <th class="pb-6">Líquido a Pagar</th>
                                <th class="pb-6 pr-4 text-right">Ação</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="withdrawal in withdrawals" :key="withdrawal.id" class="group hover:bg-white transition-colors">
                                <td class="py-10 pl-4 transition-colors">
                                    <div class="font-black text-brand-dark text-lg uppercase tracking-tight">{{ withdrawal.user.name }}</div>
                                    <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest mt-1">{{ withdrawal.user.email }}</div>
                                </td>
                                <td class="py-10">
                                    <div class="inline-flex flex-col gap-2">
                                        <div class="px-4 py-2 bg-brand-blue/5 border border-brand-blue/10 rounded-xl text-brand-blue text-xs font-bold tracking-tight">
                                            🔑 {{ withdrawal.user.pix_key }}
                                        </div>
                                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">CPF/CNPJ: {{ withdrawal.user.document }}</span>
                                    </div>
                                </td>
                                <td class="py-10 font-black text-gray-400 text-sm">R$ {{ Number(withdrawal.request_amount).toFixed(2) }}</td>
                                <td class="py-10 font-black text-brand-dark text-2xl tracking-tighter">R$ {{ Number(withdrawal.net_amount).toFixed(2) }}</td>
                                <td class="py-10 pr-4 text-right">
                                    <div v-if="withdrawal.status === 'pending'">
                                        <button @click="authorizePayment(withdrawal.id)" 
                                                class="px-8 py-4 bg-brand-orange hover:bg-brand-orange-hover text-white font-black text-[10px] uppercase tracking-widest rounded-2xl shadow-xl shadow-brand-orange/20 transition-all active:scale-95">
                                            Autorizar PIX
                                        </button>
                                    </div>
                                    <div v-else class="inline-flex items-center gap-3 px-6 py-3 bg-green-100 text-green-700 rounded-2xl">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                        <span class="text-[10px] font-black uppercase tracking-widest">Pago</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
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
