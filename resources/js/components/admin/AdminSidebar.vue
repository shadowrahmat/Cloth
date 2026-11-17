<template>
  <div>
    <!-- Mobile Backdrop Overlay -->
    <div
      v-if="mobileOpen"
      class="fixed inset-0 z-40 bg-black/60 backdrop-blur-xs lg:hidden transition-opacity"
      @click="$emit('close-mobile')"
    ></div>

    <!-- Sidebar Element -->
    <aside
      ref="sidebarRef"
      :class="[
        'fixed lg:static top-0 bottom-0 left-0 z-50 flex flex-col shrink-0 select-none transition-all duration-300 ease-in-out',
        'bg-[#14382A] text-white border-r border-emerald-900/60 shadow-xl lg:shadow-none',
        mobileOpen ? 'translate-x-0 w-[288px]' : '-translate-x-full lg:translate-x-0 lg:w-[256px]'
      ]"
    >
      <!-- Top Brand Header -->
      <div class="h-16 px-4 border-b border-emerald-900/80 flex items-center justify-between shrink-0 bg-[#0F2D21]">
        <router-link to="/admin" class="flex items-center gap-2">
          <img
            src="/images/logo-white.png"
            alt="DRAWER CLOTHING"
            class="h-9 sm:h-10 w-auto max-w-[170px] object-contain brightness-110"
          />
        </router-link>

        <div class="flex items-center gap-1">
          <!-- View Storefront link -->
          <router-link
            to="/"
            target="_blank"
            class="p-1.5 text-emerald-300/70 hover:text-white hover:bg-white/10 rounded-md transition-colors"
            title="View Live Storefront"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
            </svg>
          </router-link>

          <!-- Mobile close button -->
          <button
            type="button"
            @click="$emit('close-mobile')"
            class="p-1.5 text-emerald-300/70 hover:text-white hover:bg-white/10 rounded-md lg:hidden transition-colors"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Scrollable Navigation Container with Scroll Memory -->
      <nav
        ref="navContainerRef"
        class="flex-1 overflow-y-auto px-3.5 py-4 space-y-6 text-xs text-emerald-100/80 divide-y divide-emerald-900/50"
        @scroll="saveScrollPosition"
      >
        <!-- SECTION 1: OVERVIEW -->
        <div class="space-y-1">
          <span class="px-3 text-[10px] uppercase tracking-[0.2em] text-emerald-400 font-semibold block mb-2">
            1. Overview
          </span>
          <router-link
            to="/admin"
            exact-active-class="bg-[#16A36A] text-white font-medium shadow-xs"
            class="flex items-center justify-between px-3 py-2 rounded-md hover:bg-white/10 hover:text-white transition-colors"
          >
            <div class="flex items-center gap-2.5">
              <span>📊</span>
              <span>Executive Dashboard</span>
            </div>
          </router-link>

          <router-link
            to="/admin/reports"
            active-class="bg-[#16A36A] text-white font-medium shadow-xs"
            class="flex items-center justify-between px-3 py-2 rounded-md hover:bg-white/10 hover:text-white transition-colors"
          >
            <div class="flex items-center gap-2.5">
              <span>📈</span>
              <span>Sales Reports</span>
            </div>
          </router-link>

          <router-link
            to="/admin/reports/sales"
            active-class="bg-[#16A36A] text-white font-medium shadow-xs"
            class="flex items-center justify-between px-3 py-2 rounded-md hover:bg-white/10 hover:text-white transition-colors"
          >
            <div class="flex items-center gap-2.5">
              <span>📉</span>
              <span>Sales Analysis</span>
            </div>
          </router-link>
        </div>

        <!-- SECTION 2: SALES & FULFILLMENT -->
        <div class="pt-4 space-y-1">
          <span class="px-3 text-[10px] uppercase tracking-[0.2em] text-emerald-400 font-semibold block mb-2">
            2. Sales & Fulfillment
          </span>
          <router-link
            to="/admin/orders"
            active-class="bg-[#16A36A] text-white font-medium shadow-xs"
            class="flex items-center justify-between px-3 py-2 rounded-md hover:bg-white/10 hover:text-white transition-colors"
          >
            <div class="flex items-center gap-2.5">
              <span>📦</span>
              <span>Orders Management</span>
            </div>
            <span
              v-if="badges.pending_orders > 0"
              class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-400 text-amber-950"
            >
              {{ badges.pending_orders }}
            </span>
          </router-link>

          <router-link
            to="/admin/logistics"
            active-class="bg-[#16A36A] text-white font-medium shadow-xs"
            class="flex items-center justify-between px-3 py-2 rounded-md hover:bg-white/10 hover:text-white transition-colors"
          >
            <div class="flex items-center gap-2.5">
              <span>🚚</span>
              <span>Logistics & Courier</span>
            </div>
            <span class="text-[9px] uppercase tracking-wider text-emerald-300 font-mono">Steadfast</span>
          </router-link>

          <router-link
            to="/admin/customers"
            active-class="bg-[#16A36A] text-white font-medium shadow-xs"
            class="flex items-center justify-between px-3 py-2 rounded-md hover:bg-white/10 hover:text-white transition-colors"
          >
            <div class="flex items-center gap-2.5">
              <span>👥</span>
              <span>Customers Directory</span>
            </div>
          </router-link>

          <router-link
            to="/admin/coupons"
            active-class="bg-[#16A36A] text-white font-medium shadow-xs"
            class="flex items-center justify-between px-3 py-2 rounded-md hover:bg-white/10 hover:text-white transition-colors"
          >
            <div class="flex items-center gap-2.5">
              <span>🎟️</span>
              <span>Coupons & Promotions</span>
            </div>
          </router-link>

          <router-link
            to="/admin/messages"
            active-class="bg-[#16A36A] text-white font-medium shadow-xs"
            class="flex items-center justify-between px-3 py-2 rounded-md hover:bg-white/10 hover:text-white transition-colors"
          >
            <div class="flex items-center gap-2.5">
              <span>💬</span>
              <span>Inquiries & Messages</span>
            </div>
            <span
              v-if="badges.new_inquiries > 0"
              class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-400 text-sky-950"
            >
              {{ badges.new_inquiries }}
            </span>
          </router-link>

          <router-link
            to="/admin/returns"
            active-class="bg-[#16A36A] text-white font-medium shadow-xs"
            class="flex items-center justify-between px-3 py-2 rounded-md hover:bg-white/10 hover:text-white transition-colors"
          >
            <div class="flex items-center gap-2.5">
              <span>🔄</span>
              <span>Returns & Exchanges</span>
            </div>
            <span
              v-if="badges.pending_returns > 0"
              class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-400 text-rose-950"
            >
              {{ badges.pending_returns }}
            </span>
          </router-link>
        </div>

        <!-- SECTION 3: CATALOG & INVENTORY -->
        <div class="pt-4 space-y-1">
          <span class="px-3 text-[10px] uppercase tracking-[0.2em] text-emerald-400 font-semibold block mb-2">
            3. Catalog & Inventory
          </span>
          <router-link
            to="/admin/products"
            active-class="bg-[#16A36A] text-white font-medium shadow-xs"
            class="flex items-center justify-between px-3 py-2 rounded-md hover:bg-white/10 hover:text-white transition-colors"
          >
            <div class="flex items-center gap-2.5">
              <span>👔</span>
              <span>Products Management</span>
            </div>
          </router-link>

          <router-link
            to="/admin/categories"
            active-class="bg-[#16A36A] text-white font-medium shadow-xs"
            class="flex items-center justify-between px-3 py-2 rounded-md hover:bg-white/10 hover:text-white transition-colors"
          >
            <div class="flex items-center gap-2.5">
              <span>📁</span>
              <span>Categories Hierarchy</span>
            </div>
          </router-link>

          <router-link
            to="/admin/inventory"
            active-class="bg-[#16A36A] text-white font-medium shadow-xs"
            class="flex items-center justify-between px-3 py-2 rounded-md hover:bg-white/10 hover:text-white transition-colors"
          >
            <div class="flex items-center gap-2.5">
              <span>📊</span>
              <span>Inventory Control & Logs</span>
            </div>
            <span
              v-if="badges.low_stock > 0"
              class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-400 text-amber-950"
            >
              {{ badges.low_stock }}
            </span>
          </router-link>

          <router-link
            to="/admin/reviews"
            active-class="bg-[#16A36A] text-white font-medium shadow-xs"
            class="flex items-center justify-between px-3 py-2 rounded-md hover:bg-white/10 hover:text-white transition-colors"
          >
            <div class="flex items-center gap-2.5">
              <span>⭐</span>
              <span>Reviews & Ratings</span>
            </div>
            <span
              v-if="badges.unapproved_reviews > 0"
              class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-300 text-emerald-950"
            >
              {{ badges.unapproved_reviews }}
            </span>
          </router-link>

          <router-link
            to="/admin/media"
            active-class="bg-[#16A36A] text-white font-medium shadow-xs"
            class="flex items-center justify-between px-3 py-2 rounded-md hover:bg-white/10 hover:text-white transition-colors"
          >
            <div class="flex items-center gap-2.5">
              <span>🖼️</span>
              <span>Central Media Library</span>
            </div>
          </router-link>
        </div>

        <!-- SECTION 4: HEADLESS CMS & CONTENT -->
        <div class="pt-4 space-y-1">
          <span class="px-3 text-[10px] uppercase tracking-[0.2em] text-emerald-400 font-semibold block mb-2">
            4. Headless CMS
          </span>
          <router-link
            to="/admin/homepage-builder"
            active-class="bg-[#16A36A] text-white font-medium shadow-xs"
            class="flex items-center justify-between px-3 py-2 rounded-md hover:bg-white/10 hover:text-white transition-colors"
          >
            <div class="flex items-center gap-2.5">
              <span>🎨</span>
              <span>Homepage Builder</span>
            </div>
          </router-link>

          <router-link
            to="/admin/content"
            active-class="bg-[#16A36A] text-white font-medium shadow-xs"
            class="flex items-center justify-between px-3 py-2 rounded-md hover:bg-white/10 hover:text-white transition-colors"
          >
            <div class="flex items-center gap-2.5">
              <span>📄</span>
              <span>Pages & Policy CMS</span>
            </div>
          </router-link>

          <router-link
            to="/admin/blog"
            active-class="bg-[#16A36A] text-white font-medium shadow-xs"
            class="flex items-center justify-between px-3 py-2 rounded-md hover:bg-white/10 hover:text-white transition-colors"
          >
            <div class="flex items-center gap-2.5">
              <span>📰</span>
              <span>Blog & Editorial Engine</span>
            </div>
          </router-link>
        </div>

        <!-- SECTION 5: CONFIGURATION & SETTINGS -->
        <div class="pt-4 space-y-1">
          <span class="px-3 text-[10px] uppercase tracking-[0.2em] text-emerald-400 font-semibold block mb-2">
            5. Configuration & Settings
          </span>
          <router-link
            to="/admin/settings"
            active-class="bg-[#16A36A] text-white font-medium shadow-xs"
            class="flex items-center justify-between px-3 py-2 rounded-md hover:bg-white/10 hover:text-white transition-colors"
          >
            <div class="flex items-center gap-2.5">
              <span>⚙️</span>
              <span>Central Settings Console</span>
            </div>
            <span class="text-[9px] uppercase tracking-wider text-emerald-300 font-mono">7 Tabs</span>
          </router-link>
        </div>

        <!-- SECTION 6: ACCESS CONTROL & SECURITY -->
        <div class="pt-4 space-y-1">
          <span class="px-3 text-[10px] uppercase tracking-[0.2em] text-emerald-400 font-semibold block mb-2">
            6. Security & Staff RBAC
          </span>
          <router-link
            to="/admin/users"
            active-class="bg-[#16A36A] text-white font-medium shadow-xs"
            class="flex items-center justify-between px-3 py-2 rounded-md hover:bg-white/10 hover:text-white transition-colors"
          >
            <div class="flex items-center gap-2.5">
              <span>👤</span>
              <span>Staff Users Management</span>
            </div>
          </router-link>

          <router-link
            to="/admin/roles"
            active-class="bg-[#16A36A] text-white font-medium shadow-xs"
            class="flex items-center justify-between px-3 py-2 rounded-md hover:bg-white/10 hover:text-white transition-colors"
          >
            <div class="flex items-center gap-2.5">
              <span>🛡️</span>
              <span>Roles & 50+ Permissions</span>
            </div>
          </router-link>

          <router-link
            to="/admin/audit-logs"
            active-class="bg-[#16A36A] text-white font-medium shadow-xs"
            class="flex items-center justify-between px-3 py-2 rounded-md hover:bg-white/10 hover:text-white transition-colors"
          >
            <div class="flex items-center gap-2.5">
              <span>📋</span>
              <span>Security Audit Logs</span>
            </div>
          </router-link>
        </div>
      </nav>

      <!-- Bottom Staff Profile Strip -->
      <div class="p-3.5 border-t border-emerald-900/80 bg-[#0F2D21] flex items-center justify-between text-xs shrink-0">
        <div class="flex items-center gap-2.5 min-w-0">
          <div class="w-8 h-8 rounded-full bg-emerald-800 text-white font-bold flex items-center justify-center shrink-0 border border-emerald-600">
            {{ userInitial }}
          </div>
          <div class="min-w-0">
            <span class="text-white font-medium block truncate text-xs">{{ authStore.user?.name || 'Administrator' }}</span>
            <span class="text-[10px] text-emerald-300/80 block uppercase font-mono tracking-wider truncate">
              {{ authStore.user?.role || 'Super Admin' }}
            </span>
          </div>
        </div>

        <button
          type="button"
          @click="handleLogout"
          class="p-1.5 text-emerald-300/70 hover:text-rose-400 hover:bg-white/10 rounded-md transition-colors"
          title="Sign Out"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
          </svg>
        </button>
      </div>
    </aside>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import api from '../../api';

