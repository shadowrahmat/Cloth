<template>
  <component
    :is="to ? 'router-link' : 'div'"
    :to="to || undefined"
    :class="[
      'block bg-white rounded-xl px-4 py-3.5 border border-[#ECEFED] transition-colors',
      to ? 'hover:border-[#16A36A]/40' : '',
    ]"
  >
    <div class="flex items-center gap-2 text-[12.5px] text-[#69736E]">
      <span v-if="$slots.icon" class="text-[#16A36A] shrink-0 flex"><slot name="icon"></slot></span>
      <span class="truncate">{{ title }}</span>
    </div>

    <div v-if="loading" class="animate-pulse mt-2.5 space-y-1.5">
      <div class="h-6 bg-slate-100 rounded w-2/3"></div>
      <div class="h-3 bg-slate-50 rounded w-1/2"></div>
    </div>

    <template v-else>
      <div class="mt-1.5 text-[22px] leading-tight font-bold tracking-tight text-[#171C1A] tabular-nums">{{ value }}</div>

      <div v-if="hasChange || change || subtext" class="mt-1 flex items-center gap-1.5 text-[11.5px] min-w-0">
        <!-- Real period-over-period change (number, e.g. 12.5 or -3) -->
        <span
          v-if="hasChange"
          :class="[
            'shrink-0 font-semibold tabular-nums',
            changePct > 0 ? 'text-emerald-600' : changePct < 0 ? 'text-rose-600' : 'text-slate-500',
          ]"
        >
          {{ changePct > 0 ? '↑' : changePct < 0 ? '↓' : '→' }} {{ bnNumber(Math.abs(changePct)) }}%
        </span>
        <!-- Free-text tag (legacy) -->
        <span v-else-if="change" :class="['shrink-0 font-medium', trend === 'down' ? 'text-rose-600' : 'text-emerald-700']">{{ change }}</span>
        <span v-if="subtext" class="text-[#9AA39F] truncate">{{ subtext }}</span>
      </div>
    </template>
  </component>
</template>

<script setup>
import { computed } from 'vue';
import { bnNumber } from '../../admin-nav';

const props = defineProps({
  title: { type: String, required: true },
  value: { type: [String, Number], default: '—' },
  /** Percentage change vs the previous period; null/undefined hides it. */
  changePct: { type: Number, default: null },
  /** Optional free-text tag, shown only when changePct is absent. */
  change: { type: String, default: null },
  trend: { type: String, default: 'up' },
  subtext: { type: String, default: null },
  loading: { type: Boolean, default: false },
  /** Makes the whole card a link. */
  to: { type: [String, Object], default: null },
});

const hasChange = computed(() => typeof props.changePct === 'number' && !Number.isNaN(props.changePct));
</script>
