<template>
  <div class="pb-16">
    <!-- Page header -->
    <header class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 sm:pt-12 pb-6">
      <nav class="text-[12px] text-[#A89B8A] mb-3 flex items-center gap-1.5" aria-label="Breadcrumb">
        <router-link to="/" class="hover:text-[#111111]">হোম</router-link>
        <ChevronRight class="w-3 h-3" />
        <router-link v-if="slug" to="/collections" class="hover:text-[#111111]">সব পোশাক</router-link>
        <template v-if="slug"><ChevronRight class="w-3 h-3" /><span class="text-[#111111]">{{ pageTitle }}</span></template>
        <span v-else class="text-[#111111]">সব পোশাক</span>
      </nav>
      <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3" v-reveal>
        <div>
          <h1 class="font-serif text-3xl sm:text-[42px] leading-tight text-[#111111]">{{ pageTitle }}</h1>
          <p v-if="pageDescription" class="text-sm text-[#69736E] max-w-2xl mt-2 leading-relaxed">{{ pageDescription }}</p>
        </div>
        <p class="text-[13px] text-[#69736E] shrink-0">
          <template v-if="meta">{{ bnNumber(meta.total) }}টি পণ্য</template>
        </p>
      </div>

      <!-- Sub-category pills -->
      <div v-if="subcategories.length" class="flex gap-2 overflow-x-auto no-scrollbar pt-5 -mx-4 px-4 sm:mx-0 sm:px-0">
        <router-link
          v-for="sub in subcategories" :key="sub.id" :to="`/category/${sub.slug}`"
          :class="['shrink-0 px-4 h-9 inline-flex items-center rounded-full border text-[13px] transition-colors', slug === sub.slug ? 'bg-[#111111] text-white border-[#111111]' : 'bg-white border-[#E5DED3] text-[#111111]/80 hover:border-[#111111]']"
        >{{ sub.name }}</router-link>
      </div>
    </header>

    <!-- Toolbar (sticky under the header) -->
    <div class="sticky top-14 lg:top-16 z-30 bg-[#F7F5F0]/95 backdrop-blur border-y border-[#E5DED3]/70">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-14 flex items-center gap-3">
        <button
          type="button" @click="sheetOpen = true"
          class="lg:hidden inline-flex items-center gap-2 h-9 px-3.5 rounded-full border border-[#111111] text-[13px] font-medium"
        >
          <SlidersHorizontal class="w-4 h-4" /> ফিল্টার
          <span v-if="activeCount" class="min-w-5 h-5 px-1.5 rounded-full bg-[#111111] text-white text-[11px] flex items-center justify-center">{{ bnNumber(activeCount) }}</span>
        </button>

        <!-- Active filter chips -->
        <div class="hidden lg:flex flex-1 min-w-0 items-center gap-2 overflow-x-auto no-scrollbar">
          <span v-if="!chips.length" class="text-[13px] text-[#A89B8A]">বাম পাশ থেকে ফিল্টার বেছে নিন</span>
          <TransitionGroup name="fade">
            <button v-for="c in chips" :key="c.key" type="button" @click="c.remove()"
              class="shrink-0 inline-flex items-center gap-1.5 h-8 pl-3 pr-2 rounded-full bg-white border border-[#E5DED3] text-[13px] hover:border-[#111111] transition-colors">
              {{ c.label }} <X class="w-3.5 h-3.5 text-[#A89B8A]" />
            </button>
          </TransitionGroup>
          <button v-if="chips.length > 1" type="button" @click="clearAll" class="shrink-0 text-[13px] font-medium underline underline-offset-4 ml-1">সব মুছুন</button>
        </div>
        <div class="flex-1 lg:hidden"></div>

        <!-- Sort -->
        <label class="relative inline-flex items-center">
          <span class="sr-only">সাজান</span>
          <ArrowUpDown class="w-4 h-4 absolute left-3 pointer-events-none text-[#111111]/70" />
          <select :value="filters.sort" @change="update({ sort: $event.target.value })"
                  class="appearance-none h-9 pl-9 pr-8 rounded-full border border-[#E5DED3] bg-white text-[13px] font-medium cursor-pointer hover:border-[#111111] focus:outline-none focus:border-[#111111]">
            <option v-for="o in SORTS" :key="o.value" :value="o.value">{{ o.label }}</option>
          </select>
          <ChevronDown class="w-4 h-4 absolute right-2.5 pointer-events-none text-[#A89B8A]" />
        </label>

        <!-- Density -->
        <div class="hidden lg:flex items-center rounded-full border border-[#E5DED3] bg-white p-0.5">
          <button v-for="d in [3, 4]" :key="d" type="button" @click="setColumns(d)" :aria-pressed="columns === d"
                  :class="['w-8 h-8 rounded-full flex items-center justify-center transition-colors', columns === d ? 'bg-[#111111] text-white' : 'text-[#111111]/60 hover:text-[#111111]']"
                  :title="`এক সারিতে ${bnNumber(d)}টি`">
            <component :is="d === 3 ? Grid3x3 : LayoutGrid" class="w-4 h-4" />
          </button>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 grid lg:grid-cols-[256px_1fr] gap-10 items-start">
      <!-- Desktop sidebar -->
      <aside class="hidden lg:block sticky top-36 max-h-[calc(100vh-10rem)] overflow-y-auto no-scrollbar pr-1" data-lenis-prevent>
        <FilterPanel :facets="facets" :filters="filters" :show-categories="!routeCategory" @update="update" />
      </aside>

      <!-- Results -->
      <section aria-live="polite">
        <!-- First load -->
        <div v-if="loading && !products.length" :class="['grid gap-x-3 sm:gap-x-6 gap-y-8', columns === 3 ? 'grid-cols-2 md:grid-cols-3' : 'grid-cols-2 md:grid-cols-3 lg:grid-cols-4']">
          <div v-for="n in 8" :key="n" class="space-y-3"><div class="skeleton aspect-[3/4] rounded-sm"></div><div class="skeleton h-3 w-2/3 rounded"></div><div class="skeleton h-3 w-1/3 rounded"></div></div>
        </div>

        <!-- Empty -->
        <div v-else-if="!products.length" class="py-20 text-center max-w-sm mx-auto" v-reveal>
          <div class="w-16 h-16 mx-auto mb-5 rounded-full bg-white border border-[#E5DED3] flex items-center justify-center"><SearchX class="w-7 h-7 text-[#A89B8A]" /></div>
          <h3 class="font-serif text-2xl text-[#111111] mb-2">কোনো পণ্য পাওয়া যায়নি</h3>
          <p class="text-sm text-[#69736E] mb-6">ফিল্টার একটু কমিয়ে আবার চেষ্টা করুন।</p>
          <button v-if="activeCount" type="button" @click="clearAll" class="px-6 h-11 bg-[#111111] text-white text-sm rounded-sm">সব ফিল্টার মুছুন</button>
        </div>

        <template v-else>
          <div :class="['transition-opacity duration-300', loading ? 'opacity-50 pointer-events-none' : '']">
            <ProductGrid :products="products" :columns="columns" />
          </div>

          <!-- Load more -->
          <div v-if="meta && meta.current_page < meta.last_page" class="mt-14 flex flex-col items-center gap-3">
            <p class="text-[13px] text-[#69736E]">{{ bnNumber(products.length) }} / {{ bnNumber(meta.total) }}টি পণ্য দেখছেন</p>
            <div class="w-48 h-0.5 bg-[#E5DED3] rounded-full overflow-hidden"><div class="h-full bg-[#111111] transition-all duration-500" :style="{ width: `${(products.length / meta.total) * 100}%` }"></div></div>
            <button type="button" @click="loadMore" :disabled="loadingMore"
                    class="mt-2 inline-flex items-center gap-2 h-11 px-8 border border-[#111111] text-sm font-medium rounded-sm hover:bg-[#111111] hover:text-white transition-colors disabled:opacity-50">
              <span v-if="loadingMore" class="w-4 h-4 border-2 border-current/30 border-t-current rounded-full animate-spin"></span>
              আরও পণ্য দেখুন
            </button>
          </div>
        </template>
      </section>
    </div>

    <!-- Mobile filter sheet -->
    <Teleport to="body">
      <Transition name="fade"><div v-if="sheetOpen" class="fixed inset-0 z-50 bg-black/40 lg:hidden" @click="sheetOpen = false"></div></Transition>
      <Transition name="sheet">
        <div v-if="sheetOpen" data-lenis-prevent role="dialog" aria-modal="true" aria-label="ফিল্টার"
             class="fixed inset-x-0 bottom-0 z-50 lg:hidden max-h-[88vh] flex flex-col bg-[#F7F5F0] rounded-t-2xl shadow-2xl">
          <div class="pt-2.5 pb-1 flex justify-center"><span class="w-10 h-1 rounded-full bg-[#D8CCBC]"></span></div>
          <div class="px-5 h-12 flex items-center justify-between border-b border-[#E5DED3]">
            <h2 class="text-[16px] font-semibold">ফিল্টার</h2>
            <div class="flex items-center gap-2">
              <button v-if="activeCount" type="button" @click="clearAll" class="text-[13px] underline underline-offset-4">সব মুছুন</button>
              <button type="button" @click="sheetOpen = false" class="p-2 -mr-2" aria-label="বন্ধ করুন"><X class="w-5 h-5" /></button>
            </div>
          </div>
          <div class="flex-1 overflow-y-auto px-5">
            <FilterPanel :facets="facets" :filters="filters" :show-categories="!routeCategory" @update="update" />
          </div>
          <div class="p-4 border-t border-[#E5DED3] bg-white">
            <button type="button" @click="sheetOpen = false" class="w-full h-12 bg-[#111111] text-white text-[15px] font-medium rounded-sm inline-flex items-center justify-center gap-2">
              <span v-if="loading" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
              {{ bnNumber(facets.total ?? meta?.total ?? 0) }}টি পণ্য দেখুন
            </button>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import {
  ChevronRight, ChevronDown, SlidersHorizontal, X, ArrowUpDown, Grid3x3, LayoutGrid, SearchX,
} from 'lucide-vue-next';
import api from '../../api';
import ProductGrid from '../../components/public/ProductGrid.vue';
import FilterPanel from '../../components/public/shop/FilterPanel.vue';
import { bnNumber, taka } from '../../utils/bn';

