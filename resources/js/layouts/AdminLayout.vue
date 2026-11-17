<template>
  <div class="min-h-screen flex bg-[#F7F8FA] text-[#171C1A]">
    <!-- Master Admin 6-Section Sidebar -->
    <AdminSidebar
      :mobile-open="mobileOpen"
      @close-mobile="mobileOpen = false"
    />

    <!-- Main Workspace Container -->
    <div class="flex-1 flex flex-col min-w-0 overflow-y-auto max-h-screen">
      <!-- Master Header -->
      <AdminHeader
        :title="currentTitle"
        @toggle-mobile="mobileOpen = !mobileOpen"
      />

      <!-- Content Canvas -->
      <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto">
        <router-view />
      </main>
    </div>

    <!-- Global Toast Notification Provider -->
    <ToastNotification />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRoute } from 'vue-router';
import AdminSidebar from '../components/admin/AdminSidebar.vue';
import AdminHeader from '../components/admin/AdminHeader.vue';
import ToastNotification from '../components/admin/ToastNotification.vue';

const route = useRoute();
const mobileOpen = ref(false);
const currentTitle = computed(() => route.meta?.title || 'Executive Dashboard');
</script>
