<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps(['href', 'active']);

const classes = computed(() =>
    props.active
        ? 'inline-flex items-center px-1 pt-1 border-b-2 border-brand-orange text-sm font-black uppercase tracking-widest leading-5 text-brand-dark focus:outline-none transition duration-150 ease-in-out'
        : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-bold uppercase tracking-widest leading-5 text-gray-400 hover:text-brand-orange hover:border-gray-200 focus:outline-none transition duration-150 ease-in-out'
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