defineProps({
  mobileOpen: {
    type: Boolean,
    default: false,
  },
});

defineEmits(['close-mobile']);

const router = useRouter();
const authStore = useAuthStore();
const navContainerRef = ref(null);

const badges = ref({
  pending_orders: 0,
  low_stock: 0,
  unapproved_reviews: 0,
  new_inquiries: 0,
  pending_returns: 0,
});

const userInitial = computed(() => {
  const name = authStore.user?.name || 'Admin';
  return name.charAt(0).toUpperCase();
});

const saveScrollPosition = () => {
  if (navContainerRef.value) {
    sessionStorage.setItem('admin_sidebar_scroll', navContainerRef.value.scrollTop);
  }
};

const restoreScrollPosition = () => {
  const saved = sessionStorage.getItem('admin_sidebar_scroll');
  if (saved && navContainerRef.value) {
    navContainerRef.value.scrollTop = parseInt(saved, 10);
  }
};

const fetchBadges = async () => {
  try {
    const res = await api.get('/admin/dashboard/badges');
    if (res.data?.data) {
      badges.value = res.data.data;
    }
  } catch (err) {
    // Graceful fallback
  }
};

const handleLogout = async () => {
  await authStore.logout();
  router.push('/admin/login');
};

onMounted(() => {
  fetchBadges();
  nextTick(() => {
    restoreScrollPosition();
  });
});
</script>
