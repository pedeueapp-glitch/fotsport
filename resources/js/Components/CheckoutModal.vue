<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import Modal from '@/Components/Modal.vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    show: Boolean,
    pixQrcode: String,
    pixCopyPaste: String,
    total: Number,
    itemsCount: Number,
    txid: String,
});

const emit = defineEmits(['close']);

const copied = ref(false);
const isChecking = ref(false);
const paymentStatus = ref('pending'); // pending, success, error

const copyToClipboard = () => {
    navigator.clipboard.writeText(props.pixCopyPaste);
    copied.value = true;
    setTimeout(() => { copied.value = false; }, 2000);
};

const checkStatus = async () => {
    if (isChecking.value) return;
    isChecking.value = true;
    
    try {
        const response = await axios.post(route('store.checkout.check'), {
            txid: props.txid
        });

        if (response.data.status === 'CONCLUIDA') {
            paymentStatus.value = 'success';
            setTimeout(() => {
                router.visit(route('customer.dashboard'));
            }, 2000);
        }
    } catch (err) {
        console.error('Erro ao verificar status:', err);
    } finally {
        isChecking.value = false;
    }
};

// Verificação automática a cada 10 segundos
let statusInterval = null;
onMounted(() => {
    statusInterval = setInterval(checkStatus, 10000);
});
onUnmounted(() => {
    if (statusInterval) clearInterval(statusInterval);
});

const close = () => {
    emit('close');
};
</script>

<template>
    <Modal :show="show" @close="close" maxWidth="sm">
        <div class="p-8">
            <!-- Header Compacto -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-xl font-black text-brand-dark uppercase tracking-tight">Pagamento Pix</h2>
                    <p class="text-[9px] text-brand-orange font-black uppercase tracking-widest">Liberação instantânea</p>
                </div>
                <button @click="close" class="p-2 text-gray-300 hover:text-brand-dark transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Status Sucesso -->
            <div v-if="paymentStatus === 'success'" class="py-10 text-center animate-bounce">
                <div class="w-20 h-20 bg-green-500 rounded-full flex items-center justify-center text-white mx-auto mb-6 shadow-xl shadow-green-500/20">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <h3 class="text-xl font-black text-brand-dark uppercase tracking-tight">Pagamento Confirmado!</h3>
                <p class="text-xs text-gray-400 mt-2 font-medium">Redirecionando para suas fotos...</p>
            </div>

            <template v-else>
                <!-- Resumo Minimalista -->
                <div class="bg-gray-50 rounded-[2rem] p-6 mb-6 border border-gray-100 flex justify-between items-center">
                    <div>
                        <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-0.5">Total</p>
                        <p class="text-2xl font-black text-brand-blue tracking-tighter">R$ {{ total ? total.toFixed(2) : '0.00' }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-0.5">Itens</p>
                        <p class="text-lg font-black text-brand-dark tracking-tighter">{{ itemsCount }} fotos</p>
                    </div>
                </div>

                <!-- QR Code Menor -->
                <div class="text-center mb-6">
                    <div v-if="pixQrcode" class="bg-white p-3 border border-gray-100 rounded-3xl inline-block shadow-lg mb-4">
                        <img :src="pixQrcode" alt="QR Code Pix" class="w-40 h-40 mx-auto rounded-lg" />
                    </div>
                    
                    <div class="relative max-w-[240px] mx-auto">
                        <input 
                            readonly 
                            :value="pixCopyPaste"
                            class="w-full bg-gray-50 border border-gray-100 rounded-2xl pl-4 pr-12 py-3 text-[9px] font-mono text-gray-400 truncate focus:ring-0 focus:border-brand-blue"
                        />
                        <button 
                            @click="copyToClipboard"
                            class="absolute right-2 top-1.5 p-2 bg-brand-dark text-white rounded-xl hover:bg-black transition-all"
                            title="Copiar Código"
                        >
                            <svg v-if="!copied" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" /></svg>
                            <span v-else class="text-[8px] font-black uppercase">✓</span>
                        </button>
                    </div>
                </div>

                <!-- Botão de Verificação -->
                <button 
                    @click="checkStatus"
                    :disabled="isChecking"
                    class="w-full py-4 bg-brand-blue hover:bg-brand-blue-hover text-white rounded-2xl flex items-center justify-center gap-3 transition-all active:scale-95 shadow-xl shadow-brand-blue/10 mb-4"
                >
                    <div v-if="isChecking" class="w-4 h-4 border-2 border-white/20 border-t-white rounded-full animate-spin"></div>
                    <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="text-[10px] font-black uppercase tracking-widest">{{ isChecking ? 'Verificando...' : 'Já paguei, confirmar' }}</span>
                </button>

                <p class="text-[8px] text-gray-300 font-bold uppercase tracking-[.2em] text-center">
                    Verificação automática a cada 10 segundos
                </p>
            </template>
        </div>
    </Modal>
</template>


