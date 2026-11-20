<template>
  <div class="space-y-4">
    <div
      v-for="(chart, ci) in modelValue"
      :key="chart._k || ci"
      class="rounded-xl border border-[#E7EBE8] bg-white p-4 sm:p-5 space-y-4"
    >
      <!-- Name + delete -->
      <div class="flex items-start gap-3">
        <label class="flex-1 min-w-0">
          <span class="block text-[12px] font-semibold text-[#4B5450] mb-1">চার্টের নাম</span>
          <input v-model="chart.name" maxlength="80" class="sc-input" placeholder="যেমন: প্যান্ট / পাঞ্জাবি" />
        </label>
        <button
          type="button"
          class="mt-6 h-9 w-9 shrink-0 inline-flex items-center justify-center rounded-lg text-[#69736E] hover:bg-rose-50 hover:text-rose-600"
          :aria-label="`${chart.name || 'চার্ট'} মুছুন`"
          @click="removeChart(ci)"
        >
          <Trash2 class="w-4 h-4" />
        </button>
      </div>

      <!-- Categories -->
      <div>
        <p class="text-[12px] font-semibold text-[#4B5450] mb-1.5">কোন ক্যাটাগরির পণ্যে দেখাবে</p>
        <div class="flex flex-wrap gap-1.5">
          <label
            v-for="cat in categories"
            :key="cat.slug"
            :class="[
              'inline-flex items-center gap-1.5 h-8 px-3 rounded-full border text-[12.5px] cursor-pointer select-none transition-colors',
              (chart.categories || []).includes(cat.slug) ? 'bg-[#14382A] border-[#14382A] text-white' : 'bg-white border-[#D9DFDB] text-[#4B5450] hover:border-[#14382A]',
            ]"
          >
            <input type="checkbox" class="sr-only" :value="cat.slug" v-model="chart.categories" />
            {{ cat.label }}
          </label>
          <span v-if="!categories.length" class="text-[12px] text-[#69736E]">ক্যাটাগরি লোড হচ্ছে…</span>
        </div>
        <p class="text-[11.5px] text-[#69736E] mt-1.5">
          কিছু না বাছলে এই চার্ট বাকি সব পণ্যে দেখাবে। মূল ক্যাটাগরি বাছলে তার সাব-ক্যাটাগরিতেও দেখাবে।
        </p>
      </div>

      <!-- Note -->
      <label class="block">
        <span class="block text-[12px] font-semibold text-[#4B5450] mb-1">ছোট নোট (চার্টের ওপরে দেখাবে)</span>
        <textarea v-model="chart.note" rows="2" maxlength="400" class="sc-input" placeholder="যেমন: মাপ ইঞ্চিতে দেওয়া।"></textarea>
      </label>

      <!-- Table -->
      <div>
        <p class="text-[12px] font-semibold text-[#4B5450] mb-1.5">মাপের টেবিল</p>
        <div class="overflow-x-auto rounded-lg border border-[#E7EBE8]">
          <table class="w-full text-[13px] border-collapse">
            <thead>
              <tr class="bg-[#F5F7F6]">
                <th v-for="(col, i) in chart.columns" :key="'h' + i" class="p-1.5 min-w-28 align-top">
                  <div class="flex items-center gap-1">
                    <input v-model="chart.columns[i]" maxlength="30" class="sc-cell font-semibold" :placeholder="`কলাম ${i + 1}`" :aria-label="`কলাম ${i + 1} এর নাম`" />
                    <button
                      v-if="chart.columns.length > 2"
                      type="button"
                      class="h-7 w-7 shrink-0 inline-flex items-center justify-center rounded text-[#69736E] hover:bg-rose-50 hover:text-rose-600"
                      :aria-label="`কলাম ${col || i + 1} মুছুন`"
                      @click="removeColumn(chart, i)"
                    >
                      <X class="w-3.5 h-3.5" />
                    </button>
                  </div>
                </th>
                <th class="w-9"></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(row, ri) in chart.rows" :key="'r' + ri" class="border-t border-[#E7EBE8]">
                <td v-for="(col, i) in chart.columns" :key="'c' + i" class="p-1.5">
                  <input v-model="row[i]" maxlength="30" class="sc-cell" :aria-label="`সারি ${ri + 1}, ${col || 'কলাম ' + (i + 1)}`" />
                </td>
                <td class="p-1.5 text-center">
                  <button
                    type="button"
                    class="h-7 w-7 inline-flex items-center justify-center rounded text-[#69736E] hover:bg-rose-50 hover:text-rose-600"
                    :aria-label="`সারি ${ri + 1} মুছুন`"
                    @click="chart.rows.splice(ri, 1)"
                  >
                    <X class="w-3.5 h-3.5" />
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="flex flex-wrap gap-2 mt-2">
          <button type="button" class="sc-btn" :disabled="chart.rows.length >= 30" @click="addRow(chart)"><Plus class="w-3.5 h-3.5" /> সারি যোগ করুন</button>
          <button type="button" class="sc-btn" :disabled="chart.columns.length >= 8" @click="addColumn(chart)"><Plus class="w-3.5 h-3.5" /> কলাম যোগ করুন</button>
        </div>
      </div>
    </div>

    <button
      type="button"
      class="w-full h-11 inline-flex items-center justify-center gap-2 rounded-xl border border-dashed border-[#B9C4BE] text-[13px] font-semibold text-[#14382A] hover:bg-white disabled:opacity-40"
      :disabled="modelValue.length >= 12"
      @click="addChart"
    >
      <Plus class="w-4 h-4" /> নতুন সাইজ চার্ট
    </button>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Plus, X, Trash2 } from 'lucide-vue-next';
