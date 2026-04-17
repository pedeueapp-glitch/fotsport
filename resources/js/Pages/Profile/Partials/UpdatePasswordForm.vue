<script setup>
import InputError from '@/Components/InputError.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value.focus();
            }
        },
    });
};
</script>

<template>
    <section>
        <header class="mb-10">
            <h2 class="text-2xl font-black text-brand-dark uppercase tracking-tighter">Segurança <span class="text-brand-orange">da Conta</span></h2>
            <p class="mt-2 text-sm text-gray-400 font-medium">
                Mantenha sua senha atualizada para garantir a segurança dos seus dados.
            </p>
        </header>

        <form @submit.prevent="updatePassword" class="space-y-6 max-w-xl">
            <div>
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Senha Atual</label>
                <input
                    id="current_password"
                    ref="currentPasswordInput"
                    v-model="form.current_password"
                    type="password"
                    class="w-full px-6 py-4 bg-white border border-gray-200 rounded-2xl text-sm font-bold focus:border-brand-blue focus:ring-4 focus:ring-brand-blue/10 transition-all"
                    autocomplete="current-password"
                />
                <InputError :message="form.errors.current_password" class="mt-2" />
            </div>

            <div>
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Nova Senha</label>
                <input
                    id="password"
                    ref="passwordInput"
                    v-model="form.password"
                    type="password"
                    class="w-full px-6 py-4 bg-white border border-gray-200 rounded-2xl text-sm font-bold focus:border-brand-blue focus:ring-4 focus:ring-brand-blue/10 transition-all"
                    autocomplete="new-password"
                />
                <InputError :message="form.errors.password" class="mt-2" />
            </div>

            <div>
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Confirmar Nova Senha</label>
                <input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    class="w-full px-6 py-4 bg-white border border-gray-200 rounded-2xl text-sm font-bold focus:border-brand-blue focus:ring-4 focus:ring-brand-blue/10 transition-all"
                    autocomplete="new-password"
                />
                <InputError :message="form.errors.password_confirmation" class="mt-2" />
            </div>

            <div class="flex items-center gap-6 pt-4">
                <button :disabled="form.processing" class="px-12 py-5 bg-brand-dark hover:bg-brand-blue text-white font-black text-xs uppercase tracking-[0.2em] rounded-2xl shadow-xl shadow-brand-dark/10 transition-all active:scale-95 disabled:opacity-50">
                    Alterar Senha
                </button>

                <Transition enter-from-class="opacity-0" leave-to-class="opacity-0" class="transition duration-500">
                    <p v-if="form.recentlySuccessful" class="text-[10px] font-black text-green-500 uppercase tracking-widest">✓ Senha atualizada</p>
                </Transition>
            </div>
        </form>
    </section>
</template>
