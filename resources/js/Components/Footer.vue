<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';

const currentYear = new Date().getFullYear();
</script>

<template>
    <footer class="bg-brand-dark text-white pt-24 pb-12 border-t border-white/5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-16 mb-20">
                <!-- Branding Section -->
                <div class="md:col-span-5">
                    <Link :href="route('store.index')" class="inline-block mb-8">
                        <ApplicationLogo class="h-14 w-auto brightness-0 invert" />
                    </Link>
                    <p class="text-gray-400 text-sm leading-loose max-w-sm mb-10 font-medium">
                        A plataforma pioneira em fotografia esportiva no Brasil. 
                        Conectamos a emoção da superação à memória eterna de cada atleta.
                    </p>
                    <div class="flex gap-4">
                        <a href="#" class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center hover:bg-brand-blue transition-all duration-300">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center hover:bg-brand-blue transition-all duration-300">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                        </a>
                    </div>
                </div>

                <!-- Navigation Columns -->
                <div class="md:col-span-7 grid grid-cols-2 sm:grid-cols-3 gap-12">
                    <div>
                        <h4 class="text-white font-black uppercase tracking-[0.2em] text-[11px] mb-8">Navegação</h4>
                        <ul class="space-y-4">
                            <li><Link :href="route('store.index')" class="text-gray-400 hover:text-white text-xs transition-all uppercase tracking-widest font-bold">Página Inicial</Link></li>
                            <li><Link :href="route('store.photographers')" class="text-gray-400 hover:text-white text-xs transition-all uppercase tracking-widest font-bold">Fotógrafos</Link></li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="text-white font-black uppercase tracking-[0.2em] text-[11px] mb-8">Suporte</h4>
                        <ul class="space-y-4">
                            <li><a href="#" class="text-gray-400 hover:text-white text-xs transition-all uppercase tracking-widest font-bold">Central de Ajuda</a></li>
                            <li><a href="#" class="text-gray-400 hover:text-white text-xs transition-all uppercase tracking-widest font-bold">Termos de Uso</a></li>
                            <li><a href="#" class="text-gray-400 hover:text-white text-xs transition-all uppercase tracking-widest font-bold">Privacidade</a></li>
                        </ul>
                    </div>

                    <div class="col-span-2 sm:col-span-1">
                        <h4 class="text-white font-black uppercase tracking-[0.2em] text-[11px] mb-8">Newsletter</h4>
                        <p class="text-gray-400 text-[10px] uppercase font-bold tracking-widest mb-4">Receba novidades e promoções.</p>
                        <form class="relative">
                            <input type="email" placeholder="SEU E-MAIL" class="w-full bg-white/5 border-white/10 rounded-xl py-3 px-4 text-[10px] font-bold text-white placeholder:text-gray-600 focus:ring-brand-blue/30 focus:border-brand-blue" />
                            <button class="absolute right-2 top-1/2 -translate-y-1/2 text-brand-blue hover:text-white transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Bottom Section -->
            <div class="pt-12 border-t border-white/5 flex flex-col md:flex-row justify-between items-center gap-8">
                <div class="flex flex-col md:flex-row items-center gap-8">
                    <p class="text-gray-500 text-[10px] font-black uppercase tracking-[0.2em]">
                        &copy; {{ currentYear }} FOTSPORT. TODOS OS DIREITOS RESERVADOS.
                    </p>
                </div>
                
                <div class="flex items-center gap-6">
                    <span class="text-gray-600 text-[9px] font-black uppercase tracking-[0.3em]">MADE WITH PASSION BY SPORT LOVERS</span>
                </div>
            </div>
        </div>
    </footer>
</template>