import api from '../../api';

/** v-model: [{ name, categories: [slug], note, columns: [..], rows: [[..]] }] — edited in place. */
const props = defineProps({
  modelValue: { type: Array, required: true },
});

const categories = ref([]);
let seq = 0;

function addChart() {
  props.modelValue.push({
    _k: `new-${++seq}`,
    name: '',
    categories: [],
    note: '',
    columns: ['সাইজ', 'বুক (ইঞ্চি)', 'লম্বা (ইঞ্চি)'],
    rows: [['', '', '']],
  });
}
function removeChart(i) {
  const name = props.modelValue[i]?.name || 'এই চার্ট';
  if (window.confirm(`"${name}" মুছে ফেলবেন?`)) props.modelValue.splice(i, 1);
}
function addRow(chart) {
  chart.rows.push(chart.columns.map(() => ''));
}
function addColumn(chart) {
  chart.columns.push('');
  chart.rows.forEach((r) => r.push(''));
}
function removeColumn(chart, i) {
  chart.columns.splice(i, 1);
  chart.rows.forEach((r) => r.splice(i, 1));
}

onMounted(async () => {
  try {
    const res = await api.get('/public/categories');
    categories.value = (res.data.data || []).flatMap((c) => [
      { slug: c.slug, label: c.name },
      ...(c.children || []).map((ch) => ({ slug: ch.slug, label: `${c.name} › ${ch.name}` })),
    ]);
  } catch { /* the chips just stay empty */ }
});
</script>

<style scoped>
.sc-input {
  width: 100%;
  padding: 0.5rem 0.75rem;
  border: 1px solid #D9DFDB;
  border-radius: 0.5rem;
  background: #FFFFFF;
  font-size: 13.5px;
  color: #171C1A;
}
.sc-cell {
  width: 100%;
  min-width: 0;
  height: 32px;
  padding: 0 0.5rem;
  border: 1px solid transparent;
  border-radius: 0.375rem;
  background: transparent;
  font-size: 13px;
  color: #171C1A;
}
.sc-cell:hover { border-color: #E2E7E4; }
.sc-input:focus, .sc-cell:focus { outline: none; border-color: #16A36A; background: #FFFFFF; box-shadow: 0 0 0 3px rgba(22, 163, 106, 0.15); }
.sc-btn {
  display: inline-flex; align-items: center; gap: 6px;
  height: 32px; padding: 0 12px;
  border-radius: 0.5rem; border: 1px solid #D9DFDB; background: #FFFFFF;
  font-size: 12.5px; font-weight: 600; color: #14382A;
}
.sc-btn:hover:not(:disabled) { border-color: #14382A; }
.sc-btn:disabled { opacity: .4; cursor: not-allowed; }
</style>
