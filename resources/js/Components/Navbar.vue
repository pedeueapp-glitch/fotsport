<script setup>
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, onMounted, watch } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputError from '@/Components/InputError.vue';
import Checkbox from '@/Components/Checkbox.vue';

const showCustomerModal = ref(false);
const showPhotographerModal = ref(false);
const isLoginMode = ref(true);
const needsRegistration = ref(false);
const mobileMenuOpen = ref(false);
const mobilePanelExpanded = ref(false);

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
    window.addEventListener('show-photographer-modal', () => {
        showPhotographerModal.value = true;
        isLoginMode.value = false;
    });
});

const customerForm = useForm({
    cpf: '',
    name: '',
    phone: '',
});

const searchForm = useForm({
    q: '',
});

const loginForm = useForm({
    email: '',
    password: '',
    remember: false,
});

const registerForm = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submitSearch = () => {
    if (searchForm.q.trim().length > 2) {
        searchForm.get(route('store.search.events'));
    }
};

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

const submitLogin = () => {
    loginForm.post(route('login'), {
        onFinish: () => loginForm.reset('password'),
    });
};

const submitRegister = () => {
    registerForm.post(route('register'), {
        onFinish: () => registerForm.reset('password', 'password_confirmation'),
    });
};

const toggleMobileMenu = () => {
    mobileMenuOpen.value = !mobileMenuOpen.value;
};
</script>

