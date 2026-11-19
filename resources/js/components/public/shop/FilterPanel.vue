<template>
  <div class="divide-y divide-[#E5DED3]/70">
    <!-- Categories (only on the all-products page; category pages use the pills in the header) -->
    <FilterSection v-if="showCategories && categories.length" title="ক্যাটাগরি" :count="filters.categories.length" v-model:open="open.category">
      <ul class="space-y-0.5">
        <li v-for="c in visibleCategories" :key="c.slug">
          <label :class="['flex items-center gap-3 py-1.5 cursor-pointer group', c.is_child && 'pl-4']">
            <input type="checkbox" class="peer sr-only" :checked="filters.categories.includes(c.slug)" @change="toggle('categories', c.slug)" />
            <span class="w-[18px] h-[18px] rounded-[4px] border border-[#CFC6B8] flex items-center justify-center transition-colors peer-checked:bg-[#111111] peer-checked:border-[#111111] peer-focus-visible:ring-2 peer-focus-visible:ring-black/20">
              <Check v-if="filters.categories.includes(c.slug)" class="w-3 h-3 text-white" :stroke-width="3" />
            </span>
            <span class="flex-1 text-[14px] text-[#111111]/85 group-hover:text-[#111111]">{{ c.name }}</span>
            <span class="text-[12px] text-[#A89B8A] tabular-nums">{{ bnNumber(c.count) }}</span>
          </label>
        </li>
      </ul>
      <button v-if="categories.length > 8" type="button" @click="showAllCats = !showAllCats" class="mt-1 text-[13px] font-medium text-[#111111] underline underline-offset-4">
        {{ showAllCats ? 'কম দেখান' : `আরও ${bnNumber(categories.length - 8)}টি দেখুন` }}
      </button>
    </FilterSection>

    <!-- Size -->
    <FilterSection v-if="sizes.length" title="সাইজ" :count="filters.size.length" v-model:open="open.size">
      <div class="flex flex-wrap gap-2">
        <button
          v-for="s in sizes" :key="s.value" type="button"
          @click="toggle('size', s.value)"
          :disabled="!s.count && !filters.size.includes(s.value)"
          :aria-pressed="filters.size.includes(s.value)"
          :title="`${bnNumber(s.count)}টি পণ্য`"
          :class="[
            'min-w-11 h-10 px-3 rounded-sm border text-[13px] font-medium transition-all duration-200',
            filters.size.includes(s.value)
              ? 'bg-[#111111] text-white border-[#111111] shadow-sm'
              : 'bg-white text-[#111111] border-[#E5DED3] hover:border-[#111111]',
            'disabled:opacity-30 disabled:line-through disabled:cursor-not-allowed',
          ]"
        >{{ s.value }}</button>
      </div>
    </FilterSection>

    <!-- Colour (only when products have colours) -->
    <FilterSection v-if="colors.length" title="রং" :count="filters.color.length" v-model:open="open.color">
      <div class="flex flex-wrap gap-2.5">
        <button
          v-for="c in colors" :key="c.value" type="button"
          @click="toggle('color', c.value)"
          :aria-pressed="filters.color.includes(c.value)"
          class="group flex flex-col items-center gap-1 w-14"
          :title="`${c.value} (${bnNumber(c.count)})`"
        >
          <span :class="['w-9 h-9 rounded-full border transition-all', filters.color.includes(c.value) ? 'ring-2 ring-offset-2 ring-[#111111] border-transparent' : 'border-black/10 group-hover:scale-110']" :style="{ background: c.code || '#ddd' }"></span>
          <span class="text-[11px] text-[#111111]/70 truncate max-w-full">{{ c.value }}</span>
        </button>
      </div>
    </FilterSection>

    <!-- Price -->
    <FilterSection v-if="facets.price && facets.price.max > facets.price.min" title="দাম" :count="filters.min_price !== null || filters.max_price !== null ? 1 : 0" v-model:open="open.price">
      <PriceRange
        :min="priceMin" :max="priceMax"
        :model-min="filters.min_price" :model-max="filters.max_price"
        @change="(r) => emit('update', { min_price: r.min, max_price: r.max })"
      />
    </FilterSection>

    <!-- Toggles -->
    <div class="py-4 space-y-3">
      <SwitchRow :checked="filters.on_sale" @toggle="emit('update', { on_sale: !filters.on_sale })" label="শুধু অফারের পণ্য" :count="facets.on_sale" />
      <SwitchRow :checked="filters.in_stock" @toggle="emit('update', { in_stock: !filters.in_stock })" label="স্টকে আছে এমন পণ্য" :count="facets.in_stock" />
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, h } from 'vue';
import { Check, ChevronDown } from 'lucide-vue-next';
import PriceRange from './PriceRange.vue';
import { bnNumber } from '../../../utils/bn';

