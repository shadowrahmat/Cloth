<template>
  <div class="py-12 sm:py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header Banner / Title -->
    <div class="mb-12 border-b border-[#D8CCBC]/40 pb-8">
      <span class="text-xs uppercase tracking-[0.25em] text-[#A89B8A] font-medium block mb-2">Curated Collection</span>
      <h1 class="font-serif text-3xl sm:text-5xl text-[#111111] capitalize">
        {{ currentCategoryName }}
      </h1>
      <p v-if="currentCategoryDescription" class="text-xs sm:text-sm text-[#A89B8A] max-w-2xl mt-3 font-light leading-relaxed">
        {{ currentCategoryDescription }}
      </p>

      <!-- Subcategories Pill Tabs -->
      <div v-if="subcategories.length" class="flex flex-wrap gap-2 pt-6">
        <router-link
          v-for="sub in subcategories"
          :key="sub.id"
          :to="`/collection/${sub.slug}`"
          :class="[
            'px-4 py-2 text-xs uppercase tracking-wider font-medium transition-colors border',
            route.params.slug === sub.slug
              ? 'bg-[#111111] text-white border-[#111111]'
              : 'bg-white text-[#111111] border-[#D8CCBC]/60 hover:border-[#111111]'
          ]"
        >
          {{ sub.name }}
        </router-link>
      </div>
    </div>

    <!-- Layout: Filters Sidebar & Product Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
      <!-- Desktop Filters Sidebar -->
      <aside class="hidden lg:block lg:col-span-3 space-y-8 pr-4">
        <!-- Sort By -->
        <div class="space-y-3">
          <h5 class="text-xs uppercase tracking-widest text-[#111111] font-semibold">Sort Order</h5>
          <select
            v-model="filters.sort"
            @change="applyFilters"
            class="w-full px-3 py-2 bg-white border border-[#D8CCBC] text-xs text-[#111111] focus:outline-none focus:border-[#111111]"
          >
            <option value="newest">Newest Releases</option>
            <option value="bestseller">Best Selling</option>
            <option value="price_asc">Price: Low to High</option>
            <option value="price_desc">Price: High to Low</option>
            <option value="featured">Featured First</option>
          </select>
        </div>

        <!-- Size Filter -->
        <div class="space-y-3">
          <h5 class="text-xs uppercase tracking-widest text-[#111111] font-semibold">Size</h5>
          <div class="flex flex-wrap gap-2">
            <button
              v-for="s in ['XS', 'S', 'M', 'L', 'XL', '38R', '40R', '42R']"
              :key="s"
              type="button"
              @click="toggleFilter('size', s)"
              :class="[
                'px-3 py-1.5 text-xs border transition-colors',
                filters.size === s ? 'bg-[#111111] text-white border-[#111111]' : 'bg-white text-[#111111] border-[#D8CCBC]/60 hover:border-[#111111]'
              ]"
            >
              {{ s }}
            </button>
          </div>
        </div>

        <!-- Color Filter -->
        <div class="space-y-3">
          <h5 class="text-xs uppercase tracking-widest text-[#111111] font-semibold">Color</h5>
          <div class="flex flex-wrap gap-2">
            <button
              v-for="c in ['Black', 'Ivory', 'Sand', 'Taupe', 'Navy']"
              :key="c"
              type="button"
              @click="toggleFilter('color', c)"
              :class="[
                'px-3 py-1.5 text-xs border transition-colors',
                filters.color === c ? 'bg-[#111111] text-white border-[#111111]' : 'bg-white text-[#111111] border-[#D8CCBC]/60 hover:border-[#111111]'
              ]"
            >
              {{ c }}
            </button>
          </div>
        </div>

        <!-- Price Range -->
        <div class="space-y-3">
          <h5 class="text-xs uppercase tracking-widest text-[#111111] font-semibold">Price Range ($)</h5>
          <div class="flex items-center gap-2">
            <input
              type="number"
              v-model.number="filters.min_price"
              @change="applyFilters"
              placeholder="Min"
              class="w-full px-3 py-1.5 bg-white border border-[#D8CCBC] text-xs"
            />
            <span class="text-[#A89B8A]">—</span>
            <input
              type="number"
              v-model.number="filters.max_price"
              @change="applyFilters"
              placeholder="Max"
              class="w-full px-3 py-1.5 bg-white border border-[#D8CCBC] text-xs"
            />
          </div>
        </div>

        <!-- Toggles -->
        <div class="space-y-2.5 pt-2 border-t border-[#D8CCBC]/40">
          <label class="flex items-center gap-2.5 cursor-pointer text-xs text-[#111111]">
            <input
              type="checkbox"
              v-model="filters.on_sale"
              @change="applyFilters"
              class="rounded border-[#D8CCBC]"
            />
            <span>On Sale Only</span>
          </label>
          <label class="flex items-center gap-2.5 cursor-pointer text-xs text-[#111111]">
            <input
              type="checkbox"
              v-model="filters.in_stock"
              @change="applyFilters"
              class="rounded border-[#D8CCBC]"
            />
            <span>In Stock Only</span>
          </label>
        </div>

        <!-- Clear Filters -->
        <button
          v-if="hasActiveFilters"
          type="button"
          @click="clearFilters"
          class="text-xs text-[#A89B8A] hover:text-[#111111] underline tracking-wider uppercase block"
        >
          Reset All Filters
        </button>
      </aside>

      <!-- Products Grid Area -->
      <div class="col-span-12 lg:col-span-9">
        <!-- Results Count & Mobile Sort -->
        <div class="flex items-center justify-between mb-8 pb-3 border-b border-[#D8CCBC]/30 text-xs text-[#A89B8A]">
          <span>Displaying {{ products.length }} {{ products.length === 1 ? 'garment' : 'garments' }}</span>

          <select
            v-model="filters.sort"
            @change="applyFilters"
            class="lg:hidden px-3 py-1.5 bg-white border border-[#D8CCBC] text-xs text-[#111111]"
          >
            <option value="newest">Newest</option>
            <option value="bestseller">Best Sellers</option>
            <option value="price_asc">Price Low to High</option>
            <option value="price_desc">Price High to Low</option>
          </select>
        </div>

        <!-- Loading -->
        <LoadingState v-if="loading" />

        <!-- Grid -->
        <ProductGrid
          v-else-if="products.length"
          :products="products"
        />

        <!-- Empty State -->
        <EmptyState
          v-else
          title="No garments found"
          description="There are currently no pieces in this category matching your selected filters."
        >
          <template #action>
            <button
              @click="clearFilters"
              class="px-6 py-2.5 bg-[#111111] text-white text-xs uppercase tracking-widest font-semibold"
            >
              Reset Filters
            </button>
          </template>
        </EmptyState>

        <!-- Pagination -->
        <div
          v-if="meta && meta.last_page > 1"
          class="flex justify-center items-center gap-2 pt-16 border-t border-[#D8CCBC]/40 mt-16"
        >
          <button
            :disabled="meta.current_page <= 1"
            @click="fetchProducts(meta.current_page - 1)"
            class="px-4 py-2 border border-[#D8CCBC] text-xs uppercase disabled:opacity-40"
          >
            Previous
          </button>
          <span class="px-4 py-2 text-xs font-semibold">
            {{ meta.current_page }} of {{ meta.last_page }}
          </span>
          <button
            :disabled="meta.current_page >= meta.last_page"
            @click="fetchProducts(meta.current_page + 1)"
            class="px-4 py-2 border border-[#D8CCBC] text-xs uppercase disabled:opacity-40"
          >
            Next
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import api from '../../api';
import ProductGrid from '../../components/public/ProductGrid.vue';
import LoadingState from '../../components/common/LoadingState.vue';
import EmptyState from '../../components/common/EmptyState.vue';

