<template>
  <div class="space-y-5">
    <!-- ============================================================ header -->
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3">
      <div>
        <h2 class="text-xl font-bold tracking-tight text-admin-text">পণ্য</h2>
        <p class="text-xs text-admin-muted mt-0.5">সব পণ্য, দাম, সাইজ-রং আর স্টক এক জায়গায়। নাম বা ছবিতে ক্লিক করলে এডিট খুলবে।</p>
      </div>
      <div class="flex flex-wrap items-center gap-2">
        <button type="button" class="adm-btn" :disabled="exporting" @click="exportCsv">
          <Download :size="15" /> {{ exporting ? 'এক্সপোর্ট হচ্ছে…' : 'CSV এক্সপোর্ট' }}
        </button>
        <button type="button" class="adm-btn" @click="importModalOpen = true">
          <Upload :size="15" /> ইমপোর্ট
        </button>
        <button type="button" class="adm-btn adm-btn-primary" @click="openCreate">
          <Plus :size="16" /> নতুন পণ্য
        </button>
      </div>
    </div>

    <!-- ============================================================ filter chips -->
    <div class="flex flex-wrap items-center gap-2">
      <button
        v-for="f in statusFilters"
        :key="f.key"
        type="button"
        @click="setQuickFilter(f)"
        :class="['pm-chip', isQuickActive(f) && 'is-active', f.tone && `tone-${f.tone}`]"
      >
        {{ f.label }}
        <span class="pm-chip-count">{{ bnNumber(counts[f.countKey] ?? 0) }}</span>
      </button>
    </div>

    <div class="adm-card overflow-hidden">
      <!-- ============================================================ toolbar -->
      <div class="flex flex-col lg:flex-row lg:items-center gap-3 p-4 border-b border-admin-border">
        <div class="relative flex-1 min-w-0">
          <Search :size="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-admin-muted pointer-events-none" />
          <input v-model="searchQuery" type="search" class="adm-input pl-9" placeholder="নাম, SKU বা লিংক দিয়ে খুঁজুন…" />
        </div>
        <select v-model="categoryFilter" class="adm-input lg:w-56" aria-label="ক্যাটাগরি">
          <option value="">সব ক্যাটাগরি</option>
          <option v-for="c in categoryOptions" :key="c.id" :value="c.id">{{ c.label }}</option>
        </select>
        <select v-model="sortKey" class="adm-input lg:w-48" aria-label="সাজানো">
          <option v-for="s in sortOptions" :key="s.key" :value="s.key">{{ s.label }}</option>
        </select>
        <button v-if="hasFilters" type="button" class="adm-btn adm-btn-ghost text-admin-muted" @click="clearFilters">
          <X :size="14" /> ফিল্টার মুছুন
        </button>
      </div>

      <!-- ============================================================ bulk bar -->
      <div v-if="selected.length" class="flex flex-wrap items-center gap-2 px-4 py-2.5 bg-[#EEF6F1] border-b border-admin-border text-xs">
        <span class="font-semibold text-admin-sidebar mr-2">{{ bnNumber(selected.length) }}টি বাছাই করা</span>
        <button type="button" class="adm-btn" @click="bulk('published')"><Eye :size="14" /> প্রকাশ করুন</button>
        <button type="button" class="adm-btn" @click="bulk('draft')"><EyeOff :size="14" /> ড্রাফট করুন</button>
        <button type="button" class="adm-btn" @click="bulk('archived')"><Archive :size="14" /> আর্কাইভ</button>
        <button type="button" class="adm-btn adm-btn-danger" @click="bulk('delete')"><Trash2 :size="14" /> মুছুন</button>
        <button type="button" class="adm-btn adm-btn-ghost ml-auto" @click="selected = []">বাছাই বাতিল</button>
      </div>

      <!-- ============================================================ table -->
      <div class="overflow-x-auto">
        <table class="w-full text-left text-[13px] min-w-[860px]">
          <thead class="bg-admin-bg text-[11.5px] text-admin-muted font-semibold">
            <tr>
              <th class="w-10 pl-4 py-3">
                <input type="checkbox" class="w-4 h-4 accent-[#14382A]" :checked="allOnPageSelected" :indeterminate.prop="someOnPageSelected" @change="toggleAll" aria-label="সব বাছাই" />
              </th>
              <th class="py-3 px-3">পণ্য</th>
              <th class="py-3 px-3">ক্যাটাগরি</th>
              <th class="py-3 px-3">দাম</th>
              <th class="py-3 px-3">স্টক</th>
              <th class="py-3 px-3">অবস্থা</th>
              <th class="py-3 px-3 text-right pr-4">কাজ</th>
            </tr>
          </thead>
          <tbody v-if="loading">
            <tr v-for="n in 6" :key="n" class="border-t border-admin-border">
              <td colspan="7" class="px-4 py-3"><div class="h-12 rounded-lg bg-admin-bg animate-pulse"></div></td>
            </tr>
          </tbody>
          <tbody v-else-if="products.length">
            <tr
              v-for="p in products"
              :key="p.id"
              :class="['border-t border-admin-border transition-colors', selected.includes(p.id) ? 'bg-[#F3FAF6]' : 'hover:bg-admin-bg/60']"
            >
              <td class="pl-4 py-3 align-middle">
                <input type="checkbox" class="w-4 h-4 accent-[#14382A]" :value="p.id" v-model="selected" :aria-label="p.name + ' বাছাই'" />
              </td>
              <td class="py-3 px-3">
                <button type="button" class="flex items-center gap-3 text-left group" @click="openEdit(p)">
                  <span class="w-11 h-14 rounded-md bg-admin-bg overflow-hidden shrink-0 border border-admin-border">
                    <img v-if="p.featured_image" :src="p.featured_image" alt="" class="w-full h-full object-cover" loading="lazy" />
                  </span>
                  <span class="min-w-0">
                    <span class="block font-semibold text-admin-text group-hover:text-admin-accent truncate max-w-[280px]">{{ p.name }}</span>
                    <span class="block text-[11.5px] text-admin-muted font-mono mt-0.5">{{ p.sku }}<template v-if="p.variants?.length"> · <span class="font-sans">{{ bnNumber(p.variants.length) }}টি সাইজ/রং</span></template></span>
                    <span v-if="p.is_new || p.is_bestseller || p.is_featured" class="flex gap-1 mt-1">
                      <span v-if="p.is_new" class="adm-chip bg-sky-50 text-sky-700">নতুন</span>
                      <span v-if="p.is_bestseller" class="adm-chip bg-amber-50 text-amber-700">বেস্টসেলার</span>
                      <span v-if="p.is_featured" class="adm-chip bg-violet-50 text-violet-700">হোমপেজে</span>
                    </span>
                  </span>
                </button>
              </td>
              <td class="py-3 px-3">
                <button v-if="p.category" type="button" class="text-admin-text hover:text-admin-accent hover:underline" @click="categoryFilter = p.category_id" :title="p.category.name + ' দিয়ে ফিল্টার'">
                  {{ p.category.name }}
                </button>
                <span v-else class="text-admin-muted">—</span>
              </td>
              <td class="py-3 px-3 whitespace-nowrap">
                <template v-if="isOnSale(p)">
                  <span class="font-semibold text-admin-text">{{ money(p.sale_price) }}</span>
                  <span class="block text-[11.5px] text-admin-muted"><s>{{ money(p.price) }}</s> · <span class="text-rose-600 font-semibold">{{ bnNumber(discountPct(p.price, p.sale_price)) }}% ছাড়</span></span>
                </template>
                <span v-else class="font-semibold text-admin-text">{{ money(p.price) }}</span>
              </td>
              <td class="py-3 px-3 whitespace-nowrap">
                <span :class="['adm-chip', stockTone(p.stock)]">
                  <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                  {{ p.stock <= 0 ? 'স্টক শেষ' : bnNumber(p.stock) + 'টি' }}
                </span>
              </td>
              <td class="py-3 px-3">
                <span :class="['adm-chip', statusMeta[p.status]?.chip || 'bg-slate-100 text-slate-600']">{{ statusMeta[p.status]?.label || p.status }}</span>
              </td>
              <td class="py-3 px-3 pr-4">
                <div class="flex items-center justify-end gap-0.5">
                  <button type="button" class="pm-icon" title="এডিট" @click="openEdit(p)"><Pencil :size="16" /></button>
                  <button type="button" class="pm-icon" title="কপি করে নতুন পণ্য" @click="duplicate(p)"><Copy :size="16" /></button>
                  <a :href="`/product/${p.slug}`" target="_blank" rel="noopener" class="pm-icon" title="সাইটে দেখুন"><ExternalLink :size="16" /></a>
                  <button type="button" class="pm-icon hover:!text-rose-600 hover:!bg-rose-50" title="মুছুন" @click="remove(p)"><Trash2 :size="16" /></button>
                </div>
              </td>
            </tr>
          </tbody>
          <tbody v-else>
            <tr>
              <td colspan="7" class="px-4 py-16 text-center">
                <PackageSearch :size="36" class="mx-auto text-admin-muted/60" :stroke-width="1.5" />
                <p class="mt-3 text-sm font-semibold text-admin-text">{{ hasFilters ? 'এই ফিল্টারে কোনো পণ্য নেই' : 'এখনো কোনো পণ্য নেই' }}</p>
                <p class="text-xs text-admin-muted mt-1">
                  <button v-if="hasFilters" type="button" class="text-admin-accent font-semibold hover:underline" @click="clearFilters">ফিল্টার মুছুন</button>
                  <button v-else type="button" class="text-admin-accent font-semibold hover:underline" @click="openCreate">প্রথম পণ্য যোগ করুন</button>
                </p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- ============================================================ pagination -->
      <div v-if="meta && meta.total > 0" class="flex flex-col sm:flex-row items-center justify-between gap-3 px-4 py-3 border-t border-admin-border text-xs text-admin-muted">
        <span>মোট {{ bnNumber(meta.total) }}টি পণ্যের মধ্যে {{ bnNumber(rangeFrom) }}–{{ bnNumber(rangeTo) }}</span>
        <div v-if="meta.last_page > 1" class="flex items-center gap-1">
          <button type="button" class="adm-btn px-2.5" :disabled="meta.current_page <= 1" @click="fetchProducts(meta.current_page - 1)" aria-label="আগের পাতা"><ChevronLeft :size="15" /></button>
          <button
            v-for="n in pageNumbers"
            :key="n"
            type="button"
            :class="['adm-btn px-3', n === meta.current_page && 'adm-btn-primary']"
            :disabled="n === '…'"
            @click="n !== '…' && fetchProducts(n)"
          >{{ n === '…' ? '…' : bnNumber(n) }}</button>
          <button type="button" class="adm-btn px-2.5" :disabled="meta.current_page >= meta.last_page" @click="fetchProducts(meta.current_page + 1)" aria-label="পরের পাতা"><ChevronRight :size="15" /></button>
        </div>
      </div>
    </div>

    <!-- ============================================================ editor -->
    <ProductEditor
      v-if="editorOpen"
      :product="editing"
      :categories="categories"
      :brands="brands"
      @close="editorOpen = false"
      @saved="onSaved"
      @duplicate="duplicate"
      @delete="remove"
    />

    <!-- ============================================================ import -->
    <BaseModal :is-open="importModalOpen" title="পণ্য ইমপোর্ট (CSV / Excel)" max-width="lg" @close="importModalOpen = false">
      <div class="space-y-4 text-sm">
        <p class="text-admin-muted text-xs leading-relaxed">
          আগে “CSV এক্সপোর্ট” করে ফাইলটা দেখে নিন, একই কলামে লিখে আপলোড করুন। একই SKU বা লিংকের পণ্য থাকলে সেটা আপডেট হবে, না থাকলে নতুন তৈরি হবে।
          <span class="font-mono text-[11px] block mt-2 text-admin-text">ID, Name, SKU, Slug, Category, Price, Sale Price, Stock, Status, Is Featured</span>
        </p>
        <label class="block border-2 border-dashed border-admin-border rounded-xl p-6 text-center cursor-pointer hover:border-admin-accent transition-colors">
          <Upload :size="22" class="mx-auto text-admin-muted" />
          <span class="block mt-2 text-xs font-semibold text-admin-text">{{ selectedImportFile ? selectedImportFile.name : 'ফাইল বাছাই করুন' }}</span>
          <span class="block text-[11px] text-admin-muted mt-0.5">.csv, .xlsx — সর্বোচ্চ ১০ MB</span>
          <input ref="importInputRef" type="file" class="sr-only" accept=".csv,.xlsx,.xls" @change="selectedImportFile = $event.target.files?.[0] || null" />
        </label>
        <div class="flex justify-end gap-2 pt-2">
          <button type="button" class="adm-btn" @click="importModalOpen = false">বাতিল</button>
          <button type="button" class="adm-btn adm-btn-primary" :disabled="!selectedImportFile || importing" @click="submitImport">
            {{ importing ? 'ইমপোর্ট হচ্ছে…' : 'আপলোড করুন' }}
          </button>
        </div>
      </div>
    </BaseModal>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute } from 'vue-router';
