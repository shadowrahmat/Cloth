<template>
  <section
    v-if="items.length"
    class="cf"
    aria-roledescription="carousel"
    :aria-labelledby="headingId"
    @keydown="onKey"
  >
    <!-- Header -->
    <header class="cf-head">
      <p :class="['cf-eyebrow', isBn(eyebrow) && 'is-bn']">{{ eyebrow }}</p>
      <h2 :id="headingId" :class="['cf-title', isBn(heading) && 'is-bn']">
        <template v-for="(part, i) in headingParts" :key="i">
          <em v-if="part.hi" :class="['cf-hi', isBn(part.text) && 'no-italic']">{{ part.text }}</em>
          <template v-else>{{ part.text }}</template>
        </template>
      </h2>
    </header>

    <!-- ===================================================== tablet / desktop: 3D stage -->
    <template v-if="!isPhone">
      <div
        ref="stageEl"
        class="cf-stage"
        @pointerdown="onPointerDown"
        @pointerup="onPointerUp"
        @pointercancel="dragStart = null"
      >
        <button
          v-for="(p, i) in items"
          :key="p.id"
          type="button"
          :class="['cf-card', jumping.has(p.id) && 'is-jumping']"
          :style="cardStyle(i)"
          :aria-label="offsetOf(i) === 0 ? `${p.name} — বাছাই করা, পণ্যের পেজ খুলুন` : `${p.name} দেখুন`"
          :aria-current="offsetOf(i) === 0 ? 'true' : undefined"
          :aria-hidden="isHidden(i) ? 'true' : undefined"
          :tabindex="isHidden(i) ? -1 : 0"
          :inert="isHidden(i) || undefined"
          @click="onCardClick(i)"
        >
          <img
            :src="p.image"
            :alt="p.name"
            :loading="Math.abs(offsetOf(i)) <= 1 ? 'eager' : 'lazy'"
            decoding="async"
            draggable="false"
          />
          <span v-if="p.badge" class="cf-badge">{{ p.badge === 'sale' ? 'সেল' : 'নতুন' }}</span>
        </button>
      </div>

      <!-- Details of the active product -->
      <div class="cf-details">
        <div class="cf-live" aria-live="polite" aria-atomic="true">
          <h3 class="cf-name">{{ current.name }}</h3>
          <div class="cf-meta">
            <span class="cf-price">{{ taka(current.price) }}</span>
            <s v-if="current.compare_at_price" class="cf-compare">{{ taka(current.compare_at_price) }}</s>
            <template v-if="current.colors?.length">
              <span class="cf-divider" aria-hidden="true"></span>
              <span class="cf-dots" :aria-label="'রং: ' + current.colors.map((c) => c.name).join(', ')">
                <span v-for="c in current.colors" :key="c.name" :class="['cf-dot', isLight(c.code) && 'is-light']" :style="{ background: c.code }" :title="c.name"></span>
              </span>
            </template>
          </div>
        </div>
        <div class="cf-actions">
          <button type="button" class="cf-round cf-prev" :disabled="!wraps && active === 0" aria-label="আগের পণ্য" @click="step(-1)">
            <ChevronLeft :size="22" :stroke-width="1.75" />
          </button>
          <button
            type="button"
            class="cf-add"
            :disabled="!current.in_stock || adding"
            :title="needsChoice(current) ? 'সাইজ/রং বাছাই করতে পণ্যের পেজ খুলবে' : undefined"
            @click="addProduct(current)"
          >
            <ShoppingBag :size="18" :stroke-width="1.9" />
            {{ !current.in_stock ? 'স্টক শেষ' : adding ? 'যোগ হচ্ছে…' : 'কার্টে যোগ করুন' }}
          </button>
          <button type="button" class="cf-round cf-next" :disabled="!wraps && active === items.length - 1" aria-label="পরের পণ্য" @click="step(1)">
            <ChevronRight :size="22" :stroke-width="1.75" />
          </button>
        </div>
      </div>
    </template>

    <!-- ===================================================== phones: swipe row -->
    <template v-else>
      <ul class="cf-row" data-lenis-prevent>
        <li v-for="(p, i) in items" :key="p.id" class="cf-slide">
          <div class="cf-slide-card">
            <router-link :to="`/product/${p.slug}`" class="cf-slide-link" :aria-label="p.name">
              <img :src="p.image" :alt="p.name" :loading="i < 2 ? 'eager' : 'lazy'" decoding="async" />
              <span v-if="p.badge" class="cf-badge">{{ p.badge === 'sale' ? 'সেল' : 'নতুন' }}</span>
            </router-link>
            <button
              type="button"
              class="cf-plus"
              :disabled="!p.in_stock"
              :aria-label="p.in_stock ? `${p.name} কার্টে যোগ করুন` : `${p.name} — স্টক শেষ`"
              @click="addProduct(p)"
            >
              <Plus :size="22" :stroke-width="2" />
            </button>
          </div>
          <p class="cf-slide-name">{{ p.name }}</p>
          <p class="cf-slide-price">
            <span>{{ taka(p.price) }}</span>
            <s v-if="p.compare_at_price">{{ taka(p.compare_at_price) }}</s>
          </p>
        </li>
      </ul>
      <p class="cf-hint"><MoveHorizontal :size="15" :stroke-width="1.75" aria-hidden="true" /> আরও দেখতে সোয়াইপ করুন</p>
    </template>

    <!-- "added to cart" toast (never takes focus) -->
    <Teleport to="body">
      <Transition name="cf-toast">
        <div v-if="toast" class="cf-toast" role="status">
          <span class="cf-toast-text">{{ toast }}</span>
          <template v-if="toastOk">
            <span aria-hidden="true">·</span>
            <button type="button" class="cf-toast-link" @click="openCart">কার্ট দেখুন</button>
          </template>
        </div>
      </Transition>
    </Teleport>
  </section>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { useRouter } from 'vue-router';
