<template>
  <form @submit.prevent="$emit('submit', formData)" class="space-y-6">
    <div class="grid grid-cols-12 gap-6">
      <div
        v-for="field in fields"
        :key="field.name"
        :class="`col-span-12 sm:col-span-${field.colSpan || 12}`"
      >
        <label class="block text-xs uppercase tracking-wider font-semibold text-[#111111] mb-2">
          {{ field.label }}
          <span v-if="field.required" class="text-rose-500">*</span>
        </label>

        <!-- Text / Email / URL Input -->
        <input
          v-if="['text', 'email', 'url', 'slug'].includes(field.type)"
          :type="field.type === 'email' ? 'email' : (field.type === 'url' ? 'url' : 'text')"
          v-model="formData[field.name]"
          :required="field.required"
          :placeholder="field.placeholder || ''"
          class="w-full px-4 py-2.5 bg-white border border-[#D8CCBC] text-xs text-[#111111] focus:outline-none focus:border-[#111111]"
        />

        <!-- Number Input -->
        <input
          v-else-if="field.type === 'number'"
          type="number"
          step="any"
          v-model.number="formData[field.name]"
          :required="field.required"
          class="w-full px-4 py-2.5 bg-white border border-[#D8CCBC] text-xs text-[#111111] focus:outline-none focus:border-[#111111]"
        />

        <!-- Date / Datetime Input -->
        <input
          v-else-if="['date', 'datetime'].includes(field.type)"
          :type="field.type === 'datetime' ? 'datetime-local' : 'date'"
          v-model="formData[field.name]"
          :required="field.required"
          class="w-full px-4 py-2.5 bg-white border border-[#D8CCBC] text-xs text-[#111111] focus:outline-none focus:border-[#111111]"
        />

        <!-- Textarea -->
        <textarea
          v-else-if="field.type === 'textarea'"
          rows="3"
          v-model="formData[field.name]"
          :required="field.required"
          class="w-full px-4 py-2.5 bg-white border border-[#D8CCBC] text-xs text-[#111111] focus:outline-none focus:border-[#111111]"
        />

        <!-- Longtext / Content -->
        <textarea
          v-else-if="field.type === 'longtext'"
          rows="8"
          v-model="formData[field.name]"
          :required="field.required"
          placeholder="Enter text, markdown, or editorial layout..."
          class="w-full px-4 py-2.5 bg-white border border-[#D8CCBC] text-xs text-[#111111] font-mono focus:outline-none focus:border-[#111111]"
        />

        <!-- Select Dropdown -->
        <select
          v-else-if="field.type === 'select'"
          v-model="formData[field.name]"
          :required="field.required"
          class="w-full px-4 py-2.5 bg-white border border-[#D8CCBC] text-xs text-[#111111] focus:outline-none focus:border-[#111111]"
        >
          <option :value="null">-- Select {{ field.label }} --</option>
          <option
            v-for="opt in field.options"
            :key="opt.value !== undefined ? opt.value : opt"
            :value="opt.value !== undefined ? opt.value : opt"
          >
            {{ opt.label || opt }}
          </option>
        </select>

        <!-- Relation Dropdown -->
        <select
          v-else-if="field.type === 'relation'"
          v-model="formData[field.name]"
          class="w-full px-4 py-2.5 bg-white border border-[#D8CCBC] text-xs text-[#111111] focus:outline-none focus:border-[#111111]"
        >
          <option :value="null">-- None (Top Level) --</option>
          <option
            v-for="opt in relationOptions[field.name] || []"
            :key="opt[field.valueKey || 'id']"
            :value="opt[field.valueKey || 'id']"
          >
            {{ opt[field.labelKey || 'name'] }}
          </option>
        </select>

        <!-- Media Picker -->
        <MediaPicker
          v-else-if="field.type === 'media'"
          v-model="formData[field.name]"
        />

        <!-- Boolean Checkbox Switch -->
        <label v-else-if="field.type === 'boolean'" class="flex items-center gap-3 cursor-pointer pt-2">
          <input
            type="checkbox"
            v-model="formData[field.name]"
            class="w-4 h-4 text-[#111111] rounded border-[#D8CCBC] focus:ring-black"
          />
          <span class="text-xs text-[#111111]">{{ field.label }}</span>
        </label>
      </div>
    </div>

    <!-- Actions -->
    <div class="flex items-center justify-end gap-3 pt-6 border-t border-[#D8CCBC]/40">
      <button
        type="button"
        @click="$emit('cancel')"
        class="px-6 py-2.5 border border-[#D8CCBC] text-xs uppercase tracking-wider font-medium text-[#111111] hover:bg-[#F7F5F0] transition-colors"
      >
        Cancel
      </button>
      <button
        type="submit"
        :disabled="loading"
        class="px-8 py-2.5 bg-[#111111] hover:bg-black text-white text-xs uppercase tracking-[0.15em] font-semibold transition-colors disabled:opacity-50"
      >
        {{ loading ? 'Saving...' : 'Save Record' }}
      </button>
    </div>
  </form>
</template>

<script setup>
import { ref, reactive, watch, onMounted } from 'vue';
import api from '../../api';
import MediaPicker from './MediaPicker.vue';

const props = defineProps({
  fields: { type: Array, required: true },
  initialData: { type: Object, default: () => ({}) },
  loading: { type: Boolean, default: false },
});

const emit = defineEmits(['submit', 'cancel']);

const formData = reactive({});
const relationOptions = reactive({});

// Populate default/initial values
const initForm = () => {
  props.fields.forEach(f => {
    if (props.initialData && props.initialData[f.name] !== undefined) {
      formData[f.name] = props.initialData[f.name];
    } else if (f.defaultValue !== undefined) {
      formData[f.name] = f.defaultValue;
    } else if (f.type === 'boolean') {
      formData[f.name] = false;
    } else {
      formData[f.name] = null;
    }
  });
};

watch(() => props.initialData, () => initForm(), { deep: true });

onMounted(async () => {
  initForm();

  // Load relation dropdown data
  for (const f of props.fields) {
    if (f.type === 'relation' && f.endpoint) {
      try {
        const res = await api.get(f.endpoint);
        relationOptions[f.name] = res.data.data || [];
      } catch (err) {
        console.error('Failed to load relation options for ' + f.name, err);
      }
    }
  }
});
</script>
