<script setup>
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, onMounted, watch } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const showCustomerModal = ref(false);
const needsRegistration = ref(false);
const mobileMenuOpen = ref(false);

const page = usePage();

// Abre o modal de login do cliente se disparado globalmente ou via Flash
watch(() => page.props.flash?.show_login, (newVal) => {
    if (newVal) {
        showCustomerModal.value = true;
    }
}, { immediate: true });

onMounted(() => {
    window.addEventListener('show-customer-login', () => {
        showCustomerModal.value = true;
    });
});

const customerForm = useForm({
    cpf: '',
    name: '',
    phone: '',
});

const submitCustomerAuth = () => {
    customerForm.post(route('store.customer.login'), {
        onSuccess: (page) => {
            if (page.props.flash?.needs_registration) {
                needsRegistration.value = true;
            } else {
                showCustomerModal.value = false;
            }
        }
    });
};

const toggleMobileMenu = () => {
    mobileMenuOpen.value = !mobileMenuOpen.value;
};
</script>

<template>
    <nav class="bg-white shadow-sm sticky top-0 z-[100] border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-28 items-center">
                <!-- Logo -->
                <div class="flex items-center">
                    <Link :href="route('store.index')" class="flex items-center gap-2">
                        <ApplicationLogo class="h-20 w-auto" />
                    </Link>
                </div>

                <!-- Navigation (Desktop) -->
                <div class="hidden md:flex items-center gap-6">
                    <!-- FOTÓGRAFO LOGADO (Prioridade Máxima) -->
                    <template v-if="$page.props.auth.user">
                        <Link :href="route('dashboard')" 
                              :class="route().current('dashboard') ? 'text-brand-orange' : 'text-gray-600'"
                              class="font-black text-[11px] uppercase tracking-widest hover:text-brand-orange transition-all">
                            Meus Eventos
                        </Link>
                        <Link :href="route('financial.index')" 
                              :class="route().current('financial.*') ? 'text-brand-orange' : 'text-gray-600'"
                              class="font-black text-[11px] uppercase tracking-widest hover:text-brand-orange transition-all">
                            Financeiro
                        </Link>
                        <!-- Admin Links Desktop -->
                        <template v-if="$page.props.auth.user.is_superadmin">
                            <div class="h-6 w-px bg-gray-100 mx-2"></div>
                            <Link :href="route('admin.photographers.index')" 
                                  :class="route().current('admin.photographers.*') ? 'text-brand-orange' : 'text-gray-400'"
                                  class="font-black text-[10px] uppercase tracking-widest hover:text-brand-orange transition-all">
                                Fotógrafos
                            </Link>
                            <Link :href="route('admin.events.index')" 
                                  :class="route().current('admin.events.*') ? 'text-brand-orange' : 'text-gray-400'"
                                  class="font-black text-[10px] uppercase tracking-widest hover:text-brand-orange transition-all">
                                Eventos
                            </Link>
                            <Link :href="route('admin.withdrawals.index')" 
                                  :class="route().current('admin.withdrawals.*') ? 'text-brand-orange' : 'text-gray-400'"
                                  class="font-black text-[10px] uppercase tracking-widest hover:text-brand-orange transition-all">
                                Saques
                            </Link>
                            <Link :href="route('admin.billing.index')" 
                                  :class="route().current('admin.billing.*') ? 'text-brand-orange' : 'text-gray-400'"
                                  class="font-black text-[10px] uppercase tracking-widest hover:text-brand-orange transition-all">
                                Faturamento
                            </Link>
                            <Link :href="route('admin.settings.index')" 
                                  :class="route().current('admin.settings.*') ? 'text-brand-orange' : 'text-gray-400'"
                                  class="font-black text-[10px] uppercase tracking-widest hover:text-brand-orange transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            </Link>
                        </template>
                        <Link :href="route('profile.edit')" class="text-gray-400 hover:text-brand-dark transition-colors px-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </Link>
                        <Link :href="route('logout')" method="post" as="button" class="text-red-400 hover:text-red-600 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        </Link>
                    </template>

                    <!-- CLIENTE LOGADO (CPF) OU VISITANTE -->
                    <template v-else>
                        <!-- Se for apenas cliente logado -->
                        <Link v-if="$page.props.auth.customer" :href="route('customer.dashboard')" 
                              class="text-brand-blue font-black text-[11px] uppercase tracking-[0.2em] border-b-2 border-brand-blue/20 pb-1">
                            Minhas Fotos
                        </Link>
                        
                        <!-- Botão "Seja um Fotógrafo" (Aparece para visitante e cliente logado) -->
                        <Link :href="route('register')" 
                              class="bg-brand-blue hover:bg-brand-blue-hover text-white px-8 py-3 rounded-2xl font-black text-[10px] transition-all uppercase tracking-[0.2em] shadow-xl shadow-brand-blue/10 active:scale-95">
                            Seja um Fotógrafo
                        </Link>

                        <!-- Link para Sair do CPF (Discreto) -->
                        <Link v-if="$page.props.auth.customer" :href="route('logout')" method="post" as="button"
                              class="text-[9px] text-gray-300 font-bold uppercase tracking-widest hover:text-red-400 transition-colors ml-4">
                            Sair
                        </Link>

                        <!-- Link entrar para quem não é fotógrafo -->
                        <Link v-if="!$page.props.auth.customer" :href="route('login')" 
                              class="text-gray-400 font-black text-[10px] uppercase tracking-widest hover:text-brand-orange transition-colors">
                            Entrar
                        </Link>
                    </template>
                </div>

                <!-- Mobile Menu Button -->
                <div class="md:hidden flex items-center">
                    <button @click="toggleMobileMenu" class="text-gray-600 p-2 focus:outline-none">
                        <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path v-if="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                            <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Drawer -->
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0 -translate-y-4"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 -translate-y-4"
        >
            <div v-if="mobileMenuOpen" class="md:hidden bg-white border-b border-gray-100 shadow-2xl p-6 space-y-4 italic">
                <!-- Se é Fotógrafo -->
                <template v-if="$page.props.auth.user">
                    <Link :href="route('dashboard')" @click="mobileMenuOpen = false" class="block text-gray-700 font-black text-xs uppercase tracking-widest py-3 border-b border-gray-50">Meus Eventos</Link>
                    <Link :href="route('financial.index')" @click="mobileMenuOpen = false" class="block text-gray-700 font-black text-xs uppercase tracking-widest py-3 border-b border-gray-50">Financeiro</Link>
                    
                    <!-- Admin Mobile Links -->
                    <template v-if="$page.props.auth.user.is_superadmin">
                        <Link :href="route('admin.photographers.index')" @click="mobileMenuOpen = false" class="block text-brand-orange font-black text-xs uppercase tracking-widest py-3 border-b border-gray-50">Gerenciar Fotógrafos</Link>
                        <Link :href="route('admin.events.index')" @click="mobileMenuOpen = false" class="block text-brand-orange font-black text-xs uppercase tracking-widest py-3 border-b border-gray-50">Todos Eventos</Link>
                        <Link :href="route('admin.withdrawals.index')" @click="mobileMenuOpen = false" class="block text-brand-orange font-black text-xs uppercase tracking-widest py-3 border-b border-gray-50">Aprovar Saques</Link>
                        <Link :href="route('admin.billing.index')" @click="mobileMenuOpen = false" class="block text-brand-orange font-black text-xs uppercase tracking-widest py-3 border-b border-gray-50">Faturamento Global</Link>
                        <Link :href="route('admin.settings.index')" @click="mobileMenuOpen = false" class="block text-brand-orange font-black text-xs uppercase tracking-widest py-3 border-b border-gray-50">Configurações</Link>
                    </template>
                    
                    <Link :href="route('logout')" method="post" as="button" class="block w-full text-left text-red-500 font-black text-xs uppercase tracking-widest py-3">Sair</Link>
                </template>
                
                <!-- Visitante ou Cliente -->
                <template v-else>
                    <Link v-if="$page.props.auth.customer" :href="route('customer.dashboard')" class="block text-brand-blue font-black text-xs uppercase tracking-widest py-3 border-b border-gray-50">Minhas Fotos</Link>
                    <Link :href="route('register')" class="block bg-brand-blue text-white text-center py-4 rounded-xl font-black text-[10px] uppercase tracking-widest shadow-lg">Seja um Fotógrafo</Link>
                    <Link :href="route('login')" class="block text-center text-gray-400 font-black text-[10px] uppercase tracking-widest pt-4">Entrar como Fotógrafo</Link>
                    <Link v-if="$page.props.auth.customer" :href="route('logout')" method="post" as="button" class="block text-center text-gray-300 text-[9px] uppercase font-bold pt-2">Sair do CPF</Link>
                </template>
            </div>
        </Transition>

        <!-- Modal de Login do Cliente -->
        <Modal :show="showCustomerModal" @close="showCustomerModal = false">
            <div class="p-8">
                <div class="flex justify-center mb-6">
                    <ApplicationLogo class="h-8 w-auto" />
                </div>
                <h2 class="text-2xl font-black text-gray-900 text-center mb-2 uppercase tracking-tighter">
                    {{ needsRegistration ? 'Complete seu cadastro' : 'Bem-vindo' }}
                </h2>
                <p class="text-center text-sm text-gray-500 mb-8 font-medium">
                    {{ needsRegistration ? 'Quase lá! Só mais alguns dados.' : 'Informe seu CPF para acessar suas fotos.' }}
                </p>

                <form @submit.prevent="submitCustomerAuth" class="space-y-6">
                    <div>
                        <InputLabel for="cpf" value="CPF" class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-2" />
                        <TextInput
                            id="cpf"
                            v-model="customerForm.cpf"
                            type="text"
                            class="mt-1 block w-full rounded-xl border-gray-100 focus:border-brand-orange focus:ring-brand-orange"
                            placeholder="000.000.000-00"
                            :disabled="needsRegistration"
                            required
                        />
                    </div>

                    <div v-if="needsRegistration" class="space-y-6">
                        <div>
                            <InputLabel for="name" value="Nome Completo" />
                            <TextInput id="name" v-model="customerForm.name" type="text" class="mt-1 block w-full rounded-xl border-gray-100" required />
                        </div>
                        <div>
                            <InputLabel for="phone" value="WhatsApp" />
                            <TextInput id="phone" v-model="customerForm.phone" type="text" class="mt-1 block w-full rounded-xl border-gray-100" required />
                        </div>
                    </div>

                    <div class="mt-8">
                        <PrimaryButton class="w-full justify-center py-5 bg-brand-orange hover:bg-brand-orange-hover rounded-2xl shadow-xl shadow-brand-orange/20" :disabled="customerForm.processing">
                            {{ needsRegistration ? 'Finalizar' : 'Acessar Galeria' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </nav>
</template>
