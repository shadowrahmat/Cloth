<template>
  <div class="flex items-center gap-1.5 flex-wrap">
    <button
      v-for="color in colors"
      :key="color.name || color"
      type="button"
      @click="$emit('select', color.name || color)"
      :title="color.name || color"
      :class="[
        'w-3.5 h-3.5 rounded-full border transition-all duration-200',
        isSelected(color.name || color) ? 'ring-1 ring-offset-1 ring-[#111111] scale-110' : 'border-black/20 hover:scale-105'
      ]"
      :style="{ backgroundColor: getColorCode(color) }"
    />
  </div>
</template>

<script setup>
const props = defineProps({
  colors: { type: Array, default: () => [] },
  modelValue: { type: String, default: '' },
});

defineEmits(['select']);

const colorPalette = {
  'black': '#111111',
  'white': '#FFFFFF',
  'pure white': '#FFFFFF',
  'ivory': '#F7F5F0',
  'sand': '#D8CCBC',
  'taupe': '#A89B8A',
  'navy': '#1B2A4A',
  'charcoal': '#2C2C2C',
  'olive': '#555D50',
  'camel': '#C19A6B',
};

const getColorCode = (color) => {
  if (color && typeof color === 'object' && color.code) {
    return color.code;
  }
  const name = (typeof color === 'string' ? color : color?.name || '').toLowerCase();
  return colorPalette[name] || '#111111';
};

const isSelected = (name) => {
  return props.modelValue && props.modelValue.toLowerCase() === name.toLowerCase();
};
</script>
