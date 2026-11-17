<template>
  <section
    class="relative w-full h-[80vh] min-h-[560px] max-h-[850px] lg:h-[86vh] bg-black text-white overflow-hidden select-none"
    @mouseenter="isPaused = true"
    @mouseleave="isPaused = false"
  >
    <!-- Background Cinematic Photography Slides (Full-Bleed 16:9 Widescreen Banner) -->
    <div class="absolute inset-0 w-full h-full">
      <transition-group name="omega-fade">
        <div
          v-for="(slide, idx) in slides"
          v-show="currentSlideIndex === idx"
          :key="slide.id"
          class="absolute inset-0 w-full h-full"
        >
          <img
            :src="slide.image"
            :alt="slide.title"
            class="w-full h-full object-cover object-center scale-100 transition-transform duration-[6000ms] ease-out will-change-transform"
            :class="{ 'scale-105': currentSlideIndex === idx }"
          />
        </div>
      </transition-group>
    </div>

    <!-- Cinematic Vignette Gradients for Text Contrast -->
    <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 to-black/25 pointer-events-none z-10"></div>
    <div class="absolute inset-0 bg-gradient-to-r from-black/75 via-black/25 to-transparent pointer-events-none z-10"></div>

    <!-- Floating Navigation Arrows (Left & Right) -->
    <button
      type="button"
      @click="prevSlide"
      class="absolute left-4 sm:left-8 top-1/2 -translate-y-1/2 w-11 h-11 sm:w-13 sm:h-13 rounded-full bg-black/40 hover:bg-black/75 text-white backdrop-blur-md flex items-center justify-center border border-white/20 text-2xl transition-all shadow-xl active:scale-95 cursor-pointer z-30 group"
      title="পূর্ববর্তী স্লাইড"
      aria-label="Previous Slide"
    >
      <span class="transition-transform group-hover:-translate-x-0.5">‹</span>
    </button>

    <button
      type="button"
      @click="nextSlide"
      class="absolute right-4 sm:right-8 top-1/2 -translate-y-1/2 w-11 h-11 sm:w-13 sm:h-13 rounded-full bg-black/40 hover:bg-black/75 text-white backdrop-blur-md flex items-center justify-center border border-white/20 text-2xl transition-all shadow-xl active:scale-95 cursor-pointer z-30 group"
      title="পরবর্তী স্লাইড"
      aria-label="Next Slide"
    >
      <span class="transition-transform group-hover:translate-x-0.5">›</span>
    </button>

    <!-- Main Content Container -->
    <div class="relative w-full h-full max-w-[1560px] mx-auto px-6 sm:px-12 lg:px-16 flex flex-col justify-end pb-12 sm:pb-16 lg:pb-20 z-20">
      
      <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-8 lg:gap-12">
        
        <!-- ============================================== -->
        <!-- BOTTOM-LEFT: Bold Campaign Title, Description & White Pill CTA -->
        <!-- ============================================== -->
        <div class="max-w-2xl space-y-4 sm:space-y-5 animate-slide-up">
          
          <!-- Campaign Title -->
          <h1 class="text-4xl sm:text-6xl lg:text-7xl font-bold text-white tracking-tight leading-none drop-shadow-md">
            {{ currentSlide.title }}
          </h1>

          <!-- Description / Tagline -->
          <p class="text-sm sm:text-base lg:text-lg text-white/90 font-light leading-relaxed max-w-xl drop-shadow-sm">
            {{ currentSlide.subtitle }}
          </p>

          <!-- White Pill CTA Button -->
          <div class="pt-2">
            <router-link
              :to="currentSlide.link"
              class="inline-flex items-center gap-3 px-7 sm:px-8 py-3.5 sm:py-4 bg-white text-[#111111] hover:bg-[#14382A] hover:text-white rounded-full font-bold text-xs sm:text-sm tracking-wider uppercase transition-all duration-300 shadow-2xl group cursor-pointer"
            >
              <span>{{ currentSlide.cta }}</span>
              <span class="text-base font-bold transition-transform duration-300 group-hover:translate-x-1.5">›</span>
            </router-link>
          </div>
        </div>

        <!-- ============================================== -->
        <!-- BOTTOM-RIGHT: Category Tabs with Circular Progress Timer -->
        <!-- ============================================== -->
        <div class="flex items-center gap-5 sm:gap-7 lg:gap-8 overflow-x-auto pb-2 lg:pb-0 scrollbar-none">
          <div
            v-for="(slide, idx) in slides"
            :key="slide.id"
            class="shrink-0"
          >
            <!-- Active Tab with Animated Circular Progress Ring -->
            <button
              v-if="currentSlideIndex === idx"
              type="button"
              @click="setSlide(idx)"
              class="flex items-center gap-2.5 text-sm sm:text-base font-semibold tracking-wider text-white transition-all cursor-pointer group"
            >
              <!-- SVG Radial Progress Ring -->
              <div class="relative w-5 h-5 sm:w-6 sm:h-6 flex items-center justify-center shrink-0">
                <svg class="w-full h-full -rotate-90" viewBox="0 0 36 36">
                  <!-- Inactive track ring -->
                  <path
                    class="text-white/20"
                    stroke-width="3.5"
                    stroke="currentColor"
                    fill="none"
                    d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                  />
                  <!-- Active progress stroke -->
                  <path
                    class="text-white transition-all duration-75 ease-linear"
                    :stroke-dasharray="`${progressPercent}, 100`"
                    stroke-width="3.5"
                    stroke-linecap="round"
                    stroke="currentColor"
                    fill="none"
                    d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                  />
                </svg>
              </div>

              <!-- Label with underline -->
              <span class="border-b-2 border-white pb-0.5 whitespace-nowrap drop-shadow-sm">
                {{ slide.tabName }}
              </span>
            </button>

            <!-- Inactive Tab -->
            <button
              v-else
              type="button"
              @click="setSlide(idx)"
              class="text-sm sm:text-base font-medium tracking-wider text-white/55 hover:text-white transition-colors cursor-pointer whitespace-nowrap drop-shadow-sm"
            >
              {{ slide.tabName }}
            </button>
          </div>
        </div>

      </div>

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

