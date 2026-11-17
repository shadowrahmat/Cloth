<template>
  <div
    v-if="visible && content.enabled !== false"
    class="relative z-40 text-center py-2.5 px-4 text-xs tracking-widest uppercase font-medium transition-all"
    :style="{ backgroundColor: content.bg_color || '#111111', color: content.text_color || '#FFFFFF' }"
  >
    <div class="max-w-7xl mx-auto flex items-center justify-center gap-3">
      <span>{{ content.text || 'Complimentary Worldwide Shipping on Orders Over $150' }}</span>
      <router-link
        v-if="content.link_text && content.link_url"
        :to="content.link_url"
        class="underline underline-offset-4 font-semibold hover:opacity-80 transition-opacity"
      >
        {{ content.link_text }}
      </router-link>
    </div>

    <!-- Dismiss button -->
    <button
      type="button"
      @click="visible = false"
      class="absolute right-4 top-1/2 -translate-y-1/2 opacity-60 hover:opacity-100 transition-opacity"
      aria-label="Dismiss announcement"
    >
      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
      </svg>
    </button>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  content: {
    type: Object,
    default: () => ({
      text: 'সারাদেশে ২৫০০ টাকার বেশি অর্ডারে ফ্রি ডেলিভারি • সহজ সাইজ এক্সচেঞ্জ সুবিধা',
      link_url: '/collections',
      link_text: 'কালেকশন দেখুন',
      enabled: true,
    }),
  },
});

const visible = ref(true);
</script>
