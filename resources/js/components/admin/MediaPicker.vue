<template>
  <div>
    <!-- Input Trigger with Preview -->
    <div class="flex items-center gap-4">
      <div
        v-if="modelValue"
        class="relative w-20 h-24 bg-[#F7F5F0] border border-[#D8CCBC] overflow-hidden group shrink-0"
      >
        <img :src="modelValue" alt="Selected media" class="w-full h-full object-cover" />
        <button
          type="button"
          @click="$emit('update:modelValue', '')"
          class="absolute inset-0 bg-black/60 text-white opacity-0 group-hover:opacity-100 flex items-center justify-center text-xs transition-opacity"
        >
          Remove
        </button>
      </div>

      <div class="flex-1 space-y-2">
        <div class="flex gap-2">
          <input
            type="text"
            :value="modelValue"
            @input="$emit('update:modelValue', $event.target.value)"
            placeholder="https://... or choose from media library"
            class="flex-1 px-3 py-2 bg-white border border-[#D8CCBC] text-xs text-[#111111] focus:outline-none focus:border-[#111111]"
          />
          <button
            type="button"
            @click="modalOpen = true; loadMedia()"
            class="px-4 py-2 bg-[#111111] hover:bg-black text-white text-xs uppercase tracking-wider font-medium transition-colors shrink-0"
          >
            Library
          </button>
        </div>
      </div>
    </div>

    <!-- Media Library Modal -->
    <BaseModal
      :is-open="modalOpen"
      title="Select or Upload Media"
      max-width="3xl"
      @close="modalOpen = false"
    >
      <div class="space-y-4">
        <!-- Upload Row -->
        <div class="p-4 bg-[#F7F5F0] border border-dashed border-[#D8CCBC] flex items-center justify-between">
          <div>
            <h5 class="text-xs font-semibold text-[#111111]">Upload Media Files (Single or Multiple)</h5>
            <p class="text-[11px] text-[#A89B8A]">Batch select multiple images up to 50MB (.jpg, .png, .webp)</p>
          </div>
          <label class="px-4 py-2 bg-[#111111] hover:bg-black text-white text-xs uppercase tracking-wider font-semibold cursor-pointer transition-colors">
            <span>{{ uploading ? 'Uploading...' : 'Browse Computer' }}</span>
            <input type="file" multiple @change="handleFileUpload" class="hidden" accept="image/*" />
          </label>
        </div>

        <!-- Media Grid -->
        <div v-if="loading" class="py-12 text-center text-xs text-[#A89B8A]">
          Loading media library...
        </div>

        <div v-else-if="files.length" class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-3 max-h-96 overflow-y-auto pr-1">
          <div
            v-for="file in files"
            :key="file.id"
            @click="selectMedia(file)"
            class="group relative aspect-square bg-[#F7F5F0] border border-[#D8CCBC]/40 overflow-hidden cursor-pointer hover:border-[#111111] transition-all"
          >
            <img :src="file.url" :alt="file.name" class="w-full h-full object-cover" />
            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center p-1 text-center text-white text-[10px] font-medium">
              Select
            </div>
          </div>
        </div>

        <div v-else class="py-12 text-center text-xs text-[#A89B8A]">
          No media files uploaded yet.
        </div>
      </div>
    </BaseModal>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import api from '../../api';
import BaseModal from '../common/BaseModal.vue';

const props = defineProps({
  modelValue: { type: String, default: '' },
});

const emit = defineEmits(['update:modelValue']);

const modalOpen = ref(false);
const files = ref([]);
const loading = ref(false);
const uploading = ref(false);

const loadMedia = async () => {
  loading.value = true;
  try {
    const res = await api.get('/admin/media?per_page=60');
    files.value = res.data.data || [];
  } catch (err) {
    console.error('Failed to load media', err);
  } finally {
    loading.value = false;
  }
};

const handleFileUpload = async (event) => {
  const selectedFiles = Array.from(event.target.files || []);
  if (selectedFiles.length === 0) return;

  const formData = new FormData();
  selectedFiles.forEach((file) => {
    formData.append('files[]', file);
  });
  uploading.value = true;

  try {
    const res = await api.post('/admin/media/upload', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    const responseData = res.data.data;
    if (Array.isArray(responseData)) {
      files.value.unshift(...responseData);
      if (responseData.length > 0) {
        selectMedia(responseData[0]);
      }
    } else if (responseData) {
      files.value.unshift(responseData);
      selectMedia(responseData);
    }
  } catch (err) {
    alert(err.response?.data?.message || 'Upload failed');
  } finally {
    uploading.value = false;
    event.target.value = '';
  }
};

const selectMedia = (file) => {
  emit('update:modelValue', file.url);
  modalOpen.value = false;
};
</script>
