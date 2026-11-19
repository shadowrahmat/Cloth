import { ref } from 'vue';
import api from '../api';

// Products shown inside the Shop mega menu. Fetched once (in the background
// after page load) and shared, so the menu opens with photos already there.
const SORT = { new: 'newest', bestseller: 'bestseller', featured: 'featured' };
const cache = {};

export const megaSort = (config) => SORT[config?.source] || 'newest';
export const megaCount = (config) => Math.min(4, Math.max(2, Number(config?.count) || 3));

export function useMegaProducts(config) {
  if (!config || config.source === 'none') return ref([]);
  const key = `${megaSort(config)}:${megaCount(config)}`;
  if (!cache[key]) {
    cache[key] = ref([]);
    api.get('/public/products', { params: { sort: megaSort(config), per_page: megaCount(config) } })
      .then((res) => {
        cache[key].value = (res.data.data || []).filter((p) => p.featured_image).slice(0, megaCount(config));
        // Warm the browser cache for the photos too.
        cache[key].value.forEach((p) => { const img = new Image(); img.src = p.featured_image; });
      })
      .catch(() => {});
  }
  return cache[key];
}
