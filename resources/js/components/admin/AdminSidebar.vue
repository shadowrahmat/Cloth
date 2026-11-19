<template>
  <div>
    <!-- Mobile backdrop -->
    <div
      v-if="mobileOpen"
      class="fixed inset-0 z-40 bg-black/50 backdrop-blur-xs lg:hidden"
      @click="$emit('close-mobile')"
    ></div>

    <aside
      :class="[
        'fixed lg:sticky top-0 left-0 z-50 h-screen flex flex-col shrink-0 select-none',
        'bg-[#14382A] text-emerald-50 border-r border-emerald-950/60 transition-[width,transform] duration-200 ease-out',
        mobileOpen ? 'translate-x-0 w-72' : '-translate-x-full lg:translate-x-0',
        !mobileOpen && (collapsed ? 'lg:w-[72px]' : 'lg:w-64'),
      ]"
      aria-label="Admin navigation"
    >
      <!-- Brand -->
      <div :class="['h-16 flex items-center shrink-0 bg-[#0F2D21] border-b border-emerald-950/70', collapsed ? 'justify-center px-2' : 'justify-between px-4']">
        <router-link to="/admin" class="flex items-center min-w-0" title="ড্যাশবোর্ড">
          <img v-if="!collapsed" src="/images/logo-white.png" alt="DRAWER CLOTHING" class="h-9 w-auto max-w-[150px] object-contain" />
          <span v-else class="w-9 h-9 rounded-lg bg-[#16A36A] text-white font-bold flex items-center justify-center">D</span>
        </router-link>
        <button
          type="button"
          @click="$emit('close-mobile')"
          class="p-1.5 rounded-md text-emerald-300 hover:text-white hover:bg-white/10 lg:hidden"
          aria-label="মেনু বন্ধ করুন"
        >
          <X class="w-5 h-5" />
        </button>
      </div>

      <!-- Search trigger -->
      <div :class="['shrink-0 pt-3 pb-1', collapsed ? 'px-3' : 'px-3']">
        <button
          type="button"
          @click="ui.paletteOpen = true"
          :class="[
            'w-full flex items-center gap-2 rounded-lg bg-white/[0.07] hover:bg-white/[0.12] text-emerald-200/80 hover:text-white text-xs transition-colors',
            collapsed ? 'justify-center h-10' : 'px-3 h-9',
          ]"
          title="খুঁজুন বা যেকোনো পেজে যান (Ctrl + K)"
        >
          <Search class="w-4 h-4 shrink-0" />
          <template v-if="!collapsed">
            <span class="flex-1 text-left">খুঁজুন / পেজে যান…</span>
            <kbd class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-black/20 text-emerald-300/80">Ctrl K</kbd>
          </template>
        </button>
      </div>

      <!-- Navigation -->
      <nav ref="navRef" class="flex-1 overflow-y-auto overscroll-contain px-3 py-2 space-y-1 text-[13px] [scrollbar-width:thin] [scrollbar-color:rgba(255,255,255,0.18)_transparent]" @scroll="saveScroll">
        <div v-for="group in visibleGroups" :key="group.key" class="pt-2">
          <!-- Group heading (folds the group) -->
          <button
            v-if="!collapsed"
            type="button"
            @click="ui.toggleGroup(group.key)"
            class="w-full flex items-center justify-between px-2 py-1.5 text-[11px] font-semibold tracking-wide text-emerald-300/70 hover:text-emerald-100 rounded"
            :aria-expanded="isGroupOpen(group)"
          >
            <span>{{ group.label }}</span>
            <span class="flex items-center gap-1.5">
              <span v-if="!isGroupOpen(group) && groupAlertCount(group)" class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
              <ChevronDown :class="['w-3.5 h-3.5 transition-transform', !isGroupOpen(group) && '-rotate-90']" />
            </span>
          </button>
          <div v-else class="mx-auto my-1.5 w-6 border-t border-emerald-800/70"></div>

          <div v-show="collapsed || isGroupOpen(group)" class="space-y-0.5 mt-0.5">
            <router-link
              v-for="item in group.items"
              :key="item.to"
              :to="item.to"
              @click="$emit('close-mobile')"
              :title="collapsed ? `${item.label} (${item.hint})` : item.hint"
              :class="[
                'group relative flex items-center gap-3 rounded-lg transition-colors',
                collapsed ? 'justify-center h-10' : 'px-2.5 py-2',
                isActive(item)
                  ? 'bg-[#16A36A] text-white font-semibold shadow-sm'
                  : 'text-emerald-100/85 hover:bg-white/[0.08] hover:text-white',
              ]"
              :aria-current="isActive(item) ? 'page' : undefined"
            >
              <component :is="item.icon" class="w-[18px] h-[18px] shrink-0" :stroke-width="isActive(item) ? 2.2 : 1.8" />
              <span v-if="!collapsed" class="flex-1 truncate">{{ item.label }}</span>

              <!-- Counter -->
              <template v-if="badgeValue(item)">
                <span
                  v-if="!collapsed"
                  :class="['min-w-[22px] px-1.5 py-0.5 rounded-full text-[10px] font-bold text-center leading-none', badgeClass(item)]"
                >
                  {{ badgeValue(item) }}
                </span>
                <span
                  v-else-if="item.badgeTone"
                  :class="['absolute top-1.5 right-2 w-2 h-2 rounded-full ring-2 ring-[#14382A]', dotClass(item)]"
                ></span>
              </template>
            </router-link>
          </div>
        </div>
      </nav>

      <!-- Footer: store link, collapse, profile -->
      <div class="shrink-0 border-t border-emerald-950/70 bg-[#0F2D21]">
        <div :class="['flex', collapsed ? 'flex-col items-center gap-1 py-2' : 'items-center justify-between px-3 py-2']">
          <a
            href="/"
            target="_blank"
            rel="noopener"
            :class="['flex items-center gap-2 text-xs text-emerald-300/90 hover:text-white rounded-md hover:bg-white/10', collapsed ? 'p-2' : 'px-2 py-1.5']"
            title="ওয়েবসাইট দেখুন (নতুন ট্যাবে)"
          >
            <ExternalLink class="w-4 h-4" />
            <span v-if="!collapsed">ওয়েবসাইট দেখুন</span>
          </a>
          <button
            type="button"
            @click="ui.toggleSidebar()"
            class="hidden lg:flex p-2 rounded-md text-emerald-300/80 hover:text-white hover:bg-white/10"
            :title="collapsed ? 'মেনু বড় করুন' : 'মেনু ছোট করুন'"
          >
            <ChevronsRight v-if="collapsed" class="w-4 h-4" />
            <ChevronsLeft v-else class="w-4 h-4" />
          </button>
        </div>

        <div :class="['flex items-center border-t border-emerald-950/70', collapsed ? 'flex-col gap-2 py-3' : 'justify-between px-3 py-3']">
          <div class="flex items-center gap-2.5 min-w-0">
            <div class="w-8 h-8 rounded-full bg-emerald-700 text-white text-xs font-bold flex items-center justify-center shrink-0 ring-1 ring-emerald-500/60" :title="userName">
              {{ userInitial }}
            </div>
            <div v-if="!collapsed" class="min-w-0">
              <p class="text-xs font-medium text-white truncate">{{ userName }}</p>
              <p class="text-[10px] text-emerald-300/80 uppercase tracking-wider truncate">{{ roleLabel }}</p>
            </div>
          </div>
          <button
            type="button"
            @click="handleLogout"
            class="p-2 rounded-md text-emerald-300/80 hover:text-white hover:bg-rose-500/20"
            title="লগআউট"
          >
            <LogOut class="w-4 h-4" />
          </button>
        </div>
      </div>
    </aside>
  </div>
