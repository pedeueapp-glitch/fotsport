<script setup>
import { Head, useForm } from '@inertiajs/vue3';
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

    <div class="min-h-screen bg-gray-50 flex flex-col font-sans text-brand-dark">
        <Navbar />

        <main class="flex-grow max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-20 w-full flex flex-col">
            <header class="mb-12 flex justify-between items-center bg-white p-8 rounded-[2rem] shadow-sm border border-gray-100">
                <div class="flex items-center gap-6">
                    <div class="w-14 h-14 bg-brand-blue/10 rounded-2xl flex items-center justify-center text-brand-blue">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                         <div class="flex items-center gap-2 mb-1">
                             <span :class="ticket.status === 'open' ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-400'"
                                  class="px-3 py-0.5 rounded-full text-[7px] font-black uppercase tracking-widest">
                                {{ ticket.status === 'open' ? 'Aberto' : 'Encerrado' }}
                            </span>
                            <span class="text-[9px] text-gray-400 font-bold uppercase tracking-widest">Ticket #{{ ticket.id }}</span>
                         </div>
                        <h1 class="text-2xl font-black text-brand-dark uppercase tracking-tight">{{ ticket.subject }}</h1>
                    </div>
                </div>
                <Link :href="route('photographer.support.index')" class="text-[10px] font-black uppercase tracking-widest text-gray-400 hover:text-brand-dark transition-colors">Voltar</Link>
            </header>

            <!-- Chat Area -->
            <div ref="chatContainer" class="flex-grow bg-white rounded-[3rem] p-8 md:p-12 shadow-xl border border-gray-100 overflow-y-auto max-h-[600px] mb-8 space-y-8 scroll-smooth">
                <div v-for="msg in ticket.messages" :key="msg.id" 
                     :class="msg.is_admin_reply ? 'justify-start mr-12' : 'justify-end ml-12'" class="flex items-start gap-4">
                    
                    <div v-if="msg.is_admin_reply" class="w-10 h-10 rounded-xl bg-brand-orange text-white flex-shrink-0 flex items-center justify-center font-black text-xs rotate-3 shadow-lg">?</div>
                    
                    <div :class="msg.is_admin_reply ? 'bg-gray-50 text-brand-dark rounded-tr-3xl rounded-br-3xl rounded-bl-3xl' : 'bg-brand-blue text-white rounded-tl-3xl rounded-bl-3xl rounded-br-3xl shadow-blue-500/20 shadow-xl'" 
                         class="p-6 md:p-8">
                        <p class="text-sm md:text-base font-medium leading-relaxed">{{ msg.message }}</p>
                        <time class="mt-4 block text-[9px] font-black uppercase tracking-widest opacity-40">{{ new Date(msg.created_at).toLocaleString('pt-BR') }}</time>
                    </div>

                    <div v-if="!msg.is_admin_reply" class="w-10 h-10 rounded-xl bg-brand-dark text-white flex-shrink-0 flex items-center justify-center font-black text-xs -rotate-3 shadow-lg">
                        {{ msg.user.name.charAt(0) }}
                    </div>
                </div>
            </div>

            <!-- Input Area -->
            <div class="bg-white p-6 rounded-[2.5rem] shadow-2xl border border-brand-blue/10">
                <form v-if="ticket.status === 'open'" @submit.prevent="submit" class="flex gap-4">
                    <input v-model="form.message" type="text" 
                           class="flex-grow bg-gray-50 border-0 rounded-2xl px-8 py-5 text-brand-dark font-bold placeholder:italic focus:ring-4 focus:ring-brand-blue/10"
                           placeholder="Digite sua resposta aqui...">
                    <button :disabled="form.processing || !form.message" class="px-8 py-5 bg-brand-blue text-white font-black uppercase tracking-widest text-[10px] rounded-2xl shadow-xl shadow-brand-blue/20 active:scale-95 disabled:opacity-30 transition-all">
                        Enviar
                    </button>
                </form>
                <div v-else class="py-5 text-center text-[10px] font-black uppercase tracking-widest text-gray-400">
                    Este chamado foi encerrado pelo suporte.
                </div>
            </div>
        </main>

        <Footer />
    </div>
</template>
