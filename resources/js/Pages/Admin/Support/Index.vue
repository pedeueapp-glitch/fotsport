<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';

const props = defineProps({
    tickets: Array
});
</script>

<template>
    <Head title="Central de Suporte - Adm" />

    <div class="min-h-screen bg-gray-50 flex flex-col font-sans text-brand-dark">
        <Navbar />

        <main class="flex-grow max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 w-full">
            <header class="mb-20">
                <div class="inline-flex items-center gap-3 mb-4">
                    <div class="h-px w-8 bg-brand-orange"></div>
                    <span class="text-[10px] font-black text-brand-orange uppercase tracking-[.4em]">Caixa de Correio</span>
                </div>
                <h1 class="text-4xl md:text-6xl font-black text-brand-dark uppercase tracking-tighter mb-4">
                    Central de <span class="text-brand-blue">Suporte</span>
                </h1>
                <p class="text-gray-400 font-medium italic">Responda aos chamados dos fotógrafos e mantenha a plataforma saudável.</p>
            </header>

            <div class="bg-white rounded-[3rem] shadow-sm border border-gray-100 overflow-hidden">
                <table class="w-full text-left">
                    <thead>
                        <tr class="text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100">
                            <th class="px-12 py-8">Status</th>
                            <th class="px-12 py-8">Fotógrafo / Assunto</th>
                            <th class="px-12 py-8">Última Mensagem</th>
                            <th class="px-12 py-8 text-right">Ação</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-for="ticket in tickets" :key="ticket.id" class="hover:bg-gray-50/50 transition-colors group">
                            <td class="px-12 py-8">
                                <span :class="ticket.status === 'open' ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-400'"
                                      class="px-4 py-1.5 rounded-full text-[9px] font-black uppercase tracking-widest">
                                    {{ ticket.status === 'open' ? 'Aberto' : 'Encerrado' }}
                                </span>
                            </td>
                            <td class="px-12 py-8">
                                <div class="flex items-center gap-4 mb-2">
                                    <div class="w-8 h-8 rounded-lg bg-brand-dark text-white flex items-center justify-center font-black text-[10px]">{{ ticket.user.name.charAt(0) }}</div>
                                    <span class="text-[11px] font-black text-brand-dark uppercase tracking-tight">{{ ticket.user.name }}</span>
                                </div>
                                <h4 class="text-lg font-black text-brand-dark uppercase tracking-tighter">{{ ticket.subject }}</h4>
                            </td>
                            <td class="px-12 py-8">
                                <p class="text-sm text-gray-400 font-medium line-clamp-1 italic">
                                    "{{ ticket.messages[0]?.message || 'Sem mensagens' }}"
                                </p>
                                <span class="text-[9px] font-black text-gray-300 uppercase mt-2 block">{{ new Date(ticket.updated_at).toLocaleString('pt-BR') }}</span>
                            </td>
                            <td class="px-12 py-8 text-right">
                                <Link :href="route('admin.support.show', ticket.id)" 
                                      class="inline-flex px-8 py-3 bg-brand-dark text-white font-black uppercase tracking-widest text-[9px] rounded-xl hover:bg-brand-blue transition-all active:scale-95 shadow-xl shadow-brand-dark/10">
                                    Abrir Conversa
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div v-if="tickets.length === 0" class="py-32 text-center text-gray-300 font-black uppercase tracking-widest text-xs">
                    Nenhum chamado recebido até o momento.
                </div>
            </div>
        </main>

        <Footer />
    </div>
</template>
