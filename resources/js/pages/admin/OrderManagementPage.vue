<template>
  <div class="space-y-5">
    <!-- Header -->
    <div>
      <h2 class="text-xl font-bold tracking-tight text-admin-text">অর্ডার</h2>
      <p class="text-xs text-admin-muted mt-0.5">নতুন অর্ডার কনফার্ম করুন, কুরিয়ারে দিন, ডেলিভারি হলে আপডেট করুন। স্ট্যাটাস বদলালে গ্রাহক ইমেইল পাবে।</p>
    </div>

    <!-- Status tabs -->
    <div class="flex flex-wrap gap-2">
      <button v-for="s in statusTabs" :key="s.key" type="button" @click="statusFilter = s.key"
        :class="['om-chip', statusFilter === s.key && 'is-active']">
        {{ s.label }}
        <span class="om-chip-count" :class="s.key === 'pending' && (counts.pending || 0) > 0 && statusFilter !== 'pending' && 'is-alert'">{{ bnNumber(s.key ? counts[s.key] || 0 : counts.all || 0) }}</span>
      </button>
    </div>

    <div class="adm-card overflow-hidden">
      <div class="p-4 border-b border-admin-border">
        <div class="relative max-w-md">
          <Search :size="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-admin-muted pointer-events-none" />
          <input v-model="searchQuery" type="search" class="adm-input pl-9" placeholder="অর্ডার নম্বর, নাম, ফোন বা ইমেইল…" />
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-[13px] min-w-[820px]">
          <thead class="bg-admin-bg text-[11.5px] text-admin-muted">
            <tr>
              <th class="font-semibold px-4 py-3">অর্ডার</th>
              <th class="font-semibold px-3 py-3">গ্রাহক</th>
              <th class="font-semibold px-3 py-3">পণ্য</th>
              <th class="font-semibold px-3 py-3">মোট</th>
              <th class="font-semibold px-3 py-3">পেমেন্ট</th>
              <th class="font-semibold px-3 py-3">অবস্থা</th>
              <th class="font-semibold px-3 py-3 pr-4 text-right">কাজ</th>
            </tr>
          </thead>
          <tbody v-if="loading">
            <tr v-for="n in 6" :key="n" class="border-t border-admin-border"><td colspan="7" class="px-4 py-3"><div class="h-10 rounded-lg bg-admin-bg animate-pulse"></div></td></tr>
          </tbody>
          <tbody v-else-if="orders.length">
            <tr v-for="o in orders" :key="o.id" class="border-t border-admin-border hover:bg-admin-bg/60 cursor-pointer" @click="openDetail(o)">
              <td class="px-4 py-3">
                <span class="block font-mono font-semibold text-admin-text text-[12.5px]">{{ o.order_number }}</span>
                <span class="block text-[11.5px] text-admin-muted mt-0.5" :title="new Date(o.created_at).toLocaleString()">{{ ago(o.created_at) }}</span>
              </td>
              <td class="px-3 py-3">
                <span class="block font-medium text-admin-text">{{ o.customer_name }}</span>
                <span class="block text-[11.5px] text-admin-muted">{{ o.customer_phone || o.customer_email }}</span>
              </td>
              <td class="px-3 py-3 text-admin-muted text-[12.5px]">{{ bnNumber(itemCount(o)) }}টি</td>
              <td class="px-3 py-3 font-semibold text-admin-text whitespace-nowrap">{{ money(o.total) }}</td>
              <td class="px-3 py-3"><span :class="['adm-chip', payMeta(o.payment_status).chip]">{{ payMeta(o.payment_status).label }}</span></td>
              <td class="px-3 py-3"><span :class="['adm-chip', statusMeta(o.order_status).chip]">{{ statusMeta(o.order_status).label }}</span></td>
              <td class="px-3 py-3 pr-4" @click.stop>
                <div class="flex justify-end gap-1">
                  <button type="button" class="adm-btn !py-1.5" @click="openDetail(o)">দেখুন</button>
                  <button type="button" class="adm-btn !py-1.5 !px-2.5" title="ইনভয়েস PDF" @click="downloadInvoice(o)"><FileDown :size="15" /></button>
                </div>
              </td>
            </tr>
          </tbody>
          <tbody v-else>
            <tr><td colspan="7" class="px-4 py-16 text-center">
              <ShoppingBag :size="34" class="mx-auto text-admin-muted/60" :stroke-width="1.5" />
              <p class="mt-3 text-sm font-semibold text-admin-text">{{ searchQuery || statusFilter ? 'এই ফিল্টারে কোনো অর্ডার নেই' : 'এখনো কোনো অর্ডার আসেনি' }}</p>
            </td></tr>
          </tbody>
        </table>
      </div>

      <div v-if="meta && meta.last_page > 1" class="flex items-center justify-between px-4 py-3 border-t border-admin-border text-xs text-admin-muted">
        <span>মোট {{ bnNumber(meta.total) }}টি</span>
        <div class="flex items-center gap-1">
          <button type="button" class="adm-btn px-2.5" :disabled="meta.current_page <= 1" @click="fetchOrders(meta.current_page - 1)"><ChevronLeft :size="15" /></button>
          <span class="px-3">{{ bnNumber(meta.current_page) }} / {{ bnNumber(meta.last_page) }}</span>
          <button type="button" class="adm-btn px-2.5" :disabled="meta.current_page >= meta.last_page" @click="fetchOrders(meta.current_page + 1)"><ChevronRight :size="15" /></button>
        </div>
      </div>
    </div>

    <!-- Detail -->
    <BaseModal :is-open="detailOpen" :title="selected ? `অর্ডার ${selected.order_number}` : 'অর্ডার'" max-width="4xl" @close="detailOpen = false">
      <div v-if="selected" class="grid md:grid-cols-[minmax(0,1fr)_300px] gap-5 text-[13px] text-admin-text">
        <div class="space-y-4 min-w-0">
          <!-- Items -->
          <div class="rounded-xl border border-admin-border overflow-hidden">
            <div v-for="it in selected.items" :key="it.id" class="flex items-center gap-3 p-3 border-b border-admin-border last:border-b-0">
              <img v-if="it.image" :src="it.image" alt="" class="w-11 h-14 rounded-md object-cover bg-admin-bg shrink-0" />
              <div class="min-w-0 flex-1">
                <p class="font-semibold truncate">{{ it.product_name }}</p>
                <p class="text-[11.5px] text-admin-muted">{{ [it.size && 'সাইজ ' + it.size, it.color].filter(Boolean).join(' · ') || '—' }} · <span class="font-mono">{{ it.sku }}</span></p>
              </div>
              <div class="text-right whitespace-nowrap">
                <p class="font-semibold">{{ money(it.subtotal) }}</p>
                <p class="text-[11.5px] text-admin-muted">{{ bnNumber(it.quantity) }} × {{ money(it.price) }}</p>
              </div>
            </div>
            <div class="bg-admin-bg p-3 space-y-1 text-[12.5px]">
              <div class="flex justify-between"><span class="text-admin-muted">পণ্যের দাম</span><span>{{ money(selected.subtotal) }}</span></div>
              <div v-if="Number(selected.discount_amount) > 0" class="flex justify-between text-emerald-700"><span>ডিসকাউন্ট {{ selected.coupon_code ? `(${selected.coupon_code})` : '' }}</span><span>− {{ money(selected.discount_amount) }}</span></div>
              <div class="flex justify-between"><span class="text-admin-muted">ডেলিভারি ({{ selected.shipping_method || '—' }})</span><span>{{ Number(selected.shipping_amount) > 0 ? money(selected.shipping_amount) : 'ফ্রি' }}</span></div>
              <div class="flex justify-between font-bold text-[14px] pt-1.5 border-t border-admin-border"><span>সর্বমোট</span><span>{{ money(selected.total) }}</span></div>
            </div>
          </div>

          <!-- Customer -->
          <div class="grid sm:grid-cols-2 gap-4">
            <div class="rounded-xl border border-admin-border p-4">
              <p class="text-[11.5px] font-semibold text-admin-muted mb-2">গ্রাহক</p>
              <p class="font-semibold">{{ selected.customer_name }}</p>
              <p v-if="selected.customer_phone" class="mt-1"><a :href="`tel:${selected.customer_phone}`" class="text-admin-sidebar font-medium hover:underline inline-flex items-center gap-1"><Phone :size="13" /> {{ selected.customer_phone }}</a></p>
              <p class="text-admin-muted text-[12px] mt-0.5 break-all">{{ selected.customer_email }}</p>
            </div>
            <div class="rounded-xl border border-admin-border p-4">
              <p class="text-[11.5px] font-semibold text-admin-muted mb-2">ডেলিভারি ঠিকানা</p>
              <p class="leading-relaxed">
                {{ selected.shipping_address?.address_line_1 }}<template v-if="selected.shipping_address?.address_line_2">, {{ selected.shipping_address.address_line_2 }}</template><br />
                {{ [selected.shipping_address?.city, selected.shipping_address?.state, selected.shipping_address?.postal_code].filter(Boolean).join(', ') }}
              </p>
            </div>
          </div>
          <div v-if="selected.order_notes" class="rounded-xl border border-amber-200 bg-amber-50 p-3 text-[12.5px]">
            <strong>গ্রাহকের নোট:</strong> {{ selected.order_notes }}
          </div>
          <p class="text-[11.5px] text-admin-muted">অর্ডার করা হয়েছে: {{ new Date(selected.created_at).toLocaleString('bn-BD', { dateStyle: 'long', timeStyle: 'short' }) }} · পেমেন্ট: {{ payMethod(selected.payment_method) }}</p>
        </div>

        <!-- Update -->
        <form class="rounded-xl bg-admin-bg border border-admin-border p-4 space-y-4 self-start" @submit.prevent="submitStatus">
          <p class="text-[13px] font-semibold">অবস্থা আপডেট</p>
          <div>
            <label class="adm-label" for="om-status">অর্ডারের অবস্থা</label>
            <select id="om-status" v-model="form.order_status" class="adm-input">
              <option v-for="s in statusOptions" :key="s.key" :value="s.key">{{ s.label }}</option>
            </select>
            <p v-if="form.order_status === 'cancelled' && selected.order_status !== 'cancelled'" class="adm-help text-amber-700">বাতিল করলে পণ্যগুলো আবার স্টকে ফিরে যাবে।</p>
          </div>
          <div>
            <label class="adm-label" for="om-pay">পেমেন্ট</label>
            <select id="om-pay" v-model="form.payment_status" class="adm-input">
              <option value="pending">বাকি (ডেলিভারিতে নেবে)</option>
              <option value="paid">টাকা পাওয়া গেছে</option>
              <option value="unpaid">পরিশোধ হয়নি</option>
              <option value="refunded">ফেরত দেওয়া হয়েছে</option>
            </select>
          </div>
          <div>
            <label class="adm-label" for="om-track">ট্র্যাকিং নম্বর</label>
            <input id="om-track" v-model.trim="form.tracking_number" class="adm-input font-mono text-[12.5px]" placeholder="কুরিয়ারের কনসাইনমেন্ট নম্বর" />
          </div>
          <label v-if="emailFor(form.order_status) && form.order_status !== selected.order_status" class="flex items-start gap-2 text-[12.5px] cursor-pointer">
            <input v-model="form.notify_customer" type="checkbox" class="w-4 h-4 mt-0.5 accent-[#14382A]" />
            <span>গ্রাহককে “{{ emailFor(form.order_status) }}” ইমেইল পাঠান</span>
          </label>
          <button type="submit" class="adm-btn adm-btn-primary w-full" :disabled="updating || !statusDirty">{{ updating ? 'সেভ হচ্ছে…' : 'আপডেট করুন' }}</button>
          <button type="button" class="adm-btn w-full" :disabled="downloading" @click="downloadInvoice(selected)"><FileDown :size="15" /> {{ downloading ? 'তৈরি হচ্ছে…' : 'ইনভয়েস PDF' }}</button>
        </form>
      </div>
    </BaseModal>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue';
