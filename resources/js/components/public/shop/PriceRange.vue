<template>
  <div class="space-y-4">
    <!-- Dual-thumb slider -->
    <div class="relative h-5" :style="{ '--lo': pct(lo) + '%', '--hi': pct(hi) + '%' }">
      <div class="absolute top-1/2 -translate-y-1/2 inset-x-0 h-1 rounded-full bg-[#E5DED3]"></div>
      <div class="absolute top-1/2 -translate-y-1/2 h-1 rounded-full bg-[#111111]" style="left: var(--lo); right: calc(100% - var(--hi))"></div>
      <input
        type="range" :min="min" :max="max" :step="step" :value="lo"
        @input="onLo($event.target.value)" @change="commit"
        class="range-thumb absolute inset-0 w-full h-5" aria-label="সর্বনিম্ন দাম"
      />
      <input
        type="range" :min="min" :max="max" :step="step" :value="hi"
        @input="onHi($event.target.value)" @change="commit"
        class="range-thumb absolute inset-0 w-full h-5" aria-label="সর্বোচ্চ দাম"
      />
    </div>

    <div class="flex items-center gap-2">
      <label class="flex-1 relative">
        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[12px] text-[#A89B8A]">৳</span>
        <input type="number" inputmode="numeric" :value="lo" @change="onLo($event.target.value); commit()" :min="min" :max="hi"
               class="w-full h-10 pl-7 pr-2 bg-white border border-[#E5DED3] rounded-sm text-[13px] tabular-nums focus:outline-none focus:border-[#111111]" aria-label="সর্বনিম্ন" />
      </label>
      <span class="text-[#A89B8A]">–</span>
      <label class="flex-1 relative">
        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[12px] text-[#A89B8A]">৳</span>
        <input type="number" inputmode="numeric" :value="hi" @change="onHi($event.target.value); commit()" :min="lo" :max="max"
               class="w-full h-10 pl-7 pr-2 bg-white border border-[#E5DED3] rounded-sm text-[13px] tabular-nums focus:outline-none focus:border-[#111111]" aria-label="সর্বোচ্চ" />
      </label>
    </div>

    <!-- Quick ranges -->
    <div v-if="presets.length" class="flex flex-wrap gap-1.5">
      <button
        v-for="p in presets" :key="p.label" type="button"
        @click="lo = p.lo; hi = p.hi; commit()"
        :class="['px-2.5 h-7 rounded-full border text-[12px] transition-colors', lo === p.lo && hi === p.hi ? 'bg-[#111111] text-white border-[#111111]' : 'border-[#E5DED3] text-[#111111]/75 hover:border-[#111111]']"
      >{{ p.label }}</button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { bnNumber } from '../../../utils/bn';

const props = defineProps({
  min: { type: Number, default: 0 },
  max: { type: Number, default: 0 },
  modelMin: { type: [Number, null], default: null },
  modelMax: { type: [Number, null], default: null },
});
const emit = defineEmits(['change']);

const step = computed(() => (props.max - props.min > 5000 ? 100 : 50));
const lo = ref(props.modelMin ?? props.min);
const hi = ref(props.modelMax ?? props.max);
watch(() => [props.modelMin, props.modelMax, props.min, props.max], () => {
  lo.value = props.modelMin ?? props.min;
  hi.value = props.modelMax ?? props.max;
});

const pct = (v) => (props.max > props.min ? ((v - props.min) / (props.max - props.min)) * 100 : 0);
const clamp = (v) => Math.min(props.max, Math.max(props.min, Math.round(Number(v) || 0)));
const onLo = (v) => { lo.value = Math.min(clamp(v), hi.value); };
const onHi = (v) => { hi.value = Math.max(clamp(v), lo.value); };

// Emits null for an untouched bound so the URL stays clean.
const commit = () => emit('change', {
  min: lo.value > props.min ? lo.value : null,
  max: hi.value < props.max ? hi.value : null,
});

// Three quick buckets across the available range, rounded to nice numbers.
const presets = computed(() => {
  const span = props.max - props.min;
  if (span < 300) return [];
  const round = (v) => Math.round(v / 100) * 100;
  const a = round(props.min + span / 3);
  const b = round(props.min + (2 * span) / 3);
  return [
    { label: `${bnNumber(a)} এর নিচে`, lo: props.min, hi: a },
    { label: `${bnNumber(a)} – ${bnNumber(b)}`, lo: a, hi: b },
    { label: `${bnNumber(b)} এর বেশি`, lo: b, hi: props.max },
  ];
});
</script>
