<template>
  <section class="py-20 lg:py-28 bg-[#EAE5DC]/60 overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-center">
        <!-- Editorial Image -->
        <div class="lg:col-span-7 relative aspect-4/5 sm:aspect-16/11 overflow-hidden bg-white shadow-xl">
          <img
            :src="content.image || 'https://images.unsplash.com/photo-1445205170230-053b83016050?auto=format&fit=crop&w=1200&q=80'"
            alt="Editorial Campaign"
            class="w-full h-full object-cover"
          />
          <div class="absolute bottom-6 left-6 bg-white/90 backdrop-blur-xs py-2 px-4 shadow-xs">
            <span class="text-[10px] tracking-[0.25em] uppercase font-semibold text-[#111111]">Biella Mills, Italy</span>
          </div>
        </div>

        <!-- Editorial Story -->
        <div class="lg:col-span-5 space-y-6 lg:pl-4">
          <span class="text-xs uppercase tracking-[0.3em] text-[#A89B8A] font-medium block">Editorial Essay</span>

          <h2 class="font-serif text-3xl sm:text-5xl text-[#111111] leading-tight">
            {{ content.heading || 'The Art of Everyday Dressing' }}
          </h2>

          <blockquote class="font-serif italic text-lg sm:text-xl text-[#111111]/80 border-l-2 border-[#111111] pl-4 my-4">
            "{{ content.quote || 'Style is more than what you wear. It is how you express your inner calm and deliberate presence.' }}"
          </blockquote>

          <p class="text-xs sm:text-sm text-[#111111]/70 font-light leading-relaxed">
            {{ content.description || 'Our Autumn/Winter collection centers on tactile warmth—unlined double-faced cashmere coats, fluid silk slips, and precision-cut virgin wool flannel engineered to move without friction.' }}
          </p>

          <div class="pt-4">
            <router-link
              :to="content.cta_link || '/pages/about-us'"
              class="inline-flex items-center gap-2 text-xs uppercase tracking-[0.2em] font-semibold text-[#111111] border-b-2 border-[#111111] pb-1 hover:text-[#A89B8A] hover:border-[#A89B8A] transition-colors"
            >
              <span>{{ content.cta_text || 'Read The Journal' }}</span>
              <span>→</span>
            </router-link>
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
