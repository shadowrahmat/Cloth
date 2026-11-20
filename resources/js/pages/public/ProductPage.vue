<template>
  <div class="pp">
    <!-- ------------------------------------------------------------ loading -->
    <div v-if="loading" class="pp-wrap" aria-busy="true" aria-label="পণ্য লোড হচ্ছে">
      <div class="skeleton pp-sk-crumb"></div>
      <div class="pp-hero">
        <div class="skeleton pp-sk-image"></div>
        <div class="pp-card pp-sk-card">
          <div class="skeleton pp-sk-line" style="width: 30%"></div>
          <div class="skeleton pp-sk-title"></div>
          <div class="skeleton pp-sk-line" style="width: 40%"></div>
          <div class="skeleton pp-sk-block"></div>
        </div>
      </div>
    </div>

    <!-- ----------------------------------------------------------- not found -->
    <div v-else-if="!product" class="pp-wrap pp-missing">
      <h1 class="pp-missing-title">পণ্যটি পাওয়া যায়নি</h1>
      <p>লিংকটি পুরোনো হতে পারে, অথবা পণ্যটি সরিয়ে নেওয়া হয়েছে।</p>
      <router-link to="/collections" class="pp-btn-primary pp-missing-btn">শপে যান</router-link>
    </div>

    <!-- ------------------------------------------------------------- product -->
    <div v-else class="pp-wrap">
      <nav class="pp-crumb" aria-label="ব্রেডক্রাম্ব">
        <router-link to="/">হোম</router-link>
        <ChevronRight :size="14" :stroke-width="1.75" aria-hidden="true" />
        <template v-if="product.category">
          <router-link :to="`/category/${product.category.slug}`">{{ product.category.name }}</router-link>
          <ChevronRight :size="14" :stroke-width="1.75" aria-hidden="true" />
        </template>
        <span aria-current="page" class="pp-crumb-current">{{ product.name }}</span>
      </nav>

      <div class="pp-hero">
        <!-- ================================================== gallery -->
        <ProductGallery
          :class="['pp-gallery', { 'pp-gallery--bento': galleryImages.length >= 3 }]"
          :images="galleryImages"
          :name="product.name"
          :sale-label="onSale ? `${bnNumber(savePercent)}% ছাড়` : ''"
        />

        <!-- ================================================== buy card -->
        <div class="pp-card pp-details">
          <div class="pp-topline">
            <router-link v-if="product.category" :to="`/category/${product.category.slug}`" class="pp-chip">{{ product.category.name }}</router-link>
            <button v-if="ratingCount" type="button" class="pp-rating" @click="openReviews">
              <Star :size="15" :stroke-width="0" fill="currentColor" aria-hidden="true" />
              <strong>{{ bnNumber(ratingAverage) }}</strong>
              <span>· {{ bnNumber(ratingCount) }}টি রিভিউ</span>
            </button>
            <div class="pp-icons">
              <button
                type="button"
                class="pp-icon-btn"
                :class="{ 'is-on': isWished }"
                :aria-pressed="isWished ? 'true' : 'false'"
                :aria-label="isWished ? 'পছন্দের তালিকা থেকে সরান' : 'পছন্দের তালিকায় রাখুন'"
                @click="wishlist.toggleWishlist(product)"
              >
                <Heart :size="18" :stroke-width="1.75" :fill="isWished ? 'currentColor' : 'none'" />
              </button>
              <button type="button" class="pp-icon-btn" aria-label="শেয়ার করুন" @click="share">
                <Share2 :size="18" :stroke-width="1.75" />
              </button>
            </div>
          </div>

          <div class="pp-head">
            <h1 class="pp-title">{{ product.name }}</h1>
            <div class="pp-price-row">
              <span class="pp-price">{{ taka(unitPrice) }}</span>
              <template v-if="onSale">
                <s class="pp-compare"><span class="sr-only">আগের দাম </span>{{ taka(comparePrice) }}</s>
                <span class="pp-save">{{ taka(comparePrice - unitPrice) }} সাশ্রয়</span>
              </template>
            </div>
            <p :class="['pp-stock', stockState.tone]">
              <span class="pp-stock-dot" aria-hidden="true"></span>{{ stockState.text }}
            </p>
          </div>

          <p v-if="shortDescription" class="pp-short">{{ shortDescription }}</p>

          <!-- Colour -->
          <div v-if="showColor" class="pp-option">
            <p class="pp-option-label" :id="`pp-color-label-${product.id}`">
              <span>রঙ</span>
              <span class="pp-option-value">{{ selectedColor }}</span>
            </p>
            <div class="pp-swatches" role="group" :aria-labelledby="`pp-color-label-${product.id}`">
              <button
                v-for="c in colorOptions"
                :key="c.name"
                type="button"
                :class="['pp-swatch', { 'is-selected': c.name === selectedColor }]"
                :aria-label="c.name"
                :aria-pressed="c.name === selectedColor ? 'true' : 'false'"
                :title="c.name"
                @click="chooseColor(c.name)"
              >
                <span :class="['pp-swatch-dot', { 'is-light': c.light }]" :style="{ background: c.css }"></span>
              </button>
            </div>
          </div>

          <!-- Size -->
          <div v-if="showSize" ref="sizeBlock" class="pp-option">
            <div class="pp-option-row">
              <p class="pp-option-label" :id="`pp-size-label-${product.id}`">
                <span>সাইজ</span>
                <span class="pp-option-value">{{ selectedSize || 'সাইজ বেছে নিন' }}</span>
              </p>
              <button v-if="sizeChart" type="button" class="pp-link" @click="sizeGuideOpen = true">
                <Ruler :size="15" :stroke-width="1.75" aria-hidden="true" />সাইজ চার্ট
              </button>
            </div>
            <div class="pp-sizes" role="group" :aria-labelledby="`pp-size-label-${product.id}`" :aria-describedby="sizeError ? 'pp-size-error' : undefined">
              <button
                v-for="s in sizeOptions"
                :key="s"
                type="button"
                :class="['pp-size', { 'is-selected': s === selectedSize, 'is-soldout': sizeSoldOut(s) }]"
                :disabled="sizeSoldOut(s)"
                :aria-pressed="s === selectedSize ? 'true' : 'false'"
                :aria-label="sizeSoldOut(s) ? `${s} — স্টক শেষ` : s"
                @click="chooseSize(s)"
              >
                {{ s }}
              </button>
            </div>
            <p v-if="sizeError" id="pp-size-error" class="pp-size-error">আগে একটি সাইজ বেছে নিন।</p>
          </div>

          <!-- Quantity + add to cart -->
          <div class="pp-buy-block">
            <div ref="actionRow" class="pp-actions">
              <div class="pp-stepper" role="group" aria-label="পরিমাণ">
                <button type="button" class="pp-step" :disabled="quantity <= 1" aria-label="পরিমাণ কমান" @click="setQuantity(quantity - 1)">
                  <Minus :size="16" :stroke-width="2" />
                </button>
                <span class="pp-qty" aria-live="polite" aria-atomic="true"><span class="sr-only">পরিমাণ </span>{{ bnNumber(quantity) }}</span>
                <button type="button" class="pp-step" :disabled="quantity >= maxQuantity" aria-label="পরিমাণ বাড়ান" @click="setQuantity(quantity + 1)">
                  <Plus :size="16" :stroke-width="2" />
                </button>
              </div>

              <button
                type="button"
                class="pp-btn-primary pp-add"
                :disabled="!canBuy || adding"
                :aria-busy="adding ? 'true' : 'false'"
                @click="addToCart"
              >
                <Check v-if="added" :size="18" :stroke-width="2" aria-hidden="true" />
                <ShoppingBag v-else :size="18" :stroke-width="1.75" aria-hidden="true" />
                <span>{{ addLabel }}</span>
              </button>
            </div>

            <button type="button" class="pp-btn-secondary pp-buy" :disabled="!canBuy || adding" @click="buyNow">
              <Zap :size="17" :stroke-width="1.75" aria-hidden="true" />এখনই কিনুন
            </button>

            <p v-if="addError" class="pp-add-error" role="alert">{{ addError }}</p>

            <div v-if="added" class="pp-confirm">
              <p class="pp-confirm-text">
                <span class="pp-confirm-icon"><Check :size="14" :stroke-width="2.5" aria-hidden="true" /></span>
                <span>{{ added }}</span>
              </p>
              <button type="button" class="pp-confirm-link" @click="cart.openDrawer()">ব্যাগ দেখুন <ArrowRight :size="15" :stroke-width="2" aria-hidden="true" /></button>
            </div>
          </div>

          <!-- Delivery & trust tiles -->
          <ul class="pp-perks">
            <li v-for="m in areas" :key="m.code">
              <span class="pp-perk-icon"><Truck :size="18" :stroke-width="1.75" aria-hidden="true" /></span>
              <span class="pp-perk-text"><strong>{{ m.name }}</strong><span>{{ taka(m.rate) }} · {{ m.estimated_delivery }}</span></span>
            </li>
            <li>
              <span class="pp-perk-icon"><Banknote :size="18" :stroke-width="1.75" aria-hidden="true" /></span>
              <span class="pp-perk-text"><strong>ক্যাশ অন ডেলিভারি</strong><span>পণ্য হাতে পেয়ে টাকা দিন</span></span>
            </li>
            <li>
              <span class="pp-perk-icon"><RefreshCw :size="18" :stroke-width="1.75" aria-hidden="true" /></span>
              <span class="pp-perk-text"><strong>৭ দিনে এক্সচেঞ্জ</strong><span>সাইজ না মিললে সহজে বদলান</span></span>
            </li>
          </ul>
        </div>
      </div>

      <!-- ========================================== details tabs -->
      <section ref="tabsSection" class="pp-card pp-tabs-card" aria-label="পণ্যের বিস্তারিত">
        <div class="pp-tabs" role="tablist" aria-label="পণ্যের বিস্তারিত">
          <button
            v-for="(tab, i) in tabs"
            :key="tab.key"
            :ref="(el) => (tabEls[i] = el)"
            type="button"
            role="tab"
            :id="`pp-tab-${tab.key}`"
            :aria-selected="activeTab === tab.key ? 'true' : 'false'"
            :aria-controls="`pp-panel-${tab.key}`"
            :tabindex="activeTab === tab.key ? 0 : -1"
            :class="['pp-tab', { 'is-active': activeTab === tab.key }]"
            @click="activeTab = tab.key"
            @keydown="onTabKey($event, i)"
          >
            {{ tab.title }}
          </button>
        </div>

        <div
          v-for="tab in tabs"
          v-show="activeTab === tab.key"
          :key="'p' + tab.key"
          :id="`pp-panel-${tab.key}`"
          role="tabpanel"
          :aria-labelledby="`pp-tab-${tab.key}`"
          tabindex="0"
          class="pp-panel"
        >
          <!-- Description -->
          <div v-if="tab.key === 'desc'" class="pp-panel-grid">
            <p class="pp-pre pp-panel-lead">{{ product.description || shortDescription || 'বিস্তারিত বিবরণ শিগগিরই যোগ করা হবে।' }}</p>
            <dl class="pp-specs">
              <div v-if="product.category"><dt>ক্যাটাগরি</dt><dd>{{ product.category.name }}</dd></div>
              <div v-if="product.brand"><dt>ব্র্যান্ড</dt><dd>{{ product.brand.name }}</dd></div>
              <div v-if="sizeOptions.length"><dt>সাইজ</dt><dd>{{ sizeOptions.join(', ') }}</dd></div>
              <div v-if="colorNames.length"><dt>রঙ</dt><dd>{{ colorNames.join(', ') }}</dd></div>
              <div><dt>কোড</dt><dd>{{ selectedVariant?.sku || product.sku }}</dd></div>
            </dl>
          </div>

          <!-- Size & fit -->
          <div v-else-if="tab.key === 'fit'" class="pp-panel-grid">
            <div class="pp-panel-stack">
              <p>আমাদের প্রতিটি পোশাক স্ট্যান্ডার্ড মাপ অনুযায়ী তৈরি। অর্ডারের আগে সাইজ চার্টের সাথে আপনার মাপ মিলিয়ে নিন।</p>
              <p v-if="product.fabric_care" class="pp-pre"><strong>ফেব্রিক ও যত্ন:</strong> {{ product.fabric_care }}</p>
            </div>
            <button v-if="sizeChart" type="button" class="pp-panel-cta" @click="sizeGuideOpen = true">
              <Ruler :size="20" :stroke-width="1.75" aria-hidden="true" />
              <span><strong>সাইজ চার্ট দেখুন</strong><span>বুক ও কোমরের মাপ ইঞ্চিতে</span></span>
              <ArrowRight :size="18" :stroke-width="1.75" aria-hidden="true" />
            </button>
          </div>

          <!-- Delivery & exchange -->
          <div v-else-if="tab.key === 'delivery'" class="pp-panel-grid">
            <div class="pp-panel-stack">
              <p v-if="product.shipping_info" class="pp-pre">{{ product.shipping_info }}</p>
              <p v-else>
                <template v-for="(m, i) in areas" :key="m.code">{{ m.name }} {{ taka(m.rate) }} ({{ m.estimated_delivery }}){{ i < areas.length - 1 ? ', ' : '। ' }}</template>
                সারাদেশে ক্যাশ অন ডেলিভারি সুবিধা আছে।
              </p>
              <p class="pp-pre">{{ product.returns_info || 'কোনো সাইজ সমস্যা হলে পণ্য হাতে পাওয়ার ৭ দিনের মধ্যে সহজে এক্সচেঞ্জ করতে পারবেন।' }}</p>
            </div>
            <router-link to="/track-order" class="pp-panel-cta">
              <Truck :size="20" :stroke-width="1.75" aria-hidden="true" />
              <span><strong>অর্ডার ট্র্যাক করুন</strong><span>আপনার পার্সেল কোথায় আছে দেখুন</span></span>
              <ArrowRight :size="18" :stroke-width="1.75" aria-hidden="true" />
            </router-link>
          </div>

          <!-- Reviews -->
          <div v-else-if="tab.key === 'reviews'" class="pp-reviews-wrap">
            <div class="pp-review-summary">
              <p class="pp-review-avg">{{ ratingCount ? bnNumber(ratingAverage) : '—' }}</p>
              <p class="pp-stars-row" :aria-label="`${bnNumber(ratingAverage)}/৫ রেটিং`">
                <Star v-for="n in 5" :key="n" :size="16" :stroke-width="0" fill="currentColor" :class="{ 'is-dim': n > Math.round(ratingAverage) || !ratingCount }" />
              </p>
              <p class="pp-review-count">{{ ratingCount ? `${bnNumber(ratingCount)}টি রিভিউ` : 'এখনো কোনো রিভিউ নেই' }}</p>
              <button type="button" class="pp-btn-secondary pp-review-btn" @click="reviewModalOpen = true">রিভিউ দিন</button>
            </div>
            <ul v-if="reviews.length" class="pp-reviews">
              <li v-for="rev in reviews" :key="rev.id">
                <div class="pp-review-head">
                  <span class="pp-avatar" aria-hidden="true">{{ (rev.author_name || '?').trim().charAt(0).toUpperCase() }}</span>
                  <strong>{{ rev.author_name }}</strong>
                  <span class="pp-stars-row pp-stars-sm" :aria-label="`${bnNumber(rev.rating)}/৫ রেটিং`">
                    <Star v-for="n in 5" :key="n" :size="13" :stroke-width="0" fill="currentColor" :class="{ 'is-dim': n > rev.rating }" />
                  </span>
                </div>
                <p v-if="rev.title" class="pp-review-title">{{ rev.title }}</p>
                <p>{{ rev.review }}</p>
              </li>
            </ul>
            <p v-else class="pp-review-empty">এই পোশাকে এখনো কোনো রিভিউ নেই। প্রথম রিভিউ দিয়ে আপনার অভিজ্ঞতা জানান!</p>
          </div>
        </div>
      </section>

      <!-- ========================================== you may also like -->
      <section v-if="related.length" class="pp-related" aria-labelledby="pp-related-title">
        <div class="pp-related-head">
          <h2 id="pp-related-title" class="pp-related-title">আপনার পছন্দ হতে পারে</h2>
          <router-link :to="product.category ? `/category/${product.category.slug}` : '/collections'" class="pp-related-all">
            সব দেখুন <ArrowRight :size="16" :stroke-width="1.75" aria-hidden="true" />
          </router-link>
        </div>
        <div class="pp-related-grid">
          <ProductCard v-for="p in related" :key="p.id" :product="p" />
        </div>
      </section>

      <!-- Space so the sticky bar never covers the end of the page -->
      <div class="pp-bar-spacer" aria-hidden="true"></div>
    </div>

    <!-- ===================== sticky add-to-cart bar (phones & tablets) -->
    <div v-if="product" :class="['pp-bar', { 'is-visible': barVisible }]" :aria-hidden="barVisible ? undefined : 'true'" :inert="barVisible ? undefined : ''">
      <img v-if="galleryImages[0]" :src="galleryImages[0].src" alt="" class="pp-bar-thumb" />
      <div class="pp-bar-info">
        <span class="pp-bar-price">{{ taka(unitPrice * quantity) }}</span>
        <span class="pp-bar-sub">{{ barSubline }}</span>
      </div>
      <button type="button" class="pp-btn-primary pp-bar-btn" :disabled="!canBuy || adding" @click="addFromBar">
        <Check v-if="added" :size="18" :stroke-width="2" aria-hidden="true" />
        <ShoppingBag v-else :size="18" :stroke-width="1.75" aria-hidden="true" />
        <span>{{ added ? 'যোগ হয়েছে' : (canBuy ? 'ব্যাগে যোগ করুন' : 'স্টক শেষ') }}</span>
      </button>
    </div>

    <!-- Screen-reader announcements (added to bag, errors, link copied) -->
    <p class="sr-only" role="status" aria-live="polite">{{ announcement }}</p>
    <Transition name="pp-toast">
      <p v-if="toast" class="pp-toast" aria-hidden="true">{{ toast }}</p>
    </Transition>

    <!-- Size chart (Admin → সাইট কাস্টমাইজ → সাইজ চার্ট) -->
    <BaseModal :is-open="sizeGuideOpen && !!sizeChart" :title="sizeChart?.name || 'সাইজ চার্ট'" max-width="md" @close="sizeGuideOpen = false">
      <div v-if="sizeChart" class="space-y-4 text-[13px] text-[#111111]">
        <p v-if="sizeChart.note" class="text-[#111111]/75 leading-relaxed">{{ sizeChart.note }}</p>
        <div class="overflow-x-auto rounded-xl border border-[#E5DED3]">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-[#F7F5F0]">
                <th v-for="(col, i) in sizeChart.columns" :key="i" class="px-3 py-2.5 font-semibold whitespace-nowrap">{{ col }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(row, ri) in sizeChart.rows" :key="ri" class="border-t border-[#E5DED3]">
                <td v-for="(col, i) in sizeChart.columns" :key="i" :class="['px-3 py-2.5 whitespace-nowrap tabular-nums', i === 0 ? 'font-semibold' : 'text-[#111111]/75']">{{ row[i] }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </BaseModal>

    <!-- Review form (the existing one) -->
    <BaseModal :is-open="reviewModalOpen" title="রিভিউ প্রদান করুন" max-width="md" @close="reviewModalOpen = false">
      <form @submit.prevent="submitReview" class="space-y-4 text-xs">
        <div>
          <label class="block font-semibold uppercase tracking-wider mb-1" for="pp-rev-name">আপনার নাম</label>
          <input id="pp-rev-name" type="text" v-model="newReview.author_name" required class="w-full px-3 py-2 border border-[#D8CCBC]" />
        </div>
        <div>
          <label class="block font-semibold uppercase tracking-wider mb-1" for="pp-rev-rating">রেটিং</label>
          <select id="pp-rev-rating" v-model="newReview.rating" class="w-full px-3 py-2 border border-[#D8CCBC]">
            <option :value="5">★★★★★ (৫/৫ অসাধারণ)</option>
            <option :value="4">★★★★☆ (৪/৫ খুব ভালো)</option>
            <option :value="3">★★★☆☆ (৩/৫ সন্তোষজনক)</option>
            <option :value="2">★★☆☆☆ (২/৫ ভালো না)</option>
            <option :value="1">★☆☆☆☆ (১/৫ খারাপ)</option>
          </select>
        </div>
        <div>
          <label class="block font-semibold uppercase tracking-wider mb-1" for="pp-rev-title">রিভিউ শিরোনাম</label>
          <input id="pp-rev-title" type="text" v-model="newReview.title" placeholder="চমৎকার ফেব্রিক ও ফিটিং" class="w-full px-3 py-2 border border-[#D8CCBC]" />
        </div>
        <div>
          <label class="block font-semibold uppercase tracking-wider mb-1" for="pp-rev-body">আপনার বিস্তারিত অভিজ্ঞতা</label>
          <textarea id="pp-rev-body" rows="4" v-model="newReview.review" required class="w-full px-3 py-2 border border-[#D8CCBC]"></textarea>
        </div>
        <p v-if="reviewMsg" class="text-[13px] text-rose-600" role="alert">{{ reviewMsg }}</p>
        <button type="submit" class="w-full py-3 bg-[#111111] text-white uppercase tracking-widest font-semibold hover:bg-black transition-colors">
          রিভিউ সাবমিট করুন
        </button>
      </form>
    </BaseModal>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { ShoppingBag, Heart, Minus, Plus, Check, ChevronRight, ArrowRight, Truck, Banknote, RefreshCw, Star, Share2, Ruler, Zap } from 'lucide-vue-next';
import ProductCard from '../../components/public/ProductCard.vue';
import { useSiteStore } from '../../stores/site';
import { useCartStore } from '../../stores/cart';
import { useWishlistStore } from '../../stores/wishlist';
import api from '../../api';
import BaseModal from '../../components/common/BaseModal.vue';
import ProductGallery from '../../components/public/product/ProductGallery.vue';
import { taka, bnNumber } from '../../utils/bn';
import { scrollTo as smoothScrollTo } from '../../utils/smoothScroll';

const route = useRoute();
const router = useRouter();
const cart = useCartStore();
const wishlist = useWishlistStore();
const site = useSiteStore();

/**
 * Size chart for this product (Admin → সাইট কাস্টমাইজ → সাইজ চার্ট):
 * the chart listing its category (or the parent category), else the chart with no categories.
 */
const sizeChart = computed(() => {
  const charts = (site.config?.size_charts || []).filter((c) => c?.columns?.length);
  const cat = product.value?.category;
  const slugs = [cat?.slug, cat?.parent?.slug].filter(Boolean);
  return charts.find((c) => (c.categories || []).some((s) => slugs.includes(s)))
    || charts.find((c) => !(c.categories || []).length)
    || null;
});

const loading = ref(true);
const product = ref(null);
const related = ref([]);

const selectedColor = ref('');
const selectedSize = ref('');
const quantity = ref(1);

const adding = ref(false);
const added = ref('');          // confirmation text after a successful add ('' = not added)
const addError = ref('');
const sizeError = ref(false);
const announcement = ref('');

const activeTab = ref('desc');
const tabEls = [];
const tabsSection = ref(null);
const toast = ref('');
const sizeGuideOpen = ref(false);
const reviewModalOpen = ref(false);
const reviewMsg = ref('');
const newReview = ref({ author_name: '', rating: 5, title: '', review: '' });

const sizeBlock = ref(null);
const actionRow = ref(null);
const barVisible = ref(false);

const unique = (list) => [...new Set(list)];
// Same-origin storage URLs are compared (and served) as paths, so "/storage/x" and "http://host/storage/x" match.
const normalizeSrc = (src) => String(src || '').replace(/^https?:\/\/[^/]+(?=\/storage\/)/, '');

// ------------------------------------------------------------- variants
const variants = computed(() => (product.value?.variants || []).filter((v) => v.is_active !== false));
const attrs = computed(() => product.value?.attributes || {});

const colorNames = computed(() => {
  const fromVariants = unique(variants.value.map((v) => v.color).filter(Boolean));
  return fromVariants.length ? fromVariants : (attrs.value.colors || []);
});
const sizeOptions = computed(() => {
  const fromVariants = unique(variants.value.map((v) => v.size).filter(Boolean));
  const fromAttrs = attrs.value.sizes || [];
  if (!fromVariants.length) return fromAttrs;
  // Keep the admin's attribute order when it lists the same sizes.
  return fromAttrs.length ? [...fromAttrs.filter((s) => fromVariants.includes(s)), ...fromVariants.filter((s) => !fromAttrs.includes(s))] : fromVariants;
});

// A single-variant product needs no choice: its options are picked automatically and not shown.
const singleVariant = computed(() => variants.value.length === 1);
const showColor = computed(() => colorNames.value.length > 0 && !singleVariant.value);
const showSize = computed(() => sizeOptions.value.length > 0 && !singleVariant.value);

const matches = (v, color, size) =>
  (!colorNames.value.length || !v.color || v.color === color) && (!sizeOptions.value.length || !v.size || v.size === size);

const findVariant = (color, size) => variants.value.find((v) => matches(v, color, size)) || null;

/** The variant the current choice points to (null until every shown option is chosen). */
const selectedVariant = computed(() => {
  if (!variants.value.length) return null;
  if (singleVariant.value) return variants.value[0];
  if (colorNames.value.length && !selectedColor.value) return null;
  if (sizeOptions.value.length && !selectedSize.value) return null;
  return findVariant(selectedColor.value, selectedSize.value);
});

const tracksStock = computed(() => product.value?.track_inventory !== false);
const stockOf = (v) => (!tracksStock.value ? Infinity : Number(v?.stock ?? 0));

/** Sold out for the currently chosen colour (or simply unavailable in that colour). */
const sizeSoldOut = (size) => {
  if (!variants.value.length) return false;
  const v = findVariant(selectedColor.value, size);
  return !v || stockOf(v) <= 0;
};

const availableStock = computed(() => {
  if (!variants.value.length) return tracksStock.value ? Number(product.value?.stock ?? 0) : Infinity;
  if (selectedVariant.value) return stockOf(selectedVariant.value);
  const pool = variants.value.filter((v) => !colorNames.value.length || !v.color || v.color === selectedColor.value);
  return pool.reduce((max, v) => Math.max(max, stockOf(v)), 0);
});
const maxQuantity = computed(() => Math.max(1, Math.min(availableStock.value, 99)));
const canBuy = computed(() => availableStock.value > 0);

// ---------------------------------------------------------------- price
// Mirrors CartService::addItem, so the button shows exactly what the bag will charge.
const unitPrice = computed(() => {
  const p = product.value;
  if (!p) return 0;
  const base = Number(p.price || 0);
  const effective = Number(p.effective_price ?? base);
  const vp = Number(selectedVariant.value?.price || 0);
  return vp > 0 && Math.abs(vp - base) > 0.009 ? vp : effective;
});
const comparePrice = computed(() => {
  const p = product.value;
  if (!p) return 0;
  const candidates = [Number(selectedVariant.value?.compare_at_price || 0), Number(p.compare_at_price || 0)];
  if (p.is_on_sale) candidates.push(Number(p.price || 0));
  const best = Math.max(...candidates);
  return best > unitPrice.value ? best : 0;
});
const onSale = computed(() => comparePrice.value > unitPrice.value);
const savePercent = computed(() => (onSale.value ? Math.round(((comparePrice.value - unitPrice.value) / comparePrice.value) * 100) : 0));

// -------------------------------------------------------------- content
const shortDescription = computed(() => {
  const p = product.value;
  if (!p) return '';
  if (p.short_description) return p.short_description;
  return String(p.description || '').split(/\n\s*\n/)[0].trim();
});
const reviews = computed(() => product.value?.reviews || []);
const isWished = computed(() => !!product.value && wishlist.isInWishlist(product.value.id));

const ratingCount = computed(() => Number(product.value?.rating_stats?.count || reviews.value.length || 0));
const ratingAverage = computed(() => {
  if (!ratingCount.value) return 0;
  const avg = Number(product.value?.rating_stats?.average || 0)
    || reviews.value.reduce((s, r) => s + Number(r.rating || 0), 0) / reviews.value.length;
  return Math.round(avg * 10) / 10;
});

/** "In stock" / "Only 3 left" / "Sold out" under the price. */
const stockState = computed(() => {
  if (!canBuy.value) return { tone: 'is-out', text: 'স্টক শেষ' };
  const n = availableStock.value;
  if (Number.isFinite(n) && n <= 5) return { tone: 'is-low', text: `মাত্র ${bnNumber(n)}টি বাকি` };
  return { tone: 'is-ok', text: 'স্টকে আছে · দ্রুত ডেলিভারি' };
});

// ------------------------------------------------------------------ tabs
const tabs = computed(() => [
  { key: 'desc', title: 'বিবরণ' },
  { key: 'fit', title: 'সাইজ ও ফিট' },
  { key: 'delivery', title: 'ডেলিভারি ও এক্সচেঞ্জ' },
  { key: 'reviews', title: `রিভিউ (${bnNumber(reviews.value.length)})` },
]);
/** Arrow keys / Home / End move between tabs (WAI-ARIA tabs pattern). */
function onTabKey(e, i) {
  const n = tabs.value.length;
  const to = { ArrowRight: (i + 1) % n, ArrowLeft: (i - 1 + n) % n, Home: 0, End: n - 1 }[e.key];
  if (to === undefined) return;
  e.preventDefault();
  activeTab.value = tabs.value[to].key;
  tabEls[to]?.focus();
}
function openReviews() {
  activeTab.value = 'reviews';
  if (tabsSection.value) smoothScrollTo(tabsSection.value, { offset: -100, duration: 0.9 });
}

// ----------------------------------------------------------------- share
let toastTimer = 0;
function showToast(text) {
  toast.value = text;
  announcement.value = text;
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => { toast.value = ''; }, 2200);
}
async function share() {
  const url = window.location.href;
  try {
    if (navigator.share) { await navigator.share({ title: product.value.name, url }); return; }
    await navigator.clipboard.writeText(url);
    showToast('লিংক কপি হয়েছে');
  } catch (err) {
    if (err?.name !== 'AbortError') showToast('লিংক কপি করা যায়নি');
  }
}

// --------------------------------------------------------------- colours
const LIGHT_NAMES = ['white', 'off white', 'off-white', 'ivory', 'cream', 'beige', 'সাদা', 'অফ হোয়াইট', 'ক্রিম'];
const isLightHex = (hex) => {
  const m = /^#?([0-9a-f]{3}|[0-9a-f]{6})$/i.exec(String(hex || '').trim());
  if (!m) return false;
  const h = m[1].length === 3 ? m[1].split('').map((c) => c + c).join('') : m[1];
  const [r, g, b] = [0, 2, 4].map((i) => parseInt(h.slice(i, i + 2), 16) / 255);
  return 0.2126 * r + 0.7152 * g + 0.0722 * b > 0.85;
};
const colorOptions = computed(() => colorNames.value.map((name) => {
  const code = variants.value.find((v) => v.color === name && v.color_code)?.color_code;
  const css = code || (typeof CSS !== 'undefined' && CSS.supports?.('color', name) ? name : 'var(--pp-neutral)');
  return { name, css, light: code ? isLightHex(code) : LIGHT_NAMES.includes(name.toLowerCase()) || !code };
}));

// --------------------------------------------------------------- gallery
const baseImages = computed(() => {
  const p = product.value;
  if (!p) return [];
  return unique([p.featured_image, ...(Array.isArray(p.gallery) ? p.gallery : [])].filter(Boolean).map(normalizeSrc));
});
const imagesOfColor = (color) => unique(variants.value.filter((v) => v.color === color && v.image).map((v) => normalizeSrc(v.image)));
/** Choosing a colour shows that colour's photos first and hides other colours' photos. */
const galleryImages = computed(() => {
  let list = baseImages.value;
  if (colorNames.value.length && selectedColor.value) {
    const own = imagesOfColor(selectedColor.value);
    if (own.length) {
      const others = new Set(colorNames.value.filter((c) => c !== selectedColor.value).flatMap(imagesOfColor));
      list = unique([...own, ...list.filter((src) => !others.has(src))]);
    }
  }
  return list.map((src) => ({ src, alt: '' }));
});

// ------------------------------------------------------------ selection
function resetAdded() {
  added.value = '';
  addError.value = '';
}
function chooseColor(name) {
  if (selectedColor.value === name) return;
  selectedColor.value = name;
  // A size that's sold out in the new colour can't stay selected.
  if (selectedSize.value && sizeSoldOut(selectedSize.value)) selectedSize.value = '';
}
function chooseSize(size) {
  if (sizeSoldOut(size)) return;
  selectedSize.value = size;
  sizeError.value = false;
}
function setQuantity(n) {
  quantity.value = Math.min(maxQuantity.value, Math.max(1, n));
}

watch([selectedColor, selectedSize], () => {
  resetAdded();
  if (quantity.value > maxQuantity.value) quantity.value = maxQuantity.value;
  syncVariantUrl();
});
watch(quantity, resetAdded);

// The URL always points at the chosen variant (?variant=ID), so it can be shared or reloaded.
function syncVariantUrl() {
  if (!product.value || singleVariant.value) return;
  const id = selectedVariant.value?.id ? String(selectedVariant.value.id) : undefined;
  if ((route.query.variant || undefined) === id) return;
  const query = { ...route.query };
  if (id) query.variant = id; else delete query.variant;
  router.replace({ query }).then(setTitle).catch(() => {});
}

function setTitle() {
  if (!product.value) return;
  const siteName = window.__SITE__?.branding?.site_name || 'DRAWER CLOTHING';
  document.title = `${product.value.name} | ${siteName}`;
}

// -------------------------------------------------------- add / buy now
const selectionLabel = computed(() => {
  const color = selectedVariant.value?.color || selectedColor.value;
  const size = selectedVariant.value?.size || selectedSize.value;
  if (color && size) return `${color} / ${size}`;
  return color || (size ? `সাইজ ${size}` : '');
});

const addLabel = computed(() => {
  if (adding.value) return 'যোগ হচ্ছে…';
  if (added.value) return 'ব্যাগে যোগ হয়েছে';
  if (!canBuy.value) return 'স্টক শেষ';
  return `ব্যাগে যোগ করুন · ${taka(unitPrice.value * quantity.value)}`;
});

const barSubline = computed(() => {
  if (!canBuy.value) return 'স্টক শেষ';
  const parts = [selectionLabel.value || (showSize.value ? 'সাইজ বেছে নিন' : ''), quantity.value > 1 ? `${bnNumber(quantity.value)}টি` : ''];
  return parts.filter(Boolean).join(' · ');
});

/** Validates, adds the current choice, shows the confirmation. Resolves true on success. */
async function addCurrent() {
  if (showSize.value && !selectedSize.value) {
    sizeError.value = true;
    announcement.value = 'আগে একটি সাইজ বেছে নিন।';
    return false;
  }
  if (!canBuy.value || adding.value) return false;
  adding.value = true;
  addError.value = '';
  const qty = quantity.value;
  try {
    await cart.addToCart(
      product.value.id,
      selectedVariant.value?.id || null,
      qty,
      selectedVariant.value?.color || selectedColor.value || null,
      selectedVariant.value?.size || selectedSize.value || null,
      { openDrawer: false },
    );
    const text = `${bnNumber(qty)} × ${selectionLabel.value || product.value.name} যোগ হয়েছে।`;
    added.value = text;
    announcement.value = `${product.value.name}: ${text}`;
    return true;
  } catch (err) {
    addError.value = err.response?.data?.message || 'ব্যাগে যোগ করা যায়নি। আবার চেষ্টা করুন।';
    announcement.value = addError.value;
    return false;
  } finally {
    adding.value = false;
  }
}

const addToCart = () => addCurrent();

async function buyNow() {
  // Already added and unchanged since: don't add it twice.
  if (added.value || (await addCurrent())) {
    cart.closeDrawer();
    router.push('/checkout');
  }
}

async function addFromBar() {
  const ok = await addCurrent();
  if (!ok && sizeError.value && sizeBlock.value) {
    smoothScrollTo(sizeBlock.value, { offset: -120, duration: 0.8 });
    sizeBlock.value.querySelector('.pp-size:not(:disabled)')?.focus({ preventScroll: true });
  }
}

// --------------------------------------------------- sticky bar (mobile)
// A throttled scroll check rather than IntersectionObserver: a fast fling or a jump
// can skip straight past the row without ever "intersecting", and the bar must still appear.
let barRaf = 0;
function checkBar() {
  barRaf = 0;
  const row = actionRow.value;
  // Only once the Add to cart row has scrolled up past the top of the screen.
  barVisible.value = !!row && row.getBoundingClientRect().bottom < 0;
}
const queueBarCheck = () => { if (!barRaf) barRaf = requestAnimationFrame(checkBar); };
function watchActionRow() {
  window.removeEventListener('scroll', queueBarCheck);
  window.addEventListener('scroll', queueBarCheck, { passive: true });
  queueBarCheck();
}

// ------------------------------------------------------- delivery areas
// The store's real shipping methods (same source as checkout); fallback = agreed defaults.
const areas = ref([
  { code: 'inside_dhaka', name: 'ঢাকার ভেতরে', rate: 60, estimated_delivery: '১–২ দিন' },
  { code: 'outside_dhaka', name: 'ঢাকার বাইরে', rate: 120, estimated_delivery: '৩–৫ দিন' },
]);
async function loadAreas() {
  try {
    const res = await api.get('/public/shipping-methods');
    const list = (res.data.data || []).slice(0, 2);
    if (list.length) areas.value = list;
  } catch { /* keep fallback */ }
}

// ---------------------------------------------------------------- reviews
async function submitReview() {
  reviewMsg.value = '';
  try {
    const res = await api.post(`/public/products/${product.value.id}/reviews`, newReview.value);
    // New reviews wait for approval (Admin → রিভিউ), so they don't appear here yet.
    if (res.data?.data?.is_approved) product.value.reviews.unshift(res.data.data);
    reviewModalOpen.value = false;
    newReview.value = { author_name: '', rating: 5, title: '', review: '' };
    showToast(res.data?.message || 'ধন্যবাদ! আপনার রিভিউ যাচাইয়ের পর প্রকাশ করা হবে।');
  } catch (err) {
    reviewMsg.value = err.response?.data?.message || 'রিভিউ সাবমিট করতে সমস্যা হয়েছে';
  }
}

// ------------------------------------------------------------------ load
async function loadProduct() {
  loading.value = true;
  try {
    const res = await api.get(`/public/products/${route.params.slug}`);
    product.value = res.data.data.product;
    related.value = (res.data.data.related_products || []).slice(0, 4);
  } catch (err) {
    product.value = null;
    related.value = [];
    if (err.response?.status !== 404) console.error('Failed to load product', err);
  } finally {
    loading.value = false;
  }
  if (!product.value) return;

  // Initial choice: the variant in the URL, else the first colour that has stock; no size until the shopper picks one.
  const fromUrl = variants.value.find((v) => String(v.id) === String(route.query.variant || ''));
  if (singleVariant.value) {
    selectedColor.value = variants.value[0].color || '';
    selectedSize.value = variants.value[0].size || '';
  } else if (fromUrl) {
    selectedColor.value = fromUrl.color || '';
    selectedSize.value = stockOf(fromUrl) > 0 ? fromUrl.size || '' : '';
  } else {
    const firstInStock = variants.value.find((v) => stockOf(v) > 0);
    selectedColor.value = colorNames.value.length ? (firstInStock?.color || colorNames.value[0]) : '';
    selectedSize.value = sizeOptions.value.length === 1 && !sizeSoldOut(sizeOptions.value[0]) ? sizeOptions.value[0] : '';
  }
  quantity.value = 1;
  resetAdded();
  setTitle();
  await nextTick();
  watchActionRow();
}

onMounted(() => {
  loadProduct();
  loadAreas();
});
onBeforeUnmount(() => {
  window.removeEventListener('scroll', queueBarCheck);
  cancelAnimationFrame(barRaf);
});
</script>

<style scoped>
/* -------------------------------------------------------------------------
   Brand tokens: the same values the cart drawer / sheet use
   (resources/css/app.css @theme + storefront components).
------------------------------------------------------------------------- */
.pp {
  --pp-text: #111111;                         /* primary-black */
  --pp-text-2: rgba(17, 17, 17, 0.72);        /* the site's text-[#111111]/80 body copy */
  --pp-muted: #69736E;                        /* darkest site grey; taupe #A89B8A is too light for text (2.5:1) */
  --pp-bg: #F7F5F0;                           /* ivory page background */
  --pp-surface: #FFFFFF;
  --pp-neutral: #EFEBE4;                      /* light neutral (image placeholders, chips, sold-out sizes) */
  --pp-border: #D8CCBC;                       /* sand */
  --pp-divider: #ECE6DC;
  --pp-accent: #111111;                       /* primary button */
  --pp-on-accent: #FFFFFF;
  --pp-gold: #C5A880;                         /* luxury-gold, rating stars */
  --pp-sale: #111111;                         /* the site's sale / discount badges are black */
  --pp-sale-tint: color-mix(in srgb, var(--pp-sale) 7%, var(--pp-surface));
  --pp-sale-ink: color-mix(in srgb, var(--pp-sale) 90%, #000000);
  --pp-success: #047857;                      /* emerald-700, storefront success messages */
  --pp-success-tint: color-mix(in srgb, var(--pp-success) 9%, var(--pp-surface));
  --pp-warn: #B45309;                         /* amber-700, low stock */
  --pp-error: #E11D48;                        /* rose-600, storefront form errors */
  --pp-radius-btn: 12px;
  --pp-radius-card: 24px;
  --pp-shadow: 0 1px 2px rgba(17, 17, 17, 0.04), 0 18px 40px -24px rgba(17, 17, 17, 0.18);

  color: var(--pp-text);
  font-family: var(--font-sans);
}
.pp :focus-visible { outline: 2px solid var(--pp-text); outline-offset: 2px; }
.sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border: 0; }

/* Same container as the header, footer and every other storefront page. */
.pp-wrap { max-width: var(--container-7xl, 1600px); margin: 0 auto; padding: 20px 32px 72px; }

/* ------------------------------------------------------------ breadcrumb */
.pp-crumb { display: flex; flex-wrap: wrap; align-items: center; gap: 6px; font-size: 13px; color: var(--pp-muted); line-height: 1.4; }
.pp-crumb a { color: inherit; transition: color .15s ease; }
.pp-crumb a:hover { color: var(--pp-text); }
.pp-crumb svg { flex-shrink: 0; opacity: .7; }
.pp-crumb-current { min-width: 0; max-width: 100%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: var(--pp-text); }

/* ------------------------------------------------------------------ hero */
.pp-hero { display: grid; grid-template-columns: minmax(0, 1fr); gap: 24px; margin-top: 20px; align-items: start; }
.pp-card { background: var(--pp-surface); border: 1px solid var(--pp-divider); border-radius: var(--pp-radius-card); box-shadow: var(--pp-shadow); }

@media (min-width: 1024px) {
  .pp-hero {
    /* The photo is sized by the screen height, so it never grows huge on wide screens. */
    --pp-stage-h: clamp(460px, calc(100svh - 330px), 680px);
    grid-template-columns: auto minmax(0, 1fr);
    gap: clamp(32px, 3.5vw, 56px);
  }
  .pp-gallery { width: calc(var(--pp-stage-h) * 0.75); position: sticky; top: 96px; }
  .pp-details { position: sticky; top: 96px; }
}
@media (min-width: 1280px) {
  /* Bento: main photo (2 parts) + next two photos (1 part), all the same height. */
  .pp-gallery--bento { width: calc(var(--pp-stage-h) * 1.125 + 12px); }
}

/* -------------------------------------------------------------- buy card */
.pp-details { display: flex; flex-direction: column; gap: 22px; min-width: 0; padding: 32px; }

.pp-topline { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.pp-chip { display: inline-flex; align-items: center; height: 28px; padding: 0 12px; border-radius: 9999px; background: var(--pp-neutral); color: var(--pp-text); font-size: 12px; font-weight: 600; letter-spacing: 0.04em; text-transform: uppercase; transition: background-color .15s ease; }
.pp-chip:hover { background: var(--pp-border); }
.pp-rating { display: inline-flex; align-items: center; gap: 4px; background: none; border: 0; padding: 4px 0; font-size: 13px; color: var(--pp-text-2); cursor: pointer; }
.pp-rating svg { color: var(--pp-gold); }
.pp-rating strong { color: var(--pp-text); font-weight: 600; }
.pp-rating:hover span { text-decoration: underline; text-underline-offset: 3px; }
.pp-icons { display: flex; gap: 6px; margin-left: auto; }
.pp-icon-btn { width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border-radius: 9999px; border: 1px solid var(--pp-divider); background: var(--pp-surface); color: var(--pp-text); cursor: pointer; transition: background-color .15s ease, border-color .15s ease, transform .15s ease; }
.pp-icon-btn:hover { border-color: var(--pp-text); }
.pp-icon-btn:active { transform: scale(0.94); }
.pp-icon-btn.is-on { background: var(--pp-text); border-color: var(--pp-text); color: var(--pp-on-accent); }

.pp-head { display: flex; flex-direction: column; gap: 10px; }
.pp-title { font-family: var(--font-serif); font-size: 34px; font-weight: 700; line-height: 1.15; letter-spacing: -0.015em; color: var(--pp-text); overflow-wrap: anywhere; }
.pp-price-row { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; }
.pp-price { font-size: 28px; font-weight: 700; font-variant-numeric: tabular-nums; letter-spacing: -0.01em; }
.pp-compare { font-size: 17px; color: var(--pp-muted); font-variant-numeric: tabular-nums; }
.pp-save { padding: 4px 10px; border-radius: 9999px; background: var(--pp-sale-tint); color: var(--pp-sale-ink); border: 1px solid var(--pp-divider); font-size: 12px; font-weight: 700; line-height: 1.4; }
.pp-stock { display: inline-flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 500; }
.pp-stock-dot { width: 8px; height: 8px; border-radius: 9999px; background: currentColor; box-shadow: 0 0 0 4px color-mix(in srgb, currentColor 15%, transparent); }
.pp-stock.is-ok { color: var(--pp-success); }
.pp-stock.is-low { color: var(--pp-warn); }
.pp-stock.is-out { color: var(--pp-error); }
.pp-short { font-size: 15px; line-height: 1.65; color: var(--pp-text-2); padding-bottom: 22px; margin-bottom: -2px; border-bottom: 1px solid var(--pp-divider); }

/* Options */
.pp-option { display: flex; flex-direction: column; gap: 12px; }
.pp-option-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
.pp-option-label { display: flex; align-items: baseline; gap: 8px; font-size: 14px; font-weight: 600; }
.pp-option-value { font-weight: 400; color: var(--pp-text-2); }
.pp-link { display: inline-flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 500; color: var(--pp-text); text-decoration: underline; text-underline-offset: 3px; background: none; border: 0; padding: 4px 0; cursor: pointer; }
.pp-link:hover { text-decoration-thickness: 2px; }

.pp-swatches { display: flex; flex-wrap: wrap; gap: 8px; margin: -6px; }   /* 44px touch areas around 32px dots */
.pp-swatch { width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; border-radius: 9999px; background: none; border: 0; padding: 0; cursor: pointer; }
.pp-swatch-dot { width: 32px; height: 32px; border-radius: 9999px; transition: box-shadow .15s ease; }
.pp-swatch-dot.is-light { box-shadow: inset 0 0 0 1px var(--pp-border); }
.pp-swatch.is-selected .pp-swatch-dot { box-shadow: 0 0 0 2px var(--pp-surface), 0 0 0 4px var(--pp-text); }
.pp-swatch.is-selected .pp-swatch-dot.is-light { box-shadow: inset 0 0 0 1px var(--pp-border), 0 0 0 2px var(--pp-surface), 0 0 0 4px var(--pp-text); }

.pp-sizes { display: flex; flex-wrap: wrap; gap: 8px; }
.pp-size {
  min-width: 58px; height: 46px; padding: 0 16px;
  border-radius: var(--pp-radius-btn); border: 1.5px solid var(--pp-divider);
  background: var(--pp-surface); color: var(--pp-text);
  font-size: 14px; font-weight: 600; cursor: pointer;
  transition: border-color .15s ease, background-color .15s ease, color .15s ease, transform .15s ease;
}
.pp-size:hover:not(:disabled) { border-color: var(--pp-text); }
.pp-size:active:not(:disabled) { transform: scale(0.96); }
.pp-size.is-selected { background: var(--pp-text); border-color: var(--pp-text); color: var(--pp-on-accent); }
.pp-size.is-soldout { background: var(--pp-neutral); border-color: var(--pp-neutral); color: var(--pp-muted); text-decoration: line-through; cursor: not-allowed; }
.pp-size-error { font-size: 13px; color: var(--pp-error); }

/* Actions */
.pp-buy-block { display: flex; flex-direction: column; }
.pp-actions { display: flex; gap: 10px; align-items: stretch; }
.pp-stepper { display: flex; align-items: center; flex-shrink: 0; border-radius: var(--pp-radius-btn); background: var(--pp-neutral); }
.pp-step { width: 44px; height: 54px; display: flex; align-items: center; justify-content: center; background: none; border: 0; color: var(--pp-text); cursor: pointer; border-radius: var(--pp-radius-btn); }
.pp-step:disabled { color: var(--pp-muted); opacity: .45; cursor: not-allowed; }
.pp-qty { min-width: 26px; text-align: center; font-size: 15px; font-weight: 600; font-variant-numeric: tabular-nums; }

.pp-btn-primary, .pp-btn-secondary {
  display: inline-flex; align-items: center; justify-content: center; gap: 8px;
  height: 54px; padding: 0 20px;
  border-radius: var(--pp-radius-btn);
  font-size: 15px; letter-spacing: 0.01em; cursor: pointer;
  transition: background-color .2s ease, color .2s ease, opacity .2s ease, transform .15s ease, box-shadow .2s ease;
}
.pp-btn-primary { background: var(--pp-accent); color: var(--pp-on-accent); border: 0; font-weight: 600; box-shadow: 0 10px 24px -14px rgba(17, 17, 17, 0.55); }
.pp-btn-primary:hover:not(:disabled) { background: #000000; box-shadow: 0 14px 28px -14px rgba(17, 17, 17, 0.65); }
.pp-btn-secondary { background: var(--pp-surface); color: var(--pp-text); border: 1.5px solid var(--pp-text); font-weight: 700; }
.pp-btn-secondary:hover:not(:disabled) { background: var(--pp-text); color: var(--pp-on-accent); }
.pp-btn-primary:active:not(:disabled), .pp-btn-secondary:active:not(:disabled) { transform: scale(0.985); }
.pp-btn-primary:disabled, .pp-btn-secondary:disabled { opacity: .45; cursor: not-allowed; box-shadow: none; }
.pp-add { flex: 1 1 auto; min-width: 0; font-variant-numeric: tabular-nums; }
.pp-add span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.pp-buy { width: 100%; margin-top: 10px; }
.pp-add-error { margin-top: 10px; font-size: 13px; color: var(--pp-error); }

.pp-confirm { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-top: 12px; padding: 12px 14px; border-radius: var(--pp-radius-btn); background: var(--pp-success-tint); font-size: 14px; animation: pp-pop .35s cubic-bezier(0.22, 1, 0.36, 1); }
.pp-confirm-text { display: flex; align-items: center; gap: 8px; color: var(--pp-success); font-weight: 500; min-width: 0; }
.pp-confirm-icon { width: 22px; height: 22px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; border-radius: 9999px; background: var(--pp-success); color: #FFFFFF; }
.pp-confirm-link { flex-shrink: 0; display: inline-flex; align-items: center; gap: 4px; font-weight: 700; color: var(--pp-text); background: none; border: 0; padding: 4px 0; cursor: pointer; }
.pp-confirm-link:hover { text-decoration: underline; text-underline-offset: 3px; }
@keyframes pp-pop { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: none; } }

/* Delivery & trust tiles */
.pp-perks { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
.pp-perks li { display: flex; align-items: center; gap: 12px; padding: 12px 14px; border-radius: 16px; background: var(--pp-bg); }
.pp-perk-icon { width: 38px; height: 38px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; border-radius: 12px; background: var(--pp-surface); color: var(--pp-text); }
.pp-perk-text { display: flex; flex-direction: column; min-width: 0; line-height: 1.35; }
.pp-perk-text strong { font-size: 13.5px; font-weight: 600; }
.pp-perk-text span { font-size: 12.5px; color: var(--pp-text-2); }

/* ------------------------------------------------------------ details tabs */
.pp-tabs-card { margin-top: 56px; padding: 8px 32px 32px; }
.pp-tabs { display: flex; gap: 28px; border-bottom: 1px solid var(--pp-divider); overflow-x: auto; scrollbar-width: none; }
.pp-tabs::-webkit-scrollbar { display: none; }
.pp-tab { position: relative; flex-shrink: 0; height: 56px; padding: 0; background: none; border: 0; font-size: 15px; font-weight: 600; color: var(--pp-muted); cursor: pointer; transition: color .15s ease; }
.pp-tab:hover { color: var(--pp-text); }
.pp-tab.is-active { color: var(--pp-text); }
.pp-tab::after { content: ''; position: absolute; left: 0; right: 0; bottom: -1px; height: 2px; border-radius: 2px; background: var(--pp-text); transform: scaleX(0); transition: transform .25s cubic-bezier(0.22, 1, 0.36, 1); }
.pp-tab.is-active::after { transform: scaleX(1); }
.pp-panel { padding-top: 28px; font-size: 15px; line-height: 1.7; color: var(--pp-text-2); outline: none; animation: pp-fade .3s ease; }
@keyframes pp-fade { from { opacity: 0; } to { opacity: 1; } }
.pp-panel strong { color: var(--pp-text); font-weight: 600; }
.pp-panel-grid { display: grid; grid-template-columns: minmax(0, 1fr); gap: 28px; align-items: start; }
@media (min-width: 900px) { .pp-panel-grid { grid-template-columns: minmax(0, 1.6fr) minmax(260px, 1fr); gap: 56px; } }
.pp-panel-stack { display: flex; flex-direction: column; gap: 12px; }
.pp-panel-lead { font-size: 16px; color: var(--pp-text); opacity: .85; }
.pp-pre { white-space: pre-line; }
.pp-specs { display: flex; flex-direction: column; border-radius: 16px; background: var(--pp-bg); padding: 6px 18px; }
.pp-specs > div { display: flex; justify-content: space-between; gap: 16px; padding: 11px 0; font-size: 14px; }
.pp-specs > div + div { border-top: 1px solid var(--pp-divider); }
.pp-specs dt { color: var(--pp-muted); }
.pp-specs dd { color: var(--pp-text); font-weight: 500; text-align: right; overflow-wrap: anywhere; }
.pp-panel-cta { display: flex; align-items: center; gap: 14px; padding: 18px; border-radius: 16px; background: var(--pp-bg); border: 0; text-align: left; cursor: pointer; color: var(--pp-text); transition: background-color .15s ease; width: 100%; }
.pp-panel-cta:hover { background: var(--pp-neutral); }
.pp-panel-cta > span { display: flex; flex-direction: column; flex: 1; line-height: 1.4; }
.pp-panel-cta > span > span { font-size: 13px; color: var(--pp-text-2); }

/* Reviews */
.pp-reviews-wrap { display: grid; grid-template-columns: minmax(0, 1fr); gap: 28px; align-items: start; }
@media (min-width: 900px) { .pp-reviews-wrap { grid-template-columns: 240px minmax(0, 1fr); gap: 56px; } }
.pp-review-summary { display: flex; flex-direction: column; align-items: flex-start; gap: 6px; padding: 22px; border-radius: 16px; background: var(--pp-bg); }
.pp-review-avg { font-family: var(--font-serif); font-size: 44px; font-weight: 700; line-height: 1; color: var(--pp-text); }
.pp-stars-row { display: inline-flex; gap: 2px; color: var(--pp-gold); }
.pp-stars-row .is-dim { color: var(--pp-border); }
.pp-review-count { font-size: 13px; color: var(--pp-text-2); }
.pp-review-btn { height: 44px; margin-top: 10px; width: 100%; font-size: 14px; }
.pp-reviews { display: flex; flex-direction: column; }
.pp-reviews li { display: flex; flex-direction: column; gap: 6px; padding: 18px 0; }
.pp-reviews li:first-child { padding-top: 0; }
.pp-reviews li + li { border-top: 1px solid var(--pp-divider); }
.pp-review-head { display: flex; align-items: center; gap: 10px; }
.pp-review-head strong { font-size: 14px; }
.pp-avatar { width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 9999px; background: var(--pp-neutral); color: var(--pp-text); font-size: 13px; font-weight: 700; }
.pp-stars-sm { margin-left: auto; }
.pp-review-title { font-weight: 600; color: var(--pp-text); }
.pp-review-empty { align-self: center; }

/* ------------------------------------------------------ you may also like */
.pp-related { margin-top: 72px; }
.pp-related-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 16px; }
.pp-related-title { font-family: var(--font-serif); font-size: 28px; font-weight: 700; line-height: 1.25; letter-spacing: -0.01em; }
.pp-related-all { display: inline-flex; align-items: center; gap: 6px; font-size: 14px; font-weight: 600; color: var(--pp-text); white-space: nowrap; }
.pp-related-all:hover { text-decoration: underline; text-underline-offset: 3px; }
.pp-related-grid { margin-top: 24px; display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 32px 24px; }

/* ------------------------------------------------------ sticky bottom bar */
.pp-bar { display: none; }
.pp-bar-spacer { display: none; }
@media (max-width: 1023.98px) {
  .pp-bar {
    position: fixed; left: 12px; right: 12px; bottom: calc(12px + env(safe-area-inset-bottom)); z-index: 40;
    display: flex; align-items: center; gap: 10px;
    padding: 8px 8px 8px 8px;
    background: rgba(255, 255, 255, 0.94);
    -webkit-backdrop-filter: blur(16px); backdrop-filter: blur(16px);
    border: 1px solid var(--pp-divider);
    border-radius: 18px;
    box-shadow: 0 16px 36px -14px rgba(17, 17, 17, 0.35);
    transform: translateY(140%);
    visibility: hidden;
    transition: transform .35s cubic-bezier(0.22, 1, 0.36, 1), visibility 0s linear .35s;
  }
  .pp-bar.is-visible { transform: none; visibility: visible; transition: transform .35s cubic-bezier(0.22, 1, 0.36, 1); }
  .pp-bar-thumb { width: 44px; height: 52px; flex-shrink: 0; border-radius: 10px; object-fit: cover; object-position: center top; background: var(--pp-neutral); }
  .pp-bar-info { display: flex; flex-direction: column; min-width: 0; flex: 1 1 auto; }
  .pp-bar-price { font-size: 16px; font-weight: 700; font-variant-numeric: tabular-nums; line-height: 1.2; }
  .pp-bar-sub { font-size: 12px; color: var(--pp-text-2); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .pp-bar-btn { flex: 0 0 auto; height: 50px; padding: 0 18px; font-size: 14px; }
  .pp-bar-spacer { display: block; height: calc(84px + env(safe-area-inset-bottom)); }
}

/* Toast ("link copied") */
.pp-toast { position: fixed; left: 50%; bottom: 32px; z-index: 60; transform: translateX(-50%); padding: 10px 18px; border-radius: 9999px; background: var(--pp-text); color: var(--pp-on-accent); font-size: 14px; font-weight: 500; box-shadow: 0 12px 30px -12px rgba(17, 17, 17, 0.5); white-space: nowrap; }
.pp-toast-enter-active, .pp-toast-leave-active { transition: opacity .25s ease, transform .25s ease; }
.pp-toast-enter-from, .pp-toast-leave-to { opacity: 0; transform: translate(-50%, 8px); }

/* ------------------------------------------------------------- skeleton */
.pp-sk-crumb { height: 14px; width: 220px; border-radius: 4px; }
.pp-sk-image { aspect-ratio: 3 / 4; border-radius: 20px; }
@media (min-width: 1024px) { .pp-sk-image { width: calc(var(--pp-stage-h) * 0.75); } }
.pp-sk-card { padding: 32px; display: flex; flex-direction: column; gap: 18px; }
.pp-sk-line { height: 14px; border-radius: 4px; }
.pp-sk-title { height: 40px; width: 80%; border-radius: 6px; }
.pp-sk-block { height: 260px; border-radius: 16px; }

.pp-missing { text-align: center; padding-top: 96px; padding-bottom: 120px; display: flex; flex-direction: column; align-items: center; gap: 12px; color: var(--pp-text-2); font-size: 15px; }
.pp-missing-title { font-family: var(--font-serif); font-size: 28px; font-weight: 700; color: var(--pp-text); }
.pp-missing-btn { margin-top: 12px; padding: 0 28px; }

/* ---------------------------------------------------------------- tablets */
@media (max-width: 1023.98px) {
  .pp-wrap { padding: 16px 24px 48px; }
  .pp-crumb, .pp-hero, .pp-tabs-card { max-width: 640px; margin-left: auto; margin-right: auto; }
  .pp-related-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
  .pp-related-grid > :nth-child(4) { display: none; }
}

/* ----------------------------------------------------------------- phones */
@media (max-width: 639.98px) {
  .pp-wrap { padding: 12px 16px 40px; }
  .pp-hero { margin-top: 12px; gap: 20px; }
  /* Flat on phones: the card chrome would only eat width. */
  .pp-details { padding: 0; border: 0; border-radius: 0; box-shadow: none; background: transparent; gap: 20px; }
  .pp-title { font-size: 26px; }
  .pp-price { font-size: 24px; }
  .pp-perks { gap: 8px; }
  .pp-perks li { flex-direction: column; align-items: flex-start; gap: 8px; padding: 12px; background: var(--pp-surface); border: 1px solid var(--pp-divider); }
  .pp-perk-icon { width: 32px; height: 32px; border-radius: 10px; background: var(--pp-bg); }
  .pp-tabs-card { margin-top: 36px; margin-left: -16px; margin-right: -16px; padding: 4px 16px 24px; border-radius: 20px; }
  .pp-tabs { gap: 22px; }
  .pp-related { margin-top: 48px; }
  .pp-related-title { font-size: 22px; }
  .pp-related-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 24px 12px; }
  .pp-related-grid > :nth-child(4) { display: block; }
}

@media (prefers-reduced-motion: reduce) {
  .pp-bar, .pp-tab::after, .pp-panel, .pp-confirm, .pp-btn-primary, .pp-btn-secondary { transition: none; animation: none; }
}
</style>
