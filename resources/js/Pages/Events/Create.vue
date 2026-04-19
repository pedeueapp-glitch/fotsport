<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import PhotographerLayout from '@/Layouts/PhotographerLayout.vue';
import { alert } from '@/utils/swal';

const form = useForm({
    name: '',
    description: '',
    category: '',
    date: '',
    location: '',
});

const categories = [
    'Corrida de Rua', 'Treinos', 'Futebol', 'Ciclismo', 'Duathlon / Triathlon', 
    'Carnaval', 'Show / Festival', 'Acampamentos', 'Beach Tênis', 'Crossfit', 
    'Arte Marcial', 'Esportes a Motor', 'Turismo', 'Formatura', 'Escolar', 
    'Evento Social', 'Ensaio', 'Futevôlei', 'Natação', 'Esportes em Geral', 'Igreja'
];

const submit = () => {
    form.post(route('events.store'), {
        onSuccess: () => alert('Sucesso', 'Evento criado com sucesso!', 'success')
    });
};
</script>

<template>
    <Head title="Novo Evento" />

    <PhotographerLayout title="Novo Evento" subtitle="Cadastre as informações básicas do seu próximo evento.">
        <template #actions>
            <Link :href="route('dashboard')" class="text-[9px] font-black text-gray-400 uppercase tracking-widest hover:text-brand-dark transition-colors">
                ← Voltar para Meus Eventos
            </Link>
        </template>

        <div class="max-w-xl">
            <form @submit.prevent="submit" class="space-y-6 bg-gray-50 p-8 rounded-xl border border-gray-100 shadow-sm">
                <div class="space-y-2">
                    <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest ml-1">Nome do Evento</label>
                    <input v-model="form.name" type="text" placeholder="Ex: Corrida de Rua 2024" class="w-full h-12 bg-white border border-gray-200 rounded-xl px-5 font-bold text-xs focus:ring-2 focus:ring-brand-blue/10 focus:border-brand-blue transition-all" required />
                    <p v-if="form.errors.name" class="mt-1 text-[9px] text-red-500 font-bold uppercase tracking-widest">{{ form.errors.name }}</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest ml-1">Categoria</label>
                        <select v-model="form.category" class="w-full h-12 bg-white border border-gray-200 rounded-xl px-5 font-bold text-xs focus:ring-2 focus:ring-brand-blue/10 focus:border-brand-blue transition-all" required>
                            <option value="" disabled>Selecione uma categoria</option>
                            <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                        </select>
                        <p v-if="form.errors.category" class="mt-1 text-[9px] text-red-500 font-bold uppercase tracking-widest">{{ form.errors.category }}</p>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest ml-1">Data</label>
                        <input v-model="form.date" type="date" class="w-full h-12 bg-white border border-gray-200 rounded-xl px-5 font-bold text-xs focus:ring-2 focus:ring-brand-blue/10 focus:border-brand-blue transition-all" required />
                        <p v-if="form.errors.date" class="mt-1 text-[9px] text-red-500 font-bold uppercase tracking-widest">{{ form.errors.date }}</p>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest ml-1">Localização</label>
                    <input v-model="form.location" type="text" placeholder="Ex: Parque Ibirapuera, São Paulo" class="w-full h-12 bg-white border border-gray-200 rounded-xl px-5 font-bold text-xs focus:ring-2 focus:ring-brand-blue/10 focus:border-brand-blue transition-all" required />
                    <p v-if="form.errors.location" class="mt-1 text-[9px] text-red-500 font-bold uppercase tracking-widest">{{ form.errors.location }}</p>
                </div>

                <div class="space-y-2">
                    <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest ml-1">Descrição (Opcional)</label>
                    <textarea v-model="form.description" rows="3" placeholder="Detalhes adicionais sobre o evento..." class="w-full bg-white border border-gray-200 rounded-xl px-5 py-4 font-bold text-xs focus:ring-2 focus:ring-brand-blue/10 focus:border-brand-blue transition-all resize-none"></textarea>
                </div>

                <div class="pt-4">
                    <button :disabled="form.processing" class="w-full py-4 bg-brand-dark text-white rounded-xl font-black text-[10px] uppercase tracking-widest shadow-xl shadow-brand-dark/10 active:scale-95 transition-all disabled:opacity-50">
                        {{ form.processing ? 'Criando Evento...' : 'Cadastrar Evento' }}
                    </button>
                </div>
            </form>
        </div>
    </PhotographerLayout>
</template>
