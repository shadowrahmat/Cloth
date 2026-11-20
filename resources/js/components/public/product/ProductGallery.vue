<template>
  <div :class="['pg', { 'pg--bento': images.length >= 3 }]">
    <div class="pg-stage">
    <!-- Main image: a native scroll-snap track, so phones can swipe it (no carousel library). -->
    <div class="pg-main">
      <div
        ref="track"
        class="pg-track"
        tabindex="-1"
        aria-roledescription="ছবির গ্যালারি"
        :aria-label="`${name} — ছবি`"
        @scroll.passive="onScroll"
      >
        <div v-for="(img, i) in images" :key="img.src + i" class="pg-slide" :aria-hidden="i !== index">
          <img
            :src="img.src"
            :alt="img.alt || (images.length > 1 ? `${name} — ছবি ${bnNumber(i + 1)}` : name)"
            :loading="i === 0 ? 'eager' : 'lazy'"
            :fetchpriority="i === 0 ? 'high' : 'auto'"
            decoding="async"
            draggable="false"
          />
        </div>
      </div>

      <span v-if="saleLabel" class="pg-badge">{{ saleLabel }}</span>
      <span v-if="caption" class="pg-caption" aria-hidden="true">{{ caption }}</span>

      <template v-if="images.length > 1">
        <button type="button" class="pg-arrow pg-arrow--prev" aria-label="আগের ছবি" @click="step(-1)">
          <ChevronLeft :size="20" :stroke-width="1.75" />
        </button>
        <button type="button" class="pg-arrow pg-arrow--next" aria-label="পরের ছবি" @click="step(1)">
          <ChevronRight :size="20" :stroke-width="1.75" />
        </button>
      </template>

      <slot name="overlay" />
    </div>

    <!-- Large screens: the next two photos beside the main one (bento). -->
    <div v-if="images.length >= 3" class="pg-side" aria-hidden="true">
      <button
        v-for="n in 2"
        :key="'s' + n"
        type="button"
        tabindex="-1"
        class="pg-side-item"
        @click="go((index + n) % images.length)"
      >
        <img :src="images[(index + n) % images.length].src" alt="" loading="lazy" decoding="async" draggable="false" />
      </button>
    </div>
    </div>

    <!-- Thumbnails: one tidy row under the main image (scrolls sideways when there are many). -->
    <div v-if="images.length > 1" ref="thumbRow" class="pg-thumbs">
      <button
        v-for="(img, i) in images"
        :key="'t' + img.src + i"
        type="button"
        :class="['pg-thumb', { 'is-active': i === index }]"
        :aria-label="`ছবি ${bnNumber(i + 1)} / ${bnNumber(images.length)} দেখুন`"
        :aria-current="i === index ? 'true' : undefined"
        @click="go(i)"
      >
        <img :src="img.src" alt="" loading="lazy" decoding="async" draggable="false" />
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick } from 'vue';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { bnNumber } from '../../../utils/bn';

const props = defineProps({
  /** [{ src, alt? }] */
  images: { type: Array, default: () => [] },
  name: { type: String, default: '' },
  /** Text of the sale badge; empty = no badge. */
  saleLabel: { type: String, default: '' },
});

const track = ref(null);
const thumbRow = ref(null);
const index = ref(0);

const caption = computed(() => {
  const img = props.images[index.value];
  if (img?.alt) return img.alt;
  return props.images.length > 1 ? `${bnNumber(index.value + 1)} / ${bnNumber(props.images.length)}` : '';
});

const reduceMotion = () => window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;

/** Show image i: slide the track and keep the active thumbnail visible inside its row. */
function go(i, smooth = true) {
  index.value = i;
  const el = track.value;
  if (el) el.scrollTo({ left: i * el.clientWidth, behavior: smooth && !reduceMotion() ? 'smooth' : 'auto' });
  revealThumb(i);
}
const step = (d) => go((index.value + d + props.images.length) % props.images.length);

function revealThumb(i) {
  const row = thumbRow.value;
  const btn = row?.children[i];
  if (!row || !btn) return;
  // Scroll only the thumbnail row, never the page.
  const left = btn.offsetLeft - row.offsetLeft;
  if (left < row.scrollLeft) row.scrollTo({ left: left - 2, behavior: 'smooth' });
  else if (left + btn.offsetWidth > row.scrollLeft + row.clientWidth) row.scrollTo({ left: left + btn.offsetWidth - row.clientWidth + 2, behavior: 'smooth' });
}

// Swiping the track updates the active thumbnail.
let raf = 0;
function onScroll() {
  cancelAnimationFrame(raf);
  raf = requestAnimationFrame(() => {
    const el = track.value;
    if (!el || !el.clientWidth) return;
    const i = Math.round(el.scrollLeft / el.clientWidth);
    if (i !== index.value && i >= 0 && i < props.images.length) {
      index.value = i;
      revealThumb(i);
    }
  });
}

// A new image set (e.g. another colour) starts from its first image.
watch(() => props.images.map((i) => i.src).join('|'), () => {
  nextTick(() => go(0, false));
});

defineExpose({ go });
</script>

<style scoped>
.pg { display: flex; flex-direction: column; gap: 12px; min-width: 0; }
.pg-stage { display: grid; grid-template-columns: minmax(0, 1fr); gap: 12px; }

