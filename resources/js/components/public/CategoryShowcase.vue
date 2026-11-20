<template>
  <!--
    Scroll-driven category showcase: the stage stays pinned while the tall
    section scrolls; scroll progress picks the active category.
    Left = numbered category index, right = that category's product photos.
  -->
  <section
    v-if="items.length"
    id="lookbook"
    ref="sectionEl"
    class="cs relative bg-[#F7F5F0]"
    :style="{ height: sectionHeight }"
    aria-labelledby="cs-heading"
  >
    <div class="sticky top-0 h-[100svh] overflow-hidden">
      <div class="h-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-20 lg:pt-24 pb-5 lg:pb-10 flex flex-col lg:grid lg:grid-cols-12 lg:gap-14 xl:gap-20 lg:items-center">

        <!-- ================================================ LEFT: heading + index -->
        <div class="lg:col-span-5 flex flex-col">
          <p class="cs-kicker"><span aria-hidden="true"></span>{{ content.kicker || 'ক্যাটাগরি অনুযায়ী দেখুন' }}</p>
          <h2 id="cs-heading" class="mt-3 font-serif text-[28px] sm:text-4xl lg:text-[46px] xl:text-[52px] leading-[1.12] text-[#111111]">
            {{ content.heading || 'আপনার স্টাইল বেছে নিন' }}
          </h2>
          <p v-if="content.description" class="hidden lg:block mt-3 text-[14px] leading-relaxed text-[#69736E] max-w-sm">{{ content.description }}</p>

          <!-- Desktop index -->
          <ol class="hidden lg:block mt-9 border-t border-[#E5DED3]">
            <li v-for="(item, i) in items" :key="item.slug" class="border-b border-[#E5DED3]">
              <button
                type="button"
                @click="jumpTo(i)"
                :aria-current="i === active ? 'true' : undefined"
                class="cs-row group"
                :class="i === active && 'is-active'"
              >
                <span class="cs-num">{{ pad(i + 1) }}</span>
                <span class="cs-name">{{ item.name }}</span>
                <span class="cs-count">{{ bnNumber(item.count) }}টি</span>
                <!-- how far through this category the scroll is -->
                <span class="cs-bar" aria-hidden="true"><span :style="{ transform: `scaleX(${i === active ? segment : 0})` }"></span></span>
              </button>
            </li>
          </ol>

          <div class="hidden lg:block mt-8">
            <Transition name="cta" mode="out-in">
              <router-link :key="current.slug" :to="current.url" class="cs-cta group">
                সব {{ current.name }} দেখুন
                <span class="cs-cta-count">{{ bnNumber(current.count) }}টি</span>
                <ArrowRight class="w-4 h-4 transition-transform group-hover:translate-x-1" />
              </router-link>
            </Transition>
          </div>

          <!-- Mobile: pill tabs + overall progress -->
          <div class="lg:hidden mt-4">
            <div ref="chipRow" class="-mx-4 px-4 flex gap-2 overflow-x-auto no-scrollbar pb-1">
              <button
                v-for="(item, i) in items" :key="item.slug" type="button"
                @click="jumpTo(i)"
                :data-chip="i"
                :aria-current="i === active ? 'true' : undefined"
                :class="['cs-chip', i === active && 'is-active']"
              >{{ item.name }}<span>{{ bnNumber(item.count) }}</span></button>
            </div>
            <div class="mt-3 h-[2px] bg-[#E5DED3] rounded-full overflow-hidden" aria-hidden="true">
              <div class="h-full bg-[#111111] origin-left" :style="{ transform: `scaleX(${Math.max(0.04, progress)})` }"></div>
            </div>
          </div>
        </div>

        <!-- ================================================ RIGHT: photos -->
        <div class="lg:col-span-7 relative flex-1 lg:flex-none min-h-0 mt-4 lg:mt-0 lg:h-[72vh] lg:max-h-[700px]">
          <!-- counter -->
          <p class="hidden lg:flex absolute -top-9 right-0 items-baseline gap-1.5 text-[13px] text-[#69736E] tabular-nums" aria-hidden="true">
            <span class="font-serif text-[22px] text-[#111111]">{{ pad(active + 1) }}</span> / {{ pad(items.length) }}
          </p>

          <div
            v-for="(item, i) in items"
            :key="item.slug"
            class="absolute inset-0"
            :class="i === active ? 'pointer-events-auto' : 'pointer-events-none'"
            :aria-hidden="i !== active"
          >
            <div :class="['h-full grid gap-3 lg:gap-4', layoutClass(item.products.length)]">
              <router-link
                v-for="(p, pi) in item.products"
                :key="p.id"
                :to="`/product/${p.slug}`"
                :tabindex="i === active ? 0 : -1"
                :class="['cs-cell group', cellClass(item.products.length, pi)]"
                :style="imageStyle(i, pi)"
              >
                <img :src="p.image" :alt="p.name" :loading="i < 2 ? 'eager' : 'lazy'" decoding="async"
                     class="absolute inset-0 w-full h-full object-cover object-top transition-transform duration-[1200ms] ease-out group-hover:scale-[1.04]" />
                <span class="cs-caption">
                  <span class="min-w-0">
                    <span class="block truncate text-[13.5px] sm:text-[14.5px] font-medium">{{ p.name }}</span>
                    <span class="block tabular-nums text-[12.5px] sm:text-[13px] text-white/80">
                      {{ taka(onSale(p) ? p.sale_price : p.price) }}
                      <s v-if="onSale(p)" class="ml-1 text-white/55">{{ taka(p.price) }}</s>
                    </span>
                  </span>
                  <span class="cs-go" aria-hidden="true"><ArrowUpRight class="w-4 h-4" /></span>
                </span>
              </router-link>

              <!-- A single product gets a category card beside it instead of one lonely photo -->
              <router-link
                v-if="item.products.length === 1"
                :to="item.url"
                :tabindex="i === active ? 0 : -1"
                class="cs-cell cs-tile group"
                :style="imageStyle(i, 1)"
              >
                <span class="text-[12px] text-[#C5A880] tracking-wide">ক্যাটাগরি · {{ pad(i + 1) }}</span>
                <span class="mt-auto font-serif text-[30px] sm:text-[38px] leading-[1.1]">{{ item.name }}</span>
                <span class="mt-2 text-[13px] text-white/60">{{ bnNumber(item.count) }}টি ডিজাইন</span>
                <span class="mt-6 inline-flex items-center gap-2 text-[14px] font-medium">
                  সব দেখুন
                  <span class="w-9 h-9 rounded-full bg-[#C5A880] text-[#111111] flex items-center justify-center transition-transform group-hover:translate-x-1"><ArrowRight class="w-4 h-4" /></span>
                </span>
              </router-link>
            </div>
          </div>
        </div>

        <!-- Mobile link -->
        <div class="lg:hidden mt-4">
          <router-link :to="current.url" class="cs-cta w-full justify-center">
            সব {{ current.name }} দেখুন
            <span class="cs-cta-count">{{ bnNumber(current.count) }}টি</span>
            <ArrowRight class="w-4 h-4" />
          </router-link>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { ArrowRight, ArrowUpRight } from 'lucide-vue-next';