const route = useRoute();
const router = useRouter();

const SORTS = [
  { value: 'newest', label: 'নতুন আগে' },
  { value: 'bestseller', label: 'জনপ্রিয়' },
  { value: 'price_asc', label: 'দাম: কম → বেশি' },
  { value: 'price_desc', label: 'দাম: বেশি → কম' },
  { value: 'discount', label: 'বেশি ছাড়' },
];

// --- what this page is (all products / a category / a curated collection)
const slug = computed(() => route.params.slug || '');
const isNewArrivals = computed(() => slug.value === 'new-arrivals');
const routeCategory = computed(() => (slug.value && !isNewArrivals.value ? slug.value : ''));

const categoriesTree = ref([]);
const collectionInfo = ref(null);
const currentCategory = computed(() => {
  for (const p of categoriesTree.value) {
    if (p.slug === slug.value) return { ...p, siblings: p.children || [] };
    const c = (p.children || []).find((x) => x.slug === slug.value);
    if (c) return { ...c, siblings: p.children || [] };
  }
  return null;
});
const subcategories = computed(() => currentCategory.value?.siblings || []);
const pageTitle = computed(() => {
  if (!slug.value) return 'সব পোশাক';
  if (isNewArrivals.value) return 'নতুন কালেকশন';
  return currentCategory.value?.name || collectionInfo.value?.name || slug.value.replaceAll('-', ' ');
});
const pageDescription = computed(() => currentCategory.value?.description || collectionInfo.value?.description || '');

