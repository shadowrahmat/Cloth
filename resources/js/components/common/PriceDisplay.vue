<template>
  <div class="inline-flex items-baseline gap-2 tabular-nums">
    <span :class="[sizeClass, 'font-medium', struckPrice ? 'text-[#B4232A]' : 'text-[#111111]']">
      {{ taka(effectivePrice) }}
    </span>
    <span v-if="struckPrice" class="text-xs text-[#A89B8A] line-through font-normal">
      {{ taka(struckPrice) }}
    </span>
    <span
      v-if="showBadge && struckPrice"
      class="px-1.5 py-0.5 text-[10px] font-semibold bg-[#111111] text-white"
    >
      -{{ bnNumber(Math.round((1 - effectivePrice / struckPrice) * 100)) }}%
    </span>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { taka, bnNumber } from '../../utils/bn';

const props = defineProps({
  price: { type: [Number, String], required: true },
  salePrice: { type: [Number, String], default: null },
  compareAtPrice: { type: [Number, String], default: null },
  size: { type: String, default: 'base' }, // 'sm', 'base', 'lg', 'xl'
  showBadge: { type: Boolean, default: false },
});

const effectivePrice = computed(() => {
  const sale = Number(props.salePrice);
  return sale > 0 && sale < Number(props.price) ? sale : Number(props.price);
});

// The higher "was" price to strike through: compare-at price, or the list price when on sale.
const struckPrice = computed(() => {
  const compare = Number(props.compareAtPrice);
  if (compare > effectivePrice.value) return compare;
  return Number(props.price) > effectivePrice.value ? Number(props.price) : null;
});

const sizeClass = computed(() => ({ sm: 'text-xs', lg: 'text-lg', xl: 'text-2xl font-serif' }[props.size] || 'text-sm'));
</script>
