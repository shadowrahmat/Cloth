<template>
  <!-- Bottom padding on phones keeps content clear of the floating tab bar -->
  <div class="min-h-screen flex flex-col bg-[#F7F5F0] text-[#111111] pb-[calc(5rem+env(safe-area-inset-bottom))] lg:pb-0">

    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:left-2 focus:z-[100] focus:px-4 focus:py-2 focus:bg-[#111111] focus:text-white">
      মূল কনটেন্টে যান
    </a>

    <AnnouncementBar />
    <MainHeader />

    <main id="main" class="flex-1">
      <router-view v-slot="{ Component, route }">
        <Transition name="page" mode="out-in" @after-enter="refreshScrollTrigger">
          <component :is="Component" :key="route.path" />
        </Transition>
      </router-view>
    </main>

    <MainFooter />
    <MobileTabBar />
  </div>
</template>

<script setup>
import { onMounted, onUnmounted } from 'vue';
import { useCartStore } from '../stores/cart';
import { useWishlistStore } from '../stores/wishlist';
import AnnouncementBar from '../components/public/AnnouncementBar.vue';
import MainHeader from '../components/public/MainHeader.vue';
import MainFooter from '../components/public/MainFooter.vue';
import MobileTabBar from '../components/public/MobileTabBar.vue';
import { initSmoothScroll, refreshScrollTrigger, destroySmoothScroll } from '../utils/smoothScroll';

const cartStore = useCartStore();
const wishlistStore = useWishlistStore();

onMounted(() => {
  // Inertia smooth scrolling (skipped automatically for reduced-motion users)
  initSmoothScroll();
  cartStore.fetchCart();
  wishlistStore.fetchWishlist();
});

onUnmounted(() => destroySmoothScroll());
</script>
