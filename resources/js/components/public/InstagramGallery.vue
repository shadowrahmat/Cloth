<template>
  <section class="py-16 bg-white border-t border-[#D8CCBC]/40">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-8 text-center space-y-2">
      <span class="text-[13px] text-[#A89B8A] font-medium">আমাদের ফ্যাশন কমিউনিটি</span>
      <h3 class="font-serif text-2xl sm:text-3xl text-[#111111]">
        {{ content.heading || 'গ্রাহকদের পছন্দের DRAWER লুক' }}
      </h3>
      <a
        :href="`https://instagram.com/${(content.handle || '@drawerclothing').replace(/^@/, '')}`"
        target="_blank"
        rel="noopener"
        class="text-xs uppercase tracking-widest text-[#A89B8A] hover:text-[#111111] transition-colors inline-block"
      >
        {{ content.handle || '@drawerclothing' }}
      </a>
    </div>

    <!-- Gallery 6 Images Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2 px-2 max-w-7xl mx-auto">
      <a
        v-for="(item, idx) in gallery"
        :key="idx"
        :href="item.link_url || 'https://instagram.com'"
        target="_blank"
        rel="noopener"
        class="group relative aspect-square overflow-hidden bg-[#F7F5F0] block"
      >
        <img
          :src="item.image"
          :alt="item.title || 'Drawer Outfit'"
          class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
        />
        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center p-2 text-center text-white">
          <svg class="w-6 h-6 mb-1 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <rect x="2" y="2" width="20" height="20" rx="5" ry="5" stroke-width="1.5"/>
            <path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z" stroke-width="1.5"/>
            <line x1="17.5" y1="6.5" x2="17.51" y2="6.5" stroke-width="2"/>
          </svg>
          <span class="text-[10px] tracking-widest font-semibold uppercase">{{ item.instagram_handle || '@drawerclothing' }}</span>
        </div>
      </a>
    </div>
  </section>
</template>

<script setup>
defineProps({
  gallery: {
    type: Array,
    default: () => [],
  },
  content: {
    type: Object,
    default: () => ({}),
  },
});
</script>
