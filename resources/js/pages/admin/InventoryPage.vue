<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <span class="text-xs uppercase tracking-[0.25em] text-[#A89B8A] font-medium block">Stock Operations</span>
        <h1 class="font-serif text-3xl text-[#111111]">Inventory & Stock Ledger</h1>
      </div>

      <!-- Mode Tabs -->
      <div class="flex gap-2 text-xs">
        <button
          type="button"
          @click="activeTab = 'stock'"
          :class="[
            'px-4 py-2 uppercase tracking-wider font-semibold border transition-colors',
            activeTab === 'stock' ? 'bg-[#111111] text-white border-[#111111]' : 'bg-white text-[#111111] border-[#D8CCBC]'
          ]"
        >
          Stock Levels
        </button>
        <button
          type="button"
          @click="activeTab = 'movements'; fetchMovements()"
          :class="[
            'px-4 py-2 uppercase tracking-wider font-semibold border transition-colors',
            activeTab === 'movements' ? 'bg-[#111111] text-white border-[#111111]' : 'bg-white text-[#111111] border-[#D8CCBC]'
          ]"
        >
          Movement Ledger
        </button>
      </div>
    </div>

    <!-- Stock Levels Tab -->
    <div v-if="activeTab === 'stock'" class="space-y-6">
      <DataTable
        :columns="stockColumns"
        :items="products"
        :loading="loading"
        :meta="meta"
        v-model:search="searchQuery"
        search-placeholder="Search by product name or SKU..."
        @page="fetchStock"
      >
        <template #col-stock="{ item }">
          <span :class="[
            'px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider',
            item.stock <= 0 ? 'bg-rose-100 text-rose-800' : (item.stock <= 5 ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800')
          ]">
            {{ item.stock }} in stock
          </span>
        </template>

        <template #row-actions="{ item }">
          <button
            type="button"
            v-can="'update_inventory'" @click="openAdjustModal(item)"
            class="px-3 py-1 bg-[#111111] text-white text-[10px] uppercase tracking-wider font-semibold hover:bg-black"
          >
            Adjust Stock
          </button>
        </template>
      </DataTable>
    </div>

    <!-- Movement Ledger Tab -->
    <div v-else class="space-y-6">
      <div class="bg-white p-6 border border-[#D8CCBC]/40 shadow-xs">
        <h4 class="font-serif text-lg text-[#111111] mb-4">Stock Movement History Audit</h4>
        <div v-if="movements.length" class="overflow-x-auto">
          <table class="w-full text-left text-xs border-collapse">
            <thead>
              <tr class="bg-[#F7F5F0] border-b border-[#D8CCBC]/40 text-[#A89B8A] uppercase tracking-wider text-[10px]">
                <th class="p-3">Garment</th>
                <th class="p-3">Event Type</th>
                <th class="p-3">Change Qty</th>
                <th class="p-3">Before → After</th>
                <th class="p-3">Reference / Notes</th>
                <th class="p-3">Date</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#D8CCBC]/30">
              <tr v-for="m in movements" :key="m.id" class="hover:bg-[#F7F5F0]/50">
                <td class="p-3 font-serif font-medium">{{ m.product?.name || 'Garment #' + m.product_id }}</td>
                <td class="p-3 capitalize font-semibold">{{ m.type?.replace('_', ' ') }}</td>
                <td :class="['p-3 font-mono font-bold', m.quantity > 0 ? 'text-emerald-700' : 'text-rose-600']">
                  {{ m.quantity > 0 ? '+' : '' }}{{ m.quantity }}
                </td>
                <td class="p-3 font-mono text-[#A89B8A]">{{ m.previous_stock }} → {{ m.current_stock }}</td>
                <td class="p-3 text-[11px] text-[#A89B8A]">{{ m.notes || m.reference_type || '—' }}</td>
                <td class="p-3 text-[11px] text-[#A89B8A]">{{ new Date(m.created_at).toLocaleString() }}</td>
              </tr>
            </tbody>
          </table>
        </div>
        <p v-else class="text-xs text-[#A89B8A] py-8 text-center">No stock movements logged yet.</p>
      </div>
    </div>

    <!-- Stock Adjustment Modal -->
    <BaseModal
      :is-open="adjustModalOpen"
      title="Manual Stock Adjustment"
      max-width="md"
      @close="adjustModalOpen = false"
    >
      <form v-if="selectedProduct" @submit.prevent="submitAdjustment" class="space-y-4 text-xs text-[#111111]">
        <div class="p-3 bg-[#F7F5F0] border border-[#D8CCBC]/40 space-y-1">
          <h5 class="font-serif text-sm font-semibold">{{ selectedProduct.name }}</h5>
          <p class="text-[#A89B8A]">Current On-Hand Stock: <strong>{{ selectedProduct.stock }} units</strong></p>
        </div>

        <div>
          <label class="block font-semibold uppercase tracking-wider mb-1">Quantity Adjustment (+ to add, - to subtract) *</label>
          <input
            type="number"
            v-model.number="adjustForm.quantity"
            required
            placeholder="e.g. 15 or -3"
            class="w-full px-3 py-2 bg-white border border-[#D8CCBC]"
          />
        </div>

        <div>
          <label class="block font-semibold uppercase tracking-wider mb-1">Reason / Event Type *</label>
          <select v-model="adjustForm.type" required class="w-full px-3 py-2 bg-white border border-[#D8CCBC]">
            <option value="manual_adjustment">Manual Inventory Reconciliation</option>
            <option value="purchase">New Supplier Batch Inbound</option>
            <option value="return">Client Exchange / Restock</option>
          </select>
        </div>

        <div>
          <label class="block font-semibold uppercase tracking-wider mb-1">Audit Ledger Notes</label>
          <textarea rows="3" v-model="adjustForm.notes" placeholder="e.g. Biella mill batch delivery #482" class="w-full px-3 py-2 bg-white border border-[#D8CCBC]"></textarea>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-[#D8CCBC]/40">
          <button type="button" @click="adjustModalOpen = false" class="px-4 py-2 border border-[#D8CCBC]">Cancel</button>
          <button type="submit" :disabled="submitting" class="px-6 py-2 bg-[#111111] text-white uppercase tracking-wider font-semibold hover:bg-black">
            {{ submitting ? 'Saving...' : 'Record Adjustment' }}
          </button>
        </div>
      </form>
    </BaseModal>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import api from '../../api';
import DataTable from '../../components/admin/DataTable.vue';
import BaseModal from '../../components/common/BaseModal.vue';

const activeTab = ref('stock');
const products = ref([]);
const movements = ref([]);
const loading = ref(true);
const meta = ref(null);
const searchQuery = ref('');

const adjustModalOpen = ref(false);
const selectedProduct = ref(null);
const submitting = ref(false);

const adjustForm = reactive({
  quantity: 0,
  type: 'manual_adjustment',
  notes: '',
});

const stockColumns = [
  { key: 'featured_image', label: 'Image', type: 'image' },
  { key: 'name', label: 'Garment Name', sortable: true },
  { key: 'sku', label: 'SKU' },
  { key: 'category', label: 'Department', type: 'relation', relationField: 'name' },
  { key: 'stock', label: 'Units On Hand' },
];

const fetchStock = async (page = 1) => {
  loading.value = true;
  try {
    const params = { page, search: searchQuery.value || undefined };
    const res = await api.get('/admin/inventory', { params });
    products.value = res.data.data || [];
    meta.value = res.data.meta || null;
  } catch (err) {
    console.error('Failed to load inventory', err);
  } finally {
    loading.value = false;
  }
};

const fetchMovements = async () => {
  try {
    const res = await api.get('/admin/inventory/movements');
    movements.value = res.data.data || [];
  } catch (err) {
    console.error('Failed to load movements', err);
  }
};

const openAdjustModal = (item) => {
  selectedProduct.value = item;
  adjustForm.quantity = 0;
  adjustForm.type = 'manual_adjustment';
  adjustForm.notes = '';
  adjustModalOpen.value = true;
};

const submitAdjustment = async () => {
  if (!selectedProduct.value) return;
  submitting.value = true;
  try {
    await api.post('/admin/inventory/adjust', {
      product_id: selectedProduct.value.id,
      quantity: adjustForm.quantity,
      type: adjustForm.type,
      notes: adjustForm.notes,
    });
    adjustModalOpen.value = false;
    fetchStock(meta.value?.current_page || 1);
  } catch (err) {
    alert(err.response?.data?.message || 'Adjustment failed');
  } finally {
    submitting.value = false;
  }
};

onMounted(() => {
  fetchStock(1);
});
</script>
