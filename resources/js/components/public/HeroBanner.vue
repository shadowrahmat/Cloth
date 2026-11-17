<template>
  <section
    class="relative w-full h-[88vh] sm:h-[92vh] lg:h-[95vh] min-h-[620px] bg-[#111111] overflow-hidden select-none flex flex-col justify-between"
    @mouseenter="isHovered = true"
    @mouseleave="isHovered = false"
  >
    <!-- Multi-Slide Cinematic Ken-Burns Imagery Background -->
    <div class="absolute inset-0 z-0">
      <div
        v-for="(slide, index) in slides"
        :key="index"
        :class="[
          'absolute inset-0 transition-opacity duration-1000 ease-out',
          currentSlide === index ? 'opacity-100 z-10 pointer-events-auto' : 'opacity-0 z-0 pointer-events-none'
        ]"
      >
        <!-- Image with Ken-Burns scale effect -->
        <img
          :src="slide.image"
          :alt="slide.headline"
          :class="[
            'w-full h-full object-cover object-center transform transition-transform duration-[7000ms] ease-out',
            currentSlide === index ? 'scale-100' : 'scale-110'
          ]"
        />

        <!-- High-Contrast Luxury Gradient Overlays -->
        <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/35 to-black/40"></div>
        <div class="absolute inset-0 bg-radial-at-c from-transparent via-black/20 to-black/60"></div>
      </div>
    </div>

    <!-- Top Accent Bar: Season & Scene Indicator -->
    <div class="relative z-20 w-full max-w-7xl mx-auto px-6 sm:px-8 lg:px-12 pt-8 sm:pt-10 flex items-center justify-between text-white/70">
      <div class="flex items-center gap-3">
        <span class="inline-block w-2 h-2 rounded-full bg-[#C5A880] animate-ping"></span>
        <span class="text-[10px] sm:text-xs uppercase tracking-[0.3em] font-medium text-white/90">
          {{ activeSlide.tagline || 'Fall / Winter 2026/27 Runway' }}
        </span>
      </div>

      <div class="hidden sm:flex items-center gap-4 text-xs font-mono tracking-widest text-[#C5A880]">
        <span>MILAN</span>
        <span class="text-white/30">•</span>
        <span>PARIS</span>
        <span class="text-white/30">•</span>
        <span>NEW YORK</span>
      </div>
    </div>

    <!-- Main Editorial Content Area (Staggered Animation) -->
    <div class="relative z-20 max-w-7xl mx-auto px-6 sm:px-8 lg:px-12 w-full my-auto text-white">
      <div class="max-w-3xl space-y-5 sm:space-y-6">
        <!-- Collection Tag -->
        <div class="inline-flex items-center gap-2 border border-white/20 bg-black/30 backdrop-blur-md px-3.5 py-1.5 rounded-full">
          <span class="w-1.5 h-1.5 rounded-full bg-[#C5A880]"></span>
          <span class="text-[10px] uppercase tracking-[0.25em] font-medium text-[#D8CCBC]">
            {{ activeSlide.badge || 'Haute Prêt-à-Porter' }}
          </span>
        </div>

        <!-- Headline with High-Impact Serif Typography -->
        <h1 class="font-serif text-4xl sm:text-6xl lg:text-7xl xl:text-8xl leading-[1.04] tracking-tight text-white drop-shadow-md">
          <span class="block transition-all duration-700 transform translate-y-0 opacity-100">
            {{ activeSlide.headline }}
          </span>
        </h1>

        <!-- Subheadline / Editorial Description -->
        <p class="text-xs sm:text-sm lg:text-base text-white/80 font-light leading-relaxed max-w-xl line-clamp-3 sm:line-clamp-none">
          {{ activeSlide.subheadline }}
        </p>

        <!-- Action CTAs -->
        <div class="flex flex-wrap items-center gap-4 pt-2 sm:pt-4">
          <router-link
            :to="activeSlide.primary_cta_link || '/collection/winter'"
            class="group inline-flex items-center gap-3 px-8 py-4 bg-white text-[#111111] text-xs uppercase tracking-[0.2em] font-semibold hover:bg-[#C5A880] hover:text-white transition-all duration-300 shadow-2xl hover:scale-102"
          >
            <span>{{ activeSlide.primary_cta_text || 'Explore Collection' }}</span>
            <span class="group-hover:translate-x-1 transition-transform">→</span>
          </router-link>

          <router-link
            :to="activeSlide.secondary_cta_link || '/collection/new-arrivals'"
            class="inline-flex items-center gap-2 px-8 py-4 border border-white/40 bg-black/20 backdrop-blur-xs text-white text-xs uppercase tracking-[0.2em] font-semibold hover:border-white hover:bg-white hover:text-[#111111] transition-all duration-300"
          >
            <span>{{ activeSlide.secondary_cta_text || 'Discover Looks' }}</span>
          </router-link>
        </div>
      </div>
    </div>

    <!-- Bottom Controls & Shoppable Floating Tag -->
    <div class="relative z-20 w-full max-w-7xl mx-auto px-6 sm:px-8 lg:px-12 pb-8 sm:pb-10 flex flex-col sm:flex-row items-start sm:items-end justify-between gap-6 border-t border-white/10 pt-6">
      
      <!-- Slide Numbering & Progress Indicators -->
      <div class="flex items-center gap-6">
        <!-- Number counter -->
        <div class="text-xs font-mono tracking-widest text-white/80">
          <span class="text-[#C5A880] text-sm font-semibold">0{{ currentSlide + 1 }}</span>
          <span class="text-white/30 mx-1">/</span>
          <span class="text-white/50">0{{ slides.length }}</span>
        </div>

        <!-- Slide Progress Bars -->
        <div class="flex items-center gap-2">
          <button
            v-for="(slide, index) in slides"
            :key="index"
            type="button"
            @click="setSlide(index)"
            class="group relative h-1 rounded-full overflow-hidden transition-all duration-300"
            :class="currentSlide === index ? 'w-12 bg-white/30' : 'w-6 bg-white/20 hover:bg-white/40'"
            :aria-label="`Go to slide ${index + 1}`"
          >
            <!-- Filling progress bar for active slide -->
            <div
              v-if="currentSlide === index"
              class="h-full bg-[#C5A880] transition-all duration-75"
              :style="{ width: `${slideProgress}%` }"
            ></div>
          </button>
        </div>

        <!-- Arrows -->
        <div class="hidden sm:flex items-center gap-2 ml-4">
          <button
            type="button"
            @click="prevSlide"
            class="w-8 h-8 rounded-full border border-white/20 flex items-center justify-center text-white/70 hover:text-white hover:border-white transition-colors"
            aria-label="Previous Slide"
          >
            ←
          </button>
          <button
            type="button"
            @click="nextSlide"
            class="w-8 h-8 rounded-full border border-white/20 flex items-center justify-center text-white/70 hover:text-white hover:border-white transition-colors"
            aria-label="Next Slide"
          >
            →
          </button>
        </div>
      </div>

      <!-- Shoppable Hotspot Badge (Prestige Signature) -->
      <router-link
        :to="activeSlide.hotspot_link || '/collection/winter'"
        class="group flex items-center gap-3.5 bg-black/60 hover:bg-black/90 backdrop-blur-md border border-white/15 hover:border-[#C5A880]/60 p-2.5 sm:px-4 sm:py-2.5 rounded-full transition-all duration-300 shadow-xl"
      >
        <div class="w-2.5 h-2.5 rounded-full bg-[#C5A880] animate-pulse"></div>
        <div class="text-left pr-2">
          <span class="block text-[9px] uppercase tracking-widest text-[#A89B8A] font-semibold">
            Featured in Scene
          </span>
          <span class="block text-xs font-serif text-white group-hover:text-[#C5A880] transition-colors line-clamp-1">
            {{ activeSlide.hotspot_name || 'Double-Faced Cashmere Wrap' }}
          </span>
        </div>
        <span class="text-xs font-mono text-[#C5A880] font-medium border-l border-white/15 pl-3">
          {{ activeSlide.hotspot_price || '$890' }}
        </span>
        <span class="text-xs text-white/50 group-hover:text-white group-hover:translate-x-0.5 transition-all">
          ↗
        </span>
      </router-link>

    </div>
  </section>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  content: {
    type: Object,
    default: () => ({}),
  },
});

