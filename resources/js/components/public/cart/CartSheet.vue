<template>
  <Teleport to="body">
    <Transition name="cs-fade">
      <div v-if="cart.isDrawerOpen" class="cs-overlay" aria-hidden="true" @click="requestClose"></div>
    </Transition>

    <Transition name="cs-slide">
      <section
        v-if="cart.isDrawerOpen"
        ref="sheetEl"
        class="cs"
        role="dialog"
        aria-modal="true"
        aria-labelledby="cs-title"
        data-lenis-prevent
        @keydown="onKeydown"
      >
        <!-- Drag area: handle + header (swipe down here to close; the list never triggers it) -->
        <div class="cs-grab" @pointerdown="onDragStart">
          <span class="cs-handle" aria-hidden="true"></span>
          <header class="cs-header">
            <h2 id="cs-title" class="cs-title">
              আপনার কার্ট
              <span v-if="cart.itemCount" class="cs-count">({{ bnNumber(cart.itemCount) }})</span>
            </h2>
            <button ref="closeBtn" type="button" class="cs-close" aria-label="কার্ট বন্ধ করুন" @click="requestClose">
              <X class="w-5 h-5" :stroke-width="1.6" />
            </button>
          </header>
        </div>

        <template v-if="cart.items.length">
          <!-- Free-delivery strip (only when the store has a free-delivery amount) -->
          <div v-if="threshold > 0" class="cs-free">
            <p class="cs-free-text">
              <template v-if="remaining > 0">আর <strong>{{ taka(remaining) }}</strong> এর পণ্য নিলেই ফ্রি ডেলিভারি</template>
              <template v-else>অভিনন্দন! আপনি ফ্রি ডেলিভারি পাচ্ছেন</template>
            </p>
            <div class="cs-bar" role="progressbar" :aria-valuenow="Math.round(progress)" aria-valuemin="0" aria-valuemax="100" aria-label="ফ্রি ডেলিভারির অগ্রগতি">
              <span class="cs-bar-fill" :style="{ width: `${progress}%` }"></span>
            </div>
          </div>

          <!-- Items (the only scrolling part) -->
          <ul class="cs-list">
            <li v-for="item in cart.items" :key="item.id" :class="['cs-row', busy[item.id] && 'is-busy']" :aria-busy="busy[item.id] ? 'true' : 'false'">
              <a :href="`/product/${item.slug}`" class="cs-img" tabindex="-1" aria-hidden="true" @click.prevent="go(`/product/${item.slug}`)">
                <img v-if="item.image" :src="item.image" alt="" />
              </a>
              <div class="cs-info">
                <div class="cs-top">
                  <a :href="`/product/${item.slug}`" class="cs-name" @click.prevent="go(`/product/${item.slug}`)">{{ item.name }}</a>
                  <button type="button" class="cs-trash" :aria-label="`${item.name} কার্ট থেকে সরান`" @click="remove(item)">
                    <Trash2 class="cs-trash-icon" :stroke-width="1.6" />
                  </button>
                </div>
                <p v-if="variantLabel(item)" class="cs-variant">{{ variantLabel(item) }}</p>
                <div class="cs-bottom">
                  <div class="cs-stepper" role="group" :aria-label="`${item.name} এর পরিমাণ`">
                    <button type="button" class="cs-step" :disabled="item.quantity <= 1" aria-label="পরিমাণ কমান" @click="setQty(item, item.quantity - 1)">
                      <Minus class="cs-step-icon" :stroke-width="2" />
                    </button>
                    <span class="cs-qty" aria-live="polite">{{ bnNumber(item.quantity) }}</span>
                    <button type="button" class="cs-step" :disabled="atStockLimit(item)" :aria-label="atStockLimit(item) ? 'স্টকে আর নেই' : 'পরিমাণ বাড়ান'" @click="setQty(item, item.quantity + 1)">
                      <Plus class="cs-step-icon" :stroke-width="2" />
                    </button>
                  </div>
                  <span class="cs-price">{{ taka(item.price * item.quantity) }}</span>
                </div>
                <p v-if="errors[item.id]" class="cs-error" role="alert">{{ errors[item.id] }}</p>
              </div>
            </li>
          </ul>

          <!-- Sticky footer -->
          <footer class="cs-footer">
            <div class="cs-sub">
              <span class="cs-sub-label">সাবটোটাল</span>
              <span class="cs-sub-amount">{{ taka(subtotal) }}</span>
            </div>
            <a href="/checkout" class="cs-btn" @click.prevent="go('/checkout')">
              চেকআউট <ArrowRight class="w-[18px] h-[18px]" :stroke-width="2" />
            </a>
          </footer>
        </template>

        <!-- Empty -->
        <div v-else class="cs-empty">
          <ShoppingBag class="cs-empty-icon" :stroke-width="1.4" />
          <p class="cs-empty-title">আপনার কার্ট খালি</p>
          <button type="button" class="cs-btn cs-btn-empty" @click="go('/collections')">কেনাকাটা শুরু করুন</button>
        </div>

        <p class="sr-only" aria-live="polite" role="status">{{ announcement }}</p>
      </section>
    </Transition>
  </Teleport>
