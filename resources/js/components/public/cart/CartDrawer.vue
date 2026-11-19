<template>
  <Teleport to="body">
    <Transition name="cd-fade">
      <div v-if="cart.isDrawerOpen" class="cd-overlay" aria-hidden="true" @click="close"></div>
    </Transition>

    <Transition name="cd-slide" @after-enter="focusFirst">
      <aside
        v-if="cart.isDrawerOpen"
        ref="drawerEl"
        class="cd"
        role="dialog"
        aria-modal="true"
        aria-labelledby="cd-title"
        data-lenis-prevent
        @keydown="onKeydown"
      >
        <!-- Header -->
        <header class="cd-header">
          <h2 id="cd-title" class="cd-title">
            শপিং ব্যাগ
            <span v-if="cart.itemCount" class="cd-badge" :aria-label="`${bnNumber(cart.itemCount)}টি পণ্য`">{{ bnNumber(cart.itemCount) }}</span>
          </h2>
          <button ref="closeBtn" type="button" class="cd-close" aria-label="ব্যাগ বন্ধ করুন" @click="close">
            <X class="w-5 h-5" :stroke-width="1.6" />
          </button>
        </header>

        <!-- Item list (the only part that scrolls on normal-height screens) -->
        <div class="cd-list">
          <template v-if="cart.items.length">
            <article
              v-for="item in cart.items"
              :key="item.id"
              :class="['cd-card', busy[item.id] && 'is-busy']"
              :aria-busy="busy[item.id] ? 'true' : 'false'"
            >
              <router-link :to="`/product/${item.slug}`" class="cd-img" tabindex="-1" aria-hidden="true" @click="close">
                <img v-if="item.image" :src="item.image" alt="" />
              </router-link>

              <div class="cd-info">
                <div class="cd-row-top">
                  <router-link :to="`/product/${item.slug}`" class="cd-name" @click="close">{{ item.name }}</router-link>
                  <button type="button" class="cd-trash" :aria-label="`${item.name} ব্যাগ থেকে সরান`" @click="remove(item)">
                    <Trash2 class="w-[18px] h-[18px]" :stroke-width="1.6" />
                  </button>
                </div>

                <span v-if="variantLabel(item)" class="cd-chip">{{ variantLabel(item) }}</span>

                <div class="cd-row-bottom">
                  <span class="cd-price">{{ taka(item.price * item.quantity) }}</span>
                  <div class="cd-stepper" role="group" :aria-label="`${item.name} এর পরিমাণ`">
                    <button type="button" class="cd-step cd-step-minus" :disabled="item.quantity <= 1" aria-label="পরিমাণ কমান" @click="setQty(item, item.quantity - 1)">
                      <Minus class="w-3.5 h-3.5" :stroke-width="2" />
                    </button>
                    <span class="cd-qty" aria-live="polite">{{ bnNumber(item.quantity) }}</span>
                    <button type="button" class="cd-step cd-step-plus" :disabled="atStockLimit(item)" :aria-label="atStockLimit(item) ? 'স্টকে আর নেই' : 'পরিমাণ বাড়ান'" @click="setQty(item, item.quantity + 1)">
                      <Plus class="w-3.5 h-3.5" :stroke-width="2" />
                    </button>
                  </div>
                </div>

                <p v-if="errors[item.id]" class="cd-error" role="alert">{{ errors[item.id] }}</p>
              </div>
            </article>
          </template>

          <!-- Empty state -->
          <div v-else class="cd-empty">
            <ShoppingBag class="cd-empty-icon" :stroke-width="1.4" />
            <p class="cd-empty-title">আপনার ব্যাগ খালি</p>
            <p class="cd-empty-text">যে পণ্য যোগ করবেন তা এখানে দেখাবে।</p>
            <button type="button" class="cd-btn cd-btn-inline" @click="startShopping">কেনাকাটা শুরু করুন</button>
          </div>
        </div>

        <template v-if="cart.items.length">
          <!-- Summary -->
          <section class="cd-summary" aria-label="অর্ডারের হিসাব">
            <div class="cd-panel cd-delivery">
              <p id="cd-delivery-label" class="cd-label">ডেলিভারি এরিয়া</p>
              <div class="cd-options" role="radiogroup" aria-labelledby="cd-delivery-label">
                <button
                  v-for="m in areas"
                  :key="m.code"
                  type="button"
                  role="radio"
                  :aria-checked="area === m.code"
                  :class="['cd-option', area === m.code && 'is-selected']"
                  @click="selectArea(m.code)"
                >
                  <span class="cd-option-title">{{ m.name }}</span>
                  <span class="cd-option-sub">{{ isFree(m) ? 'ফ্রি' : taka(m.rate) }} · {{ m.estimated_delivery }}</span>
                </button>
              </div>
            </div>

            <div class="cd-panel cd-totals">
              <div class="cd-total-row"><span>সাবটোটাল</span><strong>{{ taka(subtotal) }}</strong></div>
              <div class="cd-total-row"><span>ডেলিভারি চার্জ</span><strong>{{ deliveryFee === 0 ? 'ফ্রি' : taka(deliveryFee) }}</strong></div>
              <hr class="cd-divider" />
              <div class="cd-total-row cd-grand"><span>সর্বমোট</span><strong>{{ taka(total) }}</strong></div>
            </div>
          </section>

          <!-- Checkout -->
          <footer class="cd-checkout">
            <router-link to="/checkout" class="cd-btn" @click="close">
              চেকআউটে যান
              <ArrowRight class="w-[18px] h-[18px]" :stroke-width="1.8" />
            </router-link>
            <ul class="cd-trust">
              <li><Banknote class="cd-trust-icon" :stroke-width="1.5" /><span>ক্যাশ অন ডেলিভারি</span></li>
              <li><RefreshCw class="cd-trust-icon" :stroke-width="1.5" /><span>৭ দিনে এক্সচেঞ্জ</span></li>
              <li><ShieldCheck class="cd-trust-icon" :stroke-width="1.5" /><span>নিরাপদ চেকআউট</span></li>
            </ul>
          </footer>
        </template>

        <!-- Screen-reader announcements (removals, failures) -->
        <p class="sr-only" aria-live="polite" role="status">{{ announcement }}</p>
      </aside>
    </Transition>
  </Teleport>
