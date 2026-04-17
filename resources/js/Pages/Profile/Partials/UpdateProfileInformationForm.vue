<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    mustVerifyEmail: Boolean,
    status: String,
});

const user = usePage().props.auth.user;

const form = useForm({
    _method: 'patch',
    name: user.name,
    slug: user.slug || '',
    email: user.email,
    avatar: null,
    cover_photo: null,
    whatsapp: user.whatsapp || '',
    instagram: user.instagram || '',
    facebook: user.facebook || '',
    bio: user.bio || '',
});

const avatarPreview = ref(user.avatar ? `/${user.avatar}` : null);
const coverPreview = ref(user.cover_photo ? `/${user.cover_photo}` : null);

const handleAvatarChange = (e) => {
    const file = e.target.files[0];
    form.avatar = file;
    if (file) {
        avatarPreview.value = URL.createObjectURL(file);
    }
};

const handleCoverChange = (e) => {
    const file = e.target.files[0];
    form.cover_photo = file;
    if (file) {
        coverPreview.value = URL.createObjectURL(file);
    }
};
</script>

<template>
    <section>
        <header class="mb-10">
            <h2 class="text-2xl font-black text-brand-dark uppercase tracking-tighter">Informações <span class="text-brand-orange">Pessoais</span></h2>
            <p class="mt-2 text-sm text-gray-400 font-medium">
                Atualize sua identidade visual e redes sociais na plataforma.
            </p>
        </header>

        <form @submit.prevent="form.post(route('profile.update'))" class="space-y-8 max-w-4xl">
            <!-- Fotos -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                <!-- Cover Photo -->
                <div class="space-y-4">
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Foto de Capa</label>
                    <div class="relative group">
                        <div class="h-40 w-full bg-white rounded-3xl overflow-hidden shadow-sm border border-gray-200">
                            <img v-if="coverPreview" :src="coverPreview" class="object-cover w-full h-full group-hover:scale-105 transition duration-500" />
                            <div v-else class="h-full flex items-center justify-center text-gray-300">
                                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                        </div>
                        <label for="cover_photo" class="absolute bottom-4 right-4 cursor-pointer w-12 h-12 bg-brand-blue text-white rounded-2xl flex items-center justify-center shadow-xl hover:bg-brand-orange transition-all active:scale-95">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            <input type="file" id="cover_photo" @change="handleCoverChange" accept="image/*" class="hidden" />
                        </label>
                    </div>
                    <InputError class="mt-2" :message="form.errors.cover_photo" />
                </div>

                <!-- Avatar -->
                <div class="space-y-4">
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Foto de Perfil</label>
                    <div class="flex items-center gap-8">
                        <div class="h-40 w-40 bg-white rounded-[2rem] overflow-hidden shadow-sm border border-gray-200 relative group shrink-0">
                            <img v-if="avatarPreview" :src="avatarPreview" class="object-cover w-full h-full group-hover:scale-110 transition duration-500" />
                            <div v-else class="h-full flex items-center justify-center text-gray-300">
                                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            </div>
                            <label for="avatar" class="absolute inset-0 bg-brand-dark/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center cursor-pointer backdrop-blur-sm">
                                <span class="text-white font-black text-[10px] uppercase tracking-widest">Alterar</span>
                                <input type="file" id="avatar" @change="handleAvatarChange" accept="image/*" class="hidden" />
                            </label>
                        </div>
                        <div class="text-xs text-gray-400 leading-relaxed font-medium">
                            Use uma foto clara do seu rosto. Isso ajuda a construir confiança com os clientes.
                        </div>
                    </div>
                    <InputError class="mt-2" :message="form.errors.avatar" />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 pt-8 border-t border-gray-200/50">
                <!-- Nome -->
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Seu Nome</label>
                    <input
                        id="name"
                        type="text"
                        class="w-full px-6 py-4 bg-white border border-gray-200 rounded-2xl text-sm font-bold focus:border-brand-blue focus:ring-4 focus:ring-brand-blue/10 transition-all"
                        v-model="form.name"
                        required
                        placeholder="Nome completo"
                    />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <!-- Slug -->
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">URL do Portfólio</label>
                    <div class="flex">
                        <span class="inline-flex items-center px-6 bg-white border border-r-0 border-gray-200 rounded-l-2xl text-gray-400 text-xs font-black uppercase tracking-widest">/fotografo/</span>
                        <input
                            id="slug"
                            type="text"
                            class="w-full px-6 py-4 bg-white border border-gray-200 rounded-r-2xl text-sm font-bold focus:border-brand-blue focus:ring-4 focus:ring-brand-blue/10 transition-all"
                            v-model="form.slug"
                            placeholder="seu-nome"
                        />
                    </div>
                    <InputError class="mt-2" :message="form.errors.slug" />
                </div>

                <!-- E-mail -->
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">E-mail de Contato</label>
                    <input
                        id="email"
                        type="email"
                        class="w-full px-6 py-4 bg-white border border-gray-200 rounded-2xl text-sm font-bold focus:border-brand-blue focus:ring-4 focus:ring-brand-blue/10 transition-all"
                        v-model="form.email"
                        required
                    />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <!-- WhatsApp -->
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">WhatsApp</label>
                    <input
                        id="whatsapp"
                        type="text"
                        class="w-full px-6 py-4 bg-white border border-gray-200 rounded-2xl text-sm font-bold focus:border-brand-blue focus:ring-4 focus:ring-brand-blue/10 transition-all"
                        v-model="form.whatsapp"
                        placeholder="(00) 00000-0000"
                    />
                    <InputError class="mt-2" :message="form.errors.whatsapp" />
                </div>
            </div>

            <!-- Redes Sociais -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Instagram</label>
                    <input
                        id="instagram"
                        type="text"
                        class="w-full px-6 py-4 bg-white border border-gray-200 rounded-2xl text-sm font-bold focus:border-brand-blue focus:ring-4 focus:ring-brand-blue/10 transition-all"
                        v-model="form.instagram"
                        placeholder="@seu-perfil"
                    />
                </div>
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Facebook</label>
                    <input
                        id="facebook"
                        type="text"
                        class="w-full px-6 py-4 bg-white border border-gray-200 rounded-2xl text-sm font-bold focus:border-brand-blue focus:ring-4 focus:ring-brand-blue/10 transition-all"
                        v-model="form.facebook"
                        placeholder="Link do perfil"
                    />
                </div>
            </div>

            <!-- Biografia -->
            <div>
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Sobre Você (Biografia)</label>
                <textarea
                    id="bio"
                    rows="5"
                    class="w-full px-6 py-4 bg-white border border-gray-200 rounded-3xl text-sm font-medium focus:border-brand-blue focus:ring-4 focus:ring-brand-blue/10 transition-all"
                    v-model="form.bio"
                    placeholder="Conte sua trajetória..."
                ></textarea>
                <InputError class="mt-2" :message="form.errors.bio" />
            </div>

            <div v-if="props.mustVerifyEmail && user.email_verified_at === null">
                <p class="text-sm mt-2 text-red-500 font-bold uppercase tracking-tight">
                    E-mail não verificado.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="underline text-brand-blue hover:text-brand-orange ml-2"
                    >
                        Reenviar link.
                    </Link>
                </p>
                <div v-show="props.status === 'verification-link-sent'" class="mt-2 font-bold text-xs text-green-600 uppercase tracking-widest">
                    Link enviado!
                </div>
            </div>

            <!-- Submit -->
            <div class="flex items-center gap-6 pt-6">
                <button :disabled="form.processing" class="px-12 py-5 bg-brand-blue hover:bg-brand-blue-hover text-white font-black text-xs uppercase tracking-[0.2em] rounded-2xl shadow-xl shadow-brand-blue/20 transition-all active:scale-95 disabled:opacity-50">
                    Salvar Perfil
                </button>

                <Transition enter-from-class="opacity-0" leave-to-class="opacity-0" class="transition duration-500">
                    <p v-if="form.recentlySuccessful" class="text-[10px] font-black text-green-500 uppercase tracking-widest">✓ Alterações salvas com sucesso</p>
                </Transition>
            </div>
        </form>
    </section>
</template>