// --- filter state <-> URL query (shareable, works with Back)
const list = (v) => (v ? String(v).split(',').filter(Boolean) : []);
const num = (v) => (v === undefined || v === '' || v === null || Number.isNaN(Number(v)) ? null : Number(v));
const fromQuery = (q) => ({
  categories: list(q.cat),
  size: list(q.size),
  color: list(q.color),
  min_price: num(q.min),
  max_price: num(q.max),
  on_sale: q.sale === '1',
  in_stock: q.stock === '1',
  sort: q.sort || 'newest',
  search: q.q || '',
});
const filters = reactive(fromQuery(route.query));

const toQuery = (f) => {
  const q = {};
  if (f.categories.length) q.cat = f.categories.join(',');
  if (f.size.length) q.size = f.size.join(',');
  if (f.color.length) q.color = f.color.join(',');
  if (f.min_price !== null) q.min = f.min_price;
  if (f.max_price !== null) q.max = f.max_price;
  if (f.on_sale) q.sale = '1';
  if (f.in_stock) q.stock = '1';
  if (f.sort !== 'newest') q.sort = f.sort;
  if (f.search) q.q = f.search;
  return q;
};

const update = (patch) => {
  Object.assign(filters, patch);
  router.replace({ query: toQuery(filters) });
};
const clearAll = () => update({ categories: [], size: [], color: [], min_price: null, max_price: null, on_sale: false, in_stock: false, search: '' });

// Back/Forward or a link changing the query → mirror into state
watch(() => route.query, (q) => Object.assign(filters, fromQuery(q)));

const activeCount = computed(() =>
  filters.categories.length + filters.size.length + filters.color.length
  + (filters.min_price !== null || filters.max_price !== null ? 1 : 0)
  + (filters.on_sale ? 1 : 0) + (filters.in_stock ? 1 : 0) + (filters.search ? 1 : 0));

