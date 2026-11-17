<template>
  <header class="h-16 bg-white border-b border-[#E7EBE8] flex items-center justify-between px-4 sm:px-6 lg:px-8 shrink-0 z-30 sticky top-0 shadow-2xs">
    <!-- Left: Mobile Toggle & Breadcrumbs -->
    <div class="flex items-center gap-3 sm:gap-4 min-w-0">
      <!-- Mobile Sidebar Toggle -->
      <button
        type="button"
        @click="$emit('toggle-mobile')"
        class="p-2 text-[#69736E] hover:text-[#171C1A] hover:bg-[#F7F8FA] rounded-md lg:hidden transition-colors"
        aria-label="Toggle Navigation"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
      </button>

      <!-- Breadcrumbs -->
      <nav class="flex items-center gap-2 text-xs truncate">
        <router-link to="/admin" class="text-[#69736E] hover:text-[#14382A] font-medium hidden sm:inline">
          Console
        </router-link>
        <span class="text-slate-300 hidden sm:inline">/</span>
        <h1 class="text-sm font-semibold text-[#171C1A] truncate tracking-tight">
          {{ title || 'Executive Dashboard' }}
        </h1>
      </nav>
    </div>

    <!-- Right: Quick Actions, Global Search, Notifications, Profile Dropdown -->
    <div class="flex items-center gap-2 sm:gap-4">
      <!-- Global Quick Search Bar -->
      <div class="relative hidden md:block w-48 lg:w-64">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Quick search... (Ctrl+K)"
          class="w-full bg-[#F7F8FA] border border-[#E7EBE8] rounded-md pl-8 pr-3 py-1.5 text-xs text-[#171C1A] placeholder-[#69736E] focus:outline-none focus:ring-1 focus:ring-[#16A36A] focus:border-[#16A36A] transition-all"
          @keydown.enter="handleQuickSearch"
        />
        <svg class="w-3.5 h-3.5 text-[#69736E] absolute left-2.5 top-2.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
      </div>

      <!-- Quick Action Menu -->
      <router-link
        to="/admin/products"
        class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#14382A] text-white text-xs font-semibold rounded-md hover:bg-[#16A36A] transition-all shadow-xs"
      >
        <span>+</span>
        <span>Product</span>
      </router-link>

      <!-- Notification Bell -->
      <div class="relative">
        <button
          type="button"
          @click="showNotifications = !showNotifications"
          class="p-2 text-[#69736E] hover:text-[#171C1A] hover:bg-[#F7F8FA] rounded-md relative transition-colors"
          title="Notifications"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
          </svg>
          <span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-[#16A36A]"></span>
        </button>

        <!-- Notification Dropdown Menu -->
        <div
          v-if="showNotifications"
          class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl border border-[#E7EBE8] p-4 text-xs space-y-3 z-50"
          @click.outside="showNotifications = false"
        >
          <div class="flex items-center justify-between pb-2 border-b border-[#E7EBE8]">
            <span class="font-semibold text-[#171C1A]">Activity Notifications</span>
            <span class="text-[10px] text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full font-medium">System Live</span>
          </div>
          <div class="space-y-2 max-h-60 overflow-y-auto">
            <div class="p-2 bg-[#F7F8FA] rounded-md hover:bg-slate-100/80 transition-colors cursor-pointer">
              <span class="font-medium text-[#171C1A] block">Stock & Inventory Guard</span>
              <span class="text-[11px] text-[#69736E]">Automatic reservation and stock tracking active.</span>
            </div>
            <div class="p-2 bg-[#F7F8FA] rounded-md hover:bg-slate-100/80 transition-colors cursor-pointer">
              <span class="font-medium text-[#171C1A] block">Steadfast Courier API</span>
              <span class="text-[11px] text-[#69736E]">Logistics service ready for consignment polling.</span>
            </div>
          </div>
        </div>
      </div>

      <div class="h-4 w-px bg-[#E7EBE8] hidden sm:block"></div>

      <!-- Staff Profile Menu -->
      <div class="flex items-center gap-3">
        <div class="flex flex-col items-end text-right hidden sm:flex">
          <span class="text-xs font-semibold text-[#171C1A]">{{ authStore.user?.name || 'Administrator' }}</span>
          <span class="text-[10px] text-emerald-700 font-mono tracking-wider uppercase">{{ authStore.user?.role || 'Super Admin' }}</span>
        </div>

        <router-link
          to="/admin/settings"
          class="w-8 h-8 rounded-full bg-[#14382A] text-white flex items-center justify-center font-bold text-xs hover:bg-[#16A36A] transition-colors"
          title="Account Settings"
        >
          {{ userInitial }}
        </router-link>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';

defineProps({
  title: {
    type: String,
    default: '',
  },
});

defineEmits(['toggle-mobile']);

const router = useRouter();
const authStore = useAuthStore();
const searchQuery = ref('');
const showNotifications = ref(false);

const userInitial = computed(() => {
  const name = authStore.user?.name || 'A';
  return name.charAt(0).toUpperCase();
});

const handleQuickSearch = () => {
  if (!searchQuery.value) return;
  const q = searchQuery.value.toLowerCase();
  if (q.includes('order')) {
    router.push('/admin/orders');
  } else if (q.includes('invent') || q.includes('stock')) {
    router.push('/admin/inventory');
  } else if (q.includes('setting') || q.includes('config')) {
    router.push('/admin/settings');
  } else if (q.includes('user') || q.includes('role') || q.includes('staff')) {
    router.push('/admin/users');
  } else {
    router.push(`/admin/products`);
  }
  searchQuery.value = '';
};
</script>
