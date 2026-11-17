<template>
  <div v-if="product" class="py-10 sm:py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Breadcrumb -->
    <nav class="flex items-center space-x-2 text-[11px] uppercase tracking-wider text-[#A89B8A] mb-8">
      <router-link to="/" class="hover:text-[#111111]">হোম</router-link>
      <span>/</span>
      <router-link
        v-if="product.category"
        :to="`/collection/${product.category.slug}`"
        class="hover:text-[#111111]"
      >
        {{ product.category.name }}
      </router-link>
      <span>/</span>
      <span class="text-[#111111] truncate max-w-xs">{{ product.name }}</span>
    </nav>

    <!-- Product Layout Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-start">
      <!-- Left: Large Image Gallery -->
      <div class="lg:col-span-7 space-y-4">
        <!-- Main Large Image -->
        <div class="relative aspect-3/4 overflow-hidden bg-white shadow-sm border border-[#D8CCBC]/30">
          <img
            :src="activeImage"
            :alt="product.name"
            class="w-full h-full object-cover object-top transition-transform duration-500"
          />

          <!-- Badges -->
          <div class="absolute top-4 left-4 flex flex-col gap-1.5 z-10">
            <span
              v-if="product.is_on_sale"
              class="px-2.5 py-1 bg-[#111111] text-white text-[10px] uppercase tracking-widest font-semibold"
            >
              অফার
            </span>
            <span
              v-else-if="product.is_new"
              class="px-2.5 py-1 bg-white text-[#111111] text-[10px] uppercase tracking-widest font-semibold shadow-xs"
            >
              নতুন আগমন
            </span>
          </div>
        </div>

        <!-- Gallery Thumbnails -->
        <div v-if="galleryImages.length > 1" class="grid grid-cols-4 sm:grid-cols-5 gap-3">
          <button
            v-for="(img, idx) in galleryImages"
            :key="idx"
            type="button"
            @click="activeImage = img"
            :class="[
              'relative aspect-3/4 overflow-hidden border transition-all duration-200',
              activeImage === img ? 'border-[#111111] ring-1 ring-[#111111]' : 'border-[#D8CCBC]/40 opacity-70 hover:opacity-100'
            ]"
          >
            <img :src="img" :alt="product.name" class="w-full h-full object-cover" />
          </button>
        </div>
      </div>

      <!-- Right: Product Info & Purchase Form -->
      <div class="lg:col-span-5 space-y-6 lg:sticky lg:top-28">
        <div>
          <span v-if="product.brand" class="text-xs uppercase tracking-[0.25em] text-[#A89B8A] font-medium block mb-1">
            {{ product.brand.name }}
          </span>
          <h1 class="font-serif text-3xl sm:text-4xl text-[#111111] leading-tight">
            {{ product.name }}
          </h1>
          <p class="text-xs text-[#A89B8A] mt-1">কোড: {{ activeVariant?.sku || product.sku }}</p>
        </div>

        <!-- Price -->
        <div class="pt-2 border-t border-[#D8CCBC]/40">
          <PriceDisplay
            :price="activeVariant?.price || product.price"
            :sale-price="product.sale_price"
            :compare-at-price="product.compare_at_price"
            size="xl"
          />
        </div>

        <!-- Short Description -->
        <p v-if="product.short_description" class="text-xs sm:text-sm text-[#111111]/80 font-light leading-relaxed">
          {{ product.short_description }}
        </p>

        <!-- Color Selector -->
        <div v-if="availableColors.length" class="space-y-2 pt-2">
          <div class="flex justify-between text-xs">
            <span class="uppercase tracking-wider font-semibold text-[#111111]">
              রঙ: <strong class="font-normal text-[#A89B8A]">{{ selectedColor }}</strong>
            </span>
          </div>
          <div class="flex items-center gap-2">
            <button
              v-for="c in availableColors"
              :key="c"
              type="button"
              @click="selectedColor = c; onVariantAttributeChange()"
              :class="[
                'px-4 py-2 text-xs border uppercase tracking-wider font-medium transition-all',
                selectedColor === c
                  ? 'bg-[#111111] text-white border-[#111111]'
                  : 'bg-white text-[#111111] border-[#D8CCBC] hover:border-[#111111]'
              ]"
            >
              {{ c }}
            </button>
          </div>
        </div>

        <!-- Size Selector -->
        <div v-if="availableSizes.length" class="space-y-2 pt-2">
          <div class="flex justify-between items-center text-xs">
            <span class="uppercase tracking-wider font-semibold text-[#111111]">
              সাইজ: <strong class="font-normal text-[#A89B8A]">{{ selectedSize }}</strong>
            </span>
            <button
              type="button"
              @click="sizeGuideOpen = true"
              class="text-[#A89B8A] hover:text-[#111111] underline tracking-wider uppercase text-[11px]"
            >
              সাইজ চার্ট
            </button>
          </div>
          <div class="flex flex-wrap gap-2">
            <button
              v-for="s in availableSizes"
              :key="s"
              type="button"
              @click="selectedSize = s; onVariantAttributeChange()"
              :class="[
                'min-w-12 h-11 px-3 border text-xs uppercase tracking-wider font-medium flex items-center justify-center transition-all',
                selectedSize === s
                  ? 'bg-[#111111] text-white border-[#111111]'
                  : 'bg-white text-[#111111] border-[#D8CCBC] hover:border-[#111111]'
              ]"
            >
              {{ s }}
            </button>
          </div>
        </div>

        <!-- Stock Status Indicator -->
        <div class="text-xs flex items-center gap-2 pt-1">
          <span
            :class="[
              'w-2 h-2 rounded-full',
              inStock ? 'bg-emerald-600' : 'bg-rose-500'
            ]"
          />
          <span v-if="inStock" class="text-emerald-800 font-medium">স্টকে আছে • ডেলিভারির জন্য প্রস্তুত</span>
          <span v-else class="text-rose-600 font-medium">এই ভ্যারিয়েন্টের স্টক শেষ</span>
        </div>

        <!-- Quantity & Add to Cart -->
        <div class="space-y-3 pt-4 border-t border-[#D8CCBC]/40">
          <div class="flex gap-4">
            <!-- Quantity counter -->
            <div class="inline-flex items-center border border-[#D8CCBC] bg-white text-xs shrink-0">
              <button
                type="button"
                @click="quantity = Math.max(1, quantity - 1)"
                class="px-3.5 py-3 hover:bg-[#F7F5F0] text-[#111111]"
              >
                -
              </button>
              <span class="px-4 py-3 text-center font-medium min-w-8">{{ quantity }}</span>
              <button
                type="button"
                @click="quantity++"
                class="px-3.5 py-3 hover:bg-[#F7F5F0] text-[#111111]"
              >
                +
              </button>
            </div>

            <!-- Add to Cart -->
            <button
              type="button"
              :disabled="!inStock || adding"
              @click="handleAddToCart"
              class="flex-1 py-3.5 bg-[#111111] hover:bg-black text-white text-xs uppercase tracking-[0.2em] font-semibold transition-colors disabled:opacity-40 disabled:cursor-not-allowed shadow-md"
            >
              {{ adding ? 'যোগ হচ্ছে...' : (inStock ? 'ব্যাগে যোগ করুন' : 'স্টক শেষ') }}
            </button>

            <!-- Wishlist Button -->
            <button
              type="button"
              @click="wishlistStore.toggleWishlist(product)"
              :class="[
                'p-3.5 border transition-colors shrink-0',
                wishlistStore.isInWishlist(product.id)
                  ? 'bg-[#111111] text-white border-[#111111]'
                  : 'bg-white border-[#D8CCBC] text-[#111111] hover:border-[#111111]'
              ]"
              :title="wishlistStore.isInWishlist(product.id) ? 'পছন্দের তালিকায় যুক্ত' : 'পছন্দের তালিকায় রাখুন'"
            >
              <svg
                class="w-5 h-5"
                :fill="wishlistStore.isInWishlist(product.id) ? 'currentColor' : 'none'"
                stroke="currentColor" viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
              </svg>
            </button>
          </div>

          <!-- Buy Now Direct Checkout -->
          <button
            type="button"
            :disabled="!inStock"
            @click="handleBuyNow"
            class="w-full py-3.5 border border-[#111111] hover:bg-[#111111] hover:text-white text-[#111111] text-xs uppercase tracking-[0.2em] font-semibold transition-colors disabled:opacity-40"
          >
            সরাসরি অর্ডার করুন (বাই নাও)
          </button>
        </div>

        <!-- Accordion: Fabric, Care, Shipping -->
        <div class="pt-6 border-t border-[#D8CCBC]/40 divide-y divide-[#D8CCBC]/30 text-xs">
          <!-- Description Accordion -->
          <div class="py-3">
            <button
              type="button"
              @click="toggleAccordion('desc')"
              class="w-full flex justify-between items-center uppercase tracking-wider font-semibold text-[#111111]"
            >
              <span>পোশাকের বিবরণ ও বিস্তারিত</span>
              <span>{{ activeAccordion === 'desc' ? '−' : '+' }}</span>
            </button>
            <div v-if="activeAccordion === 'desc'" class="pt-3 text-[#111111]/80 font-light leading-relaxed">
              {{ product.description }}
            </div>
          </div>

          <!-- Fabric & Care -->
          <div class="py-3">
            <button
              type="button"
              @click="toggleAccordion('fabric')"
              class="w-full flex justify-between items-center uppercase tracking-wider font-semibold text-[#111111]"
            >
              <span>ফেব্রিক ও যত্ন নেওয়ার নির্দেশিকা</span>
              <span>{{ activeAccordion === 'fabric' ? '−' : '+' }}</span>
            </button>
            <div v-if="activeAccordion === 'fabric'" class="pt-3 text-[#111111]/80 font-light leading-relaxed">
              {{ product.fabric_care || '১০০% প্রিমিয়াম সুতি ও আরামদায়ক ফেব্রিক। স্বাভাবিক পানিতে ধুয়ে ছায়ায় শুকান অথবা ড্রাই ক্লিন করুন।' }}
            </div>
          </div>

          <!-- Shipping & Returns -->
          <div class="py-3">
            <button
              type="button"
              @click="toggleAccordion('shipping')"
              class="w-full flex justify-between items-center uppercase tracking-wider font-semibold text-[#111111]"
            >
              <span>ডেলিভারি ও এক্সচেঞ্জ পলিসি</span>
              <span>{{ activeAccordion === 'shipping' ? '−' : '+' }}</span>
            </button>
            <div v-if="activeAccordion === 'shipping'" class="pt-3 text-[#111111]/80 font-light leading-relaxed">
              {{ product.shipping_info || 'সারাদেশে দ্রুত ক্যাশ অন ডেলিভারি সুবিধা। কোনো সাইজ সমস্যা হলে ৭ দিনের মধ্যে সহজে এক্সচেঞ্জ করতে পারবেন।' }}
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Customer Reviews Section -->
    <section class="mt-24 pt-16 border-t border-[#D8CCBC]/40">
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-8 gap-4">
        <div>
          <span class="text-xs uppercase tracking-[0.25em] text-[#A89B8A] font-medium block">গ্রাহকদের মতামত</span>
          <h3 class="font-serif text-2xl sm:text-3xl text-[#111111]">
            রিভিউ ({{ product.reviews ? product.reviews.length : 0 }}টি)
          </h3>
        </div>
        <button
          type="button"
          @click="reviewModalOpen = true"
          class="px-6 py-2.5 bg-[#111111] text-white text-xs uppercase tracking-widest font-semibold hover:bg-black transition-colors"
        >
          রিভিউ দিন
        </button>
      </div>

      <!-- Reviews List -->
      <div v-if="product.reviews && product.reviews.length" class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div
          v-for="rev in product.reviews"
          :key="rev.id"
          class="bg-white p-6 border border-[#D8CCBC]/40 shadow-2xs space-y-2"
        >
          <div class="flex justify-between items-center text-xs">
            <span class="font-semibold text-[#111111]">{{ rev.author_name }}</span>
            <div class="text-[#C5A880]">
              <span v-for="s in rev.rating" :key="s">★</span>
            </div>
          </div>
          <h5 v-if="rev.title" class="font-serif text-sm font-medium text-[#111111]">{{ rev.title }}</h5>
          <p class="text-xs text-[#111111]/80 font-light leading-relaxed">"{{ rev.review }}"</p>
        </div>
      </div>
      <p v-else class="text-xs text-[#A89B8A]">এই পোশাকে এখনো কোনো রিভিউ নেই। প্রথম রিভিউ দিয়ে আপনার অভিজ্ঞতা জানান!</p>
    </section>

    <!-- Related Products -->
    <section v-if="relatedProducts.length" class="mt-24 pt-16 border-t border-[#D8CCBC]/40">
      <div class="text-center max-w-xl mx-auto mb-12 space-y-2">
        <span class="text-xs uppercase tracking-[0.25em] text-[#A89B8A] font-medium block">সম্পর্কিত পোশাকসমূহ</span>
        <h3 class="font-serif text-2xl sm:text-3xl text-[#111111]">আপনার পছন্দের আরও পোশাক</h3>
      </div>
      <ProductGrid :products="relatedProducts" />
    </section>

    <!-- Size Guide Modal -->
    <BaseModal
      :is-open="sizeGuideOpen"
      title="সাইজ চার্ট ও নির্দেশিকা"
      max-width="md"
      @close="sizeGuideOpen = false"
    >
      <div class="space-y-4 text-xs text-[#111111]">
        <p class="font-light">আমাদের প্রতিটি পোশাক স্ট্যান্ডার্ড মাপ অনুযায়ী নিখুঁতভাবে তৈরি। আরও তথ্যের জন্য গ্রাহক সহায়তায় যোগাযোগ করতে পারেন।</p>
        <table class="w-full text-left border-collapse border border-[#D8CCBC]">
          <thead>
            <tr class="bg-[#F7F5F0]">
              <th class="p-2 border border-[#D8CCBC]">সাইজ</th>
              <th class="p-2 border border-[#D8CCBC]">বুক (ইঞ্চি)</th>
              <th class="p-2 border border-[#D8CCBC]">কোমর (ইঞ্চি)</th>
              <th class="p-2 border border-[#D8CCBC]">ইউরোপিয়ান</th>
            </tr>
          </thead>
          <tbody>
            <tr><td class="p-2 border border-[#D8CCBC]">S</td><td class="p-2 border border-[#D8CCBC]">৩৬–৩৮</td><td class="p-2 border border-[#D8CCBC]">৩০–৩২</td><td class="p-2 border border-[#D8CCBC]">৪৬–৪৮</td></tr>
            <tr><td class="p-2 border border-[#D8CCBC]">M</td><td class="p-2 border border-[#D8CCBC]">৩৯–৪১</td><td class="p-2 border border-[#D8CCBC]">৩৩–৩৫</td><td class="p-2 border border-[#D8CCBC]">৫০–৫২</td></tr>
            <tr><td class="p-2 border border-[#D8CCBC]">L</td><td class="p-2 border border-[#D8CCBC]">৪২–৪৪</td><td class="p-2 border border-[#D8CCBC]">৩৬–৩৮</td><td class="p-2 border border-[#D8CCBC]">৫৪–৫৬</td></tr>
            <tr><td class="p-2 border border-[#D8CCBC]">XL</td><td class="p-2 border border-[#D8CCBC]">৪৫–৪৭</td><td class="p-2 border border-[#D8CCBC]">৩৯–৪১</td><td class="p-2 border border-[#D8CCBC]">৫৮–৬০</td></tr>
          </tbody>
        </table>
      </div>
    </BaseModal>

    <!-- Review Submission Modal -->
    <BaseModal
      :is-open="reviewModalOpen"
      title="রিভিউ প্রদান করুন"
      max-width="md"
      @close="reviewModalOpen = false"
    >
      <form @submit.prevent="submitReview" class="space-y-4 text-xs">
        <div>
          <label class="block font-semibold uppercase tracking-wider mb-1">আপনার নাম</label>
          <input type="text" v-model="newReview.author_name" required class="w-full px-3 py-2 border border-[#D8CCBC]" />
        </div>
        <div>
          <label class="block font-semibold uppercase tracking-wider mb-1">রেটিং</label>
          <select v-model="newReview.rating" class="w-full px-3 py-2 border border-[#D8CCBC]">
            <option :value="5">★★★★★ (৫/৫ অসাধারণ)</option>
            <option :value="4">★★★★☆ (৪/৫ খুব ভালো)</option>
            <option :value="3">★★★☆☆ (৩/৫ সন্তোষজনক)</option>
          </select>
        </div>
        <div>
          <label class="block font-semibold uppercase tracking-wider mb-1">রিভিউ শিরোনাম</label>
          <input type="text" v-model="newReview.title" placeholder="চমৎকার ফেব্রিক ও ফিটিং" class="w-full px-3 py-2 border border-[#D8CCBC]" />
        </div>
        <div>
          <label class="block font-semibold uppercase tracking-wider mb-1">আপনার বিস্তারিত অভিজ্ঞতা</label>
          <textarea rows="4" v-model="newReview.review" required class="w-full px-3 py-2 border border-[#D8CCBC]"></textarea>
        </div>
        <button
          type="submit"
          class="w-full py-3 bg-[#111111] text-white uppercase tracking-widest font-semibold hover:bg-black transition-colors"
        >
          রিভিউ সাবমিট করুন
        </button>
      </form>
    </BaseModal>

    <!-- Mobile Sticky Add to Cart Bar -->
    <div class="lg:hidden fixed bottom-0 inset-x-0 bg-white/95 backdrop-blur-md border-t border-[#D8CCBC]/40 p-4 z-40 flex items-center justify-between gap-4 shadow-xl">
      <div>
        <p class="font-serif text-sm text-[#111111] line-clamp-1">{{ product.name }}</p>
        <span class="text-xs font-semibold text-[#111111]">৳ {{ product.sale_price || product.price }}</span>
      </div>
      <button
        type="button"
        :disabled="!inStock"
        @click="handleAddToCart"
        class="px-6 py-3 bg-[#111111] text-white text-xs uppercase tracking-widest font-semibold hover:bg-black transition-colors shrink-0"
      >
        {{ inStock ? 'ব্যাগে যোগ করুন' : 'স্টক শেষ' }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useCartStore } from '../../stores/cart';