const props = defineProps({
  facets: { type: Object, required: true },
  filters: { type: Object, required: true },
  showCategories: { type: Boolean, default: true },
});
const emit = defineEmits(['update']);

const open = reactive({ category: true, size: true, color: true, price: true });
const showAllCats = ref(false);

const categories = computed(() => props.facets.categories || []);
const visibleCategories = computed(() => (showAllCats.value ? categories.value : categories.value.slice(0, 8)));

// Keep selected values visible even if the current results have none of them.
const withSelected = (list, selected) => {
  const out = [...(list || [])];
  selected.forEach((v) => { if (!out.some((o) => o.value === v)) out.push({ value: v, count: 0 }); });
  return out;
};
const sizes = computed(() => withSelected(props.facets.sizes, props.filters.size));
const colors = computed(() => withSelected(props.facets.colors, props.filters.color));

const priceMin = computed(() => Math.floor((props.facets.price?.min || 0) / 50) * 50);
const priceMax = computed(() => Math.ceil((props.facets.price?.max || 0) / 50) * 50);

const toggle = (key, value) => {
  const list = props.filters[key];
  emit('update', { [key]: list.includes(value) ? list.filter((v) => v !== value) : [...list, value] });
};

// --- tiny internal components -------------------------------------------
const FilterSection = (p, { slots, emit: e }) => h('section', { class: 'py-4' }, [
  h('button', {
    type: 'button',
    class: 'w-full flex items-center justify-between text-left',
    'aria-expanded': String(p.open),
    onClick: () => e('update:open', !p.open),
  }, [
    h('span', { class: 'flex items-center gap-2 text-[14px] font-semibold text-[#111111]' }, [
      p.title,
      p.count ? h('span', { class: 'min-w-5 h-5 px-1.5 rounded-full bg-[#111111] text-white text-[11px] flex items-center justify-center' }, bnNumber(p.count)) : null,
    ]),
    h(ChevronDown, { class: ['w-4 h-4 text-[#A89B8A] transition-transform duration-300', p.open ? 'rotate-180' : ''] }),
  ]),
  h('div', { class: 'grid transition-[grid-template-rows] duration-300 ease-out', style: { gridTemplateRows: p.open ? '1fr' : '0fr' } }, [
    h('div', { class: 'overflow-hidden' }, [h('div', { class: 'pt-3.5' }, slots.default?.())]),
  ]),
]);
FilterSection.props = ['title', 'count', 'open'];
FilterSection.emits = ['update:open'];

const SwitchRow = (p, { emit: e }) => h('button', {
  type: 'button', role: 'switch', 'aria-checked': String(!!p.checked),
  class: 'w-full flex items-center gap-3 text-left',
  onClick: () => e('toggle'),
}, [
  h('span', { class: ['relative w-9 h-5 rounded-full transition-colors shrink-0', p.checked ? 'bg-[#111111]' : 'bg-[#D8CCBC]'] }, [
    h('span', { class: ['absolute top-0.5 left-0.5 w-4 h-4 rounded-full bg-white shadow transition-transform duration-200', p.checked ? 'translate-x-4' : ''] }),
  ]),
  h('span', { class: 'flex-1 text-[14px] text-[#111111]/85' }, p.label),
  typeof p.count === 'number' ? h('span', { class: 'text-[12px] text-[#A89B8A] tabular-nums' }, bnNumber(p.count)) : null,
]);
SwitchRow.props = ['checked', 'label', 'count'];
SwitchRow.emits = ['toggle'];
</script>
