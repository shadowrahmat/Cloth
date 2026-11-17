<template>
  <div class="py-12 sm:py-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center max-w-xl mx-auto mb-12 space-y-3">
      <span class="text-xs uppercase tracking-[0.25em] text-[#A89B8A] font-medium block">Client Concierge</span>
      <h1 class="font-serif text-3xl sm:text-5xl text-[#111111]">Get In Touch</h1>
      <p class="text-xs sm:text-sm text-[#A89B8A] font-light leading-relaxed">
        Our client advisors are available to assist with private appointments, styling guidance, and tailoring advice.
      </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">
      <!-- Contact Info Card -->
      <div class="lg:col-span-5 bg-white p-8 border border-[#D8CCBC]/40 shadow-xs space-y-6 text-xs text-[#111111]">
        <div>
          <h4 class="font-serif text-xl mb-2">Soho Flagship</h4>
          <p class="text-[#A89B8A] leading-relaxed">
            420 West Broadway<br />
            Soho, New York, NY 10012<br />
            United States
          </p>
        </div>

        <div class="border-t border-[#D8CCBC]/30 pt-4 space-y-2">
          <h5 class="font-semibold uppercase tracking-wider text-[11px]">Direct Inquiries</h5>
          <p class="text-[#A89B8A]">concierge@drawerclothing.com</p>
          <p class="text-[#A89B8A]">+880 1700 000000</p>
        </div>

        <div class="border-t border-[#D8CCBC]/30 pt-4 space-y-1 text-[#A89B8A]">
          <h5 class="font-semibold uppercase tracking-wider text-[11px] text-[#111111]">Concierge Hours</h5>
          <p>Saturday – Thursday: 10am – 8pm</p>
          <p>Friday: 2pm – 8pm</p>
        </div>
      </div>

      <!-- Contact Message Form -->
      <div class="lg:col-span-7 bg-white p-8 border border-[#D8CCBC]/40 shadow-xs">
        <div v-if="submitted" class="p-6 bg-emerald-50 text-emerald-800 text-xs font-medium border border-emerald-200 text-center space-y-2">
          <h5 class="font-serif text-base text-emerald-900">Message Received</h5>
          <p>Thank you for contacting DRAWER CLOTHING concierge. An advisor will review your note and respond promptly.</p>
        </div>

        <form v-else @submit.prevent="handleSubmit" class="space-y-4 text-xs">
          <div>
            <label class="block font-semibold uppercase tracking-wider mb-1">Your Full Name *</label>
            <input type="text" v-model="form.name" required class="w-full px-4 py-2.5 bg-[#F7F5F0] border border-[#D8CCBC]" />
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block font-semibold uppercase tracking-wider mb-1">Email Address *</label>
              <input type="email" v-model="form.email" required class="w-full px-4 py-2.5 bg-[#F7F5F0] border border-[#D8CCBC]" />
            </div>
            <div>
              <label class="block font-semibold uppercase tracking-wider mb-1">Telephone</label>
              <input type="tel" v-model="form.phone" class="w-full px-4 py-2.5 bg-[#F7F5F0] border border-[#D8CCBC]" />
            </div>
          </div>
          <div>
            <label class="block font-semibold uppercase tracking-wider mb-1">Subject</label>
            <input type="text" v-model="form.subject" placeholder="Bespoke tailoring, sizing advice, or orders..." class="w-full px-4 py-2.5 bg-[#F7F5F0] border border-[#D8CCBC]" />
          </div>
          <div>
            <label class="block font-semibold uppercase tracking-wider mb-1">Message *</label>
            <textarea rows="5" v-model="form.message" required class="w-full px-4 py-2.5 bg-[#F7F5F0] border border-[#D8CCBC]"></textarea>
          </div>
          <button
            type="submit"
            :disabled="loading"
            class="w-full py-3.5 bg-[#111111] text-white uppercase tracking-widest font-semibold hover:bg-black transition-colors"
          >
            {{ loading ? 'Transmitting Message...' : 'Send Message' }}
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import api from '../../api';

const form = reactive({
  name: '',
  email: '',
  phone: '',
  subject: '',
  message: '',
});
const loading = ref(false);
const submitted = ref(false);

const handleSubmit = async () => {
  loading.value = true;
  try {
    await api.post('/public/contact', form);
    submitted.value = true;
  } catch (err) {
    alert(err.response?.data?.message || 'Error sending message');
  } finally {
    loading.value = false;
  }
};
</script>
