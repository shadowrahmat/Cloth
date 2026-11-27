<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-[#E7EBE8]">
      <div>
        <h2 class="text-xl font-bold tracking-tight text-[#171C1A]">Logistics & Courier Integration</h2>
        <p class="text-xs text-[#69736E] mt-0.5">Automated Steadfast Courier consignments, airway bill tracking, and delivery dispatch pipeline.</p>
      </div>

      <div class="flex items-center gap-3">
        <button
          type="button"
          v-can="'create_orders'" @click="openConsignmentModal"
          class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-[#14382A] text-white text-xs font-semibold rounded-md hover:bg-[#16A36A] transition-colors shadow-xs"
        >
          <span>🚚</span>
          <span>Create Consignment</span>
        </button>
      </div>
    </div>

    <!-- Active Shipments Table -->
    <div class="bg-white rounded-lg border border-[#E7EBE8] overflow-hidden shadow-xs">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead>
            <tr class="bg-[#F7F8FA] text-[#69736E] border-b border-[#E7EBE8]">
              <th class="py-3 px-4 font-semibold">Consignment ID</th>
              <th class="py-3 px-4 font-semibold">Courier</th>
              <th class="py-3 px-4 font-semibold">Recipient Details</th>
              <th class="py-3 px-4 font-semibold text-right">COD Amount</th>
              <th class="py-3 px-4 font-semibold text-center">Courier Status</th>
              <th class="py-3 px-4 font-semibold text-right">Tracking</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#E7EBE8]">
            <tr v-for="s in shipments" :key="s.id" class="hover:bg-[#F7F8FA]">
              <td class="py-3 px-4 font-mono font-bold text-[#14382A]">
                <div>{{ s.consignment_id || 'Pending Generation' }}</div>
                <div class="text-[10px] text-[#69736E] font-sans">Order: #{{ s.order?.order_number }}</div>
              </td>
              <td class="py-3 px-4">
                <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-emerald-100 text-emerald-900 border border-emerald-300">
                  {{ s.courier_name }}
                </span>
              </td>
              <td class="py-3 px-4 text-[#171C1A]">
                <div class="font-semibold">{{ s.recipient_name }}</div>
                <div class="text-[11px] font-mono text-[#69736E]">{{ s.recipient_phone }}</div>
                <div class="text-[11px] text-slate-500 truncate max-w-xs">{{ s.recipient_address }}</div>
              </td>
              <td class="py-3 px-4 text-right font-bold text-[#14382A]">
                ${{ Number(s.cod_amount || 0).toFixed(2) }}
              </td>
              <td class="py-3 px-4 text-center">
                <StatusBadge :status="s.status" />
              </td>
              <td class="py-3 px-4 text-right font-mono text-xs">
                <span class="px-2 py-1 bg-slate-100 border border-slate-200 rounded-sm font-semibold text-[#171C1A]">
                  {{ s.tracking_code || 'Pending' }}
                </span>
              </td>
            </tr>
            <tr v-if="!shipments.length">
              <td colspan="6" class="py-8 text-center text-slate-400">
                No courier consignments dispatched yet. Click "Create Consignment" to dispatch an order.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create Consignment Modal -->
    <BaseModal
      :is-open="isModalOpen"
      title="Create Steadfast Courier Consignment"
      @close="isModalOpen = false"
    >
      <form @submit.prevent="createConsignment" class="space-y-4 text-xs">
        <div>
          <label class="block text-xs font-semibold text-[#171C1A] mb-1">Select Ready Order *</label>
          <select
            v-model="form.order_id"
            required
            @change="prefillOrderDetails"
            class="w-full bg-white border border-[#E7EBE8] rounded-md px-3 py-2 text-[#171C1A] focus:outline-none focus:ring-1 focus:ring-[#16A36A]"
          >
            <option value="" disabled>Choose an order to dispatch...</option>
            <option v-for="order in eligibleOrders" :key="order.id" :value="order.id">
              {{ order.order_number }} — {{ order.customer_name }} (${{ order.total }})
            </option>
          </select>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-semibold text-[#171C1A] mb-1">Courier Partner *</label>
            <select
              v-model="form.courier_name"
              class="w-full bg-white border border-[#E7EBE8] rounded-md px-3 py-2 text-[#171C1A] focus:outline-none focus:ring-1 focus:ring-[#16A36A]"
            >
              <option value="Steadfast Courier">Steadfast Courier (API Direct)</option>
              <option value="Pathao Express">Pathao Express</option>
              <option value="RedX Logistics">RedX Logistics</option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-semibold text-[#171C1A] mb-1">COD Collection Amount ($) *</label>
            <input
              v-model.number="form.cod_amount"
              type="number"
              step="0.01"
              required
              class="w-full bg-white border border-[#E7EBE8] rounded-md px-3 py-2 text-[#171C1A] focus:outline-none focus:ring-1 focus:ring-[#16A36A]"
            />
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-semibold text-[#171C1A] mb-1">Recipient Name *</label>
            <input
              v-model="form.recipient_name"
              type="text"
              required
              class="w-full bg-white border border-[#E7EBE8] rounded-md px-3 py-2 text-[#171C1A] focus:outline-none focus:ring-1 focus:ring-[#16A36A]"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-[#171C1A] mb-1">Recipient Mobile *</label>
            <input
              v-model="form.recipient_phone"
              type="text"
              required
              class="w-full bg-white border border-[#E7EBE8] rounded-md px-3 py-2 text-[#171C1A] focus:outline-none focus:ring-1 focus:ring-[#16A36A]"
            />
          </div>
        </div>

        <div>
          <label class="block text-xs font-semibold text-[#171C1A] mb-1">Full Delivery Address *</label>
          <textarea
            v-model="form.recipient_address"
            rows="2"
            required
            class="w-full bg-white border border-[#E7EBE8] rounded-md p-3 text-[#171C1A] focus:outline-none focus:ring-1 focus:ring-[#16A36A]"
          ></textarea>
        </div>

        <div class="pt-4 flex justify-end gap-3 border-t border-[#E7EBE8]">
          <button
            type="button"
            @click="isModalOpen = false"
            class="px-4 py-2 border border-[#E7EBE8] text-xs font-semibold text-[#171C1A] rounded-md hover:bg-slate-50"
          >
            Cancel
          </button>
          <button
            type="submit"
            class="px-4 py-2 bg-[#14382A] hover:bg-[#16A36A] text-white text-xs font-semibold rounded-md shadow-xs transition-colors"
          >
            Dispatch to Steadfast
          </button>
        </div>
      </form>
    </BaseModal>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import BaseModal from '../../components/admin/BaseModal.vue';
import StatusBadge from '../../components/admin/StatusBadge.vue';
import { useToastStore } from '../../stores/toast';
import api from '../../api';

const toast = useToastStore();
const shipments = ref([]);
const eligibleOrders = ref([]);
const isModalOpen = ref(false);

const form = ref({
  order_id: '',
  courier_name: 'Steadfast Courier',
  cod_amount: 0,
  recipient_name: '',
  recipient_phone: '',
  recipient_address: '',
});

const fetchShipments = async () => {
  try {
    const res = await api.get('/admin/logistics');
    shipments.value = res.data?.data?.data || [];
  } catch (err) {
    console.error('Failed to load shipments', err);
  }
};

const fetchOrders = async () => {
  try {
    const res = await api.get('/admin/orders');
    eligibleOrders.value = res.data?.data?.data || [];
  } catch (err) {
    console.error('Failed to load eligible orders', err);
  }
};

const openConsignmentModal = () => {
  fetchOrders();
  form.value = {
    order_id: '',
    courier_name: 'Steadfast Courier',
    cod_amount: 0,
    recipient_name: '',
    recipient_phone: '',
    recipient_address: '',
  };
  isModalOpen.value = true;
};

const prefillOrderDetails = () => {
  const o = eligibleOrders.value.find(ord => ord.id === form.value.order_id);
  if (o) {
    form.value.recipient_name = o.customer_name;
    form.value.recipient_phone = o.customer_phone || '';
    const addr = o.shipping_address;
    if (typeof addr === 'object' && addr !== null) {
      form.value.recipient_address = `${addr.address_line_1 || ''}, ${addr.city || ''}, ${addr.country || ''}`;
    } else {
      form.value.recipient_address = addr || '';
    }
    form.value.cod_amount = o.payment_method === 'cod' ? o.total : 0;
  }
};

const createConsignment = async () => {
  try {
    await api.post('/admin/logistics/consignment', form.value);
    toast.success('Steadfast consignment dispatched successfully');
    isModalOpen.value = false;
    fetchShipments();
  } catch (err) {
    toast.error(err.response?.data?.message || 'Failed to dispatch consignment');
  }
};

onMounted(() => {
  fetchShipments();
});
</script>
