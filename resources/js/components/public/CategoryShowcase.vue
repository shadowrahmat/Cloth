<template>
  <!--
    Scroll-driven category showcase (minimal): the stage stays pinned while the
    tall section scrolls; scroll progress picks the active category.
    Left = category names, right = that category's product photos.
  -->
  <section
    v-if="items.length"
    id="lookbook"
    ref="sectionEl"
    class="relative bg-[#F7F5F0]"
    :style="{ height: sectionHeight }"
    aria-label="ক্যাটাগরি শোকেস"
  >
    <div class="sticky top-0 h-[100svh] overflow-hidden">
      <div class="h-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-20 lg:pt-24 pb-6 lg:pb-12 flex flex-col lg:grid lg:grid-cols-12 lg:gap-16 lg:items-center">

        <!-- LEFT: heading + names -->
        <div class="lg:col-span-4 flex flex-col">
          <h2 class="font-serif text-2xl sm:text-3xl lg:text-[34px] leading-tight text-[#111111]">{{ content.heading || 'ক্যাটাগরি অনুযায়ী কেনাকাটা' }}</h2>
          <p v-if="content.description" class="hidden lg:block mt-2 text-[13.5px] text-[#69736E]">{{ content.description }}</p>

          <!-- Desktop names -->
          <ul class="hidden lg:block mt-10 space-y-2">
            <li v-for="(item, i) in items" :key="item.slug">
              <button
                type="button"
                @click="jumpTo(i)"
                :aria-current="i === active ? 'true' : undefined"
                class="group flex items-center gap-3 py-1 text-left"
              >
                <span :class="['h-px bg-[#111111] transition-all duration-500 ease-[cubic-bezier(0.22,1,0.36,1)] motion-reduce:transition-none', i === active ? 'w-8' : 'w-0']"></span>
                <span :class="['font-serif text-[26px] xl:text-[30px] leading-snug transition-colors duration-500', i === active ? 'text-[#111111]' : 'text-[#111111]/25 group-hover:text-[#111111]/50']">{{ item.name }}</span>
              </button>
            </li>
          </ul>

          <div class="hidden lg:block mt-10 h-6">
            <Transition name="cta" mode="out-in">
              <router-link :key="current.slug" :to="current.url" class="group inline-flex items-center gap-2 text-[14px] font-medium text-[#111111] border-b border-[#111111] pb-0.5">
                সব {{ current.name }} দেখুন
                <ArrowRight class="w-4 h-4 transition-transform group-hover:translate-x-1" />
              </router-link>
            </Transition>
          </div>

          <!-- Mobile tabs -->
          <div ref="chipRow" class="lg:hidden mt-4 -mx-4 px-4 flex gap-5 overflow-x-auto no-scrollbar border-b border-[#E5DED3]">
            <button
              v-for="(item, i) in items" :key="item.slug" type="button"
              @click="jumpTo(i)"
              :data-chip="i"
              :class="['shrink-0 pb-2.5 -mb-px border-b text-[14px] transition-colors duration-300', i === active ? 'border-[#111111] text-[#111111]' : 'border-transparent text-[#111111]/40']"
            >{{ item.name }}</button>
          </div>
        </div>

        <!-- RIGHT: photos -->
        <div class="lg:col-span-8 relative flex-1 lg:flex-none min-h-0 mt-5 lg:mt-0 lg:h-[70vh]">
          <div
            v-for="(item, i) in items"
            :key="item.slug"
            class="absolute inset-0"
            :class="i === active ? 'pointer-events-auto' : 'pointer-events-none'"
            :aria-hidden="i !== active"
          >
            <div :class="['h-full grid gap-3 lg:gap-5', layoutClass(item.products.length)]">
              <router-link
                v-for="(p, pi) in item.products"
                :key="p.id"
                :to="`/product/${p.slug}`"
                :tabindex="i === active ? 0 : -1"
                :class="['group relative overflow-hidden bg-[#EFEBE4]', cellClass(item.products.length, pi)]"
                :style="imageStyle(i, pi)"
              >
                <img :src="p.image" :alt="p.name" :loading="i < 2 ? 'eager' : 'lazy'" decoding="async"
                     class="absolute inset-0 w-full h-full object-cover object-top transition-transform duration-[1200ms] ease-out group-hover:scale-[1.03]" />
                <span class="absolute left-3 bottom-3 max-w-[calc(100%-1.5rem)] px-3 py-1.5 bg-white/95 text-[12.5px] leading-tight text-[#111111] transition-all duration-300 lg:opacity-0 lg:translate-y-1 lg:group-hover:opacity-100 lg:group-hover:translate-y-0">
                  <span class="block truncate">{{ p.name }}</span>
                  <span class="block text-[#69736E] tabular-nums">{{ taka(p.sale_price && p.sale_price < p.price ? p.sale_price : p.price) }}</span>
                </span>
              </router-link>
            </div>
          </div>
        </div>

        <!-- Mobile link -->
        <router-link :to="current.url" class="lg:hidden mt-4 self-start inline-flex items-center gap-2 text-[14px] font-medium text-[#111111] border-b border-[#111111] pb-0.5">
          সব {{ current.name }} দেখুন <ArrowRight class="w-4 h-4" />
        </router-link>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { ArrowRight } from 'lucide-vue-next';
