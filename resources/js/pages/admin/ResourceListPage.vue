<template>
  <div v-if="config" class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3">
      <div>
        <h2 class="text-xl font-bold tracking-tight text-admin-text">{{ config.label }}</h2>
        <p v-if="config.description" class="text-xs text-admin-muted mt-0.5 max-w-2xl">{{ config.description }}</p>
      </div>

      <div class="flex items-center gap-2 shrink-0">
        <button v-if="config.exportUrl" type="button" class="adm-btn" :disabled="exporting" @click="exportCsv">
          {{ exporting ? 'ডাউনলোড হচ্ছে…' : 'CSV ডাউনলোড' }}
        </button>
        <router-link v-if="config.canCreate !== false" :to="`/admin/resources/${resourceKey}/create`" class="adm-btn adm-btn-primary">
          + নতুন {{ config.singular }}
        </router-link>
      </div>
    </div>

    <!-- Dynamic DataTable -->
    <DataTable
      :columns="config.columns"
      :items="items"
      :loading="loading"
      :meta="meta"
      v-model:search="searchQuery"
      :search-placeholder="config.searchPlaceholder"
      :sort-by="sortBy"
      :sort-direction="sortDirection"
      @sort="handleSort"
      @page="fetchData"
      @edit="handleEdit"
      @delete="confirmDelete"
    />

    <!-- Delete Confirmation Modal -->
    <BaseModal
      :is-open="deleteModalOpen"
      title="মুছে ফেলবেন?"
      max-width="sm"
      @close="deleteModalOpen = false"
    >
      <div class="space-y-4 text-xs text-[#111111]">
        <p>এই {{ config.singular }} স্থায়ীভাবে মুছে যাবে, ফেরানো যাবে না।</p>
        <div class="flex justify-end gap-3 pt-4 border-t border-[#D8CCBC]/40">
          <button
            type="button"
            @click="deleteModalOpen = false"
            class="px-4 py-2 border border-[#D8CCBC] text-xs uppercase tracking-wider font-medium"
          >
            বাতিল
          </button>
          <button
            type="button"
            @click="executeDelete"
            class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs uppercase tracking-wider font-semibold"
          >
            মুছে ফেলুন
          </button>
        </div>
      </div>
    </BaseModal>
  </div>

  <div v-else class="py-24 text-center text-xs text-rose-600">
    এই পেজটি পাওয়া যায়নি ({{ route.params.resource }})।
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { getResourceConfig } from '../../admin-resources';
import api from '../../api';
import DataTable from '../../components/admin/DataTable.vue';
import BaseModal from '../../components/common/BaseModal.vue';

const route = useRoute();
const router = useRouter();

const resourceKey = computed(() => route.params.resource);
const config = computed(() => getResourceConfig(resourceKey.value));

const items = ref([]);
const loading = ref(true);
const meta = ref(null);
const searchQuery = ref('');
const sortBy = ref('');
const sortDirection = ref('asc');
const deleteModalOpen = ref(false);
const exporting = ref(false);

/** Download the whole list as a CSV file (resources that set exportUrl). */
const exportCsv = async () => {
  exporting.value = true;
  try {
    const res = await api.get(config.value.exportUrl, { responseType: 'blob' });
    const url = URL.createObjectURL(new Blob([res.data], { type: 'text/csv' }));
    const a = Object.assign(document.createElement('a'), { href: url, download: `${resourceKey.value}-${new Date().toISOString().slice(0, 10)}.csv` });
    document.body.appendChild(a);
    a.click();
    a.remove();
    URL.revokeObjectURL(url);
  } catch {
    alert('ডাউনলোড করা যায়নি');
  } finally {
    exporting.value = false;
  }
};
const itemToDelete = ref(null);
let searchDebounce = null;

const fetchData = async (page = 1) => {
  if (!config.value) return;
  loading.value = true;
  try {
    const params = {
      page,
      search: searchQuery.value || undefined,
      sort_by: sortBy.value || undefined,
      sort_direction: sortDirection.value,
    };
    const res = await api.get(config.value.endpoint, { params });
    items.value = res.data.data || [];
    meta.value = res.data.meta || null;
  } catch (err) {
    console.error(`Failed to load ${resourceKey.value}`, err);
  } finally {
    loading.value = false;
  }
};

const handleSort = (colKey) => {
  if (sortBy.value === colKey) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortBy.value = colKey;
    sortDirection.value = 'asc';
  }
  fetchData(1);
};

watch(searchQuery, () => {
  clearTimeout(searchDebounce);
  searchDebounce = setTimeout(() => {
    fetchData(1);
  }, 300);
});

const handleEdit = (item) => {
  router.push(`/admin/resources/${resourceKey.value}/${item.id}/edit`);
};

const confirmDelete = (item) => {
  itemToDelete.value = item;
  deleteModalOpen.value = true;
};

const executeDelete = async () => {
  if (!itemToDelete.value) return;
  try {
    await api.delete(`${config.value.endpoint}/${itemToDelete.value.id}`);
    deleteModalOpen.value = false;
    itemToDelete.value = null;
    fetchData(meta.value?.current_page || 1);
  } catch (err) {
    alert(err.response?.data?.message || 'মুছে ফেলা যায়নি');
  }
};

watch(resourceKey, () => {
  items.value = [];
  searchQuery.value = '';
  sortBy.value = '';
  fetchData(1);
});

onMounted(() => {
  fetchData(1);
});
</script>