import {
  Plus, Download, Upload, Search, X, Eye, EyeOff, Archive, Trash2, Pencil, Copy, ExternalLink,
  ChevronLeft, ChevronRight, PackageSearch,
} from 'lucide-vue-next';
import api from '../../api';
import BaseModal from '../../components/common/BaseModal.vue';
import ProductEditor from '../../components/admin/ProductEditor.vue';
import { useToastStore } from '../../stores/toast';
import { useAdminDeepLink } from '../../composables/useAdminDeepLink';
import { money, bnNumber } from '../../admin-nav';

const toast = useToastStore();

const products = ref([]);
const categories = ref([]);
const brands = ref([]);
const meta = ref(null);
const counts = ref({});
const loading = ref(true);
const selected = ref([]);

const searchQuery = ref('');
// Categories page links here with ?category_id=…
const categoryFilter = ref(useRoute().query.category_id ? Number(useRoute().query.category_id) : '');
const statusFilter = ref('');
const stockFilter = ref('');
const sortKey = ref('newest');

const statusMeta = {
  published: { label: 'প্রকাশিত', chip: 'bg-emerald-50 text-emerald-700' },
  draft: { label: 'ড্রাফট', chip: 'bg-slate-100 text-slate-600' },
  archived: { label: 'আর্কাইভ', chip: 'bg-stone-100 text-stone-500' },
};

