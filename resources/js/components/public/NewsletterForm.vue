<template>
  <section class="py-20 lg:py-28 bg-[#F7F5F0] border-t border-[#D8CCBC]/40">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 text-center space-y-4">
      <span class="text-[13px] text-[#A89B8A] font-medium block">নিউজলেটার</span>

      <h2 class="font-serif text-3xl sm:text-4xl text-[#111111]">
        {{ content.heading || 'ফ্যাশন ও অফারের সাথে যুক্ত থাকুন' }}
      </h2>

      <p class="text-xs sm:text-sm text-[#A89B8A] leading-relaxed">
        {{ content.description || 'নতুন কালেকশন রিলিজ, বিশেষ ডিসকাউন্ট এবং সিজনাল ক্যাম্পেইনের আপডেট সবার আগে পেতে যুক্ত হোন।' }}
      </p>

      <!-- Subscription Form -->
      <form @submit.prevent="handleSubmit" class="pt-4 max-w-md mx-auto">
        <div v-if="success" class="p-3 bg-emerald-50 text-emerald-800 text-xs font-medium border border-emerald-200">
          ধন্যবাদ! আমাদের গ্রাহক তালিকায় আপনাকে স্বাগতম। শীঘ্রই বিশেষ অফার পাবেন।
        </div>

        <div v-else class="flex gap-2">
          <input
            type="email"
            v-model="email"
            required
            :placeholder="content.placeholder || 'আপনার ইমেইল লিখুন...'"
            class="flex-1 px-4 py-3 bg-white border border-[#D8CCBC] text-xs text-[#111111] placeholder:text-[#A89B8A] focus:outline-none focus:border-[#111111] transition-colors"
          />
          <button
            type="submit"
            :disabled="loading"
            class="px-6 py-3 bg-[#111111] hover:bg-black text-white text-xs uppercase tracking-[0.2em] font-semibold transition-colors disabled:opacity-50 shrink-0"
          >
            {{ loading ? '...' : (content.button_text || 'সাবস্ক্রাইব') }}
          </button>
        </div>

        <p class="text-[10px] text-[#A89B8A] pt-3">
          {{ content.disclaimer || 'সাবস্ক্রাইব করার মাধ্যমে আপনি আমাদের প্রাইভেসী পলিসি ও ডিজিটাল অফার গ্রহণে সম্মতি দিচ্ছেন।' }}
        </p>
      </form>
    </div>
  </section>
</template>

<script setup>
import { ref } from 'vue';
import api from '../../api';

defineProps({
  content: {
    type: Object,
    default: () => ({}),
  },
});

const email = ref('');
const loading = ref(false);
const success = ref(false);

const handleSubmit = async () => {
  if (!email.value) return;
  loading.value = true;
  try {
    await api.post('/public/newsletter/subscribe', { email: email.value });
    success.value = true;
    email.value = '';
  } catch (err) {
    console.error('Subscription error', err);
  } finally {
    loading.value = false;
  }
};
</script>
