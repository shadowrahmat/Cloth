<template>
  <section class="py-20 lg:py-28 bg-[#111111] text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="max-w-3xl mx-auto text-center space-y-6">
        <span class="text-xs uppercase tracking-[0.3em] text-[#D8CCBC] font-medium block">Philosophy</span>

        <h2 class="font-serif text-3xl sm:text-5xl text-white tracking-tight">
          {{ content.heading || 'Designed With Intention.' }}
        </h2>

        <p class="text-sm sm:text-base text-white/70 font-light leading-relaxed">
          {{ content.description || 'Founded with a resolute commitment to thoughtful design, DRAWER CLOTHING unites heritage craftsmanship with forward-looking contemporary practice. Each garment is created to be worn and cherished for generations.' }}
        </p>

        <!-- Key Pillars / Numbers -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-8 pt-10 border-t border-white/10 mt-12">
          <div class="space-y-1">
            <span class="font-serif text-3xl sm:text-4xl text-[#D8CCBC] block">
              {{ content.stat_1_number || '100%' }}
            </span>
            <span class="text-xs text-white/60 uppercase tracking-widest">
              {{ content.stat_1_label || 'Certified Organic Fibers' }}
            </span>
          </div>

          <div class="space-y-1">
            <span class="font-serif text-3xl sm:text-4xl text-[#D8CCBC] block">
              {{ content.stat_2_number || '40+' }}
            </span>
            <span class="text-xs text-white/60 uppercase tracking-widest">
              {{ content.stat_2_label || 'Hours Artisan Tailoring' }}
            </span>
          </div>

          <div class="space-y-1">
            <span class="font-serif text-3xl sm:text-4xl text-[#D8CCBC] block">
              {{ content.stat_3_number || '30' }}
            </span>
            <span class="text-xs text-white/60 uppercase tracking-widest">
              {{ content.stat_3_label || 'Days Global Returns' }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
defineProps({
  content: {
    type: Object,
    default: () => ({}),
  },
});
</script>
