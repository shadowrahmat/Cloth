<template>
  <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-x-4 sm:gap-x-6 gap-y-10">
    <ProductCard
      v-for="product in products"
      :key="product.id"
      :product="product"
    />
  </div>
</template>

<script setup>
import ProductCard from './ProductCard.vue';

defineProps({
  products: {
    type: Array,
    default: () => [],
  },
});
</script>