/* Bento (1280px+): main photo + a column with the next two photos, same total height. */
.pg-side { display: none; }
@media (min-width: 1280px) {
  .pg--bento .pg-stage { grid-template-columns: minmax(0, 2fr) minmax(0, 1fr); }
  .pg--bento .pg-side { display: flex; flex-direction: column; gap: 12px; min-height: 0; }
}
.pg-side-item {
  position: relative; flex: 1 1 0; min-height: 0;
  padding: 0; border: 0; cursor: pointer;
  border-radius: 20px; overflow: hidden;
  background: var(--pp-neutral, #EFEBE4);
}
.pg-side-item img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; object-position: center top; transition: transform .6s cubic-bezier(0.22, 1, 0.36, 1); }
.pg-side-item:hover img { transform: scale(1.04); }

/* ---------------------------------------------------------------- main */
.pg-main {
  position: relative;
  aspect-ratio: 3 / 4;              /* the shape of the store's product photos, so nothing is cropped */
  border-radius: 20px;
  overflow: hidden;
  background: var(--pp-neutral, #EFEBE4);
}
.pg-track {
  display: flex;
  width: 100%;
  height: 100%;
  overflow-x: auto;
  overflow-y: hidden;
  scroll-snap-type: x mandatory;
  overscroll-behavior-x: contain;
  scrollbar-width: none;
  outline: none;
}
.pg-track::-webkit-scrollbar { display: none; }
.pg-slide { flex: 0 0 100%; height: 100%; scroll-snap-align: start; scroll-snap-stop: always; }
.pg-slide img { width: 100%; height: 100%; object-fit: cover; object-position: center top; user-select: none; -webkit-user-drag: none; }

.pg-badge {
  position: absolute; top: 16px; left: 16px;
  padding: 5px 10px; border-radius: 9999px;
  background: var(--pp-sale, #111111); color: #FFFFFF;
  font-size: 12px; font-weight: 700; line-height: 1.2;
  pointer-events: none;
}
.pg-caption {
  position: absolute; bottom: 16px; left: 16px;
  padding: 4px 10px; border-radius: 9999px;
  background: rgba(255, 255, 255, 0.9);
  color: var(--pp-text-2, rgba(17, 17, 17, 0.75));
  font-size: 12px; font-weight: 500; line-height: 1.4;
  font-variant-numeric: tabular-nums;
  pointer-events: none;
}

/* Prev / next: visible on hover (desktop), always on touch-less keyboards via focus. */
.pg-arrow {
  position: absolute; top: 50%; z-index: 1;
  width: 40px; height: 40px; margin-top: -20px;
  display: flex; align-items: center; justify-content: center;
  border: 0; border-radius: 9999px;
  background: rgba(255, 255, 255, 0.9); color: var(--pp-text, #111111);
  cursor: pointer;
  opacity: 0; transition: opacity .2s ease, transform .15s ease;
}
.pg-arrow--prev { left: 12px; }
.pg-arrow--next { right: 12px; }
.pg-main:hover .pg-arrow, .pg-arrow:focus-visible { opacity: 1; }
.pg-arrow:active { transform: scale(0.94); }
.pg-arrow:focus-visible { outline: 2px solid var(--pp-text, #111111); outline-offset: 2px; }
@media (hover: none) { .pg-arrow { display: none; } }   /* phones swipe instead */

/* ---------------------------------------------------------- thumbnails */
.pg-thumbs {
  display: grid;
  grid-auto-flow: column;
  grid-auto-columns: calc((100% - 5 * 10px) / 6);   /* 6 across, the rest scroll sideways */
  gap: 10px;
  padding: 2px;                                     /* room for the selected border + focus ring */
  margin: -2px;
  overflow-x: auto;
  scroll-snap-type: x proximity;
  scrollbar-width: none;
}
.pg-thumbs::-webkit-scrollbar { display: none; }
.pg-thumb {
  aspect-ratio: 3 / 4;
  padding: 0;
  border-radius: 10px;
  border: 2px solid transparent;
  overflow: hidden;
  background: var(--pp-neutral, #EFEBE4);
  cursor: pointer;
  scroll-snap-align: start;
  transition: border-color .2s ease;
}
.pg-thumb img { width: 100%; height: 100%; object-fit: cover; object-position: center top; border-radius: 8px; transition: opacity .2s ease; }
.pg-thumb:not(.is-active) img { opacity: .75; }
.pg-thumb:hover img { opacity: 1; }
.pg-thumb.is-active { border-color: var(--pp-text, #111111); }
.pg-thumb:focus-visible { outline: 2px solid var(--pp-text, #111111); outline-offset: 2px; }

@media (min-width: 1280px) {
  .pg--bento .pg-thumbs { grid-auto-columns: calc((100% - 7 * 10px) / 8); }   /* 8 across under the bento */
}
@media (max-width: 639.98px) {
  .pg { gap: 10px; }
  .pg-main { border-radius: 16px; }
  .pg-thumbs { grid-auto-columns: 64px; gap: 8px; }
}

@media (prefers-reduced-motion: reduce) {
  .pg-thumb, .pg-thumb img, .pg-arrow { transition: none; }
}
</style>
