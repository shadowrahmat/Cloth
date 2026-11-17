<template>
  <section id="lookbook" class="py-14 sm:py-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Main Editorial Lookbook Container (Clean, Friendly & Luxury Off-White) -->
    <div class="relative bg-white rounded-3xl shadow-sm border border-[#E7EBE8] p-6 sm:p-10 lg:p-12 overflow-hidden">
      
      <!-- Top Header & Look Selector -->
      <div class="flex flex-col lg:flex-row lg:items-end justify-between pb-8 border-b border-[#E7EBE8] gap-6">
        <div class="space-y-2.5 max-w-2xl">
          <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-[#16A36A] animate-pulse"></span>
            <span class="text-xs uppercase tracking-[0.25em] font-bold text-[#14382A]">
              {{ content.subheading || 'কিউরেটেড উইন্টার ক্যাপসুল ২০২৭' }}
            </span>
          </div>

          <h2 class="font-serif text-3xl sm:text-4xl lg:text-5xl text-[#111111] font-normal tracking-tight">
            {{ content.heading || 'উইন্টার লুকবুক' }}
          </h2>

          <p class="text-xs sm:text-sm text-[#69736E] font-light leading-relaxed max-w-xl">
            {{ content.description || 'মডেলের পরিহিত পোশাকে সরাসরি ক্লিক করে পছন্দসই সাইজ নির্বাচন করুন এবং এককভাবে বা সম্পূর্ণ সেট একসাথে ব্যাগে যোগ করুন।' }}
          </p>
        </div>

        <!-- Look Switcher Navigation Buttons -->
        <div class="flex items-center gap-2 self-start lg:self-end">
          <button
            type="button"
            @click="prevLook"
            class="w-10 h-10 rounded-full border border-[#E7EBE8] hover:border-[#14382A] hover:bg-[#14382A] hover:text-white flex items-center justify-center text-sm transition-colors cursor-pointer shadow-2xs"
            title="পূর্ববর্তী লুক"
            aria-label="Previous Look"
          >
            ‹
          </button>
          <span class="text-xs font-mono font-bold text-[#14382A] px-2">
            ০{{ currentLookIndex + 1 }} / ০{{ activeLooks.length }}
          </span>
          <button
            type="button"
            @click="nextLook"
            class="w-10 h-10 rounded-full border border-[#E7EBE8] hover:border-[#14382A] hover:bg-[#14382A] hover:text-white flex items-center justify-center text-sm transition-colors cursor-pointer shadow-2xs"
            title="পরবর্তী লুক"
            aria-label="Next Look"
          >
            ›
          </button>
        </div>
      </div>

      <!-- Quick Look Tab Pills (Super Easy Look Selection) -->
      <div class="pt-6 pb-2 flex items-center gap-2.5 overflow-x-auto scrollbar-none">
        <button
          v-for="(look, idx) in activeLooks"
          :key="look.id || idx"
          type="button"
          @click="selectLook(idx)"
          :class="[
            'px-4 py-2.5 rounded-xl text-xs font-medium transition-all duration-300 flex items-center gap-2.5 shrink-0 cursor-pointer border',
            currentLookIndex === idx
              ? 'bg-[#14382A] text-white border-[#14382A] shadow-sm font-semibold'
              : 'bg-[#F7F8FA] hover:bg-slate-100 text-[#171C1A] border-[#E7EBE8]'
          ]"
        >
          <span class="w-2 h-2 rounded-full" :class="currentLookIndex === idx ? 'bg-[#16A36A]' : 'bg-slate-300'"></span>
          <span>লুক ০{{ idx + 1 }}: {{ look.name }}</span>
          <span class="text-[10px] opacity-75 font-mono">({{ look.garments.length }} টি পণ্য)</span>
        </button>
      </div>

      <!-- Main Split Workspace: Left Canvas & Right Product Cards -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 pt-8 items-start">
        
        <!-- ============================================== -->
        <!-- LEFT: Interactive Model Photography (7 Cols) -->
        <!-- ============================================== -->
        <div class="lg:col-span-7 flex flex-col space-y-4">
          <div class="relative w-full aspect-[3/3.8] rounded-2xl overflow-hidden bg-[#F7F8FA] border border-[#E7EBE8] shadow-md group">
            
            <!-- Model Image -->
            <transition name="look-crossfade" mode="out-in">
              <img
                :key="currentLook.image"
                :src="currentLook.image"
                :alt="currentLook.name"
                class="w-full h-full object-cover object-top transition-transform duration-700 group-hover:scale-102"
              />
            </transition>

            <!-- Subtle Gradient Base -->
            <div class="absolute inset-x-0 bottom-0 h-36 bg-gradient-to-t from-black/70 to-transparent pointer-events-none"></div>

            <!-- Top Left Tag -->
            <div class="absolute top-4 left-4 z-20">
              <div class="bg-white/95 backdrop-blur-md px-3.5 py-1.5 rounded-full border border-white/60 shadow-xs flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-[#16A36A]"></span>
                <span class="text-[11px] font-mono tracking-wider uppercase font-bold text-[#14382A]">
                  লুক ০{{ currentLookIndex + 1 }} • {{ currentLook.name }}
                </span>
              </div>
            </div>

            <!-- Interactive Hotspot Pins on the Image -->
            <div
              v-for="(garment, gIdx) in currentLook.garments"
              :key="garment.id || gIdx"
              class="absolute z-20 transition-all duration-300"
              :style="{ top: garment.hotspot?.top || '50%', left: garment.hotspot?.left || '50%' }"
            >
              <button
                type="button"
                @click="scrollToGarment(gIdx)"
                @mouseenter="activeGarmentIndex = gIdx"
                :class="[
                  'relative px-2.5 py-1.5 -translate-x-1/2 -translate-y-1/2 rounded-full flex items-center gap-1.5 text-[11px] font-semibold transition-all duration-300 shadow-lg cursor-pointer backdrop-blur-md',
                  activeGarmentIndex === gIdx
                    ? 'bg-[#14382A] text-white ring-4 ring-[#16A36A]/50 scale-110'
                    : 'bg-white/95 text-[#111111] hover:bg-[#14382A] hover:text-white'
                ]"
                :title="`দেখুন: ${garment.name}`"
              >
                <span class="w-1.5 h-1.5 rounded-full bg-[#16A36A]"></span>
                <span>{{ garment.category }}</span>
                <span class="font-mono text-[10px] opacity-80">৳{{ formatPrice(garment.price) }}</span>
              </button>
            </div>

            <!-- Bottom Left Style Tag -->
            <div class="absolute bottom-4 left-4 right-4 z-20 flex items-center justify-between text-white text-xs">
              <span class="font-light italic line-clamp-1 max-w-sm">"{{ currentLook.tagline }}"</span>
              <span class="text-[10px] font-mono text-[#C5A880] uppercase tracking-wider shrink-0 bg-black/40 px-2.5 py-1 rounded-md backdrop-blur-xs">
                {{ currentLook.garments.length }} টি আইটেম
              </span>
            </div>
          </div>

          <!-- Helpful Hint for User -->
          <div class="p-3 bg-emerald-50/70 border border-emerald-200/60 rounded-xl flex items-center gap-2.5 text-xs text-emerald-950">
            <span class="text-base">💡</span>
            <span>ছবির পিনে অথবা ডানপাশের পোশাকে ক্লিক করে সহজেই সাইজ নির্বাচন করুন ও অর্ডার করুন।</span>
          </div>
        </div>

        <!-- ============================================== -->
        <!-- RIGHT: "Shop This Look" Garment Rack (5 Cols) -->
        <!-- ============================================== -->
        <div class="lg:col-span-5 flex flex-col space-y-5">
          
          <!-- Rack Title -->
          <div class="pb-3 border-b border-[#E7EBE8] flex items-center justify-between">
            <div>
              <span class="text-[10px] uppercase tracking-widest text-[#16A36A] font-bold block">
                এই লুকের পোশাকসমূহ
              </span>
              <h3 class="font-serif text-xl sm:text-2xl text-[#111111] font-medium mt-0.5">
                {{ currentLook.name }}
              </h3>
            </div>
            <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-mono font-bold">
              {{ currentLook.garments.length }} টি পণ্য
            </span>
          </div>

          <!-- Product Cards List -->
          <div class="space-y-3.5">
            <div
              v-for="(garment, gIdx) in currentLook.garments"
              :key="garment.id || gIdx"
              :id="`garment-card-${gIdx}`"
              @mouseenter="activeGarmentIndex = gIdx"
              :class="[
                'p-4 rounded-2xl transition-all duration-300 border flex flex-col gap-3.5 bg-white',
                activeGarmentIndex === gIdx
                  ? 'border-[#14382A] ring-2 ring-[#14382A]/10 shadow-md bg-slate-50/50'
                  : 'border-[#E7EBE8] hover:border-slate-300 shadow-2xs'
              ]"
            >
              <!-- Top Row: Thumbnail, Name, Category, Price -->
              <div class="flex items-start gap-3.5">
                <!-- Thumbnail -->
                <router-link :to="`/product/${garment.slug}`" class="shrink-0 group/img">
                  <div class="w-16 h-18 sm:w-18 sm:h-20 rounded-xl overflow-hidden bg-[#F7F8FA] border border-[#E7EBE8]">
                    <img
                      :src="garment.image"
                      :alt="garment.name"
                      class="w-full h-full object-cover group-hover/img:scale-105 transition-transform"
                    />
                  </div>
                </router-link>

                <!-- Information -->
                <div class="min-w-0 flex-1">
                  <div class="flex items-center justify-between gap-2">
                    <span class="text-[10px] uppercase font-bold tracking-wider text-[#16A36A]">
                      {{ garment.category }}
                    </span>
                    <span class="font-mono text-sm sm:text-base font-bold text-[#14382A]">
                      ৳ {{ formatPrice(garment.price) }}
                    </span>
                  </div>

                  <router-link
                    :to="`/product/${garment.slug}`"
                    class="font-semibold text-xs sm:text-sm text-[#111111] hover:text-[#16A36A] transition-colors block truncate mt-0.5"
                  >
                    {{ garment.name }}
                  </router-link>

                  <p class="text-[11px] text-[#69736E] font-light truncate mt-0.5">
                    {{ garment.fabric }}
                  </p>
                </div>
              </div>

              <!-- Bottom Row: Size Picker & Quick Add Button -->
              <div class="flex items-center justify-between gap-3 pt-2.5 border-t border-[#E7EBE8] text-xs">
                
                <!-- Size Picker -->
                <div class="flex items-center gap-1.5 flex-wrap">
                  <span class="text-[10px] uppercase font-mono text-[#69736E]">সাইজ:</span>
                  <button
                    v-for="s in garment.sizes"
                    :key="s"
                    type="button"
                    @click="selectedSizes[garment.id] = s"
                    :class="[
                      'px-2.5 py-1 rounded-md text-[11px] font-mono font-semibold transition-colors border cursor-pointer',
                      selectedSizes[garment.id] === s
                        ? 'bg-[#14382A] text-white border-[#14382A]'
                        : 'bg-white text-[#171C1A] border-[#E7EBE8] hover:border-[#14382A]'
                    ]"
                  >
                    {{ s }}
                  </button>
                </div>

                <!-- Add to Bag Button -->
                <button
                  type="button"
                  @click="quickAddToCart(garment)"
                  :disabled="isAdding[garment.id]"
                  class="px-4 py-2 rounded-xl bg-[#14382A] hover:bg-[#16A36A] text-white text-xs font-semibold uppercase tracking-wider flex items-center gap-1.5 transition-colors shadow-xs cursor-pointer disabled:opacity-50 shrink-0"
                >
                  <svg v-if="!isAdding[garment.id] && !addedGarments[garment.id]" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                  </svg>
                  <span v-if="isAdding[garment.id]">যোগ হচ্ছে...</span>
                  <span v-else-if="addedGarments[garment.id]">যোগ হয়েছে ✓</span>
                  <span v-else>+ ব্যাগে নিন</span>
                </button>

              </div>
            </div>
          </div>

          <!-- Bundle Card: Buy Full Look in 1 Click -->
          <div class="p-5 rounded-2xl bg-[#F7F8FA] border border-[#E7EBE8] space-y-4 shadow-xs">
            <div class="flex items-center justify-between">
              <div>
                <span class="text-[11px] text-[#69736E] font-medium block">
                  সম্পূর্ণ লুকের মোট মূল্য ({{ currentLook.garments.length }} টি পণ্য):
                </span>
                <span class="font-serif text-2xl font-bold text-[#14382A]">
                  ৳ {{ formatPrice(currentLookTotal) }}
                </span>
              </div>

              <span class="text-[11px] font-mono text-emerald-800 font-bold bg-emerald-100 px-3 py-1 rounded-full border border-emerald-200">
                ফ্রি ডেলিভারি
              </span>
            </div>

            <!-- Big Add Entire Look Button -->
            <button
              type="button"
              @click="addEntireLookToBag"
              :disabled="isAddingAll"
              class="w-full py-3.5 px-6 rounded-xl bg-[#14382A] hover:bg-[#16A36A] text-white text-xs font-bold uppercase tracking-wider transition-all duration-300 shadow-md flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50"
            >
              <svg v-if="!isAddingAll" class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
              </svg>
              <span v-if="isAddingAll">সবগুলো ব্যাগে যোগ হচ্ছে...</span>
              <span v-else>সম্পূর্ণ সেট একসাথে ব্যাগে নিন ({{ currentLook.garments.length }} টি পোশাক)</span>
            </button>
          </div>

        </div>

      </div>

      <!-- Bottom 3 Pillars (Clean, Trust Badges) -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-10 mt-10 border-t border-[#E7EBE8] text-xs">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-full bg-emerald-50 text-[#14382A] flex items-center justify-center shrink-0 border border-emerald-200 font-bold">
            ✓
          </div>
          <div>
            <h5 class="text-[#111111] font-bold">১০০% প্রিমিয়াম সুতি ও ফেব্রিকেশন</h5>
            <p class="text-[#69736E] text-[11px]">উন্নত সুতা ও নিখুঁত কারিগরি ফিনিশিং।</p>
          </div>
        </div>

        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-full bg-emerald-50 text-[#14382A] flex items-center justify-center shrink-0 border border-emerald-200 font-bold">
            🚚
          </div>
          <div>
            <h5 class="text-[#111111] font-bold">সারাদেশে দ্রুত হোম ডেলিভারি</h5>
            <p class="text-[#69736E] text-[11px]">ঢাকা ও সারাদেশে ২-৪ দিনে নির্ভরযোগ্য ডেলিভারি।</p>
          </div>
        </div>

        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-full bg-emerald-50 text-[#14382A] flex items-center justify-center shrink-0 border border-emerald-200 font-bold">
            🔄
          </div>
          <div>
            <h5 class="text-[#111111] font-bold">৭ দিনের সহজ সাইজ পরিবর্তন</h5>
            <p class="text-[#69736E] text-[11px]">কোনো সমস্যা হলে দ্রুত সাইজ পরিবর্তনের সুবিধা।</p>
          </div>
        </div>
      </div>

    </div>
  </section>
</template>

<script setup>
import { ref, computed, reactive } from 'vue';
import { useCartStore } from '../../stores/cart';
import { useToastStore } from '../../stores/toast';

const props = defineProps({
  content: {
    type: Object,
    default: () => ({}),
  },
});

const cartStore = useCartStore();
const toast = useToastStore();

const currentLookIndex = ref(0);
const activeGarmentIndex = ref(0);
const isAdding = reactive({});
const addedGarments = reactive({});
const isAddingAll = ref(false);

const selectedSizes = reactive({
  10: 'M',
  11: 'L',
  12: 'L',
  16: '32',
  18: '42',
  19: 'L',
  22: 'M',
  26: 'One Size',
});

// 4 Curated Luxury Looks mapping directly to real product assets
const defaultLooks = [
  {
    id: 'look-01',
    name: 'আরবান ড্রেপ লুক',
    tagline: 'আরামদায়ক ক্যাজুয়াল স্টাইল ও আধুনিক কাট',
    image: '/storage/uploads/chic-burgundy-wool-coat-wide-leg-pants-fall-outfit-idea-1794824526-m3WQ.jpg',
    garments: [
      {
        id: 12,
        name: 'ড্রপ শোল্ডার ওভারসাইজড টি-শার্ট',
        slug: 'drop-shoulder-oversized-t-shirt',
        category: 'ড্রপ শোল্ডার',
        price: 490.00,
        image: '/storage/uploads/drop-shoulder-price-490size-mlxl1-1794823766-fpGc.jpg',
        fabric: '২৪০ জিএসএম কম্বড কটন • রিবড কলার',
        sizes: ['M', 'L', 'XL'],
        hotspot: { top: '30%', left: '50%' },
      },
      {
        id: 10,
        name: 'মেনস ব্যাগি ফিট ক্যাজুয়াল প্যান্ট',
        slug: 'men-baggy-fit-casual-pant',
        category: 'ব্যাগি প্যান্ট',
        price: 650.00,
        image: '/storage/uploads/baggy-pant-01-1794823750-TG8Q.webp',
        fabric: 'হেভিওয়েট কটন টুইল • ওয়াইড লেগ ফিট',
        sizes: ['M', 'L', 'XL'],
        hotspot: { top: '68%', left: '48%' },
      },
      {
        id: 26,
        name: 'লেদার স্লিম বাইফোল্ড ওয়ালেট',
        slug: 'genuine-leather-slim-bifold-wallet',
        category: 'এক্সেসরিজ',
        price: 499.00,
        image: '/storage/uploads/wallet-price-4991-1794823989-t65J.jpg',
        fabric: 'ফুল গ্রেইন অয়েল পুল-আপ জেনুইন লেদার',
        sizes: ['One Size'],
        hotspot: { top: '50%', left: '30%' },
      },
    ],
  },
  {
    id: 'look-02',
    name: 'হেরিটেজ পাঞ্জাবি',
    tagline: 'ঐতিহ্যবাহী এক্সক্লুসিভ পাঞ্জাবি ও সুতি প্যান্ট',
    image: '/storage/uploads/forest-green-coat-outfit-with-hijab-chic-black-and-green-style-1794824526-6fuq.jpg',
    garments: [
      {
        id: 18,
        name: 'এক্সক্লুসিভ হেরিটেজ পাঞ্জাবি কালেকশন',
        slug: 'exclusive-heritage-panjabi-collection',
        category: 'পাঞ্জাবি',
        price: 1499.00,
        image: '/storage/uploads/price-1499size-4042441-1794823926-a9P0.jpg',
        fabric: 'ফাইন জ্যাকার্ড ভিসকস • মেটালিক থ্রেড কলার',
        sizes: ['40', '42', '44'],
        hotspot: { top: '32%', left: '48%' },
      },
      {
        id: 16,
        name: 'প্রিমিয়াম ডেনিম ও চিনো প্যান্ট',
        slug: 'premium-denim-chino-pants',
        category: 'জিন্স প্যান্ট',
        price: 950.00,
        image: '/storage/uploads/price-950size-2829303132333435-361-1794823890-5RIh.jpg',
        fabric: '১২ আউন্স স্ট্রেচ ডেনিম • স্ট্রেইট লেগ কাট',
        sizes: ['30', '32', '34', '36'],
        hotspot: { top: '70%', left: '50%' },
      },
    ],
  },
  {
    id: 'look-03',
    name: 'মডার্ন বক্সি কাট',
    tagline: 'স্ট্রাকচার্ড বক্সি টি-শার্ট ও স্পোর্টস ট্রাউজার্স',
    image: '/storage/uploads/forest-green-old-money-winter-outfit-elegant-quiet-luxury-style-1794824526-wXXW.jpg',
    garments: [
      {
        id: 11,
        name: 'বক্সি ফিট হেভিওয়েট কটন টি-শার্ট',
        slug: 'boxy-fit-heavyweight-cotton-t-shirt',
        category: 'বক্সি টি-শার্ট',
        price: 790.00,
        image: '/storage/uploads/boxy-fit-price-790-size-mlxl1-1794823757-keB9.jpg',
        fabric: '২৬০ জিএসএম ডেনস জার্সি কটন • বক্সি সিলুয়েট',
        sizes: ['M', 'L', 'XL'],
        hotspot: { top: '32%', left: '50%' },
      },
      {
        id: 22,
        name: 'অ্যাথলেটিক পারফরম্যান্স ট্রাউজার',
        slug: 'adidas-athletic-performance-trouser',
        category: 'ট্রাউজার',
        price: 590.00,
        image: '/storage/uploads/adidas-trouser-price-5901-1794823967-jx2l.jpg',
        fabric: 'ফোর-ওয়ে স্ট্রেচ ব্রিদেবল নিট ফেব্রিক',
        sizes: ['M', 'L', 'XL'],
        hotspot: { top: '70%', left: '48%' },
      },
    ],
  },
  {
    id: 'look-04',
    name: 'উইন্টার ক্লাসিক কোট',
    tagline: 'শীতের আভিজাত্য ও মার্জিত আউটফিট সিলুয়েট',
    image: '/storage/uploads/stylish-womens-winter-long-coat-elegant-burgundy-wool-coat-outfit-1794824526-dKUI.jpg',
    garments: [
      {
        id: 19,
        name: 'ক্লাসিক পিকে পোলো টি-শার্ট',
        slug: 'classic-pique-polo-t-shirt',
        category: 'পোলো টি-শার্ট',
        price: 650.00,
        image: '/storage/uploads/polo-t-shirt-price-650size-mlxl1-1794823936-Kham.jpg',
        fabric: 'মার্সারাইজড কটন পিকে • স্ট্রাকচার্ড রিবড কাফ',
        sizes: ['M', 'L', 'XL'],
        hotspot: { top: '34%', left: '48%' },
      },
      {
        id: 10,
        name: 'মেনস ব্যাগি ফিট ক্যাজুয়াল প্যান্ট',
        slug: 'men-baggy-fit-casual-pant',
        category: 'ব্যাগি প্যান্ট',
        price: 650.00,
        image: '/storage/uploads/baggy-pant-01-1794823750-TG8Q.webp',
        fabric: 'হেভিওয়েট কটন টুইল • ওয়াইড লেগ ফিট',
        sizes: ['M', 'L', 'XL'],
        hotspot: { top: '70%', left: '50%' },
      },
    ],
  },
];

const activeLooks = computed(() => {
  if (props.content?.looks && Array.isArray(props.content.looks) && props.content.looks.length > 0) {
    return props.content.looks;
  }
  return defaultLooks;
});

const currentLook = computed(() => {
  return activeLooks.value[currentLookIndex.value] || activeLooks.value[0];
});

const currentLookTotal = computed(() => {
  if (!currentLook.value?.garments) return 0;
  return currentLook.value.garments.reduce((sum, g) => sum + (parseFloat(g.price) || 0), 0);
});

const selectLook = (index) => {
  currentLookIndex.value = index;
  activeGarmentIndex.value = 0;
};

const nextLook = () => {
  currentLookIndex.value = (currentLookIndex.value + 1) % activeLooks.value.length;
  activeGarmentIndex.value = 0;
};

const prevLook = () => {
  currentLookIndex.value = (currentLookIndex.value - 1 + activeLooks.value.length) % activeLooks.value.length;
  activeGarmentIndex.value = 0;
};

const scrollToGarment = (index) => {
  activeGarmentIndex.value = index;
  const el = document.getElementById(`garment-card-${index}`);
  if (el) {
    el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }
};

const formatPrice = (val) => {
  return Number(val || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const quickAddToCart = async (garment) => {
  const size = selectedSizes[garment.id] || (garment.sizes ? garment.sizes[0] : null);
  isAdding[garment.id] = true;
  try {
    await cartStore.addToCart(garment.id, null, 1, null, size);
    addedGarments[garment.id] = true;
    toast.success(`"${garment.name}" (সাইজ: ${size || 'স্ট্যান্ডার্ড'}) ব্যাগে যোগ করা হয়েছে!`);
    setTimeout(() => {
      addedGarments[garment.id] = false;
    }, 2500);
  } catch (err) {
    console.error('Failed to add lookbook garment to cart', err);
    toast.error('ব্যাগে যোগ করা সম্ভব হয়নি। আবার চেষ্টা করুন।');
  } finally {
    isAdding[garment.id] = false;
  }
};

const addEntireLookToBag = async () => {
  if (!currentLook.value?.garments || currentLook.value.garments.length === 0) return;
  isAddingAll.value = true;
  try {
    for (const g of currentLook.value.garments) {
      const size = selectedSizes[g.id] || (g.sizes ? g.sizes[0] : null);
      await cartStore.addToCart(g.id, null, 1, null, size);
    }
    toast.success(`সম্পূর্ণ ${currentLook.value.name} এনসেম্বল সফলভাবে ব্যাগে যোগ করা হয়েছে!`);
  } catch (err) {
    console.error('Failed to add complete ensemble', err);
    toast.error('সম্পূর্ণ পোশাক সেট ব্যাগে যোগ করা সম্ভব হয়নি।');
  } finally {
    isAddingAll.value = false;
  }
};
</script>

<style scoped>
.look-crossfade-enter-active,
.look-crossfade-leave-active {
  transition: opacity 0.4s ease-in-out;
}
.look-crossfade-enter-from,
.look-crossfade-leave-to {
  opacity: 0;
}

/* Hide scrollbar for Chrome, Safari and Opera */
.scrollbar-none::-webkit-scrollbar {
  display: none;
}
/* Hide scrollbar for IE, Edge and Firefox */
.scrollbar-none {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
