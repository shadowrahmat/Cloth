<template>
  <div>
    <!-- Section 03: Hero Campaign Banner -->
    <HeroBanner
      v-if="heroSection"
      :content="heroSection.content || {}"
    />

    <!-- Section 04: Featured Categories (Men, Women, Kids, Winter) -->
    <CategoryGrid
      v-if="categoryGridSection"
      :categories="featuredCategories"
      :content="categoryGridSection.content || {}"
    />

    <!-- Section 05: New Arrivals -->
    <section v-if="newArrivalsSection" class="py-20 lg:py-28 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-12 border-b border-[#D8CCBC]/40 pb-4 gap-4">
        <div>
          <span class="text-xs tracking-[0.25em] text-[#A89B8A] font-medium block">নতুন আগমন</span>
          <h2 class="font-serif text-3xl sm:text-4xl text-[#111111]">
            {{ newArrivalsSection.content?.heading || 'নতুন কালেকশন' }}
          </h2>
        </div>
        <router-link
          to="/collection/new-arrivals"
          class="text-xs font-semibold text-[#111111] hover:text-[#16A36A] transition-colors inline-flex items-center gap-1.5"
        >
          <span>সকল নতুন পোশাক দেখুন</span>
          <span>→</span>
        </router-link>
      </div>

      <ProductGrid :products="newArrivals" />
    </section>

    <!-- Section 05.5: Awwwards-Honored Pinned Horizontal Runway Lookbook -->
    <RunwayLookbook :products="runwayProducts" />

    <!-- Section 06: Editorial Campaign ("The Art of Everyday Dressing") -->
    <EditorialSection
      v-if="editorialSection"
      :content="editorialSection.content || {}"
    />

    <!-- Section 07: Best Sellers -->
    <section v-if="bestSellersSection" class="py-20 lg:py-28 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center max-w-2xl mx-auto mb-14 space-y-3">
        <span class="text-xs tracking-[0.25em] text-[#A89B8A] font-medium">জনপ্রিয় কালেকশন</span>
        <h2 class="font-serif text-3xl sm:text-4xl text-[#111111]">
          {{ bestSellersSection.content?.heading || 'সর্বাধিক বিক্রিত পোশাক' }}
        </h2>
        <p class="text-xs sm:text-sm text-[#A89B8A] leading-relaxed">
          {{ bestSellersSection.content?.description || 'আমাদের গ্রাহকদের সবচেয়ে পছন্দের ট্রেন্ডি ও আরামদায়ক পোশাক।' }}
        </p>
      </div>

      <ProductGrid :products="bestSellers" />
    </section>

    <!-- Section 08: Shop The Look (Interactive Lookbook Hotspots!) -->
    <ShopTheLook
      v-if="shopTheLookSection"
      :content="shopTheLookSection.content || {}"
    />

    <!-- Section 09: Brand Story ("Designed With Intention") -->
    <BrandStory
      v-if="brandStorySection"
      :content="brandStorySection.content || {}"
    />

    <!-- Section 10: Customer Testimonials -->
    <TestimonialSlider
      v-if="testimonialsSection"
      :testimonials="testimonials"
      :content="testimonialsSection.content || {}"
    />

    <!-- Section 11: Instagram & Community Gallery -->
    <InstagramGallery
      v-if="instagramSection"
      :gallery="socialGallery"
      :content="instagramSection.content || {}"
    />

    <!-- Section 12: Newsletter Subscription -->
    <NewsletterForm
      v-if="newsletterSection"
      :content="newsletterSection.content || {}"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import api from '../../api';
import HeroBanner from '../../components/public/HeroBanner.vue';
import CategoryGrid from '../../components/public/CategoryGrid.vue';
import ProductGrid from '../../components/public/ProductGrid.vue';
import RunwayLookbook from '../../components/public/RunwayLookbook.vue';
import EditorialSection from '../../components/public/EditorialSection.vue';
import ShopTheLook from '../../components/public/ShopTheLook.vue';
import BrandStory from '../../components/public/BrandStory.vue';
import TestimonialSlider from '../../components/public/TestimonialSlider.vue';
import InstagramGallery from '../../components/public/InstagramGallery.vue';
import NewsletterForm from '../../components/public/NewsletterForm.vue';
import { refreshScrollTrigger } from '../../utils/smoothScroll';

const homepageData = ref(null);
const loading = ref(true);

const sections = computed(() => homepageData.value?.sections || []);
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

// Section Lookups
const heroSection = computed(() => sections.value.find(s => s.type === 'hero'));
const categoryGridSection = computed(() => sections.value.find(s => s.type === 'category_grid'));
const newArrivalsSection = computed(() => sections.value.find(s => s.type === 'new_arrivals'));
const editorialSection = computed(() => sections.value.find(s => s.type === 'editorial'));
const bestSellersSection = computed(() => sections.value.find(s => s.type === 'best_sellers'));
const shopTheLookSection = computed(() => sections.value.find(s => s.type === 'shop_the_look'));
const brandStorySection = computed(() => sections.value.find(s => s.type === 'brand_story'));
const testimonialsSection = computed(() => sections.value.find(s => s.type === 'testimonials'));
const instagramSection = computed(() => sections.value.find(s => s.type === 'instagram_gallery'));
const newsletterSection = computed(() => sections.value.find(s => s.type === 'newsletter'));

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
