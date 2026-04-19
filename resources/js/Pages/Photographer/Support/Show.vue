<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import PhotographerLayout from '@/Layouts/PhotographerLayout.vue';
import { ref, onMounted, nextTick } from 'vue';

const props = defineProps({
    ticket: Object
});

const form = useForm({
    message: ''
});

const chatContainer = ref(null);

const scrollToBottom = async () => {
    await nextTick();
    if (chatContainer.value) {
        chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
    }
};

onMounted(scrollToBottom);

const submit = () => {
    form.post(route('photographer.support.message', props.ticket.id), {
        onSuccess: () => {
            form.reset();
            scrollToBottom();
        },
        preserveScroll: true
    });
};
</script>

<template>
    <Head :title="'Chamado: ' + ticket.subject" />

    <PhotographerLayout :title="'Chamado: ' + ticket.subject" subtitle="Histórico de mensagens e suporte.">
        <template #actions>
            <Link :href="route('photographer.support.index')" class="px-5 py-2.5 bg-white border border-gray-200 text-gray-400 hover:text-brand-dark font-black text-[9px] uppercase tracking-widest rounded-lg transition-all active:scale-95 shadow-sm italic">
                ← Voltar
            </Link>
        </template>
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Informações -->
            <div class="lg:w-1/3 xl:w-1/4">
                <div class="bg-gray-50 p-6 rounded-2xl border border-gray-100">
                    <h3 class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-6">Informações</h3>
                    
                    <div class="space-y-6">
                        <div>
                             <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Status</p>
                             <span :class="ticket.status === 'open' ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-400'"
                                  class="px-4 py-1.5 rounded-lg text-[8px] font-black uppercase tracking-widest inline-block border border-current border-opacity-10">
                                {{ ticket.status === 'open' ? 'Aberto' : 'Encerrado' }}
                            </span>
                        </div>

                        <div>
                            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Assunto</p>
                            <p class="text-[11px] font-black text-brand-dark uppercase leading-tight">{{ ticket.subject }}</p>
                        </div>

                        <div>
                            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Aberto em</p>
                            <p class="text-[10px] font-bold text-gray-500 uppercase">{{ new Date(ticket.created_at).toLocaleString('pt-BR') }}</p>
                        </div>
                    </div>

                    <Link :href="route('photographer.support.index')" class="mt-8 w-full py-3 bg-white border border-gray-200 rounded-xl text-[9px] font-black uppercase tracking-widest text-gray-400 hover:text-brand-dark text-center block transition-all italic">
                        Lista de Chamados
                    </Link>
                </div>
            </div>

            <!-- Chat -->
            <div class="lg:flex-grow flex flex-col bg-white border border-gray-100 rounded-2xl overflow-hidden shadow-sm">
                <div ref="chatContainer" class="flex-grow p-6 overflow-y-auto max-h-[500px] space-y-6 bg-gray-50/30 italic">
                    <div v-for="msg in ticket.messages" :key="msg.id" 
                         :class="msg.is_admin_reply ? 'justify-start' : 'justify-end'" class="flex items-start gap-3">
                        
                        <div v-if="msg.is_admin_reply" class="w-8 h-8 rounded-lg bg-brand-orange text-white flex-shrink-0 flex items-center justify-center font-black text-[10px] shadow-sm">
                            A
                        </div>
                        
                        <div :class="msg.is_admin_reply ? 'bg-white border border-gray-100 text-brand-dark rounded-tr-2xl rounded-br-2xl rounded-bl-2xl shadow-sm' : 'bg-brand-blue text-white rounded-tl-2xl rounded-bl-2xl rounded-br-2xl shadow-lg shadow-brand-blue/10'" 
                             class="px-5 py-4 max-w-[85%]">
                            <p class="text-xs font-bold leading-relaxed">{{ msg.message }}</p>
                            <span class="mt-2 block text-[7px] font-black uppercase tracking-widest opacity-40">{{ new Date(msg.created_at).toLocaleTimeString('pt-BR', {hour: '2-digit', minute:'2-digit'}) }}</span>
                        </div>

                        <div v-if="!msg.is_admin_reply" class="w-8 h-8 rounded-lg bg-brand-dark text-white flex-shrink-0 flex items-center justify-center font-black text-[10px]">
                             {{ msg.user.name.charAt(0) }}
                        </div>
                    </div>
                </div>

                <!-- Input -->
                <div v-if="ticket.status === 'open'" class="p-4 bg-white border-t border-gray-100">
                    <form @submit.prevent="submit" class="flex gap-3">
                        <textarea v-model="form.message" 
                               class="flex-grow bg-gray-50 border-gray-100 rounded-xl px-4 py-3 text-xs font-bold placeholder:text-gray-300 focus:ring-2 focus:ring-brand-blue/10 focus:border-brand-blue transition-all resize-none h-16 italic"
                               placeholder="Digite sua mensagem..."></textarea>
                        <button :disabled="form.processing || !form.message" 
                                 class="px-6 bg-brand-blue text-white font-black uppercase tracking-widest text-[9px] rounded-xl shadow-lg shadow-brand-blue/10 active:scale-95 disabled:opacity-30 transition-all italic">
                            Enviar
                        </button>
                    </form>
                </div>
                <div v-else class="p-4 bg-gray-50 text-center text-[9px] font-black uppercase tracking-widest text-gray-400 italic">
                    Chamado encerrado por suporte
                </div>
            </div>
        </div>
    </PhotographerLayout>
</template>
