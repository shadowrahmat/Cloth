<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-[#E7EBE8]">
      <div>
        <h2 class="text-xl font-bold tracking-tight text-[#171C1A]">Customer Directory</h2>
        <p class="text-xs text-[#69736E] mt-0.5">Manage registered client profiles, purchase history, and delivery addresses.</p>
      </div>

      <div class="flex items-center gap-3">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search customers..."
          class="text-xs bg-white border border-[#E7EBE8] rounded-md px-3 py-2 text-[#171C1A] placeholder-[#69736E] focus:outline-none focus:ring-1 focus:ring-[#16A36A] w-56"
          @input="handleSearch"
        />
      </div>
    </div>

    <!-- Customers Table -->
    <div class="bg-white rounded-lg border border-[#E7EBE8] overflow-hidden shadow-xs">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead>
            <tr class="bg-[#F7F8FA] text-[#69736E] border-b border-[#E7EBE8]">
              <th class="py-3 px-4 font-semibold">Customer</th>
              <th class="py-3 px-4 font-semibold">Contact</th>
              <th class="py-3 px-4 font-semibold text-center">Orders</th>
              <th class="py-3 px-4 font-semibold text-right">Lifetime Spent</th>
              <th class="py-3 px-4 font-semibold text-right">Registered</th>
              <th class="py-3 px-4 font-semibold text-center">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#E7EBE8]">
            <tr v-for="c in customers" :key="c.id" class="hover:bg-[#F7F8FA]">
              <td class="py-3 px-4 font-medium text-[#171C1A]">
                <div class="flex items-center gap-2.5">
                  <div class="w-7 h-7 rounded-full bg-[#14382A] text-white flex items-center justify-center font-bold text-[11px] shrink-0">
                    {{ c.name.charAt(0).toUpperCase() }}
                  </div>
                  <div>
                    <span class="block font-semibold">{{ c.name }}</span>
                    <span class="text-[11px] text-[#69736E] font-mono">ID: #CUS-{{ c.id }}</span>
                  </div>
                </div>
              </td>
              <td class="py-3 px-4 text-[#69736E]">
                <div>{{ c.email }}</div>
                <div class="text-[11px] font-mono text-[#171C1A]">{{ c.phone || '—' }}</div>
              </td>
              <td class="py-3 px-4 text-center">
                <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200/80">
                  {{ c.orders_count || 0 }} {{ c.orders_count === 1 ? 'order' : 'orders' }}
                </span>
              </td>
              <td class="py-3 px-4 text-right font-bold text-[#14382A]">
                ${{ Number(c.lifetime_spent || 0).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}
              </td>
              <td class="py-3 px-4 text-right text-[#69736E] font-mono text-[11px]">
                {{ new Date(c.created_at).toLocaleDateString() }}
              </td>
              <td class="py-3 px-4 text-center">
                <button
                  type="button"
                  @click="openCustomerDetail(c)"
                  class="px-2.5 py-1 text-xs font-semibold text-[#14382A] bg-emerald-50 hover:bg-[#14382A] hover:text-white rounded-md transition-colors"
                >
                  View 360° Profile
                </button>
              </td>
            </tr>
            <tr v-if="!customers.length && !loading">
              <td colspan="6" class="py-8 text-center text-slate-400">No customers found.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Customer Detail Modal -->
    <BaseModal
      :is-open="isDetailModalOpen"
      :title="`Client Profile: ${selectedCustomer?.name || ''}`"
      size="xl"
      @close="isDetailModalOpen = false"
    >
      <div v-if="selectedCustomer" class="space-y-6">
        <!-- Overview summary strip -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 bg-[#F7F8FA] p-4 rounded-lg border border-[#E7EBE8]">
          <div>
            <span class="text-[10px] uppercase tracking-wider text-[#69736E] block">Email & Phone</span>
            <span class="text-xs font-semibold text-[#171C1A] block mt-0.5">{{ selectedCustomer.email }}</span>
            <span class="text-xs text-[#69736E]">{{ selectedCustomer.phone || 'No phone recorded' }}</span>
          </div>
          <div>
            <span class="text-[10px] uppercase tracking-wider text-[#69736E] block">Lifetime Value</span>
            <span class="text-base font-bold text-emerald-800 block mt-0.5">
              ${{ Number(selectedCustomer.lifetime_spent || 0).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}
            </span>
          </div>
          <div>
            <span class="text-[10px] uppercase tracking-wider text-[#69736E] block">Account Status</span>
            <span class="inline-block mt-0.5 px-2 py-0.5 bg-emerald-100 text-emerald-800 rounded-sm text-[10px] font-bold uppercase">
              Active Client
            </span>
          </div>
        </div>

        <!-- Order History List -->
        <div>
          <h4 class="text-sm font-semibold text-[#171C1A] mb-3 pb-1 border-b border-[#E7EBE8]">Recent Transaction History</h4>
          <div class="space-y-2 max-h-56 overflow-y-auto">
            <div
              v-for="order in selectedCustomer.orders || []"
              :key="order.id"
              class="flex items-center justify-between p-3 bg-white border border-[#E7EBE8] rounded-md text-xs hover:border-[#16A36A]/40"
            >
              <div>
                <span class="font-semibold text-[#171C1A] block">{{ order.order_number }}</span>
                <span class="text-[11px] text-[#69736E]">{{ new Date(order.created_at).toLocaleDateString() }}</span>
              </div>
              <div class="text-right">
                <span class="font-bold text-[#14382A] block">${{ order.total }}</span>
                <span class="text-[10px] uppercase font-mono text-emerald-700">{{ order.payment_status }}</span>
              </div>
            </div>
            <div v-if="!selectedCustomer.orders?.length" class="text-xs text-slate-400 py-3 text-center">
              No orders placed by this customer yet.
            </div>
          </div>
        </div>
      </div>
    </BaseModal>
  </div>
</template>

<script setup>
import { useAdminDeepLink } from '../../composables/useAdminDeepLink';
import { ref, onMounted } from 'vue';
import BaseModal from '../../components/admin/BaseModal.vue';
import api from '../../api';

const customers = ref([]);
const loading = ref(false);
const searchQuery = ref('');
const isDetailModalOpen = ref(false);
const selectedCustomer = ref(null);

let searchTimeout = null;

const fetchCustomers = async () => {
  loading.value = true;
  try {
    const res = await api.get('/admin/customers', { params: { search: searchQuery.value } });
    customers.value = res.data?.data?.data || [];
  } catch (err) {
    console.error('Failed to load customers', err);
  } finally {
    loading.value = false;
  }
};

const handleSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(fetchCustomers, 300);
};

const openCustomerDetail = async (c) => {
  try {
    const res = await api.get(`/admin/customers/${c.id}`);
    selectedCustomer.value = res.data?.data || c;
    isDetailModalOpen.value = true;
  } catch (err) {
    selectedCustomer.value = c;
    isDetailModalOpen.value = true;
  }
};

onMounted(() => {
  fetchCustomers();
});

// Open from links like ?search=... or ?new=1 (sidebar, Ctrl+K, dashboard)
useAdminDeepLink({ search: searchQuery, reload: fetchCustomers });
</script>
