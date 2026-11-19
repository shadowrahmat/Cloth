<template>
  <div>
    <!-- Header -->
    <header class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-10 sm:pt-16 pb-8">
      <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6" v-reveal>
        <div class="max-w-2xl space-y-3">
          <p class="text-[13px] text-[#A89B8A]">{{ blog.kicker || 'ব্লগ' }}</p>
          <h1 class="font-serif text-4xl sm:text-5xl leading-tight text-[#111111]">{{ blog.title }}</h1>
          <p v-if="blog.intro" class="text-[15px] text-[#69736E] leading-relaxed">{{ blog.intro }}</p>
        </div>
        <form @submit.prevent="fetchPosts(1)" class="relative w-full lg:w-72 shrink-0">
          <Search class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-[#A89B8A]" />
          <input v-model="search" type="search" placeholder="লেখা খুঁজুন…"
                 class="w-full h-11 pl-10 pr-4 bg-white border border-[#E5DED3] rounded-sm text-sm focus:outline-none focus:border-[#111111]" />
        </form>
      </div>

      <!-- Category tabs -->
      <div v-if="categories.length" class="mt-8 flex gap-6 overflow-x-auto no-scrollbar border-b border-[#E5DED3]">
        <button v-for="c in [{ slug: '', name: 'সব লেখা' }, ...categories]" :key="c.slug" type="button" @click="selectCategory(c.slug)"
                :class="['shrink-0 pb-3 -mb-px border-b text-[14px] transition-colors', activeCategory === c.slug ? 'border-[#111111] text-[#111111]' : 'border-transparent text-[#111111]/45 hover:text-[#111111]']">
          {{ c.name }}
        </button>
      </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-16 sm:pb-24">
      <!-- Loading -->
      <div v-if="loading && !posts.length" class="space-y-10">
        <div class="grid lg:grid-cols-2 gap-8"><div class="skeleton aspect-[4/3]"></div><div class="space-y-3 py-6"><div class="skeleton h-4 w-1/4"></div><div class="skeleton h-8 w-3/4"></div><div class="skeleton h-4 w-full"></div></div></div>
      </div>

      <template v-else-if="posts.length">
        <!-- Featured (newest) post -->
        <router-link v-if="featured" :to="`/blog/${featured.slug}`" class="group grid lg:grid-cols-12 gap-6 lg:gap-12 items-center mb-14 sm:mb-20" v-reveal>
          <div class="lg:col-span-7 aspect-[16/10] overflow-hidden bg-[#EFEBE4]">
            <img v-if="featured.featured_image" :src="featured.featured_image" :alt="featured.title" class="w-full h-full object-cover transition-transform duration-[1200ms] group-hover:scale-[1.03]" />
            <div v-else class="w-full h-full flex items-center justify-center font-serif text-7xl text-[#D8CCBC]">{{ featured.title?.charAt(0) }}</div>
          </div>
          <div class="lg:col-span-5 space-y-4">
            <p class="text-[12.5px] text-[#A89B8A]"><span v-if="featured.category">{{ featured.category.name }} · </span>{{ formatDate(featured.published_at || featured.created_at) }}</p>
            <h2 class="font-serif text-3xl sm:text-4xl leading-tight text-[#111111]">{{ featured.title }}</h2>
            <p v-if="featured.excerpt" class="text-[15px] text-[#69736E] leading-relaxed line-clamp-3">{{ featured.excerpt }}</p>
            <span class="inline-flex items-center gap-2 text-[14px] font-medium text-[#111111] border-b border-[#111111] pb-0.5">পুরোটা পড়ুন <ArrowRight class="w-4 h-4 transition-transform group-hover:translate-x-1" /></span>
          </div>
        </router-link>

        <!-- Grid -->
        <div v-if="rest.length" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-x-8 gap-y-12">
          <router-link v-for="(post, i) in rest" :key="post.id" :to="`/blog/${post.slug}`" class="group block" v-reveal="{ delay: (i % 3) * 80 }">
            <div class="aspect-[4/3] overflow-hidden bg-[#EFEBE4] mb-4">
              <img v-if="post.featured_image" :src="post.featured_image" :alt="post.title" loading="lazy" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" />
              <div v-else class="w-full h-full flex items-center justify-center font-serif text-5xl text-[#D8CCBC]">{{ post.title?.charAt(0) }}</div>
            </div>
            <p class="text-[12px] text-[#A89B8A] mb-1.5"><span v-if="post.category">{{ post.category.name }} · </span>{{ formatDate(post.published_at || post.created_at) }}</p>
            <h3 class="font-serif text-xl leading-snug text-[#111111] group-hover:underline underline-offset-4 decoration-[#D8CCBC]">{{ post.title }}</h3>
            <p v-if="post.excerpt" class="mt-2 text-[14px] text-[#69736E] leading-relaxed line-clamp-2">{{ post.excerpt }}</p>
          </router-link>
        </div>

        <div v-if="meta && meta.current_page < meta.last_page" class="mt-14 text-center">
          <button type="button" @click="loadMore" :disabled="loadingMore" class="h-11 px-8 border border-[#111111] text-sm rounded-sm hover:bg-[#111111] hover:text-white transition-colors disabled:opacity-50">
            {{ loadingMore ? 'লোড হচ্ছে…' : 'আরও লেখা দেখুন' }}
          </button>
        </div>
      </template>

      <!-- Empty -->
      <div v-else class="py-10 sm:py-16" v-reveal>
        <div class="text-center max-w-md mx-auto">
          <div class="w-16 h-16 mx-auto mb-5 rounded-full bg-white border border-[#E5DED3] flex items-center justify-center"><PenLine class="w-6 h-6 text-[#A89B8A]" /></div>
          <h2 class="font-serif text-2xl text-[#111111] mb-2">{{ search || activeCategory ? 'কোনো লেখা মেলেনি' : 'শীঘ্রই নতুন লেখা আসছে' }}</h2>
          <p class="text-sm text-[#69736E]">{{ search || activeCategory ? 'অন্য শব্দ দিয়ে খুঁজে দেখুন।' : 'স্টাইল টিপস ও নতুন কালেকশনের গল্প নিয়ে খুব শিগগিরই ফিরছি। ততক্ষণ নতুন পোশাকগুলো দেখে নিন।' }}</p>
        </div>
        <div v-if="!search && !activeCategory && products.length" class="mt-12">
          <ProductGrid :products="products" />
        </div>
      </div>
    </div>

    <NewsletterForm :content="{ heading: 'নতুন লেখা ও অফার সবার আগে পান', description: 'মাসে এক-দুইবার — স্টাইল টিপস, নতুন কালেকশন আর বিশেষ ছাড়ের খবর।' }" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Search, ArrowRight, PenLine } from 'lucide-vue-next';
