<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <span class="text-xs uppercase tracking-[0.25em] text-[#A89B8A] font-medium block">Visual CMS</span>
        <h1 class="font-serif text-3xl text-[#111111]">Homepage Section Builder</h1>
      </div>

      <div class="flex items-center gap-3">
        <router-link
          to="/"
          target="_blank"
          class="px-4 py-2 border border-[#D8CCBC] text-xs uppercase tracking-wider font-semibold text-[#111111] hover:bg-white"
        >
          View Live Site ↗
        </router-link>
      </div>
    </div>

    <router-link to="/admin/appearance" class="flex items-center justify-between gap-3 p-4 bg-emerald-50/70 border border-emerald-200/70 rounded-lg text-sm text-[#14382A] hover:bg-emerald-50">
      <span><strong>লোগো, হেডার মেনু, ঘোষণা বার ও ফুটার</strong> এখন <strong>সাইট কাস্টমাইজ</strong> পেজ থেকে পরিবর্তন করুন।</span>
      <span class="shrink-0 font-semibold">সাইট কাস্টমাইজ →</span>
    </router-link>

    <!-- Sections Ordering List -->
    <div class="bg-white border border-[#D8CCBC]/40 shadow-xs divide-y divide-[#D8CCBC]/30">
      <div
        v-for="(section, index) in sections"
        :key="section.id"
        class="p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-[#F7F5F0]/60 transition-colors"
      >
        <div class="flex items-center gap-4">
          <!-- Order Badge -->
          <span class="w-8 h-8 rounded-full bg-[#111111] text-white text-xs font-mono font-bold flex items-center justify-center shrink-0">
            {{ index + 1 }}
          </span>

          <div>
            <div class="flex items-center gap-3">
              <h4 class="font-serif text-base text-[#111111]">{{ section.title }}</h4>
              <span class="px-2 py-0.5 text-[9px] uppercase tracking-wider font-semibold bg-[#F7F5F0] border border-[#D8CCBC]/60 text-[#111111]">
                {{ section.type }}
              </span>
              <span
                :class="[
                  'px-2 py-0.5 text-[9px] uppercase tracking-wider font-semibold',
                  section.is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'
                ]"
              >
                {{ section.is_active ? 'Live' : 'Hidden' }}
              </span>
            </div>
            <p v-if="section.subtitle" class="text-xs text-[#A89B8A] mt-0.5">{{ section.subtitle }}</p>
          </div>
        </div>

        <!-- Controls: Move Up/Down, Edit, Toggle -->
        <div class="flex items-center gap-2 shrink-0">
          <!-- Move Up -->
          <button
            type="button"
            :disabled="index === 0"
            @click="moveSection(index, -1)"
            class="p-2 border border-[#D8CCBC] text-[#111111] disabled:opacity-30 hover:bg-[#F7F5F0]"
            title="Move Up"
          >
            ↑
          </button>
          <!-- Move Down -->
          <button
            type="button"
            :disabled="index === sections.length - 1"
            @click="moveSection(index, 1)"
            class="p-2 border border-[#D8CCBC] text-[#111111] disabled:opacity-30 hover:bg-[#F7F5F0]"
            title="Move Down"
          >
            ↓
          </button>

          <!-- Toggle Visibility -->
          <button
            type="button"
            @click="toggleActive(section)"
            :class="[
              'px-3 py-1.5 text-xs font-semibold uppercase tracking-wider border',
              section.is_active ? 'border-amber-300 bg-amber-50 text-amber-800' : 'border-emerald-300 bg-emerald-50 text-emerald-800'
            ]"
          >
            {{ section.is_active ? 'Hide' : 'Enable' }}
          </button>

          <!-- Edit Configuration -->
          <button
            type="button"
            @click="openEditModal(section)"
            class="px-4 py-1.5 bg-[#111111] text-white text-xs uppercase tracking-wider font-semibold hover:bg-black"
          >
            Edit Settings
          </button>
        </div>
      </div>
    </div>

    <!-- Edit Section Modal -->
    <BaseModal
      :is-open="editModalOpen"
      :title="`Edit ${editingSection?.title || 'Section'}`"
      max-width="3xl"
      @close="editModalOpen = false"
    >
      <form v-if="editingSection" @submit.prevent="saveSectionChanges" class="space-y-6 text-xs text-[#111111]">
        <div class="grid grid-cols-12 gap-4">
          <div class="col-span-12 sm:col-span-8">
            <label class="block font-semibold uppercase tracking-wider mb-1">Section Display Title</label>
            <input type="text" v-model="editForm.title" required class="w-full px-3 py-2 border border-[#D8CCBC]" />
          </div>
          <div class="col-span-12 sm:col-span-4">
            <label class="block font-semibold uppercase tracking-wider mb-1">Subtitle / Kicker</label>
            <input type="text" v-model="editForm.subtitle" class="w-full px-3 py-2 border border-[#D8CCBC]" />
          </div>

          <!-- Specific fields for Hero -->
          <!-- Category showcase (scroll section): texts + which categories, in which order -->
          <template v-if="editingSection.type === 'category_showcase'">
            <div class="col-span-12 sm:col-span-4">
              <label class="block font-semibold mb-1">ছোট উপরের লেখা</label>
              <input type="text" v-model="editForm.content.kicker" class="w-full px-3 py-2 border border-[#D8CCBC]" />
            </div>
            <div class="col-span-12 sm:col-span-8">
              <label class="block font-semibold mb-1">শিরোনাম</label>
              <input type="text" v-model="editForm.content.heading" class="w-full px-3 py-2 border border-[#D8CCBC]" />
            </div>
            <div class="col-span-12 sm:col-span-8">
              <label class="block font-semibold mb-1">বিবরণ</label>
              <input type="text" v-model="editForm.content.description" class="w-full px-3 py-2 border border-[#D8CCBC]" />
            </div>
            <div class="col-span-12 sm:col-span-4">
              <label class="block font-semibold mb-1">প্রতি ক্যাটাগরিতে ছবি</label>
              <select v-model.number="editForm.content.products_per_category" class="w-full px-3 py-2 border border-[#D8CCBC] bg-white">
                <option v-for="n in 4" :key="n" :value="n">{{ n }}টি পণ্য</option>
              </select>
            </div>

            <div class="col-span-12 grid sm:grid-cols-2 gap-4">
              <div>
                <p class="font-semibold mb-1.5">দেখানো ক্যাটাগরি (ক্রম অনুযায়ী)</p>
                <p v-if="!showcaseCats.length" class="p-3 border border-dashed border-[#D8CCBC] text-[#A89B8A]">
                  কিছু বাছাই না করলে সবচেয়ে বেশি পণ্য থাকা ৫টি ক্যাটাগরি নিজে থেকে দেখাবে।
                </p>
                <ol v-else class="border border-[#D8CCBC] divide-y divide-[#D8CCBC]/50">
                  <li v-for="(slug, i) in showcaseCats" :key="slug" class="flex items-center gap-2 px-3 py-2">
                    <span class="w-5 text-[#A89B8A] tabular-nums">{{ i + 1 }}.</span>
                    <span class="flex-1">{{ categoryName(slug) }}</span>
                    <button type="button" :disabled="i === 0" @click="moveShowcase(i, -1)" class="px-1.5 disabled:opacity-30" title="উপরে">↑</button>
                    <button type="button" :disabled="i === showcaseCats.length - 1" @click="moveShowcase(i, 1)" class="px-1.5 disabled:opacity-30" title="নিচে">↓</button>
                    <button type="button" @click="showcaseCats.splice(i, 1)" class="px-1.5 text-rose-600" title="সরান">✕</button>
                  </li>
                </ol>
              </div>
              <div>
                <p class="font-semibold mb-1.5">ক্যাটাগরি যোগ করুন</p>
                <div class="max-h-56 overflow-y-auto border border-[#D8CCBC] divide-y divide-[#D8CCBC]/40">
                  <button
                    v-for="c in allCategories.filter((c) => !showcaseCats.includes(c.slug))"
                    :key="c.slug" type="button"
                    @click="showcaseCats.push(c.slug)"
                    :class="['w-full text-left px-3 py-2 hover:bg-[#F7F5F0]', c.is_child && 'pl-6 text-[#111111]/75']"
                  >+ {{ c.name }}</button>
                </div>
              </div>
            </div>
          </template>

          <template v-else-if="editingSection.type === 'hero'">
            <div class="col-span-12">
              <label class="block font-semibold uppercase tracking-wider mb-1">Headline</label>
              <input type="text" v-model="editForm.content.headline" class="w-full px-3 py-2 border border-[#D8CCBC]" />
            </div>
            <div class="col-span-12">
              <label class="block font-semibold uppercase tracking-wider mb-1">Subheadline Description</label>
              <textarea rows="3" v-model="editForm.content.subheadline" class="w-full px-3 py-2 border border-[#D8CCBC]"></textarea>
            </div>
            <div class="col-span-12 sm:col-span-6">
              <label class="block font-semibold uppercase tracking-wider mb-1">Primary CTA Button Text</label>
              <input type="text" v-model="editForm.content.primary_cta_text" class="w-full px-3 py-2 border border-[#D8CCBC]" />
            </div>
            <div class="col-span-12 sm:col-span-6">
              <label class="block font-semibold uppercase tracking-wider mb-1">Primary CTA URL</label>
              <input type="text" v-model="editForm.content.primary_cta_link" class="w-full px-3 py-2 border border-[#D8CCBC]" />
            </div>
            <div class="col-span-12">
              <label class="block font-semibold uppercase tracking-wider mb-1">Hero Desktop Background Image</label>
              <MediaPicker v-model="editForm.content.desktop_image" />
            </div>
          </template>

          <!-- Specific fields for Editorial / Announcement / Generic -->
          <template v-else>
            <div class="col-span-12">
              <label class="block font-semibold uppercase tracking-wider mb-1">Section Heading Text</label>
              <input type="text" v-model="editForm.content.heading" class="w-full px-3 py-2 border border-[#D8CCBC]" />
            </div>
            <div class="col-span-12">
              <label class="block font-semibold uppercase tracking-wider mb-1">Section Description / Body Copy</label>
              <textarea rows="4" v-model="editForm.content.description" class="w-full px-3 py-2 border border-[#D8CCBC]"></textarea>
            </div>
            <div v-if="editForm.content.text !== undefined" class="col-span-12">
              <label class="block font-semibold uppercase tracking-wider mb-1">Announcement Ticker Text</label>
              <input type="text" v-model="editForm.content.text" class="w-full px-3 py-2 border border-[#D8CCBC]" />
            </div>
            <label v-if="editingSection.type === 'new_arrivals'" class="col-span-12 flex items-center gap-2 cursor-pointer">
              <input type="checkbox" :checked="editForm.content.show_runway !== false" @change="editForm.content.show_runway = $event.target.checked" class="rounded border-[#D8CCBC]" />
              <span>এর নিচে "রানওয়ে লুকবুক" (স্ক্রল করলে পাশে সরে যাওয়া পণ্যের সারি) দেখান</span>
            </label>
            <div v-if="editForm.content.image !== undefined" class="col-span-12">
              <label class="block font-semibold uppercase tracking-wider mb-1">Section Photograph</label>
              <MediaPicker v-model="editForm.content.image" />
            </div>
          </template>

          <div class="col-span-12 pt-2 border-t border-[#D8CCBC]/40">
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" v-model="editForm.is_active" class="rounded border-[#D8CCBC]" />
              <span>Visible on Live Storefront</span>
            </label>
          </div>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-[#D8CCBC]/40">
          <button type="button" @click="editModalOpen = false" class="px-4 py-2 border border-[#D8CCBC]">Cancel</button>
          <button type="submit" :disabled="saving" class="px-6 py-2 bg-[#111111] text-white uppercase tracking-wider font-semibold hover:bg-black">
            {{ saving ? 'Saving Changes...' : 'Save & Publish Live' }}
          </button>
        </div>
      </form>
    </BaseModal>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import api from '../../api';
import BaseModal from '../../components/common/BaseModal.vue';
import MediaPicker from '../../components/admin/MediaPicker.vue';

const sections = ref([]);
const loading = ref(true);
const editModalOpen = ref(false);
const editingSection = ref(null);
const saving = ref(false);

const editForm = reactive({
  title: '',
  subtitle: '',
  is_active: true,
  content: {},
});

// Announcement, header and footer are site-wide chrome, managed in সাইট কাস্টমাইজ.
const CHROME = ['announcement', 'header', 'footer'];
const pageSections = (list) => list.filter((s) => !CHROME.includes(s.type));

const fetchSections = async () => {
  loading.value = true;
  try {
    const res = await api.get('/admin/homepage-builder');
    sections.value = pageSections(res.data.data || []);
  } catch (err) {
    console.error('Failed to load sections', err);
  } finally {
    loading.value = false;
  }
};

const moveSection = async (index, direction) => {
  const targetIndex = index + direction;
  if (targetIndex < 0 || targetIndex >= sections.value.length) return;

  const reordered = [...sections.value];
  const [removed] = reordered.splice(index, 1);
  reordered.splice(targetIndex, 0, removed);

  const payload = reordered.map((sec, i) => ({
    id: sec.id,
    display_order: i + 1,
  }));

  try {
    const res = await api.post('/admin/homepage-builder/reorder', { order: payload });
    sections.value = pageSections(res.data.data);
  } catch (err) {
    alert('Reordering failed');
  }
};

const toggleActive = async (section) => {
  try {
    const updated = !section.is_active;
    await api.put(`/admin/homepage-builder/${section.id}`, { is_active: updated });
    section.is_active = updated;
  } catch (err) {
    alert('Could not update status');
  }
};

const openEditModal = (section) => {
  editingSection.value = section;
  editForm.title = section.title;
  editForm.subtitle = section.subtitle || '';
  editForm.is_active = Boolean(section.is_active);
  editForm.content = JSON.parse(JSON.stringify(section.content || {}));
  if (section.type === 'category_showcase') {
    if (!Array.isArray(editForm.content.categories)) editForm.content.categories = [];
    editForm.content.products_per_category ||= 3;
    loadCategories();
  }
  editModalOpen.value = true;
};

// --- category showcase helpers
const allCategories = ref([]);
const loadCategories = async () => {
  if (allCategories.value.length) return;
  try {
    const res = await api.get('/public/categories');
    allCategories.value = (res.data.data || []).flatMap((p) => [
      { slug: p.slug, name: p.name, is_child: false },
      ...(p.children || []).map((c) => ({ slug: c.slug, name: c.name, is_child: true })),
    ]);
  } catch { /* list stays empty */ }
};
const showcaseCats = computed(() => editForm.content.categories || []);
const categoryName = (slug) => allCategories.value.find((c) => c.slug === slug)?.name || slug;
const moveShowcase = (i, d) => {
  const list = editForm.content.categories;
  const [x] = list.splice(i, 1);
  list.splice(i + d, 0, x);
};

const saveSectionChanges = async () => {
  if (!editingSection.value) return;
  saving.value = true;
  try {
    await api.put(`/admin/homepage-builder/${editingSection.value.id}`, editForm);
    editModalOpen.value = false;
    fetchSections();
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to save section');
  } finally {
    saving.value = false;
  }
};

onMounted(() => {
  fetchSections();
});
</script>
