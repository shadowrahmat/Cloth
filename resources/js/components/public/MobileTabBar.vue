<template>
  <!-- App-style bottom navigation (phones/tablets only) -->
  <nav
    v-if="!hidden"
    :class="[
      'lg:hidden fixed inset-x-0 bottom-0 z-40 transition-transform duration-300 ease-[cubic-bezier(0.22,1,0.36,1)]',
      tucked ? 'translate-y-full' : 'translate-y-0',
    ]"
    aria-label="মোবাইল নেভিগেশন"
  >
    <div class="mx-3 mb-[max(0.5rem,env(safe-area-inset-bottom))] rounded-2xl bg-white/90 backdrop-blur-xl border border-black/[0.06] shadow-[0_8px_30px_-8px_rgba(17,17,17,0.25)]">
      <ul class="grid grid-cols-5 h-16">
        <li v-for="tab in tabs" :key="tab.key">
          <component
            :is="tab.to ? 'router-link' : 'button'"
            :to="tab.to"
            :type="tab.to ? undefined : 'button'"
            @click="tab.action?.()"
            :aria-current="isActive(tab) ? 'page' : undefined"
            :aria-label="tab.label"
            class="relative w-full h-full flex flex-col items-center justify-center gap-0.5 active:scale-95 transition-transform"
          >
            <span :class="['relative flex items-center justify-center w-12 h-7 rounded-full transition-colors duration-300', isActive(tab) ? 'bg-[#111111]/[0.07]' : '']">
              <component :is="tab.icon" class="w-[21px] h-[21px] text-[#111111]" :stroke-width="isActive(tab) ? 2.1 : 1.6" />
              <Transition name="badge">
                <span
                  v-if="tab.badge"
                  :key="tab.badge"
                  class="absolute -top-1 right-1.5 min-w-[18px] h-[18px] px-1 rounded-full bg-[#111111] text-white text-[10px] font-bold flex items-center justify-center ring-2 ring-white"
                >{{ tab.badge > 9 ? '৯+' : bnNumber(tab.badge) }}</span>
              </Transition>
            </span>
            <span :class="['text-[11px] leading-none transition-colors', isActive(tab) ? 'text-[#111111] font-semibold' : 'text-[#111111]/55']">{{ tab.label }}</span>
          </component>
        </li>
      </ul>
    </div>
  </nav>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue';
import { useRoute } from 'vue-router';
import { Home, LayoutGrid, Search, ShoppingBag, User } from 'lucide-vue-next';
import { useCartStore } from '../../stores/cart';
import { useUiStore } from '../../stores/ui';
import { bnNumber } from '../../utils/bn';

const route = useRoute();
const cart = useCartStore();
const ui = useUiStore();

const tabs = computed(() => [
  { key: 'home', label: 'হোম', icon: Home, to: '/' },
  { key: 'shop', label: 'শপ', icon: LayoutGrid, to: '/collections' },
  { key: 'search', label: 'খুঁজুন', icon: Search, action: () => ui.openSearch() },
  { key: 'cart', label: 'ব্যাগ', icon: ShoppingBag, action: () => cart.openDrawer(), badge: cart.itemCount },
  { key: 'account', label: 'অ্যাকাউন্ট', icon: User, to: '/account' },
]);

const isActive = (tab) => {
  if (tab.key === 'search') return ui.searchOpen;
  if (tab.key === 'cart') return cart.isDrawerOpen || route.path === '/cart';
  if (tab.key === 'home') return route.path === '/';
  if (tab.key === 'shop') return ['/collections', '/collection/', '/category/', '/product/'].some((p) => route.path.startsWith(p));
  if (tab.key === 'account') return ['/account', '/wishlist', '/track-order'].some((p) => route.path.startsWith(p));
  return false;
};

// Checkout has its own flow and product pages show their own buy bar (app-style).
const hidden = computed(() => route.path.startsWith('/checkout') || route.path.startsWith('/product/'));

// Slide away while reading (scrolling down), come back on any scroll up.
const tucked = ref(false);
let lastY = 0;
let ticking = false;
const onScroll = () => {
  if (ticking) return;
  ticking = true;
  requestAnimationFrame(() => {
    const y = window.scrollY;
    const nearBottom = window.innerHeight + y >= document.documentElement.scrollHeight - 80;
    if (y < 80 || nearBottom) tucked.value = false;
    else if (y > lastY + 6) tucked.value = true;
    else if (y < lastY - 6) tucked.value = false;
    lastY = y;
    ticking = false;
  });
};
watch(() => route.fullPath, () => { tucked.value = false; });

onMounted(() => window.addEventListener('scroll', onScroll, { passive: true }));
onBeforeUnmount(() => window.removeEventListener('scroll', onScroll));
</script>

<style scoped>
.badge-enter-active { animation: badge-pop 0.35s cubic-bezier(0.34, 1.56, 0.64, 1); }
@keyframes badge-pop { from { transform: scale(0.4); opacity: 0; } to { transform: scale(1); opacity: 1; } }
</style>
