<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-[#E7EBE8]">
      <div>
        <h2 class="text-xl font-bold tracking-tight text-[#171C1A]">Pages & Policy Content CMS</h2>
        <p class="text-xs text-[#69736E] mt-0.5">Edit store policies, About Us manifesto, FAQs, and brand editorial pages.</p>
      </div>

      <div class="flex items-center gap-3">
        <button
          type="button"
          v-can="'create_cms'" @click="openCreateModal"
          class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-[#14382A] text-white text-xs font-semibold rounded-md hover:bg-[#16A36A] transition-colors shadow-xs"
        >
          <span>+</span>
          <span>Create Page</span>
        </button>
      </div>
    </div>

    <!-- Pages Table -->
    <div class="bg-white rounded-lg border border-[#E7EBE8] overflow-hidden shadow-xs">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead>
            <tr class="bg-[#F7F8FA] text-[#69736E] border-b border-[#E7EBE8]">
              <th class="py-3 px-4 font-semibold">Page Title</th>
              <th class="py-3 px-4 font-semibold">Slug / URL</th>
              <th class="py-3 px-4 font-semibold">Template</th>
              <th class="py-3 px-4 font-semibold text-center">Status</th>
              <th class="py-3 px-4 font-semibold text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#E7EBE8]">
            <tr v-for="page in pages" :key="page.id" class="hover:bg-[#F7F8FA]">
              <td class="py-3 px-4 font-semibold text-[#171C1A]">
                <div>{{ page.title }}</div>
                <div v-if="page.seo_title" class="text-[11px] text-[#69736E]">SEO: {{ page.seo_title }}</div>
              </td>
              <td class="py-3 px-4 font-mono text-[#14382A]">
                <router-link :to="`/pages/${page.slug}`" target="_blank" class="hover:underline flex items-center gap-1">
                  <span>/pages/{{ page.slug }}</span>
                  <span class="text-[10px]">↗</span>
                </router-link>
              </td>
              <td class="py-3 px-4 capitalize text-[#69736E]">
                {{ page.template || 'Default' }}
              </td>
              <td class="py-3 px-4 text-center">
                <StatusBadge :status="page.is_active ? 'active' : 'inactive'" />
              </td>
              <td class="py-3 px-4 text-right space-x-2 whitespace-nowrap">
                <button
                  type="button"
                  v-can="'update_cms'" @click="openEditModal(page)"
                  class="px-2.5 py-1 text-xs text-[#14382A] hover:bg-slate-100 rounded-md transition-colors font-medium"
                >
                  Edit Page
                </button>
                <button
                  type="button"
                  v-can="'delete_cms'" @click="deletePage(page.id)"
                  class="px-2 py-1 text-xs text-rose-600 hover:bg-rose-50 rounded-md transition-colors"
                >
                  Delete
                </button>
              </td>
            </tr>
            <tr v-if="!pages.length">
              <td colspan="5" class="py-8 text-center text-slate-400">No CMS pages registered yet.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Page Editor Modal -->
    <BaseModal
      :is-open="isModalOpen"
      :title="editingId ? 'Edit Page' : 'Create New CMS Page'"
      size="xl"
      @close="isModalOpen = false"
    >
      <form @submit.prevent="savePage" class="space-y-4 text-xs">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div class="sm:col-span-2">
            <label class="block text-xs font-semibold text-[#171C1A] mb-1">Page Title *</label>
            <input
              v-model="form.title"
              type="text"
              required
              class="w-full bg-white border border-[#E7EBE8] rounded-md px-3 py-2 text-[#171C1A] focus:outline-none focus:ring-1 focus:ring-[#16A36A]"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-[#171C1A] mb-1">Layout Template</label>
            <select
              v-model="form.template"
              class="w-full bg-white border border-[#E7EBE8] rounded-md px-3 py-2 text-[#171C1A] focus:outline-none focus:ring-1 focus:ring-[#16A36A]"
            >
              <option value="default">Default Article</option>
              <option value="about">About Brand</option>
              <option value="policy">Legal Policy</option>
              <option value="faq">FAQ Layout</option>
            </select>
          </div>
        </div>

        <div>
          <label class="block text-xs font-semibold text-[#171C1A] mb-1">Page Content (HTML / Markdown / Body) *</label>
          <textarea
            v-model="form.content"
            rows="10"
            required
            placeholder="Write page content..."
            class="w-full font-mono text-xs bg-white border border-[#E7EBE8] rounded-md p-3 text-[#171C1A] focus:outline-none focus:ring-1 focus:ring-[#16A36A]"
          ></textarea>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-semibold text-[#171C1A] mb-1">SEO Meta Title</label>
            <input
              v-model="form.seo_title"
              type="text"
              class="w-full bg-white border border-[#E7EBE8] rounded-md px-3 py-2 text-[#171C1A] focus:outline-none focus:ring-1 focus:ring-[#16A36A]"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-[#171C1A] mb-1">SEO Meta Description</label>
            <input
              v-model="form.seo_description"
              type="text"
              class="w-full bg-white border border-[#E7EBE8] rounded-md px-3 py-2 text-[#171C1A] focus:outline-none focus:ring-1 focus:ring-[#16A36A]"
            />
          </div>
        </div>

        <div class="flex items-center gap-2 pt-2">
          <input
            v-model="form.is_active"
            type="checkbox"
            id="page_active"
            class="rounded border-slate-300 text-[#16A36A] focus:ring-[#16A36A]"
          />
          <label for="page_active" class="text-xs font-medium text-[#171C1A]">Publish page on public storefront</label>
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
            Save Page
          </button>
        </div>
      </form>
    </BaseModal>
  </div>
</template>

<script setup>
import { useAdminDeepLink } from '../../composables/useAdminDeepLink';
import { ref, onMounted } from 'vue';
import BaseModal from '../../components/admin/BaseModal.vue';
import StatusBadge from '../../components/admin/StatusBadge.vue';
import { useToastStore } from '../../stores/toast';
import api from '../../api';

const toast = useToastStore();
const pages = ref([]);
const isModalOpen = ref(false);
const editingId = ref(null);

const form = ref({
  title: '',
  template: 'default',
  content: '',
  seo_title: '',
  seo_description: '',
  is_active: true,
});

const fetchPages = async () => {
  try {
    const res = await api.get('/admin/resources/pages');
    pages.value = res.data?.data?.data || [];
  } catch (err) {
    console.error('Failed to load pages', err);
  }
};

const openCreateModal = () => {
  editingId.value = null;
  form.value = {
    title: '',
    template: 'default',
    content: '',
    seo_title: '',
    seo_description: '',
    is_active: true,
  };
  isModalOpen.value = true;
};

const openEditModal = (page) => {
  editingId.value = page.id;
  form.value = { ...page };
  isModalOpen.value = true;
};

const savePage = async () => {
  try {
    if (editingId.value) {
      await api.put(`/admin/resources/pages/${editingId.value}`, form.value);
      toast.success('Page updated successfully');
    } else {
      await api.post('/admin/resources/pages', form.value);
      toast.success('Page created successfully');
    }
    isModalOpen.value = false;
    fetchPages();
  } catch (err) {
    toast.error(err.response?.data?.message || 'Failed to save page');
  }
};

const deletePage = async (id) => {
  if (!confirm('Are you sure you want to delete this page?')) return;
  try {
    await api.delete(`/admin/resources/pages/${id}`);
    toast.success('Page deleted');
    fetchPages();
  } catch (err) {
    toast.error('Failed to delete page');
  }
};

onMounted(() => {
  fetchPages();
});

// Open from links like ?search=... or ?new=1 (sidebar, Ctrl+K, dashboard)
useAdminDeepLink({ onNew: openCreateModal });
</script>
