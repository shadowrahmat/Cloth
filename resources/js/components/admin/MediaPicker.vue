<template>
  <div>
    <!-- Several images at once (product gallery): a tile that opens the library -->
    <button
      v-if="multiple"
      type="button"
      @click="open"
      class="w-full h-full min-h-[96px] flex flex-col items-center justify-center gap-1 rounded-lg border-2 border-dashed border-[#D5DCD8] text-[#69736E] hover:border-[#16A36A] hover:text-[#14382A] transition-colors text-xs font-semibold"
    >
      <ImagePlus :size="20" :stroke-width="1.75" />
      <span>{{ label || 'ছবি যোগ করুন' }}</span>
    </button>

    <!-- One image: preview + URL field + library button -->
    <div v-else class="flex items-center gap-3">
      <div v-if="modelValue" class="relative w-16 h-20 rounded-md bg-[#F7F5F0] border border-[#E7EBE8] overflow-hidden group shrink-0">
        <img :src="modelValue" alt="" class="w-full h-full object-cover" />
        <button
          type="button"
          @click="$emit('update:modelValue', '')"
          class="absolute inset-0 bg-black/55 text-white opacity-0 group-hover:opacity-100 focus:opacity-100 flex items-center justify-center text-[11px] font-semibold transition-opacity"
        >
          সরান
        </button>
      </div>
      <div class="flex-1 flex gap-2 min-w-0">
        <input
          type="text"
          :value="modelValue"
          @input="$emit('update:modelValue', $event.target.value)"
          placeholder="ছবির লিংক, অথবা লাইব্রেরি থেকে বাছুন"
          class="flex-1 min-w-0 px-3 py-2 bg-white border border-[#D5DCD8] rounded-[9px] text-xs text-[#171C1A] focus:outline-none focus:border-[#16A36A]"
        />
        <button
          type="button"
          @click="open"
          class="px-3.5 py-2 rounded-[9px] bg-[#14382A] hover:bg-[#16A36A] text-white text-xs font-semibold transition-colors shrink-0 inline-flex items-center gap-1.5"
        >
          <Images :size="14" /> লাইব্রেরি
        </button>
      </div>
    </div>

    <!-- Library -->
    <BaseModal :is-open="modalOpen" :title="multiple ? 'ছবি বাছাই করুন (একাধিক)' : 'ছবি বাছাই করুন'" max-width="3xl" @close="modalOpen = false">
      <div class="space-y-4">
        <div class="p-4 rounded-lg bg-[#F7F8FA] border border-dashed border-[#D5DCD8] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
          <div>
            <h5 class="text-xs font-semibold text-[#171C1A]">কম্পিউটার থেকে আপলোড</h5>
            <p class="text-[11px] text-[#69736E]">একসাথে অনেকগুলো বাছাই করা যায় (.jpg, .png, .webp)। আপলোড হলেই বাছাই হয়ে যাবে।</p>
          </div>
          <label class="px-4 py-2 rounded-md bg-[#14382A] hover:bg-[#16A36A] text-white text-xs font-semibold cursor-pointer transition-colors inline-flex items-center gap-1.5 shrink-0">
            <Upload :size="14" />
            <span>{{ uploading ? 'আপলোড হচ্ছে…' : 'ফাইল বাছুন' }}</span>
            <input type="file" multiple @change="handleFileUpload" class="hidden" accept="image/*" />
          </label>
        </div>

        <div v-if="loading" class="py-12 text-center text-xs text-[#69736E]">লাইব্রেরি লোড হচ্ছে…</div>

        <div v-else-if="files.length" class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-2.5 max-h-96 overflow-y-auto pr-1">
          <button
            v-for="file in files"
            :key="file.id"
            type="button"
            @click="clickFile(file)"
            :class="[
              'group relative aspect-square rounded-md bg-[#F7F5F0] overflow-hidden border-2 transition-all',
              picked.includes(file.url) ? 'border-[#16A36A] ring-2 ring-[#16A36A]/25' : 'border-transparent hover:border-[#171C1A]',
            ]"
          >
            <img :src="file.url" :alt="file.name" class="w-full h-full object-cover" loading="lazy" />
            <span
              v-if="multiple && picked.includes(file.url)"
              class="absolute top-1 right-1 min-w-5 h-5 px-1 rounded-full bg-[#16A36A] text-white text-[10px] font-bold flex items-center justify-center"
            >{{ picked.indexOf(file.url) + 1 }}</span>
          </button>
        </div>

        <div v-else class="py-12 text-center text-xs text-[#69736E]">লাইব্রেরিতে এখনো কোনো ছবি নেই। ওপরে আপলোড করুন।</div>
      </div>

      <template v-if="multiple" #footer>
        <button type="button" class="px-4 py-2 text-xs font-semibold border border-[#D5DCD8] rounded-md bg-white" @click="modalOpen = false">বাতিল</button>
        <button
          type="button"
          class="px-4 py-2 text-xs font-semibold rounded-md bg-[#14382A] text-white hover:bg-[#16A36A] disabled:opacity-40"
          :disabled="!picked.length"
          @click="confirmPick"
        >
          {{ picked.length ? `${picked.length}টি ছবি যোগ করুন` : 'ছবি বাছাই করুন' }}
        </button>
      </template>
    </BaseModal>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { ImagePlus, Images, Upload } from 'lucide-vue-next';
import api from '../../api';
import BaseModal from '../common/BaseModal.vue';

const props = defineProps({
  modelValue: { type: String, default: '' },
  /** Pick several images; emits `pick` with an array of URLs instead of v-model. */
  multiple: { type: Boolean, default: false },
  label: { type: String, default: '' },
});

const emit = defineEmits(['update:modelValue', 'pick']);

const modalOpen = ref(false);
const files = ref([]);
const loading = ref(false);
const uploading = ref(false);
const picked = ref([]);

const open = () => {
  picked.value = [];
  modalOpen.value = true;
  loadMedia();
};

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
  if (!selectedFiles.length) return;

  const formData = new FormData();
  selectedFiles.forEach((file) => formData.append('files[]', file));
  uploading.value = true;

  try {
    const res = await api.post('/admin/media/upload', formData, { headers: { 'Content-Type': 'multipart/form-data' } });
    const uploaded = [].concat(res.data.data || []);
    files.value.unshift(...uploaded);
    if (props.multiple) {
      uploaded.forEach((f) => !picked.value.includes(f.url) && picked.value.push(f.url));
    } else if (uploaded[0]) {
      clickFile(uploaded[0]);
    }
  } catch (err) {
    alert(err.response?.data?.message || 'আপলোড হয়নি');
  } finally {
    uploading.value = false;
    event.target.value = '';
  }
};

const clickFile = (file) => {
  if (props.multiple) {
    const i = picked.value.indexOf(file.url);
    i === -1 ? picked.value.push(file.url) : picked.value.splice(i, 1);
    return;
  }
  emit('update:modelValue', file.url);
  modalOpen.value = false;
};

const confirmPick = () => {
  emit('pick', [...picked.value]);
  modalOpen.value = false;
};

defineExpose({ open });
</script>