<template>
    <nav class="bg-white/80 backdrop-blur-md sticky top-0 z-[100] border-b border-gray-100/50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20 items-center gap-4">
                <!-- Logo -->
                <div class="flex items-center shrink-0">
                    <Link :href="route('store.index')" class="flex items-center gap-2">
                        <ApplicationLogo class="h-12 w-auto" />
                    </Link>
                </div>

                <!-- Search Bar (Desktop) -->
                <div class="hidden md:flex flex-grow max-w-md mx-8">
                    <form @submit.prevent="submitSearch" class="relative w-full">
                        <input 
                            type="text" 
                            v-model="searchForm.q"
                            placeholder="Buscar evento pelo nome..."
                            class="w-full bg-gray-50/50 border border-gray-100 rounded-full py-2.5 px-6 text-[10px] font-bold text-brand-dark focus:ring-2 focus:ring-brand-blue/10 focus:bg-white transition-all placeholder:text-gray-400 uppercase tracking-widest"
                        >
                        <button type="submit" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-brand-blue transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </button>
                    </form>
                </div>

                <!-- Navigation (Desktop) -->
                <div class="hidden md:flex items-center gap-6 shrink-0">
                    <Link :href="route('store.photographers')" 
                        class="text-[9px] font-black uppercase tracking-[0.2em] text-brand-dark hover:text-brand-blue transition-colors">
                        Parceiros
                    </Link>

                    <div class="h-4 w-px bg-gray-200"></div>

                    <!-- FOTÓGRAFO OU ADMIN LOGADO -->
                    <template v-if="$page.props.auth.user">
                        <Link v-if="$page.props.auth.user.is_superadmin"
                              :href="route('admin.photographers.index')" 
                              class="bg-brand-dark text-white px-5 py-2.5 rounded-xl font-black text-[9px] transition-all uppercase tracking-widest hover:bg-brand-blue shadow-lg shadow-brand-dark/10 active:scale-95">
                            Administração
                        </Link>
                        <Link v-else
                              :href="route('dashboard')" 
                              class="bg-brand-dark text-white px-5 py-2.5 rounded-xl font-black text-[9px] transition-all uppercase tracking-widest hover:bg-brand-blue shadow-lg shadow-brand-dark/10 active:scale-95">
                            Meu Painel
                        </Link>

                        <Link :href="route('logout')" method="post" as="button" class="text-gray-400 hover:text-red-500 transition-colors p-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        </Link>
                    </template>
                    <!-- CLIENTE LOGADO (CPF) OU VISITANTE -->
                    <template v-else>
                        <Link v-if="$page.props.auth.customer" :href="route('customer.dashboard')" 
                              class="text-brand-blue font-black text-[10px] uppercase tracking-[0.2em] hover:text-brand-dark transition-colors">
                            Minhas Fotos
                        </Link>
                        <button v-else @click="showCustomerModal = true"
                              class="text-brand-blue font-black text-[10px] uppercase tracking-[0.2em] hover:text-brand-dark transition-colors">
                            Minhas Fotos
                        </button>

                        <button @click="showPhotographerModal = true; isLoginMode = true;" 
                              class="bg-brand-blue hover:bg-brand-dark text-white px-6 py-2.5 rounded-xl font-black text-[9px] transition-all uppercase tracking-[0.2em] shadow-lg shadow-brand-blue/10 active:scale-95">
                            Área do Fotógrafo
                        </button>

                        <Link v-if="$page.props.auth.customer" :href="route('store.customer.logout')" method="post" as="button"
                              class="text-gray-400 hover:text-red-500 transition-colors p-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
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
            <div v-if="mobileMenuOpen" class="md:hidden bg-white border-b border-gray-100 shadow-2xl p-6 space-y-6">

                <!-- Search Mobile -->
                <div class="pb-2">
                    <form @submit.prevent="submitSearch" class="relative w-full">
                        <input 
                            type="text" 
                            v-model="searchForm.q"
                            placeholder="Buscar evento..."
                            class="w-full bg-gray-50 border-none rounded-2xl py-4 px-5 text-[11px] font-bold text-brand-dark focus:ring-2 focus:ring-brand-blue/20 transition-all placeholder:text-gray-400 uppercase tracking-widest shadow-inner"
                        >
                        <button type="submit" class="absolute right-4 top-1/2 -translate-y-1/2 text-brand-blue">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </button>
                    </form>
                </div>

                <div class="space-y-1">
                    <Link :href="route('store.photographers')" @click="mobileMenuOpen = false" 
                          class="flex items-center gap-4 text-gray-700 font-black text-xs uppercase tracking-widest py-4 px-4 hover:bg-gray-50 rounded-xl transition-all">
                        <svg class="w-5 h-5 text-brand-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Preciso de um fotógrafo
                    </Link>

                    <div class="h-px bg-gray-50 mx-4"></div>

                    <!-- Se é Fotógrafo -->
                    <template v-if="$page.props.auth.user">
                        <!-- Painel Expansível -->
                        <div class="space-y-1">
                            <button @click="mobilePanelExpanded = !mobilePanelExpanded" 
                                    class="w-full flex items-center justify-between text-brand-dark font-black text-xs uppercase tracking-widest py-4 px-4 bg-gray-50 rounded-xl transition-all border border-gray-100">
                                <span class="flex items-center gap-4">
                                    <svg class="w-5 h-5 text-brand-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                                    Meu Painel
                                </span>
                                <svg class="w-4 h-4 transition-transform duration-300" :class="mobilePanelExpanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                            </button>

                            <Transition
                                enter-active-class="transition duration-200 ease-out"
                                enter-from-class="opacity-0 -translate-y-2"
                                enter-to-class="opacity-100 translate-y-0"
                                leave-active-class="transition duration-150 ease-in"
                                leave-from-class="opacity-100 translate-y-0"
                                leave-to-class="opacity-0 -translate-y-2"
                            >
                                <div v-if="mobilePanelExpanded" class="pl-8 space-y-1 bg-gray-50/50 rounded-b-xl pb-2 border-x border-b border-gray-100">
                                    <Link :href="route('dashboard')" @click="mobileMenuOpen = false" class="block text-gray-500 font-bold text-[10px] uppercase tracking-[0.2em] py-3 px-4 hover:text-brand-blue">Meus Eventos</Link>
                                    <Link :href="route('financial.index')" @click="mobileMenuOpen = false" class="block text-gray-500 font-bold text-[10px] uppercase tracking-[0.2em] py-3 px-4 hover:text-brand-blue">Financeiro</Link>
                                    <Link :href="route('photographer.support.index')" @click="mobileMenuOpen = false" class="block text-gray-500 font-bold text-[10px] uppercase tracking-[0.2em] py-3 px-4 hover:text-brand-blue">Suporte</Link>
                                    <Link :href="route('profile.edit')" @click="mobileMenuOpen = false" class="block text-gray-500 font-bold text-[10px] uppercase tracking-[0.2em] py-3 px-4 hover:text-brand-blue">Configurações</Link>

                                    <!-- Admin Mobile Links -->
                                    <template v-if="$page.props.auth.user.is_superadmin">
                                        <div class="h-px bg-gray-200/50 mx-4 my-2"></div>
                                        <p class="text-[8px] font-black text-brand-orange uppercase tracking-widest px-4 mb-1">Administração</p>
                                        <Link :href="route('admin.photographers.index')" @click="mobileMenuOpen = false" class="block text-gray-500 font-bold text-[10px] uppercase tracking-[0.2em] py-3 px-4 hover:text-brand-orange">Gerenciar Fotógrafos</Link>
                                        <Link :href="route('admin.customers.index')" @click="mobileMenuOpen = false" class="block text-gray-500 font-bold text-[10px] uppercase tracking-[0.2em] py-3 px-4 hover:text-brand-orange">Gerenciar Clientes</Link>
                                        <Link :href="route('admin.events.index')" @click="mobileMenuOpen = false" class="block text-gray-500 font-bold text-[10px] uppercase tracking-[0.2em] py-3 px-4 hover:text-brand-orange">Todos Eventos</Link>
                                        <Link :href="route('admin.withdrawals.index')" @click="mobileMenuOpen = false" class="block text-gray-500 font-bold text-[10px] uppercase tracking-[0.2em] py-3 px-4 hover:text-brand-orange">Aprovar Saques</Link>
                                        <Link :href="route('admin.billing.index')" @click="mobileMenuOpen = false" class="block text-gray-500 font-bold text-[10px] uppercase tracking-[0.2em] py-3 px-4 hover:text-brand-orange">Faturamento Global</Link>
                                        <Link :href="route('admin.support.index')" @click="mobileMenuOpen = false" class="block text-gray-500 font-bold text-[10px] uppercase tracking-[0.2em] py-3 px-4 hover:text-brand-orange">Central de Suporte</Link>
                                        <Link :href="route('admin.settings.index')" @click="mobileMenuOpen = false" class="block text-gray-500 font-bold text-[10px] uppercase tracking-[0.2em] py-3 px-4 hover:text-brand-orange">Configurações Globais</Link>
                                    </template>
                                </div>
                            </Transition>
                        </div>
                        
                        <Link :href="route('logout')" method="post" as="button" @click="mobileMenuOpen = false" 
                              class="w-full flex items-center gap-4 text-red-500 font-black text-xs uppercase tracking-widest py-4 px-4 hover:bg-red-50 rounded-xl transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            Sair do Painel
                        </Link>
                    </template>
                    
                    <!-- Visitante ou Cliente -->
                    <template v-else>
                        <Link v-if="$page.props.auth.customer" :href="route('customer.dashboard')" @click="mobileMenuOpen = false"
                              class="flex items-center gap-4 text-brand-blue font-black text-xs uppercase tracking-widest py-4 px-4 hover:bg-blue-50 rounded-xl transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            Minhas Fotos Compradas
                        </Link>
                        <button v-else @click="showCustomerModal = true; mobileMenuOpen = false"
                              class="flex items-center gap-4 text-brand-blue font-black text-xs uppercase tracking-widest py-4 px-4 hover:bg-blue-50 rounded-xl transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            Minhas Fotos
                        </button>

                        <div class="pt-4 space-y-3">
                            <button @click="showPhotographerModal = true; isLoginMode = true; mobileMenuOpen = false" 
                                    class="block w-full bg-brand-dark text-white text-center py-5 rounded-2xl font-black text-[10px] uppercase tracking-[.2em] shadow-xl shadow-brand-dark/10 active:scale-95 transition-all">
                                Área do Fotógrafo
                            </button>
                            <Link v-if="$page.props.auth.customer" :href="route('store.customer.logout')" method="post" as="button" @click="mobileMenuOpen = false" 
                                  class="block w-full text-center text-gray-400 font-bold text-[9px] uppercase tracking-widest py-2">
                                Sair do CPF: {{ $page.props.auth.customer.cpf }}
                            </Link>
                        </div>
                    </template>
                </div>
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

        <!-- Modal de Área do Fotógrafo (Login/Registro) -->
        <Modal :show="showPhotographerModal" @close="showPhotographerModal = false" max-width="md">
            <div class="p-8 relative overflow-hidden">
                <div class="flex justify-center mb-6">
                    <ApplicationLogo class="h-8 w-auto" />
                </div>

                <div class="transition-all duration-500" :class="isLoginMode ? 'translate-x-0' : '-translate-x-full opacity-0 absolute'">
                    <h2 class="text-2xl font-black text-gray-900 text-center mb-2 uppercase tracking-tighter">Entrar</h2>
                    <p class="text-center text-sm text-gray-500 mb-8 font-medium">Acesse seu painel de fotógrafo</p>

                    <form @submit.prevent="submitLogin" class="space-y-5">
                        <div>
                            <InputLabel for="email_login" value="Email" class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-2" />
                            <TextInput id="email_login" v-model="loginForm.email" type="email" class="mt-1 block w-full" required autocomplete="username" />
                            <InputError class="mt-2" :message="loginForm.errors.email" />
                        </div>

                        <div>
                            <InputLabel for="password_login" value="Senha" class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-2" />
                            <TextInput id="password_login" v-model="loginForm.password" type="password" class="mt-1 block w-full" required autocomplete="current-password" />
                            <InputError class="mt-2" :message="loginForm.errors.password" />
                        </div>

                        <div class="flex items-center justify-between">
                            <label class="flex items-center">
                                <Checkbox name="remember" v-model:checked="loginForm.remember" />
                                <span class="ml-2 text-xs font-bold text-gray-500 uppercase tracking-widest">Lembrar de mim</span>
                            </label>
                            <Link v-if="$page.props.canResetPassword" :href="route('password.request')" class="text-[10px] font-black text-brand-orange uppercase tracking-widest">Esqueceu?</Link>
                        </div>

                        <div class="pt-4">
                            <PrimaryButton class="w-full justify-center py-5 bg-brand-dark hover:bg-brand-blue rounded-2xl shadow-xl" :disabled="loginForm.processing">Entrar</PrimaryButton>
                        </div>
                    </form>

                    <div class="mt-8 text-center">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Não tem conta?</p>
                        <button @click="isLoginMode = false" class="mt-2 text-[11px] font-black text-brand-blue uppercase tracking-widest hover:text-brand-orange transition-colors">Criar conta de fotógrafo</button>
                    </div>
                </div>

                <div class="transition-all duration-500" :class="!isLoginMode ? 'translate-x-0' : 'translate-x-full opacity-0 absolute'">
                    <h2 class="text-2xl font-black text-gray-900 text-center mb-2 uppercase tracking-tighter">Cadastrar</h2>
                    <p class="text-center text-sm text-gray-500 mb-8 font-medium">Faça parte da nossa rede de fotógrafos</p>

                    <form @submit.prevent="submitRegister" class="space-y-4">
                        <div>
                            <InputLabel for="name_reg" value="Nome Completo" class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-2" />
                            <TextInput id="name_reg" v-model="registerForm.name" type="text" class="mt-1 block w-full" required />
                            <InputError class="mt-2" :message="registerForm.errors.name" />
                        </div>

                        <div>
                            <InputLabel for="email_reg" value="Email" class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-2" />
                            <TextInput id="email_reg" v-model="registerForm.email" type="email" class="mt-1 block w-full" required />
                            <InputError class="mt-2" :message="registerForm.errors.email" />
                        </div>

                        <div>
                            <InputLabel for="password_reg" value="Senha" class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-2" />
                            <TextInput id="password_reg" v-model="registerForm.password" type="password" class="mt-1 block w-full" required />
                            <InputError class="mt-2" :message="registerForm.errors.password" />
                        </div>

                        <div>
                            <InputLabel for="password_conf" value="Confirmar Senha" class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-2" />
                            <TextInput id="password_conf" v-model="registerForm.password_confirmation" type="password" class="mt-1 block w-full" required />
                            <InputError class="mt-2" :message="registerForm.errors.password_confirmation" />
                        </div>

                        <div class="pt-4">
                            <PrimaryButton class="w-full justify-center py-5 bg-brand-blue hover:bg-brand-orange rounded-2xl shadow-xl" :disabled="registerForm.processing">Criar Conta</PrimaryButton>
                        </div>
                    </form>

                    <div class="mt-8 text-center">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Já é fotógrafo?</p>
                        <button @click="isLoginMode = true" class="mt-2 text-[11px] font-black text-brand-dark uppercase tracking-widest hover:text-brand-blue transition-colors">Acessar minha conta</button>
                    </div>
                </div>
            </div>
        </Modal>
    </nav>
</template>