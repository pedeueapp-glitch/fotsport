<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AdminSidebarLayout from '@/Layouts/AdminSidebarLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    customer: Object
});

const form = useForm({
    name: props.customer.name,
    cpf: props.customer.cpf,
    phone: props.customer.phone,
    is_active: props.customer.is_active,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.patch(route('admin.customers.update', props.customer.id));
};
</script>

<template>
    <Head :title="`Editar Cliente: ${customer.name}`" />

    <AdminSidebarLayout :title="`Editar Cliente`" :subtitle="customer.name">
        <div class="max-w-2xl">
            <form @submit.prevent="submit" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="col-span-2">
                        <InputLabel for="name" value="Nome Completo" class="text-[9px] font-black uppercase tracking-widest text-gray-400 mb-2" />
                        <TextInput id="name" type="text" class="mt-1 block w-full border-gray-100 bg-gray-50 rounded-xl focus:ring-brand-blue" v-model="form.name" required />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <div>
                        <InputLabel for="cpf" value="CPF" class="text-[9px] font-black uppercase tracking-widest text-gray-400 mb-2" />
                        <TextInput id="cpf" type="text" class="mt-1 block w-full border-gray-100 bg-gray-50 rounded-xl focus:ring-brand-blue" v-model="form.cpf" required />
                        <InputError class="mt-2" :message="form.errors.cpf" />
                    </div>

                    <div>
                        <InputLabel for="phone" value="Telefone / WhatsApp" class="text-[9px] font-black uppercase tracking-widest text-gray-400 mb-2" />
                        <TextInput id="phone" type="text" class="mt-1 block w-full border-gray-100 bg-gray-50 rounded-xl focus:ring-brand-blue" v-model="form.phone" required />
                        <InputError class="mt-2" :message="form.errors.phone" />
                    </div>

                    <div class="border-t border-gray-50 pt-4 col-span-2">
                        <h3 class="text-[10px] font-black text-brand-dark uppercase tracking-widest mb-4">Redefinir Senha (opcional)</h3>
                    </div>

                    <div>
                        <InputLabel for="password" value="Nova Senha" class="text-[9px] font-black uppercase tracking-widest text-gray-400 mb-2" />
                        <TextInput id="password" type="password" class="mt-1 block w-full border-gray-100 bg-gray-50 rounded-xl focus:ring-brand-blue" v-model="form.password" autocomplete="new-password" />
                        <InputError class="mt-2" :message="form.errors.password" />
                    </div>

                    <div>
                        <InputLabel for="password_confirmation" value="Confirmar Nova Senha" class="text-[9px] font-black uppercase tracking-widest text-gray-400 mb-2" />
                        <TextInput id="password_confirmation" type="password" class="mt-1 block w-full border-gray-100 bg-gray-50 rounded-xl focus:ring-brand-blue" v-model="form.password_confirmation" />
                    </div>

                    <div class="col-span-2">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" v-model="form.is_active" class="w-5 h-5 rounded-lg border-gray-200 text-brand-blue focus:ring-brand-blue">
                            <div>
                                <span class="text-xs font-black text-brand-dark uppercase tracking-tight group-hover:text-brand-blue transition-colors">Conta Ativa</span>
                                <p class="text-[10px] text-gray-400 font-medium">Define se o cliente pode realizar login e visualizar suas fotos.</p>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="flex items-center gap-4 pt-6 border-t border-gray-50">
                    <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing" class="bg-brand-blue hover:bg-brand-blue/90 px-8 py-4 rounded-xl">
                        Salvar Alterações
                    </PrimaryButton>
                    <Link :href="route('admin.customers.index')" class="text-[10px] font-black text-gray-400 uppercase tracking-widest hover:text-brand-dark transition-colors">
                        Cancelar
                    </Link>
                </div>
            </form>
        </div>
    </AdminSidebarLayout>
</template>
