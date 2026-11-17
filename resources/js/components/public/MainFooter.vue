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
            Contemporary silhouettes, refined tailoring, and modern wardrobe essentials designed for intentional longevity.
          </p>
          <div class="text-xs text-white/40 space-y-1">
            <p>Gulshan Avenue, Dhaka, Bangladesh</p>
            <p>Client Concierge: concierge@drawerclothing.com</p>
          </div>
        </div>

        <!-- Col 2: Client Concierge -->
        <div class="space-y-3">
          <h5 class="text-xs font-semibold uppercase tracking-[0.2em] text-[#D8CCBC]">Client Concierge</h5>
          <ul class="space-y-2 text-xs text-white/60">
            <li><router-link to="/pages/contact" class="hover:text-white transition-colors">Contact Advisor</router-link></li>
            <li><router-link to="/track-order" class="hover:text-white transition-colors">Track Shipment</router-link></li>
            <li><router-link to="/pages/shipping-returns" class="hover:text-white transition-colors">Shipping & Returns</router-link></li>
            <li><router-link to="/pages/size-guide" class="hover:text-white transition-colors">Tailoring Size Guide</router-link></li>
            <li><router-link to="/account" class="hover:text-white transition-colors">Client Account</router-link></li>
          </ul>
        </div>

        <!-- Col 3: Departments -->
        <div class="space-y-3">
          <h5 class="text-xs font-semibold uppercase tracking-[0.2em] text-[#D8CCBC]">Departments</h5>
          <ul class="space-y-2 text-xs text-white/60">
            <li><router-link to="/collection/winter" class="hover:text-white transition-colors font-medium text-[#D8CCBC]">The Winter Edit</router-link></li>
            <li><router-link to="/collection/women" class="hover:text-white transition-colors">Women's Collection</router-link></li>
            <li><router-link to="/collection/men" class="hover:text-white transition-colors">Men's Tailoring</router-link></li>
            <li><router-link to="/collection/kids" class="hover:text-white transition-colors">Children's Knits</router-link></li>
            <li><router-link to="/collection/new-arrivals" class="hover:text-white transition-colors">New Season Arrivals</router-link></li>
          </ul>
        </div>

        <!-- Col 4: The House -->
        <div class="space-y-3">
          <h5 class="text-xs font-semibold uppercase tracking-[0.2em] text-[#D8CCBC]">The House</h5>
          <ul class="space-y-2 text-xs text-white/60">
            <li><router-link to="/pages/about-us" class="hover:text-white transition-colors">The Atelier Story</router-link></li>
            <li><router-link to="/pages/shipping-returns" class="hover:text-white transition-colors">Artisan Mills</router-link></li>
            <li><router-link to="/pages/about-us" class="hover:text-white transition-colors">Sustainable Practice</router-link></li>
            <li><router-link to="/admin" class="hover:text-[#D8CCBC] transition-colors text-white/40 flex items-center gap-1">Admin Portal <span>⚙</span></router-link></li>
          </ul>
        </div>
      </div>

      <!-- Bottom Bar -->
      <div class="pt-8 flex flex-col sm:flex-row items-center justify-between text-[11px] text-white/40 gap-4">
        <p>© {{ new Date().getFullYear() }} DRAWER CLOTHING Inc. All rights reserved.</p>

        <div class="flex items-center space-x-4">
          <span>Visa</span>
          <span>Mastercard</span>
          <span>Amex</span>
          <span>Apple Pay</span>
          <span>Stripe</span>
          <span>Cash On Delivery</span>
        </div>
      </div>
    </div>
  </footer>
</template>
