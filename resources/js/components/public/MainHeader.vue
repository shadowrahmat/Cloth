<template>
  <header
    :class="[
      'z-40 transition-[background-color,box-shadow,backdrop-filter] duration-300',
      site.header.sticky !== false ? 'sticky top-0' : 'relative',
      scrolled ? 'bg-[#F7F5F0]/90 backdrop-blur-md shadow-[0_1px_0_rgba(17,17,17,0.06),0_8px_24px_-12px_rgba(17,17,17,0.12)]' : 'bg-[#F7F5F0]',
    ]"
  >
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div :class="['flex items-center justify-between gap-4 transition-[height] duration-300', scrolled ? 'h-14 lg:h-16' : 'h-14 lg:h-20']">
        <!-- Mobile menu trigger -->
        <button
          type="button"
          @click="ui.mobileMenuOpen = true"
          class="lg:hidden -ml-2 p-2 text-[#111111] hover:text-[#A89B8A] transition-colors"
          aria-label="মেনু খুলুন"
        >
          <Menu class="w-6 h-6" :stroke-width="1.5" />
        </button>

        <!-- Logo -->
        <router-link to="/" class="flex items-center py-1 shrink-0 group" :aria-label="site.siteName">
          <img
            v-if="site.branding.logo"
            :src="site.branding.logo"
            :alt="site.siteName"
            :class="['w-auto max-w-[200px] object-contain transition-all duration-300 group-hover:opacity-80', scrolled ? 'h-9 lg:h-10' : 'h-9 lg:h-14']"
          />
          <span v-else class="font-serif text-2xl tracking-tight">{{ site.siteName }}</span>
        </router-link>

        <!-- Desktop navigation -->
        <nav class="hidden lg:flex items-center gap-1 xl:gap-2 text-[13.5px] font-medium" aria-label="প্রধান মেনু">
          <div
            v-for="(item, i) in site.header.menu"
            :key="i"
            class="relative"
            @mouseenter="item.mega?.length && openMega(i)"
            @mouseleave="item.mega?.length && scheduleClose()"
          >
            <SiteLink
              :to="item.url"
              :class="[
                'relative inline-flex items-center gap-1 px-3 py-2 rounded-full transition-colors',
                isActive(item) || megaIndex === i ? 'text-[#111111]' : 'text-[#111111]/70 hover:text-[#111111]',
              ]"
              @click="megaIndex = null"
              @keydown.down.prevent="item.mega?.length && openMega(i)"
              :aria-haspopup="item.mega?.length ? 'true' : undefined"
              :aria-expanded="item.mega?.length ? String(megaIndex === i) : undefined"
            >
              {{ item.label }}
              <ChevronDown v-if="item.mega?.length" :class="['w-3.5 h-3.5 transition-transform duration-200', megaIndex === i && 'rotate-180']" />
              <span
                :class="['absolute left-3 right-3 -bottom-0.5 h-px bg-[#111111] origin-left transition-transform duration-300', isActive(item) ? 'scale-x-100' : 'scale-x-0']"
              ></span>
            </SiteLink>
          </div>
        </nav>

        <!-- Actions -->
        <div class="flex items-center gap-0.5 sm:gap-1.5 -mr-2">
          <button
            v-if="site.header.show_search !== false"
            type="button"
            @click="ui.openSearch()"
            class="hidden lg:inline-flex p-2.5 rounded-full text-[#111111] hover:bg-black/5 transition-colors"
            aria-label="পণ্য খুঁজুন"
          >
            <Search class="w-5 h-5" :stroke-width="1.5" />
          </button>
          <router-link
            v-if="site.header.show_account !== false"
            to="/account"
            class="hidden lg:inline-flex p-2.5 rounded-full text-[#111111] hover:bg-black/5 transition-colors"
            aria-label="আমার অ্যাকাউন্ট"
          >
            <User class="w-5 h-5" :stroke-width="1.5" />
          </router-link>
          <router-link
            v-if="site.header.show_wishlist !== false"
            to="/wishlist"
            class="relative p-2.5 rounded-full text-[#111111] hover:bg-black/5 transition-colors"
            aria-label="পছন্দের তালিকা"
          >
            <Heart class="w-5 h-5" :stroke-width="1.5" />
            <span v-if="wishlistStore.count > 0" class="absolute top-1 right-1 min-w-4 h-4 px-1 bg-[#111111] text-white text-[9px] font-bold rounded-full flex items-center justify-center">
              {{ wishlistStore.count }}
            </span>
          </router-link>
          <button
            type="button"
            @click="cartStore.openDrawer()"
            class="hidden lg:inline-flex relative p-2.5 rounded-full text-[#111111] hover:bg-black/5 transition-colors"
            aria-label="শপিং ব্যাগ"
          >
            <ShoppingBag class="w-5 h-5" :stroke-width="1.5" />
            <Transition name="fade">
              <span v-if="cartStore.itemCount > 0" :key="cartStore.itemCount" class="absolute top-1 right-1 min-w-4 h-4 px-1 bg-[#111111] text-white text-[9px] font-bold rounded-full flex items-center justify-center">
                {{ cartStore.itemCount }}
              </span>
            </Transition>
          </button>
        </div>
      </div>
    </div>

    <!-- Mega menu -->
    <Transition
      enter-active-class="transition duration-250 ease-out"
      enter-from-class="opacity-0 -translate-y-2"
      leave-active-class="transition duration-150 ease-in"
      leave-to-class="opacity-0 -translate-y-1"
    >
      <MegaMenu
        v-if="megaItem"
        :item="megaItem"
        :promo="site.header.mega_promo"
        :products-config="site.header.mega_products"
        @mouseenter="cancelClose"
        @mouseleave="scheduleClose"
        @close="megaIndex = null"
      />
    </Transition>

    <MobileMenu :is-open="ui.mobileMenuOpen" @close="ui.mobileMenuOpen = false" />
    <SearchDrawer :is-open="ui.searchOpen" @close="ui.searchOpen = false" />
    <MiniCart />
  </header>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';
