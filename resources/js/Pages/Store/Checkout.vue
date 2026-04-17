<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { onMounted } from 'vue';
import Footer from '@/Components/Footer.vue';
import Navbar from '@/Components/Navbar.vue';

const props = defineProps({
    preferenceId: String,
    publicKey: String,
    photos: Array,
    total: Number
});

onMounted(() => {
    // Carrega SDK oficial do Mercado Pago v2
    const script = document.createElement('script');
    script.src = 'https://sdk.mercadopago.com/js/v2';
    script.onload = () => {
        const mp = new window.MercadoPago(props.publicKey, {
            locale: 'pt-BR'
        });

        // Wallet Brick — botão de pagamento oficial MP
        mp.bricks().create('wallet', 'mp-wallet-container', {
            initialization: {
                preferenceId: props.preferenceId,
                redirectMode: 'self',   // Redireciona a própria página para o MP
            },
            customization: {
                texts: {
                    action: 'pay',
                    valueProp: 'smart_option',
                },
                visual: {
                    buttonBackground: 'black',
                    borderRadius: '24px',
                }
            },
        });
    };
    document.head.appendChild(script);
});
</script>

<template>
    <Head title="Finalizar Compra | Fotsport" />

    <div class="min-h-screen bg-white flex flex-col font-sans text-brand-dark">
        <Navbar />

        <div class="flex-grow max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 w-full">
            <div class="max-w-4xl mx-auto">

                <div class="flex flex-col items-center mb-16 text-center">
                    <h2 class="text-3xl md:text-5xl font-black text-brand-dark uppercase tracking-tighter mb-4">
                        Resumo do <span class="text-brand-orange">Pedido</span>
                    </h2>
                    <div class="h-1.5 w-20 bg-brand-blue rounded-full"></div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                    <!-- Detalhes dos Itens -->
                    <div class="space-y-6">
                        <div v-for="photo in photos" :key="photo.id"
                             class="flex items-center gap-6 p-6 bg-gray-50 rounded-[2rem] border border-gray-100 group hover:border-brand-orange/20 transition-all">
                            <div class="relative shrink-0">
                                <img :src="'/' + photo.watermarked_path"
                                     class="w-24 h-24 object-cover rounded-2xl shadow-lg group-hover:scale-105 transition-transform" />
                                <div class="absolute -top-2 -right-2 bg-brand-orange text-white text-[10px] font-black px-2 py-1 rounded-lg">
                                    📷
                                </div>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[10px] text-brand-orange uppercase font-black tracking-widest mb-1">{{ photo.event ? photo.event.name : 'Série Exclusiva' }}</p>
                                <p class="text-brand-dark font-black text-lg truncate mb-2 leading-none">{{ photo.user ? photo.user.name : 'Fotógrafo Oficial' }}</p>
                                <p class="text-brand-blue font-black">R$ {{ Number(photo.price).toFixed(2) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Checkout e Total -->
                    <div class="space-y-8">
                        <div class="bg-brand-dark rounded-[2.5rem] p-10 text-white shadow-2xl relative overflow-hidden">
                            <div class="absolute right-0 top-0 w-32 h-32 bg-brand-orange/10 rounded-full blur-3xl -mr-16 -mt-16"></div>
                            
                            <h3 class="text-xs font-black uppercase tracking-[0.4em] text-brand-orange mb-10">Total da Compra</h3>
                            
                            <div class="flex justify-between items-end mb-12">
                                <div>
                                    <p class="text-white/40 text-[10px] font-black uppercase tracking-widest mb-1">Itens Selecionados</p>
                                    <p class="text-3xl font-black">{{ photos.length }} <span class="text-sm font-medium text-white/60">un.</span></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-white/40 text-[10px] font-black uppercase tracking-widest mb-1">Valor Total</p>
                                    <p class="text-5xl font-black text-brand-orange">R$ {{ total.toFixed(2) }}</p>
                                </div>
                            </div>

                            <div class="pt-8 border-t border-white/10">
                                <p class="text-[10px] text-center text-white/30 mb-6 uppercase font-black tracking-widest">
                                    Pagamento Seguro Mercado Pago
                                </p>

                                <!-- Container onde o SDK do MP injeta o botão -->
                                <div id="mp-wallet-container" class="w-full"></div>

                                <div class="mt-8 flex justify-center items-center gap-4 opacity-30 group">
                                    <span class="h-px bg-white flex-grow"></span>
                                    <div class="text-[10px] font-black uppercase tracking-widest text-white">Fotsport Safe</div>
                                    <span class="h-px bg-white flex-grow"></span>
                                </div>
                            </div>
                        </div>

                        <Link :href="route('store.index')" class="flex items-center justify-center gap-2 text-gray-400 hover:text-brand-orange font-black text-xs uppercase tracking-widest transition-all">
                            ← Continuar Escolhendo fotos
                        </Link>
                    </div>
                </div>

            </div>
        </div>

        <Footer />
    </div>
</template>
