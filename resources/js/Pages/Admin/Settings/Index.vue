<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AdminSidebarLayout from '@/Layouts/AdminSidebarLayout.vue';
import { alert } from '@/utils/swal';

const props = defineProps({
    settings: Object
});

const form = useForm({
    withdrawal_fee: props.settings.withdrawal_fee
});

const submit = () => {
    form.patch(route('admin.settings.update'), {
        onSuccess: () => alert('Sucesso', 'Configurações atualizadas.', 'success')
    });
};
</script>

<template>
    <Head title="Ajustes do Sistema" />

    <AdminSidebarLayout title="Ajustes do Sistema" :subtitle="'Gerencie taxas e comportamento global.'">
        <div class="max-w-xl">
            <form @submit.prevent="submit" class="space-y-6 bg-gray-50 p-8 rounded-xl border border-gray-100 shadow-sm">
                <!-- Taxa de Saque -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest">Taxa de Saque (%)</label>
                        <span class="text-[9px] font-black text-brand-orange uppercase tracking-widest">Atual: {{ settings.withdrawal_fee }}%</span>
                    </div>
                    
                    <div class="relative">
                        <input v-model="form.withdrawal_fee" type="number" step="0.1" min="0" max="100"
                               class="w-full h-14 bg-white border border-gray-200 rounded-xl px-5 pr-12 font-black text-xl focus:ring-2 focus:ring-brand-blue/10 focus:border-brand-blue transition-all" />
                        <span class="absolute right-5 top-1/2 -translate-y-1/2 text-lg font-black text-gray-200">%</span>
                    </div>
                    
                    <p class="text-[9px] text-gray-400 font-medium leading-relaxed">
                        * Descontada automaticamente no saque. Ex: Saque de R$ 100 com 15% de taxa, o fotógrafo recebe R$ 85.
                    </p>
                </div>

                <div class="pt-4 border-t border-gray-200">
                    <button :disabled="form.processing" 
                            class="w-full py-4 bg-brand-dark text-white rounded-xl font-black text-[10px] uppercase tracking-widest shadow-lg shadow-brand-dark/10 active:scale-95 transition-all disabled:opacity-50">
                        {{ form.processing ? 'Gravando...' : 'Salvar Alterações' }}
                    </button>
                </div>
            </form>

            <div class="mt-8 p-6 bg-brand-blue/5 rounded-xl border border-brand-blue/10 flex items-start gap-3">
                <div class="w-8 h-8 bg-brand-blue rounded-lg flex items-center justify-center text-white shrink-0 shadow">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <h4 class="text-[9px] font-black text-brand-dark uppercase tracking-widest mb-1">Transparência</h4>
                    <p class="text-gray-500 text-[10px] leading-5 font-medium">Fotógrafos verão a taxa em seus painéis antes de solicitar o saque.</p>
                </div>
            </div>
        </div>
    </AdminSidebarLayout>
</template>


