<template>
  <div :class="['grid gap-x-3 sm:gap-x-6 gap-y-8 sm:gap-y-10', colsClass]">
    <ProductCard
      v-for="(product, i) in products"
      :key="product.id"
      :product="product"
      v-reveal="{ delay: (i % 4) * 70 }"
    />
  </div>
</template>

<script setup>
import { computed } from 'vue';
import ProductCard from './ProductCard.vue';

const props = defineProps({
  products: { type: Array, default: () => [] },
  /** Desktop column count (shop page density toggle). Mobile is always 2. */
  columns: { type: Number, default: 4 },
});

const colsClass = computed(() => ({
  2: 'grid-cols-2',
  3: 'grid-cols-2 md:grid-cols-3',
  4: 'grid-cols-2 md:grid-cols-3 lg:grid-cols-4',
}[props.columns] || 'grid-cols-2 md:grid-cols-3 lg:grid-cols-4'));
</script>
