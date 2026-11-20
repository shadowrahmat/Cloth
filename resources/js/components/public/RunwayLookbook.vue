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
            {{ t.kicker }}
          </span>
        </div>
        <h2 class="font-serif text-3xl sm:text-5xl lg:text-6xl text-white tracking-tight leading-none">
          {{ t.heading }}
        </h2>
      </div>

      <!-- Controls & Progress Tracker -->
      <div class="flex items-center gap-6">
        <div class="hidden sm:flex flex-col items-end">
          <span class="text-xs uppercase tracking-widest text-[#A89B8A] mb-1.5 font-light">
            কালেকশন দেখতে স্ক্রল করুন
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
              {{ t.intro_kicker }}
            </span>
            <h3 class="font-serif text-2xl sm:text-3xl text-white leading-snug">
              {{ t.intro_title }}
            </h3>
            <p class="text-xs sm:text-sm text-white/60 font-light leading-relaxed">
              {{ t.intro_text }}
            </p>
          </div>

          <div class="pt-6 border-t border-white/10 flex items-center justify-between text-xs text-white/40">
            <span class="uppercase tracking-widest text-[10px]">{{ t.season }}</span>
            <span class="inline-flex items-center gap-2 text-[#C5A880] font-medium text-xs">
              <span>সামনে দেখুন</span>
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
              :src="item.featured_image || '/storage/uploads/drop-shoulder-price-490size-mlxl1-1794823766-fpGc.jpg'"
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
                  <span>{{ taka(item.sale_price && Number(item.sale_price) < Number(item.price) ? item.sale_price : item.price) }}</span>
                  <span v-if="item.sale_price && Number(item.sale_price) < Number(item.price)" class="text-white/40 line-through text-[10px] ml-1.5">
                    {{ taka(item.price) }}
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
              {{ t.outro_kicker }}
            </span>
            <h3 class="font-serif text-3xl sm:text-4xl text-white leading-tight">
              {{ t.outro_title }}
            </h3>
            <p class="text-xs sm:text-sm text-white/60 font-light leading-relaxed">
              {{ t.outro_text }}
            </p>
          </div>

          <div class="pt-6">
            <router-link
              :to="t.outro_link"
              class="inline-flex items-center justify-center w-full py-3.5 px-6 bg-[#C5A880] text-[#111111] text-xs font-semibold hover:bg-white transition-colors duration-300 shadow-xl"
            >
              <span>{{ t.outro_button }}</span>
              <span class="ml-2">→</span>
            </router-link>
          </div>
        </div>
      </div>
    </div>

    <!-- Bottom Editorial Footer Strip -->
    <div class="relative z-10 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 lg:pb-8 flex items-center justify-between text-[11px] text-white/40 border-t border-white/10 pt-4">
      <div class="flex items-center gap-6">
        <span v-if="t.feature_1">{{ t.feature_1 }}</span>
        <span v-if="t.feature_1 && t.feature_2" class="hidden sm:inline">•</span>
        <span v-if="t.feature_2" class="hidden sm:inline">{{ t.feature_2 }}</span>
      </div>
      <div class="flex items-center gap-2">
        <span class="w-1.5 h-1.5 rounded-full bg-[#C5A880]"></span>
        <span class="text-white/60">{{ t.season }}</span>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';
import { gsap, ScrollTrigger } from '../../utils/smoothScroll';
import { taka } from '../../utils/bn';
import { RUNWAY_DEFAULTS } from '../../utils/homeDefaults';

const props = defineProps({
  /** texts from Admin → হোমপেজ বিল্ডার → নতুন আগমন → রানওয়ে (empty fields fall back to the defaults) */
  content: { type: Object, default: () => ({}) },
  products: {
    type: Array,
    default: () => [],
  },
});

const t = computed(() => {
  const c = props.content || {};
  return Object.fromEntries(Object.entries(RUNWAY_DEFAULTS).map(([k, v]) => [k, (c[k] ?? '').toString().trim() || v]));
});

const runwaySectionRef = ref(null);
const trackWrapperRef = ref(null);
const trackRef = ref(null);
const scrollPercent = ref(0);

// Default luxury fallback garments mapping to real media library products
const fallbackProducts = [
  {
    id: 18,
    name: 'Exclusive Heritage Panjabi Collection',
    slug: 'exclusive-heritage-panjabi-collection',
    price: '1499.00',
    sale_price: null,
    featured_image: '/storage/uploads/price-1499size-4042441-1794823926-a9P0.jpg',
    category: { name: 'Panjabi' },
  },
  {
    id: 11,
    name: 'Boxy Fit Heavyweight Cotton T-Shirt',
    slug: 'boxy-fit-heavyweight-cotton-t-shirt',
    price: '790.00',
    sale_price: null,
    featured_image: '/storage/uploads/boxy-fit-price-790-size-mlxl1-1794823757-keB9.jpg',
    category: { name: 'BOXY' },
  },
  {
    id: 12,
    name: 'Drop Shoulder Oversized T-Shirt',
    slug: 'drop-shoulder-oversized-t-shirt',
    price: '490.00',
    sale_price: null,
    featured_image: '/storage/uploads/drop-shoulder-price-490size-mlxl1-1794823766-fpGc.jpg',
    category: { name: 'Drop shoulder' },
  },
  {
    id: 10,
    name: 'Men Baggy Fit Casual Pant',
    slug: 'men-baggy-fit-casual-pant',
    price: '650.00',
    sale_price: null,
    featured_image: '/storage/uploads/baggy-pant-01-1794823750-TG8Q.webp',
    category: { name: 'Baggy pants' },
  },
  {
    id: 19,
    name: 'Classic Pique Polo T-Shirt',
    slug: 'classic-pique-polo-t-shirt',
    price: '650.00',
    sale_price: null,
    featured_image: '/storage/uploads/polo-t-shirt-price-650size-mlxl1-1794823936-Kham.jpg',
    category: { name: 'Polo t-shirt' },
  },
  {
    id: 16,
    name: 'Premium Denim & Chino Pants',
    slug: 'premium-denim-chino-pants',
    price: '950.00',
    sale_price: null,
    featured_image: '/storage/uploads/price-950size-2829303132333435-361-1794823890-5RIh.jpg',
    category: { name: 'Jeans pant' },
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
