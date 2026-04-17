<script setup>
import { ref, onMounted } from 'vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    show: Boolean,
    preferenceId: String,
    publicKey: String,
    total: Number,
    itemsCount: Number,
    initPoint: String
});

const emit = defineEmits(['close']);

const isInitializing = ref(false);

const loadMPScript = () => {
    return new Promise((resolve, reject) => {
        if (window.MercadoPago) {
            resolve();
            return;
        }
        const script = document.createElement('script');
        script.src = 'https://sdk.mercadopago.com/js/v2';
        script.async = true;
        script.onload = () => resolve();
        script.onerror = () => reject(new Error('Falha ao carregar o script do Mercado Pago'));
        document.head.appendChild(script);
    });
};

const handlePayment = async () => {
    isInitializing.value = true;
    try {
        await loadMPScript();
        
        const mp = new window.MercadoPago(props.publicKey, {
            locale: 'pt-BR'
        });

        // Abre o Checkout Pro em um Modal controlado pelo SDK
        // Usar autoOpen: true evita o erro de tentar abrir antes da resolução da instância
        mp.checkout({
            preference: {
                id: props.preferenceId
            },
            autoOpen: true,
            theme: {
                elementsColor: '#FF5B00',
                headerColor: '#FF5B00',
            }
        });
    } catch (err) {
        console.error('Erro ao abrir checkout:', err);
        // Fallback final: redirecionamento direto
        window.location.href = props.initPoint;
    } finally {
        isInitializing.value = false;
    }
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
                    <h2 class="text-3xl font-black text-brand-dark uppercase tracking-tighter">Finalizar Pedido</h2>
                    <p class="text-[10px] text-brand-orange font-black uppercase tracking-widest mt-1">Checkout Seguro via Mercado Pago</p>
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
                            <div class="w-8 h-8 bg-brand-dark rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                            <span class="text-[11px] font-black text-gray-400 uppercase tracking-widest">Quantidade</span>
                        </div>
                        <span class="font-black text-brand-dark text-xl">{{ itemsCount }} fotos</span>
                    </div>

                    <div class="h-px bg-gray-200/50 w-full"></div>

                    <div class="flex justify-between items-end">
                        <div>
                            <p class="text-[11px] font-black text-gray-400 uppercase tracking-widest mb-1">Total a Pagar</p>
                            <p class="text-4xl font-black text-brand-blue tracking-tighter leading-none">R$ {{ total ? total.toFixed(2) : '0.00' }}</p>
                        </div>
                        <div class="bg-brand-blue/10 px-3 py-1 rounded-lg">
                            <span class="text-[8px] font-black text-brand-blue uppercase tracking-widest">BRL</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botão de Ação -->
            <div class="space-y-6">
                <button 
                    @click="handlePayment" 
                    :disabled="isInitializing"
                    class="w-full h-20 bg-brand-dark hover:bg-black text-white rounded-3xl flex items-center justify-center gap-4 transition-all active:scale-95 shadow-2xl shadow-brand-dark/20 group"
                >
                    <div v-if="isInitializing" class="w-6 h-6 border-2 border-white/20 border-t-white rounded-full animate-spin"></div>
                    <svg v-else class="w-6 h-6 text-brand-orange group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    <span class="text-xs font-black uppercase tracking-[0.3em]">{{ isInitializing ? 'Conectando...' : 'Pagar Agora' }}</span>
                </button>

                <div class="flex flex-col items-center gap-3">
                    <p class="text-[9px] text-gray-400 font-bold uppercase tracking-widest text-center leading-relaxed">
                        Ambiente Seguro Criptografado <br/> 
                        <span class="text-gray-300">Suas fotos são liberadas instantaneamente</span>
                    </p>
                    <div class="flex gap-4 grayscale opacity-30 mt-2">
                        <div class="w-8 h-5 bg-gray-400 rounded-sm"></div>
                        <div class="w-8 h-5 bg-gray-400 rounded-sm"></div>
                        <div class="w-8 h-5 bg-gray-400 rounded-sm"></div>
                    </div>
                </div>
            </div>

            <button @click="close" class="w-full mt-10 text-[9px] font-black text-gray-300 hover:text-brand-orange uppercase tracking-[0.4em] transition">
                ← Cancelar e voltar à galeria
            </button>
        </div>
    </Modal>
</template>
