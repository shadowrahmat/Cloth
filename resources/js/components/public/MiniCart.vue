<template>
  <!-- One cart, two presentations: bottom sheet below 768px, Soft Cards side drawer from 768px up.
       Both share cart data and behaviour through composables/useCartPanel.js. -->
  <CartSheet v-if="isPhone" />
  <CartDrawer v-else />
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import CartSheet from './cart/CartSheet.vue';
import CartDrawer from './cart/CartDrawer.vue';

const mq = typeof window !== 'undefined' ? window.matchMedia('(max-width: 767.98px)') : null;
const isPhone = ref(mq?.matches ?? false);
const onChange = (e) => { isPhone.value = e.matches; };

onMounted(() => mq?.addEventListener('change', onChange));
onBeforeUnmount(() => mq?.removeEventListener('change', onChange));
</script>
