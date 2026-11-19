<template>
  <footer class="bg-[#111111] text-white mt-auto">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-16 pb-10">
      <div class="grid grid-cols-1 md:grid-cols-12 gap-10 pb-12 border-b border-white/10">
        <!-- Brand + contact -->
        <div class="md:col-span-4 space-y-5" v-reveal>
          <router-link to="/" class="inline-block">
            <img v-if="logo" :src="logo" :alt="site.siteName" class="h-12 w-auto max-w-[190px] object-contain" />
            <span v-else class="font-serif text-2xl">{{ site.siteName }}</span>
          </router-link>
          <p v-if="site.footer.description" class="text-[13px] text-white/60 leading-relaxed max-w-sm">{{ site.footer.description }}</p>

          <ul class="space-y-2 text-[13px] text-white/60">
            <li v-if="site.contact.address" class="flex gap-2.5"><MapPin class="w-4 h-4 mt-0.5 shrink-0 text-white/40" /><span class="whitespace-pre-line">{{ site.contact.address }}</span></li>
            <li v-if="site.contact.phone"><a :href="`tel:${phoneDigits(site.contact.phone)}`" class="flex gap-2.5 hover:text-white transition-colors"><Phone class="w-4 h-4 shrink-0 text-white/40" />{{ site.contact.phone }}</a></li>
            <li v-if="site.contact.email"><a :href="`mailto:${site.contact.email}`" class="flex gap-2.5 hover:text-white transition-colors"><Mail class="w-4 h-4 shrink-0 text-white/40" />{{ site.contact.email }}</a></li>
          </ul>

          <div v-if="site.social.length" class="flex gap-2 pt-1">
            <a
              v-for="s in site.social"
              :key="s.platform"
              :href="s.url"
              target="_blank"
              rel="noopener"
              class="w-9 h-9 rounded-full border border-white/15 flex items-center justify-center text-white/70 hover:text-[#111111] hover:bg-white hover:border-white transition-all duration-300"
              :aria-label="s.platform"
            >
              <SocialIcon :platform="s.platform" class="w-4 h-4" />
            </a>
          </div>
        </div>

        <!-- Link columns -->
        <!-- Link columns: fold-out accordion on phones, regular columns from md up -->
        <div class="md:col-span-8 md:grid md:grid-cols-3 md:gap-8 divide-y divide-white/10 md:divide-y-0 border-y border-white/10 md:border-0">
          <div v-for="(col, ci) in site.footer.columns" :key="ci" class="md:space-y-3.5" v-reveal="{ delay: ci * 80 }">
            <button
              type="button"
              @click="openCol = openCol === ci ? null : ci"
              class="md:pointer-events-none w-full flex items-center justify-between py-4 md:py-0 text-left"
              :aria-expanded="openCol === ci"
            >
              <h5 class="text-[14px] md:text-[13.5px] font-semibold text-[#D8CCBC]">{{ col.title }}</h5>
              <ChevronDown :class="['md:hidden w-4 h-4 text-white/40 transition-transform duration-300', openCol === ci && 'rotate-180']" />
            </button>
            <div class="grid md:block transition-[grid-template-rows] duration-300 ease-out" :style="isDesktop ? {} : { gridTemplateRows: openCol === ci ? '1fr' : '0fr' }">
              <ul class="overflow-hidden md:overflow-visible space-y-3 md:space-y-2.5 text-[14px] md:text-[13px] text-white/60">
                <li v-for="(link, li) in col.links || []" :key="li" :class="li === (col.links.length - 1) && 'pb-4 md:pb-0'">
                  <SiteLink :to="link.url" class="hover:text-white transition-colors">{{ link.label }}</SiteLink>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <div class="pt-8 flex flex-col-reverse sm:flex-row items-center justify-between gap-4 text-[12px] text-white/40">
        <p>{{ copyright }}</p>
        <div v-if="site.footer.payment_methods?.length" class="flex flex-wrap justify-center gap-2">
          <span v-for="m in site.footer.payment_methods" :key="m" class="px-2.5 py-1 rounded border border-white/10 text-white/55">{{ m }}</span>
        </div>
      </div>
    </div>
  </footer>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { MapPin, Phone, Mail, ChevronDown } from 'lucide-vue-next';
import { useSiteStore, phoneDigits } from '../../stores/site';
import SiteLink from '../common/SiteLink.vue';
import SocialIcon from '../common/SocialIcon.vue';

const site = useSiteStore();
const logo = computed(() => site.branding.logo_light || site.branding.logo);

// Footer columns fold on phones only.
const openCol = ref(null);
const mq = typeof window !== 'undefined' ? window.matchMedia('(min-width: 768px)') : null;
const isDesktop = ref(mq?.matches ?? true);
const onMq = (e) => { isDesktop.value = e.matches; };
onMounted(() => mq?.addEventListener('change', onMq));
onBeforeUnmount(() => mq?.removeEventListener('change', onMq));
const copyright = computed(() =>
  (site.footer.copyright || `© {year} ${site.siteName}`).replace('{year}', new Date().getFullYear()),
);
</script>
