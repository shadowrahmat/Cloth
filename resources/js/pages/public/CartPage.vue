<template>
  <div class="py-12 sm:py-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-10 border-b border-[#D8CCBC]/40 pb-6 flex items-baseline justify-between">
      <h1 class="font-serif text-3xl sm:text-4xl text-[#111111]">Your Shopping Bag</h1>
      <router-link to="/collection/winter" class="text-xs uppercase tracking-wider text-[#A89B8A] hover:text-[#111111] transition-colors">
        Continue Shopping →
      </router-link>
    </div>

    <div v-if="cartStore.items && cartStore.items.length" class="grid grid-cols-1 lg:grid-cols-12 gap-12">
      <!-- Items Table -->
      <div class="lg:col-span-8 divide-y divide-[#D8CCBC]/40">
        <div
          v-for="item in cartStore.items"
          :key="item.id"
          class="py-6 first:pt-0 flex gap-6"
        >
          <router-link :to="`/product/${item.slug}`" class="w-24 h-32 bg-white overflow-hidden shrink-0 border border-[#D8CCBC]/40">
            <img :src="item.image" :alt="item.name" class="w-full h-full object-cover" />
          </router-link>

          <div class="flex-1 flex flex-col justify-between">
            <div class="flex justify-between items-start">
              <div>
                <router-link :to="`/product/${item.slug}`" class="font-serif text-lg text-[#111111] hover:text-[#A89B8A] transition-colors">
                  {{ item.name }}
                </router-link>
                <p class="text-xs text-[#A89B8A] mt-1 space-x-3">
                  <span v-if="item.color">Color: {{ item.color }}</span>
                  <span v-if="item.size">Size: {{ item.size }}</span>
                  <span>SKU: {{ item.sku }}</span>
                </p>
              </div>

              <button
                type="button"
                @click="cartStore.removeItem(item.id)"
                class="text-xs text-[#A89B8A] hover:text-rose-600 transition-colors uppercase tracking-wider"
              >
                Remove
              </button>
            </div>

            <div class="flex items-center justify-between pt-4">
              <!-- Quantity buttons -->
              <div class="inline-flex items-center border border-[#D8CCBC] bg-white text-xs">
                <button
                  type="button"
                  @click="cartStore.updateQuantity(item.id, item.quantity - 1)"
                  class="px-3 py-1.5 hover:bg-[#F7F5F0]"
                >
                  -
                </button>
                <span class="px-4 py-1.5 font-medium">{{ item.quantity }}</span>
                <button
                  type="button"
                  @click="cartStore.updateQuantity(item.id, item.quantity + 1)"
                  class="px-3 py-1.5 hover:bg-[#F7F5F0]"
                >
                  +
                </button>
              </div>

              <div class="text-right">
                <span class="text-sm font-semibold text-[#111111]">${{ (item.price * item.quantity).toFixed(2) }}</span>
                <span v-if="item.quantity > 1" class="text-[11px] text-[#A89B8A] block">(${{ item.price }} each)</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Order Summary Card -->
      <div class="lg:col-span-4 bg-white p-6 sm:p-8 border border-[#D8CCBC]/40 shadow-xs space-y-6">
        <h3 class="font-serif text-xl text-[#111111] border-b border-[#D8CCBC]/40 pb-4">Order Summary</h3>

        <!-- Subtotal breakdown -->
        <div class="space-y-3 text-xs">
          <div class="flex justify-between text-[#111111]">
            <span>Bag Subtotal</span>
            <span class="font-semibold">${{ cartStore.subtotal.toFixed(2) }}</span>
          </div>

          <div v-if="cartStore.appliedCoupon" class="flex justify-between text-emerald-700">
            <span>Coupon ({{ cartStore.appliedCoupon.code }})</span>
            <span>-${{ cartStore.appliedCoupon.discount_amount.toFixed(2) }}</span>
          </div>

          <div class="flex justify-between text-[#111111]">
            <span>Worldwide Shipping</span>
            <span class="font-semibold text-emerald-800">
              {{ cartStore.subtotal >= 150 ? 'Complimentary' : 'Calculated at checkout' }}
            </span>
          </div>
        </div>

        <!-- Coupon Input -->
        <div class="pt-4 border-t border-[#D8CCBC]/40 space-y-2">
          <label class="block text-xs uppercase tracking-wider font-semibold text-[#111111]">Promo Code</label>
          <div class="flex gap-2">
            <input
              type="text"
              v-model="couponCode"
              placeholder="e.g. WELCOME10"
              class="w-full px-3 py-2 bg-[#F7F5F0] border border-[#D8CCBC] text-xs uppercase focus:outline-none focus:border-[#111111]"
            />
            <button
              type="button"
              @click="applyCoupon"
              class="px-4 py-2 bg-[#111111] text-white text-xs uppercase tracking-wider font-semibold hover:bg-black transition-colors shrink-0"
            >
              Apply
            </button>
          </div>
          <p v-if="couponError" class="text-xs text-rose-600">{{ couponError }}</p>
          <p v-if="cartStore.appliedCoupon" class="text-xs text-emerald-700 font-medium">Coupon applied successfully!</p>
        </div>

        <!-- Final Total -->
        <div class="pt-4 border-t border-[#D8CCBC]/40 flex justify-between items-baseline">
          <span class="font-serif text-lg text-[#111111]">Estimated Total</span>
          <span class="font-serif text-2xl font-semibold text-[#111111]">${{ cartStore.finalTotal.toFixed(2) }}</span>
        </div>

        <!-- Checkout Action -->
        <router-link
          to="/checkout"
          class="w-full block text-center py-4 bg-[#111111] hover:bg-black text-white text-xs uppercase tracking-[0.2em] font-semibold transition-colors shadow-md"
        >
          Proceed to Checkout
        </router-link>

        <p class="text-[11px] text-center text-[#A89B8A]">
          🔒 Secure 256-bit encrypted checkout with white glove returns.
        </p>
      </div>
    </div>

    <!-- Empty Bag -->
    <div v-else class="text-center py-24 max-w-md mx-auto">
      <svg class="w-16 h-16 text-[#D8CCBC] mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
      </svg>
      <h3 class="font-serif text-2xl text-[#111111] mb-2">Your shopping bag is empty</h3>
      <p class="text-xs text-[#A89B8A] mb-8 leading-relaxed">
        Explore our curated collection of architectural tailoring, Italian knitwear, and seasonal silhouettes.
      </p>
      <router-link
        to="/collection/winter"
        class="px-8 py-3.5 bg-[#111111] text-white text-xs uppercase tracking-[0.2em] font-semibold hover:bg-black transition-colors inline-block"
      >
        Explore Collection
      </router-link>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useCartStore } from '../../stores/cart';

const cartStore = useCartStore();
const couponCode = ref('');
const couponError = ref('');

const applyCoupon = async () => {
  if (!couponCode.value) return;
  couponError.value = '';
  try {
    await cartStore.applyCoupon(couponCode.value.trim());
    couponCode.value = '';
  } catch (err) {
    couponError.value = err.response?.data?.message || 'Invalid or expired coupon';
  }
};
</script>
