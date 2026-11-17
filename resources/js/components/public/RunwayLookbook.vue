<template>
  <section
    ref="runwaySectionRef"
    class="relative bg-[#111111] text-[#F7F5F0] overflow-hidden py-16 lg:py-0 lg:h-screen lg:flex lg:flex-col lg:justify-between"
  >
    <!-- Background Watermark / Ambient Texture -->
    <div class="absolute inset-0 pointer-events-none opacity-5 flex items-center justify-center select-none overflow-hidden">
      <span class="font-serif text-[28vw] uppercase font-bold tracking-tighter whitespace-nowrap text-white">
        DRAWER
      </span>
    </div>

    <!-- Top Editorial Header & Progress Bar -->
    <div class="relative z-10 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 lg:pt-10 flex flex-col sm:flex-row sm:items-end justify-between gap-6">
      <div>
        <div class="flex items-center gap-3 mb-2">
          <span class="inline-block w-2 h-2 rounded-full bg-[#C5A880] animate-pulse"></span>
          <span class="text-[11px] uppercase tracking-[0.3em] text-[#C5A880] font-medium">
            Runway Lookbook • 2026/27 Collection
          </span>
        </div>
        <h2 class="font-serif text-3xl sm:text-5xl lg:text-6xl text-white tracking-tight leading-none">
          THE RUNWAY EDIT
        </h2>
      </div>

      <!-- Controls & Progress Tracker -->
      <div class="flex items-center gap-6">
        <div class="hidden sm:flex flex-col items-end">
          <span class="text-xs uppercase tracking-widest text-[#A89B8A] mb-1.5 font-light">
            Scroll to glide the collection
          </span>
          <!-- Dynamic Progress Bar -->
          <div class="w-44 h-1 bg-white/10 rounded-full overflow-hidden">
            <div
              class="h-full bg-[#C5A880] transition-all duration-75"
              :style="{ width: `${Math.max(8, scrollPercent)}%` }"
            ></div>
          </div>
        </div>

        <div class="flex items-center gap-2 border border-white/15 px-3 py-1.5 rounded-full text-xs font-mono text-[#C5A880]">
          <span>{{ currentLookNumber }}</span>
          <span class="text-white/30">/</span>
          <span class="text-white/50">{{ displayProducts.length < 10 ? '0' + displayProducts.length : displayProducts.length }}</span>
        </div>
      </div>
    </div>

    <!-- Pinned Horizontal Track (Desktop) / Smooth Horizontal Swipe (Mobile) -->
    <div
      ref="trackWrapperRef"
      class="relative z-10 w-full my-auto py-8 lg:py-0 overflow-x-auto lg:overflow-visible no-scrollbar"
    >
      <div
        ref="trackRef"
        class="flex items-center gap-6 sm:gap-8 lg:gap-10 px-4 sm:px-6 lg:px-12 w-max"
      >
        <!-- Editorial Intro Card -->
        <div class="w-[300px] sm:w-[360px] lg:w-[420px] shrink-0 bg-white/5 border border-white/10 p-8 sm:p-10 flex flex-col justify-between aspect-3/4 backdrop-blur-md">
          <div class="space-y-4">
            <span class="text-[10px] tracking-[0.3em] uppercase text-[#C5A880] font-semibold block">
              Curated Silhouette
            </span>
            <h3 class="font-serif text-2xl sm:text-3xl text-white leading-snug">
              Sculpted tailoring for the contemporary silhouette.
            </h3>
            <p class="text-xs sm:text-sm text-white/60 font-light leading-relaxed">
              Every garment in this collection is cut from hand-selected double-faced wool, raw mulberry silk, and Tuscan buttery lambskin.
            </p>
          </div>

          <div class="pt-6 border-t border-white/10 flex items-center justify-between text-xs text-white/40">
            <span class="uppercase tracking-widest text-[10px]">Fall / Winter</span>
            <span class="inline-flex items-center gap-2 text-[#C5A880] font-medium text-xs">
              <span>Glide Forward</span>
              <span>→</span>
            </span>
          </div>
        </div>

        <!-- Runway Product Cards -->
        <div
          v-for="(item, index) in displayProducts"
          :key="item.id || index"
          class="runway-card group relative w-[280px] sm:w-[340px] lg:w-[380px] shrink-0 bg-[#181716] border border-white/10 overflow-hidden flex flex-col aspect-3/4 shadow-2xl transition-all duration-500 hover:border-[#C5A880]/60"
        >
          <!-- Look Number Badge -->
          <div class="absolute top-4 left-4 z-20 bg-black/75 backdrop-blur-md px-3 py-1 border border-white/10">
            <span class="text-[10px] tracking-[0.25em] font-mono text-[#C5A880] uppercase">
              LOOK {{ index < 9 ? '0' + (index + 1) : (index + 1) }}
            </span>
          </div>

          <!-- Product Image with Cinematic Parallax Scale -->
          <div class="relative flex-1 overflow-hidden bg-black/40">
            <img
              :src="item.featured_image || 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&w=900&q=80'"
              :alt="item.name"
              class="runway-img w-full h-full object-cover transition-transform duration-700 ease-out group-hover:scale-108"
              loading="lazy"
            />
            <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/20 to-transparent"></div>

            <!-- Floating Quick Discover Pill on Hover -->
            <div class="absolute inset-x-4 bottom-4 z-20 flex justify-between items-end">
              <div class="space-y-1 pr-2">
                <span class="text-[10px] uppercase tracking-widest text-[#A89B8A] block">
                  {{ item.category?.name || 'Exclusive Atelier' }}
                </span>
                <h4 class="font-serif text-base sm:text-lg text-white font-medium line-clamp-1 group-hover:text-[#C5A880] transition-colors">
                  {{ item.name }}
                </h4>
                <div class="text-xs text-white/90 font-mono">
                  <span>${{ item.sale_price || item.price }}</span>
                  <span v-if="item.sale_price" class="text-white/40 line-through text-[10px] ml-1.5">
                    ${{ item.price }}
                  </span>
                </div>
              </div>

              <router-link
                :to="`/product/${item.slug}`"
                class="shrink-0 w-10 h-10 rounded-full bg-white text-[#111111] hover:bg-[#C5A880] hover:text-white flex items-center justify-center transition-all duration-300 shadow-lg"
                :title="`View ${item.name}`"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
              </router-link>
            </div>
          </div>
        </div>

        <!-- Final Runway Outro / CTA Card -->
        <div class="w-[280px] sm:w-[340px] lg:w-[400px] shrink-0 bg-gradient-to-br from-[#1E1C1A] to-[#111111] border border-[#C5A880]/30 p-8 sm:p-10 flex flex-col justify-between aspect-3/4">
          <div class="space-y-4">
            <span class="text-[10px] tracking-[0.3em] uppercase text-[#C5A880] font-semibold block">
              Complete Experience
            </span>
            <h3 class="font-serif text-3xl sm:text-4xl text-white leading-tight">
              Explore The Complete Runway Edit
            </h3>
            <p class="text-xs sm:text-sm text-white/60 font-light leading-relaxed">
              Step into our complete collection catalog featuring all 48 signature designs, bespoke accessories, and seasonal footwear.
            </p>
          </div>

          <div class="pt-6">
            <router-link
              to="/collections"
              class="inline-flex items-center justify-center w-full py-3.5 px-6 bg-[#C5A880] text-[#111111] text-xs uppercase tracking-[0.2em] font-semibold hover:bg-white transition-colors duration-300 shadow-xl"
            >
              <span>Explore All Garments</span>
              <span class="ml-2">→</span>
            </router-link>
          </div>
        </div>
      </div>
    </div>

    <!-- Bottom Editorial Footer Strip -->
    <div class="relative z-10 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 lg:pb-8 flex items-center justify-between text-[11px] text-white/40 border-t border-white/10 pt-4">
      <div class="flex items-center gap-6">
        <span>CRAFTED IN MILAN & PARIS</span>
        <span class="hidden sm:inline">•</span>
        <span class="hidden sm:inline">ZERO-WASTE SUSTAINABLE CASHMERE</span>
      </div>
      <div class="flex items-center gap-2">
        <span class="w-1.5 h-1.5 rounded-full bg-[#C5A880]"></span>
        <span class="font-mono text-white/60">AUTUMN / WINTER 2026</span>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';
