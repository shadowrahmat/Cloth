<template>
  <div class="bg-white border border-[#D8CCBC]/40 shadow-xs overflow-hidden">
    <!-- Search & Filters Toolbar -->
    <div class="p-4 sm:p-6 border-b border-[#D8CCBC]/40 flex flex-col sm:flex-row gap-4 items-center justify-between">
      <div class="relative w-full sm:w-80">
        <input
          type="text"
          :value="search"
          @input="$emit('update:search', $event.target.value)"
          :placeholder="searchPlaceholder || 'Search records...'"
          class="w-full pl-9 pr-4 py-2 bg-[#F7F5F0] border border-[#D8CCBC]/60 text-xs text-[#111111] placeholder:text-[#A89B8A] focus:outline-none focus:border-[#111111]"
        />
        <svg class="w-4 h-4 text-[#A89B8A] absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
      </div>

      <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
        <slot name="filters" />
        <slot name="actions" />
      </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse text-xs">
        <thead>
          <tr class="bg-[#F7F5F0] border-b border-[#D8CCBC]/40 text-[#A89B8A] uppercase tracking-wider font-semibold text-[11px]">
            <th
              v-for="col in columns"
              :key="col.key"
              :class="['px-6 py-3.5', col.sortable ? 'cursor-pointer hover:text-[#111111]' : '']"
              @click="col.sortable ? $emit('sort', col.key) : null"
            >
              <div class="flex items-center gap-1.5">
                <span>{{ col.label }}</span>
                <span v-if="col.sortable && sortBy === col.key" class="text-[#111111]">
                  {{ sortDirection === 'desc' ? '↓' : '↑' }}
                </span>
              </div>
            </th>
            <th class="px-6 py-3.5 text-right">Actions</th>
          </tr>
        </thead>

        <tbody class="divide-y divide-[#D8CCBC]/30">
          <tr v-if="loading">
            <td :colspan="columns.length + 1" class="py-12 text-center text-[#A89B8A]">
              Loading records...
            </td>
          </tr>

          <tr v-else-if="!items.length">
            <td :colspan="columns.length + 1" class="py-12 text-center text-[#A89B8A]">
              No records found.
            </td>
          </tr>

          <tr
            v-else
            v-for="item in items"
            :key="item.id"
            class="hover:bg-[#F7F5F0]/60 transition-colors"
          >
            <td
              v-for="col in columns"
              :key="col.key"
              class="px-6 py-4 text-[#111111]"
            >
              <!-- Image type -->
              <template v-if="col.type === 'image'">
                <img
                  v-if="item[col.key]"
                  :src="item[col.key]"
                  alt=""
                  class="w-10 h-12 object-cover bg-[#F7F5F0] border border-[#D8CCBC]/30"
                />
                <span v-else class="text-[#A89B8A] text-[10px]">—</span>
              </template>

              <!-- Boolean badge -->
              <template v-else-if="col.type === 'boolean'">
                <span
                  :class="[
                    'px-2 py-0.5 text-[10px] uppercase font-semibold tracking-wider',
                    item[col.key] ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'
                  ]"
                >
                  {{ item[col.key] ? 'Yes' : 'No' }}
                </span>
              </template>

              <!-- Relation field -->
              <template v-else-if="col.type === 'relation'">
                <span v-if="item[col.key] && col.relationField">
                  {{ item[col.key][col.relationField] }}
                </span>
                <span v-else class="text-[#A89B8A]">—</span>
              </template>

              <!-- URL -->
              <template v-else-if="col.type === 'url'">
                <a
                  v-if="item[col.key]"
                  :href="item[col.key]"
                  target="_blank"
                  class="text-blue-600 hover:underline"
                >
                  Visit Link ↗
                </a>
                <span v-else class="text-[#A89B8A]">—</span>
              </template>

              <!-- Standard text / custom value -->
              <template v-else>
                <slot :name="`col-${col.key}`" :item="item" :value="item[col.key]">
                  {{ item[col.key] !== null && item[col.key] !== undefined ? item[col.key] : '—' }}
                </slot>
              </template>
            </td>

            <!-- Row Actions -->
            <td class="px-6 py-4 text-right space-x-2">
              <slot name="row-actions" :item="item">
                <button
                  type="button"
                  @click="$emit('edit', item)"
                  class="text-xs text-[#111111] hover:text-[#A89B8A] font-medium"
                >
                  Edit
                </button>
                <span class="text-[#D8CCBC]">|</span>
                <button
                  type="button"
                  @click="$emit('delete', item)"
                  class="text-xs text-rose-600 hover:text-rose-800 font-medium"
                >
                  Delete
                </button>
              </slot>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination Footer -->
    <div
      v-if="meta && meta.last_page > 1"
      class="p-4 border-t border-[#D8CCBC]/40 bg-[#F7F5F0] flex items-center justify-between text-xs"
    >
      <span class="text-[#A89B8A]">
        Showing {{ ((meta.current_page - 1) * meta.per_page) + 1 }} to {{ Math.min(meta.total, meta.current_page * meta.per_page) }} of {{ meta.total }} entries
      </span>

      <div class="flex items-center gap-1">
        <button
          type="button"
          :disabled="meta.current_page <= 1"
          @click="$emit('page', meta.current_page - 1)"
          class="px-3 py-1 bg-white border border-[#D8CCBC] disabled:opacity-40 hover:bg-[#F7F5F0]"
        >
          Previous
        </button>
        <span class="px-3 py-1 text-xs font-semibold">
          Page {{ meta.current_page }} / {{ meta.last_page }}
        </span>
        <button
          type="button"
          :disabled="meta.current_page >= meta.last_page"
          @click="$emit('page', meta.current_page + 1)"
          class="px-3 py-1 bg-white border border-[#D8CCBC] disabled:opacity-40 hover:bg-[#F7F5F0]"
        >
          Next
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  columns: { type: Array, required: true },
  items: { type: Array, default: () => [] },
  loading: { type: Boolean, default: false },
  meta: { type: Object, default: null },
  search: { type: String, default: '' },
  searchPlaceholder: { type: String, default: '' },
  sortBy: { type: String, default: '' },
  sortDirection: { type: String, default: 'asc' },
});

defineEmits(['update:search', 'sort', 'edit', 'delete', 'page']);
</script>
