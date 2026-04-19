<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminSidebarLayout from '@/Layouts/AdminSidebarLayout.vue';

const props = defineProps({
    customer: Object,
    purchases: Array
});

const getStatusLabel = (status) => {
    const map = {
        'approved': 'Aprovado',
        'pending': 'Pendente',
        'cancelled': 'Cancelado',
        'rejected': 'Rejeitado'
    };
    return map[status] || status;
};

const getStatusClass = (status) => {
    const map = {
        'approved': 'bg-green-50 text-green-600',
        'pending': 'bg-yellow-50 text-yellow-600',
        'cancelled': 'bg-red-50 text-red-600',
        'rejected': 'bg-red-50 text-red-600'
    };
    return map[status] || 'bg-gray-50 text-gray-600';
};
</script>

<template>
    <Head :title="`Compras: ${customer.name}`" />

    <AdminSidebarLayout :title="`Extrato do Cliente`" :subtitle="customer.name">
        <template #actions>
            <Link :href="route('admin.customers.index')" class="text-[10px] font-black text-gray-400 uppercase tracking-widest hover:text-brand-blue flex items-center gap-2 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Voltar
            </Link>
        </template>

        <div class="space-y-8">
            <!-- Header Info -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-gray-50 p-8 rounded-[2rem] border border-gray-100">
                <div>
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Total Gasto</p>
                    <p class="text-3xl font-black text-brand-dark tracking-tighter">R$ {{ purchases.filter(p => p.status === 'approved').reduce((acc, p) => acc + Number(p.amount), 0).toFixed(2) }}</p>
                </div>
                <div>
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Fotos Adquiridas</p>
                    <p class="text-3xl font-black text-brand-dark tracking-tighter">{{ purchases.filter(p => p.status === 'approved').length }}</p>
                </div>
                <div>
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Última Atividade</p>
                    <p class="text-xs font-black text-brand-dark uppercase tracking-tight mt-1">{{ purchases.length > 0 ? new Date(purchases[0].created_at).toLocaleDateString('pt-BR') : 'Sem registros' }}</p>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white border border-gray-100 rounded-xl overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-100">
                                <th class="px-6 py-4 text-[9px] font-black uppercase tracking-widest text-gray-400">Foto / Evento</th>
                                <th class="px-6 py-4 text-[9px] font-black uppercase tracking-widest text-gray-400">Fotógrafo</th>
                                <th class="px-6 py-4 text-[9px] font-black uppercase tracking-widest text-gray-400">Data Compra</th>
                                <th class="px-6 py-4 text-[9px] font-black uppercase tracking-widest text-gray-400 text-center">Status</th>
                                <th class="px-6 py-4 text-[9px] font-black uppercase tracking-widest text-gray-400 text-right">Valor</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="purchase in purchases" :key="purchase.id" class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <div class="w-16 h-12 bg-gray-100 rounded-lg overflow-hidden border border-gray-100 shadow-sm flex-shrink-0">
                                            <img v-if="purchase.photo" :src="'/' + (purchase.status === 'approved' ? purchase.photo.original_path : purchase.photo.watermarked_path)" class="w-full h-full object-cover">
                                        </div>
                                        <div>
                                            <p class="font-bold text-xs text-brand-dark uppercase tracking-tight line-clamp-1">{{ purchase.photo?.event?.name || 'Evento Excluído' }}</p>
                                            <p class="text-[9px] text-gray-400 font-bold uppercase tracking-widest">ID: #{{ purchase.photo_id }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-bold text-xs text-brand-blue uppercase tracking-tight">{{ purchase.photo?.user?.name || 'Sistema' }}</p>
                                </td>
                                <td class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase">
                                    {{ new Date(purchase.created_at).toLocaleDateString('pt-BR', { day: '2-digit', month: 'short', year: 'numeric' }) }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span :class="getStatusClass(purchase.status)" 
                                          class="px-3 py-1 rounded text-[8px] font-black uppercase tracking-widest border border-current border-opacity-10">
                                        {{ getStatusLabel(purchase.status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right font-black text-xs text-brand-dark">
                                    R$ {{ Number(purchase.amount).toFixed(2) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-if="!purchases.length" class="py-24 text-center">
                    <p class="text-[9px] font-black text-gray-300 uppercase tracking-[0.4em]">Este cliente ainda não realizou compras.</p>
                </div>
            </div>
        </div>
    </AdminSidebarLayout>
</template>
