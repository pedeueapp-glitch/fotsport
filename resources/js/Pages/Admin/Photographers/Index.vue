<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';
import { alert, confirm } from '@/utils/swal';

const props = defineProps({
    photographers: Array
});

const toggleStatus = async (photographer) => {
    const action = photographer.is_active ? 'Bloquear' : 'Ativar';
    const result = await confirm(`${action} Conta`, `Tem certeza que deseja ${action.toLowerCase()} o acesso deste fotógrafo?`);
    
    if (result.isConfirmed) {
        router.patch(route('admin.photographers.update', photographer.id), {
            name: photographer.name,
            email: photographer.email,
            is_active: !photographer.is_active,
            is_superadmin: photographer.is_superadmin
        }, {
            onSuccess: () => alert('Sucesso', 'Status atualizado.', 'success')
        });
    }
};
</script>

<template>
    <Head title="Gerenciar Fotógrafos" />

    <div class="min-h-screen bg-white flex flex-col font-sans text-brand-dark">
        <Navbar />

        <main class="flex-grow max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 w-full">
            <header class="mb-20">
                <div class="inline-flex items-center gap-3 mb-4">
                    <div class="h-px w-8 bg-brand-orange"></div>
                    <span class="text-[10px] font-black text-brand-orange uppercase tracking-[.4em]">Administração</span>
                </div>
                <h1 class="text-4xl md:text-6xl font-black text-brand-dark uppercase tracking-tighter mb-4">
                    Gerenciar <span class="text-brand-blue">Fotógrafos</span>
                </h1>
                <p class="text-gray-400 font-medium italic">Controle total sobre as contas e desempenho dos profissionais.</p>
            </header>

            <div class="bg-gray-50 rounded-[2.5rem] border border-gray-100 overflow-hidden shadow-sm">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-white border-b border-gray-100">
                            <th class="px-8 py-6 text-[10px] font-black uppercase tracking-widest text-gray-400">Fotógrafo</th>
                            <th class="px-8 py-6 text-[10px] font-black uppercase tracking-widest text-gray-400">Status</th>
                            <th class="px-8 py-6 text-[10px] font-black uppercase tracking-widest text-gray-400">Último Acesso</th>
                            <th class="px-8 py-6 text-[10px] font-black uppercase tracking-widest text-gray-400 text-center">Eventos</th>
                            <th class="px-8 py-6 text-[10px] font-black uppercase tracking-widest text-gray-400 text-center">Fotos Vendidas</th>
                            <th class="px-8 py-6 text-[10px] font-black uppercase tracking-widest text-gray-400 text-right">Total Bruto</th>
                            <th class="px-8 py-6 text-[10px] font-black uppercase tracking-widest text-gray-400 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="user in photographers" :key="user.id" class="hover:bg-white transition-colors group">
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-2xl bg-brand-dark flex items-center justify-center text-white font-black overflow-hidden shadow-lg">
                                        <img v-if="user.avatar" 
                                             :src="user.avatar.startsWith('http') ? user.avatar : (user.avatar.startsWith('storage/') ? '/' + user.avatar : '/storage/' + user.avatar)" 
                                             class="w-full h-full object-cover" />
                                        <span v-else>{{ user.name.charAt(0) }}</span>
                                    </div>
                                    <div>
                                        <p class="font-black text-brand-dark uppercase tracking-tight">{{ user.name }}</p>
                                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">{{ user.email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <span :class="user.is_active ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600'" 
                                      class="px-4 py-1.5 rounded-full text-[9px] font-black uppercase tracking-widest">
                                    {{ user.is_active ? 'Ativo' : 'Bloqueado' }}
                                </span>
                                <p v-if="user.is_superadmin" class="text-[8px] font-black text-brand-orange uppercase mt-1 italic">SuperAdmin</p>
                            </td>
                            <td class="px-8 py-6">
                                <div v-if="user.last_login_ip">
                                    <p class="font-black text-brand-dark uppercase tracking-tight">{{ user.last_login_city || 'Cidade Desconhecida' }}</p>
                                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">{{ user.last_login_ip }}</p>
                                </div>
                                <div v-else>
                                    <p class="text-[10px] text-gray-300 font-bold uppercase tracking-widest italic">Nenhum acesso registrado</p>
                                </div>
                            </td>
                            <td class="px-8 py-6 text-center font-bold text-gray-600">{{ user.events_count }}</td>
                            <td class="px-8 py-6 text-center font-bold text-brand-blue">{{ user.total_sales_count }}</td>
                            <td class="px-8 py-6 text-right font-black text-brand-dark">R$ {{ Number(user.total_sales_amount).toFixed(2) }}</td>
                            <td class="px-8 py-6 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <Link :href="route('admin.photographers.edit', user.id)" 
                                          class="w-10 h-10 bg-white border border-gray-100 rounded-xl flex items-center justify-center text-gray-400 hover:text-brand-blue hover:border-brand-blue transition-all">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </Link>
                                    <button @click="toggleStatus(user)" 
                                            :class="user.is_active ? 'text-red-400 hover:bg-red-50 hover:text-red-600' : 'text-green-400 hover:bg-green-50 hover:text-green-600'"
                                            class="w-10 h-10 bg-white border border-gray-100 rounded-xl flex items-center justify-center transition-all">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path v-if="user.is_active" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636" />
                                            <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </main>

        <Footer />
    </div>
</template>