const statusFilters = [
  { key: 'all', label: 'সব', countKey: 'all' },
  { key: 'published', label: 'প্রকাশিত', countKey: 'published', status: 'published' },
  { key: 'draft', label: 'ড্রাফট', countKey: 'draft', status: 'draft' },
  { key: 'archived', label: 'আর্কাইভ', countKey: 'archived', status: 'archived' },
  { key: 'low', label: 'স্টক কম', countKey: 'low_stock', stock: 'low_stock', tone: 'warn' },
  { key: 'out', label: 'স্টক শেষ', countKey: 'out_of_stock', stock: 'out_of_stock', tone: 'danger' },
];

const sortOptions = [
  { key: 'newest', label: 'নতুন আগে', by: 'created_at', dir: 'desc' },
  { key: 'oldest', label: 'পুরনো আগে', by: 'created_at', dir: 'asc' },
  { key: 'updated', label: 'সম্প্রতি এডিট করা', by: 'updated_at', dir: 'desc' },
  { key: 'name', label: 'নাম (A–Z)', by: 'name', dir: 'asc' },
  { key: 'price_low', label: 'দাম: কম থেকে বেশি', by: 'price', dir: 'asc' },
  { key: 'price_high', label: 'দাম: বেশি থেকে কম', by: 'price', dir: 'desc' },
  { key: 'stock_low', label: 'স্টক: কম আগে', by: 'stock', dir: 'asc' },
];

