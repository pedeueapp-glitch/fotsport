<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({
    events: Array
});

const deleteEvent = (event) => {
    if (confirm('Tem certeza que deseja excluir este evento? Todas as fotos NÃO COMPRADAS serão apagadas permanentemente do servidor.')) {
        router.delete(route('events.destroy', event.slug));
    }
};
</script>

<template>
    <Head title="Eventos" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Meus Eventos</h2>
                <Link
                    :href="route('events.create')"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded"
                >
                    Novo Evento
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div v-if="events.length === 0" class="text-gray-500 text-center py-8">
                        Nenhum evento criado ainda.
                    </div>
                    
                    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div v-for="event in events" :key="event.id" class="border rounded-lg overflow-hidden shadow-sm">
                            <div class="p-6">
                                <h3 class="text-lg font-bold mb-2">{{ event.name }}</h3>
                                <p class="text-sm text-gray-600 mb-4">{{ event.date }} - {{ event.location }}</p>
                                <p class="text-gray-700 text-sm mb-4 truncate">{{ event.description }}</p>
                                
                                <div class="flex justify-between items-center">
                                    <Link
                                        :href="route('events.show', event)"
                                        class="text-indigo-600 hover:text-indigo-900 font-medium"
                                    >
                                        Gerenciar Fotos &rarr;
                                    </Link>

                                    <button 
                                        v-if="$page.props.auth.user.is_superadmin"
                                        @click="deleteEvent(event)"
                                        class="text-red-600 hover:text-red-800 text-sm font-medium"
                                    >
                                        Excluir Evento
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