import { gsap, ScrollTrigger } from '../../utils/smoothScroll';

const props = defineProps({
  products: {
    type: Array,
    default: () => [],
  },
});

const runwaySectionRef = ref(null);
const trackWrapperRef = ref(null);
const trackRef = ref(null);
const scrollPercent = ref(0);

// Default luxury fallback garments if products are loading
const fallbackProducts = [
  {
    id: 'fb-1',
    name: 'Sartorial Double-Breasted Wool Coat',
    slug: 'sartorial-double-breasted-wool-coat',
    price: '890.00',
    sale_price: null,
    featured_image: 'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?auto=format&fit=crop&w=900&q=80',
    category: { name: 'Outerwear' },
  },
  {
    id: 'fb-2',
    name: 'Fluid Silk Georgette Evening Gown',
    slug: 'fluid-silk-georgette-evening-gown',
    price: '740.00',
    sale_price: '680.00',
    featured_image: 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&w=900&q=80',
    category: { name: 'Gowns' },
  },
  {
    id: 'fb-3',
    name: 'Unlined Mongolian Cashmere Wrap',
    slug: 'unlined-mongolian-cashmere-wrap',
    price: '620.00',
    sale_price: null,
    featured_image: 'https://images.unsplash.com/photo-1509631179647-0177331693ae?auto=format&fit=crop&w=900&q=80',
    category: { name: 'Knitwear' },
  },
  {
    id: 'fb-4',
    name: 'Pleated Virgin Wool Wide-Leg Trousers',
    slug: 'pleated-virgin-wool-wide-leg-trousers',
    price: '420.00',
    sale_price: null,
    featured_image: 'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?auto=format&fit=crop&w=900&q=80',
    category: { name: 'Tailoring' },
  },
  {
    id: 'fb-5',
    name: 'Handcrafted Glove Lambskin Trench',
    slug: 'handcrafted-glove-lambskin-trench',
    price: '1250.00',
    sale_price: '1100.00',
    featured_image: 'https://images.unsplash.com/photo-1445205170230-053b83016050?auto=format&fit=crop&w=900&q=80',
    category: { name: 'Leather' },
  },
];