import { ChevronLeft, ChevronRight, ShoppingBag, Plus, MoveHorizontal } from 'lucide-vue-next';
import { useCartStore } from '../../stores/cart';
import { taka } from '../../utils/bn';

const props = defineProps({
  content: { type: Object, default: () => ({}) },
  /** products from the homepage API (section.carousel) */
  products: { type: Array, default: () => [] },
});

const router = useRouter();
const cart = useCartStore();
const headingId = `cf-h-${Math.random().toString(36).slice(2, 8)}`;

const items = computed(() => props.products.slice(0, 12));
const eyebrow = computed(() => props.content.eyebrow || 'সদ্য এসেছে');
const heading = computed(() => props.content.heading || 'এই সপ্তাহের পছন্দ');
const headingParts = computed(() => {
  const hi = (props.content.highlight ?? 'পছন্দ').trim();
  const h = heading.value;
  const at = hi ? h.lastIndexOf(hi) : -1;
  if (at === -1) return [{ text: h }];
  return [{ text: h.slice(0, at) }, { text: hi, hi: true }, { text: h.slice(at + hi.length) }].filter((p) => p.text);
});
// Bangla has no italic and suffers from wide tracking, so those get gentler type settings.
const isBn = (s) => /[ঀ-৿]/.test(s || '');

// ------------------------------------------------------------------ breakpoints
const mq = (q) => (typeof window !== 'undefined' ? window.matchMedia(q) : null);
const phoneMq = mq('(max-width: 767.98px)');
const tabletMq = mq('(min-width: 768px) and (max-width: 1023.98px)');
const isPhone = ref(Boolean(phoneMq?.matches));
const isTablet = ref(Boolean(tabletMq?.matches));
const syncMq = () => {
  isPhone.value = Boolean(phoneMq?.matches);
  isTablet.value = Boolean(tabletMq?.matches);
};

