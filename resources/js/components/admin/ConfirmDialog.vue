<template>
  <BaseModal
    :is-open="isOpen"
    :title="title"
    size="sm"
    @close="$emit('cancel')"
  >
    <div class="space-y-4">
      <div class="flex items-start gap-3">
        <div
          :class="[
            'w-10 h-10 rounded-full shrink-0 flex items-center justify-center',
            confirmVariant === 'danger' ? 'bg-rose-100 text-rose-600' : 'bg-amber-100 text-amber-600'
          ]"
        >
          <svg v-if="confirmVariant === 'danger'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
          <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <p class="text-sm text-[#69736E] leading-relaxed">
          {{ message }}
        </p>
      </div>
    </div>

    <template #footer>
      <button
        type="button"
        :disabled="loading"
        @click="$emit('cancel')"
        class="px-4 py-2 border border-[#E7EBE8] bg-white text-xs font-semibold text-[#171C1A] rounded-md hover:bg-slate-50 transition-colors disabled:opacity-50"
      >
        {{ cancelText }}
      </button>

      <button
        type="button"
        :disabled="loading"
        @click="$emit('confirm')"
        :class="[
          'px-4 py-2 text-xs font-semibold text-white rounded-md transition-all shadow-xs flex items-center gap-2 disabled:opacity-50',
          confirmVariant === 'danger' ? 'bg-rose-600 hover:bg-rose-700' : 'bg-[#16A36A] hover:bg-[#14382A]'
        ]"
      >
        <span v-if="loading" class="w-3.5 h-3.5 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
        <span>{{ confirmText }}</span>
      </button>
    </template>
  </BaseModal>
</template>

<script setup>
import BaseModal from './BaseModal.vue';

defineProps({
  isOpen: {
    type: Boolean,
    default: false,
  },
  title: {
    type: String,
    default: 'Confirm Action',
  },
  message: {
    type: String,
    default: 'Are you sure you want to proceed with this action?',
  },
  confirmText: {
    type: String,
    default: 'Confirm',
  },
  cancelText: {
    type: String,
    default: 'Cancel',
  },
  confirmVariant: {
    type: String,
    default: 'danger', // 'danger' | 'primary' | 'warning'
  },
  loading: {
    type: Boolean,
    default: false,
  },
});

defineEmits(['confirm', 'cancel']);
</script>
