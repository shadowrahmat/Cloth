<template>
  <div v-if="page" class="py-12 sm:py-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
    <div class="border-b border-[#D8CCBC]/40 pb-6">
      <h1 class="font-serif text-3xl sm:text-4xl text-[#111111]">{{ page.title }}</h1>
    </div>

    <div class="prose prose-sm max-w-none text-[#111111]/80 font-light leading-relaxed whitespace-pre-line">
      {{ page.content }}
    </div>
  </div>

  <div v-else-if="loading" class="py-24 text-center text-xs text-[#A89B8A]">
    Loading page...
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import api from '../../api';

const route = useRoute();
const page = ref(null);
const loading = ref(true);

const loadPage = async () => {
  loading.value = true;
  try {
    const res = await api.get(`/public/pages/${route.params.slug}`);
    page.value = res.data.data;
  } catch (err) {
    console.error('Failed to load page', err);
  } finally {
    loading.value = false;
  }
};

watch(() => route.params.slug, () => loadPage());
onMounted(() => loadPage());
</script>
