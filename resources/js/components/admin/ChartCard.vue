<template>
  <div class="bg-white rounded-xl px-4 py-3.5 border border-[#ECEFED] flex flex-col">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3">
      <div>
        <h3 class="text-sm font-semibold text-[#171C1A] tracking-tight">{{ title }}</h3>
        <p v-if="subtitle" class="text-[11.5px] text-[#9AA39F] mt-0.5">{{ subtitle }}</p>
      </div>

      <div class="flex items-center gap-2">
        <slot name="actions">
          <select
            v-if="filterOptions && filterOptions.length"
            :value="selectedFilter"
            @change="$emit('update:selectedFilter', $event.target.value)"
            class="text-xs bg-[#F7F8FA] border border-[#E7EBE8] rounded-md px-3 py-1.5 text-[#171C1A] focus:outline-none focus:ring-1 focus:ring-[#16A36A]"
          >
            <option v-for="opt in filterOptions" :key="opt.value || opt" :value="opt.value || opt">
              {{ opt.label || opt }}
            </option>
          </select>
        </slot>
      </div>
    </div>

    <!-- Chart Canvas Area -->
    <div class="flex-1 min-h-[224px] relative">
      <slot></slot>
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    required: true,
  },
  subtitle: {
    type: String,
    default: null,
  },
  // Only shown when a page passes options AND listens to update:selectedFilter.
  filterOptions: {
    type: Array,
    default: () => [],
  },
  selectedFilter: {
    type: String,
    default: 'Last 30 Days',
  },
});

defineEmits(['update:selectedFilter']);
</script>
