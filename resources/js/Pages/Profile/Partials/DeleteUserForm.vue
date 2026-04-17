<script setup>
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    nextTick(() => passwordInput.value.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;

    form.reset();
};
</script>

<template>
    <section>
        <header class="mb-8">
            <h2 class="text-2xl font-black text-red-600 uppercase tracking-tighter">Zona de <span class="text-red-400">Perigo</span></h2>
            <p class="mt-2 text-sm text-red-800/60 font-medium">
                A exclusão da conta é permanente e todos os seus dados serão perdidos.
            </p>
        </header>

        <button @click="confirmUserDeletion" class="px-10 py-4 bg-red-600 hover:bg-red-700 text-white font-black text-xs uppercase tracking-widest rounded-2xl transition-all active:scale-95 shadow-xl shadow-red-600/20">
            Excluir Minha Conta
        </button>

        <Modal :show="confirmingUserDeletion" @close="closeModal">
            <div class="p-10 text-brand-dark">
                <h2 class="text-2xl font-black uppercase tracking-tighter mb-4">
                    Confirmar <span class="text-red-600">Exclusão</span>
                </h2>

                <p class="text-sm text-gray-500 font-medium leading-relaxed mb-8">
                    Para sua segurança, digite sua senha para confirmar que deseja apagar permanentemente todos os seus dados, eventos e fotos.
                </p>

                <div>
                    <input
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="w-full px-6 py-4 bg-gray-50 border border-gray-200 rounded-2xl text-sm font-bold focus:border-red-500 focus:ring-4 focus:ring-red-500/10 transition-all"
                        placeholder="Sua senha atual"
                        @keyup.enter="deleteUser"
                    />

                    <InputError :message="form.errors.password" class="mt-2" />
                </div>

                <div class="mt-10 flex flex-col sm:flex-row gap-4">
                    <button @click="closeModal" class="flex-grow py-4 bg-gray-100 hover:bg-gray-200 text-gray-500 font-black text-[10px] uppercase tracking-widest rounded-2xl transition-all">
                        Cancelar
                    </button>

                    <button
                        :disabled="form.processing"
                        @click="deleteUser"
                        class="flex-grow py-4 bg-red-600 hover:bg-red-700 text-white font-black text-[10px] uppercase tracking-widest rounded-2xl shadow-xl shadow-red-600/20 transition-all active:scale-95 disabled:opacity-50"
                    >
                        Confirmar Exclusão
                    </button>
                </div>
            </div>
        </Modal>
    </section>
</template>
