<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminSidebarLayout from '@/Layouts/AdminSidebarLayout.vue';
import { alert, confirm } from '@/utils/swal';

const props = defineProps({
    photographers: Array
});

const toggleVerified = async (photographer) => {
    const action = photographer.is_verified ? 'Remover Verificado' : 'Marcar como Verificado';
    const result = await confirm(`${action}`, `Deseja ${action.toLowerCase()} este fotógrafo?`);
    
    if (result.isConfirmed) {
        router.patch(route('admin.photographers.verify', photographer.id), {}, {
            onSuccess: () => alert('Sucesso', 'Status de verificação atualizado.', 'success')
        });
    }
};

const toggleStatus = async (photographer) => {
    const action = photographer.is_active ? 'Bloquear' : 'Ativar';
    const result = await confirm(`${action} Conta`, `Tem certeza que deseja ${action.toLowerCase()} o acesso deste fotógrafo?`);
    
    if (result.isConfirmed) {
        router.patch(route('admin.photographers.update', photographer.id), {
            name: photographer.name,
            email: photographer.email,
            is_active: !photographer.is_active,
            is_verified: photographer.is_verified,
            is_superadmin: photographer.is_superadmin
        }, {
            onSuccess: () => alert('Sucesso', 'Status atualizado.', 'success')
        });
    }
};
</script>

<template>
    <Head title="Gestão de Fotógrafos" />

    <AdminSidebarLayout title="Fotógrafos">
        <div class="bg-gray-50 rounded-xl border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-white border-b border-gray-100">
                            <th class="px-6 py-4 text-[9px] font-black uppercase tracking-widest text-gray-400">Fotógrafo</th>
                            <th class="px-6 py-4 text-[9px] font-black uppercase tracking-widest text-gray-400">Status</th>
                            <th class="px-6 py-4 text-[9px] font-black uppercase tracking-widest text-gray-400">Cidade/IP</th>
                            <th class="px-6 py-4 text-[9px] font-black uppercase tracking-widest text-gray-400 text-center">Vendas</th>
                            <th class="px-6 py-4 text-[9px] font-black uppercase tracking-widest text-gray-400 text-right">Total</th>
                            <th class="px-6 py-4 text-[9px] font-black uppercase tracking-widest text-gray-400 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="user in photographers" :key="user.id" class="hover:bg-white transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-brand-dark flex items-center justify-center text-white font-black overflow-hidden shadow">
                                        <img v-if="user.avatar" 
                                             :src="user.avatar.startsWith('http') ? user.avatar : (user.avatar.startsWith('storage/') ? '/' + user.avatar : '/storage/' + user.avatar)" 
                                             class="w-full h-full object-cover" />
                                        <span v-else class="text-xs">{{ user.name.charAt(0) }}</span>
                                    </div>
                                    <div>
                                        <p class="font-bold text-brand-dark text-xs uppercase tracking-tight flex items-center gap-1.5">
                                            {{ user.name }}
                                            <svg v-if="user.is_verified" class="w-3.5 h-3.5 text-blue-500 fill-current" viewBox="0 0 24 24">
                                                <path fill-rule="evenodd" d="M8.603 3.799A4.49 4.49 0 0 1 12 2.25c1.357 0 2.573.6 3.397 1.549a4.49 4.49 0 0 1 3.498 1.307 4.491 4.491 0 0 1 1.307 3.497A4.49 4.49 0 0 1 21.75 12a4.49 4.49 0 0 1-1.549 3.397 4.491 4.491 0 0 1-1.307 3.497 4.491 4.491 0 0 1-3.497 1.307A4.49 4.49 0 0 1 12 21.75a4.49 4.49 0 0 1-3.397-1.549 4.49 4.49 0 0 1-3.498-1.306 4.491 4.491 0 0 1-1.307-3.498A4.49 4.49 0 0 1 2.25 12c0-1.357.6-2.573 1.549-3.397a4.49 4.49 0 0 1 1.307-3.497 4.49 4.49 0 0 1 3.497-1.307Zm7.007 6.387a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule="evenodd" />
                                            </svg>
                                        </p>
                                        <p class="text-[9px] text-gray-400 font-bold uppercase tracking-widest">{{ user.email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col gap-1">
                                    <span :class="user.is_active ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600'" 
                                          class="px-3 py-1 rounded text-[8px] font-black uppercase tracking-widest border border-current border-opacity-10 w-fit">
                                        {{ user.is_active ? 'Ativo' : 'Bloqueado' }}
                                    </span>
                                    <span v-if="user.is_verified" class="bg-blue-50 text-blue-600 px-3 py-1 rounded text-[8px] font-black uppercase tracking-widest border border-current border-opacity-10 w-fit">
                                        Verificado
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div v-if="user.last_login_ip">
                                    <p class="font-bold text-xs text-brand-dark uppercase tracking-tight">{{ user.last_login_city || '-' }}</p>
                                    <p class="text-[9px] text-gray-400">{{ user.last_login_ip }}</p>
                                </div>
                                <span v-else class="text-[9px] text-gray-300 uppercase">Never</span>
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-xs text-brand-blue">{{ user.total_sales_count }}</td>
                            <td class="px-6 py-4 text-right font-black text-xs">R$ {{ Number(user.total_sales_amount).toFixed(2) }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2 text-xs">
                                    <button @click="toggleVerified(user)"
                                            :class="user.is_verified ? 'text-blue-500 bg-blue-50' : 'text-gray-300 bg-white'"
                                            class="w-8 h-8 border border-gray-100 rounded-lg flex items-center justify-center transition-all hover:bg-blue-500 hover:text-white"
                                            title="Alternar Verificado">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </button>
                                    <Link :href="route('admin.photographers.edit', user.id)" 
                                          class="w-8 h-8 bg-white border border-gray-100 rounded-lg flex items-center justify-center text-gray-400 hover:text-brand-blue transition-all">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </Link>
                                    <button @click="toggleStatus(user)" 
                                            :class="user.is_active ? 'text-red-400' : 'text-green-400'"
                                            class="w-8 h-8 bg-white border border-gray-100 rounded-lg flex items-center justify-center transition-all">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
        </div>
    </AdminSidebarLayout>
</template>

