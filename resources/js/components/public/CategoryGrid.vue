<template>
  <section class="py-20 lg:py-28 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
    <!-- Section Heading -->
    <div class="text-center max-w-2xl mx-auto mb-14 space-y-3">
      <span class="text-xs uppercase tracking-[0.25em] text-[#A89B8A] font-medium">ক্যাটাগরি</span>
      <h2 class="font-serif text-3xl sm:text-4xl text-[#111111] tracking-tight">
        {{ content.heading || 'কিউরেটেড ক্যাটাগরি সমূহ' }}
      </h2>
      <p class="text-xs sm:text-sm text-[#A89B8A] leading-relaxed">
        {{ content.description || 'আপনার দৈনন্দিন রুচিশীল পোশাকের জন্য আধুনিক কাটের সমাহার অন্বেষণ করুন।' }}
      </p>
    </div>

    <!-- 4 Department Cards (Men, Women, Kids, Winter) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
      <router-link
        v-for="cat in categories"
        :key="cat.id"
        :to="`/collection/${cat.slug}`"
        class="group relative aspect-3/4 overflow-hidden bg-[#EAE5DC] flex flex-col justify-end p-6 cursor-pointer"
      >
        <!-- Image with smooth zoom -->
        <img
          :src="cat.image"
          :alt="cat.name"
          class="absolute inset-0 w-full h-full object-cover object-top transition-transform duration-700 ease-out group-hover:scale-108"
        />

        <!-- Subtle gradient overlay -->
        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent transition-opacity group-hover:opacity-90" />

        <!-- Card Content -->
        <div class="relative z-10 text-white space-y-1.5 transition-transform duration-300 group-hover:-translate-y-1">
          <span class="text-[10px] tracking-[0.2em] uppercase text-[#D8CCBC] font-medium block">ক্যাটাগরি</span>
          <h3 class="font-serif text-2xl text-white tracking-wide">{{ cat.name }}</h3>
          <p v-if="cat.description" class="text-xs text-white/70 line-clamp-2 font-light">
            {{ cat.description }}
          </p>
          <div class="pt-2 flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-[#D8CCBC] group-hover:text-white transition-colors">
            <span>{{ cat.name }} দেখুন</span>
            <span class="transition-transform group-hover:translate-x-1">→</span>
          </div>
        </div>
      </router-link>
    </div>
  </section>
</template>

<script setup>
defineProps({
  categories: {
    type: Array,
    default: () => [],
  },
  content: {
    type: Object,
    default: () => ({}),
  },
});
</script>
