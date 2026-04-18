<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';

const props = defineProps({
    stats: Object
});
</script>

<template>
    <Head title="Faturamento e Relatórios" />

    <div class="min-h-screen bg-white flex flex-col font-sans text-brand-dark">
        <Navbar />

        <main class="flex-grow max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 w-full">
            <header class="mb-20">
                <div class="inline-flex items-center gap-3 mb-4">
                    <div class="h-px w-8 bg-brand-orange"></div>
                    <span class="text-[10px] font-black text-brand-orange uppercase tracking-[.4em]">Administração Financeira</span>
                </div>
                <h1 class="text-4xl md:text-6xl font-black text-brand-dark uppercase tracking-tighter mb-4">
                    Visão Geral de <span class="text-brand-blue">Faturamento</span>
                </h1>
                <p class="text-gray-400 font-medium italic">Relatórios consolidados de vendas, saques e comissões da plataforma.</p>
            </header>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-20">
                <div class="bg-gray-50 rounded-[2.5rem] p-10 border border-gray-100 flex flex-col justify-between h-64">
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Vendas Totais</span>
                    <div>
                        <p class="text-[10px] font-black text-brand-blue uppercase tracking-widest mb-2">Volume Bruto</p>
                        <p class="text-4xl font-black text-brand-dark tracking-tighter">R$ {{ Number(stats.total_sales).toFixed(2) }}</p>
                    </div>
                </div>
                <div class="bg-gray-50 rounded-[2.5rem] p-10 border border-gray-100 flex flex-col justify-between h-64">
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Saques Pagos</span>
                    <div>
                        <p class="text-[10px] font-black text-red-400 uppercase tracking-widest mb-2">Prensas Retiradas</p>
                        <p class="text-4xl font-black text-brand-dark tracking-tighter">R$ {{ Number(stats.total_withdrawals).toFixed(2) }}</p>
                    </div>
                </div>
                <div class="bg-brand-dark rounded-[2.5rem] p-10 flex flex-col justify-between h-64 shadow-2xl shadow-brand-dark/20 text-white border-4 border-brand-orange/20">
                    <span class="text-[10px] font-black text-brand-orange uppercase tracking-widest">Receita Plataforma</span>
                    <div>
                        <p class="text-[10px] font-black text-white/50 uppercase tracking-widest mb-2">Comissões Acumuladas</p>
                        <p class="text-4xl font-black text-brand-orange tracking-tighter">R$ {{ Number(stats.revenue).toFixed(2) }}</p>
                    </div>
                </div>
            </div>

            <!-- Recent Transactions Table -->
            <div class="bg-white rounded-[2.5rem] border border-gray-100 overflow-hidden shadow-sm">
                <div class="px-10 py-8 border-b border-gray-50 flex items-center justify-between">
                    <h3 class="text-xl font-black text-brand-dark uppercase tracking-tighter">Vendas Recentes</h3>
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest italic">Últimos 10 registros</span>
                </div>
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/50">
                            <th class="px-10 py-6 text-[9px] font-black uppercase tracking-widest text-gray-400">Cliente</th>
                            <th class="px-10 py-6 text-[9px] font-black uppercase tracking-widest text-gray-400">Evento / Foto</th>
                            <th class="px-10 py-6 text-[9px] font-black uppercase tracking-widest text-gray-400">Data</th>
                            <th class="px-10 py-6 text-[9px] font-black uppercase tracking-widest text-gray-400 text-right">Valor</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-for="purchase in stats.recent_purchases" :key="purchase.id" class="hover:bg-gray-50/30 transition-colors group">
                            <td class="px-10 py-6">
                                <p class="font-black text-xs text-brand-dark uppercase tracking-tight">{{ purchase.customer?.name }}</p>
                                <p class="text-[9px] text-gray-400 font-bold uppercase tracking-widest">{{ purchase.customer?.cpf }}</p>
                            </td>
                            <td class="px-10 py-6">
                                <p class="font-black text-xs text-brand-blue uppercase tracking-tight">{{ purchase.photo?.event?.name }}</p>
                                <p class="text-[9px] text-gray-400 font-bold uppercase tracking-widest">ID Foto: #{{ purchase.photo_id }}</p>
                            </td>
                            <td class="px-10 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">{{ new Date(purchase.created_at).toLocaleDateString() }}</td>
                            <td class="px-10 py-6 text-right font-black text-brand-dark">R$ {{ Number(purchase.amount).toFixed(2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </main>

        <Footer />
    </div>
</template>
