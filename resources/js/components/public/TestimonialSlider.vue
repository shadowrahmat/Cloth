<template>
  <section ref="sectionEl" class="py-14 sm:py-20 lg:py-28 max-w-7xl mx-auto sm:px-6 lg:px-8">
    <div class="text-center max-w-2xl mx-auto mb-8 sm:mb-14 space-y-2 sm:space-y-3 px-4 sm:px-0">
      <h2 class="font-serif text-[26px] sm:text-4xl text-[#111111] tracking-tight">
        {{ content.heading || 'গ্রাহকদের অনুভূতি ও অভিজ্ঞতা' }}
      </h2>
      <p class="text-[13px] sm:text-sm text-[#69736E] leading-relaxed">
        {{ content.description || 'DRAWER CLOTHING থেকে পণ্য ক্রয়কারী সম্মানিত গ্রাহকদের মূল্যবান রিভিউ ও অভিজ্ঞতা।' }}
      </p>
    </div>

    <!-- Phones: swipeable snap carousel (next card peeks in). md+: 3-column grid. -->
    <div
      ref="track"
      class="flex md:grid md:grid-cols-3 gap-3 md:gap-8 overflow-x-auto md:overflow-visible snap-x snap-mandatory no-scrollbar px-4 sm:px-0 scroll-px-4 overscroll-x-contain"
      role="region"
      aria-roledescription="carousel"
      aria-label="গ্রাহকদের রিভিউ"
      @scroll.passive="onTrackScroll"
      @pointerdown="pause"
      @touchstart.passive="pause"
    >
      <article
        v-for="(t, idx) in testimonials"
        :key="idx"
        :aria-label="`${idx + 1} / ${testimonials.length}`"
        class="snap-start shrink-0 w-[86%] md:w-auto bg-white p-6 sm:p-8 border border-[#E5DED3] flex flex-col justify-between"
      >
        <div class="space-y-4">
          <div class="flex text-[#C5A880] text-sm" :aria-label="`${t.rating || 5} স্টার`">
            <span v-for="star in (t.rating || 5)" :key="star">★</span>
          </div>
          <p class="font-serif italic text-[15px] sm:text-lg text-[#111111]/85 leading-relaxed">"{{ t.review }}"</p>
        </div>

        <div class="flex items-center gap-3 pt-5 border-t border-[#E5DED3] mt-6">
          <img v-if="t.customer_image" :src="t.customer_image" :alt="t.customer_name" class="w-10 h-10 rounded-full object-cover shrink-0" loading="lazy" />
          <div v-else class="w-10 h-10 rounded-full bg-[#EAE5DC] flex items-center justify-center font-serif text-sm text-[#111111] shrink-0">
            {{ t.customer_name?.charAt(0) || 'C' }}
          </div>
          <div class="min-w-0">
            <h6 class="font-semibold text-[13px] text-[#111111] truncate">{{ t.customer_name }}</h6>
            <span v-if="t.product_purchased" class="text-[12px] text-[#A89B8A] block truncate">ক্রয় করেছেন: {{ t.product_purchased }}</span>
          </div>
        </div>
      </article>
    </div>

    <!-- Dots (phones only) -->
    <div v-if="testimonials.length > 1" class="md:hidden mt-5 flex justify-center gap-2" role="tablist" aria-label="রিভিউ বাছাই">
      <button
        v-for="(t, i) in testimonials"
        :key="i"
        type="button"
        role="tab"
        :aria-selected="i === active"
        :aria-label="`রিভিউ ${i + 1}`"
        @click="pause(); goTo(i)"
        class="h-2 rounded-full transition-all duration-300"
        :class="i === active ? 'w-6 bg-[#111111]' : 'w-2 bg-[#111111]/20'"
      ></button>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
  testimonials: { type: Array, default: () => [] },
  content: { type: Object, default: () => ({}) },
});

const sectionEl = ref(null);
const track = ref(null);
const active = ref(0);

const cards = () => [...(track.value?.children || [])];

// Which card is closest to the left edge of the track.
const onTrackScroll = () => {
  const el = track.value;
  if (!el) return;
  const left = el.scrollLeft;
  let best = 0;
  let bestDist = Infinity;
  cards().forEach((c, i) => {
    const d = Math.abs(c.offsetLeft - el.offsetLeft - parseFloat(getComputedStyle(el).paddingLeft) - left);
    if (d < bestDist) { bestDist = d; best = i; }
  });
  active.value = best;
};

const goTo = (i) => {
  const el = track.value;
  const card = cards()[i];
  if (!el || !card) return;
  const pad = parseFloat(getComputedStyle(el).paddingLeft) || 0;
  el.scrollTo({ left: card.offsetLeft - el.offsetLeft - pad, behavior: reduceMotion ? 'auto' : 'smooth' });
};

// --- autoplay: phones only, only while visible, stops for good once the user touches it
const reduceMotion = typeof window !== 'undefined' && window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;
const isPhone = () => window.matchMedia('(max-width: 767px)').matches;
let timer = null;
let visible = false;
let userTookOver = false;
let observer = null;

const tick = () => {
  if (!visible || userTookOver || !isPhone() || props.testimonials.length < 2) return;
  goTo((active.value + 1) % props.testimonials.length);
};
const pause = () => { userTookOver = true; clearInterval(timer); };

onMounted(() => {
  if (reduceMotion) return;
  observer = new IntersectionObserver(([e]) => { visible = e.isIntersecting; }, { threshold: 0.4 });
  if (sectionEl.value) observer.observe(sectionEl.value);
  timer = setInterval(tick, 5000);
});
onBeforeUnmount(() => {
  clearInterval(timer);
  observer?.disconnect();
});
</script>