import { taka } from '../../utils/bn';
import { getLenis } from '../../utils/smoothScroll';

const props = defineProps({
  content: { type: Object, default: () => ({}) },
  items: { type: Array, default: () => [] },
});

const sectionEl = ref(null);
const chipRow = ref(null);
const active = ref(0);
const direction = ref(1);             // 1 = scrolling down, -1 = up (images enter from that side)
const progress = ref(0);

const current = computed(() => props.items[active.value] || props.items[0] || {});
// Each category gets ~75% of a screen of scrolling; the pinned stage is one screen tall.
const sectionHeight = computed(() => `calc(100svh + ${Math.max(0, props.items.length - 1) * 75}svh)`);

// --- layouts: 1 = single, 2 = side by side, 3 = one tall + two stacked, 4 = 2×2
const layoutClass = (n) => ({
  1: 'grid-cols-1',
  2: 'grid-cols-2',
  3: 'grid-cols-2 grid-rows-2 lg:grid-cols-[1.35fr_1fr]',
  4: 'grid-cols-2 grid-rows-2',
}[n] || 'grid-cols-2');
const cellClass = (n, i) => (n === 3 && i === 0 ? 'row-span-2' : '');

// Active layer images rise/fade in with a small stagger; others wait off-stage
// on the side the user is scrolling from, so the motion follows the scroll.
const reduceMotion = typeof window !== 'undefined' && window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;
const imageStyle = (layer, idx) => {
  const on = layer === active.value;
  if (reduceMotion) return { opacity: on ? 1 : 0 };
  const off = layer < active.value ? -1 : 1;     // layers above leave upward, below wait below
  return {
    opacity: on ? 1 : 0,
    transform: on ? 'translate3d(0,0,0) scale(1)' : `translate3d(0, ${off * 48}px, 0) scale(1.04)`,
    clipPath: on ? 'inset(0% 0% 0% 0%)' : (off > 0 ? 'inset(18% 0% 0% 0%)' : 'inset(0% 0% 18% 0%)'),
    transition: 'opacity .7s cubic-bezier(.22,1,.36,1), transform .9s cubic-bezier(.22,1,.36,1), clip-path .9s cubic-bezier(.22,1,.36,1)',
    transitionDelay: on ? `${idx * 90}ms` : '0ms',
  };
};

// --- scroll → active index
let ticking = false;
const measure = () => {
  ticking = false;
  const el = sectionEl.value;
  if (!el || !props.items.length) return;
  const rect = el.getBoundingClientRect();
  const total = el.offsetHeight - window.innerHeight;
  const p = total > 0 ? Math.min(1, Math.max(0, -rect.top / total)) : 0;
  progress.value = p;
  const idx = Math.min(props.items.length - 1, Math.floor(p * props.items.length));
  if (idx !== active.value) {
    direction.value = idx > active.value ? 1 : -1;
    active.value = idx;
  }
};
const onScroll = () => {
  if (!ticking) { ticking = true; requestAnimationFrame(measure); }
};

// Clicking a name scrolls to the middle of that category's stretch.
const jumpTo = (i) => {
  const el = sectionEl.value;
  if (!el) return;
  const total = el.offsetHeight - window.innerHeight;
  const top = window.scrollY + el.getBoundingClientRect().top + ((i + 0.5) / props.items.length) * total;
  const lenis = getLenis();
  if (lenis) lenis.scrollTo(top, { duration: 1 });
  else window.scrollTo({ top, behavior: reduceMotion ? 'auto' : 'smooth' });
};

// Keep the active chip visible on mobile.
watch(active, (i) => nextTick(() => {
  chipRow.value?.querySelector(`[data-chip="${i}"]`)?.scrollIntoView({ inline: 'center', block: 'nearest', behavior: reduceMotion ? 'auto' : 'smooth' });
}));

onMounted(() => {
  window.addEventListener('scroll', onScroll, { passive: true });
  window.addEventListener('resize', onScroll, { passive: true });
  measure();
});
onBeforeUnmount(() => {
  window.removeEventListener('scroll', onScroll);
  window.removeEventListener('resize', onScroll);
});
</script>

<style scoped>
.cta-enter-active, .cta-leave-active { transition: opacity .3s ease, transform .3s ease; }
.cta-enter-from { opacity: 0; transform: translateY(8px); }
.cta-leave-to { opacity: 0; transform: translateY(-8px); }
</style>