</template>

<script setup>
// Desktop (≥768px) "Soft Cards" side drawer. Cart behaviour lives in useCartPanel.
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { X, Trash2, Minus, Plus, ShoppingBag, ArrowRight, Banknote, RefreshCw, ShieldCheck } from 'lucide-vue-next';
import api from '../../../api';
import { useCartPanel } from '../../../composables/useCartPanel';
import { taka, bnNumber } from '../../../utils/bn';

const router = useRouter();
const drawerEl = ref(null);
const closeBtn = ref(null);

const { cart, busy, errors, announcement, subtotal, variantLabel, atStockLimit, setQty, remove, focusFirst, trapKeydown } =
  useCartPanel({ panelRef: drawerEl, closeBtnRef: closeBtn, onOpen: () => loadAreas() });

const close = () => cart.closeDrawer();
const startShopping = () => { close(); router.push('/collections'); };
const onKeydown = (e) => trapKeydown(e, close);

// --------------------------------------------------- delivery areas
// From the store's real shipping methods; fallback = agreed defaults.
const FALLBACK = [
  { code: 'inside_dhaka', name: 'ঢাকার ভেতরে', rate: 60, estimated_delivery: '১–২ দিন', free_threshold: 2500 },
  { code: 'outside_dhaka', name: 'ঢাকার বাইরে', rate: 120, estimated_delivery: '৩–৫ দিন', free_threshold: 2500 },
];
const STORAGE_KEY = 'cart_delivery_area';
const areas = ref(FALLBACK);
const area = ref((() => { try { return localStorage.getItem(STORAGE_KEY) || ''; } catch { return ''; } })());
let areasLoaded = false;

