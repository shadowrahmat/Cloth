<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-150 ease-out"
      enter-from-class="opacity-0"
      leave-active-class="transition duration-100 ease-in"
      leave-to-class="opacity-0"
    >
      <div
        v-if="ui.paletteOpen"
        class="fixed inset-0 z-[60] bg-slate-900/50 backdrop-blur-xs flex items-start justify-center px-4 pt-[12vh]"
        @mousedown.self="close"
      >
        <div
          class="w-full max-w-xl bg-white rounded-xl shadow-2xl border border-[#E7EBE8] overflow-hidden"
          role="dialog"
          aria-modal="true"
          aria-label="খুঁজুন"
        >
          <!-- Input -->
          <div class="flex items-center gap-3 px-4 border-b border-[#E7EBE8]">
            <Search class="w-5 h-5 text-[#69736E] shrink-0" />
            <input
              ref="inputRef"
              v-model="query"
              type="text"
              class="flex-1 h-14 text-sm text-[#171C1A] placeholder-[#9AA39F] bg-transparent focus:outline-none"
              placeholder="পেজের নাম, অর্ডার নম্বর, পণ্য বা গ্রাহক লিখুন…"
              @keydown.down.prevent="move(1)"
              @keydown.up.prevent="move(-1)"
              @keydown.enter.prevent="choose(results[activeIndex])"
              @keydown.esc.prevent="close"
            />
            <span v-if="searching" class="w-4 h-4 border-2 border-[#16A36A] border-t-transparent rounded-full animate-spin"></span>
            <kbd class="hidden sm:block text-[10px] font-mono px-1.5 py-0.5 rounded border border-[#E7EBE8] text-[#69736E]">Esc</kbd>
          </div>

          <!-- Results -->
          <div ref="listRef" class="max-h-[55vh] overflow-y-auto py-2">
            <template v-for="(section, si) in sections" :key="section.title">
              <p class="px-4 pt-2 pb-1 text-[11px] font-semibold text-[#69736E]">{{ section.title }}</p>
              <button
                v-for="r in section.items"
                :key="r.key"
                type="button"
                :data-index="r.index"
                @mousemove="activeIndex = r.index"
                @click="choose(r)"
                :class="[
                  'w-full flex items-center gap-3 px-4 py-2.5 text-left text-sm',
                  activeIndex === r.index ? 'bg-emerald-50 text-[#14382A]' : 'text-[#171C1A]',
                ]"
              >
                <span :class="['w-8 h-8 rounded-lg flex items-center justify-center shrink-0', activeIndex === r.index ? 'bg-[#16A36A] text-white' : 'bg-[#F2F4F3] text-[#4B5450]']">
                  <component :is="r.icon" class="w-4 h-4" />
                </span>
                <span class="flex-1 min-w-0">
                  <span class="block truncate font-medium">{{ r.title }}</span>
                  <span v-if="r.subtitle" class="block truncate text-xs text-[#69736E]">{{ r.subtitle }}</span>
                </span>
                <CornerDownLeft v-if="activeIndex === r.index" class="w-4 h-4 text-[#16A36A] shrink-0" />
              </button>
            </template>

            <div v-if="!results.length" class="px-4 py-10 text-center text-sm text-[#69736E]">
              <template v-if="searching">খোঁজা হচ্ছে…</template>
              <template v-else>“{{ query }}” এর সাথে কিছু মেলেনি।</template>
            </div>
          </div>

          <div class="px-4 py-2 border-t border-[#E7EBE8] bg-[#F9FAFA] flex items-center gap-4 text-[11px] text-[#69736E]">
            <span><kbd class="font-mono">↑ ↓</kbd> বাছাই</span>
            <span><kbd class="font-mono">Enter</kbd> খুলুন</span>
            <span class="ml-auto hidden sm:inline">যেকোনো জায়গা থেকে <kbd class="font-mono">Ctrl K</kbd></span>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted, onBeforeUnmount } from 'vue';
import { useRouter } from 'vue-router';
import { Search, CornerDownLeft, Plus, ShoppingBag, Shirt, User } from 'lucide-vue-next';
import api from '../../api';
import { useAuthStore } from '../../stores/auth';
import { useAdminUiStore } from '../../stores/adminUi';
import { navItems, quickCreate, money } from '../../admin-nav';

const router = useRouter();
const authStore = useAuthStore();
const ui = useAdminUiStore();

const query = ref('');
const activeIndex = ref(0);
const inputRef = ref(null);
const listRef = ref(null);
const searching = ref(false);
const remote = ref({ orders: [], products: [], customers: [] });