const isQuickActive = (f) => {
  if (f.key === 'all') return !statusFilter.value && !stockFilter.value;
  if (f.status) return statusFilter.value === f.status;
  return stockFilter.value === f.stock;
};
const setQuickFilter = (f) => {
  statusFilter.value = f.status || '';
  stockFilter.value = f.stock || '';
};

const hasFilters = computed(() => Boolean(searchQuery.value || categoryFilter.value || statusFilter.value || stockFilter.value));
const clearFilters = () => {
  searchQuery.value = '';
  categoryFilter.value = '';
  statusFilter.value = '';
  stockFilter.value = '';
};

/** Parent categories first, their children indented under them. */
const categoryOptions = computed(() => {
  const list = categories.value;
  const roots = list.filter((c) => !c.parent_id);
  const out = [];
  roots.forEach((r) => {
    out.push({ id: r.id, label: r.name });
    list.filter((c) => c.parent_id === r.id).forEach((c) => out.push({ id: c.id, label: '— ' + c.name }));
  });
  list.filter((c) => c.parent_id && !list.some((r) => r.id === c.parent_id)).forEach((c) => out.push({ id: c.id, label: c.name }));
  return out;
});

const isOnSale = (p) => p.sale_price && Number(p.sale_price) > 0 && Number(p.sale_price) < Number(p.price);
const discountPct = (price, sale) => Math.round((1 - Number(sale) / Number(price)) * 100);
const stockTone = (s) => (s <= 0 ? 'bg-rose-50 text-rose-700' : s <= 5 ? 'bg-amber-50 text-amber-700' : 'bg-emerald-50 text-emerald-700');