</template>

<script setup>
// Mobile (<768px) bottom sheet. Cart behaviour lives in useCartPanel (shared with the desktop drawer).
import { ref, computed, watch, nextTick } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { X, Trash2, Minus, Plus, ShoppingBag, ArrowRight } from 'lucide-vue-next';
import api from '../../../api';
import { useCartPanel } from '../../../composables/useCartPanel';
import { useSiteStore } from '../../../stores/site';
import { taka, bnNumber } from '../../../utils/bn';

const route = useRoute();
const router = useRouter();
const site = useSiteStore();
const sheetEl = ref(null);
const closeBtn = ref(null);

/* ------------------------------------------------------------------
   Back button support: opening adds a "#cart" history entry, so the
   phone's Back closes the sheet instead of leaving the page.
------------------------------------------------------------------ */
const HASH = '#cart';
let ownsHistoryEntry = false;
let navigating = false;

const onOpen = () => {
  loadThreshold();
  if (route.hash !== HASH) {
    ownsHistoryEntry = true;
    router.push({ path: route.path, query: route.query, hash: HASH });
  }
};
const onClose = () => {
  // Closed by the X / overlay / swipe / Esc / another component → drop our history entry.
  if (!navigating && ownsHistoryEntry && route.hash === HASH) router.replace({ path: route.path, query: route.query });
  ownsHistoryEntry = false;
};

const { cart, busy, errors, announcement, subtotal, variantLabel, atStockLimit, setQty, remove, trapKeydown } =
  useCartPanel({ panelRef: sheetEl, closeBtnRef: closeBtn, onOpen, onClose });

const requestClose = () => cart.closeDrawer();
const onKeydown = (e) => trapKeydown(e, requestClose);

// Back pressed while open: the hash disappears → close.
watch(() => route.hash, (now, before) => {
  if (before === HASH && now !== HASH && cart.isDrawerOpen) {
    ownsHistoryEntry = false;
    cart.closeDrawer();
  }
});

/** Navigate from inside the sheet: remove the #cart entry first, then go. */
const go = async (path) => {
  navigating = true;
  cart.closeDrawer();
  if (route.hash === HASH) await router.replace({ path: route.path, query: route.query });
  ownsHistoryEntry = false;
  navigating = false;
  router.push(path);
};

/* ------------------------------------------------------------------ free delivery
   The threshold checkout really charges by (delivery methods' free_threshold),
   falling back to the site setting. 0 / none = strip hidden. */
const methodThreshold = ref(null);
let thresholdLoaded = false;
async function loadThreshold() {
  if (thresholdLoaded) return;
  thresholdLoaded = true;
  try {
    const res = await api.get('/public/shipping-methods');
    const values = (res.data.data || []).map((m) => Number(m.free_threshold)).filter((v) => v > 0);
    methodThreshold.value = values.length ? Math.min(...values) : 0;
  } catch { /* use site setting */ }
}
const threshold = computed(() => (methodThreshold.value ?? site.freeShippingThreshold) || 0);
const remaining = computed(() => Math.max(0, threshold.value - Number(cart.subtotal || 0)));
const progress = computed(() => (threshold.value > 0 ? Math.min(100, (Number(cart.subtotal || 0) / threshold.value) * 100) : 0));