const allowed = (x) => !x.permission || authStore.hasPermission(x.permission);
const normalize = (s) => (s || '').toLowerCase();
const matches = (q, ...fields) => fields.some((f) => normalize(f).includes(q));

const sections = computed(() => {
  const q = normalize(query.value.trim());
  const out = [];

  const pages = navItems
    .filter(allowed)
    .filter((i) => !q || matches(q, i.label, i.hint, i.keywords, i.group))
    .slice(0, q ? 6 : 8)
    .map((i) => ({ key: 'nav:' + i.to, title: i.label, subtitle: `${i.group} · ${i.hint}`, icon: i.icon, to: i.to }));
  if (pages.length) out.push({ title: 'পেজ', items: pages });

  const creates = quickCreate
    .filter(allowed)
    .filter((c) => !q || matches(q, c.label, c.hint, 'new add create নতুন'))
    .slice(0, q ? 3 : 5)
    .map((c) => ({ key: 'new:' + c.to, title: c.label, subtitle: c.hint, icon: Plus, to: c.to }));
  if (creates.length) out.push({ title: 'নতুন যোগ করুন', items: creates });

  const r = remote.value;
  if (r.orders.length) {
    out.push({
      title: 'অর্ডার',
      items: r.orders.map((o) => ({
        key: 'order:' + o.id,
        title: `#${o.order_number} · ${o.customer_name}`,
        subtitle: `${money(o.total)} · ${o.order_status}`,
        icon: ShoppingBag,
        to: { path: '/admin/orders', query: { search: o.order_number } },
      })),
    });
  }
  if (r.products.length) {
    out.push({
      title: 'পণ্য',
      items: r.products.map((p) => ({
        key: 'product:' + p.id,
        title: p.name,
        subtitle: `${p.sku || 'SKU নেই'} · স্টক ${p.stock} · ${money(p.price)}`,
        icon: Shirt,
        to: { path: '/admin/products', query: { search: p.sku || p.name } },
      })),
    });
  }
  if (r.customers.length) {
    out.push({
      title: 'গ্রাহক',
      items: r.customers.map((c) => ({
        key: 'customer:' + c.id,
        title: c.name,
        subtitle: [c.email, c.phone].filter(Boolean).join(' · '),
        icon: User,
        to: { path: '/admin/customers', query: { search: c.email || c.name } },
      })),
    });
  }

  // Number every row so keyboard navigation runs across sections.
  let i = 0;
  out.forEach((s) => s.items.forEach((it) => { it.index = i++; }));
  return out;
});

const results = computed(() => sections.value.flatMap((s) => s.items));

const move = (step) => {
  if (!results.value.length) return;
  activeIndex.value = (activeIndex.value + step + results.value.length) % results.value.length;
  nextTick(() => listRef.value?.querySelector(`[data-index="${activeIndex.value}"]`)?.scrollIntoView({ block: 'nearest' }));
};

const close = () => { ui.paletteOpen = false; };

const choose = (r) => {
  if (!r) return;
  close();
  router.push(r.to);
};

// Live search for orders / products / customers, debounced.
let timer = null;
let requestId = 0;
watch(query, (q) => {
  activeIndex.value = 0;
  clearTimeout(timer);
  const term = q.trim();
  if (term.length < 2) {
    remote.value = { orders: [], products: [], customers: [] };
    searching.value = false;
    return;
  }
  searching.value = true;
  timer = setTimeout(async () => {
    const id = ++requestId;
    try {
      const res = await api.get('/admin/search', { params: { q: term } });
      if (id === requestId) remote.value = res.data.data;
    } catch {
      if (id === requestId) remote.value = { orders: [], products: [], customers: [] };
    } finally {
      if (id === requestId) searching.value = false;
    }
  }, 250);
});

watch(() => ui.paletteOpen, (open) => {
  if (open) {
    query.value = '';
    activeIndex.value = 0;
    nextTick(() => inputRef.value?.focus());
  }
});

// Global shortcut: Ctrl+K / Cmd+K, or "/" when not typing in a field.
const onKey = (e) => {
  const typing = ['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target?.tagName) || e.target?.isContentEditable;
  if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
    e.preventDefault();
    ui.paletteOpen = !ui.paletteOpen;
  } else if (e.key === '/' && !typing && !ui.paletteOpen) {
    e.preventDefault();
    ui.paletteOpen = true;
  }
};
onMounted(() => window.addEventListener('keydown', onKey));
onBeforeUnmount(() => window.removeEventListener('keydown', onKey));
</script>
