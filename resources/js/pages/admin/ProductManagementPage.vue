<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <span class="text-xs uppercase tracking-[0.25em] text-[#A89B8A] font-medium block">Catalog Management</span>
        <h1 class="font-serif text-3xl text-[#111111]">Garments & Silhouettes</h1>
      </div>

      <div class="flex flex-wrap items-center gap-2.5">
        <button
          type="button"
          :disabled="exporting"
          @click="exportCsv"
          class="px-4 py-2 border border-[#D8CCBC] hover:border-[#111111] bg-white text-[#111111] text-xs uppercase tracking-wider font-semibold transition-colors shadow-2xs inline-flex items-center gap-1.5"
        >
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
          </svg>
          <span>{{ exporting ? 'Exporting...' : 'Export CSV' }}</span>
        </button>

        <button
          type="button"
          @click="importModalOpen = true"
          class="px-4 py-2 border border-[#D8CCBC] hover:border-[#111111] bg-white text-[#111111] text-xs uppercase tracking-wider font-semibold transition-colors shadow-2xs inline-flex items-center gap-1.5"
        >
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
          </svg>
          <span>Import Products</span>
        </button>

        <button
          type="button"
          @click="openCreateModal"
          class="px-5 py-2 bg-[#111111] hover:bg-black text-white text-xs uppercase tracking-wider font-semibold transition-colors shadow-xs inline-flex items-center gap-2"
        >
          <span>+ Add New Product</span>
        </button>
      </div>
    </div>

    <!-- Data Table -->
    <DataTable
      :columns="columns"
      :items="products"
      :loading="loading"
      :meta="meta"
      v-model:search="searchQuery"
      search-placeholder="Search by product name, SKU, or slug..."
      :sort-by="sortBy"
      :sort-direction="sortDirection"
      @sort="handleSort"
      @page="fetchProducts"
      @edit="openEditModal"
      @delete="confirmDelete"
    >
      <template #col-price="{ item }">
        <span class="font-medium">${{ Number(item.sale_price || item.price).toFixed(2) }}</span>
        <span v-if="item.sale_price" class="text-[10px] text-[#A89B8A] line-through ml-1.5">
          ${{ Number(item.price).toFixed(2) }}
        </span>
      </template>

      <template #col-stock="{ item }">
        <span :class="[
          'px-2 py-0.5 text-[10px] font-semibold tracking-wider uppercase',
          item.stock <= 5 ? 'bg-rose-100 text-rose-800' : 'bg-emerald-100 text-emerald-800'
        ]">
          {{ item.stock }} in stock
        </span>
      </template>
    </DataTable>

    <!-- Create / Edit Product Modal -->
    <BaseModal
      :is-open="formModalOpen"
      :title="isEdit ? 'Edit Product' : 'Add New Product'"
      max-width="4xl"
      @close="formModalOpen = false"
    >
      <form @submit.prevent="saveProduct" class="space-y-6 text-xs">
        <div class="grid grid-cols-12 gap-4">
          <!-- Name & SKU -->
          <div class="col-span-12 sm:col-span-8">
            <label class="block font-semibold uppercase tracking-wider mb-1">Product Title *</label>
            <input type="text" v-model="form.name" required class="w-full px-3 py-2 border border-[#D8CCBC]" />
          </div>
          <div class="col-span-12 sm:col-span-4">
            <label class="block font-semibold uppercase tracking-wider mb-1">SKU Code</label>
            <input type="text" v-model="form.sku" placeholder="Auto-generated if empty" class="w-full px-3 py-2 border border-[#D8CCBC]" />
          </div>

          <!-- Category & Brand -->
          <div class="col-span-12 sm:col-span-6">
            <label class="block font-semibold uppercase tracking-wider mb-1">Category / Department *</label>
            <select v-model="form.category_id" required class="w-full px-3 py-2 border border-[#D8CCBC]">
              <option :value="null">-- Select Category --</option>
              <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                {{ cat.name }} {{ cat.parent ? `(${cat.parent.name})` : '' }}
              </option>
            </select>
          </div>
          <div class="col-span-12 sm:col-span-6">
            <label class="block font-semibold uppercase tracking-wider mb-1">Brand House</label>
            <select v-model="form.brand_id" class="w-full px-3 py-2 border border-[#D8CCBC]">
              <option :value="null">-- Select Brand --</option>
              <option v-for="b in brands" :key="b.id" :value="b.id">{{ b.name }}</option>
            </select>
          </div>

          <!-- Pricing -->
          <div class="col-span-12 sm:col-span-4">
            <label class="block font-semibold uppercase tracking-wider mb-1">Regular Price ($) *</label>
            <input type="number" step="0.01" v-model.number="form.price" required class="w-full px-3 py-2 border border-[#D8CCBC]" />
          </div>
          <div class="col-span-12 sm:col-span-4">
            <label class="block font-semibold uppercase tracking-wider mb-1">Sale Price ($)</label>
            <input type="number" step="0.01" v-model.number="form.sale_price" class="w-full px-3 py-2 border border-[#D8CCBC]" />
          </div>
          <div class="col-span-12 sm:col-span-4">
            <label class="block font-semibold uppercase tracking-wider mb-1">Total Stock *</label>
            <input type="number" v-model.number="form.stock" required class="w-full px-3 py-2 border border-[#D8CCBC]" />
          </div>

          <!-- Featured Image -->
          <div class="col-span-12">
            <label class="block font-semibold uppercase tracking-wider mb-1">Primary Featured Image URL</label>
            <MediaPicker v-model="form.featured_image" />
          </div>

          <!-- Product Gallery Images -->
          <div class="col-span-12">
            <div class="flex items-center justify-between mb-2">
              <label class="block font-semibold uppercase tracking-wider">Additional Product Photos / Gallery</label>
              <button
                type="button"
                @click="addGalleryImage"
                class="px-2.5 py-1 bg-[#F7F5F0] hover:bg-[#EAE5DC] text-[11px] uppercase tracking-wider font-semibold text-[#111111] border border-[#D8CCBC] transition"
              >
                + Add Another Photo
              </button>
            </div>
            <div v-if="form.gallery && form.gallery.length" class="space-y-3">
              <div v-for="(img, idx) in form.gallery" :key="idx" class="flex items-center gap-2 p-2 bg-[#F7F5F0]/60 border border-[#D8CCBC]/50">
                <div class="flex-1">
                  <MediaPicker v-model="form.gallery[idx]" />
                </div>
                <button
                  type="button"
                  @click="removeGalleryImage(idx)"
                  class="px-2 py-1 text-rose-600 hover:text-rose-800 text-xs font-bold shrink-0 hover:bg-rose-50"
                  title="Remove image"
                >
                  ✕
                </button>
              </div>
            </div>
            <p v-else class="text-[11px] text-[#A89B8A] italic">
              No extra gallery photos added. Click "+ Add Another Photo" to add more views or color angles.
            </p>
          </div>

          <!-- Short Description -->
          <div class="col-span-12">
            <label class="block font-semibold uppercase tracking-wider mb-1">Short Editorial Summary</label>
            <input type="text" v-model="form.short_description" class="w-full px-3 py-2 border border-[#D8CCBC]" />
          </div>

          <!-- Full Description -->
          <div class="col-span-12">
            <label class="block font-semibold uppercase tracking-wider mb-1">Full Description & Craftsmanship</label>
            <textarea rows="4" v-model="form.description" class="w-full px-3 py-2 border border-[#D8CCBC]"></textarea>
          </div>

          <!-- Fashion Variant Matrix (Sizes & Colors) -->
          <div class="col-span-12 border-t border-[#D8CCBC]/40 pt-4 space-y-3">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
              <div>
                <label class="block font-semibold uppercase tracking-wider text-xs">Product Variants Matrix</label>
                <p class="text-[11px] text-[#A89B8A]">Configure individual sizes, colorways, SKUs, and inventory allocations</p>
              </div>
              <div class="flex items-center gap-2">
                <button
                  type="button"
                  @click="generateStandardSizes"
                  class="px-2.5 py-1 bg-[#F7F8FA] border border-[#D8CCBC] hover:border-[#111111] text-[10px] font-semibold uppercase tracking-wider text-[#14382A]"
                >
                  + Standard Sizes (XS-XL)
                </button>
                <button
                  type="button"
                  @click="addVariantRow"
                  class="px-2.5 py-1 bg-[#14382A] text-white text-[10px] font-semibold uppercase tracking-wider hover:bg-[#16A36A]"
                >
                  + Add Single Variant
                </button>
              </div>
            </div>

            <!-- Variant Rows Table -->
            <div v-if="form.variants && form.variants.length" class="overflow-x-auto border border-[#D8CCBC]/50">
              <table class="w-full text-left text-xs">
                <thead class="bg-[#F7F8FA] text-[10px] uppercase font-semibold text-[#69736E] border-b border-[#D8CCBC]/50">
                  <tr>
                    <th class="p-2">Size</th>
                    <th class="p-2">Color Name</th>
                    <th class="p-2">Color Hex</th>
                    <th class="p-2">SKU Code</th>
                    <th class="p-2">Price ($)</th>
                    <th class="p-2">Stock</th>
                    <th class="p-2 text-center">Action</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-[#D8CCBC]/30">
                  <tr v-for="(v, vIdx) in form.variants" :key="vIdx" class="hover:bg-[#F7F8FA]/50">
                    <td class="p-2">
                      <input type="text" v-model="v.size" placeholder="e.g. M" class="w-16 px-2 py-1 border border-[#D8CCBC]" />
                    </td>
                    <td class="p-2">
                      <input type="text" v-model="v.color" placeholder="e.g. Noir" class="w-24 px-2 py-1 border border-[#D8CCBC]" />
                    </td>
                    <td class="p-2">
                      <div class="flex items-center gap-1.5">
                        <input type="color" v-model="v.color_code" class="w-6 h-6 border-0 cursor-pointer p-0 bg-transparent" />
                        <input type="text" v-model="v.color_code" placeholder="#000000" class="w-20 px-1.5 py-1 border border-[#D8CCBC] text-[11px] font-mono" />
                      </div>
                    </td>
                    <td class="p-2">
                      <input type="text" v-model="v.sku" placeholder="Auto" class="w-28 px-2 py-1 border border-[#D8CCBC] font-mono text-[11px]" />
                    </td>
                    <td class="p-2">
                      <input type="number" step="0.01" v-model.number="v.price" class="w-20 px-2 py-1 border border-[#D8CCBC]" />
                    </td>
                    <td class="p-2">
                      <input type="number" v-model.number="v.stock" class="w-16 px-2 py-1 border border-[#D8CCBC]" />
                    </td>
                    <td class="p-2 text-center">
                      <button
                        type="button"
                        @click="removeVariantRow(vIdx)"
                        class="text-rose-600 hover:text-rose-800 text-xs font-bold px-2 py-1 hover:bg-rose-50"
                        title="Remove variant"
                      >
                        ✕
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div v-else class="p-4 bg-[#F7F8FA] border border-dashed border-[#D8CCBC] text-center text-[11px] text-[#69736E]">
              No custom variants configured. The main price and stock above will apply to this piece.
            </div>
          </div>

          <!-- Toggles -->
          <div class="col-span-12 flex flex-wrap gap-6 pt-2 border-t border-[#D8CCBC]/40">
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" v-model="form.is_featured" class="rounded border-[#D8CCBC]" />
              <span>Featured on Homepage</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" v-model="form.is_bestseller" class="rounded border-[#D8CCBC]" />
              <span>Mark as Bestseller</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" v-model="form.is_new" class="rounded border-[#D8CCBC]" />
              <span>Mark as New Release</span>
            </label>
          </div>
        </div>

        <div class="flex justify-end gap-3 pt-6 border-t border-[#D8CCBC]/40">
          <button
            type="button"
            @click="formModalOpen = false"
            class="px-5 py-2.5 border border-[#D8CCBC] text-xs uppercase tracking-wider"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="saving"
            class="px-7 py-2.5 bg-[#111111] text-white text-xs uppercase tracking-wider font-semibold hover:bg-black"
          >
            {{ saving ? 'Saving...' : 'Save Product' }}
          </button>
        </div>
      </form>
    </BaseModal>

    <!-- Bulk Product Import Modal -->
    <BaseModal
      :is-open="importModalOpen"
      title="Bulk Product Import (CSV / Excel)"
      max-width="lg"
      @close="importModalOpen = false"
    >
      <div class="space-y-4 text-xs">
        <p class="text-[#69736E]">
          Upload a CSV or Excel (.xlsx) file containing your product catalog. Supported columns:
          <span class="font-mono text-[11px] block mt-1 text-[#171C1A]">ID, Name, SKU, Slug, Category, Price, Sale Price, Stock, Status, Is Featured</span>
        </p>

        <div class="border-2 border-dashed border-[#D8CCBC] p-6 text-center bg-[#F7F8FA]">
          <input
            type="file"
            ref="importInputRef"
            accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel"
            @change="handleImportFileChange"
            class="block w-full text-xs text-[#69736E] file:mr-4 file:py-2 file:px-4 file:border-0 file:text-xs file:font-semibold file:bg-[#14382A] file:text-white hover:file:bg-[#16A36A] cursor-pointer"
          />
          <p v-if="selectedImportFile" class="mt-2 text-emerald-700 font-semibold">
            Selected: {{ selectedImportFile.name }} ({{ (selectedImportFile.size / 1024).toFixed(1) }} KB)
          </p>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-[#D8CCBC]/40">
          <button
            type="button"
            @click="importModalOpen = false"
            class="px-4 py-2 border border-[#D8CCBC] text-xs uppercase tracking-wider"
          >
            Cancel
          </button>
          <button
            type="button"
            :disabled="!selectedImportFile || importing"
            @click="submitImport"
            class="px-5 py-2 bg-[#14382A] text-white text-xs uppercase tracking-wider font-semibold hover:bg-[#16A36A] disabled:opacity-50"
          >
            {{ importing ? 'Importing...' : 'Upload & Process Import' }}
          </button>
        </div>
      </div>
    </BaseModal>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue';
import api from '../../api';
import DataTable from '../../components/admin/DataTable.vue';
import BaseModal from '../../components/common/BaseModal.vue';
import MediaPicker from '../../components/admin/MediaPicker.vue';
import { useToastStore } from '../../stores/toast';

const toast = useToastStore();
const products = ref([]);
const categories = ref([]);
const brands = ref([]);
const loading = ref(true);
const saving = ref(false);
const meta = ref(null);
const searchQuery = ref('');
const sortBy = ref('created_at');
const sortDirection = ref('desc');

const formModalOpen = ref(false);
const isEdit = ref(false);
const currentId = ref(null);

// Bulk Import & Export state
const exporting = ref(false);
const importModalOpen = ref(false);
const selectedImportFile = ref(null);
const importing = ref(false);
const importInputRef = ref(null);

const columns = [
  { key: 'featured_image', label: 'Image', type: 'image' },
  { key: 'name', label: 'Product Title', sortable: true },
  { key: 'sku', label: 'SKU' },
  { key: 'category', label: 'Category', type: 'relation', relationField: 'name' },
  { key: 'price', label: 'Price' },
  { key: 'stock', label: 'Inventory' },
  { key: 'status', label: 'Status' },
];

const form = reactive({
  name: '',
  sku: '',
  category_id: null,
  brand_id: null,
  price: 0,
  sale_price: null,
  stock: 10,
  featured_image: '',
  gallery: [],
  variants: [],
  short_description: '',
  description: '',
  is_featured: false,
  is_bestseller: false,
  is_new: true,
  status: 'published',
});

const addVariantRow = () => {
  if (!Array.isArray(form.variants)) form.variants = [];
  form.variants.push({
    size: 'M',
    color: 'Standard',
    color_code: '#14382A',
    sku: form.sku ? `${form.sku}-VAR-${form.variants.length + 1}` : '',
    price: form.price || 0,
    stock: 10,
  });
};

const removeVariantRow = (idx) => {
  if (Array.isArray(form.variants)) {
    form.variants.splice(idx, 1);
  }
};

const generateStandardSizes = () => {
  if (!Array.isArray(form.variants)) form.variants = [];
  const standardSizes = ['XS', 'S', 'M', 'L', 'XL'];
  standardSizes.forEach((sz) => {
    form.variants.push({
      size: sz,
      color: 'Noir',
      color_code: '#111111',
      sku: form.sku ? `${form.sku}-${sz}` : '',
      price: form.price || 0,
      stock: 5,
    });
  });
  toast.success('Added 5 standard size variants');
};

const exportCsv = async () => {
  exporting.value = true;
  try {
    const res = await api.get('/admin/products/export', { responseType: 'blob' });
    const blob = new Blob([res.data], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `catalog-products-${new Date().toISOString().slice(0, 10)}.csv`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    window.URL.revokeObjectURL(url);
    toast.success('Product catalog CSV exported successfully');
  } catch (err) {
    console.error(err);
    toast.error('Failed to export catalog');
  } finally {
    exporting.value = false;
  }
};

const handleImportFileChange = (e) => {
  const file = e.target.files?.[0];
  selectedImportFile.value = file || null;
};

const submitImport = async () => {
  if (!selectedImportFile.value) return;
  importing.value = true;
  try {
    const formData = new FormData();
    formData.append('file', selectedImportFile.value);
    const res = await api.post('/admin/products/import', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    toast.success(res.data.message || 'Catalog imported successfully');
    importModalOpen.value = false;
    selectedImportFile.value = null;
    if (importInputRef.value) importInputRef.value.value = '';
    fetchProducts(1);
  } catch (err) {
    console.error(err);
    toast.error(err.response?.data?.message || 'Failed to process import');
  } finally {
    importing.value = false;
  }
};

const fetchProducts = async (page = 1) => {
  loading.value = true;
  try {
    const params = {
      page,
      search: searchQuery.value || undefined,
      sort_by: sortBy.value,
      sort_direction: sortDirection.value,
    };
    const res = await api.get('/admin/products', { params });
    products.value = res.data.data || [];
    meta.value = res.data.meta || null;
  } catch (err) {
    console.error('Failed to load products', err);
  } finally {
    loading.value = false;
  }
};

const handleSort = (key) => {
  if (sortBy.value === key) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortBy.value = key;
    sortDirection.value = 'asc';
  }
  fetchProducts(1);
};

const addGalleryImage = () => {
  if (!Array.isArray(form.gallery)) form.gallery = [];
  form.gallery.push('');
};

const removeGalleryImage = (idx) => {
  if (Array.isArray(form.gallery)) {
    form.gallery.splice(idx, 1);
  }
};

const openCreateModal = () => {
  isEdit.value = false;
  currentId.value = null;
  Object.assign(form, {
    name: '',
    sku: '',
    category_id: null,
    brand_id: null,
    price: 0,
    sale_price: null,
    stock: 10,
    featured_image: '',
    gallery: [],
    variants: [],
    short_description: '',
    description: '',
    is_featured: false,
    is_bestseller: false,
    is_new: true,
    status: 'published',
  });
  formModalOpen.value = true;
};

const openEditModal = (item) => {
  isEdit.value = true;
  currentId.value = item.id;
  Object.assign(form, {
    name: item.name,
    sku: item.sku,
    category_id: item.category_id,
    brand_id: item.brand_id,
    price: Number(item.price),
    sale_price: item.sale_price ? Number(item.sale_price) : null,
    stock: item.stock,
    featured_image: item.featured_image || '',
    gallery: Array.isArray(item.gallery) ? [...item.gallery] : [],
    variants: Array.isArray(item.variants)
      ? item.variants.map((v) => ({
          size: v.size || 'M',
          color: v.color || 'Standard',
          color_code: v.color_code || '#14382A',
          sku: v.sku || '',
          price: Number(v.price || item.price),
          stock: Number(v.stock || 0),
        }))
      : [],
    short_description: item.short_description || '',
    description: item.description || '',
    is_featured: Boolean(item.is_featured),
    is_bestseller: Boolean(item.is_bestseller),
    is_new: Boolean(item.is_new),
    status: item.status || 'published',
  });
  formModalOpen.value = true;
};

const saveProduct = async () => {
  saving.value = true;
  try {
    const payload = {
      ...form,
      gallery: Array.isArray(form.gallery) ? form.gallery.filter((g) => Boolean(g && g.trim())) : [],
      variants: Array.isArray(form.variants) ? form.variants : [],
    };
    if (isEdit.value) {
      await api.put(`/admin/products/${currentId.value}`, payload);
      toast.success(`Product '${form.name}' updated successfully`);
    } else {
      await api.post('/admin/products', payload);
      toast.success(`Product '${form.name}' created successfully`);
    }
    formModalOpen.value = false;
    fetchProducts(meta.value?.current_page || 1);
  } catch (err) {
    console.error(err);
    toast.error(err.response?.data?.message || 'Error saving product');
  } finally {
    saving.value = false;
  }
};

const confirmDelete = async (item) => {
  if (!confirm(`Are you sure you want to delete '${item.name}'?`)) return;
  try {
    await api.delete(`/admin/products/${item.id}`);
    toast.success(`Deleted product '${item.name}'`);
    fetchProducts(meta.value?.current_page || 1);
  } catch (err) {
    toast.error('Failed to delete product');
  }
};

onMounted(async () => {
  fetchProducts(1);
  try {
    const [catRes, brandRes] = await Promise.all([
      api.get('/admin/resources/categories?per_page=-1'),
      api.get('/admin/resources/brands?per_page=-1'),
    ]);
    categories.value = catRes.data.data || [];
    brands.value = brandRes.data.data || [];
  } catch (err) {
    console.error(err);
  }
});
</script>
