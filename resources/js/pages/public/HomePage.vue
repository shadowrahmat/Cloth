<template>
  <div>
    <!-- Sections render in the order (and visibility) set in Admin → হোমপেজ বিল্ডার -->
    <template v-for="section in sections" :key="section.id">
      <HeroBanner v-if="section.type === 'hero'" :content="section.content || {}" />

      <CategoryGrid
        v-else-if="section.type === 'category_grid'"
        :categories="featuredCategories"
        :content="section.content || {}"
      />

      <template v-else-if="section.type === 'new_arrivals'">
        <section class="py-16 lg:py-24 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="flex items-end justify-between mb-8 sm:mb-12 gap-4" v-reveal>
            <div>
              <span class="text-[11px] tracking-normal text-[#A89B8A] font-medium block mb-1">নতুন আগমন</span>
              <h2 class="font-serif text-3xl sm:text-4xl text-[#111111]">{{ section.content?.heading || 'নতুন কালেকশন' }}</h2>
            </div>
            <router-link to="/collection/new-arrivals" class="group shrink-0 text-[13px] font-medium text-[#111111] inline-flex items-center gap-1.5">
              <span class="hidden sm:inline">সব নতুন পোশাক</span><span class="sm:hidden">সব দেখুন</span>
              <ArrowRight class="w-4 h-4 transition-transform group-hover:translate-x-1" />
            </router-link>
          </div>
          <ProductGrid :products="newArrivals" />
        </section>
        <!-- Runway lookbook follows New Arrivals; switch off with "show_runway" in the builder -->
        <RunwayLookbook v-if="section.content?.show_runway !== false" :products="runwayProducts" />
      </template>

      <EditorialSection v-else-if="section.type === 'editorial'" v-reveal :content="section.content || {}" />

      <section v-else-if="section.type === 'best_sellers'" class="py-16 lg:py-24 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-10 sm:mb-14 space-y-3" v-reveal>
          <span class="text-[11px] tracking-normal text-[#A89B8A] font-medium">জনপ্রিয়</span>
          <h2 class="font-serif text-3xl sm:text-4xl text-[#111111]">{{ section.content?.heading || 'সর্বাধিক বিক্রিত পোশাক' }}</h2>
          <p class="text-sm text-[#69736E] leading-relaxed">{{ section.content?.description || 'আমাদের গ্রাহকদের সবচেয়ে পছন্দের ট্রেন্ডি ও আরামদায়ক পোশাক।' }}</p>
        </div>
        <ProductGrid :products="bestSellers" />
      </section>

      <!-- Scroll-driven category showcase (pinned; deliberately no v-reveal — a transform would break sticky) -->
      <CategoryShowcase v-else-if="section.type === 'category_showcase'" :content="section.content || {}" :items="section.showcase || []" />
      <ShopTheLook v-else-if="section.type === 'shop_the_look'" v-reveal :content="section.content || {}" />
      <BrandStory v-else-if="section.type === 'brand_story'" v-reveal :content="section.content || {}" />
      <TestimonialSlider v-else-if="section.type === 'testimonials'" v-reveal :testimonials="testimonials" :content="section.content || {}" />
      <InstagramGallery v-else-if="section.type === 'instagram_gallery'" v-reveal :gallery="socialGallery" :content="section.content || {}" />
      <NewsletterForm v-else-if="section.type === 'newsletter'" v-reveal :content="section.content || {}" />
    </template>

    <!-- First load skeleton -->
    <div v-if="loading" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
      <div class="skeleton h-[60vh] rounded-sm"></div>
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4"><div v-for="n in 4" :key="n" class="skeleton aspect-[3/4] rounded-sm"></div></div>
    </div>
  </div>
</template>
<script setup>
import { ref, computed, onMounted } from 'vue';
import { ArrowRight } from 'lucide-vue-next';
import api from '../../api';
import HeroBanner from '../../components/public/HeroBanner.vue';
import CategoryGrid from '../../components/public/CategoryGrid.vue';
import ProductGrid from '../../components/public/ProductGrid.vue';
import RunwayLookbook from '../../components/public/RunwayLookbook.vue';
import EditorialSection from '../../components/public/EditorialSection.vue';
import ShopTheLook from '../../components/public/ShopTheLook.vue';
import CategoryShowcase from '../../components/public/CategoryShowcase.vue';
import BrandStory from '../../components/public/BrandStory.vue';
import TestimonialSlider from '../../components/public/TestimonialSlider.vue';
import InstagramGallery from '../../components/public/InstagramGallery.vue';
import NewsletterForm from '../../components/public/NewsletterForm.vue';
import { refreshScrollTrigger } from '../../utils/smoothScroll';

const homepageData = ref(null);
const loading = ref(true);

const CHROME = ['announcement', 'header', 'footer'];
const sections = computed(() => (homepageData.value?.sections || []).filter((s) => !CHROME.includes(s.type)));
const featuredCategories = computed(() => homepageData.value?.featured_categories || []);
const newArrivals = computed(() => homepageData.value?.new_arrivals || []);
const bestSellers = computed(() => homepageData.value?.best_sellers || []);
const testimonials = computed(() => homepageData.value?.testimonials || []);
const socialGallery = computed(() => homepageData.value?.social_gallery || []);

const runwayProducts = computed(() => {
  if (bestSellers.value && bestSellers.value.length > 0) {
    return bestSellers.value;
  }
  if (newArrivals.value && newArrivals.value.length > 0) {
    return newArrivals.value;
  }
  return [];
});


onMounted(async () => {
  try {
    const res = await api.get('/public/homepage');
    homepageData.value = res.data.data;
  } catch (err) {
    console.error('Failed to load homepage data', err);
  } finally {
    loading.value = false;
    refreshScrollTrigger();
  }
});
</script>
