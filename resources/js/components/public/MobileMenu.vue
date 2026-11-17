<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="opacity-0 -translate-x-full"
      enter-to-class="opacity-100 translate-x-0"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="opacity-100 translate-x-0"
      leave-to-class="opacity-0 -translate-x-full"
    >
      <div
        v-if="isOpen"
        class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex"
        @click.self="$emit('close')"
      >
        <div data-lenis-prevent class="w-full max-w-sm bg-[#F7F5F0] h-full flex flex-col shadow-2xl overflow-y-auto">
          <!-- Header -->
          <div class="p-6 flex items-center justify-between border-b border-[#D8CCBC]/40">
            <router-link to="/" @click="$emit('close')">
              <img src="/images/logo-transparent.png" alt="DRAWER CLOTHING" class="h-11 w-auto max-w-[160px] object-contain" />
            </router-link>
            <button
              type="button"
              @click="$emit('close')"
              class="p-2 text-[#111111] hover:text-[#A89B8A]"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>

          <!-- Mobile Navigation Links -->
          <div class="px-6 py-6 flex-1 space-y-2 overflow-y-auto">
            <!-- NEW ARRIVALS -->
            <router-link
              to="/collection/new-arrivals"
              class="block py-3 text-base font-medium text-[#111111] hover:text-[#16A36A] border-b border-[#D8CCBC]/30 transition-colors"
              @click="$emit('close')"
            >
              নতুন কালেকশন
            </router-link>

            <!-- COLLECTIONS -->
            <router-link
              to="/collections"
              class="block py-3 text-base font-medium text-[#111111] hover:text-[#16A36A] border-b border-[#D8CCBC]/30 transition-colors"
              @click="$emit('close')"
            >
              সব কালেকশন
            </router-link>

            <!-- CLOTHING Accordion (16 Categories) -->
            <div class="border-b border-[#D8CCBC]/30 py-2">
              <div class="flex items-center justify-between py-1">
                <router-link
                  to="/collections"
                  class="text-base font-medium text-[#111111] hover:text-[#16A36A] transition-colors"
                  @click="$emit('close')"
                >
                  পোশাক (ক্যাটাগরি)
                </router-link>
                <button
                  type="button"
                  @click="shopExpanded = !shopExpanded"
                  class="p-2 text-[#69736E] hover:text-[#16A36A]"
                  aria-label="ক্যাটাগরি খুলুন বা বন্ধ করুন"
                >
                  <svg
                    :class="['w-5 h-5 transition-transform duration-200', shopExpanded ? 'rotate-180 text-[#16A36A]' : '']"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                  </svg>
                </button>
              </div>

              <!-- Shop Subcategories List -->
              <div v-if="shopExpanded" class="mt-2 pl-3 py-2 space-y-2.5 bg-white/60 rounded-lg p-3 text-sm">
                <router-link
                  to="/collections"
                  class="block font-semibold text-[#14382A] hover:text-[#16A36A] pb-2 border-b border-[#D8CCBC]/40"
                  @click="$emit('close')"
                >
                  সকল কালেকশন ব্রাউজ করুন →
                </router-link>

                <div class="space-y-1 pt-1">
                  <span class="text-[11px] font-bold uppercase tracking-wider text-[#A89B8A] block">টি-শার্ট ও স্ট্রিটওয়্যার</span>
                  <router-link to="/category/t-shirt" class="block text-[#111111] hover:text-[#16A36A] py-0.5" @click="$emit('close')">টি-শার্ট (T-shirt)</router-link>
                  <router-link to="/category/polo-t-shirt" class="block text-[#111111] hover:text-[#16A36A] py-0.5" @click="$emit('close')">পোলো টি-শার্ট (Polo)</router-link>
                  <router-link to="/category/drop-shoulder" class="block text-[#111111] hover:text-[#16A36A] py-0.5" @click="$emit('close')">ড্রপ শোল্ডার (Drop shoulder)</router-link>
                  <router-link to="/category/boxy" class="block text-[#111111] hover:text-[#16A36A] py-0.5" @click="$emit('close')">বক্সি ফিট (BOXY)</router-link>
                  <router-link to="/category/sleeveless-t-shirt" class="block text-[#111111] hover:text-[#16A36A] py-0.5" @click="$emit('close')">স্লিভলেস টি-শার্ট</router-link>
                </div>

                <div class="space-y-1 pt-2 border-t border-[#D8CCBC]/20">
                  <span class="text-[11px] font-bold uppercase tracking-wider text-[#A89B8A] block">শার্ট ও পাঞ্জাবি</span>
                  <router-link to="/category/panjabi" class="block font-medium text-[#14382A] hover:text-[#16A36A] py-0.5" @click="$emit('close')">পাঞ্জাবি (Panjabi)</router-link>
                  <router-link to="/category/casual-shirt" class="block text-[#111111] hover:text-[#16A36A] py-0.5" @click="$emit('close')">ক্যাজুয়াল শার্ট</router-link>
                  <router-link to="/category/short-sleeve-shirt" class="block text-[#111111] hover:text-[#16A36A] py-0.5" @click="$emit('close')">হাফ হাতা শার্ট</router-link>
                  <router-link to="/category/full-sleeves-shirt" class="block text-[#111111] hover:text-[#16A36A] py-0.5" @click="$emit('close')">ফুল হাতা শার্ট</router-link>
                </div>

                <div class="space-y-1 pt-2 border-t border-[#D8CCBC]/20">
                  <span class="text-[11px] font-bold uppercase tracking-wider text-[#A89B8A] block">প্যান্ট ও ট্রাউজার</span>
                  <router-link to="/category/trouser" class="block text-[#111111] hover:text-[#16A36A] py-0.5" @click="$emit('close')">ট্রাউজার (Trouser)</router-link>
                  <router-link to="/category/jeans-pant" class="block text-[#111111] hover:text-[#16A36A] py-0.5" @click="$emit('close')">জিন্স প্যান্ট (Jeans)</router-link>
                  <router-link to="/category/baggy-pants" class="block text-[#111111] hover:text-[#16A36A] py-0.5" @click="$emit('close')">ব্যাগী প্যান্ট (Baggy)</router-link>
                  <router-link to="/category/2-quarter-pants" class="block text-[#111111] hover:text-[#16A36A] py-0.5" @click="$emit('close')">২ কোয়ার্টার প্যান্ট</router-link>
                </div>

                <div class="space-y-1 pt-2 border-t border-[#D8CCBC]/20">
                  <span class="text-[11px] font-bold uppercase tracking-wider text-[#A89B8A] block">লেডিস কালেকশন</span>
                  <router-link to="/category/ladies-tops" class="block text-[#111111] hover:text-[#16A36A] py-0.5" @click="$emit('close')">লেডিস টপস (Tops)</router-link>
                  <router-link to="/category/ladies-pants" class="block text-[#111111] hover:text-[#16A36A] py-0.5" @click="$emit('close')">লেডিস প্যান্ট (Pants)</router-link>
                  <router-link to="/category/ladies-bell-bottom-pants" class="block text-[#111111] hover:text-[#16A36A] py-0.5" @click="$emit('close')">বেল-বটম প্যান্ট</router-link>
                </div>
              </div>
            </div>

            <!-- LOOKBOOK -->
            <a
              href="#lookbook"
              class="block py-3 text-base font-medium text-[#111111] hover:text-[#16A36A] border-b border-[#D8CCBC]/30 transition-colors"
              @click="$emit('close')"
            >
              লুকবুক
            </a>

            <!-- ABOUT -->
            <router-link
              to="/about"
              class="block py-3 text-base font-medium text-[#111111] hover:text-[#16A36A] border-b border-[#D8CCBC]/30 transition-colors"
              @click="$emit('close')"
            >
              আমাদের সম্পর্কে
            </router-link>

            <!-- Track Order -->
            <router-link
              to="/track-order"
              class="block py-3 text-sm font-medium text-[#69736E] hover:text-[#16A36A] transition-colors"
              @click="$emit('close')"
            >
              অর্ডার ট্র্যাক করুন
            </router-link>
          </div>

          <!-- Footer Actions -->
          <div class="p-6 bg-white border-t border-[#D8CCBC]/40 space-y-3">
            <template v-if="authStore.isCustomerAuthenticated">
              <router-link
                to="/account"
                class="w-full text-center py-3 bg-[#111111] text-white text-xs uppercase tracking-widest font-medium block"
                @click="$emit('close')"
              >
                আমার অ্যাকাউন্ট
              </router-link>
              <button
                @click="authStore.customerLogout(); $emit('close')"
                class="w-full text-center py-2 text-xs uppercase tracking-widest text-[#A89B8A] hover:text-[#111111] font-medium block"
              >
                লগআউট
              </button>
            </template>
            <template v-else>
              <router-link
                to="/account"
                class="w-full text-center py-3 bg-[#111111] text-white text-xs uppercase tracking-widest font-medium block"
                @click="$emit('close')"
              >
                সাইন ইন / রেজিস্ট্রেশন
              </router-link>
            </template>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref } from 'vue';
import { useAuthStore } from '../../stores/auth';

defineProps({
  isOpen: { type: Boolean, default: false },
  categories: { type: Array, default: () => [] },
});

defineEmits(['close']);

const authStore = useAuthStore();
const shopExpanded = ref(false);
const expandedCat = ref(null);

const toggleCategory = (id) => {
  expandedCat.value = expandedCat.value === id ? null : id;
};
</script>