</template>

<script setup>
import { computed, ref, onMounted, nextTick } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { Search, X, ChevronDown, ChevronsLeft, ChevronsRight, ExternalLink, LogOut } from 'lucide-vue-next';
import { useAuthStore } from '../../stores/auth';
import { useAdminUiStore } from '../../stores/adminUi';
import { navGroups, findNavItem, bnNumber } from '../../admin-nav';

const props = defineProps({
  mobileOpen: { type: Boolean, default: false },
});
defineEmits(['close-mobile']);

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const ui = useAdminUiStore();
const navRef = ref(null);

// The mobile drawer always shows full labels.
const collapsed = computed(() => ui.sidebarCollapsed && !props.mobileOpen);

const visibleGroups = computed(() =>
  navGroups
    .map((g) => ({ ...g, items: g.items.filter((i) => !i.permission || authStore.hasPermission(i.permission)) }))
    .filter((g) => g.items.length),
);

const activeItem = computed(() => findNavItem(route.path));
const isActive = (item) => activeItem.value?.to === item.to;

// A folded group still opens when it contains the current page.
const isGroupOpen = (group) =>
  !ui.closedGroups.includes(group.key) || group.items.some((i) => isActive(i));

const badgeValue = (item) => {
  const n = item.badge ? ui.badges[item.badge] : 0;
  return n > 0 ? bnNumber(n > 99 ? '99+' : n) : '';
};
const TONE = {
  warn: ['bg-amber-400 text-amber-950', 'bg-amber-400'],
  danger: ['bg-rose-400 text-rose-950', 'bg-rose-400'],
  info: ['bg-sky-400 text-sky-950', 'bg-sky-400'],
};
const badgeClass = (item) => TONE[item.badgeTone]?.[0] || 'bg-white/10 text-emerald-200';
const dotClass = (item) => TONE[item.badgeTone]?.[1] || '';
const groupAlertCount = (group) => group.items.filter((i) => i.badgeTone && ui.badges[i.badge] > 0).length;

const userName = computed(() => authStore.adminUser?.name || 'Administrator');
const userInitial = computed(() => userName.value.charAt(0).toUpperCase());
const roleLabel = computed(() => ({ admin: 'সুপার অ্যাডমিন', manager: 'ম্যানেজার' }[authStore.adminUser?.role] || authStore.adminUser?.role || ''));

const handleLogout = async () => {
  if (!window.confirm('লগআউট করতে চান?')) return;
  await authStore.adminLogout();
  router.push('/admin/login');
};

const saveScroll = () => {
  try { sessionStorage.setItem('admin_sidebar_scroll', String(navRef.value?.scrollTop || 0)); } catch { /* ignore */ }
};

onMounted(() => {
  nextTick(() => {
    try {
      const saved = Number(sessionStorage.getItem('admin_sidebar_scroll') || 0);
      if (navRef.value) navRef.value.scrollTop = saved;
    } catch { /* ignore */ }
  });
});
</script>
