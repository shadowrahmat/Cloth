<template>
  <div class="min-h-screen bg-[#111111] flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white p-8 sm:p-10 shadow-2xl border border-white/10 space-y-8">
      <div class="text-center space-y-3">
        <router-link to="/" class="inline-block">
          <img src="/images/logo-transparent.png" alt="DRAWER CLOTHING" class="h-16 sm:h-20 max-w-[240px] mx-auto object-contain" />
        </router-link>
        <span class="text-[10px] tracking-[0.3em] uppercase text-[#A89B8A] font-medium block">
          Control Center Administration
        </span>
      </div>

      <form @submit.prevent="handleLogin" class="space-y-5 text-xs">
        <div>
          <label class="block font-semibold uppercase tracking-wider text-[#111111] mb-1">
            Administrator Email
          </label>
          <input
            type="email"
            v-model="email"
            required
            placeholder="you@example.com"
            class="w-full px-4 py-3 bg-[#F7F5F0] border border-[#D8CCBC] text-[#111111] focus:outline-none focus:border-[#111111]"
          />
        </div>

        <div>
          <label class="block font-semibold uppercase tracking-wider text-[#111111] mb-1">
            Password
          </label>
          <input
            type="password"
            v-model="password"
            required
            placeholder="••••••••"
            class="w-full px-4 py-3 bg-[#F7F5F0] border border-[#D8CCBC] text-[#111111] focus:outline-none focus:border-[#111111]"
          />
        </div>

        <div v-if="error" class="p-3 bg-rose-50 text-rose-800 text-xs border border-rose-200">
          {{ error }}
        </div>

        <button
          type="submit"
          :disabled="loading"
          class="w-full py-3.5 bg-[#111111] hover:bg-black text-white text-xs uppercase tracking-[0.2em] font-semibold transition-colors disabled:opacity-50 shadow-md"
        >
          {{ loading ? 'Authenticating...' : 'Sign In to Control Center' }}
        </button>
      </form>

      <div class="text-center pt-2 border-t border-[#D8CCBC]/30 text-[11px] text-[#A89B8A]">
        <router-link to="/" class="hover:text-[#111111] transition-colors">
          ← Return to Storefront
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';

const router = useRouter();
const authStore = useAuthStore();

const email = ref('');
const password = ref('');
const loading = ref(false);
const error = ref('');

const handleLogin = async () => {
  loading.value = true;
  error.value = '';
  try {
    await authStore.adminLogin(email.value, password.value);
    router.push('/admin');
  } catch (err) {
    error.value = err.response?.data?.message || 'Access denied. Please check your credentials.';
  } finally {
    loading.value = false;
  }
};
</script>