const currentSlideIndex = ref(0);
const progressPercent = ref(0);
const isPaused = ref(false);

const SLIDE_DURATION = 6000; // 6 seconds per slide
const TICK_INTERVAL = 40; // 40ms interval for smooth 60fps radial ring
let elapsed = 0;
let timer = null;

// 16:9 Widescreen Banner-dimensioned Images
const slides = [
  {
    id: 'slide-01',
    tabName: 'আউটডোর জ্যাকেট',
    title: 'Own the Rain',
    subtitle: 'প্রিমিয়াম টেক-ফ্লিস ও ওয়াটারপ্রুফ আউটারওয়্যার। শীত ও বৃষ্টির প্রতিটি দিনে আন্তর্জাতিক মানের স্বাচ্ছন্দ্য।',
    cta: 'জ্যাকেট কালেকশন',
    link: '/collections',
    image: '/storage/banners/drawer-omega-hero-1920x1080.jpg',
  },
  {
    id: 'slide-02',
    tabName: 'পাঞ্জাবি সিরিজ',
    title: 'Heritage Refined',
    subtitle: 'বাঙালির ঐতিহ্য ও সমকালীন আভিজাত্যের নিখুঁত মেলবন্ধন। এক্সক্লুসিভ জ্যাকার্ড ও সুতি পাঞ্জাবি সংগ্রহ।',
    cta: 'পাঞ্জাবি কালেকশন',
    link: '/collection/panjabi',
    image: '/storage/banners/drawer-panjabi-hero-16-9.jpg',
  },
  {
    id: 'slide-03',
    tabName: 'স্ট্রিটওয়্যার ও বক্সি',
    title: 'Urban Silhouette',
    subtitle: '২৬০ জিএসএম হেভিওয়েট কটন বক্সি টি-শার্ট এবং রিল্যাক্সড ওয়াইড-লেগ ব্যাগি প্যান্ট সিরিজ।',
    cta: 'টি-শার্ট ও প্যান্ট',
    link: '/collection/t-shirt',
    image: '/storage/banners/drawer-streetwear-hero-16-9.jpg',
  },
];

const currentSlide = computed(() => slides[currentSlideIndex.value]);

const setSlide = (index) => {
  currentSlideIndex.value = index;
  elapsed = 0;
  progressPercent.value = 0;
};

const nextSlide = () => {
  const nextIdx = (currentSlideIndex.value + 1) % slides.length;
  setSlide(nextIdx);
};

const prevSlide = () => {
  const prevIdx = (currentSlideIndex.value - 1 + slides.length) % slides.length;
  setSlide(prevIdx);
};

const startTimer = () => {
  stopTimer();
  timer = setInterval(() => {
    if (!isPaused.value) {
      elapsed += TICK_INTERVAL;
      progressPercent.value = Math.min(100, (elapsed / SLIDE_DURATION) * 100);

      if (elapsed >= SLIDE_DURATION) {
        nextSlide();
      }
    }
  }, TICK_INTERVAL);
};

const stopTimer = () => {
  if (timer) {
    clearInterval(timer);
    timer = null;
  }
};

onMounted(() => {
  startTimer();
});

onUnmounted(() => {
  stopTimer();
});
</script>

<style scoped>
/* Cinematic Crossfade */
.omega-fade-enter-active,
.omega-fade-leave-active {
  transition: opacity 0.8s cubic-bezier(0.16, 1, 0.3, 1);
}

.omega-fade-enter-from {
  opacity: 0;
}

.omega-fade-leave-to {
  opacity: 0;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(16px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-slide-up {
  animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;
}

/* Hide scrollbar for Chrome, Safari and Opera */
.scrollbar-none::-webkit-scrollbar {
  display: none;
}
/* Hide scrollbar for IE, Edge and Firefox */
.scrollbar-none {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