import { useRoute } from 'vue-router';
import { Menu, Search, User, Heart, ShoppingBag, ChevronDown } from 'lucide-vue-next';
import { useSiteStore } from '../../stores/site';
import { useCartStore } from '../../stores/cart';
import { useWishlistStore } from '../../stores/wishlist';
import { useUiStore } from '../../stores/ui';
import SiteLink from '../common/SiteLink.vue';
import MegaMenu from './MegaMenu.vue';
import { useMegaProducts } from '../../utils/megaProducts';
import MobileMenu from './MobileMenu.vue';
import SearchDrawer from './SearchDrawer.vue';
import MiniCart from './MiniCart.vue';

const route = useRoute();
const site = useSiteStore();
const cartStore = useCartStore();
const wishlistStore = useWishlistStore();

const ui = useUiStore();

// --- active link: only ONE item is highlighted. Priority: exact URL match,
// then the mega menu that contains the current page, then a parent path.
const activeIndex = computed(() => {
  const path = route.path;
  const menu = site.header.menu || [];
  let i = menu.findIndex((m) => m.url === path);
  if (i < 0) i = menu.findIndex((m) => (m.mega || []).some((col) => (col.links || []).some((l) => l.url === path)));
  if (i < 0) i = menu.findIndex((m) => m.url && m.url !== '/' && !m.url.includes('#') && path.startsWith(m.url + '/'));
  return i;
});
const isActive = (item) => site.header.menu.indexOf(item) === activeIndex.value;

// --- mega menu (hover with a small close delay so the pointer can travel)
const megaIndex = ref(null);
const megaItem = computed(() => (megaIndex.value !== null ? site.header.menu[megaIndex.value] : null));
let closeTimer = null;
const openMega = (i) => { clearTimeout(closeTimer); megaIndex.value = i; };
const scheduleClose = () => { closeTimer = setTimeout(() => { megaIndex.value = null; }, 180); };
const cancelClose = () => clearTimeout(closeTimer);
watch(() => route.fullPath, () => { megaIndex.value = null; ui.closeAll(); });

// --- compact header once the page scrolls
const scrolled = ref(false);
const onScroll = () => { scrolled.value = window.scrollY > 12; };
const onKey = (e) => { if (e.key === 'Escape') megaIndex.value = null; };
onMounted(() => {
  // Prefetch the mega menu's product photos shortly after load so it opens instantly.
  if (site.header.menu?.some((m) => m.mega?.length)) {
    setTimeout(() => useMegaProducts(site.header.mega_products), 2000);
  }
  onScroll();
  window.addEventListener('scroll', onScroll, { passive: true });
  window.addEventListener('keydown', onKey);
});
onBeforeUnmount(() => {
  window.removeEventListener('scroll', onScroll);
  window.removeEventListener('keydown', onKey);
});
</script>
