<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="isOpen"
        class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 sm:p-6"
        @click.self="$emit('close')"
      >
        <div
          :class="[
            'bg-white w-full shadow-2xl relative flex flex-col max-h-[90vh] transition-all transform',
            maxWidthClass
          ]"
        >
          <!-- Header -->
          <div v-if="title || $slots.header" class="flex items-center justify-between px-6 py-4 border-b border-[#D8CCBC]/40">
            <slot name="header">
              <h3 class="font-serif text-xl tracking-tight text-[#111111]">{{ title }}</h3>
            </slot>
            <button
              type="button"
              @click="$emit('close')"
              class="text-[#A89B8A] hover:text-[#111111] transition-colors p-1"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>

          <!-- Body -->
          <div class="px-6 py-5 overflow-y-auto flex-1">
            <slot />
          </div>

          <!-- Footer -->
          <div v-if="$slots.footer" class="px-6 py-4 bg-[#F7F5F0] border-t border-[#D8CCBC]/40 flex justify-end gap-3">
            <slot name="footer" />
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  isOpen: { type: Boolean, default: false },
  title: { type: String, default: '' },
  maxWidth: { type: String, default: 'lg' }, // 'sm', 'md', 'lg', 'xl', '2xl', '3xl', '4xl'
});

defineEmits(['close']);

const maxWidthClass = computed(() => {
  switch (props.maxWidth) {
    case 'sm': return 'max-w-sm';
    case 'md': return 'max-w-md';
    case 'lg': return 'max-w-lg';
    case 'xl': return 'max-w-xl';
    case '2xl': return 'max-w-2xl';
    case '3xl': return 'max-w-3xl';
    case '4xl': return 'max-w-4xl';
    default: return 'max-w-lg';
  }
});
</script>
