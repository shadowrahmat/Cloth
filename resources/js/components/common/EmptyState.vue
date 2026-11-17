<template>
  <div class="py-16 px-6 text-center max-w-md mx-auto">
    <div class="w-12 h-12 mx-auto mb-4 text-[#D8CCBC] flex items-center justify-center">
      <slot name="icon">
        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
        </svg>
      </slot>
    </div>
    <h4 class="font-serif text-xl text-[#111111] mb-2">{{ title }}</h4>
    <p v-if="description" class="text-xs text-[#A89B8A] leading-relaxed mb-6">{{ description }}</p>
    <slot name="action" />
  </div>
</template>

<script setup>
defineProps({
  title: { type: String, default: 'No records discovered' },
  description: { type: String, default: 'There are currently no items matching your criteria in the archive.' },
});
</script>
