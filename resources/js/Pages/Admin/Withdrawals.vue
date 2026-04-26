<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AdminSidebarLayout from '@/Layouts/AdminSidebarLayout.vue';
import { confirm } from '@/utils/swal';

const props = defineProps({
    withdrawals: Array
});



const payForm = useForm({});

const markAsPaidForm = useForm({});

const markAsPaid = async (id) => {
    const result = await confirm('Aprovação Manual', 'Deseja marcar este saque como PAGO manualmente? Use apenas se tiver certeza que o dinheiro foi enviado.');
    if (result.isConfirmed) {
        markAsPaidForm.post(route('admin.withdrawals.mark-as-paid', id), {
            preserveScroll: true
        });
    }
};

const checkStatus = (id) => {
    checkForm.post(route('admin.withdrawals.check', id), {
        preserveScroll: true
    });
};

const authorizePayment = async (id) => {
    const result = await confirm('Autorizar Pagamento', 'Deseja confirmar o pagamento deste saque agora?');
    if (result.isConfirmed) {
        payForm.post(route('admin.withdrawals.authorize', id), {
            preserveScroll: true
        });
    }
};
</script>

<template>
    <Head title="Solicitações de Saque" />

    <AdminSidebarLayout title="Gestão de Saques" subtitle="Analise e autorize solicitações pendentes.">
        <div class="bg-gray-50 rounded-xl border border-gray-100 overflow-hidden shadow-sm">
            <div v-if="withdrawals.length === 0" class="py-16 text-center">
                <div class="inline-flex w-12 h-12 bg-white rounded-lg shadow-sm items-center justify-center text-gray-300 mb-4">
                     <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Nenhum pedido pendente.</p>
            </div>

            <div v-else class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-white border-b border-gray-100">
                            <th class="px-6 py-4 text-[9px] font-black uppercase tracking-widest text-gray-400">Fotógrafo</th>
                            <th class="px-6 py-4 text-[9px] font-black uppercase tracking-widest text-gray-400">PIX / Dados</th>
                            <th class="px-6 py-4 text-[9px] font-black uppercase tracking-widest text-gray-400">Total</th>
                            <th class="px-6 py-4 text-[9px] font-black uppercase tracking-widest text-gray-400">Líquido</th>
                            <th class="px-6 py-4 text-[9px] font-black uppercase tracking-widest text-gray-400 text-right">Ação</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 font-medium">
                        <tr v-for="withdrawal in withdrawals" :key="withdrawal.id" class="hover:bg-white transition-colors">
                            <td class="px-6 py-4">
                                <p class="font-bold text-xs text-brand-dark uppercase tracking-tight">{{ withdrawal.user.name }}</p>
                                <p class="text-[9px] text-gray-400">{{ withdrawal.user.email }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <div class="inline-flex flex-col">
                                    <span class="text-[10px] font-black text-brand-blue uppercase">{{ withdrawal.pix_key || withdrawal.user.pix_key }}</span>
                                    <div class="flex items-center gap-2">
                                        <span class="text-[8px] bg-gray-200 px-1.5 py-0.5 rounded text-gray-500 font-black uppercase">{{ withdrawal.pix_key_type || withdrawal.user.pix_key_type }}</span>
                                        <span class="text-[8px] text-gray-300 uppercase tracking-tighter">{{ withdrawal.user.document }}</span>
                                    </div>
                                    <span v-if="withdrawal.efi_e2e_id" class="text-[7px] text-green-400 font-mono mt-1">E2E ID: {{ withdrawal.efi_e2e_id }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-[10px] text-gray-400 tracking-tighter">R$ {{ Number(withdrawal.request_amount).toFixed(2) }}</td>
                            <td class="px-6 py-4 text-xs font-black text-brand-dark">R$ {{ Number(withdrawal.net_amount).toFixed(2) }}</td>
                            <td class="px-6 py-4 text-right">
                                <template v-if="withdrawal.status === 'pending'">
                                    <button @click="authorizePayment(withdrawal.id)" 
                                            class="px-5 py-2.5 bg-brand-orange hover:bg-brand-orange-hover text-white font-black text-[9px] uppercase tracking-widest rounded-lg shadow-sm transition-all active:scale-95">
                                        Autorizar PIX
                                    </button>
                                </template>
                                <template v-else-if="withdrawal.status === 'processing'">
                                    <div class="flex items-center justify-end gap-3">
                                        <button @click="markAsPaid(withdrawal.id)" 
                                                class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-500 font-black text-[8px] uppercase tracking-widest rounded-lg transition-all active:scale-95 border border-gray-200">
                                            Aprovar Manual
                                        </button>
                                        <div class="flex items-center gap-1">
                                            <span class="inline-flex items-center gap-2 px-4 py-2 bg-blue-50 text-blue-600 rounded-lg text-[9px] font-black uppercase tracking-widest border border-blue-100 animate-pulse">
                                                Processando...
                                            </span>
                                            <button @click="checkStatus(withdrawal.id)" :disabled="checkForm.processing" class="p-2 text-blue-400 hover:text-blue-600 transition-colors disabled:opacity-50">
                                                <svg class="w-4 h-4" :class="checkForm.processing ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                                <template v-else-if="withdrawal.status === 'paid'">
                                    <span class="inline-flex items-center gap-2 px-4 py-2 bg-green-50 text-green-600 rounded-lg text-[9px] font-black uppercase tracking-widest border border-green-100">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                        Pago
                                    </span>
                                </template>
                                <template v-else-if="withdrawal.status === 'rejected'">
                                    <span class="inline-flex items-center gap-2 px-4 py-2 bg-red-50 text-red-600 rounded-lg text-[9px] font-black uppercase tracking-widest border border-red-100">
                                        Recusado
                                    </span>
                                </template>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminSidebarLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;900&display=swap');

.font-sans {
    font-family: 'Inter', sans-serif;
}
</style>
