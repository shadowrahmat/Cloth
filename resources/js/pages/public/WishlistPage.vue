<template>
  <div class="py-12 sm:py-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-10 border-b border-[#D8CCBC]/40 pb-6 flex items-baseline justify-between">
      <h1 class="font-serif text-3xl sm:text-4xl text-[#111111]">Saved Pieces & Wishlist</h1>
      <span class="text-xs text-[#A89B8A]">{{ wishlistStore.count }} Saved Items</span>
    </div>

    <div v-if="wishlistStore.items && wishlistStore.items.length">
      <ProductGrid :products="wishlistStore.items" />
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-24 max-w-md mx-auto">
      <svg class="w-16 h-16 text-[#D8CCBC] mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
      </svg>
      <h3 class="font-serif text-2xl text-[#111111] mb-2">Your wishlist is empty</h3>
      <p class="text-xs text-[#A89B8A] mb-8 leading-relaxed">
        Save garments you desire while exploring our seasonal runway edits.
      </p>
      <router-link
        to="/collection/winter"
        class="px-8 py-3.5 bg-[#111111] text-white text-xs uppercase tracking-[0.2em] font-semibold hover:bg-black transition-colors inline-block"
      >
        Explore Collection
      </router-link>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useWishlistStore } from '../../stores/wishlist';
import ProductGrid from '../../components/public/ProductGrid.vue';

const wishlistStore = useWishlistStore();

onMounted(() => {
  wishlistStore.fetchWishlist();
});
</script>
