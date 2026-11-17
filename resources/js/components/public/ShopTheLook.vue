<template>
  <section class="py-20 lg:py-28 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center max-w-2xl mx-auto mb-14 space-y-3">
      <span class="text-xs uppercase tracking-[0.25em] text-[#A89B8A] font-medium">Curated Outfit</span>
      <h2 class="font-serif text-3xl sm:text-4xl text-[#111111] tracking-tight">
        {{ content.heading || 'Shop The Look' }}
      </h2>
      <p class="text-xs sm:text-sm text-[#A89B8A] leading-relaxed">
        {{ content.description || 'Hover or tap each hotspot to inspect tailored garments and add directly to your bag.' }}
      </p>
    </div>

    <!-- Interactive Lookbook Canvas -->
    <div class="relative max-w-4xl mx-auto aspect-4/5 sm:aspect-16/10 overflow-hidden bg-[#EAE5DC] shadow-2xl">
      <img
        :src="content.image || 'https://images.unsplash.com/photo-1509631179647-0177331693ae?auto=format&fit=crop&w=1400&q=80'"
        alt="Shop The Look Outfit"
        class="w-full h-full object-cover object-center"
      />

      <!-- Interactive Hotspots -->
      <div
        v-for="(spot, index) in hotspots"
        :key="index"
        class="absolute"
        :style="{ top: spot.top, left: spot.left }"
      >
        <!-- Pulsing Pin -->
        <div class="relative group">
          <button
            type="button"
            class="relative w-7 h-7 -translate-x-1/2 -translate-y-1/2 rounded-full bg-white text-[#111111] flex items-center justify-center shadow-lg transition-transform hover:scale-125 focus:outline-none cursor-pointer"
            @click="activeSpot = activeSpot === index ? null : index"
            :aria-label="spot.title"
          >
            <span class="absolute inline-flex h-full w-full rounded-full bg-white opacity-75 animate-ping"></span>
            <span class="relative w-2.5 h-2.5 rounded-full bg-[#111111]"></span>
          </button>

          <!-- Hotspot Tooltip Card -->
          <div
            :class="[
              'absolute z-30 w-56 p-4 bg-white/95 backdrop-blur-md shadow-2xl transition-all duration-300 text-left pointer-events-auto',
              'top-full mt-2 -left-20 sm:-left-24',
              activeSpot === index ? 'opacity-100 scale-100 visible' : 'opacity-0 scale-95 invisible group-hover:opacity-100 group-hover:scale-100 group-hover:visible'
            ]"
          >
            <span class="text-[9px] uppercase tracking-widest text-[#A89B8A] font-semibold block">Curated Piece</span>
            <h5 class="font-serif text-sm text-[#111111] leading-tight mt-1 mb-1">{{ spot.title }}</h5>
            <span class="text-xs font-semibold text-[#111111] block mb-3">{{ spot.price }}</span>
            <router-link
              v-if="spot.product_slug"
              :to="`/product/${spot.product_slug}`"
              class="w-full block text-center py-2 bg-[#111111] hover:bg-black text-white text-[10px] uppercase tracking-widest font-semibold transition-colors"
            >
              Inspect Garment
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  content: {
    type: Object,
    default: () => ({}),
  },
});

const activeSpot = ref(null);

const defaultHotspots = [
  { top: '25%', left: '52%', title: 'Cashmere Roll-Neck Sweater', price: '$380.00', product_slug: 'chunky-cashmere-roll-neck-sweater' },
  { top: '65%', left: '48%', title: 'Pleated Wool Wide-Leg Trousers', price: '$280.00', product_slug: 'pleated-wool-wide-leg-trousers' },
  { top: '40%', left: '35%', title: 'Double-Faced Cashmere Overcoat', price: '$890.00', product_slug: 'double-faced-cashmere-overcoat' },
];

const hotspots = computed(() => {
  return props.content?.hotspots && props.content.hotspots.length ? props.content.hotspots : defaultHotspots;
});
</script>