import { Search, FileDown, ChevronLeft, ChevronRight, ShoppingBag, Phone } from 'lucide-vue-next';
import api from '../../api';
import BaseModal from '../../components/common/BaseModal.vue';
import { useToastStore } from '../../stores/toast';
import { useAdminDeepLink } from '../../composables/useAdminDeepLink';
import { money, bnNumber } from '../../admin-nav';

const toast = useToastStore();
const orders = ref([]);
const loading = ref(true);
const meta = ref(null);
const counts = ref({});
const searchQuery = ref('');
const statusFilter = ref('');

const statusOptions = [
  { key: 'pending', label: 'নতুন (কনফার্ম বাকি)', chip: 'bg-amber-50 text-amber-700' },
  { key: 'confirmed', label: 'কনফার্মড', chip: 'bg-sky-50 text-sky-700' },
  { key: 'processing', label: 'প্যাক হচ্ছে', chip: 'bg-violet-50 text-violet-700' },
  { key: 'ready_to_ship', label: 'কুরিয়ারে বুক করা', chip: 'bg-indigo-50 text-indigo-700' },
  { key: 'shipped', label: 'শিপড (পথে আছে)', chip: 'bg-blue-50 text-blue-700' },
  { key: 'delivered', label: 'ডেলিভারড', chip: 'bg-emerald-50 text-emerald-700' },
  { key: 'cancelled', label: 'বাতিল', chip: 'bg-rose-50 text-rose-700' },
  { key: 'refunded', label: 'রিফান্ড', chip: 'bg-stone-100 text-stone-600' },
];
const statusTabs = [
  { key: '', label: 'সব' },
  ...statusOptions.filter((s) => ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'].includes(s.key)).map((s) => ({ key: s.key, label: s.key === 'pending' ? 'নতুন' : s.label.replace(/ \(.*\)/, '') })),
];
const statusMeta = (k) => statusOptions.find((s) => s.key === k) || { label: k, chip: 'bg-slate-100 text-slate-600' };
const PAY = {
  paid: { label: 'পরিশোধিত', chip: 'bg-emerald-50 text-emerald-700' },
  pending: { label: 'ডেলিভারিতে', chip: 'bg-slate-100 text-slate-600' },
  unpaid: { label: 'পরিশোধ হয়নি', chip: 'bg-amber-50 text-amber-700' },
  failed: { label: 'ব্যর্থ', chip: 'bg-rose-50 text-rose-700' },
  refunded: { label: 'ফেরত', chip: 'bg-stone-100 text-stone-600' },
};
const payMeta = (k) => PAY[k] || { label: k || '—', chip: 'bg-slate-100 text-slate-600' };
const payMethod = (m) => ({ cod: 'ক্যাশ অন ডেলিভারি', bkash: 'বিকাশ', nagad: 'নগদ' }[m] || m || '—');
const EMAILS = { confirmed: 'অর্ডার কনফার্ম হয়েছে', shipped: 'অর্ডার কুরিয়ারে দেওয়া হয়েছে', delivered: 'অর্ডার ডেলিভারি হয়েছে', cancelled: 'অর্ডার বাতিল' };
const emailFor = (s) => EMAILS[s];
const itemCount = (o) => (o.items || []).reduce((n, i) => n + Number(i.quantity || 0), 0);

const ago = (iso) => {
  const mins = Math.round((Date.now() - new Date(iso).getTime()) / 60000);
  if (mins < 1) return 'এইমাত্র';
  if (mins < 60) return `${bnNumber(mins)} মিনিট আগে`;
  const h = Math.round(mins / 60);
  if (h < 24) return `${bnNumber(h)} ঘণ্টা আগে`;
  const d = Math.round(h / 24);
  if (d < 7) return `${bnNumber(d)} দিন আগে`;
  return new Date(iso).toLocaleDateString('bn-BD', { day: 'numeric', month: 'short', year: 'numeric' });
};

let reqId = 0;
const fetchOrders = async (page = 1) => {
  const id = ++reqId;
  loading.value = true;
  try {
    const res = await api.get('/admin/orders', { params: { page, search: searchQuery.value || undefined, order_status: statusFilter.value || undefined } });
    if (id !== reqId) return;
    orders.value = res.data.data || [];
    meta.value = res.data.meta || null;
    counts.value = res.data.meta?.counts || {};
  } catch {
    if (id === reqId) toast.error('অর্ডার লোড হয়নি');
  } finally {
    if (id === reqId) loading.value = false;
  }
};
let t;
watch(searchQuery, () => { clearTimeout(t); t = setTimeout(() => fetchOrders(1), 300); });
watch(statusFilter, () => fetchOrders(1));

// ------------------------------------------------------------------ detail
const detailOpen = ref(false);
const selected = ref(null);
const updating = ref(false);
const downloading = ref(false);
const form = reactive({ order_status: 'pending', payment_status: 'pending', tracking_number: '', notify_customer: true });
const statusDirty = computed(() => selected.value && (
  form.order_status !== selected.value.order_status
  || form.payment_status !== selected.value.payment_status
  || (form.tracking_number || '') !== (selected.value.tracking_number || '')
));

const openDetail = async (o) => {
  try {
    const res = await api.get(`/admin/orders/${o.id}`);
    selected.value = res.data.data;
    Object.assign(form, { order_status: selected.value.order_status, payment_status: selected.value.payment_status, tracking_number: selected.value.tracking_number || '', notify_customer: true });
    detailOpen.value = true;
  } catch {
    toast.error('অর্ডারের তথ্য লোড হয়নি');
  }
};

const submitStatus = async () => {
  updating.value = true;
  try {
    const res = await api.put(`/admin/orders/${selected.value.id}/status`, { ...form });
    const emailed = form.notify_customer && emailFor(form.order_status) && form.order_status !== selected.value.order_status;
    selected.value = { ...selected.value, ...res.data.data };
    toast.success(`অর্ডার আপডেট হয়েছে${emailed ? ' — গ্রাহককে ইমেইল যাচ্ছে' : ''}`);
    detailOpen.value = false;
    fetchOrders(meta.value?.current_page || 1);
  } catch (e) {
    toast.error(e.response?.data?.message || 'আপডেট হয়নি');
  } finally {
    updating.value = false;
  }
};

const downloadInvoice = async (o) => {
  downloading.value = true;
  try {
    const res = await api.get(`/admin/orders/${o.id}/invoice`, { responseType: 'blob' });
    const url = URL.createObjectURL(new Blob([res.data], { type: 'application/pdf' }));
    const a = Object.assign(document.createElement('a'), { href: url, download: `invoice-${o.order_number}.pdf` });
    document.body.appendChild(a);
    a.click();
    a.remove();
    URL.revokeObjectURL(url);
  } catch {
    toast.error('ইনভয়েস তৈরি করা যায়নি');
  } finally {
    downloading.value = false;
  }
};

onMounted(() => fetchOrders(1));
useAdminDeepLink({ search: searchQuery, reload: () => fetchOrders(1) });
</script>

<style scoped>
.om-chip {
  display: inline-flex; align-items: center; gap: 8px; padding: 7px 12px; border-radius: 999px;
  font-size: 12.5px; font-weight: 600; color: var(--color-admin-muted); background: #fff; border: 1px solid var(--color-admin-border); transition: all .15s;
}
.om-chip:hover { color: var(--color-admin-text); border-color: #C9D2CD; }
.om-chip-count { font-size: 11px; padding: 0 7px; border-radius: 999px; background: var(--color-admin-bg); line-height: 1.7; }
.om-chip-count.is-alert { background: #FEF3C7; color: #B45309; }
.om-chip.is-active { background: var(--color-admin-sidebar); border-color: var(--color-admin-sidebar); color: #fff; }
.om-chip.is-active .om-chip-count { background: rgba(255, 255, 255, .18); color: #fff; }
</style>