// ------------------------------------------------------------------ position maths
const active = ref(0);
const wraps = computed(() => items.value.length >= 5);
const current = computed(() => items.value[active.value] || {});
const maxVisible = computed(() => (isTablet.value ? 1 : 2));

/** Distance from the active card, the shortest way round the circle. */
const offsetOf = (i) => {
  const n = items.value.length;
  let d = i - active.value;
  if (!wraps.value) return d;
  d = ((d % n) + n) % n;
  if (d > n / 2) d -= n;
  return d;
};
const isHidden = (i) => Math.abs(offsetOf(i)) > maxVisible.value;
const cardStyle = (i) => {
  const d = offsetOf(i);
  const ad = Math.abs(d);
  return {
    '--d': d,
    '--ad': ad,
    zIndex: 30 - ad * 10,
    opacity: ad > maxVisible.value ? 0 : ad === 2 ? 0.55 : 1,
    filter: ad >= 1 ? 'brightness(0.72)' : 'brightness(1)',
  };
};

// A card that wraps from one end to the other must not fly across the stage.
const jumping = ref(new Set());
watch(active, async (now, before) => {
  const n = items.value.length;
  const set = new Set();
  items.value.forEach((p, i) => {
    let a = i - before;
    a = ((a % n) + n) % n;
    if (a > n / 2) a -= n;
    if (wraps.value && Math.abs(offsetOf(i) - a) > 1) set.add(p.id);
  });
  jumping.value = set;
  await nextTick();
  requestAnimationFrame(() => requestAnimationFrame(() => (jumping.value = new Set())));
});

const step = (dir) => {
  const n = items.value.length;
  if (!n) return;
  if (wraps.value) active.value = (active.value + dir + n) % n;
  else active.value = Math.min(n - 1, Math.max(0, active.value + dir));
};

// ------------------------------------------------------------------ input
let suppressClick = false;
const onCardClick = (i) => {
  if (suppressClick) return;
  if (offsetOf(i) === 0) router.push(`/product/${items.value[i].slug}`);
  else active.value = i;
};

/** Arrow keys only while focus is inside this section. */
const onKey = (e) => {
  if (isPhone.value || e.altKey || e.ctrlKey || e.metaKey) return;
  if (e.key === 'ArrowRight') { e.preventDefault(); step(1); }
  else if (e.key === 'ArrowLeft') { e.preventDefault(); step(-1); }
};

const SWIPE = 50;
let dragStart = null;
const onPointerDown = (e) => {
  if (e.pointerType === 'mouse' && e.button !== 0) return;
  dragStart = { x: e.clientX, y: e.clientY };
};
const onPointerUp = (e) => {
  if (!dragStart) return;
  const dx = e.clientX - dragStart.x;
  const dy = e.clientY - dragStart.y;
  dragStart = null;
  if (Math.abs(dx) >= SWIPE && Math.abs(dx) > Math.abs(dy)) {
    step(dx < 0 ? 1 : -1);
    suppressClick = true; // the click that follows a drag must not open/select a card
    setTimeout(() => (suppressClick = false), 0);
  }
};
// Trackpad two-finger swipe arrives as horizontal wheel movement.
let wheelSum = 0;
let wheelLock = 0;
let wheelReset = 0;
const onWheel = (e) => {
  if (Math.abs(e.deltaX) <= Math.abs(e.deltaY)) return;
  e.preventDefault();
  if (Date.now() < wheelLock) return;
  wheelSum += e.deltaX;
  clearTimeout(wheelReset);
  wheelReset = setTimeout(() => (wheelSum = 0), 180);
  if (Math.abs(wheelSum) >= SWIPE) {
    step(wheelSum > 0 ? 1 : -1);
    wheelSum = 0;
    wheelLock = Date.now() + 450;
  }
};
const stageEl = ref(null);
// Vue's @wheel is passive; re-bind as non-passive so a sideways swipe doesn't trigger "back".
watch(stageEl, (el, old) => {
  old?.removeEventListener('wheel', onWheel);
  el?.addEventListener('wheel', onWheel, { passive: false });
});

