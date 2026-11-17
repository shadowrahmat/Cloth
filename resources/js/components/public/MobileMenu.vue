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

          <!-- Navigation Accordion -->
          <div class="px-6 py-6 flex-1 space-y-6">
            <div v-for="cat in categories" :key="cat.id" class="border-b border-[#D8CCBC]/30 pb-4">
              <div class="flex items-center justify-between py-1">
                <router-link
                  :to="`/collection/${cat.slug}`"
                  class="font-serif text-xl text-[#111111]"
                  @click="$emit('close')"
                >
                  {{ cat.name }}
                </router-link>
                <button
                  v-if="cat.children && cat.children.length"
                  @click="toggleCategory(cat.id)"
                  class="p-1 text-[#A89B8A]"
                >
                  <svg
                    :class="['w-4 h-4 transition-transform', expandedCat === cat.id ? 'rotate-180' : '']"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                  </svg>
                </button>
              </div>

              <!-- Subcategories -->
              <div v-if="expandedCat === cat.id && cat.children && cat.children.length" class="mt-3 pl-3 space-y-2">
                <router-link
                  v-for="sub in cat.children"
                  :key="sub.id"
                  :to="`/collection/${sub.slug}`"
                  class="block text-sm text-[#111111]/70 hover:text-[#111111] py-1"
                  @click="$emit('close')"
                >
                  {{ sub.name }}
                </router-link>
              </div>
            </div>

            <!-- Static Links -->
            <div class="space-y-3 pt-2 text-sm tracking-wider uppercase font-medium text-[#111111]">
              <router-link to="/collection/new-arrivals" class="block py-1" @click="$emit('close')">New Arrivals</router-link>
              <router-link to="/collection/winter" class="block py-1 text-[#C5A880]" @click="$emit('close')">Winter Collection</router-link>
              <router-link to="/pages/about-us" class="block py-1 text-[#A89B8A]" @click="$emit('close')">The Atelier Story</router-link>
              <router-link to="/pages/contact" class="block py-1 text-[#A89B8A]" @click="$emit('close')">Client Concierge</router-link>
              <router-link to="/track-order" class="block py-1 text-[#A89B8A]" @click="$emit('close')">Track Order</router-link>
            </div>
          </div>

          <!-- Footer Actions -->
          <div class="p-6 bg-white border-t border-[#D8CCBC]/40 space-y-3">
            <template v-if="authStore.isCustomerAuthenticated">
              <router-link
                to="/account"
                class="w-full text-center py-3 bg-[#111111] text-white text-xs uppercase tracking-widest font-medium block"
                @click="$emit('close')"
              >
                My Client Account
              </router-link>
              <button
                @click="authStore.customerLogout(); $emit('close')"
                class="w-full text-center py-2 text-xs uppercase tracking-widest text-[#A89B8A] hover:text-[#111111] font-medium block"
              >
                Sign Out
              </button>
            </template>
            <template v-else>
              <router-link
                to="/account"
                class="w-full text-center py-3 bg-[#111111] text-white text-xs uppercase tracking-widest font-medium block"
                @click="$emit('close')"
              >
                Sign In / Register
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
const expandedCat = ref(null);

const toggleCategory = (id) => {
  expandedCat.value = expandedCat.value === id ? null : id;
};
</script>
