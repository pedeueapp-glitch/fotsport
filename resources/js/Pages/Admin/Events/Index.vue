<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminSidebarLayout from '@/Layouts/AdminSidebarLayout.vue';
import { alert, confirm } from '@/utils/swal';

const props = defineProps({
    events: Array
});

const deleteEvent = async (event) => {
    const result = await confirm(
        '⚠️ Excluir Evento',
        `Excluir "${event.name}" e TODAS as suas fotos do servidor? Esta ação é IRREVERSÍVEL.`
    );
    if (result.isConfirmed) {
        router.delete(route('admin.events.destroy', event.slug), {
            onSuccess: () => alert('Sucesso', 'Evento e arquivos excluídos.', 'success')
        });
    }
};
</script>

<template>
    <Head title="Gerenciar Eventos" />

    <AdminSidebarLayout title="Gestão de Eventos" subtitle="Controle global de todos os eventos da plataforma.">
        <div class="space-y-4">
            <div v-for="event in events" :key="event.id"
                 class="bg-gray-50 rounded-xl border border-gray-100 p-4 flex flex-col md:flex-row items-center justify-between gap-4 hover:bg-white hover:shadow-md transition-all duration-300">

                <div class="flex items-center gap-4 flex-1 min-w-0">
                    <div class="w-16 h-16 bg-brand-dark rounded-xl flex flex-col items-center justify-center text-white shrink-0 shadow">
                        <span class="text-lg font-black leading-none">{{ event.photos_count }}</span>
                        <span class="text-[7px] font-black uppercase tracking-widest mt-1 opacity-60">Fotos</span>
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 mb-0.5">
                            <span class="text-[8px] font-black text-brand-orange uppercase tracking-widest">{{ event.date }}</span>
                            <div class="w-1 h-1 bg-gray-200 rounded-full"></div>
                            <span class="text-[8px] font-black text-gray-400 uppercase tracking-widest">{{ event.user?.name || 'Sistema' }}</span>
                        </div>
                        <h3 class="text-sm font-black text-brand-dark uppercase tracking-tighter line-clamp-1 italic">{{ event.name }}</h3>
                        <p class="text-[9px] text-gray-400 font-bold uppercase tracking-widest italic">{{ event.location }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-2 flex-shrink-0">
                    <!-- Ver fotos do evento -->
                    <Link :href="route('admin.events.photos', event.slug)"
                          class="px-4 py-2 bg-brand-dark text-white border border-brand-dark rounded-lg text-[8px] font-black uppercase tracking-widest hover:bg-brand-blue hover:border-brand-blue transition-all">
                        Fotos
                    </Link>
                    <a :href="route('store.event', event)" target="_blank"
                       class="px-4 py-2 border border-gray-100 rounded-lg text-[8px] font-black uppercase tracking-widest text-gray-400 hover:text-brand-blue transition-all bg-white">
                        Loja
                    </a>
                    <Link :href="route('admin.events.edit', event.slug)"
                          class="px-4 py-2 bg-white border border-gray-100 rounded-lg text-[8px] font-black uppercase tracking-widest text-brand-dark hover:border-brand-dark transition-all">
                        Editar
                    </Link>
                    <button @click="deleteEvent(event)"
                            class="w-10 h-10 bg-white border border-red-50 text-red-200 hover:bg-red-500 hover:text-white rounded-lg flex items-center justify-center transition-all shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                    </button>
                </div>
            </div>

            <div v-if="events.length === 0" class="py-16 text-center bg-gray-50 rounded-xl border border-dashed border-gray-200">
                <p class="text-gray-300 font-black uppercase tracking-widest text-xs">Nenhum evento cadastrado ainda.</p>
            </div>
        </div>
    </AdminSidebarLayout>
</template>
