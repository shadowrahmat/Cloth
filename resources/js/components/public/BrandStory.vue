<template>
  <section class="py-20 lg:py-28 bg-[#111111] text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="max-w-3xl mx-auto text-center space-y-6">
        <span class="text-xs uppercase tracking-[0.3em] text-[#D8CCBC] font-medium block">আমাদের দর্শন ও ঐতিহ্য</span>

        <h2 class="font-serif text-3xl sm:text-5xl text-white tracking-tight">
          {{ content.heading || 'পরিমিতিবোধ ও নিখুঁত কারিগরি শিল্প' }}
        </h2>

        <p class="text-sm sm:text-base text-white/70 font-light leading-relaxed">
          {{ content.description || 'DRAWER CLOTHING প্রতিষ্ঠিত হয়েছে সমকালীন আভিজাত্য ও আধুনিক লাইফস্টাইলের নিখুঁত মেলবন্ধনে। প্রতিটি ফেব্রিক বাছাই থেকে শুরু করে নিখুঁত সেলাই পর্যন্ত—আমরা তৈরি করি এমন সব পোশাক যা আপনাকে দেয় অনন্য ফ্যাশন ও স্বাচ্ছন্দ্যময় অনুভূতি।' }}
        </p>

        <!-- Key Pillars / Numbers -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-8 pt-10 border-t border-white/10 mt-12">
          <div class="space-y-1">
            <span class="font-serif text-3xl sm:text-4xl text-[#D8CCBC] block">
              {{ content.stat_1_number || '১০০%' }}
            </span>
            <span class="text-xs text-white/60 uppercase tracking-widest">
              {{ content.stat_1_label || 'প্রিমিয়াম সুতি ও ফেব্রিক' }}
            </span>
          </div>

          <div class="space-y-1">
            <span class="font-serif text-3xl sm:text-4xl text-[#D8CCBC] block">
              {{ content.stat_2_number || '২৪/৭' }}
            </span>
            <span class="text-xs text-white/60 uppercase tracking-widest">
              {{ content.stat_2_label || 'সার্বক্ষণিক গ্রাহক সেবা' }}
            </span>
          </div>

          <div class="space-y-1">
            <span class="font-serif text-3xl sm:text-4xl text-[#D8CCBC] block">
              {{ content.stat_3_number || '৭ দিন' }}
            </span>
            <span class="text-xs text-white/60 uppercase tracking-widest">
              {{ content.stat_3_label || 'সহজ এক্সচেঞ্জ সুবিধা' }}
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
