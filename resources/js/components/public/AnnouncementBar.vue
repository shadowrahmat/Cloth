<template>
  <Transition name="fade">
    <div
      v-if="show"
      class="relative z-40 text-[11.5px] sm:text-xs font-medium tracking-wide"
      :style="{ backgroundColor: a.bg_color || '#111111', color: a.text_color || '#FFFFFF' }"
    >
      <div class="max-w-7xl mx-auto h-9 px-10 flex items-center justify-center gap-3 min-w-0">
        <span class="truncate">{{ a.text }}</span>
        <SiteLink
          v-if="a.link_text && a.link_url"
          :to="a.link_url"
          class="hidden sm:inline shrink-0 underline underline-offset-4 font-semibold hover:opacity-80 transition-opacity"
        >
          {{ a.link_text }}
        </SiteLink>
      </div>
      <button
        type="button"
        @click="dismiss"
        class="absolute right-2 top-1/2 -translate-y-1/2 p-1.5 opacity-60 hover:opacity-100 transition-opacity"
        aria-label="ঘোষণা বন্ধ করুন"
      >
        <X class="w-3.5 h-3.5" />
      </button>
    </div>
  </Transition>
</template>

<script setup>
import { ref, computed } from 'vue';
import { X } from 'lucide-vue-next';
import { useSiteStore } from '../../stores/site';
import SiteLink from '../common/SiteLink.vue';

const site = useSiteStore();
const a = computed(() => site.announcement);

// Dismissal is remembered for this text only, so a new announcement shows again.
const storageKey = computed(() => 'announcement_dismissed:' + (a.value.text || ''));
const dismissed = ref((() => { try { return sessionStorage.getItem(storageKey.value) === '1'; } catch { return false; } })());
const show = computed(() => a.value.enabled !== false && a.value.text && !dismissed.value);

const dismiss = () => {
  dismissed.value = true;
  try { sessionStorage.setItem(storageKey.value, '1'); } catch { /* ignore */ }
};
</script>
