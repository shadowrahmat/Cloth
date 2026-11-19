<template>
  <div class="space-y-4">
    <!-- Greeting + quick actions -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
      <div>
        <h2 class="text-xl font-bold tracking-tight text-[#171C1A]">{{ greeting }}, {{ firstName }}</h2>
        <p class="text-[12.5px] text-[#9AA39F]">{{ todayLabel }}</p>
      </div>
      <div class="flex flex-wrap gap-2">
        <router-link
          v-for="a in quickActions"
          :key="a.to"
          :to="a.to"
          :class="[
            'inline-flex items-center gap-1.5 h-8 px-3 rounded-lg text-[13px] font-medium transition-colors',
            a.primary ? 'bg-[#14382A] text-white hover:bg-[#16A36A]' : 'text-[#4B5450] hover:bg-white hover:text-[#171C1A]',
          ]"
        >
          <component :is="a.icon" class="w-4 h-4" />
          {{ a.label }}
        </router-link>
      </div>
    </div>

    <!-- To-do strip -->
    <div v-if="ui.alerts.length" class="flex flex-wrap gap-2">
      <router-link
        v-for="a in ui.alerts"
        :key="a.key"
        :to="a.to"
        :class="['group inline-flex items-center gap-2 pl-3 pr-2.5 h-9 rounded-lg border text-[13px] transition-colors', TONE_BOX[a.tone]]"
      >
        <span :class="['font-bold tabular-nums', TONE_TEXT[a.tone]]">{{ bnNumber(a.count) }}</span>
        <span class="text-[#4B5450]">{{ a.label.replace(/^টি /, '') }}</span>
        <ArrowUpRight class="w-3.5 h-3.5 text-[#C4CBC8] group-hover:text-[#16A36A]" />
      </router-link>
    </div>
    <p v-else class="text-[13px] text-[#69736E] flex items-center gap-1.5">
      <span class="text-emerald-600">✓</span> সব কাজ শেষ, এখন কিছু বাকি নেই।
    </p>

    <!-- KPIs -->
    <div class="grid grid-cols-2 md:grid-cols-3 2xl:grid-cols-6 gap-3">
      <MetricCard title="আজকের বিক্রি" :value="money(stats.sales_today)" :subtext="`${bnNumber(stats.orders_today || 0)} টি অর্ডার`" :loading="loading" to="/admin/orders">
        <template #icon><Banknote class="w-4 h-4" /></template>
      </MetricCard>
      <MetricCard title="গত ৭ দিনের বিক্রি" :value="money(stats.sales_week)" :change-pct="stats.sales_week_change" subtext="আগের ৭ দিনের তুলনায়" :loading="loading" to="/admin/reports">
        <template #icon><TrendingUp class="w-4 h-4" /></template>
      </MetricCard>
      <MetricCard title="গত ৭ দিনের অর্ডার" :value="bnNumber(stats.orders_week || 0)" :change-pct="stats.orders_week_change" subtext="আগের ৭ দিনের তুলনায়" :loading="loading" to="/admin/orders">
        <template #icon><ShoppingBag class="w-4 h-4" /></template>
      </MetricCard>
      <MetricCard title="মোট বিক্রি" :value="money(stats.total_sales)" :subtext="`আদায় হয়েছে ${money(stats.collected)}`" :loading="loading" to="/admin/reports">
        <template #icon><Wallet class="w-4 h-4" /></template>
      </MetricCard>
      <MetricCard title="নতুন গ্রাহক (৭ দিন)" :value="bnNumber(stats.customers_week || 0)" :change-pct="stats.customers_week_change" :subtext="`মোট ${bnNumber(stats.total_customers || 0)} জন`" :loading="loading" to="/admin/customers">
        <template #icon><UserPlus class="w-4 h-4" /></template>
      </MetricCard>
      <MetricCard title="পণ্য" :value="bnNumber(stats.total_products || 0)" :subtext="stats.low_stock_count ? `${bnNumber(stats.low_stock_count)} টির স্টক কম` : 'সব পণ্যে স্টক আছে'" :loading="loading" to="/admin/products">
        <template #icon><Shirt class="w-4 h-4" /></template>
      </MetricCard>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-3">
      <div class="lg:col-span-8">
        <ChartCard title="বিক্রি — গত ৭ দিন" subtitle="বাতিল বাদে সব অর্ডারের মোট টাকা">
          <div v-if="loading" class="h-56 animate-pulse bg-slate-50 rounded-lg"></div>
          <div v-else-if="!hasSales" class="h-56 flex flex-col items-center justify-center text-sm text-[#69736E] gap-1">
            <ChartColumn class="w-8 h-8 text-[#C4CBC8]" />
            গত ৭ দিনে কোনো বিক্রি হয়নি
          </div>
          <div v-else class="h-56 relative"><Line :data="revenueChartData" :options="lineOptions" /></div>
        </ChartCard>
      </div>
      <div class="lg:col-span-4">
        <ChartCard title="দৈনিক অর্ডার" subtitle="প্রতিদিন কয়টি অর্ডার এসেছে">
          <div v-if="loading" class="h-56 animate-pulse bg-slate-50 rounded-lg"></div>
          <div v-else class="h-56 relative"><Bar :data="orderVolumeChartData" :options="barOptions" /></div>
        </ChartCard>
      </div>
    </div>

    <!-- Recent orders + low stock -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-3 items-start">
      <section class="lg:col-span-7 bg-white rounded-xl border border-[#ECEFED]">
        <div class="px-4 py-3 border-b border-[#F0F2F1] flex items-center justify-between">
          <h3 class="text-sm font-semibold text-[#171C1A]">সাম্প্রতিক অর্ডার</h3>
          <router-link to="/admin/orders" class="text-xs font-semibold text-[#16A36A] hover:underline">সব অর্ডার →</router-link>
        </div>
        <div v-if="loading" class="p-5 space-y-3">
          <div v-for="n in 4" :key="n" class="h-10 bg-slate-50 rounded animate-pulse"></div>
        </div>
        <div v-else-if="recentOrders.length" class="divide-y divide-[#F0F2F1]">
          <router-link
            v-for="o in recentOrders"
            :key="o.id"
            :to="{ path: '/admin/orders', query: { search: o.order_number } }"
            class="flex items-center gap-4 px-4 py-2.5 hover:bg-[#F7F8FA] transition-colors"
          >
            <div class="min-w-0 flex-1">
              <p class="text-sm font-semibold text-[#171C1A] truncate">{{ o.customer_name }}</p>
              <p class="text-xs text-[#69736E] font-mono truncate">#{{ o.order_number }} · {{ timeAgo(o.created_at) }}</p>
            </div>
            <StatusBadge :status="o.order_status" :label="ORDER_STATUS[o.order_status]" />
            <span class="w-24 text-right text-sm font-semibold text-[#171C1A] tabular-nums">{{ money(o.total) }}</span>
          </router-link>
        </div>
        <p v-else class="px-5 py-10 text-center text-sm text-[#69736E]">এখনো কোনো অর্ডার আসেনি।</p>
      </section>

      <section class="lg:col-span-5 bg-white rounded-xl border border-[#ECEFED]">
        <div class="px-4 py-3 border-b border-[#F0F2F1] flex items-center justify-between">
          <h3 class="text-sm font-semibold text-[#171C1A] flex items-center gap-2">
            <TriangleAlert v-if="lowStock.length" class="w-4 h-4 text-amber-500" /> স্টক কম
          </h3>
          <router-link to="/admin/inventory" class="text-xs font-semibold text-[#16A36A] hover:underline">ইনভেন্টরি →</router-link>
        </div>
        <div v-if="loading" class="p-5 space-y-3">
          <div v-for="n in 4" :key="n" class="h-10 bg-slate-50 rounded animate-pulse"></div>
        </div>
        <div v-else-if="lowStock.length" class="divide-y divide-[#F0F2F1]">
          <router-link
            v-for="p in lowStock"
            :key="p.id"
            :to="{ path: '/admin/products', query: { search: p.sku || p.name } }"
            class="flex items-center gap-3 px-4 py-2 hover:bg-[#F7F8FA] transition-colors"
          >
            <img v-if="p.featured_image" :src="p.featured_image" :alt="p.name" class="w-9 h-11 object-cover rounded bg-[#F7F8FA] shrink-0" loading="lazy" />
            <div v-else class="w-9 h-11 rounded bg-[#F2F4F3] shrink-0"></div>
            <div class="flex-1 min-w-0">
              <p class="text-sm text-[#171C1A] truncate">{{ p.name }}</p>
              <p class="text-xs text-[#69736E] truncate">{{ p.category?.name || '—' }}</p>
            </div>
            <span :class="['px-2 py-0.5 rounded-full text-xs font-semibold tabular-nums', p.stock <= 0 ? 'bg-rose-50 text-rose-700' : 'bg-amber-50 text-amber-800']">
              {{ p.stock <= 0 ? 'শেষ' : `${bnNumber(p.stock)} টি` }}
            </span>
          </router-link>
        </div>
        <p v-else class="px-5 py-10 text-center text-sm text-[#69736E]">সব পণ্যে যথেষ্ট স্টক আছে ✓</p>
      </section>
    </div>

    <!-- Recent activity -->
    <section v-if="activities.length" class="bg-white rounded-xl border border-[#ECEFED]">
      <div class="px-4 py-3 border-b border-[#F0F2F1] flex items-center justify-between">
        <h3 class="text-sm font-semibold text-[#171C1A] flex items-center gap-2"><Clock class="w-4 h-4 text-[#69736E]" /> সাম্প্রতিক কার্যক্রম</h3>
        <router-link v-if="authStore.hasPermission('view_settings')" to="/admin/audit-logs" class="text-xs font-semibold text-[#16A36A] hover:underline">সব দেখুন →</router-link>
      </div>
      <ul class="divide-y divide-[#F0F2F1]">
        <li v-for="a in activities" :key="a.id" class="px-4 py-2 flex items-center gap-3 text-[13px]">
          <span class="w-7 h-7 rounded-full bg-[#F2F4F3] text-[#4B5450] text-xs font-bold flex items-center justify-center shrink-0">
            {{ (a.user?.name || '?').charAt(0).toUpperCase() }}
          </span>
          <span class="flex-1 min-w-0 truncate text-[#171C1A]">{{ a.description || a.action }}</span>
          <span class="text-xs text-[#9AA39F] whitespace-nowrap">{{ timeAgo(a.created_at) }}</span>
        </li>
      </ul>
    </section>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import {
  Plus, ShoppingBag, Truck, LayoutTemplate, ArrowUpRight, Banknote, TrendingUp,
  Wallet, UserPlus, Shirt, ChartColumn, TriangleAlert, Clock,
} from 'lucide-vue-next';
import {
  Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, BarElement, Tooltip, Filler,
} from 'chart.js';
import { Line, Bar } from 'vue-chartjs';
import api from '../../api';
import MetricCard from '../../components/admin/MetricCard.vue';
import ChartCard from '../../components/admin/ChartCard.vue';
import StatusBadge from '../../components/admin/StatusBadge.vue';
import { useAuthStore } from '../../stores/auth';
import { useAdminUiStore } from '../../stores/adminUi';
import { money, bnNumber } from '../../admin-nav';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, BarElement, Tooltip, Filler);

