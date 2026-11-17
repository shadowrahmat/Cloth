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
          <template v-if="editingSection.type === 'hero'">
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
import { ref, reactive, onMounted } from 'vue';
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

const fetchSections = async () => {
  loading.value = true;
  try {
    const res = await api.get('/admin/homepage-builder');
    sections.value = res.data.data || [];
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
    sections.value = res.data.data;
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
  editModalOpen.value = true;
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