/* ------------------------------------------------------------------ swipe down to close */
let drag = null;
const onDragStart = (e) => {
  if (e.button > 0 || e.target.closest('button, a')) return;   // let the X button / links work
  drag = { y: e.clientY, t: performance.now(), dy: 0, id: e.pointerId };
  sheetEl.value.style.transition = 'none';
  e.currentTarget.setPointerCapture?.(e.pointerId);
  window.addEventListener('pointermove', onDragMove);
  window.addEventListener('pointerup', onDragEnd, { once: true });
  window.addEventListener('pointercancel', onDragEnd, { once: true });
};
const onDragMove = (e) => {
  if (!drag || e.pointerId !== drag.id) return;
  drag.dy = Math.max(0, e.clientY - drag.y);
  sheetEl.value.style.transform = `translate3d(0, ${drag.dy}px, 0)`;
};
const onDragEnd = () => {
  window.removeEventListener('pointermove', onDragMove);
  if (!drag || !sheetEl.value) { drag = null; return; }
  const { dy, t } = drag;
  drag = null;
  const velocity = dy / Math.max(1, performance.now() - t);     // px per ms
  const el = sheetEl.value;
  if (dy > 80 || (velocity > 0.5 && dy > 12)) {
    el.style.transition = 'transform .22s ease-out';
    el.style.transform = 'translate3d(0, 100%, 0)';
    setTimeout(requestClose, 200);
  } else {
    el.style.transition = 'transform .32s cubic-bezier(.34, 1.4, .64, 1)';
    el.style.transform = '';
    nextTick(() => setTimeout(() => { if (sheetEl.value) sheetEl.value.style.transition = ''; }, 340));
  }
};
</script>

<style scoped>
/* Brand tokens (resources/css/app.css + storefront components) */
.cs {
  --cs-accent: #111111;
  --cs-on-accent: #FFFFFF;
  --cs-text: #111111;
  --cs-text-2: #69736E;
  --cs-muted: #A89B8A;
  --cs-bg: #F7F5F0;            /* page background */
  --cs-neutral: #EFEBE4;       /* light neutral (image placeholders) */
  --cs-border: #D8CCBC;        /* sand */
  --cs-divider: #E5DED3;
  --cs-error: #E11D48;
  --cs-radius-btn: 2px;        /* site primary buttons are near-square */

  position: fixed; left: 0; right: 0; bottom: 0; z-index: 50;
  max-height: 88vh; max-height: 88dvh;
  display: flex; flex-direction: column;
  background: var(--cs-bg); color: var(--cs-text);
  border-radius: 24px 24px 0 0;
  box-shadow: 0 -12px 40px -12px rgba(17, 17, 17, 0.3);
  font-family: var(--font-sans);
  will-change: transform;
}
.cs :focus-visible { outline: 2px solid var(--cs-accent); outline-offset: 2px; }
.cs-overlay { position: fixed; inset: 0; z-index: 50; background: rgba(17, 17, 17, 0.55); }

/* Handle + header (drag area) */
.cs-grab { flex-shrink: 0; touch-action: none; cursor: grab; user-select: none; }
.cs-handle { display: block; width: 40px; height: 5px; margin: 10px auto 0; border-radius: 9999px; background: var(--cs-border); }
.cs-header { display: flex; align-items: center; justify-content: space-between; padding: 6px 8px 4px 20px; }
.cs-title { display: flex; align-items: baseline; gap: 8px; font-family: var(--font-serif); font-size: 18px; font-weight: 700; letter-spacing: -0.01em; }
.cs-count { font-family: var(--font-sans); font-size: 14px; font-weight: 400; color: var(--cs-text-2); letter-spacing: 0; }
.cs-close { width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; color: var(--cs-text); border-radius: 9999px; }

/* Free-delivery strip */
.cs-free { flex-shrink: 0; padding: 4px 20px 14px; display: flex; flex-direction: column; gap: 8px; }
.cs-free-text { font-size: 13px; font-weight: 500; }
.cs-free-text strong { font-weight: 700; font-variant-numeric: tabular-nums; }
.cs-bar { height: 6px; border-radius: 9999px; background: var(--cs-neutral); overflow: hidden; }
.cs-bar-fill { display: block; height: 100%; border-radius: 9999px; background: var(--cs-accent); transition: width .5s cubic-bezier(0.22, 1, 0.36, 1); }