const catName = (s) => (facets.value.categories || []).find((c) => c.slug === s)?.name || s;
const chips = computed(() => [
  ...(filters.search ? [{ key: 'q', label: `"${filters.search}"`, remove: () => update({ search: '' }) }] : []),
  ...filters.categories.map((s) => ({ key: 'c' + s, label: catName(s), remove: () => update({ categories: filters.categories.filter((x) => x !== s) }) })),
  ...filters.size.map((s) => ({ key: 's' + s, label: `সাইজ ${s}`, remove: () => update({ size: filters.size.filter((x) => x !== s) }) })),
  ...filters.color.map((s) => ({ key: 'k' + s, label: s, remove: () => update({ color: filters.color.filter((x) => x !== s) }) })),
  ...(filters.min_price !== null || filters.max_price !== null
    ? [{ key: 'p', label: `${filters.min_price !== null ? taka(filters.min_price) : '৳ ০'} – ${filters.max_price !== null ? taka(filters.max_price) : 'সর্বোচ্চ'}`, remove: () => update({ min_price: null, max_price: null }) }]
    : []),
  ...(filters.on_sale ? [{ key: 'sale', label: 'অফার', remove: () => update({ on_sale: false }) }] : []),
  ...(filters.in_stock ? [{ key: 'stock', label: 'স্টকে আছে', remove: () => update({ in_stock: false }) }] : []),
]);

// --- data
const products = ref([]);
const meta = ref(null);
const facets = ref({ categories: [], sizes: [], colors: [], price: null });
const loading = ref(true);
const loadingMore = ref(false);
const sheetOpen = ref(false);

const apiParams = (page = 1) => {
  const p = {
    page,
    per_page: 12,
    sort: isNewArrivals.value && filters.sort === 'newest' ? 'newest' : filters.sort,
    categories: filters.categories.join(',') || undefined,
    size: filters.size.join(',') || undefined,
    color: filters.color.join(',') || undefined,
    min_price: filters.min_price ?? undefined,
    max_price: filters.max_price ?? undefined,
    on_sale: filters.on_sale ? 1 : undefined,
    in_stock: filters.in_stock ? 1 : undefined,
    search: filters.search || undefined,
  };
  if (routeCategory.value) {
    // /category/x and /collection/x share this page: the API ignores whichever doesn't exist.
    p.category = routeCategory.value;
    if (route.name === 'collection.show') p.collection = routeCategory.value;
  }
  return p;
};

let requestId = 0;
const fetchAll = async () => {
  const id = ++requestId;
  loading.value = true;
  try {
    const params = apiParams(1);
    const [res, fac] = await Promise.all([
      api.get('/public/products', { params }),
      api.get('/public/products/facets', { params: { ...params, page: undefined, per_page: undefined, sort: undefined } }),
    ]);
    if (id !== requestId) return;            // a newer filter change won
    products.value = res.data.data || [];
    meta.value = res.data.meta || null;
    facets.value = fac.data.data || facets.value;
  } catch (err) {
    console.error('Failed to load products', err);
  } finally {
    if (id === requestId) loading.value = false;
  }
};

const loadMore = async () => {
  if (!meta.value) return;
  loadingMore.value = true;
  try {
    const res = await api.get('/public/products', { params: apiParams(meta.value.current_page + 1) });
    products.value.push(...(res.data.data || []));
    meta.value = res.data.meta;
  } finally {
    loadingMore.value = false;
  }
};

// Debounce so dragging the price slider or ticking several sizes = one request.
let timer = null;
watch(() => JSON.stringify(toQuery(filters)), () => {
  clearTimeout(timer);
  timer = setTimeout(fetchAll, 220);
});

// --- grid density preference
const columns = ref((() => { try { return Number(localStorage.getItem('shop_columns')) || 4; } catch { return 4; } })());
const setColumns = (n) => { columns.value = n; try { localStorage.setItem('shop_columns', String(n)); } catch { /* ignore */ } };

// Lock page scroll behind the mobile sheet
watch(sheetOpen, (v) => { document.documentElement.style.overflow = v ? 'hidden' : ''; });

onMounted(async () => {
  fetchAll();
  try {
    const res = await api.get('/public/categories');
    categoriesTree.value = res.data.data || [];
    if (route.name === 'collection.show' && routeCategory.value && !currentCategory.value) {
      const col = await api.get(`/public/collections/${routeCategory.value}`).catch(() => null);
      collectionInfo.value = col?.data?.data || null;
    }
  } catch { /* header falls back to the slug */ }
});
</script>
