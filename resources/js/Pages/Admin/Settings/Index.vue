<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';
import { alert } from '@/utils/swal';

const props = defineProps({
    settings: Object
});

const form = useForm({
    withdrawal_fee: props.settings.withdrawal_fee
});

const submit = () => {
    form.patch(route('admin.settings.update'), {
        onSuccess: () => alert('Sucesso', 'Configurações atualizadas com sucesso!', 'success')
    });
};
</script>

<template>
    <Head title="Configurações do Sistema" />

    <div class="min-h-screen bg-white flex flex-col font-sans text-brand-dark">
        <Navbar />

        <main class="flex-grow max-w-2xl mx-auto px-4 py-20 w-full">
            <header class="mb-12">
                <div class="inline-flex items-center gap-3 mb-4">
                    <div class="h-px w-8 bg-brand-orange"></div>
                    <span class="text-[10px] font-black text-brand-orange uppercase tracking-[.4em]">Propriedades do Negócio</span>
                </div>
                <h1 class="text-3xl font-black text-brand-dark uppercase tracking-tighter mb-4">
                    Ajustes de <span class="text-brand-blue">Sistemas</span>
                </h1>
                <p class="text-gray-400 font-medium italic">Gerencie taxas globais e comportamentos da plataforma.</p>
            </header>

            <form @submit.prevent="submit" class="space-y-8 bg-gray-50 p-10 rounded-[2.5rem] border border-gray-100 shadow-sm">
                <!-- Taxa de Saque -->
                <div class="space-y-6">
                    <div class="flex items-center justify-between">
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest">Taxa de Saque (%)</label>
                        <span class="text-[10px] font-black text-brand-orange uppercase tracking-widest">Atual: {{ settings.withdrawal_fee }}%</span>
                    </div>
                    
                    <div class="relative">
                        <input v-model="form.withdrawal_fee" type="number" step="0.1" min="0" max="100"
                               class="w-full h-16 bg-white border-none rounded-2xl px-6 pr-16 font-black text-2xl shadow-sm focus:ring-4 focus:ring-brand-blue/10 transition-all" />
                        <span class="absolute right-6 top-1/2 -translate-y-1/2 text-xl font-black text-gray-300">%</span>
                    </div>
                    
                    <p class="text-[10px] text-gray-400 font-medium italic leading-relaxed">
                        * Esta taxa será descontada automaticamente do valor solicitado pelo fotógrafo no momento do saque. 
                        Exemplo: Se o fotógrafo solicitar R$ 100,00 e a taxa for 15%, ele receberá R$ 85,00.
                    </p>
                </div>

                <div class="pt-8 border-t border-gray-200">
                    <button :disabled="form.processing" 
                            class="w-full py-5 bg-brand-dark text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-xl shadow-brand-dark/10 active:scale-95 transition-all disabled:opacity-50">
                        {{ form.processing ? 'Atualizando...' : 'Salvar Configurações' }}
                    </button>
                </div>
            </form>

            <!-- Cards Informativos -->
            <div class="mt-12 grid grid-cols-1 gap-6">
                <div class="p-8 bg-brand-blue/5 rounded-[2rem] border border-brand-blue/10 flex items-start gap-4">
                    <div class="w-10 h-10 bg-brand-blue rounded-xl flex items-center justify-center text-white shrink-0 shadow-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <h4 class="text-xs font-black text-brand-dark uppercase tracking-widest mb-2">Transparência</h4>
                        <p class="text-gray-500 text-[11px] leading-6 font-medium">Os fotógrafos verão a taxa aplicada em tempo real em seus painéis antes de confirmarem a solicitação de saque.</p>
                    </div>
                </div>
            </div>
        </main>

        <Footer />
    </div>
</template>