// ------------------------------------------------------------------ cart
const adding = ref(false);
const toast = ref('');
const toastOk = ref(true);
let toastTimer = 0;
const showToast = (text, ok = true) => {
  toast.value = text;
  toastOk.value = ok;
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => (toast.value = ''), 2500);
};
const openCart = () => {
  toast.value = '';
  cart.openDrawer();
};

/** More than one buyable size/colour = the buyer has to choose on the product page. */
const needsChoice = (p) => (p.variants || []).filter((v) => v.stock > 0).length > 1 || (p.variants || []).length > 1;

const addProduct = async (p) => {
  if (!p?.id || !p.in_stock) return;
  if (needsChoice(p)) {
    router.push(`/product/${p.slug}`);
    return;
  }
  const v = (p.variants || [])[0] || null;
  adding.value = true;
  try {
    await cart.addToCart(p.id, v?.id ?? null, 1, v?.color || null, v?.size || null, { openDrawer: false });
    showToast(`${p.name} কার্টে যোগ হয়েছে`);
  } catch (e) {
    showToast(e.response?.data?.message || 'কার্টে যোগ করা যায়নি', false);
  } finally {
    adding.value = false;
  }
};

// ------------------------------------------------------------------ misc
const isLight = (hex) => {
  const m = /^#?([0-9a-f]{6})$/i.exec(hex || '');
  if (!m) return false;
  const n = parseInt(m[1], 16);
  const [r, g, b] = [(n >> 16) & 255, (n >> 8) & 255, n & 255];
  return (0.2126 * r + 0.7152 * g + 0.0722 * b) / 255 > 0.8;
};

onMounted(() => {
  phoneMq?.addEventListener('change', syncMq);
  tabletMq?.addEventListener('change', syncMq);
});
onBeforeUnmount(() => {
  phoneMq?.removeEventListener('change', syncMq);
  tabletMq?.removeEventListener('change', syncMq);
  stageEl.value?.removeEventListener('wheel', onWheel);
  clearTimeout(toastTimer);
});
</script>

<style scoped>
/* Brand: darkest #111111 (footer), page ivory #F7F5F0, highlight gold #C5A880, muted taupe #A89B8A.
   Gold on #111 = 8.9:1, taupe on #111 = 6.9:1 — both pass 4.5:1. */
.cf {
  --cf-bg: #111111;
  --cf-text: #F7F5F0;
  --cf-muted: #A89B8A;
  --cf-hi: #C5A880;
  --cf-card-w: 340px;
  --cf-card-h: 440px;
  --cf-shift: 280px;
  --cf-stage-h: 470px;
  --cf-rot: -24deg;
  position: relative;
  background: var(--cf-bg);
  color: var(--cf-text);
  padding: 120px 0 112px;
  overflow: hidden;
  overflow-x: clip;
}

/* ---------- header */
.cf-head { text-align: center; padding: 0 16px; }
.cf-eyebrow { font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.16em; color: var(--cf-hi); }
.cf-eyebrow.is-bn { letter-spacing: 0.02em; }
.cf-title {
  margin-top: 14px; font-family: var(--font-serif); font-weight: 400;
  font-size: 104px; line-height: 0.9; letter-spacing: -0.04em; color: var(--cf-text);
}
.cf-title.is-bn { letter-spacing: -0.01em; }
.cf-hi { color: var(--cf-hi); font-style: italic; }
.cf-hi.no-italic { font-style: normal; }

