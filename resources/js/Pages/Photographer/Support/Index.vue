<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';
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

    <div class="min-h-screen bg-white flex flex-col font-sans text-brand-dark">
        <Navbar />

        <main class="flex-grow max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 w-full">
            <header class="mb-20 flex justify-between items-end">
                <div>
                    <div class="inline-flex items-center gap-3 mb-4">
                        <div class="h-px w-8 bg-brand-orange"></div>
                        <span class="text-[10px] font-black text-brand-orange uppercase tracking-[.4em]">Central de Ajuda</span>
                    </div>
                    <h1 class="text-4xl md:text-6xl font-black text-brand-dark uppercase tracking-tighter mb-4">
                        Meus <span class="text-brand-blue">Chamados</span>
                    </h1>
                    <p class="text-gray-400 font-medium italic">Fale diretamente com a equipe do Fotsport sobre suas dúvidas ou problemas.</p>
                </div>
                <button @click="showNewTicketModal = true" class="px-8 py-4 bg-brand-dark text-white font-black uppercase tracking-widest text-[10px] rounded-2xl hover:bg-brand-blue transition-all active:scale-95 shadow-xl">
                    Abrir Chamado
                </button>
            </header>

            <div v-if="tickets.length === 0" class="py-32 text-center bg-gray-50 rounded-[3rem] border border-gray-100 italic text-gray-400">
                Você não possui nenhum chamado aberto no momento.
            </div>

            <div v-else class="grid grid-cols-1 gap-6">
                <Link v-for="ticket in tickets" :key="ticket.id" :href="route('photographer.support.show', ticket.id)" 
                      class="group bg-white border border-gray-100 p-8 rounded-[2.5rem] hover:border-brand-blue hover:shadow-2xl transition-all flex justify-between items-center">
                    <div>
                        <div class="flex items-center gap-4 mb-2">
                             <span :class="ticket.status === 'open' ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-400'"
                                  class="px-4 py-1 rounded-full text-[8px] font-black uppercase tracking-widest">
                                {{ ticket.status === 'open' ? 'Aberto' : 'Encerrado' }}
                            </span>
                            <span class="text-[10px] text-gray-300 font-bold uppercase tracking-widest">#{{ ticket.id }} • {{ new Date(ticket.created_at).toLocaleDateString('pt-BR') }}</span>
                        </div>
                        <h3 class="text-xl font-black text-brand-dark uppercase group-hover:text-brand-blue transition-colors">{{ ticket.subject }}</h3>
                    </div>
                    <div class="flex items-center gap-6">
                        <div v-if="ticket.messages_count > 0" class="text-right">
                             <span class="block text-[10px] font-black text-brand-orange uppercase tracking-widest mb-1">{{ ticket.messages_count }} Respostas Admin</span>
                             <div class="flex -space-x-2">
                                 <div class="w-8 h-8 rounded-full border-2 border-white bg-brand-orange text-white flex items-center justify-center text-[10px] font-black">?</div>
                             </div>
                        </div>
                        <svg class="w-6 h-6 text-gray-200 group-hover:translate-x-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </div>
                </Link>
            </div>
        </main>

        <!-- Modal Novo Chamado -->
        <Transition enter-active-class="duration-300 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="duration-200 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="showNewTicketModal" class="fixed inset-0 z-50 flex items-center justify-center p-6 bg-brand-dark/20 backdrop-blur-sm">
                <div class="bg-white w-full max-w-xl rounded-[3rem] p-12 shadow-2xl relative">
                    <button @click="showNewTicketModal = false" class="absolute top-8 right-8 text-gray-300 hover:text-brand-dark transition-colors">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>

                    <h3 class="text-3xl font-black text-brand-dark uppercase tracking-tighter mb-8">Novo <span class="text-brand-orange">Chamado</span></h3>

                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2 ml-1">Assunto</label>
                            <input v-model="form.subject" type="text" class="w-full px-8 py-5 bg-gray-50 border-0 rounded-2xl focus:ring-4 focus:ring-brand-blue/10 text-brand-dark font-bold placeholder:italic" placeholder="Título resumido do problema" required>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2 ml-1">Mensagem Detalhada</label>
                            <textarea v-model="form.message" rows="5" class="w-full px-8 py-5 bg-gray-50 border-0 rounded-2xl focus:ring-4 focus:ring-brand-blue/10 text-brand-dark font-bold placeholder:italic resize-none" placeholder="Explique seu caso com o máximo de detalhes possível..." required></textarea>
                        </div>
                        <button :disabled="form.processing" class="w-full py-5 bg-brand-blue text-white font-black uppercase tracking-[0.2em] text-[10px] rounded-2xl shadow-xl shadow-brand-blue/20 hover:scale-[1.02] active:scale-95 transition-all">
                            {{ form.processing ? 'Enviando...' : 'Abrir Chamado Agora' }}
                        </button>
                    </form>
                </div>
            </div>
        </Transition>

        <Footer />
    </div>
</template>