// ------------------------------------------------------------------ loading
let reqId = 0;
const fetchProducts = async (page = 1) => {
  const id = ++reqId;
  loading.value = true;
  const sort = sortOptions.find((s) => s.key === sortKey.value) || sortOptions[0];
  try {
    const res = await api.get('/admin/products', {
      params: {
        page,
        per_page: 15,
        search: searchQuery.value || undefined,
        category_id: categoryFilter.value || undefined,
        status: statusFilter.value || undefined,
        stock_status: stockFilter.value || undefined,
        sort_by: sort.by,
        sort_direction: sort.dir,
      },
    });
    if (id !== reqId) return;
    products.value = res.data.data || [];
    meta.value = res.data.meta || null;
    counts.value = res.data.meta?.counts || {};
    selected.value = selected.value.filter((sid) => products.value.some((p) => p.id === sid));
  } catch (err) {
    if (id === reqId) toast.error('পণ্যের তালিকা লোড হয়নি');
  } finally {
    if (id === reqId) loading.value = false;
  }
};

let searchTimer;
watch(searchQuery, () => {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(() => fetchProducts(1), 300);
});
watch([categoryFilter, statusFilter, stockFilter, sortKey], () => fetchProducts(1));

const rangeFrom = computed(() => (meta.value ? (meta.value.current_page - 1) * meta.value.per_page + 1 : 0));
const rangeTo = computed(() => (meta.value ? Math.min(meta.value.total, meta.value.current_page * meta.value.per_page) : 0));
const pageNumbers = computed(() => {
  if (!meta.value) return [];
  const { current_page: c, last_page: l } = meta.value;
  const set = new Set([1, l, c - 1, c, c + 1].filter((n) => n >= 1 && n <= l));
  const sorted = [...set].sort((a, b) => a - b);
  const out = [];
  sorted.forEach((n, i) => {
    if (i && n - sorted[i - 1] > 1) out.push('…');
    out.push(n);
  });
  return out;
});

// ------------------------------------------------------------------ selection + bulk
const allOnPageSelected = computed(() => products.value.length > 0 && products.value.every((p) => selected.value.includes(p.id)));
const someOnPageSelected = computed(() => !allOnPageSelected.value && products.value.some((p) => selected.value.includes(p.id)));
const toggleAll = () => {
  selected.value = allOnPageSelected.value ? [] : products.value.map((p) => p.id);
};

const bulk = async (action) => {
  const n = bnNumber(selected.value.length);
  if (action === 'delete' && !confirm(`${n}টি পণ্য মুছে ফেলবেন? এটা ফেরানো যাবে না।`)) return;
  try {
    const res = await api.post('/admin/products/bulk', { ids: selected.value, action });
    toast.success(res.data.message);
    selected.value = [];
    fetchProducts(meta.value?.current_page || 1);
  } catch (err) {
    toast.error(err.response?.data?.message || 'কাজটি করা যায়নি');
  }
};

// ------------------------------------------------------------------ row actions
const editorOpen = ref(false);
const editing = ref(null);

const openCreate = () => {
  editing.value = null;
  editorOpen.value = true;
};
const openEdit = (p) => {
  editing.value = p;
  editorOpen.value = true;
};
const onSaved = (product, { close }) => {
  if (close) editorOpen.value = false;
  else editing.value = product;
  fetchProducts(meta.value?.current_page || 1);
};

const duplicate = async (p) => {
  try {
    const res = await api.post(`/admin/products/${p.id}/duplicate`);
    toast.success(res.data.message);
    await fetchProducts(1);
    openEdit(res.data.data);
  } catch (err) {
    toast.error(err.response?.data?.message || 'কপি করা যায়নি');
  }
};

