<template>
  <div class="py-24 sm:py-32 max-w-xl mx-auto px-4 text-center">
    <p class="font-serif text-7xl sm:text-8xl text-[#D8CCBC] mb-6">404</p>
    <h1 class="font-serif text-2xl sm:text-3xl text-[#111111] mb-3">পেজটি খুঁজে পাওয়া যায়নি</h1>
    <p class="text-xs text-[#A89B8A] leading-relaxed mb-10">
      আপনি যে পেজটি খুঁজছেন সেটি সরিয়ে নেওয়া হয়েছে অথবা লিংকটি সঠিক নয়।
    </p>
    <div class="flex flex-col sm:flex-row gap-3 justify-center">
      <router-link
        to="/"
        class="px-8 py-3.5 bg-[#111111] text-white text-xs uppercase tracking-[0.2em] font-semibold hover:bg-black transition-colors"
      >
        হোমপেজে ফিরে যান
      </router-link>
      <router-link
        to="/collections"
        class="px-8 py-3.5 border border-[#111111] text-[#111111] text-xs uppercase tracking-[0.2em] font-semibold hover:bg-[#111111] hover:text-white transition-colors"
      >
        কালেকশন দেখুন
      </router-link>
    </div>
  </div>
</template>
