<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="isOpen"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 overflow-y-auto bg-black/60 backdrop-blur-xs"
        @click.self="handleBackdropClick"
      >
        <div
          :class="[
            'w-full bg-white rounded-lg shadow-2xl border border-[#E7EBE8] overflow-hidden flex flex-col max-h-[90vh] my-auto transform transition-all duration-200',
            sizeClass
          ]"
        >
          <!-- Header -->
          <div class="px-6 py-4 border-b border-[#E7EBE8] flex items-center justify-between bg-[#F7F8FA]">
            <div>
              <h3 class="text-base font-semibold text-[#171C1A]">
                <slot name="title">{{ title }}</slot>
              </h3>
              <p v-if="subtitle" class="text-xs text-[#69736E] mt-0.5">{{ subtitle }}</p>
            </div>

            <button
              type="button"
              @click="$emit('close')"
              class="p-1.5 text-[#69736E] hover:text-[#171C1A] hover:bg-slate-200/60 rounded-md transition-colors"
              aria-label="Close"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- Body -->
          <div class="p-6 overflow-y-auto flex-1">
            <slot></slot>
          </div>

          <!-- Footer -->
          <div v-if="$slots.footer" class="px-6 py-4 bg-[#F7F8FA] border-t border-[#E7EBE8] flex items-center justify-end gap-3">
            <slot name="footer"></slot>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false,
  },
  title: {
    type: String,
    default: '',
  },
  subtitle: {
    type: String,
    default: null,
  },
  size: {
    type: String,
    default: 'lg', // 'sm' | 'md' | 'lg' | 'xl' | '2xl' | 'full'
  },
  closeOnBackdrop: {
    type: Boolean,
    default: true,
  },
});

const emit = defineEmits(['close']);

const sizeClass = computed(() => {
  switch (props.size) {
    case 'sm': return 'max-w-md';
    case 'md': return 'max-w-lg';
    case 'lg': return 'max-w-2xl';
    case 'xl': return 'max-w-4xl';
    case '2xl': return 'max-w-6xl';
    case 'full': return 'max-w-[95vw] h-[92vh]';
    default: return 'max-w-2xl';
  }
});

const handleBackdropClick = () => {
  if (props.closeOnBackdrop) {
    emit('close');
  }
};

const handleKeyDown = (e) => {
  if (e.key === 'Escape' && props.isOpen) {
    emit('close');
  }
};

onMounted(() => {
  window.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeyDown);
});
</script>
