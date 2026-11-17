<template>
  <div class="py-12 sm:py-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center max-w-md mx-auto mb-10 space-y-2">
      <span class="text-xs uppercase tracking-[0.25em] text-[#A89B8A] font-medium block">Concierge Fulfillment</span>
      <h1 class="font-serif text-3xl sm:text-4xl text-[#111111]">Track Your Shipment</h1>
      <p class="text-xs text-[#A89B8A]">Enter your order number and email to inspect fulfillment progress.</p>
    </div>

    <!-- Search Box -->
    <div class="bg-white p-6 sm:p-8 border border-[#D8CCBC]/40 shadow-xs max-w-xl mx-auto">
      <form @submit.prevent="track" class="space-y-4 text-xs">
        <div>
          <label class="block font-semibold uppercase tracking-wider mb-1">Order Number (e.g. ORD-...)</label>
          <input
            type="text"
            v-model="orderNumber"
            required
            placeholder="ORD-20261116-XXXXX"
            class="w-full px-4 py-2.5 bg-[#F7F5F0] border border-[#D8CCBC] font-mono focus:outline-none focus:border-[#111111]"
          />
        </div>
        <div>
          <label class="block font-semibold uppercase tracking-wider mb-1">Billing / Shipping Email</label>
          <input
            type="email"
            v-model="email"
            required
            placeholder="elena@example.com"
            class="w-full px-4 py-2.5 bg-[#F7F5F0] border border-[#D8CCBC] focus:outline-none focus:border-[#111111]"
          />
        </div>
        <p v-if="error" class="text-rose-600 text-xs">{{ error }}</p>
        <button
          type="submit"
          :disabled="loading"
          class="w-full py-3.5 bg-[#111111] text-white uppercase tracking-widest font-semibold hover:bg-black transition-colors"
        >
          {{ loading ? 'Querying Logistics Ledger...' : 'Inspect Status' }}
        </button>
      </form>
    </div>

    <!-- Tracking Result Card -->
    <div v-if="order" class="mt-10 bg-white p-6 sm:p-8 border border-[#D8CCBC]/40 shadow-md space-y-6">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-[#D8CCBC]/30 pb-4 gap-2">
        <div>
          <span class="text-xs text-[#A89B8A] block">Order Reference</span>
          <h4 class="font-mono font-bold text-lg text-[#111111]">{{ order.order_number }}</h4>
        </div>
        <div class="text-right">
          <span class="text-xs text-[#A89B8A] block">Current Status</span>
          <span class="inline-block px-3 py-1 bg-emerald-100 text-emerald-800 text-xs uppercase font-semibold">
            {{ order.order_status }}
          </span>
        </div>
      </div>

      <!-- Timeline Steps -->
      <div class="grid grid-cols-4 gap-2 text-center text-[11px] py-4">
        <div :class="['space-y-1', isPassed('pending') ? 'text-[#111111] font-semibold' : 'text-[#A89B8A]']">
          <div :class="['w-8 h-8 rounded-full mx-auto flex items-center justify-center border', isPassed('pending') ? 'bg-[#111111] text-white border-[#111111]' : 'border-[#D8CCBC]']">1</div>
          <span>Received</span>
        </div>
        <div :class="['space-y-1', isPassed('confirmed') ? 'text-[#111111] font-semibold' : 'text-[#A89B8A]']">
          <div :class="['w-8 h-8 rounded-full mx-auto flex items-center justify-center border', isPassed('confirmed') ? 'bg-[#111111] text-white border-[#111111]' : 'border-[#D8CCBC]']">2</div>
          <span>Tailoring / Packed</span>
        </div>
        <div :class="['space-y-1', isPassed('shipped') ? 'text-[#111111] font-semibold' : 'text-[#A89B8A]']">
          <div :class="['w-8 h-8 rounded-full mx-auto flex items-center justify-center border', isPassed('shipped') ? 'bg-[#111111] text-white border-[#111111]' : 'border-[#D8CCBC]']">3</div>
          <span>In Transit</span>
        </div>
        <div :class="['space-y-1', isPassed('delivered') ? 'text-[#111111] font-semibold' : 'text-[#A89B8A]']">
          <div :class="['w-8 h-8 rounded-full mx-auto flex items-center justify-center border', isPassed('delivered') ? 'bg-emerald-700 text-white border-emerald-700' : 'border-[#D8CCBC]']">4</div>
          <span>Delivered</span>
        </div>
      </div>

      <!-- Tracking Number & Courier info -->
      <div v-if="order.tracking_number" class="p-4 bg-[#F7F5F0] border border-[#D8CCBC]/40 text-xs flex justify-between">
        <span>Courier Waybill / Tracking:</span>
        <span class="font-mono font-semibold">{{ order.tracking_number }}</span>
      </div>

      <!-- Ordered Items -->
      <div class="border-t border-[#D8CCBC]/30 pt-4 divide-y divide-[#D8CCBC]/20">
        <div v-for="item in order.items" :key="item.id" class="py-2.5 flex justify-between text-xs">
          <span>{{ item.quantity }}x {{ item.product_name }} ({{ item.size || 'Standard' }})</span>
          <span class="font-medium">${{ Number(item.subtotal).toFixed(2) }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import api from '../../api';

const orderNumber = ref('');
const email = ref('');
const loading = ref(false);
const error = ref('');
const order = ref(null);

const stages = ['pending', 'confirmed', 'processing', 'shipped', 'delivered'];

const isPassed = (stage) => {
  if (!order.value) return false;
  const currentIdx = stages.indexOf(order.value.order_status);
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
    error.value = err.response?.data?.message || 'No order record found.';
  } finally {
    loading.value = false;
  }
};
</script>