/* ---------- 3D stage */
.cf-stage {
  position: relative; margin-top: 64px; height: var(--cf-stage-h); width: 100%;
  perspective: 1600px; touch-action: pan-y; user-select: none;
}
.cf-card {
  position: absolute; top: 0; left: 50%;
  width: var(--cf-card-w); height: var(--cf-card-h); margin-left: calc(var(--cf-card-w) / -2);
  border-radius: 34px; overflow: hidden; padding: 0; border: 0; cursor: pointer;
  background: #EFEBE4;                        /* neutral behind cut-out product shots */
  box-shadow: 0 34px 80px rgba(0, 0, 0, 0.5);
  transform:
    translate3d(calc(var(--d) * var(--cf-shift)), calc(var(--ad) * 26px), 0)
    scale(calc(1 - var(--ad) * 0.14))
    rotateY(calc(var(--d) * var(--cf-rot)));
  transition: transform 0.8s cubic-bezier(.2, .7, .2, 1), opacity 0.6s ease, filter 0.6s ease;
  will-change: transform;
}
.cf-card.is-jumping { transition: none; }
.cf-card[aria-hidden='true'] { pointer-events: none; }
.cf-card img { width: 100%; height: 100%; object-fit: cover; display: block; -webkit-user-drag: none; }
.cf-card:focus-visible { outline: 3px solid var(--cf-hi); outline-offset: 4px; }
.cf-badge {
  position: absolute; top: 18px; left: 18px; padding: 6px 12px; border-radius: 999px;
  background: #111111; color: var(--cf-text);
  font-size: 11px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; line-height: 1;
}

/* ---------- details */
.cf-details { margin-top: 36px; display: flex; flex-direction: column; align-items: center; gap: 14px; padding: 0 16px; text-align: center; }
.cf-live { display: flex; flex-direction: column; align-items: center; gap: 14px; }
.cf-name { font-family: var(--font-serif); font-weight: 400; font-size: 44px; line-height: 1.1; color: var(--cf-text); max-width: 900px; }
.cf-meta { display: flex; align-items: center; justify-content: center; gap: 12px; flex-wrap: wrap; }
.cf-price { font-size: 18px; font-weight: 700; }
.cf-compare { font-size: 15px; color: var(--cf-muted); }
.cf-divider { width: 1px; height: 18px; background: rgba(247, 245, 240, 0.25); }
.cf-dots { display: inline-flex; gap: 6px; }
.cf-dot { width: 14px; height: 14px; border-radius: 999px; }
.cf-dot.is-light { box-shadow: inset 0 0 0 1px rgba(17, 17, 17, 0.35), 0 0 0 1px rgba(247, 245, 240, 0.35); }