async function loadAreas() {
  if (areasLoaded) return;
  areasLoaded = true;
  try {
    const res = await api.get('/public/shipping-methods');
    const list = (res.data.data || []).slice(0, 2);
    if (list.length) areas.value = list;
  } catch { /* keep fallback */ }
  if (!areas.value.some((m) => m.code === area.value)) area.value = areas.value[0]?.code || '';
}
const selectArea = (code) => {
  area.value = code;
  try { localStorage.setItem(STORAGE_KEY, code); } catch { /* storage blocked */ }
};

// ------------------------------------------------------------ totals
const selected = computed(() => areas.value.find((m) => m.code === area.value) || areas.value[0]);
const isFree = (m) => Number(m.free_threshold) > 0 && Number(cart.subtotal) >= Number(m.free_threshold);
const deliveryFee = computed(() => (selected.value ? (isFree(selected.value) ? 0 : Number(selected.value.rate)) : 0));
const total = computed(() => subtotal.value + deliveryFee.value);
</script>
<style scoped>
/* -------------------------------------------------------------------------
   Brand tokens (from resources/css/app.css + storefront components).
   Only the faint accent tint is derived; every other value is a site colour.
------------------------------------------------------------------------- */
.cd {
  --cd-accent: #111111;          /* primary button colour */
  --cd-on-accent: #FFFFFF;
  --cd-text: #111111;
  --cd-text-2: #69736E;          /* secondary text */
  --cd-muted: #A89B8A;           /* taupe */
  --cd-bg: #F7F5F0;              /* ivory — site background / light neutral */
  --cd-surface: #FFFFFF;
  --cd-border: #E5DED3;
  --cd-divider: #F0ECE5;
  --cd-error: #E11D48;           /* rose-600, used by storefront form errors */
  --cd-accent-tint: rgba(17, 17, 17, 0.04);
  --cd-radius-card: 2px;         /* site cards / images use rounded-sm */
  --cd-radius-media: 2px;
  --cd-shadow-soft: 0 1px 2px rgba(17, 17, 17, 0.06), 0 1px 1px rgba(17, 17, 17, 0.03);

  position: fixed;
  inset: 0 0 0 auto;
  z-index: 50;
  width: 420px;
  height: 100dvh;
  display: flex;
  flex-direction: column;
  background: var(--cd-bg);
  color: var(--cd-text);
  box-shadow: -18px 0 40px -20px rgba(17, 17, 17, 0.35);
  font-family: var(--font-sans);
}
@media (max-width: 480px) { .cd { width: 100%; } }

.cd :focus-visible { outline: 2px solid var(--cd-accent); outline-offset: 2px; }

.cd-overlay { position: fixed; inset: 0; z-index: 50; background: rgba(17, 17, 17, 0.45); }

/* Header -------------------------------------------------------------- */
.cd-header {
  flex-shrink: 0;
  display: flex; align-items: center; justify-content: space-between;
  padding: 20px 16px 14px 24px;
  background: var(--cd-bg);
}
.cd-title {
  display: flex; align-items: center; gap: 10px;
  font-family: var(--font-serif);
  font-size: 20px; font-weight: 700; letter-spacing: -0.01em; line-height: 1.2;
}
.cd-badge {
  min-width: 24px; height: 24px; padding: 0 7px;
  display: inline-flex; align-items: center; justify-content: center;
  border-radius: 9999px;
  background: var(--cd-text); color: var(--cd-bg);
  font-family: var(--font-sans); font-size: 12px; font-weight: 700;
}
.cd-close {
  width: 44px; height: 44px; border-radius: 9999px;
  display: flex; align-items: center; justify-content: center;
  background: var(--cd-surface); color: var(--cd-text);
  box-shadow: var(--cd-shadow-soft);
  transition: transform .15s ease;
}
.cd-close:active { transform: scale(.95); }

