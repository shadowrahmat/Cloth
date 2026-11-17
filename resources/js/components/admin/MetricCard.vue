<template>
  <div class="bg-white rounded-lg p-5 border border-[#E7EBE8] shadow-xs flex flex-col justify-between transition-all hover:border-[#16A36A]/40 hover:shadow-sm">
    <div class="flex items-center justify-between mb-3">
      <span class="text-xs uppercase font-medium tracking-wider text-[#69736E]">{{ title }}</span>
      <div v-if="$slots.icon" class="w-8 h-8 rounded-md bg-[#F7F8FA] border border-[#E7EBE8] flex items-center justify-center text-[#14382A]">
        <slot name="icon"></slot>
      </div>
    </div>

    <div v-if="loading" class="animate-pulse space-y-2 py-1">
      <div class="h-7 bg-slate-200 rounded-sm w-3/4"></div>
      <div class="h-3 bg-slate-100 rounded-sm w-1/2"></div>
    </div>

    <div v-else class="space-y-1">
      <div class="text-2xl sm:text-3xl font-semibold tracking-tight text-[#171C1A]">
        {{ value }}
      </div>

      <div class="flex items-center gap-2 pt-1 text-xs">
        <span
          v-if="change"
          :class="[
            'inline-flex items-center font-medium',
            trend === 'down' ? 'text-rose-600' : 'text-emerald-700'
          ]"
        >
          <span>{{ trend === 'down' ? '↓' : '↑' }} {{ change }}</span>
        </span>
        <span class="text-[#69736E] text-[11px] truncate">{{ subtext || 'vs last month' }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    required: true,
  },
  value: {
    type: [String, Number],
    default: '—',
  },
  change: {
    type: String,
    default: null,
  },
  trend: {
    type: String,
    default: 'up', // 'up' | 'down' | 'neutral'
  },
  subtext: {
    type: String,
    default: null,
  },
  loading: {
    type: Boolean,
    default: false,
  },
});
</script>