const route = useRoute();

const products = ref([]);
const categoriesTree = ref([]);
const currentCategory = ref(null);
const subcategories = ref([]);
const loading = ref(true);
const meta = ref(null);

const filters = reactive({
  sort: 'newest',
  size: '',
  color: '',
  min_price: null,
  max_price: null,
  on_sale: false,
  in_stock: false,
});

const currentCategoryName = computed(() => {
  const slug = route.params.slug;
  if (!slug || slug === 'all') return 'All Garments & Silhouettes';
  if (slug === 'new-arrivals') return 'New Season Arrivals';
  if (slug === 'winter') return 'The Winter Collection';
  return currentCategory.value?.name || slug.replace('-', ' ');
});

const currentCategoryDescription = computed(() => {
  return currentCategory.value?.description || '';
});

const hasActiveFilters = computed(() => {
  return !!(filters.size || filters.color || filters.min_price || filters.max_price || filters.on_sale || filters.in_stock);
});

const toggleFilter = (key, val) => {
  filters[key] = filters[key] === val ? '' : val;
  applyFilters();
};

const clearFilters = () => {
  filters.size = '';
  filters.color = '';
  filters.min_price = null;
  filters.max_price = null;
  filters.on_sale = false;
  filters.in_stock = false;
  applyFilters();
};

const applyFilters = () => {
  fetchProducts(1);
};

const fetchProducts = async (page = 1) => {
  loading.value = true;
  try {
    const slug = route.params.slug;
    const params = {
      page,
      sort: filters.sort,
      size: filters.size || undefined,
      color: filters.color || undefined,
      min_price: filters.min_price || undefined,
      max_price: filters.max_price || undefined,
      on_sale: filters.on_sale ? 1 : undefined,
      in_stock: filters.in_stock ? 1 : undefined,
    };

    if (slug && slug !== 'all' && slug !== 'new-arrivals') {
      params.category = slug;
    }

    const res = await api.get('/public/products', { params });
    products.value = res.data.data || [];
    meta.value = res.data.meta || null;
  } catch (err) {
    console.error('Failed to fetch collection products', err);
  } finally {
    loading.value = false;
  }
};

const loadCategoryMeta = async () => {
  try {
    const res = await api.get('/public/categories');
    categoriesTree.value = res.data.data || [];

    const slug = route.params.slug;
    // Find category in parent or child
    let found = null;
    let subs = [];

    for (const parent of categoriesTree.value) {
      if (parent.slug === slug) {
        found = parent;
        subs = parent.children || [];
        break;
      }
      if (parent.children) {
        const c = parent.children.find(child => child.slug === slug);
        if (c) {
          found = c;
          subs = parent.children;
          break;
        }
      }
    }

    currentCategory.value = found;
    subcategories.value = subs;
  } catch (err) {
    console.error('Failed to load category meta', err);
  }
};

watch(() => route.params.slug, () => {
  loadCategoryMeta();
  fetchProducts(1);
});

onMounted(() => {
  loadCategoryMeta();
  fetchProducts(1);
});
</script>