const authStore = useAuthStore();
const ui = useAdminUiStore();

const data = ref(null);
const loading = ref(true);

const stats = computed(() => data.value?.stats || {});
const salesChart = computed(() => data.value?.sales_chart || []);
const recentOrders = computed(() => data.value?.recent_orders || []);
const lowStock = computed(() => data.value?.low_stock_products || []);
const activities = computed(() => data.value?.recent_activities || []);
const hasSales = computed(() => salesChart.value.some((d) => d.sales > 0));

const TONE_TEXT = { warn: 'text-amber-600', danger: 'text-rose-600', info: 'text-sky-600' };
const TONE_BOX = {
  warn: 'border-amber-200/70 bg-amber-50/60 hover:bg-amber-50',
  danger: 'border-rose-200/70 bg-rose-50/60 hover:bg-rose-50',
  info: 'border-sky-200/70 bg-sky-50/60 hover:bg-sky-50',
};
const ORDER_STATUS = {
  pending: 'অপেক্ষমাণ', confirmed: 'কনফার্ম', processing: 'প্রস্তুত হচ্ছে', ready_to_ship: 'শিপিং রেডি',
  shipped: 'পাঠানো হয়েছে', delivered: 'ডেলিভারড', cancelled: 'বাতিল',
};

const firstName = computed(() => (authStore.adminUser?.name || 'অ্যাডমিন').split(' ')[0]);
const greeting = computed(() => {
  const h = new Date().getHours();
  if (h < 12) return 'শুভ সকাল';
  if (h < 17) return 'শুভ দুপুর';
  if (h < 20) return 'শুভ সন্ধ্যা';
  return 'শুভ রাত্রি';
});
const todayLabel = new Date().toLocaleDateString('bn-BD', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });

const quickActions = computed(() => [
  { label: 'নতুন পণ্য', to: '/admin/products?new=1', icon: Plus, primary: true, permission: 'create_products' },
  { label: 'অর্ডার দেখুন', to: '/admin/orders', icon: ShoppingBag, permission: 'view_orders' },
  { label: 'কুরিয়ারে পাঠান', to: '/admin/logistics', icon: Truck, permission: 'view_orders' },
  { label: 'হোমপেজ সাজান', to: '/admin/homepage-builder', icon: LayoutTemplate, permission: 'view_cms' },
].filter((a) => authStore.hasPermission(a.permission)));

const timeAgo = (value) => {
  if (!value) return '';
  const diff = (Date.now() - new Date(value).getTime()) / 1000;
  if (diff < 60) return 'এইমাত্র';
  if (diff < 3600) return `${bnNumber(Math.floor(diff / 60))} মিনিট আগে`;
  if (diff < 86400) return `${bnNumber(Math.floor(diff / 3600))} ঘণ্টা আগে`;
  if (diff < 86400 * 7) return `${bnNumber(Math.floor(diff / 86400))} দিন আগে`;
  return new Date(value).toLocaleDateString('bn-BD', { day: 'numeric', month: 'short' });
};

// --- charts
const revenueChartData = computed(() => ({
  labels: salesChart.value.map((d) => d.date),
  datasets: [{
    data: salesChart.value.map((d) => d.sales),
    borderColor: '#16A36A',
    backgroundColor: 'rgba(22, 163, 106, 0.10)',
    pointBackgroundColor: '#14382A',
    pointBorderColor: '#fff',
    pointRadius: 4,
    pointHoverRadius: 6,
    fill: true,
    tension: 0.35,
  }],
}));
const orderVolumeChartData = computed(() => ({
  labels: salesChart.value.map((d) => d.date),
  datasets: [{ data: salesChart.value.map((d) => d.orders), backgroundColor: '#14382A', hoverBackgroundColor: '#16A36A', borderRadius: 4 }],
}));
const baseOptions = (fmt) => ({
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
    tooltip: { backgroundColor: '#14382A', padding: 10, cornerRadius: 6, callbacks: { label: (c) => ' ' + fmt(c.parsed.y) } },
  },
  scales: {
    x: { grid: { display: false }, ticks: { color: '#69736E', font: { size: 11 } } },
    y: { beginAtZero: true, grid: { color: '#EEF1EF' }, ticks: { color: '#69736E', font: { size: 11 }, precision: 0, callback: (v) => fmt(v) } },
  },
});
const lineOptions = baseOptions((v) => money(v));
const barOptions = baseOptions((v) => `${v} টি`);

onMounted(async () => {
  ui.fetchBadges({ force: true });
  try {
    const res = await api.get('/admin/dashboard');
    data.value = res.data.data;
  } catch (err) {
    console.error('Failed to load dashboard', err);
  } finally {
    loading.value = false;
  }
});
</script>