import api from '../../api';
import { useSiteStore } from '../../stores/site';
import ProductGrid from '../../components/public/ProductGrid.vue';
import NewsletterForm from '../../components/public/NewsletterForm.vue';

const site = useSiteStore();
const blog = computed(() => site.pages.blog || {});

const posts = ref([]);
const categories = ref([]);
const meta = ref(null);
const loading = ref(true);
const loadingMore = ref(false);
const search = ref('');
const activeCategory = ref('');
const products = ref([]);

// The newest post is featured only on the unfiltered first view.
const showFeatured = computed(() => !search.value && !activeCategory.value);
const featured = computed(() => (showFeatured.value ? posts.value[0] : null));
const rest = computed(() => (showFeatured.value ? posts.value.slice(1) : posts.value));

const formatDate = (v) => (v ? new Date(v).toLocaleDateString('bn-BD', { year: 'numeric', month: 'long', day: 'numeric' }) : '');

const params = (page) => ({ page, per_page: 10, search: search.value || undefined, category: activeCategory.value || undefined });

const fetchPosts = async (page = 1) => {
  loading.value = true;
  try {
    const res = await api.get('/public/blog', { params: params(page) });
    const { posts: paginated, categories: cats } = res.data.data;
    posts.value = paginated.data || [];
    meta.value = { current_page: paginated.current_page, last_page: paginated.last_page };
    categories.value = cats || [];
    if (!posts.value.length && !products.value.length) loadProducts();
  } catch (err) {
    console.error('Failed to load blog', err);
  } finally {
    loading.value = false;
  }
};

const loadMore = async () => {
  loadingMore.value = true;
  try {
    const res = await api.get('/public/blog', { params: params(meta.value.current_page + 1) });
    const p = res.data.data.posts;
    posts.value.push(...(p.data || []));
    meta.value = { current_page: p.current_page, last_page: p.last_page };
  } finally {
    loadingMore.value = false;
  }
};

const loadProducts = async () => {
  try {
    const res = await api.get('/public/products', { params: { sort: 'newest', per_page: 4 } });
    products.value = res.data.data || [];
  } catch { /* optional */ }
};

const selectCategory = (slug) => { activeCategory.value = slug; fetchPosts(1); };

onMounted(() => fetchPosts(1));
</script>
