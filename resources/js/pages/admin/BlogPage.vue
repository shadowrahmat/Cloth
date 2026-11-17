<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-[#E7EBE8]">
      <div>
        <h2 class="text-xl font-bold tracking-tight text-[#171C1A]">Blog & Editorial Engine</h2>
        <p class="text-xs text-[#69736E] mt-0.5">Author high-fashion articles, styling guides, runway essays, and journal stories.</p>
      </div>

      <div class="flex items-center gap-3">
        <button
          type="button"
          @click="openCreateModal"
          class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-[#14382A] text-white text-xs font-semibold rounded-md hover:bg-[#16A36A] transition-colors shadow-xs"
        >
          <span>+</span>
          <span>New Journal Post</span>
        </button>
      </div>
    </div>

    <!-- Posts Table -->
    <div class="bg-white rounded-lg border border-[#E7EBE8] overflow-hidden shadow-xs">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead>
            <tr class="bg-[#F7F8FA] text-[#69736E] border-b border-[#E7EBE8]">
              <th class="py-3 px-4 font-semibold">Article</th>
              <th class="py-3 px-4 font-semibold">Category</th>
              <th class="py-3 px-4 font-semibold">Author</th>
              <th class="py-3 px-4 font-semibold text-center">Status</th>
              <th class="py-3 px-4 font-semibold text-center">Views</th>
              <th class="py-3 px-4 font-semibold text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#E7EBE8]">
            <tr v-for="post in posts" :key="post.id" class="hover:bg-[#F7F8FA]">
              <td class="py-3 px-4 font-medium text-[#171C1A]">
                <div class="flex items-center gap-3">
                  <div class="w-12 h-12 rounded-md bg-[#F7F8FA] border border-[#E7EBE8] overflow-hidden shrink-0">
                    <img v-if="post.featured_image" :src="post.featured_image" class="w-full h-full object-cover" />
                    <div v-else class="w-full h-full flex items-center justify-center text-slate-300">📰</div>
                  </div>
                  <div class="min-w-0">
                    <span class="block font-semibold text-sm truncate max-w-sm">{{ post.title }}</span>
                    <span class="text-[11px] font-mono text-[#69736E]">/blog/{{ post.slug }}</span>
                  </div>
                </div>
              </td>
              <td class="py-3 px-4 text-[#69736E]">
                <span class="px-2 py-0.5 rounded-sm bg-slate-100 font-medium text-[#14382A]">
                  {{ post.category?.name || 'General Fashion' }}
                </span>
              </td>
              <td class="py-3 px-4 text-[#171C1A]">
                {{ post.author?.name || 'Editorial Team' }}
              </td>
              <td class="py-3 px-4 text-center">
                <StatusBadge :status="post.status" />
              </td>
              <td class="py-3 px-4 text-center font-mono text-[#69736E]">
                {{ post.views_count || 0 }}
              </td>
              <td class="py-3 px-4 text-right space-x-2 whitespace-nowrap">
                <button
                  type="button"
                  @click="openEditModal(post)"
                  class="px-2.5 py-1 text-xs text-[#14382A] hover:bg-slate-100 rounded-md transition-colors font-medium"
                >
                  Edit
                </button>
                <button
                  type="button"
                  @click="deletePost(post.id)"
                  class="px-2 py-1 text-xs text-rose-600 hover:bg-rose-50 rounded-md transition-colors"
                >
                  Delete
                </button>
              </td>
            </tr>
            <tr v-if="!posts.length">
              <td colspan="6" class="py-8 text-center text-slate-400">No blog articles published yet. Click "New Journal Post" to begin.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Article Editor Modal -->
    <BaseModal
      :is-open="isModalOpen"
      :title="editingId ? 'Edit Journal Post' : 'New Fashion Article'"
      size="xl"
      @close="isModalOpen = false"
    >
      <form @submit.prevent="savePost" class="space-y-4 text-xs">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div class="sm:col-span-2">
            <label class="block text-xs font-semibold text-[#171C1A] mb-1">Article Headline *</label>
            <input
              v-model="form.title"
              type="text"
              required
              placeholder="e.g. The Architecture of Italian Cashmere"
              class="w-full bg-white border border-[#E7EBE8] rounded-md px-3 py-2 text-[#171C1A] focus:outline-none focus:ring-1 focus:ring-[#16A36A]"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-[#171C1A] mb-1">Status Workflow *</label>
            <select
              v-model="form.status"
              class="w-full bg-white border border-[#E7EBE8] rounded-md px-3 py-2 text-[#171C1A] focus:outline-none focus:ring-1 focus:ring-[#16A36A]"
            >
              <option value="draft">Draft</option>
              <option value="published">Published</option>
              <option value="scheduled">Scheduled</option>
            </select>
          </div>
        </div>

        <div>
          <label class="block text-xs font-semibold text-[#171C1A] mb-1">Featured Cover Image URL</label>
          <input
            v-model="form.featured_image"
            type="text"
            placeholder="https://..."
            class="w-full bg-white border border-[#E7EBE8] rounded-md px-3 py-2 text-[#171C1A] focus:outline-none focus:ring-1 focus:ring-[#16A36A]"
          />
        </div>

        <div>
          <label class="block text-xs font-semibold text-[#171C1A] mb-1">Short Excerpt / Deck</label>
          <textarea
            v-model="form.excerpt"
            rows="2"
            placeholder="A short introductory synopsis for the journal rail..."
            class="w-full bg-white border border-[#E7EBE8] rounded-md p-3 text-[#171C1A] focus:outline-none focus:ring-1 focus:ring-[#16A36A]"
          ></textarea>
        </div>

        <div>
          <label class="block text-xs font-semibold text-[#171C1A] mb-1">Article Story Content (HTML / Text) *</label>
          <textarea
            v-model="form.content"
            rows="10"
            required
            placeholder="Write editorial story..."
            class="w-full font-mono text-xs bg-white border border-[#E7EBE8] rounded-md p-3 text-[#171C1A] focus:outline-none focus:ring-1 focus:ring-[#16A36A]"
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
            <label class="block text-xs font-semibold text-[#171C1A] mb-1">SEO Description</label>
            <input
              v-model="form.seo_description"
              type="text"
              class="w-full bg-white border border-[#E7EBE8] rounded-md px-3 py-2 text-[#171C1A] focus:outline-none focus:ring-1 focus:ring-[#16A36A]"
            />
          </div>
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
            Save Article
          </button>
        </div>
      </form>
    </BaseModal>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import BaseModal from '../../components/admin/BaseModal.vue';
import StatusBadge from '../../components/admin/StatusBadge.vue';
import { useToastStore } from '../../stores/toast';
import api from '../../api';

const toast = useToastStore();
const posts = ref([]);
const isModalOpen = ref(false);
const editingId = ref(null);

const form = ref({
  title: '',
  category_id: null,
  featured_image: '',
  excerpt: '',
  content: '',
  status: 'draft',
  seo_title: '',
  seo_description: '',
});

const fetchPosts = async () => {
  try {
    const res = await api.get('/admin/blog');
    posts.value = res.data?.data?.posts?.data || [];
  } catch (err) {
    console.error('Failed to load blog posts', err);
  }
};

const openCreateModal = () => {
  editingId.value = null;
  form.value = {
    title: '',
    category_id: null,
    featured_image: '',
    excerpt: '',
    content: '',
    status: 'draft',
    seo_title: '',
    seo_description: '',
  };
  isModalOpen.value = true;
};

const openEditModal = (p) => {
  editingId.value = p.id;
  form.value = { ...p };
  isModalOpen.value = true;
};

const savePost = async () => {
  try {
    if (editingId.value) {
      await api.put(`/admin/blog/${editingId.value}`, form.value);
      toast.success('Article updated successfully');
    } else {
      await api.post('/admin/blog', form.value);
      toast.success('Article published successfully');
    }
    isModalOpen.value = false;
    fetchPosts();
  } catch (err) {
    toast.error(err.response?.data?.message || 'Failed to save article');
  }
};

const deletePost = async (id) => {
  if (!confirm('Are you sure you want to delete this article?')) return;
  try {
    await api.delete(`/admin/blog/${id}`);
    toast.success('Article deleted');
    fetchPosts();
  } catch (err) {
    toast.error('Failed to delete article');
  }
};

onMounted(() => {
  fetchPosts();
});
</script>
