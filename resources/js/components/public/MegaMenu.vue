<template>
  <div
    class="absolute top-full left-0 w-full bg-white border-b border-[#D8CCBC]/50 shadow-2xl transition-all duration-300 py-10 px-8 z-50"
    @mouseenter="$emit('mouseenter')"
    @mouseleave="$emit('mouseleave')"
  >
    <div class="max-w-7xl mx-auto grid grid-cols-12 gap-8">
      <!-- Group 1: Tees & Casuals (5 Categories) -->
      <div class="col-span-2 space-y-4">
        <h4 class="font-serif text-sm tracking-wider uppercase text-[#14382A] font-bold border-b border-[#D8CCBC]/40 pb-2.5">
          টি-শার্ট ও স্ট্রিটওয়্যার
        </h4>
        <ul class="space-y-2.5 text-sm text-[#171C1A]/85">
          <li>
            <router-link to="/category/t-shirt" class="hover:text-[#16A36A] hover:translate-x-1 inline-block transition-all duration-150" @click="$emit('close')">
              টি-শার্ট (T-shirt)
            </router-link>
          </li>
          <li>
            <router-link to="/category/polo-t-shirt" class="hover:text-[#16A36A] hover:translate-x-1 inline-block transition-all duration-150" @click="$emit('close')">
              পোলো টি-শার্ট (Polo)
            </router-link>
          </li>
          <li>
            <router-link to="/category/drop-shoulder" class="hover:text-[#16A36A] hover:translate-x-1 inline-block transition-all duration-150" @click="$emit('close')">
              ড্রপ শোল্ডার (Drop shoulder)
            </router-link>
          </li>
          <li>
            <router-link to="/category/boxy" class="hover:text-[#16A36A] hover:translate-x-1 inline-block transition-all duration-150 font-semibold text-[#14382A]" @click="$emit('close')">
              বক্সি ফিট (BOXY)
            </router-link>
          </li>
          <li>
            <router-link to="/category/sleeveless-t-shirt" class="hover:text-[#16A36A] hover:translate-x-1 inline-block transition-all duration-150" @click="$emit('close')">
              স্লিভলেস টি-শার্ট
            </router-link>
          </li>
        </ul>
      </div>

      <!-- Group 2: Shirts & Panjabi (4 Categories) -->
      <div class="col-span-2 space-y-4">
        <h4 class="font-serif text-sm tracking-wider uppercase text-[#14382A] font-bold border-b border-[#D8CCBC]/40 pb-2.5">
          শার্ট ও পাঞ্জাবি
        </h4>
        <ul class="space-y-2.5 text-sm text-[#171C1A]/85">
          <li>
            <router-link to="/category/panjabi" class="hover:text-[#16A36A] hover:translate-x-1 inline-block transition-all duration-150 font-semibold text-[#14382A]" @click="$emit('close')">
              পাঞ্জাবি (Panjabi)
            </router-link>
          </li>
          <li>
            <router-link to="/category/casual-shirt" class="hover:text-[#16A36A] hover:translate-x-1 inline-block transition-all duration-150" @click="$emit('close')">
              ক্যাজুয়াল শার্ট
            </router-link>
          </li>
          <li>
            <router-link to="/category/short-sleeve-shirt" class="hover:text-[#16A36A] hover:translate-x-1 inline-block transition-all duration-150" @click="$emit('close')">
              হাফ হাতা শার্ট
            </router-link>
          </li>
          <li>
            <router-link to="/category/full-sleeves-shirt" class="hover:text-[#16A36A] hover:translate-x-1 inline-block transition-all duration-150" @click="$emit('close')">
              ফুল হাতা শার্ট
            </router-link>
          </li>
        </ul>
      </div>

      <!-- Group 3: Pants & Bottoms (4 Categories) -->
      <div class="col-span-2 space-y-4">
        <h4 class="font-serif text-sm tracking-wider uppercase text-[#14382A] font-bold border-b border-[#D8CCBC]/40 pb-2.5">
          প্যান্ট ও ট্রাউজার
        </h4>
        <ul class="space-y-2.5 text-sm text-[#171C1A]/85">
          <li>
            <router-link to="/category/trouser" class="hover:text-[#16A36A] hover:translate-x-1 inline-block transition-all duration-150" @click="$emit('close')">
              ট্রাউজার (Trouser)
            </router-link>
          </li>
          <li>
            <router-link to="/category/jeans-pant" class="hover:text-[#16A36A] hover:translate-x-1 inline-block transition-all duration-150" @click="$emit('close')">
              জিন্স প্যান্ট (Jeans)
            </router-link>
          </li>
          <li>
            <router-link to="/category/baggy-pants" class="hover:text-[#16A36A] hover:translate-x-1 inline-block transition-all duration-150" @click="$emit('close')">
              ব্যাগী প্যান্ট (Baggy)
            </router-link>
          </li>
          <li>
            <router-link to="/category/2-quarter-pants" class="hover:text-[#16A36A] hover:translate-x-1 inline-block transition-all duration-150" @click="$emit('close')">
              ২ কোয়ার্টার প্যান্ট
            </router-link>
          </li>
        </ul>
      </div>

      <!-- Group 4: Ladies Collection (3 Categories) -->
      <div class="col-span-2 space-y-4">
        <h4 class="font-serif text-sm tracking-wider uppercase text-[#14382A] font-bold border-b border-[#D8CCBC]/40 pb-2.5">
          লেডিস কালেকশন
        </h4>
        <ul class="space-y-2.5 text-sm text-[#171C1A]/85">
          <li>
            <router-link to="/category/ladies-tops" class="hover:text-[#16A36A] hover:translate-x-1 inline-block transition-all duration-150" @click="$emit('close')">
              লেডিস টপস (Tops)
            </router-link>
          </li>
          <li>
            <router-link to="/category/ladies-pants" class="hover:text-[#16A36A] hover:translate-x-1 inline-block transition-all duration-150" @click="$emit('close')">
              লেডিস প্যান্ট (Pants)
            </router-link>
          </li>
          <li>
            <router-link to="/category/ladies-bell-bottom-pants" class="hover:text-[#16A36A] hover:translate-x-1 inline-block transition-all duration-150" @click="$emit('close')">
              বেল-বটম প্যান্ট
            </router-link>
          </li>
        </ul>
      </div>

      <!-- Group 5: Brand Showcase & All Products Link -->
      <div class="col-span-4 pl-6 border-l border-[#D8CCBC]/30 flex flex-col justify-between">
        <div class="p-6 bg-[#F7F5F0] border border-[#D8CCBC]/50 rounded-xs space-y-3">
          <img src="/images/logo-transparent.png" alt="DRAWER CLOTHING" class="h-10 w-auto object-contain" />
          <h5 class="font-serif text-base text-[#111111] font-semibold">১৬টি কিউরেটেড ফ্যাশন ক্যাটাগরি</h5>
          <p class="text-xs text-[#69736E] leading-relaxed">
            উৎসবের ঐতিহ্যবাহী পাঞ্জাবি, আধুনিক হেভিওয়েট ড্রপ শোল্ডার টি-শার্ট থেকে শুরু করে আরামদায়ক ট্রাউজার ও লেডিস কালেকশন।
          </p>
          <div class="pt-2">
            <router-link
              to="/collections"
              class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#14382A] text-white hover:bg-[#16A36A] text-xs font-semibold uppercase tracking-wider transition-colors shadow-xs"
              @click="$emit('close')"
            >
              <span>সকল পোশাক ব্রাউজ করুন</span>
              <span>→</span>
            </router-link>
          </div>
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
