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
                <!-- Logo Section -->
                <div class="flex items-center">
                    <Link :href="route('store.index')" class="flex items-center gap-2">
                        <ApplicationLogo class="h-20 w-auto" />
                    </Link>
                </div>

                <!-- Navigation Links (Desktop) -->
                <div class="hidden md:flex items-center gap-8">
                    <!-- Links para Usuário Comum (Cliente) -->
                    <template v-if="!$page.props.auth.user">
                        <Link v-if="$page.props.auth.customer" :href="route('customer.dashboard')" 
                              class="text-brand-blue hover:text-brand-orange font-bold text-sm transition-colors uppercase tracking-widest">
                            Minhas Fotos
                        </Link>
                        <button v-else @click="showCustomerModal = true" 
                                class="text-gray-600 hover:text-brand-orange font-bold text-sm transition-colors uppercase tracking-widest">
                            Minhas Fotos
                        </button>
                        
                        <Link :href="route('login')" 
                              class="text-gray-600 hover:text-brand-orange font-bold text-sm transition-colors uppercase tracking-widest">
                            Entrar
                        </Link>
                        
                        <Link :href="route('register')" 
                              class="bg-brand-blue hover:bg-brand-blue-hover text-white px-6 py-2.5 rounded-full font-bold text-sm transition-all uppercase tracking-widest shadow-md hover:shadow-lg active:scale-95">
                            Seja um Fotógrafo
                        </Link>
                    </template>

                    <!-- Links para Fotógrafo / Admin Logado -->
                    <template v-else>
                        <!-- Área do Cliente (Compras) -->
                        <div class="flex items-center gap-6 pr-8 border-r border-gray-100">
                            <Link v-if="$page.props.auth.customer" :href="route('customer.dashboard')" 
                                  class="flex items-center gap-2 text-brand-blue hover:text-brand-orange font-black text-[10px] transition-all uppercase tracking-widest">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                Minhas Compras
                            </Link>
                            <button v-else @click="showCustomerModal = true" 
                                    class="flex items-center gap-2 text-gray-400 hover:text-brand-orange font-black text-[10px] transition-all uppercase tracking-widest">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                Acessar Compras
                            </button>
                        </div>

                        <!-- Área de Gestão (Fotógrafo) -->
                        <div class="flex items-center gap-6 pl-2">
                            <Link :href="route('dashboard')" 
                                  :class="route().current('dashboard') ? 'text-brand-orange' : 'text-gray-600'"
                                  class="flex items-center gap-2 hover:text-brand-orange font-black text-[10px] transition-all uppercase tracking-widest">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                                Meus Eventos
                            </Link>
                            <Link :href="route('financial.index')" 
                                  :class="route().current('financial.*') ? 'text-brand-orange' : 'text-gray-600'"
                                  class="flex items-center gap-2 hover:text-brand-orange font-black text-[10px] transition-all uppercase tracking-widest">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Financeiro
                            </Link>
                            <Link v-if="$page.props.auth.user.is_superadmin" :href="route('admin.withdrawals.index')" 
                                  :class="route().current('admin.withdrawals.*') ? 'text-brand-orange' : 'text-gray-600'"
                                  class="flex items-center gap-2 hover:text-brand-orange font-black text-[10px] transition-all uppercase tracking-widest border-l pl-6 border-gray-100">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                Admin
                            </Link>
                        </div>

                        <div class="h-8 w-px bg-gray-100 mx-2"></div>

                        <div class="flex items-center gap-4">
                            <Link :href="route('profile.edit')" class="text-gray-400 hover:text-brand-dark transition-colors" title="Meu Perfil">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </Link>
                            <Link :href="route('logout')" method="post" as="button" class="text-red-400 hover:text-red-600 transition-colors" title="Sair">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            </Link>
                        </div>
                    </template>
                </div>

                <!-- Mobile Navigation Button -->
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

        <!-- Mobile Menu Drawer -->
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0 -translate-y-4"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 -translate-y-4"
        >
            <div v-if="mobileMenuOpen" class="md:hidden bg-white border-b border-gray-100 shadow-lg overflow-hidden">
                <div class="px-4 pt-2 pb-6 space-y-2">
                    <!-- Usuário não logado (como fotógrafo) -->
                    <template v-if="!$page.props.auth.user">
                        <Link v-if="$page.props.auth.customer" :href="route('customer.dashboard')" 
                              @click="mobileMenuOpen = false"
                              class="flex items-center gap-3 px-4 py-4 text-brand-blue font-black text-sm uppercase tracking-widest hover:bg-gray-50 rounded-2xl transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                            Minhas Fotos
                        </Link>
                        <button v-else @click="showCustomerModal = true; mobileMenuOpen = false" 
                                class="w-full text-left flex items-center gap-3 px-4 py-4 text-gray-600 font-black text-sm uppercase tracking-widest hover:bg-gray-50 rounded-2xl transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                            Minhas Fotos
                        </button>
                        <div class="grid grid-cols-2 gap-2 pt-2">
                            <Link :href="route('login')" 
                                  @click="mobileMenuOpen = false"
                                  class="text-center border border-brand-blue text-brand-blue px-6 py-4 rounded-2xl font-black text-sm transition-all uppercase tracking-widest">
                                Entrar
                            </Link>
                            <Link :href="route('register')" 
                                  @click="mobileMenuOpen = false"
                                  class="text-center bg-brand-blue text-white px-6 py-4 rounded-2xl font-black text-sm transition-all uppercase tracking-widest shadow-md">
                                Cadastrar
                            </Link>
                        </div>
                    </template>

                    <!-- Fotógrafo logado -->
                    <template v-else>
                        <div class="bg-gray-50/50 rounded-3xl p-2 mb-4">
                            <div class="px-4 py-3 text-[10px] font-black text-gray-400 uppercase tracking-widest">Área do Cliente</div>
                            <Link v-if="$page.props.auth.customer" :href="route('customer.dashboard')" 
                                  @click="mobileMenuOpen = false"
                                  class="flex items-center gap-3 px-4 py-4 text-brand-blue font-black text-sm uppercase tracking-widest hover:bg-white rounded-2xl transition-all shadow-sm">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                Minhas Compras
                            </Link>
                            <button v-else @click="showCustomerModal = true; mobileMenuOpen = false" 
                                    class="w-full text-left flex items-center gap-3 px-4 py-4 text-gray-400 font-black text-sm uppercase tracking-widest hover:bg-white rounded-2xl transition-all shadow-sm">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                Acessar Compras
                            </button>
                        </div>

                        <div class="px-4 py-3 text-[10px] font-black text-brand-orange uppercase tracking-widest">Área de Gestão</div>
                        <Link :href="route('dashboard')" @click="mobileMenuOpen = false"
                              class="flex items-center gap-3 px-4 py-4 text-gray-600 font-black text-sm uppercase tracking-widest hover:bg-gray-50 rounded-2xl transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                            Meus Eventos
                        </Link>
                        <Link :href="route('financial.index')" @click="mobileMenuOpen = false"
                              class="flex items-center gap-3 px-4 py-4 text-gray-600 font-black text-sm uppercase tracking-widest hover:bg-gray-50 rounded-2xl transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Financeiro
                        </Link>
                        <Link v-if="$page.props.auth.user.is_superadmin" :href="route('admin.withdrawals.index')" @click="mobileMenuOpen = false"
                              class="flex items-center gap-3 px-4 py-4 text-brand-orange font-black text-sm uppercase tracking-widest hover:bg-gray-50 rounded-2xl transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            Super Admin
                        </Link>
                        
                        <div class="h-px bg-gray-100 my-4"></div>
                        
                        <div class="flex items-center justify-between px-4 pb-4">
                            <Link :href="route('profile.edit')" @click="mobileMenuOpen = false" class="flex items-center gap-2 text-gray-400 font-black text-[10px] uppercase tracking-widest">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                Perfil
                            </Link>
                            <Link :href="route('logout')" method="post" as="button" @click="mobileMenuOpen = false" class="flex items-center gap-2 text-red-400 font-black text-[10px] uppercase tracking-widest">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                Sair
                            </Link>
                        </div>
                    </template>
                </div>
            </div>
        </Transition>

        <!-- Customer Login Modal -->
        <Modal :show="showCustomerModal" @close="showCustomerModal = false">
            <div class="p-8">
                <div class="flex justify-center mb-6">
                    <ApplicationLogo class="h-8 w-auto" />
                </div>
                
                <h2 class="text-2xl font-bold text-gray-900 text-center mb-2">
                    {{ needsRegistration ? 'Complete seu cadastro' : 'Bem-vindo de volta' }}
                </h2>

                <p class="text-center text-sm text-gray-500 mb-8">
                    {{ needsRegistration ? 'Precisamos de mais algumas informações.' : 'Informe seu CPF para acessar suas fotos.' }}
                </p>

                <form @submit.prevent="submitCustomerAuth" class="space-y-6">
                    <div>
                        <InputLabel for="cpf" value="CPF" />
                        <TextInput
                            id="cpf"
                            v-model="customerForm.cpf"
                            type="text"
                            class="mt-1 block w-full border-gray-200 focus:border-brand-orange focus:ring-brand-orange"
                            placeholder="000.000.000-00"
                            :disabled="needsRegistration"
                            required
                        />
                        <div v-if="customerForm.errors.cpf" class="text-red-500 text-xs mt-1">{{ customerForm.errors.cpf }}</div>
                    </div>

                    <div v-if="needsRegistration" class="space-y-6 animate-fade-in">
                        <div>
                            <InputLabel for="name" value="Nome Completo" />
                            <TextInput
                                id="name"
                                v-model="customerForm.name"
                                type="text"
                                class="mt-1 block w-full border-gray-200 focus:border-brand-orange focus:ring-brand-orange"
                                placeholder="Seu nome"
                                required
                            />
                            <div v-if="customerForm.errors.name" class="text-red-500 text-xs mt-1">{{ customerForm.errors.name }}</div>
                        </div>

                        <div>
                            <InputLabel for="phone" value="Telefone / WhatsApp" />
                            <TextInput
                                id="phone"
                                v-model="customerForm.phone"
                                type="text"
                                class="mt-1 block w-full border-gray-200 focus:border-brand-orange focus:ring-brand-orange"
                                placeholder="(00) 00000-0000"
                                required
                            />
                            <div v-if="customerForm.errors.phone" class="text-red-500 text-xs mt-1">{{ customerForm.errors.phone }}</div>
                        </div>
                    </div>

                    <div class="mt-8">
                        <PrimaryButton class="w-full justify-center py-4 bg-brand-orange hover:bg-brand-orange-hover" :disabled="customerForm.processing">
                            {{ needsRegistration ? 'Finalizar Cadastro' : 'Acessar Fotos' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </nav>
</template>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.3s ease-out;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
