<script setup>
import { ref } from 'vue';

const props = defineProps({
    brands: {
        type: Array,
        default: () => []
    }
});

const brandScroll = ref(null);

const scrollBrands = (direction) => {
    if (!brandScroll.value) return;
    const scrollAmount = 400;
    brandScroll.value.scrollBy({
        left: direction === 'right' ? scrollAmount : -scrollAmount,
        behavior: 'smooth'
    });
};
</script>

<template>
    <div class="w-full bg-white border-y border-gray-100 py-4 relative group/brand-carousel">
        <div class="max-w-7xl mx-auto px-4 mb-2">
            <p class="text-[8px] font-black text-gray-400 uppercase tracking-[0.3em] text-center md:text-left">Nossos Parceiros e Marcas</p>
        </div>
        
        <div class="max-w-7xl mx-auto px-4 relative">
            <!-- Navigation Arrows -->
            <button @click="scrollBrands('left')" 
                    class="absolute left-0 top-1/2 -translate-y-1/2 z-20 w-8 h-8 bg-white shadow-xl rounded-full flex items-center justify-center text-brand-dark opacity-0 group-hover/brand-carousel:opacity-100 transition-opacity hover:bg-brand-blue hover:text-white border border-gray-100 -ml-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
            </button>

            <button @click="scrollBrands('right')" 
                    class="absolute right-0 top-1/2 -translate-y-1/2 z-20 w-8 h-8 bg-white shadow-xl rounded-full flex items-center justify-center text-brand-dark opacity-0 group-hover/brand-carousel:opacity-100 transition-opacity hover:bg-brand-blue hover:text-white border border-gray-100 -mr-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
            </button>

            <div ref="brandScroll" 
                 class="flex items-center justify-center gap-16 overflow-x-auto scrollbar-hide scroll-smooth snap-x">
                <div v-for="brand in brands" :key="brand.id" class="flex-shrink-0 grayscale opacity-40 hover:grayscale-0 hover:opacity-100 transition-all cursor-pointer snap-center">
                    <img :src="brand.logo_path" :alt="brand.name" class="h-8 md:h-10 w-auto object-contain" />
                </div>
                
                <!-- Fallback if no brands -->
                <template v-if="brands.length === 0">
                    <div v-for="n in 8" :key="n" class="h-10 w-28 bg-gray-50 rounded-lg flex-shrink-0 flex items-center justify-center text-[10px] font-bold text-gray-200 uppercase tracking-widest border border-gray-100 border-dashed snap-center">
                        Marca {{ n }}
                    </div>
                </template>
            </div>
        </div>
    </div>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
