<template>
  <div
    class="absolute top-full left-0 w-full bg-white border-b border-[#D8CCBC]/40 shadow-xl transition-all duration-300 py-10 px-8 z-50"
    @mouseenter="$emit('mouseenter')"
    @mouseleave="$emit('mouseleave')"
  >
    <div class="max-w-7xl mx-auto grid grid-cols-12 gap-8">
      <!-- 4 Category Columns: Men, Women, Kids, Winter -->
      <div
        v-for="cat in categories"
        :key="cat.id"
        class="col-span-2 space-y-4"
      >
        <router-link
          :to="`/collection/${cat.slug}`"
          class="font-serif text-lg text-[#111111] hover:text-[#A89B8A] transition-colors block border-b border-[#D8CCBC]/30 pb-2"
          @click="$emit('close')"
        >
          {{ cat.name }}
        </router-link>

        <ul class="space-y-2.5 text-xs text-[#111111]/70">
          <li v-for="sub in cat.children" :key="sub.id">
            <router-link
              :to="`/collection/${sub.slug}`"
              class="hover:text-[#111111] transition-colors hover:translate-x-0.5 inline-block"
              @click="$emit('close')"
            >
              {{ sub.name }}
            </router-link>
          </li>
          <li class="pt-2">
            <router-link
              :to="`/collection/${cat.slug}`"
              class="text-[11px] font-semibold tracking-wider uppercase text-[#111111] underline underline-offset-4"
              @click="$emit('close')"
            >
              View All {{ cat.name }} →
            </router-link>
          </li>
        </ul>
      </div>

      <!-- Editorial Showcase Column -->
      <div class="col-span-4 pl-6 border-l border-[#D8CCBC]/30 flex flex-col justify-between">
        <div class="relative overflow-hidden group aspect-16/10 mb-3 bg-[#F7F5F0]">
          <img
            src="https://images.unsplash.com/photo-1490481651871-ab68de25d43d?auto=format&fit=crop&w=800&q=80"
            alt="The Autumn/Winter Editorial"
            class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
          />
          <div class="absolute inset-0 bg-black/20 flex items-end p-4">
            <span class="text-white text-xs tracking-widest uppercase font-semibold">The Winter Edit</span>
          </div>
        </div>
        <div>
          <h5 class="font-serif text-base text-[#111111] mb-1">Architectural Outerwear & Knits</h5>
          <p class="text-xs text-[#A89B8A] mb-3">Pure Mongolian cashmere, tailored wool topcoats, and minimalist shearling jackets.</p>
          <router-link
            to="/collection/winter"
            class="text-xs font-semibold uppercase tracking-widest text-[#111111] hover:text-[#A89B8A] transition-colors inline-flex items-center gap-1.5"
            @click="$emit('close')"
          >
            Explore Capsule Collection
            <span>→</span>
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  categories: {
    type: Array,
    default: () => [],
  },
});

defineEmits(['mouseenter', 'mouseleave', 'close']);
</script>
