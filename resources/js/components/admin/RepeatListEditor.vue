<template>
  <div class="space-y-2.5">
    <div v-for="(row, i) in modelValue" :key="row._k || (row._k = uid())" class="flex gap-2 items-start p-3 rounded-lg border border-[#ECEFED] bg-[#FAFBFA]">
      <div class="flex flex-col pt-1">
        <button type="button" :disabled="i === 0" @click="move(i, -1)" class="p-0.5 text-[#9AA39F] hover:text-[#171C1A] disabled:opacity-30" title="উপরে"><ChevronUp class="w-4 h-4" /></button>
        <button type="button" :disabled="i === modelValue.length - 1" @click="move(i, 1)" class="p-0.5 text-[#9AA39F] hover:text-[#171C1A] disabled:opacity-30" title="নিচে"><ChevronDown class="w-4 h-4" /></button>
      </div>
      <div :class="['flex-1 min-w-0 grid gap-2', inline ? 'sm:grid-cols-[1fr_2fr]' : '']">
        <template v-for="f in fields" :key="f.key">
          <textarea v-if="f.type === 'textarea'" v-model="row[f.key]" :placeholder="f.label" rows="2" class="w-full px-3 py-2 rounded-lg border border-[#E7EBE8] bg-white text-sm focus:outline-none focus:border-[#16A36A] focus:ring-2 focus:ring-[#16A36A]/15"></textarea>
          <input v-else v-model="row[f.key]" :placeholder="f.label" class="w-full h-9 px-3 rounded-lg border border-[#E7EBE8] bg-white text-sm focus:outline-none focus:border-[#16A36A] focus:ring-2 focus:ring-[#16A36A]/15" />
        </template>
      </div>
      <button type="button" @click="modelValue.splice(i, 1)" class="p-2 rounded-lg text-[#9AA39F] hover:text-rose-600 hover:bg-rose-50" title="মুছুন"><Trash2 class="w-4 h-4" /></button>
    </div>
    <button v-if="modelValue.length < max" type="button" @click="add"
            class="inline-flex items-center gap-1.5 h-8 px-3 rounded-lg border border-dashed border-[#CFD6D3] text-[13px] text-[#4B5450] hover:border-[#16A36A] hover:text-[#16A36A]">
      <Plus class="w-3.5 h-3.5" /> {{ addLabel }}
    </button>
  </div>
</template>

<script setup>
import { ChevronUp, ChevronDown, Trash2, Plus } from 'lucide-vue-next';

const props = defineProps({
  modelValue: { type: Array, required: true },
  /** [{ key, label, type: 'text' | 'textarea' }] */
  fields: { type: Array, required: true },
  max: { type: Number, default: 10 },
  addLabel: { type: String, default: 'যোগ করুন' },
  /** Put two fields side by side (e.g. value + label). */
  inline: { type: Boolean, default: false },
});

let n = 0;
const uid = () => `r${Date.now().toString(36)}${n++}`;
const add = () => props.modelValue.push(Object.fromEntries([...props.fields.map((f) => [f.key, '']), ['_k', uid()]]));
const move = (i, d) => { const [x] = props.modelValue.splice(i, 1); props.modelValue.splice(i + d, 0, x); };
</script>
