<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="opacity-0 -translate-y-4"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 -translate-y-4"
    >
      <div
        v-if="isOpen"
        class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex flex-col justify-start"
        @click.self="$emit('close')"
      >
        <div class="bg-white w-full border-b border-[#D8CCBC]/40 shadow-2xl py-8 px-6 sm:px-12">
          <div class="max-w-7xl mx-auto">
            <!-- Search Input Header -->
            <div class="flex items-center justify-between gap-4 border-b-2 border-[#111111] pb-3 mb-6">
              <svg class="w-6 h-6 text-[#111111]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
              </svg>
              <input
                ref="searchInput"
                type="text"
                v-model="query"
                @input="handleSearch"
                @keydown.enter.prevent="viewAll"
                @keydown.esc="$emit('close')"
                placeholder="পোশাক, পাঞ্জাবি, টি-শার্ট, প্যান্ট খুঁজুন..."
                class="w-full text-xl sm:text-2xl font-serif text-[#111111] placeholder:text-[#A89B8A] focus:outline-none bg-transparent"
              />
              <button
                type="button"
                @click="$emit('close')"
                class="p-2 text-[#A89B8A] hover:text-[#111111] transition-colors"
              >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/>
                </svg>
              </button>
            </div>

            <!-- Quick Suggestions -->
            <div v-if="!query && !results.length" class="space-y-4">
              <span class="text-xs uppercase tracking-widest text-[#A89B8A] font-semibold">জনপ্রিয় অনুসন্ধান:</span>
              <div class="flex flex-wrap gap-2">
                <button
                  v-for="tag in ['পাঞ্জাবি', 'টি-শার্ট', 'ব্যাগি প্যান্ট', 'পোলো টি-শার্ট', 'ড্রপ শোল্ডার', 'ক্যাজুয়াল শার্ট']"
                  :key="tag"
                  type="button"
                  @click="query = tag; handleSearch()"
                  class="px-3 py-1.5 bg-[#F7F5F0] hover:bg-[#D8CCBC]/40 text-xs text-[#111111] tracking-wide transition-colors"
                >
                  {{ tag }}
                </button>
              </div>
            </div>

            <!-- Live Results -->
            <div v-if="results.length" class="mt-6">
              <h5 class="text-xs uppercase tracking-widest text-[#A89B8A] font-semibold mb-4">খুঁজে পাওয়া পোশাক ({{ results.length }}টি):</h5>
              <div data-lenis-prevent class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 max-h-[60vh] overflow-y-auto pr-2">
                <router-link
                  v-for="item in results"
                  :key="item.id"
                  :to="`/product/${item.slug}`"
                  class="group flex gap-3 items-center p-2 hover:bg-[#F7F5F0] transition-colors"
                  @click="$emit('close')"
                >
                  <div class="w-16 h-20 bg-[#F7F5F0] overflow-hidden shrink-0">
                    <img :src="item.featured_image" :alt="item.name" class="w-full h-full object-cover group-hover:scale-105 transition-transform" />
                  </div>
                  <div class="flex-1 min-w-0">
                    <h6 class="text-xs font-serif text-[#111111] truncate group-hover:text-[#A89B8A]">{{ item.name }}</h6>
                    <p class="text-[11px] text-[#A89B8A] mb-1">কোড: {{ item.sku }}</p>
                    <span class="text-xs font-semibold text-[#111111]">৳ {{ Number(item.sale_price || item.price).toLocaleString() }}</span>
                  </div>
                </router-link>
              </div>
              <button
                type="button"
                @click="viewAll"
                class="mt-5 inline-flex items-center gap-2 h-11 px-6 bg-[#111111] text-white text-sm rounded-sm hover:bg-black transition-colors"
              >
                "{{ query.trim() }}" এর সব ফলাফল ফিল্টারসহ দেখুন →
              </button>
            </div>

            <div v-else-if="query && query.length >= 2 && !loading" class="mt-8 text-center text-sm text-[#A89B8A]">
              "{{ query }}" দিয়ে কোনো পোশাক পাওয়া যায়নি।
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, watch, nextTick } from 'vue';
import { useRouter } from 'vue-router';
import api from '../../api';

const router = useRouter();

// Enter / "view all": open the shop page with the search applied (and all filters available).
const viewAll = () => {
  const q = query.value.trim();
  if (q.length < 2) return;
  emit('close');
  router.push({ path: '/collections', query: { q } });
};

const props = defineProps({
  isOpen: { type: Boolean, default: false },
});

const emit = defineEmits(['close']);

const query = ref('');
const results = ref([]);
const loading = ref(false);
const searchInput = ref(null);
let debounceTimeout = null;

watch(() => props.isOpen, (open) => {
  if (open) {
    nextTick(() => {
      searchInput.value?.focus();
    });
  } else {
    query.value = '';
    results.value = [];
  }
});

const handleSearch = () => {
  clearTimeout(debounceTimeout);
  if (!query.value || query.value.trim().length < 2) {
    results.value = [];
    return;
  }

  debounceTimeout = setTimeout(async () => {
    loading.value = true;
    try {
      const response = await api.get('/public/search', { params: { q: query.value.trim() } });
      results.value = response.data.data || [];
    } catch (err) {
      console.error(err);
    } finally {
      loading.value = false;
    }
  }, 250);
};
</script>
