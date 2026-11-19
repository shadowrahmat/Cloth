<template>
  <a v-if="external" :href="to" :target="newTab ? '_blank' : undefined" :rel="newTab ? 'noopener' : undefined" v-bind="$attrs">
    <slot />
  </a>
  <router-link v-else :to="to || '/'" v-bind="$attrs"><slot /></router-link>
</template>

<script setup>
// Links from the CMS can be internal paths, full URLs, or tel:/mailto: links.
import { computed } from 'vue';
import { isExternal } from '../../stores/site';

defineOptions({ inheritAttrs: false });

const props = defineProps({ to: { type: String, default: '/' } });
const external = computed(() => isExternal(props.to));
const newTab = computed(() => /^(https?:)?\/\//.test(props.to || ''));
</script>
