<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <span class="text-xs uppercase tracking-[0.25em] text-[#A89B8A] font-medium block">Asset Management</span>
        <h1 class="font-serif text-3xl text-[#111111]">Centralized Media Library</h1>
      </div>

      <div class="flex items-center gap-3">
        <label class="px-5 py-2.5 bg-[#111111] hover:bg-black text-white text-xs uppercase tracking-wider font-semibold cursor-pointer transition-colors shadow-xs inline-flex items-center gap-2">
          <span>{{ uploading ? uploadStatusText : '+ Upload Multiple Assets' }}</span>
          <input 
            type="file" 
            ref="fileInputRef"
            multiple 
            @change="handleUpload" 
            class="hidden" 
            accept="image/*,.pdf,.doc,.docx" 
          />
        </label>
      </div>
    </div>

    <!-- Drag & Drop Upload Zone -->
    <div
      @dragover.prevent="isDragging = true"
      @dragleave.prevent="isDragging = false"
      @drop.prevent="handleDrop"
      :class="[
        'p-6 border-2 border-dashed transition-all text-center rounded-xs cursor-pointer',
        isDragging
          ? 'border-[#111111] bg-[#F7F5F0]'
          : 'border-[#D8CCBC] bg-white hover:border-[#111111]/70'
      ]"
      @click="triggerFileInput"
    >
      <div class="max-w-md mx-auto space-y-2 pointer-events-none">
        <div class="w-10 h-10 mx-auto rounded-full bg-[#F7F5F0] flex items-center justify-center text-[#111111]">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
          </svg>
        </div>
        <h4 class="font-serif text-sm text-[#111111]">
          Drag & Drop multiple images here, or <span class="underline">browse files</span>
        </h4>
        <p class="text-[11px] text-[#A89B8A]">
          Supports batch upload of multiple images (.jpg, .png, .webp) up to 50MB per file
        </p>
        <div v-if="uploading" class="pt-2 text-xs font-mono font-medium text-emerald-700 animate-pulse">
          {{ uploadStatusText }}
        </div>
      </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="p-4 bg-white border border-[#D8CCBC]/40 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-4">
      <input
        type="text"
        v-model="search"
        @input="debounceSearch"
        placeholder="Search media files by filename or alt text..."
        class="w-full sm:w-80 px-4 py-2 bg-[#F7F5F0] border border-[#D8CCBC]/60 text-xs focus:outline-none focus:border-[#111111]"
      />

      <span class="text-xs text-[#A89B8A] font-mono">{{ files.length }} assets loaded</span>
    </div>

    <!-- Media Grid -->
    <div v-if="loading" class="py-24 text-center text-xs text-[#A89B8A]">
      Loading media gallery...
    </div>

    <div v-else-if="files.length" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
      <div
        v-for="file in files"
        :key="file.id"
        class="group bg-white border border-[#D8CCBC]/40 shadow-2xs overflow-hidden flex flex-col justify-between"
      >
        <div class="aspect-square bg-[#F7F5F0] relative overflow-hidden">
          <img 
            :src="file.url" 
            :alt="file.name" 
            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" 
            loading="lazy"
          />
          <button
            type="button"
            @click="copyUrl(file.url)"
            class="absolute top-2 right-2 p-1.5 bg-white/95 text-[#111111] text-[10px] uppercase font-semibold opacity-0 group-hover:opacity-100 transition-opacity hover:bg-black hover:text-white"
            title="Copy URL"
          >
            Copy URL
          </button>
        </div>

        <div class="p-3 text-xs space-y-1">
          <p class="font-serif text-xs truncate text-[#111111] font-medium" :title="file.file_name">
            {{ file.name || file.file_name }}
          </p>
          <div class="flex justify-between items-center text-[10px] text-[#A89B8A] pt-1">
            <span>{{ formatSize(file.size) }}</span>
            <div class="flex items-center gap-2">
              <button
                v-if="file.mime_type && file.mime_type.startsWith('image/')"
                type="button"
                @click="optimizeFile(file)"
                class="text-emerald-700 hover:text-emerald-900 font-medium transition-colors"
                title="Optimize to WebP format"
              >
                WebP
              </button>
              <button
                type="button"
                @click="deleteFile(file.id)"
                class="text-rose-600 hover:text-rose-800 font-medium transition-colors"
              >
                Delete
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="bg-white p-16 text-center border border-[#D8CCBC]/40">
      <h5 class="font-serif text-xl text-[#111111] mb-2">No media assets found</h5>
      <p class="text-xs text-[#A89B8A]">Upload campaign photography, product silhouettes, or lookbook items.</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../../api';
