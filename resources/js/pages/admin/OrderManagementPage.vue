<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <span class="text-xs uppercase tracking-[0.25em] text-[#A89B8A] font-medium block">Fulfillment Operations</span>
        <h1 class="font-serif text-3xl text-[#111111]">Client Orders</h1>
      </div>

      <!-- Status Filters Tabs -->
      <div class="flex flex-wrap gap-2 text-xs">
        <button
          v-for="st in ['all', 'pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled']"
          :key="st"
          type="button"
          @click="statusFilter = st === 'all' ? '' : st; fetchOrders(1)"
          :class="[
            'px-3 py-1.5 uppercase tracking-wider font-semibold border transition-colors',
            (statusFilter === st || (st === 'all' && !statusFilter))
              ? 'bg-[#111111] text-white border-[#111111]'
              : 'bg-white text-[#111111] border-[#D8CCBC]/60 hover:border-[#111111]'
          ]"
        >
          {{ st }}
        </button>
      </div>
    </div>

    <!-- Data Table -->
    <DataTable
      :columns="columns"
      :items="orders"
      :loading="loading"
      :meta="meta"
      v-model:search="searchQuery"
      search-placeholder="Search by order number or client email..."
      @page="fetchOrders"
      @edit="openDetailModal"
    >
      <template #col-order_number="{ item }">
        <span class="font-mono font-bold text-[#111111]">{{ item.order_number }}</span>
      </template>

      <template #col-total="{ item }">
        <span class="font-semibold">${{ Number(item.total).toFixed(2) }}</span>
      </template>

      <template #col-payment_status="{ item }">
        <span :class="[
          'px-2 py-0.5 text-[10px] uppercase font-semibold tracking-wider',
          item.payment_status === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'
        ]">
          {{ item.payment_status }}
        </span>
      </template>

      <template #col-order_status="{ item }">
        <span :class="[
          'px-2.5 py-1 text-[10px] uppercase font-bold tracking-wider',
          statusBadgeClass(item.order_status)
        ]">
          {{ item.order_status }}
        </span>
      </template>

      <template #row-actions="{ item }">
        <div class="flex items-center gap-2">
          <button
            type="button"
            @click="openDetailModal(item)"
            class="px-3 py-1 bg-[#111111] text-white text-[11px] uppercase tracking-wider font-semibold hover:bg-black"
          >
            Inspect Order
          </button>
          <button
            type="button"
            @click="downloadInvoice(item)"
            title="Download PDF Invoice"
            class="px-2 py-1 border border-[#D8CCBC] text-[#111111] hover:bg-[#F7F5F0] text-[11px] font-medium"
          >
            PDF
          </button>
        </div>
      </template>
    </DataTable>

    <!-- Order Detail & Fulfillment Modal -->
    <BaseModal
      :is-open="detailModalOpen"
      title="Order Details & Status Fulfillment"
      max-width="3xl"
      @close="detailModalOpen = false"
    >
      <div v-if="selectedOrder" class="space-y-6 text-xs text-[#111111]">
        <!-- Overview Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-4 bg-[#F7F5F0] border border-[#D8CCBC]/40">
          <div>
            <span class="text-[10px] text-[#A89B8A] uppercase">Order Reference</span>
            <p class="font-mono font-bold text-sm">{{ selectedOrder.order_number }}</p>
          </div>
          <div>
            <span class="text-[10px] text-[#A89B8A] uppercase">Placed Date</span>
            <p class="font-medium">{{ new Date(selectedOrder.created_at).toLocaleDateString() }}</p>
          </div>
          <div>
            <span class="text-[10px] text-[#A89B8A] uppercase">Payment Method</span>
            <p class="font-semibold uppercase">{{ selectedOrder.payment_method }} ({{ selectedOrder.payment_status }})</p>
          </div>
          <div>
            <span class="text-[10px] text-[#A89B8A] uppercase">Fulfillment Status</span>
            <p class="font-bold text-emerald-800 uppercase">{{ selectedOrder.order_status }}</p>
          </div>
        </div>

        <!-- Ordered Items Table -->
        <div>
          <h5 class="font-serif text-base text-[#111111] mb-2">Ordered Garments ({{ selectedOrder.items?.length || 0 }})</h5>
          <div class="border border-[#D8CCBC]/40 divide-y divide-[#D8CCBC]/30">
            <div
              v-for="item in selectedOrder.items"
              :key="item.id"
              class="p-3 flex items-center justify-between"
            >
              <div class="flex items-center gap-3">
                <img v-if="item.image" :src="item.image" class="w-10 h-12 object-cover bg-[#F7F5F0]" />
                <div>
                  <h6 class="font-serif text-xs font-semibold">{{ item.product_name }}</h6>
                  <p class="text-[10px] text-[#A89B8A]">
                    SKU: {{ item.sku }} • Size: {{ item.size || 'N/A' }} • Color: {{ item.color || 'N/A' }}
                  </p>
                </div>
              </div>
              <div class="text-right">
                <span class="font-medium">${{ Number(item.subtotal).toFixed(2) }}</span>
                <span class="text-[10px] text-[#A89B8A] block">Qty: {{ item.quantity }} × ${{ item.price }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Delivery & Customer Details -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="p-4 bg-white border border-[#D8CCBC]/40 space-y-3">
            <div>
              <h6 class="font-semibold uppercase tracking-wider text-[11px] mb-2">Shipping Destination</h6>
              <p class="font-medium">{{ selectedOrder.customer_name }}</p>
              <p>{{ selectedOrder.shipping_address?.address_line_1 }}</p>
              <p>{{ selectedOrder.shipping_address?.city }}, {{ selectedOrder.shipping_address?.state }} {{ selectedOrder.shipping_address?.postal_code }}</p>
              <p>{{ selectedOrder.shipping_address?.country }}</p>
              <p class="text-[#A89B8A] pt-1">Tel: {{ selectedOrder.customer_phone }}</p>
            </div>
            <div class="pt-2 border-t border-[#D8CCBC]/40">
              <button
                type="button"
                :disabled="downloadingInvoice"
                @click="downloadInvoice(selectedOrder)"
                class="w-full py-2 bg-white hover:bg-[#F7F5F0] text-[#14382A] border border-[#14382A] font-semibold uppercase tracking-wider text-[10px] transition flex items-center justify-center gap-1.5"
              >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>{{ downloadingInvoice ? 'Generating PDF...' : 'Download Official PDF Invoice' }}</span>
              </button>
            </div>
          </div>

          <!-- Status Update Controls -->
          <div class="p-4 bg-[#F7F5F0] border border-[#D8CCBC]/40 space-y-3">
            <h6 class="font-semibold uppercase tracking-wider text-[11px]">Update Fulfillment Status</h6>
            <div>
              <label class="block text-[10px] uppercase font-semibold mb-1">Order Status</label>
              <select v-model="statusUpdateForm.order_status" class="w-full px-3 py-1.5 bg-white border border-[#D8CCBC]">
                <option value="pending">Pending</option>
                <option value="confirmed">Confirmed</option>
                <option value="processing">Processing / Tailoring</option>
                <option value="shipped">Shipped</option>
                <option value="delivered">Delivered</option>
                <option value="cancelled">Cancelled (Restore Stock)</option>
              </select>
            </div>
            <div>
              <label class="block text-[10px] uppercase font-semibold mb-1">Payment Status</label>
              <select v-model="statusUpdateForm.payment_status" class="w-full px-3 py-1.5 bg-white border border-[#D8CCBC]">
                <option value="unpaid">Unpaid</option>
                <option value="pending">Pending</option>
                <option value="paid">Paid</option>
                <option value="refunded">Refunded</option>
              </select>
            </div>
            <div>
              <label class="block text-[10px] uppercase font-semibold mb-1">Tracking Number / Waybill</label>
              <input type="text" v-model="statusUpdateForm.tracking_number" placeholder="e.g. DHL-982347192" class="w-full px-3 py-1.5 bg-white border border-[#D8CCBC]" />
            </div>
            <button
              type="button"
              :disabled="updatingStatus"
              @click="submitStatusUpdate"
              class="w-full py-2 bg-[#111111] text-white font-semibold uppercase tracking-wider text-[10px] hover:bg-black"
            >
              {{ updatingStatus ? 'Updating...' : 'Save Status Update' }}
            </button>
          </div>
        </div>
      </div>
    </BaseModal>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import api from '../../api';
import DataTable from '../../components/admin/DataTable.vue';
import BaseModal from '../../components/common/BaseModal.vue';
import { useToastStore } from '../../stores/toast';

const toast = useToastStore();
const orders = ref([]);
const loading = ref(true);
const meta = ref(null);
const searchQuery = ref('');
const statusFilter = ref('');
const detailModalOpen = ref(false);
const selectedOrder = ref(null);
const updatingStatus = ref(false);
const downloadingInvoice = ref(false);

const columns = [
  { key: 'order_number', label: 'Order #', sortable: true },
  { key: 'customer_name', label: 'Client Name', sortable: true },
  { key: 'customer_email', label: 'Email' },
  { key: 'total', label: 'Total' },
  { key: 'payment_status', label: 'Payment' },
  { key: 'order_status', label: 'Fulfillment' },
  { key: 'created_at', label: 'Placed Date', sortable: true },
];

const statusUpdateForm = reactive({
  order_status: 'pending',
  payment_status: 'unpaid',
  tracking_number: '',
});

const statusBadgeClass = (status) => {
  switch (status) {
    case 'delivered': return 'bg-emerald-100 text-emerald-800';
    case 'shipped': return 'bg-blue-100 text-blue-800';
    case 'processing': return 'bg-amber-100 text-amber-800';
    case 'cancelled': return 'bg-rose-100 text-rose-800';
    default: return 'bg-[#F7F5F0] text-[#111111]';
  }
};

const fetchOrders = async (page = 1) => {
  loading.value = true;
  try {
    const params = {
      page,
      search: searchQuery.value || undefined,
      order_status: statusFilter.value || undefined,
    };
    const res = await api.get('/admin/orders', { params });
    orders.value = res.data.data || [];
    meta.value = res.data.meta || null;
  } catch (err) {
    console.error('Failed to load orders', err);
  } finally {
    loading.value = false;
  }
};

const openDetailModal = async (item) => {
  try {
    const res = await api.get(`/admin/orders/${item.id}`);
    selectedOrder.value = res.data.data;
    statusUpdateForm.order_status = selectedOrder.value.order_status;
    statusUpdateForm.payment_status = selectedOrder.value.payment_status;
    statusUpdateForm.tracking_number = selectedOrder.value.tracking_number || '';
    detailModalOpen.value = true;
  } catch (err) {
    toast.error('Failed to load order details');
  }
};

const downloadInvoice = async (order) => {
  if (!order) return;
  downloadingInvoice.value = true;
  try {
    const res = await api.get(`/admin/orders/${order.id}/invoice`, {
      responseType: 'blob'
    });
    const blob = new Blob([res.data], { type: 'application/pdf' });
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `invoice-${order.order_number}.pdf`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    window.URL.revokeObjectURL(url);
    toast.success(`Invoice downloaded for #${order.order_number}`);
  } catch (err) {
    console.error(err);
    toast.error('Failed to generate PDF invoice');
  } finally {
    downloadingInvoice.value = false;
  }
};

const submitStatusUpdate = async () => {
  if (!selectedOrder.value) return;
  updatingStatus.value = true;
  try {
    await api.put(`/admin/orders/${selectedOrder.value.id}/status`, statusUpdateForm);
    selectedOrder.value.order_status = statusUpdateForm.order_status;
    selectedOrder.value.payment_status = statusUpdateForm.payment_status;
    selectedOrder.value.tracking_number = statusUpdateForm.tracking_number;
    detailModalOpen.value = false;
    toast.success(`Order #${selectedOrder.value.order_number} status updated to ${statusUpdateForm.order_status}`);
    fetchOrders(meta.value?.current_page || 1);
  } catch (err) {
    toast.error(err.response?.data?.message || 'Error updating status');
  } finally {
    updatingStatus.value = false;
  }
};

onMounted(() => {
  fetchOrders(1);
});
</script>
