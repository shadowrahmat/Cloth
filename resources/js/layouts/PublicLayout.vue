<template>
  <div class="min-h-screen flex flex-col bg-[#F7F5F0] text-[#111111]">
    <!-- Announcement Bar (Dynamic from homepage sections) -->
    <AnnouncementBar :content="announcementContent" />

    <!-- Main Navigation Header with MegaMenu & Drawers -->
    <MainHeader />

    <!-- Page Content Slot -->
    <main class="flex-1">
      <router-view />
    </main>

    <!-- Luxury Multi-column Footer -->
    <MainFooter />
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick } from 'vue';
import { useRouter } from 'vue-router';
import { useCartStore } from '../stores/cart';
import { useWishlistStore } from '../stores/wishlist';
import api from '../api';
import AnnouncementBar from '../components/public/AnnouncementBar.vue';
import MainHeader from '../components/public/MainHeader.vue';
import MainFooter from '../components/public/MainFooter.vue';
import { initSmoothScroll, scrollTo, refreshScrollTrigger, destroySmoothScroll } from '../utils/smoothScroll';

const router = useRouter();
const cartStore = useCartStore();
const wishlistStore = useWishlistStore();
const announcementContent = ref({
  text: 'Complimentary Worldwide Shipping on Orders Over $150 • Enjoy 30-Day White Glove Returns',
  link_url: '/collection/winter',
  link_text: 'Discover The Edit',
  enabled: true,
});

let unhookRouter = null;

onMounted(async () => {
  // Initialize Awwwards-style Lenis Inertia Smooth Scroll
  initSmoothScroll();

  // Scroll to top and refresh ScrollTrigger on route navigation
  unhookRouter = router.afterEach(() => {
    nextTick(() => {
      scrollTo(0, { immediate: true });
      refreshScrollTrigger();
    });
  });

  // Initialize cart & wishlist on mount
  cartStore.fetchCart();
  wishlistStore.fetchWishlist();

  // Load active announcement bar settings
  try {
    const res = await api.get('/public/homepage');
    const sections = res.data.data?.sections || [];
    const ann = sections.find(s => s.type === 'announcement');
    if (ann && ann.content) {
      announcementContent.value = ann.content;
    }
  } catch (err) {
    // default already present
  }
});

onUnmounted(() => {
  if (unhookRouter) {
    unhookRouter();
  }
  destroySmoothScroll();
});
</script>
