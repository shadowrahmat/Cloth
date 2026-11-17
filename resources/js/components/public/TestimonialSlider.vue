<template>
  <section class="py-20 lg:py-28 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center max-w-2xl mx-auto mb-14 space-y-3">
      <span class="text-xs uppercase tracking-[0.25em] text-[#A89B8A] font-medium">গ্রাহকদের মতামত</span>
      <h2 class="font-serif text-3xl sm:text-4xl text-[#111111] tracking-tight">
        {{ content.heading || 'গ্রাহকদের অনুভূতি ও অভিজ্ঞতা' }}
      </h2>
      <p class="text-xs sm:text-sm text-[#A89B8A] leading-relaxed">
        {{ content.description || 'DRAWER CLOTHING থেকে পণ্য ক্রয়কারী সম্মানিত গ্রাহকদের মূল্যবান রিভিউ ও অভিজ্ঞতা।' }}
      </p>
    </div>

    <!-- Testimonials Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
      <div
        v-for="(t, idx) in testimonials"
        :key="idx"
        class="bg-white p-8 border border-[#D8CCBC]/40 shadow-xs flex flex-col justify-between"
      >
        <div class="space-y-4">
          <!-- Stars -->
          <div class="flex text-[#C5A880] text-sm">
            <span v-for="star in (t.rating || 5)" :key="star">★</span>
          </div>

          <!-- Review -->
          <p class="font-serif italic text-base sm:text-lg text-[#111111]/85 leading-relaxed">
            "{{ t.review }}"
          </p>
        </div>

        <div class="flex items-center gap-3 pt-6 border-t border-[#D8CCBC]/30 mt-6">
          <img
            v-if="t.customer_image"
            :src="t.customer_image"
            :alt="t.customer_name"
            class="w-10 h-10 rounded-full object-cover shrink-0"
          />
          <div class="w-10 h-10 rounded-full bg-[#EAE5DC] flex items-center justify-center font-serif text-sm text-[#111111] shrink-0" v-else>
            {{ t.customer_name?.charAt(0) || 'C' }}
          </div>
          <div>
            <h6 class="font-sans font-semibold text-xs text-[#111111]">{{ t.customer_name }}</h6>
            <span v-if="t.product_purchased" class="text-[11px] text-[#A89B8A] block">
              ক্রয় করেছেন: {{ t.product_purchased }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
defineProps({
  testimonials: {
    type: Array,
    default: () => [],
  },
  content: {
    type: Object,
    default: () => ({}),
  },
});
</script>