/* Item list ----------------------------------------------------------- */
.cd-list {
  flex: 1 1 auto; min-height: 0; overflow-y: auto; overscroll-behavior: contain;
  padding: 4px 16px 12px;
  display: flex; flex-direction: column; gap: 10px;
}
.cd-card {
  display: flex; gap: 14px; padding: 12px;
  background: var(--cd-surface);
  border-radius: var(--cd-radius-card);
  box-shadow: var(--cd-shadow-soft);
  transition: opacity .2s ease;
}
.cd-card.is-busy { opacity: .55; pointer-events: none; }
.cd-img {
  width: 88px; height: 88px; flex-shrink: 0; overflow: hidden;
  border-radius: var(--cd-radius-media);
  background: #EFEBE4;
}
.cd-img img { width: 100%; height: 100%; object-fit: cover; object-position: top; display: block; }
.cd-info { flex: 1 1 auto; min-width: 0; display: flex; flex-direction: column; gap: 6px; }
.cd-row-top { display: flex; align-items: flex-start; gap: 4px; }
.cd-name {
  flex: 1 1 auto; min-width: 0;
  font-size: 15px; font-weight: 600; line-height: 1.35; color: var(--cd-text);
  display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}
.cd-name:hover { text-decoration: underline; text-underline-offset: 2px; }
.cd-trash {
  width: 44px; height: 44px; margin: -12px -12px -12px 0; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
  color: var(--cd-muted); border-radius: 9999px;
  transition: color .15s ease;
}
.cd-trash:hover { color: var(--cd-text); }
.cd-chip {
  align-self: flex-start;
  padding: 2px 9px; border-radius: 9999px;
  background: var(--cd-bg); color: var(--cd-text-2);
  font-size: 12px; line-height: 18px;
}
.cd-row-bottom { margin-top: auto; display: flex; align-items: center; justify-content: space-between; gap: 8px; }
.cd-price { font-size: 15px; font-weight: 700; font-variant-numeric: tabular-nums; }

.cd-stepper {
  height: 36px; padding: 0 3px;
  display: inline-flex; align-items: center; gap: 2px;
  border-radius: 9999px; background: var(--cd-bg);
}
.cd-step {
  position: relative;
  width: 30px; height: 30px; border-radius: 9999px;
  display: flex; align-items: center; justify-content: center;
  transition: opacity .15s ease, transform .15s ease;
}
.cd-step::after { content: ''; position: absolute; inset: -7px; }   /* 44×44 touch area */
.cd-step:active:not(:disabled) { transform: scale(.92); }
.cd-step:disabled { opacity: .35; cursor: not-allowed; }
.cd-step-minus { background: var(--cd-surface); color: var(--cd-text); box-shadow: 0 1px 2px rgba(17, 17, 17, 0.12); }
.cd-step-plus { background: var(--cd-accent); color: var(--cd-on-accent); }
.cd-qty { min-width: 26px; text-align: center; font-size: 14px; font-weight: 600; font-variant-numeric: tabular-nums; }
.cd-error { font-size: 12px; color: var(--cd-error); }

/* Empty state --------------------------------------------------------- */
.cd-empty {
  margin-top: 120px; padding: 36px 24px;
  display: flex; flex-direction: column; align-items: center; text-align: center; gap: 6px;
  background: var(--cd-surface); border-radius: var(--cd-radius-card); box-shadow: var(--cd-shadow-soft);
}
.cd-empty-icon { width: 36px; height: 36px; color: var(--cd-muted); margin-bottom: 6px; }
.cd-empty-title { font-size: 17px; font-weight: 700; }
.cd-empty-text { font-size: 14px; color: var(--cd-text-2); }

