<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AdminSidebarLayout from '@/Layouts/AdminSidebarLayout.vue';
import { alert } from '@/utils/swal';

const props = defineProps({
    event: Object
});

const form = useForm({
    name: props.event.name,
    category: props.event.category || '',
    location: props.event.location,
    date: props.event.date,
});

const categories = [
    'Corrida de Rua', 'Treinos', 'Futebol', 'Ciclismo', 'Duathlon / Triathlon', 
    'Carnaval', 'Show / Festival', 'Acampamentos', 'Beach Tênis', 'Crossfit', 
    'Arte Marcial', 'Esportes a Motor', 'Turismo', 'Formatura', 'Escolar', 
    'Evento Social', 'Ensaio', 'Futevôlei', 'Natação', 'Esportes em Geral', 'Igreja'
];

const submit = () => {
    form.patch(route('admin.events.update', props.event.slug), {
        onSuccess: () => alert('Sucesso', 'Evento atualizado.', 'success')
    });
};
</script>

<template>
    <Head :title="`Editar: ${event.name}`" />

    <AdminSidebarLayout title="Editar Evento" :subtitle="'Ajuste global de informações.'">
        <div class="max-w-xl">
            <form @submit.prevent="submit" class="space-y-6 bg-gray-50 p-8 rounded-xl border border-gray-100 shadow-sm italic">
                <div class="space-y-2">
                    <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest italic ml-1">Nome do Evento</label>
                    <input v-model="form.name" type="text" class="w-full h-12 bg-white border border-gray-200 rounded-xl px-5 font-bold text-xs focus:ring-2 focus:ring-brand-blue/10 focus:border-brand-blue" required />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest italic ml-1">Categoria</label>
                        <select v-model="form.category" class="w-full h-12 bg-white border border-gray-200 rounded-xl px-5 font-bold text-xs focus:ring-2 focus:ring-brand-blue/10 focus:border-brand-blue" required>
                            <option value="" disabled>Selecione uma categoria</option>
                            <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest italic ml-1">Data do Evento</label>
                        <input v-model="form.date" type="date" class="w-full h-12 bg-white border border-gray-200 rounded-xl px-5 font-bold text-xs focus:ring-2 focus:ring-brand-blue/10 focus:border-brand-blue" required />
                    </div>
                </div>

                <div class="pt-4">
                    <button :disabled="form.processing" class="w-full py-4 bg-brand-blue text-white rounded-xl font-black text-[10px] uppercase tracking-widest shadow-lg shadow-brand-blue/10 active:scale-95 transition-all">
                        {{ form.processing ? 'Salvando...' : 'Salvar Alterações' }}
                    </button>
                </div>
            </form>
        </div>
    </AdminSidebarLayout>
</template>


