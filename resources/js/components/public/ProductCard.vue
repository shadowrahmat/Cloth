<template>
  <div class="group relative flex flex-col">
    <!-- Image Area -->
    <div class="relative aspect-3/4 overflow-hidden bg-[#EAE5DC] mb-3">
      <router-link :to="`/product/${product.slug}`" class="block w-full h-full">
        <!-- Main Image -->
        <img
          :src="product.featured_image"
          :alt="product.name"
          class="w-full h-full object-cover object-top transition-transform duration-700 ease-out group-hover:scale-105"
        />

        <!-- Hover Secondary Image (if gallery exists) -->
        <img
          v-if="hoverImage"
          :src="hoverImage"
          :alt="product.name"
          class="absolute inset-0 w-full h-full object-cover object-top opacity-0 transition-opacity duration-500 ease-in-out group-hover:opacity-100"
        />
      </router-link>

      <!-- Badges -->
      <div class="absolute top-3 left-3 flex flex-col gap-1.5 z-10 pointer-events-none">
        <span
          v-if="product.is_on_sale"
          class="px-2 py-0.5 bg-[#111111] text-white text-[10px] font-semibold"
        >
          অফার
        </span>
        <span
          v-else-if="product.is_new"
          class="px-2 py-0.5 bg-white text-[#111111] text-[10px] font-semibold shadow-xs"
        >
          নতুন
        </span>
      </div>

      <!-- Wishlist Action Button -->
      <button
        type="button"
        @click.prevent="wishlistStore.toggleWishlist(product)"
        :class="[
          'absolute top-3 right-3 z-10 p-2 rounded-full transition-all duration-300',
          wishlistStore.isInWishlist(product.id)
            ? 'bg-[#111111] text-white'
            : 'bg-white/80 backdrop-blur-xs text-[#111111] hover:bg-white hover:scale-110 shadow-xs'
        ]"
        :title="wishlistStore.isInWishlist(product.id) ? 'উইশলিস্ট থেকে সরান' : 'উইশলিস্টে রাখুন'"
      >
        <svg
          class="w-4 h-4"
          :fill="wishlistStore.isInWishlist(product.id) ? 'currentColor' : 'none'"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="1.5"
            d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
          />
        </svg>
      </button>

      <!-- Quick Add Overlay Button -->
      <div class="absolute inset-x-3 bottom-3 z-10 opacity-0 translate-y-2 group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-300">
        <button
          type="button"
          @click.prevent="handleQuickAdd"
          class="w-full py-2.5 bg-white/95 backdrop-blur-xs hover:bg-[#111111] text-[#111111] hover:text-white text-xs font-semibold transition-colors shadow-md"
        >
          ব্যাগে যোগ করুন
        </button>
      </div>
    </div>

    <!-- Product Meta -->
    <div class="space-y-1">
      <span v-if="product.category" class="text-[10px] uppercase tracking-wider text-[#A89B8A] font-medium block">
        {{ product.category.name }}
      </span>

      <h4 class="font-serif text-sm sm:text-base text-[#111111] hover:text-[#A89B8A] transition-colors leading-snug line-clamp-1">
        <router-link :to="`/product/${product.slug}`">
          {{ product.name }}
        </router-link>
      </h4>

      <!-- Price & Rating -->
      <div class="flex items-center justify-between pt-0.5">
        <PriceDisplay
          :price="product.price"
          :sale-price="product.sale_price"
          :compare-at-price="product.compare_at_price"
        />

        <!-- Available Colors -->
        <ColorSwatches
          v-if="availableColors.length"
          :colors="availableColors"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useCartStore } from '../../stores/cart';
import { useWishlistStore } from '../../stores/wishlist';
import PriceDisplay from '../common/PriceDisplay.vue';
import ColorSwatches from '../common/ColorSwatches.vue';

const props = defineProps({
  product: {
    type: Object,
    required: true,
  },
});

const cartStore = useCartStore();
const wishlistStore = useWishlistStore();

const hoverImage = computed(() => {
  if (props.product.gallery && Array.isArray(props.product.gallery) && props.product.gallery.length > 1) {
    return props.product.gallery[1];
  }
  return null;
});

const availableColors = computed(() => {
  return props.product.attributes?.colors || [];
});

const handleQuickAdd = () => {
  const firstVariant = props.product.variants && props.product.variants.length ? props.product.variants[0] : null;
  cartStore.addToCart(
    props.product.id,
    firstVariant ? firstVariant.id : null,
    1,
    firstVariant?.color || null,
    firstVariant?.size || null
  );
};
</script>
