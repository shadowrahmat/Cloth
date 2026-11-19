<template>
  <div class="min-h-screen flex bg-[#F5F7F6] text-[#171C1A]">
    <AdminSidebar :mobile-open="mobileOpen" @close-mobile="mobileOpen = false" />

    <div class="flex-1 flex flex-col min-w-0">
      <AdminHeader :title="currentTitle" @toggle-mobile="mobileOpen = !mobileOpen" />

      <main class="flex-1 w-full px-4 py-5 sm:px-6 lg:px-8 lg:py-6">
        <router-view />
      </main>
    </div>

    <CommandPalette />
    <ToastNotification />
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';
import { useRoute } from 'vue-router';
import AdminSidebar from '../components/admin/AdminSidebar.vue';
import AdminHeader from '../components/admin/AdminHeader.vue';
import CommandPalette from '../components/admin/CommandPalette.vue';
import ToastNotification from '../components/admin/ToastNotification.vue';
import { useAdminUiStore } from '../stores/adminUi';

const route = useRoute();
const ui = useAdminUiStore();
const mobileOpen = ref(false);
const currentTitle = computed(() => route.meta?.title || '');

// Keep sidebar counters and the bell fresh: on every page change, and every minute.
let timer = null;
onMounted(() => {
  ui.fetchBadges({ force: true });
  timer = setInterval(() => {
    if (document.visibilityState === 'visible') ui.fetchBadges({ force: true });
  }, 60000);
});
onBeforeUnmount(() => clearInterval(timer));
watch(() => route.path, () => {
  mobileOpen.value = false;
  ui.fetchBadges();
});
</script>
