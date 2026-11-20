<template>
  <Teleport to="body">
    <div class="fixed bottom-5 right-5 z-[80] flex flex-col gap-2.5 max-w-sm w-full pointer-events-none px-4 sm:px-0">
      <TransitionGroup
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="opacity-0 translate-y-3 scale-95"
        enter-to-class="opacity-100 translate-y-0 scale-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100 translate-y-0 scale-100"
        leave-to-class="opacity-0 translate-y-2 scale-95"
      >
        <div
          v-for="toast in toastStore.toasts"
          :key="toast.id"
          :class="[
            'pointer-events-auto flex items-start gap-3 p-4 rounded-lg shadow-lg border text-xs backdrop-blur-xs transition-all',
            toastTypeClass(toast.type)
          ]"
        >
          <div class="shrink-0 mt-0.5">
            <!-- Icons -->
            <svg v-if="toast.type === 'success'" class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <svg v-else-if="toast.type === 'error'" class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            <svg v-else-if="toast.type === 'warning'" class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <svg v-else class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>

          <div class="flex-1 min-w-0 font-medium leading-relaxed">
            {{ toast.message }}
          </div>

          <button
            type="button"
            @click="toastStore.remove(toast.id)"
            class="shrink-0 p-1 text-slate-400 hover:text-slate-600 transition-colors"
          >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<script setup>
import { useToastStore } from '../../stores/toast';

const toastStore = useToastStore();

const toastTypeClass = (type) => {
  switch (type) {
    case 'success':
      return 'bg-white text-[#171C1A] border-emerald-200';
    case 'error':
      return 'bg-white text-[#171C1A] border-rose-200';
    case 'warning':
      return 'bg-white text-[#171C1A] border-amber-200';
    case 'info':
      return 'bg-white text-[#171C1A] border-sky-200';
    default:
      return 'bg-white text-[#171C1A] border-[#E7EBE8]';
  }
};
</script>
