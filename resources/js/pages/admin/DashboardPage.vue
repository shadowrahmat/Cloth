<template>
  <div class="space-y-8">
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-[#E7EBE8]">
      <div>
        <span class="text-xs uppercase tracking-[0.2em] text-[#16A36A] font-semibold block">Overview & Analytics</span>
        <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-[#171C1A]">Executive Performance Dashboard</h1>
      </div>

      <div class="flex flex-wrap items-center gap-2.5">
        <router-link
          to="/admin/categories"
          class="px-4 py-2 border border-emerald-300 bg-emerald-50/60 text-[#14382A] text-xs uppercase tracking-wider font-semibold rounded-md hover:bg-emerald-100 transition-colors shadow-2xs inline-flex items-center gap-1.5"
        >
          <span class="text-sm">📁</span>
          <span>ক্যাটাগরি সমূহ ({{ stats.total_categories || 16 }})</span>
        </router-link>

        <router-link
          to="/admin/products"
          class="px-4 py-2 bg-[#14382A] text-white text-xs uppercase tracking-wider font-semibold rounded-md hover:bg-[#16A36A] transition-colors shadow-2xs inline-flex items-center gap-1.5"
        >
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          <span>Add Product</span>
        </router-link>

        <router-link
          to="/admin/logistics"
          class="px-4 py-2 border border-[#E7EBE8] bg-white text-[#171C1A] text-xs uppercase tracking-wider font-semibold rounded-md hover:bg-[#F7F8FA] transition-colors shadow-2xs inline-flex items-center gap-1.5"
        >
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
          </svg>
          <span>Dispatch Orders</span>
        </router-link>

        <router-link
          to="/admin/homepage-builder"
          class="px-4 py-2 border border-[#E7EBE8] bg-white text-[#171C1A] text-xs uppercase tracking-wider font-semibold rounded-md hover:bg-[#F7F8FA] transition-colors shadow-2xs"
        >
          Homepage Builder
        </router-link>
      </div>
    </div>

    <!-- 6 KPI Metrics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
      <MetricCard
        title="Total Revenue"
        :value="'$' + Number(stats.total_sales || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })"
        change="14.2%"
        trend="up"
        subtext="Settled orders"
        :loading="loading"
      />

      <MetricCard
        title="Settled Orders"
        :value="String(stats.total_orders || 0)"
        change="8.5%"
        trend="up"
        subtext="Client checkouts"
        :loading="loading"
      />

      <MetricCard
        title="Pending Fulfillment"
        :value="String(stats.pending_orders || 0)"
        trend="down"
        subtext="Needs packing"
        :loading="loading"
      />

      <MetricCard
        title="Catalog Garments"
        :value="String(stats.total_products || 0)"
        subtext="Active in store"
        :loading="loading"
      />

      <MetricCard
        title="Low Stock Warning"
        :value="String(stats.low_stock_count || 0)"
        change="Urgent"
        trend="down"
        subtext="≤ 5 units remaining"
        :loading="loading"
      />

      <MetricCard
        title="Registered Clients"
        :value="String(stats.total_customers || 0)"
        change="12.0%"
        trend="up"
        subtext="Client accounts"
        :loading="loading"
      />
    </div>

    <!-- Chart Visualization Section -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
      <!-- 7-Day Revenue Trajectory Chart (8 cols) -->
      <div class="lg:col-span-8">
        <ChartCard
          title="Revenue Trajectory (Last 7 Days)"
          subtitle="Real-time daily settled revenue from customer checkouts"
        >
          <template #actions>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[11px] font-semibold text-emerald-800 bg-emerald-50 rounded-full border border-emerald-200">
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 animate-pulse"></span>
              Live Feed
            </span>
          </template>

          <div v-if="loading" class="h-64 flex items-center justify-center text-xs text-[#69736E]">
            Loading revenue trajectory...
          </div>
          <div v-else class="h-64 relative">
            <Line :data="revenueChartData" :options="chartOptions" />
          </div>
        </ChartCard>
      </div>

      <!-- Order Volume Breakdown (4 cols) -->
      <div class="lg:col-span-4">
        <ChartCard
          title="Daily Orders Volume"
          subtitle="Orders placed per day"
        >
          <div v-if="loading" class="h-64 flex items-center justify-center text-xs text-[#69736E]">
            Loading volume chart...
          </div>
          <div v-else class="h-64 relative">
            <Bar :data="orderVolumeChartData" :options="barOptions" />
          </div>
        </ChartCard>
      </div>
    </div>

    <!-- Recent Orders & Critical Stock Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
      <!-- Recent Orders (7 cols) -->
      <div class="lg:col-span-7 bg-white rounded-lg p-6 border border-[#E7EBE8] shadow-xs space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-[#E7EBE8]">
          <div>
            <h3 class="text-base font-semibold text-[#171C1A]">Recent Client Orders</h3>
            <p class="text-xs text-[#69736E]">Latest luxury checkouts requiring fulfillment</p>
          </div>
          <router-link to="/admin/orders" class="text-xs font-semibold text-[#16A36A] hover:underline">
            View All Orders →
          </router-link>
        </div>

        <div v-if="recentOrders.length" class="divide-y divide-[#E7EBE8] text-xs">
          <div
            v-for="o in recentOrders"
            :key="o.id"
            class="py-3 flex items-center justify-between gap-4"
          >
            <div class="min-w-0">
              <span class="font-mono font-bold text-[#171C1A] text-xs block truncate">#{{ o.order_number }}</span>
              <p class="text-[11px] text-[#69736E] truncate">
                {{ o.customer_name }} • {{ new Date(o.created_at).toLocaleDateString() }}
              </p>
            </div>
            <div class="flex items-center gap-3 shrink-0">
              <StatusBadge :status="o.order_status" />
              <span class="font-semibold text-sm text-[#171C1A]">${{ Number(o.total).toFixed(2) }}</span>
              <router-link
                to="/admin/orders"
                class="px-2.5 py-1 text-[10px] uppercase font-bold tracking-wider text-[#14382A] border border-[#E7EBE8] rounded-xs hover:bg-[#F7F8FA]"
              >
                Inspect
              </router-link>
            </div>
          </div>
        </div>
        <p v-else class="text-xs text-[#69736E] py-8 text-center">No orders received yet.</p>
      </div>

      <!-- Top Garments & Inventory Alerts (5 cols) -->
      <div class="lg:col-span-5 bg-white rounded-lg p-6 border border-[#E7EBE8] shadow-xs space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-[#E7EBE8]">
          <div>
            <h3 class="text-base font-semibold text-[#171C1A]">Catalog Spotlight & Stock</h3>
            <p class="text-xs text-[#69736E]">Current inventory velocity & pieces</p>
          </div>
          <router-link to="/admin/products" class="text-xs font-semibold text-[#16A36A] hover:underline">
            Manage Catalog →
          </router-link>
        </div>

        <div v-if="topProducts.length" class="divide-y divide-[#E7EBE8] text-xs">
          <div
            v-for="p in topProducts"
            :key="p.id"
            class="py-2.5 flex items-center gap-3"
          >
            <img
              :src="p.featured_image || '/placeholder.jpg'"
              :alt="p.name"
              class="w-10 h-12 object-cover rounded-xs bg-[#F7F8FA] border border-[#E7EBE8] shrink-0"
            />
            <div class="flex-1 min-w-0">
              <h6 class="font-medium text-xs text-[#171C1A] truncate">{{ p.name }}</h6>
              <span class="text-[11px] text-[#69736E]">${{ Number(p.sale_price || p.price).toFixed(2) }}</span>
            </div>
            <span :class="[
              'px-2 py-0.5 text-[10px] font-semibold rounded-xs uppercase tracking-wider',
              p.stock <= 5 ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-emerald-50 text-emerald-800 border border-emerald-200'
            ]">
              {{ p.stock }} in stock
            </span>
          </div>
        </div>
        <p v-else class="text-xs text-[#69736E] py-8 text-center">No garments in catalog yet.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import api from '../../api';
import MetricCard from '../../components/admin/MetricCard.vue';
import ChartCard from '../../components/admin/ChartCard.vue';
import StatusBadge from '../../components/admin/StatusBadge.vue';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  Title,
  Tooltip,
  Legend,
  Filler,
} from 'chart.js';
import { Line, Bar } from 'vue-chartjs';

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  Title,
  Tooltip,
  Legend,
  Filler
);

