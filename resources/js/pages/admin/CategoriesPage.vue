<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-[#E7EBE8]">
      <div>
        <h2 class="text-xl font-bold tracking-tight text-[#171C1A]">Category Hierarchy & Taxonomy</h2>
        <p class="text-xs text-[#69736E] mt-0.5">Organize collections, nested parent-child categories, banners, and search engine metadata.</p>
      </div>

      <div class="flex items-center gap-3">
        <button
          type="button"
          @click="openCreateModal"
          class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-[#14382A] text-white text-xs font-semibold rounded-md hover:bg-[#16A36A] transition-colors shadow-xs"
        >
          <span>+</span>
          <span>Create Category</span>
        </button>
      </div>
    </div>

    <!-- Categories List Table -->
    <div class="bg-white rounded-lg border border-[#E7EBE8] overflow-hidden shadow-xs">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead>
            <tr class="bg-[#F7F8FA] text-[#69736E] border-b border-[#E7EBE8]">
              <th class="py-3 px-4 font-semibold">Category</th>
              <th class="py-3 px-4 font-semibold">Parent Category</th>
              <th class="py-3 px-4 font-semibold text-center">Products</th>
              <th class="py-3 px-4 font-semibold text-center">Featured</th>
              <th class="py-3 px-4 font-semibold text-center">Status</th>
              <th class="py-3 px-4 font-semibold text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#E7EBE8]">
            <tr v-for="cat in categories" :key="cat.id" class="hover:bg-[#F7F8FA]">
              <td class="py-3 px-4 font-medium text-[#171C1A]">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-md bg-[#F7F8FA] border border-[#E7EBE8] overflow-hidden shrink-0">
                    <img v-if="cat.image" :src="cat.image" class="w-full h-full object-cover" />
                    <div v-else class="w-full h-full flex items-center justify-center text-slate-300">📁</div>
                  </div>
                  <div>
                    <span class="block font-semibold text-sm">{{ cat.name }}</span>
                    <span class="text-[11px] font-mono text-[#69736E]">/{{ cat.slug }}</span>
                  </div>
                </div>
              </td>
              <td class="py-3 px-4 text-[#69736E]">
                <span v-if="cat.parent" class="font-medium text-[#14382A]">↳ {{ cat.parent.name }}</span>
                <span v-else class="text-slate-400">Root Category</span>
              </td>
              <td class="py-3 px-4 text-center">
                <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-800">
                  {{ cat.products_count || 0 }} items
                </span>
              </td>
              <td class="py-3 px-4 text-center">
                <span v-if="cat.is_featured" class="text-amber-500 font-bold">★ Featured</span>
                <span v-else class="text-slate-400">—</span>
              </td>
              <td class="py-3 px-4 text-center">
                <StatusBadge :status="cat.is_active ? 'active' : 'inactive'" />
              </td>
              <td class="py-3 px-4 text-right space-x-2 whitespace-nowrap">
                <button
                  type="button"
                  @click="openEditModal(cat)"
                  class="px-2.5 py-1 text-xs text-[#14382A] hover:bg-slate-100 rounded-md transition-colors"
                >
                  Edit
                </button>
                <button
                  type="button"
                  @click="deleteCategory(cat.id)"
                  class="px-2.5 py-1 text-xs text-rose-600 hover:bg-rose-50 rounded-md transition-colors"
                >
                  Delete
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Category Modal -->
    <BaseModal
      :is-open="isModalOpen"
      :title="editingId ? 'Edit Category' : 'Create New Collection Category'"
      size="lg"
      @close="isModalOpen = false"
    >
      <form @submit.prevent="saveCategory" class="space-y-4 text-xs">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-semibold text-[#171C1A] mb-1">Category Name *</label>
            <input
              v-model="form.name"
              type="text"
              required
              class="w-full bg-white border border-[#E7EBE8] rounded-md px-3 py-2 text-[#171C1A] focus:outline-none focus:ring-1 focus:ring-[#16A36A]"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-[#171C1A] mb-1">Parent Category</label>
            <select
              v-model="form.parent_id"
              class="w-full bg-white border border-[#E7EBE8] rounded-md px-3 py-2 text-[#171C1A] focus:outline-none focus:ring-1 focus:ring-[#16A36A]"
            >
              <option :value="null">None (Top-level Root Category)</option>
              <option v-for="c in rootCategories" :key="c.id" :value="c.id">
                {{ c.name }}
              </option>
            </select>
          </div>
        </div>

        <div>
          <label class="block text-xs font-semibold text-[#171C1A] mb-1">Image URL / Banner Path</label>
          <input
            v-model="form.image"
            type="text"
            placeholder="https://..."
            class="w-full bg-white border border-[#E7EBE8] rounded-md px-3 py-2 text-[#171C1A] focus:outline-none focus:ring-1 focus:ring-[#16A36A]"
          />
        </div>

        <div>
          <label class="block text-xs font-semibold text-[#171C1A] mb-1">Description</label>
          <textarea
            v-model="form.description"
            rows="2"
            class="w-full bg-white border border-[#E7EBE8] rounded-md p-3 text-[#171C1A] focus:outline-none focus:ring-1 focus:ring-[#16A36A]"
          ></textarea>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-semibold text-[#171C1A] mb-1">SEO Title</label>
            <input
              v-model="form.seo_title"
              type="text"
              class="w-full bg-white border border-[#E7EBE8] rounded-md px-3 py-2 text-[#171C1A] focus:outline-none focus:ring-1 focus:ring-[#16A36A]"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-[#171C1A] mb-1">Display Order</label>
            <input
              v-model.number="form.display_order"
              type="number"
              class="w-full bg-white border border-[#E7EBE8] rounded-md px-3 py-2 text-[#171C1A] focus:outline-none focus:ring-1 focus:ring-[#16A36A]"
            />
          </div>
        </div>

        <div class="flex items-center gap-6 pt-2">
          <label class="flex items-center gap-2 text-xs font-medium text-[#171C1A]">
            <input v-model="form.is_active" type="checkbox" class="rounded border-slate-300 text-[#16A36A] focus:ring-[#16A36A]" />
            Active Category
          </label>
          <label class="flex items-center gap-2 text-xs font-medium text-[#171C1A]">
            <input v-model="form.is_featured" type="checkbox" class="rounded border-slate-300 text-[#16A36A] focus:ring-[#16A36A]" />
            Featured on Storefront
          </label>
        </div>

        <div class="pt-4 flex justify-end gap-3 border-t border-[#E7EBE8]">
          <button
            type="button"
            @click="isModalOpen = false"
            class="px-4 py-2 border border-[#E7EBE8] text-xs font-semibold text-[#171C1A] rounded-md hover:bg-slate-50"
          >
            Cancel
          </button>
          <button
            type="submit"
            class="px-4 py-2 bg-[#14382A] hover:bg-[#16A36A] text-white text-xs font-semibold rounded-md shadow-xs transition-colors"
          >
            Save Category
          </button>
        </div>
      </form>
    </BaseModal>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import BaseModal from '../../components/admin/BaseModal.vue';
import StatusBadge from '../../components/admin/StatusBadge.vue';
import { useToastStore } from '../../stores/toast';
import api from '../../api';

const toast = useToastStore();
const categories = ref([]);
const isModalOpen = ref(false);
const editingId = ref(null);

const form = ref({
  name: '',
  parent_id: null,
  image: '',
  description: '',
  seo_title: '',
  display_order: 0,
  is_active: true,
  is_featured: false,
});

const rootCategories = computed(() => {
  return categories.value.filter(c => c.id !== editingId.value && !c.parent_id);
});

const fetchCategories = async () => {
  try {
    const res = await api.get('/admin/resources/categories');
    categories.value = res.data?.data?.data || [];
  } catch (err) {
    console.error('Failed to load categories', err);
  }
};

const openCreateModal = () => {
  editingId.value = null;
  form.value = {
    name: '',
    parent_id: null,
    image: '',
    description: '',
    seo_title: '',
    display_order: 0,
    is_active: true,
    is_featured: false,
  };
  isModalOpen.value = true;
};

const openEditModal = (cat) => {
  editingId.value = cat.id;
  form.value = {
    ...cat,
    parent_id: cat.parent_id || null,
  };
  isModalOpen.value = true;
};

const saveCategory = async () => {
  try {
    if (editingId.value) {
      await api.put(`/admin/resources/categories/${editingId.value}`, form.value);
      toast.success('Category updated successfully');
    } else {
      await api.post('/admin/resources/categories', form.value);
      toast.success('Category created successfully');
    }
    isModalOpen.value = false;
    fetchCategories();
  } catch (err) {
    toast.error(err.response?.data?.message || 'Failed to save category');
  }
};

const deleteCategory = async (id) => {
  if (!confirm('Are you sure you want to delete this category?')) return;
  try {
    await api.delete(`/admin/resources/categories/${id}`);
    toast.success('Category deleted');
    fetchCategories();
  } catch (err) {
    toast.error('Failed to delete category');
  }
};

onMounted(() => {
  fetchCategories();
});
</script>
