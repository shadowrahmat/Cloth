<template>
  <div v-if="config" class="max-w-4xl mx-auto space-y-8">
    <!-- Header -->
    <div class="border-b border-[#D8CCBC]/40 pb-6 flex items-center justify-between">
      <div>
        <router-link
          :to="`/admin/resources/${resourceKey}`"
          class="text-xs text-[#A89B8A] hover:text-[#111111] transition-colors mb-1 block"
        >
          ← Back to {{ config.label }}
        </router-link>
        <h1 class="font-serif text-3xl text-[#111111]">
          {{ isEdit ? `Edit ${config.singular}` : `Create New ${config.singular}` }}
        </h1>
      </div>
    </div>

    <!-- Error Banner -->
    <div v-if="errorMessage" class="p-4 bg-rose-50 border border-rose-200 text-rose-800 text-xs font-medium">
      {{ errorMessage }}
    </div>

    <!-- Dynamic Form Card -->
    <div class="bg-white p-6 sm:p-10 border border-[#D8CCBC]/40 shadow-xs">
      <DynamicForm
        :fields="config.fields"
        :initial-data="initialData"
        :loading="submitting"
        @submit="handleSubmit"
        @cancel="router.push(`/admin/resources/${resourceKey}`)"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { getResourceConfig } from '../../admin-resources';
import api from '../../api';
import DynamicForm from '../../components/admin/DynamicForm.vue';

const route = useRoute();
const router = useRouter();

const resourceKey = computed(() => route.params.resource);
const config = computed(() => getResourceConfig(resourceKey.value));
const isEdit = computed(() => !!route.params.id);

const initialData = ref({});
const submitting = ref(false);
const errorMessage = ref('');

const loadRecord = async () => {
  if (!isEdit.value || !config.value) return;
  try {
    const res = await api.get(`${config.value.endpoint}/${route.params.id}`);
    initialData.value = res.data.data || {};
  } catch (err) {
    errorMessage.value = 'Failed to load record details.';
  }
};

const handleSubmit = async (formData) => {
  submitting.value = true;
  errorMessage.value = '';
  try {
    if (isEdit.value) {
      await api.put(`${config.value.endpoint}/${route.params.id}`, formData);
    } else {
      await api.post(config.value.endpoint, formData);
    }
    router.push(`/admin/resources/${resourceKey.value}`);
  } catch (err) {
    if (err.response?.data?.errors) {
      const errs = err.response.data.errors;
      errorMessage.value = Object.values(errs).flat().join(' ');
    } else {
      errorMessage.value = err.response?.data?.message || 'Error saving record.';
    }
  } finally {
    submitting.value = false;
  }
};

onMounted(() => {
  loadRecord();
});
</script>
