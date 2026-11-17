<template>
  <div class="py-16 flex flex-col items-center justify-center text-center">
    <div class="w-8 h-8 border-2 border-[#D8CCBC] border-t-[#111111] rounded-full animate-spin mb-3"></div>
    <p v-if="text" class="text-xs uppercase tracking-widest text-[#A89B8A] font-medium">{{ text }}</p>
  </div>
</template>

<script setup>
defineProps({
  text: { type: String, default: 'লোড হচ্ছে...' },
});
</script>
