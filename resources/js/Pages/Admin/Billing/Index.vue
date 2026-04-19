<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminSidebarLayout from '@/Layouts/AdminSidebarLayout.vue';

const props = defineProps({
    stats: Object
});

</script>

<template>
    <Head title="Faturamento Adm" />

    <AdminSidebarLayout title="Faturamento Global" :subtitle="'Relatórios consolidados de vendas e comissões.'">
        <!-- Stats Grid (Financial) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div class="bg-gray-50 rounded-xl p-6 border border-gray-100 flex flex-col justify-between h-32">
                <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Vendas Totais</span>
                <div>
                    <p class="text-[8px] font-black text-brand-blue uppercase tracking-widest">Volume Bruto</p>
                    <p class="text-xl font-black text-brand-dark tracking-tighter">R$ {{ Number(stats.total_sales).toFixed(2) }}</p>
                </div>
            </div>
            <div class="bg-gray-50 rounded-xl p-6 border border-gray-100 flex flex-col justify-between h-32">
                <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Saques Pagos</span>
                <div>
                    <p class="text-[8px] font-black text-red-400 uppercase tracking-widest">Pagos</p>
                    <p class="text-xl font-black text-brand-dark tracking-tighter">R$ {{ Number(stats.total_withdrawals).toFixed(2) }}</p>
                </div>
            </div>
            <div class="bg-brand-dark rounded-xl p-6 flex flex-col justify-between h-32 shadow-lg shadow-brand-dark/10 text-white border-2 border-brand-orange/20 relative overflow-hidden group">
                <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:scale-125 transition-transform duration-500">
                    <svg class="w-20 h-20" fill="currentColor" viewBox="0 0 20 20"><path d="M11 3a1 1 0 10-2 0v1a1 1 0 102 0V3zM15.657 5.757a1 1 0 00-1.414-1.414l-.707.707a1 1 0 001.414 1.414l.707-.707zM18 10a1 1 0 01-1 1h-1a1 1 0 110-2h1a1 1 0 011 1zM5.05 6.464A1 1 0 106.464 5.05l-.707-.707a1 1 0 00-1.414 1.414l.707.707zM5 10a1 1 0 01-1 1H3a1 1 0 110-2h1a1 1 0 011 1zM8 16v-1a1 1 0 00-2 0v1a1 1 0 102 0zm7-1a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1z"></path></svg>
                </div>
                <span class="text-[9px] font-black text-brand-orange uppercase tracking-widest relative z-10">Receita Plataforma</span>
                <div class="relative z-10">
                    <p class="text-[8px] font-black text-white/40 uppercase tracking-widest">Comissões</p>
                    <p class="text-xl font-black text-brand-orange tracking-tighter">R$ {{ Number(stats.revenue).toFixed(2) }}</p>
                </div>
            </div>
        </div>

        <!-- Operational Stats Grid -->
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
            <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
                <p class="text-[8px] font-black text-gray-400 uppercase tracking-widest mb-1">Clientes</p>
                <p class="text-xl font-black text-brand-dark tracking-tighter">{{ stats.customers_count }}</p>
                <div class="h-1 w-6 bg-brand-blue rounded-full mt-2"></div>
            </div>
            <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
                <p class="text-[8px] font-black text-gray-400 uppercase tracking-widest mb-1">Fotógrafos</p>
                <p class="text-xl font-black text-brand-dark tracking-tighter">{{ stats.photographers_count }}</p>
                <div class="h-1 w-6 bg-brand-orange rounded-full mt-2"></div>
            </div>
            <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
                <p class="text-[8px] font-black text-gray-400 uppercase tracking-widest mb-1">Acessos Est.</p>
                <p class="text-xl font-black text-brand-dark tracking-tighter">{{ stats.total_accesses }}</p>
                <div class="h-1 w-6 bg-green-400 rounded-full mt-2"></div>
            </div>
            <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
                <p class="text-[8px] font-black text-gray-400 uppercase tracking-widest mb-1">Fotos Enviadas</p>
                <p class="text-xl font-black text-brand-dark tracking-tighter">{{ stats.total_photos_count }}</p>
                <div class="h-1 w-6 bg-purple-400 rounded-full mt-2"></div>
            </div>
            <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
                <p class="text-[8px] font-black text-gray-400 uppercase tracking-widest mb-1">Fotos Compradas</p>
                <p class="text-xl font-black text-brand-dark tracking-tighter">{{ stats.total_purchases_count }}</p>
                <div class="h-1 w-6 bg-brand-orange rounded-full mt-2"></div>
            </div>
        </div>

        <!-- Recent Transactions Table -->
        <div class="bg-gray-50 rounded-xl border border-gray-100 overflow-hidden shadow-sm">
            <div class="px-6 py-4 bg-white border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-xs font-black text-brand-dark uppercase tracking-tighter">Vendas Recentes</h3>
                <span class="text-[8px] font-black text-gray-400 uppercase tracking-widest">Top 10</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-white/50 border-b border-gray-50">
                            <th class="px-6 py-4 text-[8px] font-black uppercase tracking-widest text-gray-400">Cliente</th>
                            <th class="px-6 py-4 text-[8px] font-black uppercase tracking-widest text-gray-400">Evento / Foto</th>
                            <th class="px-6 py-4 text-[8px] font-black uppercase tracking-widest text-gray-400">Data</th>
                            <th class="px-6 py-4 text-[8px] font-black uppercase tracking-widest text-gray-400 text-right">Valor</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 font-medium">
                        <tr v-for="purchase in stats.recent_purchases" :key="purchase.id" class="hover:bg-white transition-colors">
                            <td class="px-6 py-4">
                                <p class="font-black text-[10px] text-brand-dark uppercase tracking-tight">{{ purchase.customer?.name }}</p>
                                <p class="text-[8px] text-gray-400 font-bold uppercase tracking-widest">{{ purchase.customer?.cpf }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-black text-[10px] text-brand-blue uppercase tracking-tight">{{ purchase.photo?.event?.name }}</p>
                                <p class="text-[8px] text-gray-400 font-bold uppercase tracking-widest">#{{ purchase.photo_id }}</p>
                            </td>
                            <td class="px-6 py-4 text-[9px] font-black text-gray-400 uppercase">{{ new Date(purchase.created_at).toLocaleDateString('pt-BR') }}</td>
                            <td class="px-6 py-4 text-right font-black text-[10px] text-brand-dark">R$ {{ Number(purchase.amount).toFixed(2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-if="!stats.recent_purchases?.length" class="py-12 text-center text-gray-300 font-black uppercase tracking-widest text-[9px]">
                Nenhuma venda registrada.
            </div>
        </div>
    </AdminSidebarLayout>
</template>


