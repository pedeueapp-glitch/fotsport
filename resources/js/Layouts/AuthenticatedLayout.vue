<script setup>
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
</script>

<template>
    <div>
        <div class="min-h-screen bg-gray-50/50">
            <nav class="bg-white border-b border-gray-100 sticky top-0 z-50">
                <!-- Primary Navigation Menu -->
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-20">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="shrink-0 flex items-center">
                                <Link :href="route('dashboard')" class="flex items-center">
                                    <ApplicationLogo class="h-9 w-auto" />
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                                <NavLink :href="route('dashboard')" :active="route().current('dashboard')" 
                                         class="text-xs font-black uppercase tracking-[0.2em]">
                                    Painel
                                </NavLink>
                                <NavLink :href="route('financial.index')" :active="route().current('financial.*')" 
                                         class="text-xs font-black uppercase tracking-[0.2em]">
                                    Meu Financeiro
                                </NavLink>
                                <NavLink v-if="$page.props.auth.user.is_superadmin" :href="route('admin.withdrawals.index')" :active="route().current('admin.withdrawals.*')" 
                                         class="text-xs font-black uppercase tracking-[0.2em] text-brand-orange">
                                    Admin
                                </NavLink>
                            </div>
                        </div>

                        <div class="hidden sm:flex sm:items-center sm:ml-6 gap-4">
                            <!-- Link para a Loja -->
                            <Link :href="route('store.index')" class="text-[10px] font-black uppercase tracking-widest text-gray-400 hover:text-brand-orange transition flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                Ver Vitrine
                            </Link>

                            <div class="h-8 w-px bg-gray-100"></div>

                            <!-- Settings Dropdown -->
                            <div class="ml-3 relative">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="inline-flex items-center px-4 py-2 border border-transparent text-xs font-black uppercase tracking-widest rounded-xl text-gray-600 bg-gray-50 hover:text-brand-orange focus:outline-none transition ease-in-out duration-150"
                                            >
                                                {{ $page.props.auth.user.name }}

                                                <svg
                                                    class="ml-2 -mr-0.5 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <DropdownLink :href="route('profile.edit')"> Meu Perfil </DropdownLink>
                                        <div class="border-t border-gray-100"></div>
                                        <DropdownLink :href="route('logout')" method="post" as="button" class="text-red-500 font-bold">
                                            Sair da Conta
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-mr-2 flex items-center sm:hidden">
                            <button
                                @click="showingNavigationDropdown = !showingNavigationDropdown"
                                class="inline-flex items-center justify-center p-3 rounded-2xl text-gray-400 hover:text-brand-blue hover:bg-gray-100 focus:outline-none transition duration-150 ease-in-out"
                            >
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path
                                        :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex': !showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2.5"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex': showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2.5"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div
                    :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }"
                    class="sm:hidden bg-white border-t border-gray-100 overflow-hidden transition-all"
                >
                    <div class="pt-2 pb-3 space-y-1">
                        <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')" class="font-bold">
                            Painel
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('financial.index')" :active="route().current('financial.*')" class="font-bold">
                            Meu Financeiro
                        </ResponsiveNavLink>
                        <ResponsiveNavLink v-if="$page.props.auth.user.is_superadmin" :href="route('admin.withdrawals.index')" :active="route().current('admin.withdrawals.*')" class="text-brand-orange font-bold">
                            Administração
                        </ResponsiveNavLink>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div class="pt-4 pb-1 border-t border-gray-200 bg-gray-50">
                        <div class="px-4">
                            <div class="font-black text-xs uppercase tracking-widest text-brand-dark">
                                {{ $page.props.auth.user.name }}
                            </div>
                            <div class="font-medium text-[10px] text-gray-400 uppercase tracking-widest">{{ $page.props.auth.user.email }}</div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')" class="font-bold"> Perfil </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('logout')" method="post" as="button" class="text-red-500 font-bold">
                                Sair
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header class="bg-white/80 backdrop-blur-md border-b border-gray-100" v-if="$slots.header">
                <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 flex items-center gap-4">
                    <div class="h-8 w-1.5 bg-brand-orange rounded-full"></div>
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main class="py-12">
                <slot />
            </main>
        </div>
    </div>
</template>
