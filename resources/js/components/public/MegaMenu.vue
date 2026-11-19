<template>
  <div
    class="absolute top-full inset-x-0 z-50 bg-white border-t border-black/5 shadow-[0_30px_60px_-30px_rgba(17,17,17,0.25)]"
    @mouseenter="$emit('mouseenter')"
    @mouseleave="$emit('mouseleave')"
  >
    <div class="max-w-7xl mx-auto px-8 py-8 flex gap-12">
      <!-- Category links -->
      <div class="flex-1 min-w-0 flex flex-col">
        <div class="grid gap-x-8 gap-y-6" :style="{ gridTemplateColumns: `repeat(${Math.min(columns.length || 1, 4)}, minmax(0, 1fr))` }">
          <div v-for="(col, ci) in columns" :key="ci">
            <h4 class="text-[12.5px] text-[#A89B8A] mb-3">{{ col.title }}</h4>
            <ul class="space-y-2">
              <li v-for="(link, li) in col.links || []" :key="li">
                <SiteLink :to="link.url" class="text-[14px] text-[#111111]/75 hover:text-[#111111] transition-colors" @click="$emit('close')">
                  {{ link.label }}
                </SiteLink>
              </li>
            </ul>
          </div>
        </div>
        <SiteLink :to="item.url" class="group mt-auto pt-7 inline-flex items-center gap-1.5 text-[13.5px] font-medium text-[#111111] self-start" @click="$emit('close')">
          সব পণ্য দেখুন
          <ArrowRight class="w-4 h-4 transition-transform group-hover:translate-x-1" />
        </SiteLink>
      </div>

      <!-- Product photos -->
      <div v-if="showProducts" class="shrink-0" :style="{ width: `${productCount * 168 + (productCount - 1) * 16}px` }">
        <div class="flex items-center justify-between mb-3">
          <h4 class="text-[12.5px] text-[#A89B8A]">{{ productsConfig.title }}</h4>
          <SiteLink :to="productsLink" class="text-[12.5px] text-[#111111]/60 hover:text-[#111111]" @click="$emit('close')">সব দেখুন</SiteLink>
        </div>
        <div class="flex gap-4">
          <template v-if="products.length">
            <router-link
              v-for="(p, i) in products"
              :key="p.id"
              :to="`/product/${p.slug}`"
              class="group w-[168px] shrink-0 mega-card"
              :style="{ animationDelay: `${i * 60}ms` }"
              @click="$emit('close')"
            >
              <div class="aspect-[3/4] overflow-hidden bg-[#F2EEE7]">
                <img :src="p.featured_image" :alt="p.name" class="w-full h-full object-cover object-top transition-transform duration-700 group-hover:scale-[1.04]" />
              </div>
              <p class="mt-2 text-[13px] text-[#111111] leading-snug line-clamp-1">{{ p.name }}</p>
              <p class="text-[12.5px] text-[#69736E] tabular-nums">{{ taka(effective(p)) }}</p>
            </router-link>
          </template>
          <template v-else>
            <div v-for="n in productCount" :key="n" class="w-[168px] shrink-0"><div class="skeleton aspect-[3/4]"></div><div class="skeleton h-3 mt-2 w-3/4"></div></div>
          </template>
        </div>
      </div>

      <!-- Fallback text card (only when product photos are switched off) -->
      <SiteLink
        v-else-if="promo?.title"
        :to="promo.button_url || item.url"
        class="w-80 shrink-0 p-6 bg-[#F7F5F0] flex flex-col"
        @click="$emit('close')"
      >
        <img v-if="promo.image" :src="promo.image" alt="" class="w-full aspect-video object-cover mb-4" />
        <h5 class="font-serif text-lg text-[#111111]">{{ promo.title }}</h5>
        <p v-if="promo.text" class="mt-2 text-[13px] text-[#69736E] leading-relaxed">{{ promo.text }}</p>
        <span v-if="promo.button_text" class="mt-auto pt-4 text-[13px] font-medium text-[#111111]">{{ promo.button_text }} →</span>
      </SiteLink>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { ArrowRight } from 'lucide-vue-next';
import SiteLink from '../common/SiteLink.vue';
import { taka } from '../../utils/bn';
import { useMegaProducts, megaSort, megaCount } from '../../utils/megaProducts';

const props = defineProps({
  item: { type: Object, required: true },
  promo: { type: Object, default: () => ({}) },
  productsConfig: { type: Object, default: () => ({ title: 'নতুন এসেছে', source: 'new', count: 3 }) },
});
defineEmits(['mouseenter', 'mouseleave', 'close']);

const columns = computed(() => props.item.mega || []);
const showProducts = computed(() => (props.productsConfig?.source || 'new') !== 'none');
const productCount = computed(() => megaCount(props.productsConfig));
const productsLink = computed(() => {
  const sort = megaSort(props.productsConfig);
  return sort === 'newest' ? '/collection/new-arrivals' : `/collections?sort=${sort}`;
});
const products = useMegaProducts(props.productsConfig);
const effective = (p) => (Number(p.sale_price) > 0 && Number(p.sale_price) < Number(p.price) ? p.sale_price : p.price);
</script>

<style scoped>
.mega-card { animation: mega-in 0.5s cubic-bezier(0.22, 1, 0.36, 1) both; }
@keyframes mega-in { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: none; } }
@media (prefers-reduced-motion: reduce) { .mega-card { animation: none; } }
</style>
