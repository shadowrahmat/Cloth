<template>
  <div>
    <!-- Hero -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-10 sm:pt-16 pb-14 sm:pb-20 grid lg:grid-cols-12 gap-10 lg:gap-16 items-center">
      <div class="lg:col-span-5 space-y-5" v-reveal>
        <p class="text-[13px] text-[#A89B8A]">{{ about.kicker }}</p>
        <h1 class="font-serif text-4xl sm:text-5xl leading-[1.15] text-[#111111]">{{ about.title }}</h1>
        <p class="text-[15px] text-[#69736E] leading-relaxed max-w-md">{{ about.intro }}</p>
        <div class="flex flex-wrap gap-3 pt-2">
          <router-link to="/collections" class="h-11 px-6 inline-flex items-center gap-2 bg-[#111111] text-white text-sm rounded-sm hover:bg-black transition-colors">
            শপে যান <ArrowRight class="w-4 h-4" />
          </router-link>
          <router-link to="/contact" class="h-11 px-6 inline-flex items-center text-sm text-[#111111] border border-[#111111]/20 rounded-sm hover:border-[#111111] transition-colors">যোগাযোগ</router-link>
        </div>
      </div>
      <div class="lg:col-span-7" v-reveal="{ delay: 120, variant: 'zoom' }">
        <div class="aspect-[4/3] lg:aspect-[5/4] overflow-hidden bg-[#EFEBE4]">
          <img v-if="heroImage" :src="heroImage" :alt="site.siteName" class="w-full h-full object-cover" />
        </div>
      </div>
    </section>

    <!-- Stats -->
    <section v-if="about.stats?.length" class="border-y border-[#E5DED3] bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-2 lg:grid-cols-4 divide-x divide-[#E5DED3]">
        <div v-for="(s, i) in about.stats" :key="i" class="py-8 sm:py-10 px-4 text-center" v-reveal="{ delay: i * 80 }">
          <p class="font-serif text-3xl sm:text-4xl text-[#111111]">{{ s.value }}</p>
          <p class="mt-1.5 text-[13px] text-[#69736E]">{{ s.label }}</p>
        </div>
      </div>
    </section>

    <!-- Story -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24 grid lg:grid-cols-2 gap-10 lg:gap-20 items-center">
      <div class="order-2 lg:order-1" v-reveal="{ variant: 'left' }">
        <div class="aspect-[4/5] max-w-md overflow-hidden bg-[#EFEBE4]">
          <img v-if="storyImage" :src="storyImage" alt="" class="w-full h-full object-cover object-top" loading="lazy" />
        </div>
      </div>
      <div class="order-1 lg:order-2 space-y-5" v-reveal>
        <h2 class="font-serif text-3xl sm:text-4xl leading-tight text-[#111111]">{{ about.story_title }}</h2>
        <p v-for="(para, i) in paragraphs" :key="i" class="text-[15px] text-[#4B5450] leading-[1.9]">{{ para }}</p>
      </div>
    </section>

    <!-- Values -->
    <section v-if="about.values?.length" class="bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24">
        <h2 class="font-serif text-3xl sm:text-4xl text-[#111111] text-center mb-12" v-reveal>{{ about.values_title }}</h2>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-px bg-[#E5DED3] border border-[#E5DED3]">
          <div v-for="(v, i) in about.values" :key="i" class="bg-white p-7 sm:p-8 space-y-3" v-reveal="{ delay: i * 90 }">
            <component :is="VALUE_ICONS[i % VALUE_ICONS.length]" class="w-6 h-6 text-[#111111]" :stroke-width="1.5" />
            <h3 class="text-[16px] font-semibold text-[#111111]">{{ v.title }}</h3>
            <p class="text-[14px] text-[#69736E] leading-relaxed">{{ v.text }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Quote -->
    <section v-if="about.quote" class="max-w-4xl mx-auto px-4 sm:px-6 py-16 sm:py-24 text-center" v-reveal>
      <Quote class="w-8 h-8 mx-auto text-[#D8CCBC] mb-6" />
      <blockquote class="font-serif text-2xl sm:text-[34px] leading-snug text-[#111111]">{{ about.quote }}</blockquote>
      <p v-if="about.quote_author" class="mt-5 text-[13px] text-[#A89B8A]">— {{ about.quote_author }}</p>
    </section>

    <!-- Popular products -->
    <section v-if="products.length" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-16 sm:pb-24">
      <div class="flex items-end justify-between mb-8" v-reveal>
        <h2 class="font-serif text-2xl sm:text-3xl text-[#111111]">জনপ্রিয় পোশাক</h2>
        <router-link to="/collections?sort=bestseller" class="group text-[13.5px] font-medium inline-flex items-center gap-1.5">সব দেখুন <ArrowRight class="w-4 h-4 transition-transform group-hover:translate-x-1" /></router-link>
      </div>
      <ProductGrid :products="products" />
    </section>

    <!-- CTA -->
    <section v-if="about.cta_title" class="bg-[#111111] text-white">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 py-16 sm:py-20 text-center space-y-4" v-reveal>
        <h2 class="font-serif text-3xl sm:text-4xl">{{ about.cta_title }}</h2>
        <p v-if="about.cta_text" class="text-[15px] text-white/65">{{ about.cta_text }}</p>
        <SiteLink v-if="about.cta_button" :to="about.cta_url || '/collections'" class="mt-3 h-12 px-8 inline-flex items-center gap-2 bg-white text-[#111111] text-sm font-medium rounded-sm hover:bg-[#F7F5F0] transition-colors">
          {{ about.cta_button }} <ArrowRight class="w-4 h-4" />
        </SiteLink>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { ArrowRight, Quote, Sparkles, Ruler, BadgePercent, RefreshCw, Truck, Heart, ShieldCheck, Leaf } from 'lucide-vue-next';
import api from '../../api';
import { useSiteStore } from '../../stores/site';
import ProductGrid from '../../components/public/ProductGrid.vue';
import SiteLink from '../../components/common/SiteLink.vue';

const site = useSiteStore();
const about = computed(() => site.pages.about || {});
const VALUE_ICONS = [Sparkles, Ruler, BadgePercent, RefreshCw, Truck, Heart, ShieldCheck, Leaf];

const products = ref([]);
// If no images are set in the admin, borrow photos from popular products.
const heroImage = computed(() => about.value.hero_image || products.value[0]?.featured_image);
const storyImage = computed(() => about.value.story_image || products.value[1]?.featured_image || products.value[0]?.featured_image);
const paragraphs = computed(() => String(about.value.story_text || '').split(/\n\s*\n/).map((p) => p.trim()).filter(Boolean));

onMounted(async () => {
  try {
    const res = await api.get('/public/products', { params: { sort: 'bestseller', per_page: 4 } });
    products.value = res.data.data || [];
  } catch { /* section hides */ }
});
</script>
