<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import PhotographerLayout from '@/Layouts/PhotographerLayout.vue';

defineProps({
    events: Array
});

const { auth } = usePage().props;
</script>

<template>
    <Head title="Meus Eventos" />

    <PhotographerLayout title="Meus Eventos" subtitle="Gerencie seus eventos e suba novas fotos para venda.">
        <template #actions>
            <!-- Botão Ver Portfólio -->
            <a
                v-if="auth?.user?.slug"
                :href="`/fotografo/${auth.user.slug}`"
                target="_blank"
                class="inline-flex items-center gap-2 px-5 py-3 border border-gray-200 bg-white text-brand-dark font-black text-[10px] uppercase tracking-widest rounded-lg transition-all hover:border-brand-blue hover:text-brand-blue hover:shadow-md active:scale-95"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                Ver Portfólio
            </a>

            <!-- Botão Criar Evento -->
            <Link :href="route('events.create')" class="inline-flex items-center gap-2 px-6 py-3 bg-brand-dark text-white font-black text-[10px] uppercase tracking-widest rounded-lg transition-all hover:bg-brand-blue shadow-lg active:scale-95 text-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
                Criar Evento
            </Link>
        </template>

        <!-- Empty State -->
        <div v-if="events.length === 0" class="py-20 text-center bg-gray-50 rounded-2xl border border-dashed border-gray-200">
            <div class="w-16 h-16 bg-white rounded-xl flex items-center justify-center mx-auto mb-6 shadow-sm border border-gray-100">
                <svg class="w-8 h-8 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
            </div>
            <h3 class="text-xl font-black text-brand-dark uppercase tracking-tight mb-2">Nenhum evento disponível</h3>
            <p class="text-gray-400 mb-8 max-w-sm mx-auto text-[10px] font-bold uppercase tracking-widest">Crie o primeiro evento da plataforma ou aguarde novos eventos.</p>
            <Link :href="route('events.create')" class="px-8 py-3.5 bg-brand-blue text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-xl shadow-xl shadow-brand-blue/10 active:scale-95 transition-all">
                Criar Agora
            </Link>
        </div>

        <!-- Event Grid -->
        <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <Link v-for="event in events" :key="event.id" :href="route('events.show', event)" class="group bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-xl transition-all border border-gray-100 flex flex-col">
                <div class="h-48 w-full bg-brand-dark relative overflow-hidden">
                    <img v-if="event.photos && event.photos.length > 0" :src="'/' + event.photos[0].watermarked_path" class="w-full h-full object-cover opacity-80 group-hover:scale-105 transition duration-500" />
                    <div v-else class="w-full h-full flex items-center justify-center bg-gray-50 text-gray-200">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    </div>
                </div>

                <div class="p-6">
                    <div class="flex items-center justify-between mb-1">
                        <div class="flex items-center gap-2">
                            <span v-if="event.category" class="bg-brand-blue text-white text-[7px] font-black px-2 py-0.5 rounded uppercase tracking-widest">{{ event.category }}</span>
                            <p class="text-[9px] font-black text-brand-orange uppercase tracking-[0.2em]">{{ event.location || 'Local indefinido' }}</p>
                        </div>
                        <span class="text-[8px] font-black text-gray-300 uppercase tracking-widest">por {{ event.user?.name || '—' }}</span>
                    </div>
                    <h3 class="text-lg font-black text-brand-dark uppercase group-hover:text-brand-blue transition-colors line-clamp-1 tracking-tighter">{{ event.name }}</h3>

                    <div class="mt-4 pt-4 border-t border-gray-50 flex justify-between items-center text-[10px] font-black uppercase tracking-widest text-gray-400 group-hover:text-brand-dark transition-colors">
                        <span>{{ event.photos_count || 0 }} Fotos</span>
                        <span>Enviar Fotos →</span>
                    </div>
                </div>
            </Link>
        </div>
    </PhotographerLayout>
</template>
