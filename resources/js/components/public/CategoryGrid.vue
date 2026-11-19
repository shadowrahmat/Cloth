<template>
  <section class="py-14 sm:py-20 lg:py-28 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
    <!-- Heading -->
    <div class="text-center max-w-2xl mx-auto mb-8 sm:mb-14 space-y-2 sm:space-y-3">
      <h2 class="font-serif text-[26px] sm:text-4xl text-[#111111] tracking-tight">
        {{ content.heading || 'কিউরেটেড ক্যাটাগরি সমূহ' }}
      </h2>
      <p class="text-[13px] sm:text-sm text-[#69736E] leading-relaxed">
        {{ content.description || 'আপনার দৈনন্দিন রুচিশীল পোশাকের জন্য আধুনিক কাটের সমাহার অন্বেষণ করুন।' }}
      </p>
    </div>

    <!-- 2×2 on phones, 4 across on desktop -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6">
      <router-link
        v-for="(cat, i) in categories"
        :key="cat.id"
        :to="`/category/${cat.slug}`"
        class="group relative aspect-[3/4] overflow-hidden bg-[#EAE5DC] flex flex-col justify-end p-3.5 sm:p-6"
        v-reveal="{ delay: i * 70 }"
      >
        <img
          :src="cat.image"
          :alt="cat.name"
          loading="lazy"
          class="absolute inset-0 w-full h-full object-cover object-top transition-transform duration-700 ease-out group-hover:scale-105"
        />
        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/10 to-transparent" />

        <div class="relative z-10 text-white transition-transform duration-300 group-hover:-translate-y-1">
          <h3 class="font-serif text-lg sm:text-2xl leading-tight">{{ cat.name }}</h3>
          <p v-if="cat.description" class="hidden sm:block mt-1.5 text-xs text-white/70 line-clamp-2">{{ cat.description }}</p>
          <span class="mt-1.5 sm:mt-3 inline-flex items-center gap-1 text-[12px] sm:text-[13px] text-white/85 group-hover:text-white">
            দেখুন <span class="transition-transform group-hover:translate-x-1">→</span>
          </span>
        </div>
      </router-link>
    </div>
  </section>
</template>

<script setup>
defineProps({
  categories: { type: Array, default: () => [] },
  content: { type: Object, default: () => ({}) },
});
</script>
