<script setup>
import { computed, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const show = ref(false);
const message = ref('');
const type = ref('success'); // success, error

const flash = computed(() => page.props.flash);

watch(flash, (newFlash) => {
    if (newFlash.success || newFlash.error) {
        message.value = newFlash.success || newFlash.error;
        type.value = newFlash.success ? 'success' : 'error';
        show.value = true;
        
        setTimeout(() => {
            show.value = false;
        }, 5000);
    }
}, { deep: true, immediate: true });

const close = () => {
    show.value = false;
};
</script>

<template>
    <Transition
        enter-active-class="transform ease-out duration-300 transition"
        enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
        enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
        leave-active-class="transition ease-in duration-100"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div v-if="show" class="fixed top-24 right-4 z-[999] w-full max-w-sm overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-black ring-opacity-5 pointer-events-auto border border-gray-100">
            <div class="p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <!-- Sucesso -->
                        <div v-if="type === 'success'" class="w-10 h-10 bg-green-50 rounded-xl flex items-center justify-center text-green-500">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <!-- Erro -->
                        <div v-else class="w-10 h-10 bg-red-50 rounded-xl flex items-center justify-center text-red-500">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4 w-0 flex-1 pt-0.5">
                        <p class="text-[10px] font-black uppercase tracking-widest" :class="type === 'success' ? 'text-green-600' : 'text-red-600'">
                            {{ type === 'success' ? 'Sucesso' : 'Erro no Sistema' }}
                        </p>
                        <p class="mt-1 text-xs font-bold text-brand-dark leading-relaxed">
                            {{ message }}
                        </p>
                    </div>
                    <div class="ml-4 flex flex-shrink-0">
                        <button type="button" @click="close" class="inline-flex rounded-md bg-white text-gray-400 hover:text-gray-500 focus:outline-none">
                            <span class="sr-only">Fechar</span>
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            <!-- Barra de Progresso -->
            <div class="h-1 w-full bg-gray-50 overflow-hidden">
                <div class="h-full transition-all duration-[5000ms] ease-linear" :class="type === 'success' ? 'bg-green-500' : 'bg-red-500'" :style="{ width: show ? '0%' : '100%' }"></div>
            </div>
        </div>
    </Transition>
</template>
