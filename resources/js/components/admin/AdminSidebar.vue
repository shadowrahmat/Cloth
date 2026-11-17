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
        mobileOpen ? 'translate-x-0 w-[288px]' : '-translate-x-full lg:translate-x-0 lg:w-[264px]'
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
          <a
            href="/"
            target="_blank"
            class="p-1.5 text-emerald-300/80 hover:text-white hover:bg-white/10 rounded-md transition-colors"
            title="লাইভ স্টোরফ্রন্ট দেখুন"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
            </svg>
          </a>

          <!-- Mobile close button -->
          <button
            type="button"
            @click="$emit('close-mobile')"
            class="p-1.5 text-emerald-300/80 hover:text-white hover:bg-white/10 rounded-md lg:hidden transition-colors"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Quick Storefront Link Bar -->
      <div class="px-3.5 py-2 bg-emerald-950/40 border-b border-emerald-900/50 flex items-center justify-between text-[11px]">
        <span class="text-emerald-300/80 flex items-center gap-1.5 font-medium">
          <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
          <span>DRAWER Admin v2.4</span>
        </span>
        <a
          href="/shop"
          target="_blank"
          class="text-emerald-300 hover:text-white font-medium hover:underline inline-flex items-center gap-1"
        >
          <span>শপ দেখুন</span>
          <span>↗</span>
        </a>
      </div>

      <!-- Scrollable Navigation Container with Scroll Memory -->
      <nav
        ref="navContainerRef"
        class="flex-1 overflow-y-auto px-3 py-4 space-y-5 text-xs text-emerald-100/85 divide-y divide-emerald-900/40"
        @scroll="saveScrollPosition"
      >
        <!-- SECTION 1: OVERVIEW & DASHBOARD -->
        <div class="space-y-1">
          <div class="px-3 pb-1.5 flex items-center justify-between">
            <span class="text-[10px] uppercase tracking-[0.2em] text-emerald-400 font-bold block">
              ১. ওভারভিউ (Overview)
            </span>
          </div>

          <router-link
            to="/admin"
            exact-active-class="bg-[#16A36A] text-white font-semibold shadow-xs"
            class="flex items-center justify-between px-3 py-2 rounded-md hover:bg-white/10 hover:text-white transition-colors"
          >
            <div class="flex items-center gap-2.5">
              <span class="text-sm">📊</span>
              <div>
                <span class="block font-medium">ড্যাশবোর্ড</span>
                <span class="text-[10px] text-emerald-300/70 block -mt-0.5">Executive Dashboard</span>
              </div>
            </div>
          </router-link>

          <router-link
            to="/admin/reports"
            active-class="bg-[#16A36A] text-white font-semibold shadow-xs"
            class="flex items-center justify-between px-3 py-2 rounded-md hover:bg-white/10 hover:text-white transition-colors"
          >
            <div class="flex items-center gap-2.5">
              <span class="text-sm">📈</span>
              <div>
                <span class="block font-medium">বিক্রয় রিপোর্ট</span>
                <span class="text-[10px] text-emerald-300/70 block -mt-0.5">Sales Reports</span>
              </div>
            </div>
          </router-link>

          <router-link
            to="/admin/reports/sales"
            active-class="bg-[#16A36A] text-white font-semibold shadow-xs"
            class="flex items-center justify-between px-3 py-2 rounded-md hover:bg-white/10 hover:text-white transition-colors"
          >
            <div class="flex items-center gap-2.5">
              <span class="text-sm">📉</span>
              <div>
                <span class="block font-medium">সেলস অ্যানালাইসিস</span>
                <span class="text-[10px] text-emerald-300/70 block -mt-0.5">Sales Analysis</span>
              </div>
            </div>
          </router-link>
        </div>

        <!-- SECTION 2: CATALOG & PRODUCTS (PRIORITY #1!) -->
        <div class="pt-4 space-y-1">
          <div class="px-3 pb-1.5 flex items-center justify-between">
            <span class="text-[10px] uppercase tracking-[0.2em] text-emerald-400 font-bold block">
              ২. ক্যাটালগ ও পণ্য (Catalog)
            </span>
            <span class="text-[9px] uppercase px-1.5 py-0.2 bg-emerald-500/20 text-emerald-300 rounded font-mono">
              Core
            </span>
          </div>

          <!-- CATEGORIES WITH HIGHLIGHT & BADGE -->
          <router-link
            to="/admin/categories"
            active-class="bg-[#16A36A] text-white font-semibold shadow-xs"
            class="flex items-center justify-between px-3 py-2 rounded-md hover:bg-white/10 hover:text-white transition-colors group"
          >
            <div class="flex items-center gap-2.5">
              <span class="text-sm">📁</span>
              <div>
                <span class="block font-medium">ক্যাটাগরি সমূহ</span>
                <span class="text-[10px] text-emerald-300/70 block -mt-0.5">Categories & Taxonomy</span>
              </div>
            </div>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/30 text-emerald-200 border border-emerald-400/30">
              {{ badges.total_categories || 16 }}
            </span>
          </router-link>

          <!-- PRODUCTS MANAGEMENT -->
          <router-link
            to="/admin/products"
            active-class="bg-[#16A36A] text-white font-semibold shadow-xs"
            class="flex items-center justify-between px-3 py-2 rounded-md hover:bg-white/10 hover:text-white transition-colors"
          >
            <div class="flex items-center gap-2.5">
              <span class="text-sm">👔</span>
              <div>
                <span class="block font-medium">পণ্য তালিকা ও ম্যানেজমেন্ট</span>
                <span class="text-[10px] text-emerald-300/70 block -mt-0.5">Products Catalog</span>
              </div>
            </div>
            <span v-if="badges.total_products" class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-white/10 text-emerald-200">
              {{ badges.total_products }}
            </span>
          </router-link>

          <!-- INVENTORY & STOCK -->
          <router-link
            to="/admin/inventory"
            active-class="bg-[#16A36A] text-white font-semibold shadow-xs"
            class="flex items-center justify-between px-3 py-2 rounded-md hover:bg-white/10 hover:text-white transition-colors"
          >
            <div class="flex items-center gap-2.5">
              <span class="text-sm">📦</span>
              <div>
                <span class="block font-medium">স্টক ও ইনভেন্টরি</span>
                <span class="text-[10px] text-emerald-300/70 block -mt-0.5">Inventory & Stock</span>
              </div>
            </div>
            <span
              v-if="badges.low_stock > 0"
              class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-400 text-amber-950"
              title="কম স্টক সতর্কতা"
            >
              {{ badges.low_stock }}
            </span>
          </router-link>

          <!-- CUSTOMER REVIEWS -->
          <router-link
            to="/admin/reviews"
            active-class="bg-[#16A36A] text-white font-semibold shadow-xs"
            class="flex items-center justify-between px-3 py-2 rounded-md hover:bg-white/10 hover:text-white transition-colors"
          >
            <div class="flex items-center gap-2.5">
              <span class="text-sm">⭐</span>
              <div>
                <span class="block font-medium">রিভিউ ও রেটিং</span>
                <span class="text-[10px] text-emerald-300/70 block -mt-0.5">Reviews & Ratings</span>
              </div>
            </div>
            <span
              v-if="badges.unapproved_reviews > 0"
              class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-400 text-emerald-950"
            >
              {{ badges.unapproved_reviews }}
            </span>
          </router-link>

          <!-- MEDIA LIBRARY -->
          <router-link
            to="/admin/media"
            active-class="bg-[#16A36A] text-white font-semibold shadow-xs"
            class="flex items-center justify-between px-3 py-2 rounded-md hover:bg-white/10 hover:text-white transition-colors"
          >
            <div class="flex items-center gap-2.5">
              <span class="text-sm">🖼️</span>
              <div>
                <span class="block font-medium">মিডিয়া লাইব্রেরি</span>
                <span class="text-[10px] text-emerald-300/70 block -mt-0.5">Media Assets & CDN</span>
              </div>
            </div>
          </router-link>
        </div>

        <!-- SECTION 3: SALES & FULFILLMENT -->
        <div class="pt-4 space-y-1">
          <div class="px-3 pb-1.5 flex items-center justify-between">
            <span class="text-[10px] uppercase tracking-[0.2em] text-emerald-400 font-bold block">
              ৩. সেলস ও ডেলিভারি (Orders)
            </span>
          </div>

          <!-- ORDERS -->
          <router-link
            to="/admin/orders"
            active-class="bg-[#16A36A] text-white font-semibold shadow-xs"
            class="flex items-center justify-between px-3 py-2 rounded-md hover:bg-white/10 hover:text-white transition-colors"
          >
            <div class="flex items-center gap-2.5">
              <span class="text-sm">🛍️</span>
              <div>
                <span class="block font-medium">অর্ডার ম্যানেজমেন্ট</span>
                <span class="text-[10px] text-emerald-300/70 block -mt-0.5">Orders & Fulfillment</span>
              </div>
            </div>
            <span
              v-if="badges.pending_orders > 0"
              class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-400 text-amber-950 shadow-xs animate-pulse"
            >
              {{ badges.pending_orders }}
            </span>
          </router-link>

          <!-- LOGISTICS (STEADFAST) -->
          <router-link
            to="/admin/logistics"
            active-class="bg-[#16A36A] text-white font-semibold shadow-xs"
            class="flex items-center justify-between px-3 py-2 rounded-md hover:bg-white/10 hover:text-white transition-colors"
          >
            <div class="flex items-center gap-2.5">
              <span class="text-sm">🚚</span>
              <div>
                <span class="block font-medium">লজিস্টিকস ও কুরিয়ার</span>
                <span class="text-[10px] text-emerald-300/70 block -mt-0.5">Steadfast Courier</span>
              </div>
            </div>
            <span class="text-[9px] uppercase tracking-wider text-emerald-300 font-mono">Steadfast</span>
          </router-link>

          <!-- CUSTOMERS -->
          <router-link
            to="/admin/customers"
            active-class="bg-[#16A36A] text-white font-semibold shadow-xs"
            class="flex items-center justify-between px-3 py-2 rounded-md hover:bg-white/10 hover:text-white transition-colors"
          >
            <div class="flex items-center gap-2.5">
              <span class="text-sm">👥</span>
              <div>
                <span class="block font-medium">গ্রাহক তালিকা</span>
                <span class="text-[10px] text-emerald-300/70 block -mt-0.5">Customers Directory</span>
              </div>
            </div>
          </router-link>

          <!-- RETURNS -->
          <router-link
            to="/admin/returns"
            active-class="bg-[#16A36A] text-white font-semibold shadow-xs"
            class="flex items-center justify-between px-3 py-2 rounded-md hover:bg-white/10 hover:text-white transition-colors"
          >
            <div class="flex items-center gap-2.5">
              <span class="text-sm">🔄</span>
              <div>
                <span class="block font-medium">রিটার্ন ও এক্সচেঞ্জ</span>
                <span class="text-[10px] text-emerald-300/70 block -mt-0.5">Returns & Exchanges</span>
              </div>
            </div>
            <span
              v-if="badges.pending_returns > 0"
              class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-400 text-rose-950"
            >
              {{ badges.pending_returns }}
            </span>
          </router-link>

          <!-- COUPONS -->
          <router-link
            to="/admin/coupons"
            active-class="bg-[#16A36A] text-white font-semibold shadow-xs"
            class="flex items-center justify-between px-3 py-2 rounded-md hover:bg-white/10 hover:text-white transition-colors"
          >
            <div class="flex items-center gap-2.5">
              <span class="text-sm">🎟️</span>
              <div>
                <span class="block font-medium">কুপন ও ডিসকাউন্ট</span>
                <span class="text-[10px] text-emerald-300/70 block -mt-0.5">Coupons & Promos</span>
              </div>
            </div>
          </router-link>
        </div>

        <!-- SECTION 4: CMS & MARKETING -->
        <div class="pt-4 space-y-1">
          <div class="px-3 pb-1.5 flex items-center justify-between">
            <span class="text-[10px] uppercase tracking-[0.2em] text-emerald-400 font-bold block">
              ৪. ওয়েবসাইট ও কনটেন্ট (CMS)
            </span>
          </div>

          <router-link
            to="/admin/homepage-builder"
            active-class="bg-[#16A36A] text-white font-semibold shadow-xs"
            class="flex items-center justify-between px-3 py-2 rounded-md hover:bg-white/10 hover:text-white transition-colors"
          >
            <div class="flex items-center gap-2.5">
              <span class="text-sm">🎨</span>
              <div>
                <span class="block font-medium">হোমপেজ বিল্ডার</span>
                <span class="text-[10px] text-emerald-300/70 block -mt-0.5">Visual Homepage Builder</span>
              </div>
            </div>
          </router-link>

          <router-link
            to="/admin/messages"
            active-class="bg-[#16A36A] text-white font-semibold shadow-xs"
            class="flex items-center justify-between px-3 py-2 rounded-md hover:bg-white/10 hover:text-white transition-colors"
          >
            <div class="flex items-center gap-2.5">
              <span class="text-sm">💬</span>
              <div>
                <span class="block font-medium">মেসেজ ও ইনকোয়ারি</span>
                <span class="text-[10px] text-emerald-300/70 block -mt-0.5">Client Messages</span>
              </div>
            </div>
            <span
              v-if="badges.new_inquiries > 0"
              class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-400 text-sky-950"
            >
              {{ badges.new_inquiries }}
            </span>
          </router-link>

          <router-link
            to="/admin/content"
            active-class="bg-[#16A36A] text-white font-semibold shadow-xs"
            class="flex items-center justify-between px-3 py-2 rounded-md hover:bg-white/10 hover:text-white transition-colors"
          >
            <div class="flex items-center gap-2.5">
              <span class="text-sm">📄</span>
              <div>
                <span class="block font-medium">পেজ ও পলিসি CMS</span>
                <span class="text-[10px] text-emerald-300/70 block -mt-0.5">Pages & Static Content</span>
              </div>
            </div>
          </router-link>

          <router-link
            to="/admin/blog"
            active-class="bg-[#16A36A] text-white font-semibold shadow-xs"
            class="flex items-center justify-between px-3 py-2 rounded-md hover:bg-white/10 hover:text-white transition-colors"
          >
            <div class="flex items-center gap-2.5">
              <span class="text-sm">📰</span>
              <div>
                <span class="block font-medium">ব্লগ ও এডিটোরিয়াল</span>
                <span class="text-[10px] text-emerald-300/70 block -mt-0.5">Blog Articles</span>
              </div>
            </div>
          </router-link>
        </div>

        <!-- SECTION 5: SETTINGS & ACCESS CONTROL -->
        <div class="pt-4 space-y-1">
          <div class="px-3 pb-1.5 flex items-center justify-between">
            <span class="text-[10px] uppercase tracking-[0.2em] text-emerald-400 font-bold block">
              ৫. সিস্টেম ও সেটিংস (Settings)
            </span>
          </div>

          <router-link
            to="/admin/settings"
            active-class="bg-[#16A36A] text-white font-semibold shadow-xs"
            class="flex items-center justify-between px-3 py-2 rounded-md hover:bg-white/10 hover:text-white transition-colors"
          >
            <div class="flex items-center gap-2.5">
              <span class="text-sm">⚙️</span>
              <div>
                <span class="block font-medium">স্টোর সেটিংস</span>
                <span class="text-[10px] text-emerald-300/70 block -mt-0.5">Central Settings (7 Tabs)</span>
              </div>
            </div>
          </router-link>

          <router-link
            to="/admin/users"
            active-class="bg-[#16A36A] text-white font-semibold shadow-xs"
            class="flex items-center justify-between px-3 py-2 rounded-md hover:bg-white/10 hover:text-white transition-colors"
          >
            <div class="flex items-center gap-2.5">
              <span class="text-sm">👤</span>
              <div>
                <span class="block font-medium">স্টাফ ও অ্যাডমিন</span>
                <span class="text-[10px] text-emerald-300/70 block -mt-0.5">Staff & Team Users</span>
              </div>
            </div>
          </router-link>

          <router-link
            to="/admin/roles"
            active-class="bg-[#16A36A] text-white font-semibold shadow-xs"
            class="flex items-center justify-between px-3 py-2 rounded-md hover:bg-white/10 hover:text-white transition-colors"
          >
            <div class="flex items-center gap-2.5">
              <span class="text-sm">🛡️</span>
              <div>
                <span class="block font-medium">রোলস ও পারমিশন</span>
                <span class="text-[10px] text-emerald-300/70 block -mt-0.5">RBAC Permissions</span>
              </div>
            </div>
          </router-link>

          <router-link
            to="/admin/audit-logs"
            active-class="bg-[#16A36A] text-white font-semibold shadow-xs"
            class="flex items-center justify-between px-3 py-2 rounded-md hover:bg-white/10 hover:text-white transition-colors"
          >
            <div class="flex items-center gap-2.5">
              <span class="text-sm">📋</span>
              <div>
                <span class="block font-medium">সিকিউরিটি অডিট লগ</span>
                <span class="text-[10px] text-emerald-300/70 block -mt-0.5">Activity & Security Logs</span>
              </div>
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
          class="p-2 text-emerald-300/70 hover:text-white hover:bg-white/10 rounded-md transition-colors"
          title="লগআউট"
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
  total_categories: 16,
  total_products: 0,
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
      badges.value = {
        ...badges.value,
        ...res.data.data,
      };
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
