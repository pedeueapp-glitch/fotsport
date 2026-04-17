<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <footer class="bg-white border-t border-gray-100 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12">
                <!-- Branding -->
                <div class="col-span-1 md:col-span-2">
                    <Link :href="route('store.index')">
                        <ApplicationLogo class="h-10 w-auto mb-6" />
                    </Link>
                    <p class="text-gray-500 text-sm max-w-sm leading-relaxed">
                        A plataforma definitiva para fotógrafos esportivos e atletas. 
                        Encontre suas conquistas, guarde suas memórias.
                    </p>
                </div>

                <!-- Links -->
                <div>
                    <h4 class="text-brand-dark font-bold uppercase tracking-widest text-xs mb-6">Plataforma</h4>
                    <ul class="space-y-4">
                        <li><Link :href="route('store.index')" class="text-gray-500 hover:text-brand-orange text-sm transition-colors uppercase tracking-widest font-medium">Início</Link></li>
                        <li><Link :href="route('login')" class="text-gray-500 hover:text-brand-orange text-sm transition-colors uppercase tracking-widest font-medium">Área do Fotógrafo</Link></li>
                    </ul>
                </div>

                <!-- Contact -->
                <div>
                    <h4 class="text-brand-dark font-bold uppercase tracking-widest text-xs mb-6">Contato</h4>
                    <ul class="space-y-4">
                        <li class="text-gray-500 text-sm flex items-center gap-2">
                            <span class="text-brand-orange">●</span> suporte@fotsport.com.br
                        </li>
                        <li class="text-gray-500 text-sm flex items-center gap-2">
                            <span class="text-brand-orange">●</span> @fotsport_oficial
                        </li>
                    </ul>
                </div>
            </div>

            <div class="mt-16 pt-8 border-t border-gray-50 flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-gray-400 text-xs uppercase tracking-widest">
                    &copy; {{ new Date().getFullYear() }} Fotsport. Todos os direitos reservados.
                </p>
                <div class="flex gap-6">
                   <span class="text-gray-300 text-[10px] uppercase font-bold tracking-[0.2em]">Tecnologia para Esporte</span>
                </div>
            </div>
        </div>
    </footer>
</template>
