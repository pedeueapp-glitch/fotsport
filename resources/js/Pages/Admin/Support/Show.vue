<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';
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
    <Head :title="'Suporte Admn: ' + ticket.subject" />

    <div class="min-h-screen bg-white flex flex-col font-sans text-brand-dark">
        <Navbar />

        <main class="flex-grow max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-20 w-full flex flex-col lg:flex-row gap-12">
            <!-- Sidebar Info -->
            <aside class="lg:w-1/3">
                <div class="bg-gray-50 p-10 rounded-[3rem] border border-gray-100 sticky top-20">
                    <header class="mb-10">
                        <span class="text-[9px] font-black text-brand-orange uppercase tracking-[0.4em] mb-4 block">Informações do Chamado</span>
                        <h1 class="text-3xl font-black text-brand-dark uppercase tracking-tighter mb-4">{{ ticket.subject }}</h1>
                    </header>

                    <div class="space-y-8">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-white rounded-2xl shadow-xl flex items-center justify-center text-brand-blue font-black">{{ ticket.user.name.charAt(0) }}</div>
                            <div>
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Fotógrafo</p>
                                <p class="font-black text-brand-dark uppercase">{{ ticket.user.name }}</p>
                            </div>
                        </div>

                        <div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Status</p>
                            <button @click="toggleStatus" 
                                    :class="ticket.status === 'open' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600'"
                                    class="px-6 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest hover:scale-105 transition-transform active:scale-95">
                                {{ ticket.status === 'open' ? 'Aberto (Clique p/ Fechar)' : 'Fechado (Clique p/ Abrir)' }}
                            </button>
                        </div>

                        <div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Data de Abertura</p>
                            <p class="font-bold text-gray-600">{{ new Date(ticket.created_at).toLocaleString('pt-BR') }}</p>
                        </div>
                    </div>

                    <div class="mt-12 pt-12 border-t border-gray-200">
                        <Link :href="route('admin.support.index')" class="w-full py-4 bg-white border border-gray-200 text-center block rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-gray-100 transition-all">Voltar para a Lista</Link>
                    </div>
                </div>
            </aside>

            <!-- Chat Admin -->
            <div class="lg:w-2/3 flex flex-col">
                <div ref="chatContainer" class="flex-grow bg-white border border-gray-100 rounded-[3.5rem] p-10 md:p-12 shadow-sm overflow-y-auto max-h-[700px] mb-8 space-y-10 scroll-smooth">
                    <div v-for="msg in ticket.messages" :key="msg.id" 
                         :class="!msg.is_admin_reply ? 'justify-start mr-12' : 'justify-end ml-12'" class="flex items-start gap-4">
                        
                        <div v-if="!msg.is_admin_reply" class="w-10 h-10 rounded-xl bg-gray-100 text-brand-dark flex-shrink-0 flex items-center justify-center font-black text-xs shadow-sm">
                             {{ ticket.user.name.charAt(0) }}
                        </div>
                        
                        <div :class="!msg.is_admin_reply ? 'bg-gray-100 text-brand-dark rounded-tr-3xl rounded-br-3xl rounded-bl-3xl' : 'bg-brand-orange text-white rounded-tl-3xl rounded-bl-3xl rounded-br-3xl shadow-orange-500/20 shadow-xl'" 
                             class="p-8">
                            <p class="text-base font-medium leading-relaxed">{{ msg.message }}</p>
                            <time class="mt-4 block text-[9px] font-black uppercase tracking-widest opacity-40">{{ new Date(msg.created_at).toLocaleString('pt-BR') }}</time>
                        </div>

                        <div v-if="msg.is_admin_reply" class="w-10 h-10 rounded-xl bg-brand-orange text-white flex-shrink-0 flex items-center justify-center font-black text-xs rotate-6 shadow-lg">?</div>
                    </div>
                </div>

                <!-- Admin Response Form -->
                <div class="bg-gray-50 p-6 rounded-[2.5rem] border border-gray-100">
                    <form @submit.prevent="submit" class="flex gap-4">
                        <textarea v-model="form.message" 
                               class="flex-grow bg-white border-0 rounded-2xl px-8 py-5 text-brand-dark font-bold placeholder:italic focus:ring-4 focus:ring-brand-orange/10 resize-none h-20"
                               placeholder="Digite sua resposta oficial..."></textarea>
                        <button :disabled="form.processing || !form.message" class="px-10 bg-brand-orange text-white font-black uppercase tracking-[0.2em] text-[10px] rounded-2xl shadow-xl shadow-brand-orange/20 active:scale-95 disabled:opacity-30 transition-all">
                            Enviar Resposta
                        </button>
                    </form>
                </div>
            </div>
        </main>

        <Footer />
    </div>
</template>
