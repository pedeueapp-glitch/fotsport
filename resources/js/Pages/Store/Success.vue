<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { onMounted } from 'vue';
import Footer from '@/Components/Footer.vue';
import Navbar from '@/Components/Navbar.vue';
import confetti from 'canvas-confetti';

onMounted(() => {
    // Explosão de confetes para celebrar a compra bem-sucedida
    const duration = 3 * 1000;
    const end = Date.now() + duration;

    (function frame() {
        confetti({
            particleCount: 3,
            angle: 60,
            spread: 55,
            origin: { x: 0 },
            colors: ['#FF5B00', '#0047FF']
        });
        confetti({
            particleCount: 3,
            angle: 120,
            spread: 55,
            origin: { x: 1 },
            colors: ['#FF5B00', '#0047FF']
        });

        if (Date.now() < end) {
            requestAnimationFrame(frame);
        }
    }());
});
</script>

<template>
    <Head title="Pagamento Confirmado | Fotsport" />

    <div class="min-h-screen bg-white flex flex-col font-sans text-brand-dark overflow-hidden">
        <Navbar />

        <main class="flex-grow flex items-center justify-center py-20 px-4 relative">
            <!-- Background Decorations -->
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-4xl h-full max-h-4xl pointer-events-none">
                <div class="absolute top-0 right-0 w-96 h-96 bg-brand-orange/10 rounded-full blur-[120px]"></div>
                <div class="absolute bottom-0 left-0 w-96 h-96 bg-brand-blue/10 rounded-full blur-[120px]"></div>
            </div>

            <div class="max-w-2xl w-full relative z-10 animate-fade-in">
                <div class="bg-white/80 backdrop-blur-2xl rounded-[4rem] p-12 md:p-20 text-center border border-gray-100 shadow-[0_50px_100px_rgba(0,0,0,0.05)]">
                    
                    <div class="relative inline-block mb-12">
                        <!-- Icon Success -->
                        <div class="w-32 h-32 bg-green-500 rounded-[2.5rem] flex items-center justify-center shadow-2xl shadow-green-500/30 rotate-6 animate-bounce-slow">
                            <svg class="w-16 h-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div class="absolute -bottom-4 -right-4 w-12 h-12 bg-white rounded-2xl shadow-xl flex items-center justify-center animate-pulse">
                            <svg class="w-6 h-6 text-brand-orange" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
                        </div>
                    </div>
                    
                    <div class="inline-flex items-center gap-3 mb-6 bg-green-50 px-6 py-2 rounded-full border border-green-100">
                        <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                        <span class="text-[10px] font-black text-green-600 uppercase tracking-[.4em]">Pagamento Confirmado</span>
                    </div>

                    <h1 class="text-4xl md:text-6xl font-black text-brand-dark uppercase tracking-tighter mb-8 leading-tight">
                        Excelente Escolha! <br/> <span class="text-brand-blue">Fotos Liberadas.</span>
                    </h1>
                    
                    <p class="text-gray-500 text-lg font-medium mb-12 max-w-md mx-auto leading-relaxed">
                        Sua transação foi concluída com sucesso. Agora suas memórias estão prontas para serem guardadas para sempre.
                    </p>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <Link :href="route('customer.dashboard')" class="flex items-center justify-center gap-3 px-10 py-6 bg-brand-dark text-white text-[11px] font-black uppercase tracking-[.3em] rounded-3xl hover:bg-black transition-all shadow-2xl shadow-brand-dark/20 active:scale-95 group">
                            Minha Galeria
                            <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </Link>
                        
                        <Link :href="route('store.index')" class="flex items-center justify-center px-10 py-6 bg-white text-gray-400 text-[11px] font-black uppercase tracking-[.3em] rounded-3xl border-2 border-gray-100 hover:border-brand-orange hover:text-brand-orange transition-all active:scale-95">
                            Nova Busca
                        </Link>
                    </div>
                </div>

                <div class="mt-16 text-center">
                    <div class="flex items-center justify-center gap-6 opacity-30 mb-6">
                        <div class="w-12 h-12 bg-gray-200 rounded-xl"></div>
                        <div class="w-12 h-12 bg-gray-200 rounded-xl"></div>
                        <div class="w-12 h-12 bg-gray-200 rounded-xl"></div>
                    </div>
                    <p class="text-xs text-gray-400 font-bold uppercase tracking-widest">
                        Obrigado por escolher a Fotsport para registrar seus melhores momentos.
                    </p>
                </div>
            </div>
        </main>

        <Footer />
    </div>
</template>

<style scoped>
.animate-fade-in {
    animation: fadeIn 1s ease-out;
}
.animate-bounce-slow {
    animation: bounceSlow 3s infinite;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
}
@keyframes bounceSlow {
    0%, 100% { transform: rotate(6deg) translateY(0); }
    50% { transform: rotate(3deg) translateY(-10px); }
}
</style>
