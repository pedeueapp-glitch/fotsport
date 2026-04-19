<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminSidebarLayout from '@/Layouts/AdminSidebarLayout.vue';
import { alert, confirm } from '@/utils/swal';

const props = defineProps({
    customers: Array
});

const toggleStatus = async (customer) => {
    const action = customer.is_active ? 'Bloquear' : 'Ativar';
    const result = await confirm(`${action} Cliente`, `Tem certeza que deseja ${action.toLowerCase()} o acesso deste cliente?`);
    
    if (result.isConfirmed) {
        router.patch(route('admin.customers.update', customer.id), {
            name: customer.name,
            cpf: customer.cpf,
            phone: customer.phone,
            is_active: !customer.is_active
        }, {
            onSuccess: () => alert('Sucesso', 'Status atualizado.', 'success')
        });
    }
};
</script>

<template>
    <Head title="Gestão de Clientes" />

    <AdminSidebarLayout title="Clientes" subtitle="Gestão completa da base de compradores.">
        <div class="bg-gray-50 rounded-xl border border-gray-100 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-white border-b border-gray-100">
                            <th class="px-6 py-4 text-[9px] font-black uppercase tracking-widest text-gray-400">Cliente</th>
                            <th class="px-6 py-4 text-[9px] font-black uppercase tracking-widest text-gray-400">CPF / Contato</th>
                            <th class="px-6 py-4 text-[9px] font-black uppercase tracking-widest text-gray-400">Status</th>
                            <th class="px-6 py-4 text-[9px] font-black uppercase tracking-widest text-gray-400 text-center">Compras</th>
                            <th class="px-6 py-4 text-[9px] font-black uppercase tracking-widest text-gray-400 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="customer in customers" :key="customer.id" class="hover:bg-white transition-colors">
                            <td class="px-6 py-4">
                                <div>
                                    <p class="font-bold text-brand-dark text-xs uppercase tracking-tight line-clamp-1">{{ customer.name }}</p>
                                    <p class="text-[9px] text-gray-400 font-medium">Cadastrado em: {{ new Date(customer.created_at).toLocaleDateString('pt-BR') }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-black text-[10px] text-brand-blue uppercase tracking-widest">{{ customer.cpf }}</p>
                                <p class="text-[9px] text-gray-400 font-bold uppercase tracking-widest mt-0.5">{{ customer.phone }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span :class="customer.is_active ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600'" 
                                      class="px-3 py-1 rounded text-[8px] font-black uppercase tracking-widest border border-current border-opacity-10">
                                    {{ customer.is_active ? 'Ativo' : 'Bloqueado' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <Link :href="route('admin.customers.purchases', customer.id)" class="inline-flex items-center gap-2 group">
                                    <span class="font-bold text-xs text-brand-dark group-hover:text-brand-orange transition-colors">{{ customer.purchases_count }}</span>
                                    <svg class="w-3.5 h-3.5 text-gray-300 group-hover:text-brand-orange transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 11l-7 7-7-7"/></svg>
                                </Link>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2 text-xs">
                                    <Link :href="route('admin.customers.purchases', customer.id)" 
                                          title="Ver Compras"
                                          class="w-8 h-8 bg-white border border-gray-100 rounded-lg flex items-center justify-center text-gray-400 hover:text-brand-orange transition-all shadow-sm">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                    </Link>
                                    <Link :href="route('admin.customers.edit', customer.id)" 
                                          title="Editar"
                                          class="w-8 h-8 bg-white border border-gray-100 rounded-lg flex items-center justify-center text-gray-400 hover:text-brand-blue transition-all shadow-sm">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </Link>
                                    <button @click="toggleStatus(customer)" 
                                            :title="customer.is_active ? 'Bloquear' : 'Ativar'"
                                            :class="customer.is_active ? 'text-red-400 hover:bg-red-50' : 'text-green-400 hover:bg-green-50'"
                                            class="w-8 h-8 bg-white border border-gray-100 rounded-lg flex items-center justify-center transition-all shadow-sm">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path v-if="customer.is_active" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636" />
                                            <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-if="!customers.length" class="py-24 text-center">
                <p class="text-[9px] font-black text-gray-300 uppercase tracking-[0.4em]">Nenhum cliente cadastrado ainda.</p>
            </div>
        </div>
    </AdminSidebarLayout>
</template>