const remove = async (p) => {
  if (!confirm(`“${p.name}” মুছে ফেলবেন?`)) return;
  try {
    await api.delete(`/admin/products/${p.id}`);
    toast.success('পণ্য মুছে ফেলা হয়েছে');
    editorOpen.value = false;
    fetchProducts(meta.value?.current_page || 1);
  } catch (err) {
    toast.error('মুছে ফেলা যায়নি');
  }
};

// ------------------------------------------------------------------ import / export
const exporting = ref(false);
const importModalOpen = ref(false);
const selectedImportFile = ref(null);
const importing = ref(false);
const importInputRef = ref(null);

const exportCsv = async () => {
  exporting.value = true;
  try {
    const res = await api.get('/admin/products/export', { responseType: 'blob' });
    const url = URL.createObjectURL(new Blob([res.data], { type: 'text/csv' }));
    const a = Object.assign(document.createElement('a'), { href: url, download: `products-${new Date().toISOString().slice(0, 10)}.csv` });
    document.body.appendChild(a);
    a.click();
    a.remove();
    URL.revokeObjectURL(url);
    toast.success('CSV ফাইল ডাউনলোড হয়েছে');
  } catch {
    toast.error('এক্সপোর্ট করা যায়নি');
  } finally {
    exporting.value = false;
  }
};

const submitImport = async () => {
  if (!selectedImportFile.value) return;
  importing.value = true;
  try {
    const fd = new FormData();
    fd.append('file', selectedImportFile.value);
    const res = await api.post('/admin/products/import', fd, { headers: { 'Content-Type': 'multipart/form-data' } });
    const d = res.data.data || {};
    toast.success(`ইমপোর্ট হয়েছে: নতুন ${bnNumber(d.imported ?? 0)}টি, আপডেট ${bnNumber(d.updated ?? 0)}টি`);
    importModalOpen.value = false;
    selectedImportFile.value = null;
    if (importInputRef.value) importInputRef.value.value = '';
    fetchProducts(1);
  } catch (err) {
    toast.error(err.response?.data?.message || 'ইমপোর্ট করা যায়নি');
  } finally {
    importing.value = false;
  }
};

onMounted(async () => {
  fetchProducts(1);
  try {
    const [catRes, brandRes] = await Promise.all([
      api.get('/admin/resources/categories?per_page=-1'),
      api.get('/admin/resources/brands?per_page=-1'),
    ]);
    const rawCats = catRes.data?.data?.data || catRes.data?.data || [];
    categories.value = Array.isArray(rawCats) ? rawCats : [];
    const rawBrands = brandRes.data?.data?.data || brandRes.data?.data || [];
    brands.value = Array.isArray(rawBrands) ? rawBrands : [];
  } catch {
    /* the editor still works without the lists */
  }
});

// ?search=… / ?new=1 from the sidebar, Ctrl+K and the dashboard
useAdminDeepLink({ search: searchQuery, onNew: openCreate });
</script>

<style scoped>
.pm-chip {
  display: inline-flex; align-items: center; gap: 8px; padding: 7px 12px; border-radius: 999px;
  font-size: 12.5px; font-weight: 600; color: var(--color-admin-muted); background: #fff; border: 1px solid var(--color-admin-border);
  transition: all .15s;
}
.pm-chip:hover { color: var(--color-admin-text); border-color: #C9D2CD; }
.pm-chip-count { font-size: 11px; padding: 0 7px; border-radius: 999px; background: var(--color-admin-bg); color: var(--color-admin-muted); line-height: 1.7; }
.pm-chip.is-active { background: var(--color-admin-sidebar); border-color: var(--color-admin-sidebar); color: #fff; }
.pm-chip.is-active .pm-chip-count { background: rgba(255, 255, 255, .18); color: #fff; }
.pm-chip.tone-warn:not(.is-active) .pm-chip-count { background: #FEF3C7; color: #B45309; }
.pm-chip.tone-danger:not(.is-active) .pm-chip-count { background: #FFE4E6; color: #BE123C; }
.pm-icon {
  display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 8px;
  color: var(--color-admin-muted); transition: background .15s, color .15s;
}
.pm-icon:hover { background: var(--color-admin-bg); color: var(--color-admin-text); }
</style>
