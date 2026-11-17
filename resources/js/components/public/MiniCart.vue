<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="opacity-0 translate-x-full"
      enter-to-class="opacity-100 translate-x-0"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="opacity-100 translate-x-0"
      leave-to-class="opacity-0 translate-x-full"
    >
      <div
        v-if="cartStore.isDrawerOpen"
        class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex justify-end"
        @click.self="cartStore.closeDrawer()"
      >
        <div class="w-full max-w-md bg-[#F7F5F0] h-full flex flex-col shadow-2xl">
          <!-- Drawer Header -->
          <div class="p-6 bg-white border-b border-[#D8CCBC]/40 flex items-center justify-between">
            <div class="flex items-baseline gap-2">
              <h3 class="font-serif text-xl text-[#111111]">শপিং ব্যাগ</h3>
              <span class="text-xs text-[#A89B8A]">({{ cartStore.itemCount }} টি পণ্য)</span>
            </div>
            <button
              type="button"
              @click="cartStore.closeDrawer()"
              class="p-2 text-[#A89B8A] hover:text-[#111111] transition-colors"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>

          <!-- Free Shipping Progress -->
          <div class="px-6 py-3 bg-[#EAE5DC] text-[11px] text-[#111111] border-b border-[#D8CCBC]/30">
            <template v-if="cartStore.subtotal >= 2500">
              <div class="flex items-center gap-1.5 text-emerald-800 font-medium">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span>অভিনন্দন! আপনি ফ্রি ডেলিভারি সুবিধা উপভোগ করতে পারবেন।</span>
              </div>
            </template>
            <template v-else>
              <div class="flex justify-between mb-1.5 font-medium">
                <span>২৫০০ টাকার বেশি অর্ডারে ফ্রি ডেলিভারি</span>
                <span>আর ৳{{ Math.max(0, 2500 - cartStore.subtotal).toFixed(0) }} যোগ করুন</span>
              </div>
              <div class="w-full h-1 bg-white/70 rounded-full overflow-hidden">
                <div
                  class="h-full bg-[#111111] transition-all duration-300"
                  :style="{ width: `${Math.min(100, (cartStore.subtotal / 2500) * 100)}%` }"
                />
              </div>
            </template>
          </div>

          <!-- Bag Items List -->
          <div data-lenis-prevent class="flex-1 overflow-y-auto p-6 divide-y divide-[#D8CCBC]/40">
            <template v-if="cartStore.items && cartStore.items.length">
              <div
                v-for="item in cartStore.items"
                :key="item.id"
                class="py-4 first:pt-0 last:pb-0 flex gap-4"
              >
                <!-- Thumbnail -->
                <router-link
                  :to="`/product/${item.slug}`"
                  @click="cartStore.closeDrawer()"
                  class="w-20 h-26 bg-white overflow-hidden shrink-0 border border-[#D8CCBC]/30"
                >
                  <img :src="item.image" :alt="item.name" class="w-full h-full object-cover" />
                </router-link>

                <!-- Item Details -->
                <div class="flex-1 min-w-0 flex flex-col justify-between">
                  <div>
                    <div class="flex justify-between items-start gap-2">
                      <router-link
                        :to="`/product/${item.slug}`"
                        @click="cartStore.closeDrawer()"
                        class="font-serif text-sm text-[#111111] hover:text-[#A89B8A] transition-colors truncate block"
                      >
                        {{ item.name }}
                      </router-link>
                      <button
                        type="button"
                        @click="cartStore.removeItem(item.id)"
                        class="text-xs text-[#A89B8A] hover:text-[#111111] transition-colors"
                        title="Remove"
                      >
                        ×
                      </button>
                    </div>

                    <div class="text-[11px] text-[#A89B8A] mt-1 space-x-2">
                      <span v-if="item.color">রঙ: {{ item.color }}</span>
                      <span v-if="item.size">সাইজ: {{ item.size }}</span>
                    </div>
                  </div>

                  <!-- Price & Quantity -->
                  <div class="flex items-center justify-between mt-3">
                    <div class="inline-flex items-center border border-[#D8CCBC] bg-white text-xs">
                      <button
                        type="button"
                        @click="cartStore.updateQuantity(item.id, item.quantity - 1)"
                        class="px-2 py-1 hover:bg-[#F7F5F0] text-[#111111]"
                      >
                        -
                      </button>
                      <span class="px-2.5 py-1 text-center font-medium">{{ item.quantity }}</span>
                      <button
                        type="button"
                        @click="cartStore.updateQuantity(item.id, item.quantity + 1)"
                        class="px-2 py-1 hover:bg-[#F7F5F0] text-[#111111]"
                      >
                        +
                      </button>
                    </div>

                    <span class="text-xs font-semibold text-[#111111]">
                      ৳ {{ Number(item.price * item.quantity).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}
                    </span>
                  </div>
                </div>
              </div>
            </template>

            <!-- Empty Bag State -->
            <div v-else class="h-full flex flex-col items-center justify-center text-center py-16">
              <svg class="w-12 h-12 text-[#D8CCBC] mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
              </svg>
              <h5 class="font-serif text-lg text-[#111111] mb-1">আপনার শপিং ব্যাগটি খালি</h5>
              <p class="text-xs text-[#A89B8A] mb-6">আমাদের প্রিমিয়াম কালেকশন থেকে আপনার পছন্দের পোশাক যুক্ত করুন।</p>
              <router-link
                to="/clothing"
                @click="cartStore.closeDrawer()"
                class="px-6 py-2.5 bg-[#111111] text-white text-xs uppercase tracking-widest font-medium hover:bg-black/90 transition-colors"
              >
                পোশাক কালেকশন দেখুন
              </router-link>
            </div>
          </div>

          <!-- Drawer Footer -->
          <div v-if="cartStore.items && cartStore.items.length" class="p-6 bg-white border-t border-[#D8CCBC]/40 space-y-4">
            <!-- Subtotal -->
            <div class="flex justify-between items-baseline">
              <span class="text-xs uppercase tracking-wider text-[#A89B8A] font-medium">সাবটোটাল</span>
              <span class="font-serif text-xl font-bold text-[#111111]">৳ {{ Number(cartStore.subtotal).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}</span>
            </div>
            <p class="text-[11px] text-[#A89B8A]">ডেলিভারি চার্জ চেকআউটের সময় যোগ করা হবে।</p>

            <!-- Action Buttons -->
            <div class="grid grid-cols-2 gap-3 pt-2">
              <router-link
                to="/cart"
                @click="cartStore.closeDrawer()"
                class="w-full text-center py-3.5 border border-[#111111] text-xs uppercase tracking-widest font-semibold hover:bg-[#111111] hover:text-white transition-colors"
              >
                ব্যাগ দেখুন
              </router-link>
              <router-link
                to="/checkout"
                @click="cartStore.closeDrawer()"
                class="w-full text-center py-3.5 bg-[#111111] text-white text-xs uppercase tracking-widest font-semibold hover:bg-black/90 transition-colors shadow-sm"
              >
                চেকআউট
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { useCartStore } from '../../stores/cart';

const cartStore = useCartStore();
</script>
