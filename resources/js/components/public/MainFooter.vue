<template>
  <footer class="bg-[#111111] text-white pt-16 pb-12 border-t border-white/10 mt-auto">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-10 pb-12 border-b border-white/10">
        <!-- Col 1: Brand -->
        <div class="lg:col-span-2 space-y-4">
          <router-link to="/" class="inline-block">
            <img
              src="/images/logo-white.png"
              alt="DRAWER CLOTHING"
              class="h-12 sm:h-14 w-auto max-w-[200px] object-contain brightness-110"
            />
          </router-link>
          <p class="text-xs text-white/60 font-light leading-relaxed max-w-sm">
            সমসাময়িক লাইফস্টাইল, আধুনিক মার্জিত টেইলারিং এবং দীর্ঘস্থায়ী আরামের উদ্দেশ্যে তৈরি প্রিমিয়াম পোশাক।
          </p>
          <div class="text-xs text-white/40 space-y-1">
            <p>গুলশান অ্যাভিনিউ, ঢাকা, বাংলাদেশ</p>
            <p>গ্রাহক সেবা: support@drawerclothing.com</p>
          </div>
        </div>

        <!-- Col 2: Client Concierge -->
        <div class="space-y-3">
          <h5 class="text-xs font-semibold uppercase tracking-[0.2em] text-[#D8CCBC]">গ্রাহক সহায়তা</h5>
          <ul class="space-y-2 text-xs text-white/60">
            <li><router-link to="/contact" class="hover:text-white transition-colors">যোগাযোগ করুন</router-link></li>
            <li><router-link to="/track-order" class="hover:text-white transition-colors">অর্ডার ট্র্যাক করুন</router-link></li>
            <li><router-link to="/contact" class="hover:text-white transition-colors">শিপিং ও রিটার্ন পলিসি</router-link></li>
            <li><router-link to="/collections" class="hover:text-white transition-colors">সাইজ গাইড</router-link></li>
            <li><router-link to="/account" class="hover:text-white transition-colors">গ্রাহক অ্যাকাউন্ট</router-link></li>
          </ul>
        </div>

        <!-- Col 3: Departments -->
        <div class="space-y-3">
          <h5 class="text-xs font-semibold uppercase tracking-[0.2em] text-[#D8CCBC]">ক্যাটাগরি সমূহ</h5>
          <ul class="space-y-2 text-xs text-white/60">
            <li><router-link to="/category/panjabi" class="hover:text-white transition-colors font-medium text-[#D8CCBC]">পাঞ্জাবি কালেকশন</router-link></li>
            <li><router-link to="/category/drop-shoulder" class="hover:text-white transition-colors">ড্রপ শোল্ডার টি-শার্ট</router-link></li>
            <li><router-link to="/category/boxy" class="hover:text-white transition-colors">বক্সি ফিট টি-শার্ট</router-link></li>
            <li><router-link to="/category/baggy-pants" class="hover:text-white transition-colors">ব্যাগী প্যান্ট ও ট্রাউজার</router-link></li>
            <li><router-link to="/collection/new-arrivals" class="hover:text-white transition-colors">নতুন সিজন রিলিজ</router-link></li>
          </ul>
        </div>

        <!-- Col 4: The House -->
        <div class="space-y-3">
          <h5 class="text-xs font-semibold uppercase tracking-[0.2em] text-[#D8CCBC]">আমাদের পরিচয়</h5>
          <ul class="space-y-2 text-xs text-white/60">
            <li><router-link to="/about" class="hover:text-white transition-colors">আমাদের গল্প ও দর্শন</router-link></li>
            <li><router-link to="/about" class="hover:text-white transition-colors">ফেব্রিক ও কারিগরি মান</router-link></li>
            <li><router-link to="/collections" class="hover:text-white transition-colors">সব প্রোডাক্ট ক্যাটালগ</router-link></li>
            <li><router-link to="/admin" class="hover:text-[#D8CCBC] transition-colors text-white/40 flex items-center gap-1">অ্যাডমিন প্যানেল <span>⚙</span></router-link></li>
          </ul>
        </div>
      </div>

      <!-- Bottom Bar -->
      <div class="pt-8 flex flex-col sm:flex-row items-center justify-between text-[11px] text-white/40 gap-4">
        <p>© {{ new Date().getFullYear() }} DRAWER CLOTHING. সর্বস্বত্ব সংরক্ষিত।</p>

        <div class="flex items-center space-x-4">
          <span>বিকাশ (bKash)</span>
          <span>নগদ (Nagad)</span>
          <span>ভিসা / মাস্টারকার্ড</span>
          <span>ক্যাশ অন ডেলিভারি (COD)</span>
        </div>
      </div>
    </div>
  </footer>
</template>
