<template>
  <article v-if="post" class="py-12 sm:py-20">
    <!-- Header -->
    <header class="max-w-3xl mx-auto px-4 sm:px-6 text-center mb-10">
      <router-link to="/blog" class="text-[10px] uppercase tracking-[0.25em] text-[#A89B8A] hover:text-[#111111]">
        ← ব্লগ
      </router-link>
      <p class="mt-6 text-[10px] uppercase tracking-[0.2em] text-[#A89B8A]">
        <span v-if="post.category">{{ post.category.name }} • </span>{{ formatDate(post.published_at || post.created_at) }}
      </p>
      <h1 class="mt-3 font-serif text-3xl sm:text-5xl text-[#111111] leading-tight">{{ post.title }}</h1>
      <p v-if="post.excerpt" class="mt-5 text-sm text-[#111111]/70 font-light leading-relaxed">{{ post.excerpt }}</p>
      <p v-if="post.author" class="mt-4 text-xs text-[#A89B8A]">লিখেছেন {{ post.author.name }}</p>
    </header>

    <div v-if="post.featured_image" class="max-w-5xl mx-auto px-4 sm:px-6 mb-12">
      <img :src="post.featured_image" :alt="post.title" class="w-full max-h-[560px] object-cover border border-[#D8CCBC]/30" />
    </div>

    <!-- Body: stored as plain text from the admin editor, rendered with preserved line breaks -->
    <div class="max-w-2xl mx-auto px-4 sm:px-6 text-[15px] text-[#111111]/85 font-light leading-8 whitespace-pre-line">
      {{ post.content }}
    </div>

    <!-- Related -->
    <section v-if="related.length" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-20 pt-12 border-t border-[#D8CCBC]/40">
      <h2 class="font-serif text-2xl text-[#111111] mb-8">আরও পড়ুন</h2>
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-8">
        <router-link v-for="item in related" :key="item.id" :to="`/blog/${item.slug}`" class="group block">
          <div class="aspect-[4/3] bg-[#F7F5F0] overflow-hidden mb-3 border border-[#D8CCBC]/30">
            <img
              v-if="item.featured_image"
              :src="item.featured_image"
              :alt="item.title"
              loading="lazy"
              class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
            />
          </div>
          <h3 class="font-serif text-lg text-[#111111] leading-snug group-hover:underline underline-offset-4 decoration-[#D8CCBC]">
            {{ item.title }}
          </h3>
        </router-link>
      </div>
    </section>
  </article>

  <LoadingState v-else-if="loading" />

  <EmptyState
    v-else
    title="লেখাটি পাওয়া যায়নি"
    description="লেখাটি সরিয়ে নেওয়া হয়েছে অথবা এখনো প্রকাশিত হয়নি।"
  >
    <template #action>
      <router-link to="/blog" class="px-8 py-3.5 bg-[#111111] text-white text-xs uppercase tracking-[0.2em] font-semibold inline-block">
        ব্লগে ফিরে যান
      </router-link>
    </template>
  </EmptyState>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import api from '../../api';
import LoadingState from '../../components/common/LoadingState.vue';
import EmptyState from '../../components/common/EmptyState.vue';

const route = useRoute();
const post = ref(null);
const related = ref([]);
const loading = ref(true);

const formatDate = (value) =>
  value ? new Date(value).toLocaleDateString('bn-BD', { year: 'numeric', month: 'long', day: 'numeric' }) : '';

const loadPost = async () => {
  loading.value = true;
  post.value = null;
  related.value = [];
  try {
    const res = await api.get(`/public/blog/${route.params.slug}`);
    post.value = res.data.data.post;
    related.value = res.data.data.related || [];
    document.title = `${post.value.seo_title || post.value.title} | ব্লগ`;
  } catch (err) {
    if (err.response?.status !== 404) console.error('Failed to load post', err);
  } finally {
    loading.value = false;
  }
};

watch(() => route.params.slug, (slug) => slug && loadPost());
onMounted(loadPost);
</script>