import { useWishlistStore } from '../../stores/wishlist';
import api from '../../api';
import PriceDisplay from '../../components/common/PriceDisplay.vue';
import ProductGrid from '../../components/public/ProductGrid.vue';
import BaseModal from '../../components/common/BaseModal.vue';

const route = useRoute();
const router = useRouter();
const cartStore = useCartStore();
const wishlistStore = useWishlistStore();

const product = ref(null);
const relatedProducts = ref([]);
const activeImage = ref('');
const quantity = ref(1);
const selectedColor = ref('');
const selectedSize = ref('');
const activeAccordion = ref('desc');
const adding = ref(false);
const sizeGuideOpen = ref(false);
const reviewModalOpen = ref(false);

const newReview = ref({
  author_name: '',
  rating: 5,
  title: '',
  review: '',
});

const galleryImages = computed(() => {
  const images = [];
  if (product.value?.featured_image) images.push(product.value.featured_image);
  if (product.value?.gallery && Array.isArray(product.value.gallery)) {
    product.value.gallery.forEach(img => {
      if (!images.includes(img)) images.push(img);
    });
  }
  return images;
});

const availableColors = computed(() => {
  if (product.value?.attributes?.colors?.length) {
    return product.value.attributes.colors;
  }
  if (product.value?.variants) {
    return [...new Set(product.value.variants.map(v => v.color).filter(Boolean))];
  }
  return [];
});

