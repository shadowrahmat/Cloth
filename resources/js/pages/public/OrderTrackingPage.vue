<template>
  <div class="py-12 sm:py-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center max-w-md mx-auto mb-10 space-y-2">
      <span class="text-xs uppercase tracking-[0.25em] text-[#A89B8A] font-medium block">অর্ডার ট্র্যাকিং ও ডেলিভারি তথ্য</span>
      <h1 class="font-serif text-3xl sm:text-4xl text-[#111111]">আপনার অর্ডারের অবস্থান জানুন</h1>
      <p class="text-xs text-[#A89B8A]">আপনার অর্ডার নম্বর ও ইমেইল অ্যাড্রেস দিয়ে ডেলিভারি অগ্রগতি যাচাই করুন।</p>
    </div>

    <!-- Search Box -->
    <div class="bg-white p-6 sm:p-8 border border-[#D8CCBC]/40 shadow-xs max-w-xl mx-auto">
      <form @submit.prevent="track" class="space-y-4 text-xs">
        <div>
          <label class="block font-semibold uppercase tracking-wider mb-1">অর্ডার নম্বর (যেমন: ORD-...)</label>
          <input
            type="text"
            v-model="orderNumber"
            required
            placeholder="ORD-20261116-XXXXX"
            class="w-full px-4 py-2.5 bg-[#F7F5F0] border border-[#D8CCBC] font-mono focus:outline-none focus:border-[#111111]"
          />
        </div>
        <div>
          <label class="block font-semibold uppercase tracking-wider mb-1">অর্ডারে ব্যবহৃত ইমেইল</label>
          <input
            type="email"
            v-model="email"
            required
            placeholder="name@example.com"
            class="w-full px-4 py-2.5 bg-[#F7F5F0] border border-[#D8CCBC] focus:outline-none focus:border-[#111111]"
          />
        </div>
        <p v-if="error" class="text-rose-600 text-xs">{{ error }}</p>
        <button
          type="submit"
          :disabled="loading"
          class="w-full py-3.5 bg-[#111111] text-white uppercase tracking-widest font-semibold hover:bg-black transition-colors"
        >
          {{ loading ? 'অনুসন্ধান চলছে...' : 'অর্ডার স্ট্যাটাস দেখুন' }}
        </button>
      </form>
    </div>

    <!-- Tracking Result Card -->
    <div v-if="order" class="mt-10 bg-white p-6 sm:p-8 border border-[#D8CCBC]/40 shadow-md space-y-6">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-[#D8CCBC]/30 pb-4 gap-2">
        <div>
          <span class="text-xs text-[#A89B8A] block">অর্ডার রেফারেন্স</span>
          <h4 class="font-mono font-bold text-lg text-[#111111]">{{ order.order_number }}</h4>
        </div>
        <div class="text-right">
          <span class="text-xs text-[#A89B8A] block">বর্তমান অবস্থা</span>
          <span class="inline-block px-3 py-1 bg-emerald-100 text-emerald-800 text-xs uppercase font-semibold">
            {{ order.order_status }}
          </span>
        </div>
      </div>

      <!-- Timeline Steps -->
      <div class="grid grid-cols-4 gap-2 text-center text-[11px] py-4">
        <div :class="['space-y-1', isPassed('pending') ? 'text-[#111111] font-semibold' : 'text-[#A89B8A]']">
          <div :class="['w-8 h-8 rounded-full mx-auto flex items-center justify-center border', isPassed('pending') ? 'bg-[#111111] text-white border-[#111111]' : 'border-[#D8CCBC]']">১</div>
          <span>অর্ডার গৃহীত</span>
        </div>
        <div :class="['space-y-1', isPassed('confirmed') ? 'text-[#111111] font-semibold' : 'text-[#A89B8A]']">
          <div :class="['w-8 h-8 rounded-full mx-auto flex items-center justify-center border', isPassed('confirmed') ? 'bg-[#111111] text-white border-[#111111]' : 'border-[#D8CCBC]']">২</div>
          <span>প্যাকিং সম্পন্ন</span>
        </div>
        <div :class="['space-y-1', isPassed('shipped') ? 'text-[#111111] font-semibold' : 'text-[#A89B8A]']">
          <div :class="['w-8 h-8 rounded-full mx-auto flex items-center justify-center border', isPassed('shipped') ? 'bg-[#111111] text-white border-[#111111]' : 'border-[#D8CCBC]']">৩</div>
          <span>ডেলিভারির পথে</span>
        </div>
        <div :class="['space-y-1', isPassed('delivered') ? 'text-[#111111] font-semibold' : 'text-[#A89B8A]']">
          <div :class="['w-8 h-8 rounded-full mx-auto flex items-center justify-center border', isPassed('delivered') ? 'bg-emerald-700 text-white border-emerald-700' : 'border-[#D8CCBC]']">৪</div>
          <span>ডেলিভার্ড</span>
        </div>
      </div>

      <!-- Tracking Number & Courier info -->
      <div v-if="order.tracking_number" class="p-4 bg-[#F7F5F0] border border-[#D8CCBC]/40 text-xs flex justify-between">
        <span>কুরিয়ার ট্র্যাকিং নম্বর:</span>
        <span class="font-mono font-semibold">{{ order.tracking_number }}</span>
      </div>

      <!-- Ordered Items -->
      <div class="border-t border-[#D8CCBC]/30 pt-4 divide-y divide-[#D8CCBC]/20">
        <div v-for="item in order.items" :key="item.id" class="py-2.5 flex justify-between text-xs">
          <span>{{ item.quantity }}x {{ item.product_name }} ({{ item.size || 'স্ট্যান্ডার্ড' }})</span>
          <span class="font-semibold">৳ {{ Number(item.subtotal).toLocaleString() }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import api from '../../api';
import { useAuthStore } from '../../stores/auth';

const route = useRoute();
const authStore = useAuthStore();

const orderNumber = ref(typeof route.query.order === 'string' ? route.query.order : '');
// Links in order emails carry ?order=…&email=… so the status opens straight away.
const email = ref(typeof route.query.email === 'string' ? route.query.email : authStore.customerUser?.email || '');
const loading = ref(false);
const error = ref('');
const order = ref(null);

const stages = ['pending', 'confirmed', 'processing', 'shipped', 'delivered'];
// Statuses that sit between timeline stages map onto the nearest stage.
const STAGE_ALIASES = { ready_to_ship: 'processing' };

const isPassed = (stage) => {
  if (!order.value) return false;
  const status = STAGE_ALIASES[order.value.order_status] || order.value.order_status;
  const currentIdx = stages.indexOf(status);
  const targetIdx = stages.indexOf(stage);
  return currentIdx >= targetIdx;
};

const track = async () => {
  loading.value = true;
  error.value = '';
  order.value = null;
  try {
    const res = await api.post('/public/track-order', {
      order_number: orderNumber.value.trim(),
      email: email.value.trim(),
    });
    order.value = res.data.data;
  } catch (err) {
    error.value = err.response?.data?.message || 'কোনো অর্ডার রেকর্ড পাওয়া যায়নি। তথ্য পুনরায় যাচাই করুন।';
  } finally {
    loading.value = false;
  }
};

// Arriving from the account page: both fields are known, look the order up straight away.
onMounted(() => {
  if (orderNumber.value && email.value) track();
});
</script>
