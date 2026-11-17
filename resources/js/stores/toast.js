import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useToastStore = defineStore('toast', () => {
  const toasts = ref([]);

  const add = (message, type = 'success', duration = 4000) => {
    const id = Date.now() + Math.random().toString(36).substring(2, 6);
    const toast = { id, message, type, duration };
    toasts.value.push(toast);

    if (duration > 0) {
      setTimeout(() => {
        remove(id);
      }, duration);
    }
    return id;
  };

  const success = (message, duration = 4000) => add(message, 'success', duration);
  const error = (message, duration = 5000) => add(message, 'error', duration);
  const warning = (message, duration = 4500) => add(message, 'warning', duration);
  const info = (message, duration = 4000) => add(message, 'info', duration);

  const remove = (id) => {
    toasts.value = toasts.value.filter(t => t.id !== id);
  };

  return {
    toasts,
    add,
    success,
    error,
    warning,
    info,
    remove,
  };
});
