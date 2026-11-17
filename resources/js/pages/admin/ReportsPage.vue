<template>
  <div class="space-y-6">
    <!-- Header with Filters & Export -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-[#E7EBE8]">
      <div>
        <h2 class="text-xl font-bold tracking-tight text-[#171C1A]">Sales & Financial Reports</h2>
        <p class="text-xs text-[#69736E] mt-0.5">Transactional breakdowns, discount analytics, and net revenue summaries.</p>
      </div>

      <div class="flex items-center gap-3">
        <!-- Date Preset Dropdown -->
        <select
          v-model="datePreset"
          @change="handlePresetChange"
          class="text-xs bg-white border border-[#E7EBE8] rounded-md px-3 py-2 text-[#171C1A] focus:outline-none focus:ring-1 focus:ring-[#16A36A]"
        >
          <option value="last_7">Last 7 Days</option>
          <option value="last_30">Last 30 Days</option>
          <option value="this_month">This Month</option>
          <option value="all_time">All Time</option>
        </select>

        <button
          type="button"
          @click="exportCsv"
          class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-[#14382A] text-white text-xs font-semibold rounded-md hover:bg-[#16A36A] transition-colors shadow-xs"
        >
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
          </svg>
          <span>Export CSV</span>
        </button>
      </div>
    </div>

    <!-- Core Financial Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <MetricCard
        title="Gross Sales"
        :value="formatCurrency(metrics.gross_sales)"
        subtext="Total transaction volume"
      >
        <template #icon>💰</template>
      </MetricCard>

      <MetricCard
        title="Net Sales (Paid)"
        :value="formatCurrency(metrics.net_sales)"
        subtext="Realized revenue"
      >
        <template #icon>💵</template>
      </MetricCard>

      <MetricCard
        title="Total Discounts"
        :value="formatCurrency(metrics.total_discounts)"
        subtext="Coupons and promotions"
        trend="down"
      >
        <template #icon>🎟️</template>
      </MetricCard>

      <MetricCard
        title="Average Order Value"
        :value="formatCurrency(metrics.average_order_value)"
        subtext="AOV across paid orders"
      >
        <template #icon>📊</template>
      </MetricCard>
    </div>

    <!-- Secondary Metrics Strip -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
      <div class="bg-white p-4 rounded-lg border border-[#E7EBE8]">
        <span class="text-[11px] uppercase tracking-wider text-[#69736E] font-medium block">Total Orders</span>
        <span class="text-xl font-bold text-[#171C1A] mt-1 block">{{ metrics.total_orders || 0 }}</span>
      </div>

      <div class="bg-white p-4 rounded-lg border border-[#E7EBE8]">
        <span class="text-[11px] uppercase tracking-wider text-[#69736E] font-medium block">Units Sold</span>
        <span class="text-xl font-bold text-[#171C1A] mt-1 block">{{ metrics.total_units_sold || 0 }}</span>
      </div>

      <div class="bg-white p-4 rounded-lg border border-[#E7EBE8]">
        <span class="text-[11px] uppercase tracking-wider text-[#69736E] font-medium block">Shipping Revenue</span>
        <span class="text-xl font-bold text-[#171C1A] mt-1 block">{{ formatCurrency(metrics.shipping_revenue) }}</span>
      </div>

      <div class="bg-white p-4 rounded-lg border border-[#E7EBE8]">
        <span class="text-[11px] uppercase tracking-wider text-[#69736E] font-medium block">Refunded Amount</span>
        <span class="text-xl font-bold text-rose-600 mt-1 block">{{ formatCurrency(metrics.refunded_amount) }}</span>
      </div>
    </div>

    <!-- Breakdown Tables: Payment Methods & Order Lifecycle -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Payment Methods Distribution -->
      <div class="bg-white rounded-lg border border-[#E7EBE8] p-5 shadow-xs">
        <h3 class="text-sm font-semibold text-[#171C1A] mb-4 pb-2 border-b border-[#E7EBE8] flex items-center justify-between">
          <span>Payment Gateway Breakdown</span>
          <span class="text-[11px] font-mono text-[#69736E]">bKash • Nagad • Card • COD</span>
        </h3>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead>
              <tr class="text-[#69736E] border-b border-[#E7EBE8]">
                <th class="pb-2 font-medium">Gateway / Method</th>
                <th class="pb-2 font-medium text-center">Orders</th>
                <th class="pb-2 font-medium text-right">Volume</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#E7EBE8]">
              <tr v-for="pm in paymentMethods" :key="pm.payment_method" class="hover:bg-[#F7F8FA]">
                <td class="py-2.5 font-medium text-[#171C1A] uppercase">{{ pm.payment_method }}</td>
                <td class="py-2.5 text-center text-[#69736E]">{{ pm.count }}</td>
                <td class="py-2.5 text-right font-semibold text-[#14382A]">{{ formatCurrency(pm.total) }}</td>
              </tr>
              <tr v-if="!paymentMethods.length">
                <td colspan="3" class="py-6 text-center text-slate-400">No payment data recorded in this timeframe.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Order Status Distribution -->
      <div class="bg-white rounded-lg border border-[#E7EBE8] p-5 shadow-xs">
        <h3 class="text-sm font-semibold text-[#171C1A] mb-4 pb-2 border-b border-[#E7EBE8] flex items-center justify-between">
          <span>Fulfillment Status Distribution</span>
          <span class="text-[11px] font-mono text-[#69736E]">Lifecycle Pipeline</span>
        </h3>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead>
              <tr class="text-[#69736E] border-b border-[#E7EBE8]">
                <th class="pb-2 font-medium">Order Status</th>
                <th class="pb-2 font-medium text-right">Order Count</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#E7EBE8]">
              <tr v-for="os in orderStatuses" :key="os.order_status" class="hover:bg-[#F7F8FA]">
                <td class="py-2.5">
                  <StatusBadge :status="os.order_status" />
                </td>
                <td class="py-2.5 text-right font-bold text-[#171C1A]">{{ os.count }}</td>
              </tr>
              <tr v-if="!orderStatuses.length">
                <td colspan="2" class="py-6 text-center text-slate-400">No order status records found.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import MetricCard from '../../components/admin/MetricCard.vue';
import StatusBadge from '../../components/admin/StatusBadge.vue';
import api from '../../api';

const datePreset = ref('last_30');
const metrics = ref({});
const paymentMethods = ref([]);
const orderStatuses = ref([]);

const formatCurrency = (val) => {
  return `$${Number(val || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
};

const fetchReports = async () => {
  try {
    const res = await api.get('/admin/reports');
    if (res.data?.data) {
      metrics.value = res.data.data.metrics || {};
      paymentMethods.value = res.data.data.payment_methods || [];
      orderStatuses.value = res.data.data.order_statuses || [];
    }
  } catch (err) {
    console.error('Failed to load reports', err);
  }
};

const handlePresetChange = () => {
  fetchReports();
};

const exportCsv = () => {
  const rows = [
    ['Metric', 'Value'],
    ['Gross Sales', metrics.value.gross_sales],
    ['Net Sales (Paid)', metrics.value.net_sales],
    ['Total Discounts', metrics.value.total_discounts],
    ['Average Order Value', metrics.value.average_order_value],
    ['Total Orders', metrics.value.total_orders],
    ['Units Sold', metrics.value.total_units_sold],
    ['Shipping Revenue', metrics.value.shipping_revenue],
    ['Refunded Amount', metrics.value.refunded_amount],
  ];

  const csvContent = 'data:text/csv;charset=utf-8,' + rows.map(e => e.join(',')).join('\n');
  const encodedUri = encodeURI(csvContent);
  const link = document.createElement('a');
  link.setAttribute('href', encodedUri);
  link.setAttribute('download', `sales-report-${Date.now()}.csv`);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};

onMounted(() => {
  fetchReports();
});
</script>
