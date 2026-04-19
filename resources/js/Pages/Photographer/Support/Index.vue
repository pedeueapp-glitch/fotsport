<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import PhotographerLayout from '@/Layouts/PhotographerLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    tickets: Array
});

const showNewTicketModal = ref(false);

const form = useForm({
    subject: '',
    message: ''
});

const submit = () => {
    form.post(route('photographer.support.store'), {
        onSuccess: () => {
            showNewTicketModal.value = false;
            form.reset();
        }
    });
};
</script>

<template>
    <Head title="Suporte ao Fotógrafo" />

    <PhotographerLayout title="Central de Suporte" subtitle="Estamos aqui para ajudar com suas dúvidas e suporte técnico.">
        <template #actions>
            <button @click="showNewTicketModal = true" class="px-5 py-2.5 bg-brand-dark text-white font-black uppercase tracking-widest text-[9px] rounded-lg hover:bg-brand-blue transition-all active:scale-95 shadow-sm italic">
                Abrir Chamado
            </button>
        </template>

        <div v-if="tickets.length === 0" class="py-16 text-center bg-gray-50 rounded-xl border border-gray-100 text-gray-400 text-[10px] uppercase tracking-widest italic">
            Nenhum chamado aberto.
        </div>

        <div v-else class="grid grid-cols-1 gap-4">
            <Link v-for="ticket in tickets" :key="ticket.id" :href="route('photographer.support.show', ticket.id)" 
                  class="group bg-white border border-gray-100 p-6 rounded-xl hover:border-brand-blue hover:shadow-lg transition-all flex justify-between items-center">
                <div>
                    <div class="flex items-center gap-3 mb-1">
                         <span :class="ticket.status === 'open' ? 'bg-green-50 text-green-600 border-green-100' : 'bg-gray-50 text-gray-400 border-gray-100'"
                              class="px-2.5 py-0.5 rounded text-[7px] font-black uppercase tracking-widest border">
                            {{ ticket.status === 'open' ? 'Aberto' : 'Encerrado' }}
                        </span>
                        <span class="text-[9px] text-gray-300 font-bold uppercase tracking-widest">#{{ ticket.id }} • {{ new Date(ticket.created_at).toLocaleDateString('pt-BR') }}</span>
                    </div>
                    <h3 class="text-xs font-black text-brand-dark uppercase group-hover:text-brand-blue transition-colors line-clamp-1 italic">{{ ticket.subject }}</h3>
                </div>
                <div class="flex items-center gap-4">
                    <div v-if="ticket.messages_count > 0" class="text-right">
                         <span class="block text-[8px] font-black text-brand-orange uppercase tracking-widest">{{ ticket.messages_count }} Respostas</span>
                    </div>
                    <svg class="w-4 h-4 text-gray-200 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </div>
            </Link>
        </div>

        <!-- Modal Novo Chamado -->
        <Transition enter-active-class="duration-300 ease-out" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="duration-200 ease-in" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <div v-if="showNewTicketModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-brand-dark/40 backdrop-blur-sm">
                <div class="bg-white w-full max-w-lg rounded-2xl p-8 shadow-2xl relative border border-gray-100">
                    <button @click="showNewTicketModal = false" class="absolute top-6 right-6 text-gray-300 hover:text-brand-dark transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>

                    <h3 class="text-xl font-black text-brand-dark uppercase tracking-tighter mb-6">Novo <span class="text-brand-orange">Chamado</span></h3>

                    <form @submit.prevent="submit" class="space-y-5">
                        <div>
                            <label class="block text-[8px] font-black text-gray-400 uppercase tracking-[0.2em] mb-1.5 ml-1">Assunto</label>
                            <input v-model="form.subject" type="text" class="w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-xl focus:ring-2 focus:ring-brand-blue/10 focus:border-brand-blue text-xs font-bold" placeholder="Assunto do chamado" required>
                        </div>
                        <div>
                            <label class="block text-[8px] font-black text-gray-400 uppercase tracking-[0.2em] mb-1.5 ml-1">Mensagem</label>
                            <textarea v-model="form.message" rows="4" class="w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-xl focus:ring-2 focus:ring-brand-blue/10 focus:border-brand-blue text-xs font-bold resize-none" placeholder="Descreva sua solicitação..." required></textarea>
                        </div>
                        <button :disabled="form.processing" class="w-full py-4 bg-brand-blue text-white font-black uppercase tracking-widest text-[9px] rounded-xl shadow-lg shadow-brand-blue/10 hover:bg-brand-blue-hover active:scale-95 transition-all">
                            {{ form.processing ? 'Enviando...' : 'Abrir Chamado Agora' }}
                        </button>
                    </form>
                </div>
            </div>
        </Transition>
    </PhotographerLayout>
</template>
