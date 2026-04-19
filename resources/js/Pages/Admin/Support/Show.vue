<script setup>
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import AdminSidebarLayout from '@/Layouts/AdminSidebarLayout.vue';
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
    form.post(route('admin.support.message', props.ticket.id), {
        onSuccess: () => {
            form.reset();
            scrollToBottom();
        },
        preserveScroll: true
    });
};

const toggleStatus = () => {
    router.patch(route('admin.support.toggle', props.ticket.id), {}, {
        preserveScroll: true
    });
};
</script>

<template>
    <Head :title="'Suporte ADM: ' + ticket.subject" />

    <AdminSidebarLayout :title="'Chamado: ' + ticket.subject">
        <div class="flex flex-col lg:flex-row gap-8 min-h-[600px]">
            <!-- Detalhes do Chamado -->
            <div class="lg:w-1/3 space-y-4">
                <div class="bg-gray-50 p-6 rounded-2xl border border-gray-100">
                    <h3 class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-4">Informações</h3>
                    
                    <div class="space-y-6">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-brand-dark rounded-xl flex items-center justify-center text-white font-black text-xs shadow">
                                {{ ticket.user.name.charAt(0) }}
                            </div>
                            <div>
                                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Fotógrafo</p>
                                <p class="text-xs font-black text-brand-dark uppercase truncate max-w-[150px]">{{ ticket.user.name }}</p>
                            </div>
                        </div>

                        <div>
                            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-2">Status Atual</p>
                            <button @click="toggleStatus" 
                                    :class="ticket.status === 'open' ? 'bg-green-100 text-green-600 border-green-200' : 'bg-red-100 text-red-600 border-red-200'"
                                    class="w-full px-4 py-3 rounded-xl text-[9px] font-black uppercase tracking-widest border transition-all active:scale-95 text-center">
                                {{ ticket.status === 'open' ? 'Aberto (Fechar)' : 'Encerrado (Reabrir)' }}
                            </button>
                        </div>

                        <div>
                            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Abertura</p>
                            <p class="text-[10px] font-bold text-brand-dark uppercase">{{ new Date(ticket.created_at).toLocaleString('pt-BR') }}</p>
                        </div>
                    </div>

                    <Link :href="route('admin.support.index')" class="mt-8 w-full py-3 bg-white border border-gray-200 rounded-xl text-[9px] font-black uppercase tracking-widest text-gray-400 hover:text-brand-dark hover:border-brand-dark transition-all block text-center">
                        Voltar para Lista
                    </Link>
                </div>
            </div>

            <!-- Área de Chat -->
            <div class="lg:w-2/3 flex flex-col bg-white border border-gray-100 rounded-2xl overflow-hidden shadow-sm">
                <div ref="chatContainer" class="flex-grow p-6 overflow-y-auto max-h-[500px] space-y-6 bg-gray-50/30">
                    <div v-for="msg in ticket.messages" :key="msg.id" 
                         :class="!msg.is_admin_reply ? 'justify-start' : 'justify-end'" class="flex items-start gap-3">
                        
                        <div v-if="!msg.is_admin_reply" class="w-8 h-8 rounded-lg bg-gray-200 text-gray-500 flex-shrink-0 flex items-center justify-center font-black text-[10px]">
                             {{ ticket.user.name.charAt(0) }}
                        </div>
                        
                        <div :class="!msg.is_admin_reply ? 'bg-white border border-gray-100 text-brand-dark rounded-tr-2xl rounded-br-2xl rounded-bl-2xl shadow-sm' : 'bg-brand-orange text-white rounded-tl-2xl rounded-bl-2xl rounded-br-2xl shadow-lg shadow-brand-orange/10'" 
                             class="px-5 py-4 max-w-[85%]">
                            <p class="text-xs font-bold leading-relaxed">{{ msg.message }}</p>
                            <span class="mt-2 block text-[7px] font-black uppercase tracking-widest opacity-40">{{ new Date(msg.created_at).toLocaleTimeString('pt-BR', {hour: '2-digit', minute:'2-digit'}) }}</span>
                        </div>

                        <div v-if="msg.is_admin_reply" class="w-8 h-8 rounded-lg bg-brand-orange text-white flex-shrink-0 flex items-center justify-center font-black text-[10px] shadow-sm shadow-brand-orange/20">
                            A
                        </div>
                    </div>
                </div>

                <!-- Input -->
                <div class="p-4 bg-white border-t border-gray-100">
                    <form @submit.prevent="submit" class="flex gap-3">
                        <input v-model="form.message" 
                               class="flex-grow bg-gray-50 border-gray-100 rounded-xl px-4 py-3 text-xs font-bold placeholder:text-gray-300 focus:ring-2 focus:ring-brand-orange/10 focus:border-brand-orange transition-all"
                               placeholder="Digite sua resposta oficial..." />
                        <button :disabled="form.processing || !form.message" 
                                class="px-6 bg-brand-orange text-white font-black uppercase tracking-widest text-[9px] rounded-xl shadow-lg shadow-brand-orange/10 active:scale-95 disabled:opacity-30 transition-all shrink-0">
                            Enviar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </AdminSidebarLayout>
</template>