/* Summary ------------------------------------------------------------- */
.cd-summary { flex-shrink: 0; padding: 4px 16px 0; display: flex; flex-direction: column; gap: 10px; }
.cd-panel { background: var(--cd-surface); border-radius: var(--cd-radius-card); box-shadow: var(--cd-shadow-soft); }
.cd-delivery { padding: 14px; }
.cd-label { font-size: 13px; font-weight: 600; margin-bottom: 10px; }
.cd-options { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
.cd-option {
  height: 56px; padding: 0 12px;
  display: flex; flex-direction: column; align-items: flex-start; justify-content: center; text-align: left;
  border: 1.5px solid var(--cd-border); border-radius: var(--cd-radius-media);
  background: var(--cd-surface);
  transition: border-color .15s ease, background-color .15s ease;
}
.cd-option.is-selected { border-color: var(--cd-accent); background: var(--cd-accent-tint); }
.cd-option-title { font-size: 14px; font-weight: 700; line-height: 1.3; }
.cd-option-sub { font-size: 12px; color: var(--cd-text-2); line-height: 1.3; font-variant-numeric: tabular-nums; }

.cd-totals { padding: 16px; display: flex; flex-direction: column; gap: 10px; }
.cd-total-row { display: flex; align-items: baseline; justify-content: space-between; font-size: 14px; }
.cd-total-row > span { color: var(--cd-text-2); }
.cd-total-row > strong { font-weight: 600; font-variant-numeric: tabular-nums; }
.cd-divider { border: 0; border-top: 1px solid var(--cd-divider); margin: 0; }
.cd-grand > span { font-size: 15px; font-weight: 600; color: var(--cd-text); }
.cd-grand > strong { font-family: var(--font-serif); font-size: 20px; font-weight: 700; }

/* Checkout ------------------------------------------------------------ */
.cd-checkout {
  flex-shrink: 0;
  padding: 10px 16px calc(20px + env(safe-area-inset-bottom));
  display: flex; flex-direction: column; gap: 10px;
  background: var(--cd-bg);
}
.cd-btn {
  width: 100%; height: 54px;
  display: inline-flex; align-items: center; justify-content: center; gap: 8px;
  background: var(--cd-accent); color: var(--cd-on-accent);
  border-radius: var(--cd-radius-card);
  font-size: 15px; font-weight: 600;
  transition: background-color .15s ease, transform .15s ease;
}
.cd-btn:hover { background: #000000; }
.cd-btn:active { transform: scale(.99); }
.cd-btn-inline { width: auto; height: 46px; padding: 0 22px; margin-top: 12px; font-size: 14px; }
.cd-trust { display: grid; grid-template-columns: repeat(3, 1fr); gap: 6px; }
.cd-trust li { display: flex; flex-direction: column; align-items: center; gap: 5px; text-align: center; font-size: 12px; color: var(--cd-text-2); }
.cd-trust-icon { width: 20px; height: 20px; color: var(--cd-text); }

/* Short screens: the whole drawer scrolls; header/checkout stay stuck ---- */
@media (max-height: 760px) {
  .cd { overflow-y: auto; overscroll-behavior: contain; }
  .cd-header { position: sticky; top: 0; z-index: 2; }
  .cd-list { flex: none; overflow: visible; }
  .cd-checkout { position: sticky; bottom: 0; z-index: 2; margin-top: auto; box-shadow: 0 -10px 24px -14px rgba(17, 17, 17, 0.28); }
}

/* Motion: 0.3s ease-out slide + overlay fade; none for reduced motion ---- */
.cd-slide-enter-active, .cd-slide-leave-active { transition: transform .3s cubic-bezier(0.22, 1, 0.36, 1); }
.cd-slide-enter-from, .cd-slide-leave-to { transform: translate3d(100%, 0, 0); }
.cd-fade-enter-active, .cd-fade-leave-active { transition: opacity .3s ease-out; }
.cd-fade-enter-from, .cd-fade-leave-to { opacity: 0; }
@media (prefers-reduced-motion: reduce) {
  .cd-slide-enter-active, .cd-slide-leave-active { transition: none; }
  .cd-slide-enter-from, .cd-slide-leave-to { transform: none; }
}
</style>
