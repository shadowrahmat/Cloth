<template>
  <div v-if="config" class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <span class="text-xs uppercase tracking-[0.25em] text-[#A89B8A] font-medium block">Resource Management</span>
        <h1 class="font-serif text-3xl text-[#111111]">{{ config.label }}</h1>
      </div>

      <router-link
        :to="`/admin/resources/${resourceKey}/create`"
        class="px-5 py-2.5 bg-[#111111] hover:bg-black text-white text-xs uppercase tracking-wider font-semibold transition-colors shadow-xs shrink-0 inline-flex items-center gap-2"
      >
        <span>+ Add New {{ config.singular }}</span>
      </router-link>
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
      title="Confirm Record Deletion"
      max-width="sm"
      @close="deleteModalOpen = false"
    >
      <div class="space-y-4 text-xs text-[#111111]">
        <p>Are you sure you want to permanently delete this {{ config.singular?.toLowerCase() }}? This action cannot be reversed.</p>
        <div class="flex justify-end gap-3 pt-4 border-t border-[#D8CCBC]/40">
          <button
            type="button"
            @click="deleteModalOpen = false"
            class="px-4 py-2 border border-[#D8CCBC] text-xs uppercase tracking-wider font-medium"
          >
            Cancel
          </button>
          <button
            type="button"
            @click="executeDelete"
            class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs uppercase tracking-wider font-semibold"
          >
            Delete
          </button>
        </div>
      </div>
    </BaseModal>
  </div>

  <div v-else class="py-24 text-center text-xs text-rose-600">
    Unregistered resource '{{ route.params.resource }}'. Please check admin-resources.js configuration.
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
    alert(err.response?.data?.message || 'Delete operation failed');
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
