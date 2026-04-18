<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';
import { alert } from '@/utils/swal';

const props = defineProps({
    event: Object
});

const form = useForm({
    name: props.event.name,
    location: props.event.location,
    date: props.event.date,
});

const submit = () => {
    form.patch(route('admin.events.update', props.event.slug), {
        onSuccess: () => alert('Sucesso', 'Evento atualizado com sucesso!', 'success')
    });
};
</script>

<template>
    <Head :title="`Editar Evento: ${event.name}`" />

    <div class="min-h-screen bg-white flex flex-col font-sans text-brand-dark">
        <Navbar />

        <main class="flex-grow max-w-2xl mx-auto px-4 py-20 w-full">
            <header class="mb-12">
                <h1 class="text-3xl font-black text-brand-dark uppercase tracking-tighter">Editar <span class="text-brand-blue">Evento</span></h1>
                <p class="text-gray-400 font-medium italic">Alteração global de informações do evento.</p>
            </header>

            <form @submit.prevent="submit" class="space-y-8 bg-gray-50 p-10 rounded-[2.5rem] border border-gray-100 shadow-sm">
                <div class="space-y-4">
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest">Nome do Evento</label>
                    <input v-model="form.name" type="text" class="w-full h-14 bg-white border-none rounded-2xl px-6 font-bold shadow-sm focus:ring-2 focus:ring-brand-blue" required />
                </div>

                <div class="space-y-4">
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest">Local</label>
                    <input v-model="form.location" type="text" class="w-full h-14 bg-white border-none rounded-2xl px-6 font-bold shadow-sm focus:ring-2 focus:ring-brand-blue" required />
                </div>

                <div class="space-y-4">
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest">Data do Evento</label>
                    <input v-model="form.date" type="date" class="w-full h-14 bg-white border-none rounded-2xl px-6 font-bold shadow-sm focus:ring-2 focus:ring-brand-blue" required />
                </div>

                <button :disabled="form.processing" class="w-full py-5 bg-brand-blue text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-xl shadow-brand-blue/20 active:scale-95 transition-all">
                    Atualizar Evento
                </button>
            </form>
        </main>

        <Footer />
    </div>
</template>