const dashboardData = ref(null);
const loading = ref(true);

const stats = computed(() => dashboardData.value?.stats || {});
const salesChart = computed(() => dashboardData.value?.sales_chart || []);
const recentOrders = computed(() => dashboardData.value?.recent_orders || []);
const topProducts = computed(() => dashboardData.value?.top_products || []);

const revenueChartData = computed(() => {
  const labels = salesChart.value.map(d => d.date);
  const data = salesChart.value.map(d => d.sales);

  return {
    labels,
    datasets: [
      {
        label: 'Revenue ($)',
        backgroundColor: 'rgba(22, 163, 106, 0.12)',
        borderColor: '#16A36A',
        pointBackgroundColor: '#14382A',
        pointBorderColor: '#FFFFFF',
        pointHoverBackgroundColor: '#FFFFFF',
        pointHoverBorderColor: '#16A36A',
        pointRadius: 4,
        pointHoverRadius: 6,
        fill: true,
        tension: 0.35,
        data,
      },
    ],
  };
});

const orderVolumeChartData = computed(() => {
  const labels = salesChart.value.map(d => d.date);
  const data = salesChart.value.map(d => d.orders);

  return {
    labels,
    datasets: [
      {
        label: 'Orders',
        backgroundColor: '#14382A',
        borderRadius: 4,
        hoverBackgroundColor: '#16A36A',
        data,
      },
    ],
  };
});

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
    tooltip: {
      backgroundColor: '#14382A',
      titleFont: { size: 12, weight: '600' },
      bodyFont: { size: 12 },
      padding: 10,
      cornerRadius: 6,
      callbacks: {
        label: (context) => ` $${context.parsed.y.toFixed(2)}`,
      },
    },
  },
  scales: {
    x: {
      grid: { display: false },
      ticks: { color: '#69736E', font: { size: 11 } },
    },
    y: {
      grid: { color: '#E7EBE8' },
      ticks: {
        color: '#69736E',
        font: { size: 11 },
        callback: (val) => `$${val}`,
      },
    },
  },
};

const barOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
    tooltip: {
      backgroundColor: '#14382A',
      titleFont: { size: 12, weight: '600' },
      bodyFont: { size: 12 },
      padding: 10,
      cornerRadius: 6,
      callbacks: {
        label: (context) => ` ${context.parsed.y} orders`,
      },
    },
  },
  scales: {
    x: {
      grid: { display: false },
      ticks: { color: '#69736E', font: { size: 11 } },
    },
    y: {
      grid: { color: '#E7EBE8' },
      ticks: {
        color: '#69736E',
        font: { size: 11 },
        stepSize: 1,
      },
    },
  },
};

onMounted(async () => {
  try {
    const res = await api.get('/admin/dashboard');
    dashboardData.value = res.data.data;
  } catch (err) {
    console.error('Failed to load dashboard data', err);
  } finally {
    loading.value = false;
  }
});
</script>