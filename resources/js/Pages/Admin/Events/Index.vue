<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';
import { alert, confirm } from '@/utils/swal';

const props = defineProps({
    events: Array
});

const deleteEvent = async (event) => {
    const result = await confirm('Excluir Evento', `Tem certeza que deseja excluir o evento "${event.name}"? Todas as fotos serão perdidas!`);
    
    if (result.isConfirmed) {
        router.delete(route('admin.events.destroy', event.slug), {
            onSuccess: () => alert('Sucesso', 'Evento excluído.', 'success')
        });
    }
};
</script>

<template>
    <Head title="Gerenciar Eventos (Admin)" />

    <div class="min-h-screen bg-white flex flex-col font-sans text-brand-dark">
        <Navbar />

        <main class="flex-grow max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 w-full">
            <header class="mb-20">
                <div class="inline-flex items-center gap-3 mb-4">
                    <div class="h-px w-8 bg-brand-orange"></div>
                    <span class="text-[10px] font-black text-brand-orange uppercase tracking-[.4em]">Administração</span>
                </div>
                <h1 class="text-4xl md:text-6xl font-black text-brand-dark uppercase tracking-tighter mb-4">
                    Controle de <span class="text-brand-blue">Eventos</span>
                </h1>
                <p class="text-gray-400 font-medium italic">Gestão global de todos os eventos criados no Fotsport.</p>
            </header>

            <div class="grid grid-cols-1 gap-6">
                <div v-for="event in events" :key="event.id" 
                     class="bg-gray-50 rounded-[2rem] p-8 border border-gray-100 flex flex-col md:flex-row items-center justify-between gap-8 hover:bg-white hover:shadow-2xl transition-all duration-500 group">
                    
                    <div class="flex items-center gap-6 flex-1">
                        <div class="w-20 h-20 bg-brand-dark rounded-2xl flex items-center justify-center text-white shrink-0 shadow-lg">
                            <span class="text-2xl font-black">{{ event.photos_count }}</span>
                        </div>
                        <div>
                            <div class="flex items-center gap-3 mb-1">
                                <span class="text-[9px] font-black text-brand-orange uppercase tracking-widest">{{ event.date }}</span>
                                <div class="w-1 h-1 bg-gray-200 rounded-full"></div>
                                <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest">{{ event.user?.name || 'Sistema' }}</span>
                            </div>
                            <h3 class="text-xl font-black text-brand-dark uppercase tracking-tighter group-hover:text-brand-blue transition-colors">{{ event.name }}</h3>
                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">{{ event.location }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <a :href="route('store.event', event)" target="_blank"
                           class="px-6 py-3 border border-gray-200 rounded-xl text-[10px] font-black uppercase tracking-widest text-gray-400 hover:text-brand-blue transition-all">
                            Ver Loja
                        </a>
                        <Link :href="route('admin.events.edit', event.slug)"
                              class="px-6 py-3 bg-white border border-gray-200 rounded-xl text-[10px] font-black uppercase tracking-widest text-brand-dark hover:border-brand-dark transition-all">
                            Editar
                        </Link>
                        <button @click="deleteEvent(event)"
                                class="w-12 h-12 bg-red-50 text-red-300 hover:bg-red-500 hover:text-white rounded-xl flex items-center justify-center transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </button>
                    </div>
                </div>
            </div>
        </main>

        <Footer />
    </div>
</template>