import { taka, bnNumber } from '../../utils/bn';
import { getLenis } from '../../utils/smoothScroll';

const props = defineProps({
  content: { type: Object, default: () => ({}) },
  items: { type: Array, default: () => [] },
});

const sectionEl = ref(null);
const chipRow = ref(null);
const active = ref(0);
const progress = ref(0);              // 0–1 through the whole section

const current = computed(() => props.items[active.value] || props.items[0] || {});
// Each category gets ~65% of a screen of scrolling; the pinned stage is one screen tall.
const sectionHeight = computed(() => `calc(100svh + ${Math.max(0, props.items.length - 1) * 65}svh)`);
// 0–1 through the active category (drives its underline)
const segment = computed(() => {
  const n = props.items.length;
  return n ? Math.min(1, Math.max(0, progress.value * n - active.value)) : 0;
});

const pad = (n) => bnNumber(String(n).padStart(2, '0'));
const onSale = (p) => Number(p.sale_price) > 0 && Number(p.sale_price) < Number(p.price);

// --- layouts: 1 = photo + category card, 2 = side by side, 3 = one tall + two stacked, 4 = 2×2
const layoutClass = (n) => ({
  1: 'grid-cols-2 lg:grid-cols-[1.4fr_1fr]',
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
    clipPath: on ? 'inset(0% 0% 0% 0% round 18px)' : (off > 0 ? 'inset(18% 0% 0% 0% round 18px)' : 'inset(0% 0% 18% 0% round 18px)'),
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
  if (idx !== active.value) active.value = idx;
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
/* Brand: ink #111111, ivory #F7F5F0, sand line #E5DED3, muted #69736E, gold #C5A880 */
.cs-kicker { display: flex; align-items: center; gap: 10px; font-size: 13px; font-weight: 500; color: #8C7A5F; }
.cs-kicker > span { width: 28px; height: 1px; background: #C5A880; }

/* index rows */
.cs-row {
  position: relative; width: 100%; display: grid; grid-template-columns: 34px 1fr auto; align-items: baseline;
  gap: 12px; padding: 13px 0 14px; text-align: left; transition: padding .5s cubic-bezier(.22,1,.36,1);
}
.cs-num { font-size: 12px; color: #A89B8A; font-variant-numeric: tabular-nums; transition: color .4s; }
.cs-name {
  font-family: var(--font-serif); font-size: 24px; line-height: 1.2; color: rgba(17, 17, 17, .3);
  transition: color .5s, font-size .5s cubic-bezier(.22,1,.36,1);
}
.cs-count {
  font-size: 12px; color: #69736E; padding: 2px 9px; border-radius: 999px; border: 1px solid #E5DED3;
  opacity: 0; transform: translateX(-6px); transition: opacity .4s, transform .4s;
}
.cs-row:hover .cs-name { color: rgba(17, 17, 17, .6); }
.cs-row.is-active { padding: 16px 0 18px; }
.cs-row.is-active .cs-num { color: #8C7A5F; }
.cs-row.is-active .cs-name { color: #111111; font-size: 32px; }
.cs-row.is-active .cs-count { opacity: 1; transform: none; }
.cs-row:focus-visible { outline: 2px solid #111111; outline-offset: 2px; }
.cs-bar { position: absolute; left: 0; right: 0; bottom: -1px; height: 1px; overflow: hidden; }
.cs-bar > span { display: block; height: 100%; background: #111111; transform-origin: left; transition: transform .25s linear; }
@media (min-width: 1280px) { .cs-name { font-size: 26px; } .cs-row.is-active .cs-name { font-size: 36px; } }

/* call to action */
.cs-cta {
  display: inline-flex; align-items: center; gap: 10px; height: 50px; padding: 0 22px; border-radius: 999px;
  background: #111111; color: #F7F5F0; font-size: 14px; font-weight: 500; transition: background .2s;
}
.cs-cta:hover { background: #000; }
.cs-cta-count { font-size: 12px; padding: 1px 8px; border-radius: 999px; background: rgba(247, 245, 240, .14); }

/* mobile chips */
.cs-chip {
  flex-shrink: 0; display: inline-flex; align-items: center; gap: 6px; height: 36px; padding: 0 14px; border-radius: 999px;
  font-size: 13.5px; color: #111111; background: #fff; border: 1px solid #E5DED3; transition: background .25s, color .25s, border-color .25s;
}
.cs-chip > span { font-size: 11px; color: #69736E; }
.cs-chip.is-active { background: #111111; border-color: #111111; color: #F7F5F0; }
.cs-chip.is-active > span { color: #C5A880; }

/* photo cells */
.cs-cell { position: relative; overflow: hidden; border-radius: 18px; background: #EFEBE4; }
.cs-cell:focus-visible { outline: 2px solid #111111; outline-offset: 3px; }
.cs-caption {
  position: absolute; inset: auto 0 0 0; display: flex; align-items: flex-end; justify-content: space-between; gap: 12px;
  padding: 44px 14px 14px; color: #fff; background: linear-gradient(to top, rgba(17, 17, 17, .72), rgba(17, 17, 17, 0));
}
.cs-go {
  flex-shrink: 0; width: 34px; height: 34px; border-radius: 999px; display: flex; align-items: center; justify-content: center;
  background: #F7F5F0; color: #111111; transition: transform .3s cubic-bezier(.22,1,.36,1);
}
.group:hover .cs-go { transform: rotate(45deg); }
@media (min-width: 1024px) { .cs-caption { padding: 56px 18px 18px; } }

.cs-tile { display: flex; flex-direction: column; padding: 22px; background: #111111; color: #F7F5F0; }
@media (min-width: 1024px) { .cs-tile { padding: 32px; } }

.cta-enter-active, .cta-leave-active { transition: opacity .3s ease, transform .3s ease; }
.cta-enter-from { opacity: 0; transform: translateY(8px); }
.cta-leave-to { opacity: 0; transform: translateY(-8px); }
@media (prefers-reduced-motion: reduce) {
  .cs-row, .cs-name, .cs-count, .cs-bar > span { transition: none; }
}
</style>
