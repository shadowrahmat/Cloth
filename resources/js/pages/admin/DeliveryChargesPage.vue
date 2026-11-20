<template>
  <div class="space-y-6 max-w-4xl">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 pb-4 border-b border-[#E7EBE8]">
      <div>
        <h2 class="text-xl font-bold tracking-tight text-[#171C1A]">ডেলিভারি চার্জ</h2>
        <p class="text-xs text-[#69736E] mt-0.5">
          চেকআউট, কার্ট আর প্রোডাক্ট পেজে এই চার্জ, সময় আর ফ্রি ডেলিভারির সীমাই দেখানো হয়। সবচেয়ে কম চার্জেরটা আগে বাছাই থাকে।
        </p>
      </div>
      <router-link to="/admin/appearance?tab=announcement" class="text-xs font-semibold text-[#14382A] hover:underline shrink-0">
        ঘোষণা বারের লেখা বদলান →
      </router-link>
    </div>

    <div v-if="loading" class="grid gap-4">
      <div v-for="n in 2" :key="n" class="h-44 rounded-xl bg-white border border-[#E7EBE8] animate-pulse"></div>
    </div>

    <p v-else-if="loadError" class="p-4 rounded-lg bg-rose-50 text-rose-700 text-sm">{{ loadError }}</p>

    <template v-else>
      <!-- Active methods -->
      <form
        v-for="m in activeMethods"
        :key="m.id"
        class="bg-white rounded-xl border border-[#E7EBE8] shadow-xs p-5 space-y-4"
        @submit.prevent="save(m)"
      >
        <div class="flex items-center justify-between gap-3">
          <div class="flex items-center gap-3 min-w-0">
            <span class="w-10 h-10 rounded-lg bg-[#EEF6F1] text-[#14382A] flex items-center justify-center shrink-0">
              <Truck :size="20" :stroke-width="1.75" />
            </span>
            <div class="min-w-0">
              <p class="font-semibold text-[#171C1A] truncate">{{ m.name || 'নাম নেই' }}</p>
              <p class="text-[11px] text-[#69736E] font-mono">{{ m.code }}</p>
            </div>
          </div>
          <label class="inline-flex items-center gap-2 text-xs text-[#69736E] cursor-pointer select-none">
            <input type="checkbox" v-model="m.is_active" class="w-4 h-4 accent-[#14382A]" />
            চালু
          </label>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
          <label class="block">
            <span class="block text-xs font-semibold text-[#171C1A] mb-1">এরিয়ার নাম (গ্রাহক যা দেখবে)</span>
            <input v-model.trim="m.name" required maxlength="255" class="admin-input" placeholder="যেমন: ঢাকার ভেতরে" />
          </label>
          <label class="block">
            <span class="block text-xs font-semibold text-[#171C1A] mb-1">ডেলিভারি চার্জ (৳)</span>
            <input v-model.number="m.rate" type="number" min="0" step="1" required class="admin-input" />
          </label>
          <label class="block">
            <span class="block text-xs font-semibold text-[#171C1A] mb-1">ডেলিভারির সময়</span>
            <input v-model.trim="m.estimated_delivery" maxlength="100" class="admin-input" placeholder="যেমন: ১–২ দিন" />
          </label>
          <label class="block">
            <span class="block text-xs font-semibold text-[#171C1A] mb-1">ফ্রি ডেলিভারি, এর বেশি অর্ডারে (৳)</span>
            <input v-model.number="m.free_threshold" type="number" min="0" step="1" class="admin-input" placeholder="খালি বা ০ = ফ্রি ডেলিভারি নেই" />
          </label>
        </div>

        <div class="flex items-center justify-between gap-3 pt-1">
          <p class="text-xs text-[#69736E]">
            গ্রাহক দেখবে: <strong class="text-[#171C1A]">{{ m.name }} · ৳{{ Number(m.rate || 0) }} · {{ m.estimated_delivery || '—' }}</strong>
            <span v-if="Number(m.free_threshold) > 0"> · ৳{{ Number(m.free_threshold) }}+ অর্ডারে ফ্রি</span>
          </p>
          <button
            type="submit"
            :disabled="saving[m.id] || !dirty(m)"
            class="px-4 py-2 rounded-md bg-[#14382A] text-white text-xs font-semibold hover:bg-[#16A36A] transition-colors disabled:opacity-40 disabled:cursor-not-allowed shrink-0"
          >
            {{ saving[m.id] ? 'সেভ হচ্ছে…' : 'সেভ করুন' }}
          </button>
        </div>
      </form>

      <p v-if="!activeMethods.length" class="p-4 rounded-lg bg-amber-50 text-amber-800 text-sm">
        কোনো ডেলিভারি পদ্ধতি চালু নেই — এভাবে থাকলে গ্রাহক অর্ডার করতে পারবে না। নিচ থেকে অন্তত একটি চালু করুন।
      </p>

      <!-- Turned-off methods -->
      <details v-if="inactiveMethods.length" class="bg-white rounded-xl border border-[#E7EBE8]">
        <summary class="px-5 py-3 text-xs font-semibold text-[#69736E] cursor-pointer select-none">
          বন্ধ করা পদ্ধতি ({{ inactiveMethods.length }})
        </summary>
        <ul class="divide-y divide-[#E7EBE8] border-t border-[#E7EBE8]">
          <li v-for="m in inactiveMethods" :key="m.id" class="px-5 py-3 flex items-center justify-between gap-3 text-sm">
            <span class="min-w-0 truncate">{{ m.name }} <span class="text-[11px] text-[#69736E]">· ৳{{ Number(m.rate) }}</span></span>
            <button type="button" class="text-xs font-semibold text-[#14382A] hover:underline shrink-0" :disabled="saving[m.id]" @click="m.is_active = true; save(m)">
              চালু করুন
            </button>
          </li>
        </ul>
      </details>
    </template>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { Truck } from 'lucide-vue-next';
import api from '../../api';
import { useToastStore } from '../../stores/toast';

const toast = useToastStore();
const methods = ref([]);
const saved = reactive({});      // last saved copy per id, to know what changed
const saving = reactive({});
const loading = ref(true);
const loadError = ref('');

const FIELDS = ['name', 'rate', 'estimated_delivery', 'free_threshold', 'is_active'];
const snapshot = (m) => JSON.stringify(FIELDS.map((f) => (f === 'rate' || f === 'free_threshold' ? Number(m[f] || 0) : m[f] ?? '')));
const dirty = (m) => saved[m.id] !== snapshot(m);

// "Active" is decided by the saved state, so a card doesn't jump away while you untick it.
const isSavedActive = (m) => JSON.parse(saved[m.id] || '[]')[4] !== false;
const activeMethods = computed(() => methods.value.filter(isSavedActive));
const inactiveMethods = computed(() => methods.value.filter((m) => !isSavedActive(m)));

async function load() {
  loading.value = true;
  loadError.value = '';
  try {
    const res = await api.get('/admin/resources/shipping-methods', { params: { per_page: 50 } });
    methods.value = (res.data.data || []).map((m) => ({
      ...m,
      rate: Number(m.rate),
      free_threshold: m.free_threshold === null ? null : Number(m.free_threshold),
      is_active: !!m.is_active,
    }));
    methods.value.forEach((m) => { saved[m.id] = snapshot(m); });
  } catch (err) {
    loadError.value = err.response?.data?.message || 'ডেলিভারি চার্জ লোড করা যায়নি।';
  } finally {
    loading.value = false;
  }
}

async function save(m) {
  saving[m.id] = true;
  try {
    const payload = {
      name: m.name,
      code: m.code,
      description: m.description ?? null,
      rate: Number(m.rate || 0),
      free_threshold: Number(m.free_threshold) > 0 ? Number(m.free_threshold) : null,
      estimated_delivery: m.estimated_delivery || null,
      is_active: !!m.is_active,
    };
    await api.put(`/admin/resources/shipping-methods/${m.id}`, payload);
    m.free_threshold = payload.free_threshold;
    saved[m.id] = snapshot(m);
    toast.success(`${m.name} সেভ হয়েছে — চেকআউটে এখনই কার্যকর`);
  } catch (err) {
    const errors = err.response?.data?.errors;
    toast.error(errors ? Object.values(errors).flat()[0] : (err.response?.data?.message || 'সেভ করা যায়নি'));
  } finally {
    delete saving[m.id];
  }
}

onMounted(load);
</script>

<style scoped>
.admin-input {
  width: 100%;
  padding: 0.5rem 0.75rem;
  border: 1px solid #D9DFDB;
  border-radius: 0.375rem;
  background: #FFFFFF;
  font-size: 0.875rem;
  color: #171C1A;
}
.admin-input:focus { outline: none; border-color: #16A36A; box-shadow: 0 0 0 3px rgba(22, 163, 106, 0.15); }
</style>
