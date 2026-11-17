<template>
  <div class="inline-flex items-baseline gap-2">
    <span :class="[sizeClass, 'font-medium text-[#111111]']">
      ৳ {{ formatPrice(effectivePrice) }}
    </span>
    <span
      v-if="compareAtPrice && compareAtPrice > effectivePrice"
      class="text-xs text-[#A89B8A] line-through font-normal"
    >
      ৳ {{ formatPrice(compareAtPrice) }}
    </span>
    <span
      v-if="showBadge && isOnSale"
      class="px-1.5 py-0.5 text-[10px] uppercase tracking-wider font-semibold bg-[#111111] text-white"
    >
      অফার
    </span>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  price: { type: [Number, String], required: true },
  salePrice: { type: [Number, String], default: null },
  compareAtPrice: { type: [Number, String], default: null },
  size: { type: String, default: 'base' }, // 'sm', 'base', 'lg', 'xl'
  showBadge: { type: Boolean, default: false },
});

const effectivePrice = computed(() => {
  if (props.salePrice && Number(props.salePrice) > 0) {
    return Number(props.salePrice);
  }
  return Number(props.price);
});

const isOnSale = computed(() => {
  return props.salePrice && Number(props.salePrice) < Number(props.price);
});

const sizeClass = computed(() => {
  switch (props.size) {
    case 'sm': return 'text-xs';
    case 'lg': return 'text-lg';
    case 'xl': return 'text-2xl font-serif';
    default: return 'text-sm';
  }
});

const formatPrice = (val) => {
  return Number(val || 0).toFixed(2);
};
</script>