/* Item list */
.cs-list { flex: 1 1 auto; min-height: 0; overflow-y: auto; overscroll-behavior: contain; padding: 0 20px; }
.cs-row { display: flex; gap: 12px; padding: 12px 0; border-top: 1px solid var(--cs-divider); transition: opacity .2s ease; }
.cs-row.is-busy { opacity: .5; pointer-events: none; }
.cs-img { width: 68px; height: 68px; flex-shrink: 0; overflow: hidden; border-radius: 12px; background: var(--cs-neutral); }
.cs-img img { width: 100%; height: 100%; object-fit: cover; object-position: top; display: block; }
.cs-info { flex: 1 1 auto; min-width: 0; display: flex; flex-direction: column; gap: 2px; }
.cs-top { display: flex; align-items: flex-start; gap: 4px; }
.cs-name {
  flex: 1 1 auto; min-width: 0; font-size: 14px; font-weight: 600; line-height: 1.35; color: var(--cs-text);
  display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; overflow-wrap: anywhere;
}
.cs-trash { width: 44px; height: 44px; margin: -13px -13px -13px 0; flex-shrink: 0; display: flex; align-items: center; justify-content: center; color: var(--cs-muted); border-radius: 9999px; }
.cs-trash:active { color: var(--cs-text); }
.cs-trash-icon { width: 17px; height: 17px; }
.cs-variant { font-size: 12px; color: var(--cs-text-2); }
.cs-bottom { margin-top: 4px; display: flex; align-items: center; justify-content: space-between; gap: 8px; }
.cs-stepper { height: 34px; padding: 0 4px; display: inline-flex; align-items: center; border-radius: 9999px; background: var(--cs-neutral); }
.cs-step { position: relative; width: 30px; height: 34px; display: flex; align-items: center; justify-content: center; color: var(--cs-text); }
.cs-step::after { content: ''; position: absolute; inset: -5px -7px; }   /* 44×44 touch area */
.cs-step:disabled { opacity: .35; }
.cs-step-icon { width: 14px; height: 14px; }
.cs-qty { min-width: 22px; text-align: center; font-size: 13px; font-weight: 700; font-variant-numeric: tabular-nums; }
.cs-price { font-size: 14px; font-weight: 700; font-variant-numeric: tabular-nums; }
.cs-error { font-size: 12px; color: var(--cs-error); }

/* Sticky footer */
.cs-footer {
  flex-shrink: 0; display: flex; align-items: center; gap: 16px;
  padding: 14px 20px calc(28px + env(safe-area-inset-bottom));
  border-top: 1px solid var(--cs-divider);
  background: var(--cs-bg);
}
.cs-sub { display: flex; flex-direction: column; flex-shrink: 0; }
.cs-sub-label { font-size: 12px; color: var(--cs-text-2); }
.cs-sub-amount { font-family: var(--font-serif); font-size: 20px; font-weight: 700; white-space: nowrap; font-variant-numeric: tabular-nums; }
.cs-btn {
  flex: 1 1 auto; height: 54px;
  display: inline-flex; align-items: center; justify-content: center; gap: 8px;
  background: var(--cs-accent); color: var(--cs-on-accent);
  border-radius: var(--cs-radius-btn);
  font-size: 16px; font-weight: 700;
  transition: transform .15s ease;
}
.cs-btn:active { transform: scale(.99); }

/* Empty */
.cs-empty { padding: 28px 20px calc(36px + env(safe-area-inset-bottom)); display: flex; flex-direction: column; align-items: center; text-align: center; gap: 10px; }
.cs-empty-icon { width: 40px; height: 40px; color: var(--cs-muted); }
.cs-empty-title { font-size: 16px; font-weight: 700; }
.cs-btn-empty { flex: none; height: 48px; padding: 0 24px; margin-top: 6px; font-size: 15px; }

/* Motion: 0.3s ease-out slide up + overlay fade; no slide for reduced motion */
.cs-slide-enter-active, .cs-slide-leave-active { transition: transform .3s cubic-bezier(0.22, 1, 0.36, 1); }
.cs-slide-enter-from, .cs-slide-leave-to { transform: translate3d(0, 100%, 0); }
.cs-fade-enter-active, .cs-fade-leave-active { transition: opacity .3s ease-out; }
.cs-fade-enter-from, .cs-fade-leave-to { opacity: 0; }
@media (prefers-reduced-motion: reduce) {
  .cs-slide-enter-active, .cs-slide-leave-active, .cs-bar-fill { transition: none; }
  .cs-slide-enter-from, .cs-slide-leave-to { transform: none; }
}
</style>
