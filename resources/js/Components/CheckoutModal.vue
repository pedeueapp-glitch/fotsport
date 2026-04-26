<script setup>
import { ref } from 'vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    show: Boolean,
    pixQrcode: String,
    pixCopyPaste: String,
    total: Number,
    itemsCount: Number,
});

const emit = defineEmits(['close']);

const copied = ref(false);

const copyToClipboard = () => {
    navigator.clipboard.writeText(props.pixCopyPaste);
    copied.value = true;
    setTimeout(() => {
        copied.value = false;
    }, 2000);
};

const close = () => {
    emit('close');
};
</script>

<template>
    <Modal :show="show" @close="close" maxWidth="md">
        <div class="p-10">
            <div class="flex justify-between items-start mb-10">
                <div>
                    <h2 class="text-3xl font-black text-brand-dark uppercase tracking-tighter">Pagamento Pix</h2>
                    <p class="text-[10px] text-brand-orange font-black uppercase tracking-widest mt-1">Sua compra é liberada instantaneamente</p>
                </div>
                <button @click="close" class="p-2 text-gray-400 hover:text-brand-dark transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Resumo Financeiro Premium -->
            <div class="bg-gray-50 rounded-[3rem] p-10 mb-10 border border-gray-100 relative group overflow-hidden shadow-inner">
                <div class="absolute top-0 right-0 w-40 h-40 bg-brand-orange/5 rounded-full blur-3xl -mr-20 -mt-20 group-hover:bg-brand-orange/10 transition-all"></div>
                
                <div class="relative z-10 space-y-6">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-brand-dark rounded-lg flex items-center justify-center text-white">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                            <span class="text-[11px] font-black text-gray-400 uppercase tracking-widest">{{ itemsCount }} fotos selecionadas</span>
                        </div>
                    </div>

                    <div class="h-px bg-gray-200/50 w-full"></div>

                    <div class="flex justify-between items-end">
                        <div>
                            <p class="text-[11px] font-black text-gray-400 uppercase tracking-widest mb-1">Total do Pedido</p>
                            <p class="text-4xl font-black text-brand-blue tracking-tighter leading-none">R$ {{ total ? total.toFixed(2) : '0.00' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- QR Code e Pix Copia e Cola -->
            <div class="space-y-8 text-center">
                <div v-if="pixQrcode" class="bg-white p-4 border-2 border-gray-100 rounded-[2rem] inline-block shadow-xl">
                    <img :src="pixQrcode" alt="QR Code Pix" class="w-56 h-56 mx-auto rounded-xl" />
                </div>

                <div class="space-y-4">
                    <p class="text-[10px] text-gray-400 font-black uppercase tracking-[.2em]">Ou use o código Copia e Cola</p>
                    
                    <div class="relative group">
                        <textarea 
                            readonly 
                            :value="pixCopyPaste"
                            class="w-full bg-gray-50 border-2 border-gray-100 rounded-3xl p-6 text-[10px] font-mono text-gray-500 break-all resize-none focus:ring-0 focus:border-brand-blue h-24"
                        ></textarea>
                        
                        <button 
                            @click="copyToClipboard"
                            class="absolute bottom-4 right-4 bg-brand-dark hover:bg-black text-white px-6 py-2 rounded-xl text-[9px] font-black uppercase tracking-widest transition-all active:scale-95 shadow-xl"
                        >
                            {{ copied ? 'Copiado!' : 'Copiar Código' }}
                        </button>
                    </div>
                </div>

                <div class="bg-blue-50 p-6 rounded-[2rem] border border-blue-100/50">
                    <div class="flex items-center gap-4 text-left">
                        <div class="w-10 h-10 bg-brand-blue rounded-full flex items-center justify-center text-white shrink-0 shadow-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <p class="text-[9px] text-brand-blue font-bold uppercase tracking-widest leading-relaxed">
                            Após o pagamento, suas fotos serão desbloqueadas automaticamente no seu painel em até 30 segundos.
                        </p>
                    </div>
                </div>
            </div>

            <button @click="close" class="w-full mt-10 text-[9px] font-black text-gray-300 hover:text-brand-orange uppercase tracking-[0.4em] transition">
                ← Fechar e voltar
            </button>
        </div>
    </Modal>
</template>