import { useToastStore } from '../../stores/toast';

const toast = useToastStore();
const files = ref([]);
const loading = ref(true);
const uploading = ref(false);
const uploadStatusText = ref('Uploading...');
const isDragging = ref(false);
const search = ref('');
const fileInputRef = ref(null);
let searchTimer = null;

const formatSize = (bytes) => {
  if (!bytes) return '0 KB';
  const kb = bytes / 1024;
  if (kb < 1024) return `${kb.toFixed(1)} KB`;
  return `${(kb / 1024).toFixed(1)} MB`;
};

const fetchFiles = async () => {
  loading.value = true;
  try {
    const params = { search: search.value || undefined, per_page: 60 };
    const res = await api.get('/admin/media', { params });
    files.value = res.data.data || [];
  } catch (err) {
    console.error('Failed to load media files', err);
  } finally {
    loading.value = false;
  }
};

const debounceSearch = () => {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(() => {
    fetchFiles();
  }, 300);
};

const triggerFileInput = () => {
  if (fileInputRef.value) {
    fileInputRef.value.click();
  }
};

const handleDrop = async (event) => {
  isDragging.value = false;
  const droppedFiles = event.dataTransfer?.files;
  if (droppedFiles && droppedFiles.length > 0) {
    await uploadFileList(Array.from(droppedFiles));
  }
};

const handleUpload = async (event) => {
  const selectedFiles = Array.from(event.target.files || []);
  if (selectedFiles.length === 0) return;
  await uploadFileList(selectedFiles);
  event.target.value = ''; // Reset input
};

const uploadFileList = async (fileList) => {
  uploading.value = true;
  uploadStatusText.value = `Uploading ${fileList.length} asset(s)...`;

  try {
    const formData = new FormData();
    fileList.forEach((file) => {
      formData.append('files[]', file);
    });

    const res = await api.post('/admin/media/upload', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });

    const responseData = res.data.data;
    if (Array.isArray(responseData)) {
      files.value.unshift(...responseData);
    } else if (responseData) {
      files.value.unshift(responseData);
    }
    toast.success(`Successfully uploaded ${fileList.length} asset(s)`);
  } catch (err) {
    toast.error(err.response?.data?.message || 'Bulk upload failed. Ensure images are under 50MB.');
  } finally {
    uploading.value = false;
  }
};

const copyUrl = (url) => {
  navigator.clipboard.writeText(url);
  toast.success('Asset URL copied to clipboard');
};

const optimizeFile = async (file) => {
  try {
    const res = await api.post(`/admin/media/${file.id}/optimize`, { width: 1200 });
    if (res.data.data?.optimized_url) {
      navigator.clipboard.writeText(res.data.data.optimized_url);
      toast.success('Optimized to WebP! WebP URL copied to clipboard');
    } else {
      toast.success('Optimized successfully to WebP');
    }
  } catch (err) {
    toast.error(err.response?.data?.message || 'Failed to optimize image');
  }
};

const deleteFile = async (id) => {
  if (!confirm('Are you sure you want to permanently delete this media file?')) return;
  try {
    await api.delete(`/admin/media/${id}`);
    files.value = files.value.filter(f => f.id !== id);
    toast.success('Media file deleted');
  } catch (err) {
    toast.error('Delete failed');
  }
};

onMounted(() => {
  fetchFiles();
});
</script>