const currentSlide = ref(0);
const slideProgress = ref(0);
const isHovered = ref(false);
let timer = null;
const SLIDE_DURATION = 6500; // 6.5s per slide

// Luxury Slides Collection
const slides = computed(() => {
  const dynamicHeroImg = props.content?.desktop_image;
  const dynamicHeadline = props.content?.headline;
  const dynamicSubheadline = props.content?.subheadline;

  return [
    {
      badge: 'Runway Capsule 2026/27',
      tagline: 'Paris Fashion Week Edit',
      headline: dynamicHeadline || 'A New Perspective on Modern Luxury',
      subheadline: dynamicSubheadline || 'Architectural silhouettes crafted in Northern Italy from double-faced virgin wool and pure Mongolian cashmere.',
      image: dynamicHeroImg || 'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?auto=format&fit=crop&w=2000&q=85',
      primary_cta_text: props.content?.primary_cta_text || 'Explore The Collection',
      primary_cta_link: props.content?.primary_cta_link || '/collection/winter',
      secondary_cta_text: props.content?.secondary_cta_text || 'Discover Lookbook',
      secondary_cta_link: props.content?.secondary_cta_link || '/collection/new-arrivals',
      hotspot_name: 'Sartorial Double-Breasted Coat',
      hotspot_price: '$890.00',
      hotspot_link: '/collection/winter',
    },
    {
      badge: 'Bespoke Tailoring',
      tagline: 'Milano Artisan Series',
      headline: 'Precision Cuts, Fluid Movement',
      subheadline: 'Unstructured jackets and flowing trousers engineered without friction for the contemporary cosmopolitan aesthetic.',
      image: 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&w=2000&q=85',
      primary_cta_text: 'Shop Sartorial',
      primary_cta_link: '/collection/men',
      secondary_cta_text: 'View New Arrivals',
      secondary_cta_link: '/collection/new-arrivals',
      hotspot_name: 'Fluid Silk Georgette Evening Gown',
      hotspot_price: '$680.00',
      hotspot_link: '/collection/women',
    },
    {
      badge: 'Sustainable Heritage',
      tagline: 'Artisanal Cashmere Guild',
      headline: 'Tactile Warmth & Timeless Grace',
      subheadline: 'Hand-loomed textured knits and buttery lambskin layers made to accompany you across seasons with enduring poise.',
      image: 'https://images.unsplash.com/photo-1445205170230-053b83016050?auto=format&fit=crop&w=2000&q=85',
      primary_cta_text: 'Discover Knitwear',
      primary_cta_link: '/collection/winter',
      secondary_cta_text: 'Our Brand Story',
      secondary_cta_link: '/pages/about-us',
      hotspot_name: 'Unlined Mongolian Cashmere Wrap',
      hotspot_price: '$620.00',
      hotspot_link: '/collection/winter',
    },
  ];
});

const activeSlide = computed(() => slides.value[currentSlide.value]);

const nextSlide = () => {
  currentSlide.value = (currentSlide.value + 1) % slides.value.length;
  slideProgress.value = 0;
};

const prevSlide = () => {
  currentSlide.value = (currentSlide.value - 1 + slides.value.length) % slides.value.length;
  slideProgress.value = 0;
};

const setSlide = (index) => {
  currentSlide.value = index;
  slideProgress.value = 0;
};

onMounted(() => {
  const tickInterval = 50; // 50ms tick
  const step = (tickInterval / SLIDE_DURATION) * 100;

  timer = setInterval(() => {
    if (!isHovered.value) {
      slideProgress.value += step;
      if (slideProgress.value >= 100) {
        nextSlide();
      }
    }
  }, tickInterval);
});

onUnmounted(() => {
  if (timer) clearInterval(timer);
});
</script>
