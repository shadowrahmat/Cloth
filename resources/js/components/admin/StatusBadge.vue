<template>
  <span
    :class="[
      'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-medium tracking-wide uppercase',
      badgeClass
    ]"
  >
    <span :class="['w-1.5 h-1.5 rounded-full', dotClass]"></span>
    <span>{{ label || status }}</span>
  </span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  status: {
    type: String,
    required: true,
  },
  label: {
    type: String,
    default: null,
  },
  variant: {
    type: String,
    default: null, // 'success', 'warning', 'danger', 'info', 'neutral'
  },
});

const resolvedVariant = computed(() => {
  if (props.variant) return props.variant;
  const s = (props.status || '').toLowerCase();

  if (['paid', 'delivered', 'active', 'published', 'in_stock', 'completed', 'approved', 'resolved', 'success'].includes(s)) {
    return 'success';
  }
  if (['pending', 'processing', 'unpaid', 'in_progress', 'low_stock', 'warning', 'ready_to_ship', 'packed'].includes(s)) {
    return 'warning';
  }
  if (['cancelled', 'failed', 'refunded', 'rejected', 'out_of_stock', 'danger', 'inactive'].includes(s)) {
    return 'danger';
  }
  if (['shipped', 'picked_up', 'in_transit', 'info', 'confirmed'].includes(s)) {
    return 'info';
  }
  return 'neutral';
});

const badgeClass = computed(() => {
  switch (resolvedVariant.value) {
    case 'success':
      return 'bg-emerald-50 text-emerald-800 border border-emerald-200/80';
    case 'warning':
      return 'bg-amber-50 text-amber-800 border border-amber-200/80';
    case 'danger':
      return 'bg-rose-50 text-rose-800 border border-rose-200/80';
    case 'info':
      return 'bg-sky-50 text-sky-800 border border-sky-200/80';
    default:
      return 'bg-slate-100 text-slate-700 border border-slate-200';
  }
});

const dotClass = computed(() => {
  switch (resolvedVariant.value) {
    case 'success':
      return 'bg-emerald-500';
    case 'warning':
      return 'bg-amber-500';
    case 'danger':
      return 'bg-rose-500';
    case 'info':
      return 'bg-sky-500';
    default:
      return 'bg-slate-400';
  }
});
</script>