const displayProducts = computed(() => {
  if (props.products && props.products.length > 0) {
    return props.products.slice(0, 8);
  }
  return fallbackProducts;
});

const currentLookNumber = computed(() => {
  const count = displayProducts.value.length;
  if (count === 0) return '01';
  const current = Math.min(count, Math.max(1, Math.ceil((scrollPercent.value / 100) * count)));
  return current < 10 ? `0${current}` : `${current}`;
});

let scrollTriggerInstance = null;

const initScrollAnimation = () => {
  if (typeof window === 'undefined' || !runwaySectionRef.value || !trackRef.value) return;

  // On desktop / larger screens, initialize the GSAP ScrollTrigger pinned track
  if (window.innerWidth >= 1024) {
    const track = trackRef.value;
    const section = runwaySectionRef.value;

    const totalScroll = track.scrollWidth - window.innerWidth + 180;

    scrollTriggerInstance = ScrollTrigger.create({
      trigger: section,
      pin: true,
      start: 'top top',
      end: () => `+=${totalScroll}`,
      scrub: 1.1,
      invalidateOnRefresh: true,
      animation: gsap.to(track, {
        x: () => -totalScroll,
        ease: 'none',
      }),
      onUpdate: (self) => {
        scrollPercent.value = Math.round(self.progress * 100);
      },
    });

    ScrollTrigger.refresh();
  }
};

onMounted(() => {
  nextTick(() => {
    // Delay slightly to ensure fonts and layout dimensions are resolved
    setTimeout(initScrollAnimation, 200);
  });

  const handleResize = () => {
    if (scrollTriggerInstance) {
      scrollTriggerInstance.kill();
      scrollTriggerInstance = null;
    }
    initScrollAnimation();
  };

  window.addEventListener('resize', handleResize);

  onUnmounted(() => {
    window.removeEventListener('resize', handleResize);
    if (scrollTriggerInstance) {
      scrollTriggerInstance.kill();
      scrollTriggerInstance = null;
    }
  });
});
</script>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
  display: none;
}
.no-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
