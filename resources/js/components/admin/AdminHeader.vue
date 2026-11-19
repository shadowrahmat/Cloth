<template>
  <header class="h-16 bg-white/95 backdrop-blur border-b border-[#E7EBE8] flex items-center gap-3 px-4 sm:px-6 shrink-0 z-30 sticky top-0">
    <!-- Mobile menu -->
    <button
      type="button"
      @click="$emit('toggle-mobile')"
      class="p-2 -ml-2 text-[#4B5450] hover:text-[#171C1A] hover:bg-[#F2F4F3] rounded-lg lg:hidden"
      aria-label="মেনু খুলুন"
    >
      <Menu class="w-5 h-5" />
    </button>

    <!-- Breadcrumb -->
    <nav class="flex items-center gap-1.5 min-w-0 text-sm" aria-label="Breadcrumb">
      <span v-if="crumb.group" class="hidden sm:inline text-[#69736E] whitespace-nowrap">{{ crumb.group }}</span>
      <ChevronRight v-if="crumb.group" class="hidden sm:block w-4 h-4 text-[#C4CBC8] shrink-0" />
      <h1 class="font-semibold text-[#171C1A] truncate">{{ crumb.page }}</h1>
    </nav>

    <div class="ml-auto flex items-center gap-1.5 sm:gap-2">
      <!-- Search -->
      <button
        type="button"
        @click="ui.paletteOpen = true"
        class="hidden md:flex items-center gap-2 w-56 lg:w-72 h-9 px-3 rounded-lg border border-[#E7EBE8] bg-[#F7F8FA] hover:bg-white hover:border-[#CFD6D3] text-xs text-[#69736E] transition-colors"
      >
        <Search class="w-4 h-4" />
        <span class="flex-1 text-left">অর্ডার, পণ্য, গ্রাহক খুঁজুন…</span>
        <kbd class="text-[10px] font-mono px-1.5 py-0.5 rounded border border-[#E7EBE8] bg-white">Ctrl K</kbd>
      </button>
      <button
        type="button"
        @click="ui.paletteOpen = true"
        class="md:hidden p-2 text-[#4B5450] hover:bg-[#F2F4F3] rounded-lg"
        aria-label="খুঁজুন"
      >
        <Search class="w-5 h-5" />
      </button>

      <!-- Quick create -->
      <div v-if="createItems.length" class="relative" ref="createRef">
        <button
          type="button"
          @click="toggle('create')"
          class="flex items-center gap-1.5 h-9 px-3 rounded-lg bg-[#14382A] hover:bg-[#16A36A] text-white text-xs font-semibold transition-colors"
          :aria-expanded="open === 'create'"
        >
          <Plus class="w-4 h-4" />
          <span class="hidden sm:inline">নতুন</span>
        </button>
        <div v-if="open === 'create'" class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-xl border border-[#E7EBE8] py-1.5 z-50">
          <router-link
            v-for="c in createItems"
            :key="c.to"
            :to="c.to"
            @click="open = null"
            class="flex items-center justify-between px-3.5 py-2 text-sm text-[#171C1A] hover:bg-emerald-50"
          >
            <span>{{ c.label }}</span>
            <span class="text-[11px] text-[#9AA39F]">{{ c.hint }}</span>
          </router-link>
        </div>
      </div>

      <!-- Notifications -->
      <div class="relative" ref="bellRef">
        <button
          type="button"
          @click="toggle('bell')"
          class="relative p-2 text-[#4B5450] hover:text-[#171C1A] hover:bg-[#F2F4F3] rounded-lg"
          :aria-label="`নোটিফিকেশন (${ui.alertCount})`"
          :aria-expanded="open === 'bell'"
        >
          <Bell class="w-5 h-5" />
          <span
            v-if="ui.alertCount"
            class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 rounded-full bg-rose-500 text-white text-[10px] font-bold flex items-center justify-center ring-2 ring-white"
          >
            {{ ui.alertCount > 99 ? '99+' : bnNumber(ui.alertCount) }}
          </span>
        </button>

        <div v-if="open === 'bell'" class="absolute right-0 mt-2 w-[min(20rem,calc(100vw-2rem))] bg-white rounded-xl shadow-xl border border-[#E7EBE8] z-50 overflow-hidden">
          <div class="px-4 py-3 border-b border-[#E7EBE8] flex items-center justify-between">
            <span class="text-sm font-semibold text-[#171C1A]">যা করা বাকি</span>
            <button type="button" @click="ui.fetchBadges({ force: true })" class="text-[11px] text-[#16A36A] hover:underline">রিফ্রেশ</button>
          </div>
          <div v-if="ui.alerts.length" class="divide-y divide-[#F0F2F1]">
            <router-link
              v-for="a in ui.alerts"
              :key="a.key"
              :to="a.to"
              @click="open = null"
              class="flex items-center gap-3 px-4 py-3 hover:bg-[#F7F8FA]"
            >
              <span :class="['w-2 h-2 rounded-full shrink-0', TONE_DOT[a.tone]]"></span>
              <span class="flex-1 text-sm text-[#171C1A]"><strong>{{ bnNumber(a.count) }}</strong> {{ a.label }}</span>
              <ChevronRight class="w-4 h-4 text-[#C4CBC8]" />
            </router-link>
          </div>
          <div v-else class="px-4 py-8 text-center text-sm text-[#69736E]">
            <p class="text-2xl mb-1">✓</p>
            সব কাজ শেষ, এখন কিছু বাকি নেই।
          </div>
        </div>
      </div>

      <!-- Profile -->
      <div class="relative" ref="profileRef">
        <button
          type="button"
          @click="toggle('profile')"
          class="flex items-center gap-2 pl-1 pr-1 sm:pr-2 h-9 rounded-lg hover:bg-[#F2F4F3]"
          :aria-expanded="open === 'profile'"
        >
          <span class="w-8 h-8 rounded-full bg-[#14382A] text-white text-xs font-bold flex items-center justify-center">{{ userInitial }}</span>
          <ChevronDown class="hidden sm:block w-4 h-4 text-[#69736E]" />
        </button>
        <div v-if="open === 'profile'" class="absolute right-0 mt-2 w-60 bg-white rounded-xl shadow-xl border border-[#E7EBE8] py-1.5 z-50">
          <div class="px-4 py-2.5 border-b border-[#F0F2F1]">
            <p class="text-sm font-semibold text-[#171C1A] truncate">{{ authStore.adminUser?.name || 'Administrator' }}</p>
            <p class="text-xs text-[#69736E] truncate">{{ authStore.adminUser?.email }}</p>
          </div>
          <a href="/" target="_blank" rel="noopener" class="flex items-center gap-2.5 px-4 py-2 text-sm text-[#171C1A] hover:bg-[#F7F8FA]">
            <ExternalLink class="w-4 h-4 text-[#69736E]" /> ওয়েবসাইট দেখুন
          </a>
          <router-link v-if="authStore.hasPermission('view_users')" to="/admin/users" @click="open = null" class="flex items-center gap-2.5 px-4 py-2 text-sm text-[#171C1A] hover:bg-[#F7F8FA]">
            <UserCog class="w-4 h-4 text-[#69736E]" /> পাসওয়ার্ড ও স্টাফ
          </router-link>
          <router-link v-if="authStore.hasPermission('view_settings')" to="/admin/settings" @click="open = null" class="flex items-center gap-2.5 px-4 py-2 text-sm text-[#171C1A] hover:bg-[#F7F8FA]">
            <Settings class="w-4 h-4 text-[#69736E]" /> স্টোর সেটিংস
          </router-link>
          <div class="border-t border-[#F0F2F1] mt-1.5 pt-1.5">
            <button type="button" @click="handleLogout" class="w-full flex items-center gap-2.5 px-4 py-2 text-sm text-rose-600 hover:bg-rose-50">
              <LogOut class="w-4 h-4" /> লগআউট
            </button>
          </div>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { Menu, Search, Plus, Bell, ChevronRight, ChevronDown, ExternalLink, UserCog, Settings, LogOut } from 'lucide-vue-next';
import { useAuthStore } from '../../stores/auth';
import { useAdminUiStore } from '../../stores/adminUi';
import { findNavItem, quickCreate, bnNumber } from '../../admin-nav';

const props = defineProps({
  title: { type: String, default: '' },
});
defineEmits(['toggle-mobile']);

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const ui = useAdminUiStore();

const TONE_DOT = { warn: 'bg-amber-400', danger: 'bg-rose-500', info: 'bg-sky-500' };

const crumb = computed(() => {
  const item = findNavItem(route.path);
  if (!item) return { group: '', page: props.title || 'অ্যাডমিন' };
  // Sub-pages (e.g. /resources/x/create) keep the section, show their own title.
  const isExact = route.path.replace(/\/+$/, '') === item.to;
  return { group: item.group, page: isExact ? item.label : (props.title || item.label) };
});

const createItems = computed(() => quickCreate.filter((c) => !c.permission || authStore.hasPermission(c.permission)));
const userInitial = computed(() => (authStore.adminUser?.name || 'A').charAt(0).toUpperCase());

// --- dropdowns: one open at a time, close on outside click / Esc / navigation
const open = ref(null);
const createRef = ref(null);
const bellRef = ref(null);
const profileRef = ref(null);
const toggle = (name) => {
  open.value = open.value === name ? null : name;
  if (name === 'bell' && open.value) ui.fetchBadges({ force: true });
};
const onDocClick = (e) => {
  const refs = { create: createRef, bell: bellRef, profile: profileRef };
  const el = refs[open.value]?.value;
  if (el && !el.contains(e.target)) open.value = null;
};
const onEsc = (e) => { if (e.key === 'Escape') open.value = null; };
onMounted(() => {
  document.addEventListener('mousedown', onDocClick);
  document.addEventListener('keydown', onEsc);
});
onBeforeUnmount(() => {
  document.removeEventListener('mousedown', onDocClick);
  document.removeEventListener('keydown', onEsc);
});
watch(() => route.fullPath, () => { open.value = null; });

const handleLogout = async () => {
  open.value = null;
  await authStore.adminLogout();
  router.push('/admin/login');
};
</script>
