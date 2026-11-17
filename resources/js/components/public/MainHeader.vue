<template>
  <header class="sticky top-0 z-40 bg-[#F7F5F0]/95 backdrop-blur-md border-b border-[#D8CCBC]/40 transition-all duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-20">
        <!-- Mobile Menu Trigger -->
        <button
          type="button"
          @click="mobileMenuOpen = true"
          class="lg:hidden p-2 text-[#111111] hover:text-[#A89B8A] transition-colors"
          aria-label="Open mobile navigation"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16"/>
          </svg>
        </button>

        <!-- Brand Identity / Logo -->
        <div class="flex items-center">
          <router-link to="/" class="group flex items-center py-1">
            <img
              src="/images/logo-transparent.png"
              alt="DRAWER CLOTHING"
              class="h-12 sm:h-14 lg:h-15 max-w-[180px] sm:max-w-[220px] w-auto object-contain transition-transform duration-300 group-hover:scale-105"
            />
          </router-link>
        </div>

        <!-- Desktop Navigation Bar -->
        <nav class="hidden lg:flex items-center space-x-8 text-xs uppercase tracking-widest font-medium">
          <router-link
            to="/collection/new-arrivals"
            class="text-[#111111] hover:text-[#A89B8A] transition-colors py-2"
          >
            New Arrivals
          </router-link>

          <div
            class="relative py-2"
            @mouseenter="openMegaMenu"
            @mouseleave="scheduleCloseMegaMenu"
          >
            <router-link
              to="/collection/women"
              class="text-[#111111] hover:text-[#A89B8A] transition-colors inline-flex items-center gap-1"
            >
              Women
              <svg class="w-3 h-3 text-[#A89B8A]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </router-link>
          </div>

          <div
            class="relative py-2"
            @mouseenter="openMegaMenu"
            @mouseleave="scheduleCloseMegaMenu"
          >
            <router-link
              to="/collection/men"
              class="text-[#111111] hover:text-[#A89B8A] transition-colors inline-flex items-center gap-1"
            >
              Men
              <svg class="w-3 h-3 text-[#A89B8A]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </router-link>
          </div>

          <router-link
            to="/collection/kids"
            class="text-[#111111] hover:text-[#A89B8A] transition-colors py-2"
          >
            Kids
          </router-link>

          <router-link
            to="/collection/winter"
            class="text-[#111111] hover:text-[#C5A880] transition-colors font-semibold py-2"
          >
            The Winter Edit
          </router-link>

          <router-link
            to="/collections"
            class="text-[#111111] hover:text-[#A89B8A] transition-colors py-2"
          >
            Collections
          </router-link>
        </nav>

        <!-- Right Action Icons: Search, Account, Wishlist, Cart -->
        <div class="flex items-center space-x-3 sm:space-x-5">
          <!-- Search -->
          <button
            type="button"
            @click="searchOpen = true"
            class="p-2 text-[#111111] hover:text-[#A89B8A] transition-colors"
            title="Search catalog"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
          </button>

          <!-- Customer Account / Admin link -->
          <router-link
            :to="authStore.isCustomerAuthenticated ? '/account' : '/account'"
            class="p-2 text-[#111111] hover:text-[#A89B8A] transition-colors relative"
            title="Client account"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
          </router-link>

          <!-- Wishlist -->
          <router-link
            to="/wishlist"
            class="p-2 text-[#111111] hover:text-[#A89B8A] transition-colors relative"
            title="Wishlist"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
            </svg>
            <span
              v-if="wishlistStore.count > 0"
              class="absolute top-1 right-1 w-4 h-4 bg-[#111111] text-white text-[9px] font-bold rounded-full flex items-center justify-center"
            >
              {{ wishlistStore.count }}
            </span>
          </router-link>

          <!-- Shopping Bag -->
          <button
            type="button"
            @click="cartStore.openDrawer()"
            class="p-2 text-[#111111] hover:text-[#A89B8A] transition-colors relative flex items-center gap-1.5"
            title="Shopping bag"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
            </svg>
            <span
              v-if="cartStore.itemCount > 0"
              class="absolute top-1 right-1 w-4 h-4 bg-[#111111] text-white text-[9px] font-bold rounded-full flex items-center justify-center"
            >
              {{ cartStore.itemCount }}
            </span>
          </button>
        </div>
      </div>
    </div>

    <!-- Mega Menu Dropdown -->
    <MegaMenu
      v-if="megaMenuOpen"
      :categories="categories"
      @mouseenter="cancelCloseMegaMenu"
      @mouseleave="scheduleCloseMegaMenu"
      @close="megaMenuOpen = false"
    />

    <!-- Mobile Slide-out Menu -->
    <MobileMenu
      :is-open="mobileMenuOpen"
      :categories="categories"
      @close="mobileMenuOpen = false"
    />

    <!-- Search Overlay Drawer -->
    <SearchDrawer
      :is-open="searchOpen"
      @close="searchOpen = false"
    />

    <!-- Mini Cart Drawer -->
    <MiniCart />
  </header>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAuthStore } from '../../stores/auth';
import { useCartStore } from '../../stores/cart';
import { useWishlistStore } from '../../stores/wishlist';
import api from '../../api';
import MegaMenu from './MegaMenu.vue';
import MobileMenu from './MobileMenu.vue';
import SearchDrawer from './SearchDrawer.vue';
import MiniCart from './MiniCart.vue';

const authStore = useAuthStore();
const cartStore = useCartStore();
const wishlistStore = useWishlistStore();

const categories = ref([]);
const megaMenuOpen = ref(false);
const mobileMenuOpen = ref(false);
const searchOpen = ref(false);
let closeTimer = null;

const openMegaMenu = () => {
  clearTimeout(closeTimer);
  megaMenuOpen.value = true;
};

const scheduleCloseMegaMenu = () => {
  closeTimer = setTimeout(() => {
    megaMenuOpen.value = false;
  }, 200);
};

const cancelCloseMegaMenu = () => {
  clearTimeout(closeTimer);
};

onMounted(async () => {
  try {
    const res = await api.get('/public/categories');
    categories.value = res.data.data || [];
  } catch (err) {
    console.error('Failed to load categories', err);
  }
});
</script>
