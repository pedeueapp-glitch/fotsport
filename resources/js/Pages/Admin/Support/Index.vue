<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminSidebarLayout from '@/Layouts/AdminSidebarLayout.vue';

const props = defineProps({
    tickets: Array
});


</script>

<template>
    <Head title="Suporte ADM" />

    <AdminSidebarLayout title="Suporte Administrativo" subtitle="Gerencie os chamados dos fotógrafos.">
        <div class="bg-gray-50 rounded-xl border border-gray-100 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-white border-b border-gray-100">
                            <th class="px-6 py-4 text-[9px] font-black uppercase tracking-widest text-gray-400">Status</th>
                            <th class="px-6 py-4 text-[9px] font-black uppercase tracking-widest text-gray-400">Fotógrafo / Assunto</th>
                            <th class="px-6 py-4 text-[9px] font-black uppercase tracking-widest text-gray-400">Última Atualização</th>
                            <th class="px-6 py-4 text-[9px] font-black uppercase tracking-widest text-gray-400 text-right">Ação</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 font-medium">
                        <tr v-for="ticket in tickets" :key="ticket.id" class="hover:bg-white transition-colors">
                            <td class="px-6 py-4">
                                <span :class="ticket.status === 'open' ? 'bg-green-50 text-green-600 border-green-100' : 'bg-gray-50 text-gray-400 border-gray-100'"
                                      class="px-3 py-1 rounded text-[8px] font-black uppercase tracking-widest border">
                                    {{ ticket.status === 'open' ? 'Aberto' : 'Encerrado' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2 mb-1">
                                    <div class="w-6 h-6 rounded bg-brand-dark text-white flex items-center justify-center font-black text-[8px]">{{ ticket.user.name.charAt(0) }}</div>
                                    <span class="text-[9px] font-black text-brand-dark uppercase tracking-tight">{{ ticket.user.name }}</span>
                                </div>
                                <h4 class="text-xs font-black text-brand-dark uppercase tracking-tighter line-clamp-1">{{ ticket.subject }}</h4>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-[9px] font-black text-gray-400 uppercase">{{ new Date(ticket.updated_at).toLocaleString('pt-BR', { day: '2-digit', month: '2-digit', hour: '2-digit', minute: '2-digit' }) }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <Link :href="route('admin.support.show', ticket.id)" 
                                      class="inline-flex px-5 py-2 bg-brand-dark text-white font-black uppercase tracking-widest text-[8px] rounded-lg hover:bg-brand-blue transition-all active:scale-95 shadow-sm">
                                    Responder
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-if="tickets.length === 0" class="py-16 text-center text-gray-300 font-black uppercase tracking-widest text-[9px]">
                Nenhum chamado pendente.
            </div>
        </div>
    </AdminSidebarLayout>
</template>

