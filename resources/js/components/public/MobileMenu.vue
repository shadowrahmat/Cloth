<template>
  <Teleport to="body">
    <Transition name="fade">
      <div v-if="isOpen" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-[2px]" @click="$emit('close')"></div>
    </Transition>
    <Transition
      enter-active-class="transition duration-350 ease-[cubic-bezier(0.22,1,0.36,1)]"
      enter-from-class="-translate-x-full"
      leave-active-class="transition duration-250 ease-in"
      leave-to-class="-translate-x-full"
    >
      <aside
        v-if="isOpen"
        data-lenis-prevent
        class="fixed inset-y-0 left-0 z-50 w-[88%] max-w-sm bg-[#F7F5F0] flex flex-col shadow-2xl"
        role="dialog"
        aria-modal="true"
        aria-label="মেনু"
      >
        <div class="h-16 px-5 flex items-center justify-between border-b border-[#D8CCBC]/40 shrink-0">
          <router-link to="/" @click="$emit('close')">
            <img v-if="site.branding.logo" :src="site.branding.logo" :alt="site.siteName" class="h-10 w-auto max-w-[150px] object-contain" />
            <span v-else class="font-serif text-xl">{{ site.siteName }}</span>
          </router-link>
          <button type="button" @click="$emit('close')" class="p-2 -mr-2 rounded-full hover:bg-black/5" aria-label="মেনু বন্ধ করুন">
            <X class="w-5 h-5" :stroke-width="1.5" />
          </button>
        </div>

        <nav class="flex-1 overflow-y-auto px-5 py-3" aria-label="মোবাইল মেনু">
          <div v-for="(item, i) in site.header.menu" :key="i" class="border-b border-[#D8CCBC]/30">
            <div class="flex items-center">
              <SiteLink :to="item.url" class="flex-1 py-3.5 text-[15px] font-medium text-[#111111]" @click="$emit('close')">
                {{ item.label }}
              </SiteLink>
              <button
                v-if="item.mega?.length"
                type="button"
                @click="open = open === i ? null : i"
                class="p-2.5 -mr-2 rounded-full hover:bg-black/5"
                :aria-expanded="open === i"
                :aria-label="`${item.label} খুলুন`"
              >
                <ChevronDown :class="['w-4.5 h-4.5 transition-transform duration-300', open === i && 'rotate-180']" />
              </button>
            </div>

            <!-- Sub-menu (accordion, animated height) -->
            <div
              v-if="item.mega?.length"
              class="grid transition-[grid-template-rows] duration-300 ease-out"
              :style="{ gridTemplateRows: open === i ? '1fr' : '0fr' }"
            >
              <div class="overflow-hidden">
                <div class="pb-4 space-y-4">
                  <div v-for="(col, ci) in item.mega" :key="ci">
                    <p class="text-[11px] font-semibold tracking-normal text-[#A89B8A] mb-1.5">{{ col.title }}</p>
                    <div class="grid grid-cols-2 gap-x-3">
                      <SiteLink
                        v-for="(link, li) in col.links || []"
                        :key="li"
                        :to="link.url"
                        class="py-1.5 text-[14px] text-[#111111]/80 active:text-[#111111]"
                        @click="$emit('close')"
                      >
                        {{ link.label }}
                      </SiteLink>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="py-4 space-y-1 text-[14px] text-[#69736E]">
            <router-link to="/track-order" class="flex items-center gap-2.5 py-2" @click="$emit('close')"><Truck class="w-4 h-4" /> অর্ডার ট্র্যাক করুন</router-link>
            <router-link to="/wishlist" class="flex items-center gap-2.5 py-2" @click="$emit('close')"><Heart class="w-4 h-4" /> পছন্দের তালিকা</router-link>
            <router-link to="/contact" class="flex items-center gap-2.5 py-2" @click="$emit('close')"><MessageCircle class="w-4 h-4" /> যোগাযোগ</router-link>
          </div>
        </nav>

        <div class="p-5 border-t border-[#D8CCBC]/40 bg-white space-y-3 shrink-0">
          <router-link
            to="/account"
            class="flex items-center justify-center gap-2 w-full py-3 bg-[#111111] text-white text-[13px] font-medium rounded-sm"
            @click="$emit('close')"
          >
            <User class="w-4 h-4" />
            {{ authStore.isCustomerAuthenticated ? 'আমার অ্যাকাউন্ট' : 'লগইন / রেজিস্ট্রেশন' }}
          </router-link>
          <a v-if="site.contact.phone" :href="`tel:${phoneDigits(site.contact.phone)}`" class="flex items-center justify-center gap-2 text-[13px] text-[#69736E]">
            <Phone class="w-3.5 h-3.5" /> {{ site.contact.phone }}
          </a>
        </div>
      </aside>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, watch } from 'vue';
import { X, ChevronDown, Truck, Heart, MessageCircle, User, Phone } from 'lucide-vue-next';
import { useSiteStore, phoneDigits } from '../../stores/site';
import { useAuthStore } from '../../stores/auth';
import SiteLink from '../common/SiteLink.vue';

const props = defineProps({ isOpen: { type: Boolean, default: false } });
defineEmits(['close']);

const site = useSiteStore();
const authStore = useAuthStore();
const open = ref(null);

// Lock page scroll while the drawer is open.
watch(() => props.isOpen, (v) => {
  document.documentElement.style.overflow = v ? 'hidden' : '';
  if (!v) open.value = null;
});
</script>
