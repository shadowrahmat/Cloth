<template>
  <div class="space-y-2">
    <TransitionGroup name="list" tag="div" class="space-y-2">
      <div v-for="(link, i) in modelValue" :key="link._k || (link._k = uid())" class="flex items-center gap-2">
        <div class="flex flex-col -my-1">
          <button type="button" :disabled="i === 0" @click="move(i, -1)" class="p-0.5 text-[#9AA39F] hover:text-[#171C1A] disabled:opacity-30" title="উপরে নিন"><ChevronUp class="w-3.5 h-3.5" /></button>
          <button type="button" :disabled="i === modelValue.length - 1" @click="move(i, 1)" class="p-0.5 text-[#9AA39F] hover:text-[#171C1A] disabled:opacity-30" title="নিচে নিন"><ChevronDown class="w-3.5 h-3.5" /></button>
        </div>
        <input v-model="link.label" :placeholder="labelPlaceholder" class="w-2/5 min-w-0 h-9 px-3 rounded-lg border border-[#E7EBE8] text-sm focus:outline-none focus:border-[#16A36A] focus:ring-2 focus:ring-[#16A36A]/15" />
        <input v-model="link.url" :list="listId" placeholder="/collections বা https://…" class="flex-1 min-w-0 h-9 px-3 rounded-lg border border-[#E7EBE8] text-sm font-mono text-[13px] focus:outline-none focus:border-[#16A36A] focus:ring-2 focus:ring-[#16A36A]/15" />
        <button type="button" @click="remove(i)" class="p-2 rounded-lg text-[#9AA39F] hover:text-rose-600 hover:bg-rose-50" title="মুছুন"><Trash2 class="w-4 h-4" /></button>
      </div>
    </TransitionGroup>
    <button
      v-if="modelValue.length < max"
      type="button"
      @click="add"
      class="inline-flex items-center gap-1.5 h-8 px-3 rounded-lg border border-dashed border-[#CFD6D3] text-[13px] text-[#4B5450] hover:border-[#16A36A] hover:text-[#16A36A]"
    >
      <Plus class="w-3.5 h-3.5" /> {{ addLabel }}
    </button>
  </div>
</template>

<script setup>
import { ChevronUp, ChevronDown, Trash2, Plus } from 'lucide-vue-next';

const props = defineProps({
  modelValue: { type: Array, required: true },
  listId: { type: String, default: 'site-link-suggestions' },
  max: { type: Number, default: 20 },
  addLabel: { type: String, default: 'লিংক যোগ করুন' },
  labelPlaceholder: { type: String, default: 'লেখা' },
});

let n = 0;
const uid = () => `k${Date.now().toString(36)}${n++}`;

// The array is mutated in place so nested editors stay simple.
const add = () => props.modelValue.push({ label: '', url: '', _k: uid() });
const remove = (i) => props.modelValue.splice(i, 1);
const move = (i, d) => {
  const [item] = props.modelValue.splice(i, 1);
  props.modelValue.splice(i + d, 0, item);
};
</script>

<style scoped>
.list-move, .list-enter-active, .list-leave-active { transition: all 0.25s ease; }
.list-enter-from, .list-leave-to { opacity: 0; transform: translateY(-6px); }
.list-leave-active { position: absolute; }
</style>
