<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-[#E7EBE8]">
      <div>
        <h2 class="text-xl font-bold tracking-tight text-[#171C1A]">Sales & Product Analysis</h2>
        <p class="text-xs text-[#69736E] mt-0.5">Interactive visual trends, revenue velocities, and category profitability.</p>
      </div>

      <div class="flex items-center gap-3">
        <select
          v-model="timeframeDays"
          @change="fetchAnalysis"
          class="text-xs bg-white border border-[#E7EBE8] rounded-md px-3 py-2 text-[#171C1A] focus:outline-none focus:ring-1 focus:ring-[#16A36A]"
        >
          <option :value="7">Last 7 Days</option>
          <option :value="14">Last 14 Days</option>
          <option :value="30">Last 30 Days</option>
          <option :value="60">Last 60 Days</option>
        </select>
      </div>
    </div>

    <!-- Charts Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Revenue Trend Line Chart -->
      <ChartCard
        title="Revenue Trajectory ($)"
        subtitle="Daily paid revenue across the selected timeframe"
      >
        <div v-if="loading" class="h-64 flex items-center justify-center text-xs text-[#69736E]">
          Loading chart analytics...
        </div>
        <div v-else class="h-64 relative">
          <Line :data="revenueChartData" :options="chartOptions" />
        </div>
      </ChartCard>

      <!-- Order Volume Bar Chart -->
      <ChartCard
        title="Daily Order Volume"
        subtitle="Number of orders placed per day"
      >
        <div v-if="loading" class="h-64 flex items-center justify-center text-xs text-[#69736E]">
          Loading chart analytics...
        </div>
        <div v-else class="h-64 relative">
          <Bar :data="orderVolumeChartData" :options="barChartOptions" />
        </div>
      </ChartCard>
    </div>

    <!-- Top Selling Categories Table -->
    <div class="bg-white rounded-lg border border-[#E7EBE8] p-5 shadow-xs">
      <div class="flex items-center justify-between pb-3 border-b border-[#E7EBE8] mb-4">
        <div>
          <h3 class="text-sm font-semibold text-[#171C1A]">Top Performing Product Categories</h3>
          <p class="text-xs text-[#69736E]">Categories driving the highest volume and paid transactions.</p>
        </div>
        <span class="text-xs font-mono text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-md font-semibold">
          High Margin
        </span>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead>
            <tr class="text-[#69736E] border-b border-[#E7EBE8]">
              <th class="pb-2.5 font-medium">Category</th>
              <th class="pb-2.5 font-medium text-center">Units Sold</th>
              <th class="pb-2.5 font-medium text-right">Total Revenue Generated</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#E7EBE8]">
            <tr v-for="cat in topCategories" :key="cat.name" class="hover:bg-[#F7F8FA]">
              <td class="py-3 font-semibold text-[#171C1A]">{{ cat.name }}</td>
              <td class="py-3 text-center text-[#69736E] font-mono">{{ cat.units_sold }}</td>
              <td class="py-3 text-right font-bold text-[#14382A]">${{ Number(cat.revenue || 0).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}</td>
            </tr>
            <tr v-if="!topCategories.length">
              <td colspan="3" class="py-6 text-center text-slate-400">No category sales recorded yet.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import ChartCard from '../../components/admin/ChartCard.vue';
import api from '../../api';
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

const timeframeDays = ref(30);
const loading = ref(true);
const timelineData = ref([]);
const topCategories = ref([]);

const revenueChartData = computed(() => {
  const labels = timelineData.value.map(t => t.date);
  const data = timelineData.value.map(t => t.sales);

  return {
    labels,
    datasets: [
      {
        label: 'Paid Revenue ($)',
        backgroundColor: 'rgba(22, 163, 106, 0.1)',
        borderColor: '#16A36A',
        pointBackgroundColor: '#14382A',
        pointBorderColor: '#FFFFFF',
        pointHoverBackgroundColor: '#FFFFFF',
        pointHoverBorderColor: '#16A36A',
        data,
        fill: true,
        tension: 0.35,
      },
    ],
  };
});

const orderVolumeChartData = computed(() => {
  const labels = timelineData.value.map(t => t.date);
  const data = timelineData.value.map(t => t.orders);

  return {
    labels,
    datasets: [
      {
        label: 'Orders Count',
        backgroundColor: '#14382A',
        borderRadius: 4,
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
      titleFont: { size: 12 },
      bodyFont: { size: 12 },
      padding: 10,
    },
  },
  scales: {
    x: { grid: { display: false }, ticks: { font: { size: 10 } } },
    y: { grid: { color: '#E7EBE8' }, ticks: { font: { size: 10 } } },
  },
};

const barChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
    tooltip: {
      backgroundColor: '#14382A',
      padding: 10,
    },
  },
  scales: {
    x: { grid: { display: false }, ticks: { font: { size: 10 } } },
    y: { grid: { color: '#E7EBE8' }, ticks: { stepSize: 1, font: { size: 10 } } },
  },
};

const fetchAnalysis = async () => {
  loading.value = true;
  try {
    const res = await api.get('/admin/reports/sales', { params: { days: timeframeDays.value } });
    if (res.data?.data) {
      timelineData.value = res.data.data.timeline || [];
      topCategories.value = res.data.data.top_categories || [];
    }
  } catch (err) {
    console.error('Failed to load analysis', err);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchAnalysis();
});
</script>