.cf-actions { margin-top: 8px; display: flex; align-items: center; gap: 12px; }
.cf-round {
  width: 56px; height: 56px; border-radius: 999px; display: inline-flex; align-items: center; justify-content: center;
  transition: background 0.2s, border-color 0.2s, opacity 0.2s, transform 0.2s;
}
.cf-prev { border: 1px solid rgba(247, 245, 240, 0.3); background: transparent; color: var(--cf-text); }
.cf-prev:hover:not(:disabled) { border-color: rgba(247, 245, 240, 0.7); }
.cf-next { background: var(--cf-text); color: #111111; }
.cf-next:hover:not(:disabled) { background: #FFFFFF; }
.cf-round:disabled { opacity: 0.3; cursor: not-allowed; }
.cf-add {
  height: 56px; padding: 0 30px; border-radius: 999px; display: inline-flex; align-items: center; gap: 10px;
  background: var(--cf-hi); color: #111111; font-size: 15px; font-weight: 600;
  transition: background 0.2s, transform 0.2s, opacity 0.2s;
}
.cf-add:hover:not(:disabled) { background: #D3BB98; }
.cf-add:disabled { opacity: 0.55; cursor: not-allowed; }
.cf-round:focus-visible, .cf-add:focus-visible { outline: 2px solid var(--cf-hi); outline-offset: 3px; }

/* ---------- 1024–1279 */
@media (max-width: 1279.98px) {
  .cf { --cf-card-w: 300px; --cf-card-h: 390px; --cf-shift: 230px; --cf-stage-h: 420px; padding: 104px 0 96px; }
  .cf-title { font-size: 88px; }
}
/* ---------- 768–1023: only active and ±1 */
@media (max-width: 1023.98px) {
  .cf { --cf-card-w: 260px; --cf-card-h: 340px; --cf-shift: 200px; --cf-stage-h: 380px; padding: 88px 0 80px; }
  .cf-title { font-size: 72px; }
  .cf-name { font-size: 36px; }
}

/* ---------- phones: swipe row */
@media (max-width: 767.98px) {
  .cf { padding: 64px 0 56px; }
  .cf-title { font-size: 44px; line-height: 1.05; }
}
.cf-row {
  margin-top: 40px; display: flex; gap: 10px; padding: 0 14vw 8px; list-style: none;
  overflow-x: auto; scroll-snap-type: x mandatory; scrollbar-width: none; -webkit-overflow-scrolling: touch;
  overscroll-behavior-x: contain;
}
.cf-row::-webkit-scrollbar { display: none; }
.cf-slide { flex: 0 0 72vw; scroll-snap-align: center; }
.cf-slide-card { position: relative; aspect-ratio: 340 / 440; border-radius: 28px; overflow: hidden; background: #EFEBE4; box-shadow: 0 24px 50px rgba(0, 0, 0, 0.45); }
.cf-slide-link { display: block; width: 100%; height: 100%; }
.cf-slide-link img { width: 100%; height: 100%; object-fit: cover; display: block; }
.cf-plus {
  position: absolute; right: 14px; bottom: 14px; width: 52px; height: 52px; border-radius: 999px;
  display: flex; align-items: center; justify-content: center; background: var(--cf-text); color: #111111;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
}
.cf-plus:disabled { opacity: 0.5; }
.cf-slide-name { margin-top: 14px; font-family: var(--font-serif); font-size: 22px; line-height: 1.2; color: var(--cf-text); }
.cf-slide-price { margin-top: 6px; display: flex; gap: 8px; align-items: baseline; font-size: 15px; font-weight: 700; }
.cf-slide-price s { font-size: 13px; font-weight: 400; color: var(--cf-muted); }
.cf-hint { margin-top: 18px; display: flex; align-items: center; justify-content: center; gap: 6px; font-size: 13px; color: var(--cf-muted); }

/* The slide nearest the centre is full size; others shrink and fade with the scroll. */
@supports (animation-timeline: view()) {
  .cf-slide { animation: cf-focus linear both; animation-timeline: view(inline); }
  @keyframes cf-focus {
    0%, 100% { transform: scale(0.86); opacity: 0.55; }
    50% { transform: scale(1); opacity: 1; }
  }
}

/* ---------- toast */
.cf-toast {
  position: fixed; left: 50%; bottom: 32px; z-index: 65; transform: translateX(-50%);
  display: flex; align-items: center; gap: 8px; max-width: calc(100vw - 32px);
  padding: 11px 20px; border-radius: 999px; background: #111111; color: #F7F5F0;
  font-size: 14px; box-shadow: 0 16px 40px -12px rgba(0, 0, 0, 0.55), 0 0 0 1px rgba(247, 245, 240, 0.12);
}
.cf-toast-text { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.cf-toast-link { color: #C5A880; font-weight: 600; white-space: nowrap; text-decoration: underline; text-underline-offset: 3px; }
@media (max-width: 1023.98px) { .cf-toast { bottom: calc(84px + env(safe-area-inset-bottom)); } }
.cf-toast-enter-active, .cf-toast-leave-active { transition: opacity 0.25s ease, transform 0.25s ease; }
.cf-toast-enter-from, .cf-toast-leave-to { opacity: 0; transform: translate(-50%, 10px); }

/* ---------- reduced motion: no 3D turn, quick slide + fade */
@media (prefers-reduced-motion: reduce) {
  .cf { --cf-rot: 0deg; }
  .cf-card { transition: transform 0.25s ease, opacity 0.25s ease, filter 0.25s ease; }
  .cf-slide { animation: none; }
  .cf-toast-enter-active, .cf-toast-leave-active { transition: opacity 0.15s; }
}
</style>
