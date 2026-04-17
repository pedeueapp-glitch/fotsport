<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps(['href', 'active']);

const classes = computed(() =>
    props.active
        ? 'block w-full pl-3 pr-4 py-3 border-l-4 border-brand-orange text-left text-base font-black uppercase tracking-widest text-brand-orange bg-brand-orange/5 focus:outline-none transition duration-150 ease-in-out'
        : 'block w-full pl-3 pr-4 py-3 border-l-4 border-transparent text-left text-base font-bold uppercase tracking-widest text-gray-500 hover:text-brand-dark hover:bg-gray-50 hover:border-gray-100 focus:outline-none transition duration-150 ease-in-out'
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