const availableSizes = computed(() => {
  if (product.value?.attributes?.sizes?.length) {
    return product.value.attributes.sizes;
  }
  if (product.value?.variants) {
    return [...new Set(product.value.variants.map(v => v.size).filter(Boolean))];
  }
  return [];
});

const activeVariant = computed(() => {
  if (!product.value?.variants?.length) return null;
  return product.value.variants.find(v => {
    const matchColor = !selectedColor.value || v.color === selectedColor.value;
    const matchSize = !selectedSize.value || v.size === selectedSize.value;
    return matchColor && matchSize;
  }) || product.value.variants[0];
});

const inStock = computed(() => {
  if (activeVariant.value) {
    return activeVariant.value.stock > 0;
  }
  return (product.value?.stock || 0) > 0;
});

const toggleAccordion = (name) => {
  activeAccordion.value = activeAccordion.value === name ? null : name;
};

const onVariantAttributeChange = () => {
  if (activeVariant.value?.image) {
    activeImage.value = activeVariant.value.image;
  }
};

const handleAddToCart = async () => {
  if (!inStock.value) return;
  adding.value = true;
  try {
    await cartStore.addToCart(
      product.value.id,
      activeVariant.value?.id || null,
      quantity.value,
      selectedColor.value || null,
      selectedSize.value || null
    );
  } catch (err) {
    alert(err.response?.data?.message || 'ব্যাগে যোগ করা যায়নি');
  } finally {
    adding.value = false;
  }
};

const handleBuyNow = async () => {
  await handleAddToCart();
  cartStore.closeDrawer();
  router.push('/checkout');
};

const submitReview = async () => {
  try {
    const res = await api.post(`/public/products/${product.value.id}/reviews`, newReview.value);
    product.value.reviews.unshift(res.data.data);
    reviewModalOpen.value = false;
    newReview.value = { author_name: '', rating: 5, title: '', review: '' };
    alert('আপনার মূল্যবান মতামতের জন্য ধন্যবাদ!');
  } catch (err) {
    alert(err.response?.data?.message || 'রিভিউ সাবমিট করতে সমস্যা হয়েছে');
  }
};

const loadProduct = async () => {
  try {
    const res = await api.get(`/public/products/${route.params.slug}`);
    product.value = res.data.data.product;
    relatedProducts.value = res.data.data.related_products || [];
    activeImage.value = product.value.featured_image;

    // Set default selections
    if (availableColors.value.length) selectedColor.value = availableColors.value[0];
    if (availableSizes.value.length) selectedSize.value = availableSizes.value[0];
  } catch (err) {
    console.error('Failed to load product', err);
  }
};

watch(() => route.params.slug, () => {
  loadProduct();
});

onMounted(() => {
  loadProduct();
});
</script>
