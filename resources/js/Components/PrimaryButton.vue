<template>
    <button
        @click="$emit('click')"
        class="inline-flex items-center px-4 py-2 bg-brand-orange border border-transparent rounded-md font-bold text-xs text-white uppercase tracking-widest hover:bg-brand-orange-hover focus:bg-brand-orange-hover active:bg-brand-orange-hover focus:outline-none focus:ring-2 focus:ring-brand-orange focus:ring-offset-2 transition ease-in-out duration-150"
    >
        <slot />
    </button>
</template>
