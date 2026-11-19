<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-[#E7EBE8]">
      <div>
        <h2 class="text-xl font-bold tracking-tight text-[#171C1A]">Coupons & Promotional Rules</h2>
        <p class="text-xs text-[#69736E] mt-0.5">Manage fixed/percentage promotional discount codes, spend thresholds, and caps.</p>
      </div>

      <div class="flex items-center gap-3">
        <button
          type="button"
          @click="openCreateModal"
          class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-[#14382A] text-white text-xs font-semibold rounded-md hover:bg-[#16A36A] transition-colors shadow-xs"
        >
          <span>+</span>
          <span>Create Coupon</span>
        </button>
      </div>
    </div>

    <!-- Coupons Table -->
    <div class="bg-white rounded-lg border border-[#E7EBE8] overflow-hidden shadow-xs">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead>
            <tr class="bg-[#F7F8FA] text-[#69736E] border-b border-[#E7EBE8]">
              <th class="py-3 px-4 font-semibold">Code</th>
              <th class="py-3 px-4 font-semibold">Discount</th>
              <th class="py-3 px-4 font-semibold">Rules / Cap</th>
              <th class="py-3 px-4 font-semibold">Usage</th>
              <th class="py-3 px-4 font-semibold">Validity</th>
              <th class="py-3 px-4 font-semibold text-center">Status</th>
              <th class="py-3 px-4 font-semibold text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#E7EBE8]">
            <tr v-for="c in coupons" :key="c.id" class="hover:bg-[#F7F8FA]">
              <td class="py-3 px-4 font-mono font-bold text-[#14382A]">
                <div class="flex items-center gap-2">
                  <span class="px-2 py-0.5 bg-emerald-50 border border-emerald-200/80 rounded-sm">{{ c.code }}</span>
                </div>
                <span class="text-[11px] text-[#69736E] font-sans block mt-0.5">{{ c.description || 'Promotional Discount' }}</span>
              </td>
              <td class="py-3 px-4 font-semibold text-[#171C1A]">
                <span v-if="c.discount_type === 'percentage'">{{ c.discount_value }}% OFF</span>
                <span v-else>${{ c.discount_value }} OFF</span>
              </td>
              <td class="py-3 px-4 text-[#69736E]">
                <div>Min Spend: <span class="font-medium text-[#171C1A]">${{ c.min_spend || '0' }}</span></div>
                <div v-if="c.max_discount">Max Cap: <span class="font-medium text-[#171C1A]">${{ c.max_discount }}</span></div>
              </td>
              <td class="py-3 px-4 text-[#69736E] font-mono">
                {{ c.used_count || 0 }} / {{ c.usage_limit || '∞' }}
              </td>
              <td class="py-3 px-4 text-[#69736E] text-[11px]">
                <span v-if="c.expires_at">Exp: {{ new Date(c.expires_at).toLocaleDateString() }}</span>
                <span v-else class="text-emerald-700 font-medium">Never Expires</span>
              </td>
              <td class="py-3 px-4 text-center">
                <StatusBadge :status="c.is_active ? 'active' : 'inactive'" />
              </td>
              <td class="py-3 px-4 text-right space-x-2">
                <button
                  type="button"
                  @click="openEditModal(c)"
                  class="px-2.5 py-1 text-xs text-[#14382A] hover:bg-slate-100 rounded-md transition-colors"
                >
                  Edit
                </button>
                <button
                  type="button"
                  @click="deleteCoupon(c.id)"
                  class="px-2.5 py-1 text-xs text-rose-600 hover:bg-rose-50 rounded-md transition-colors"
                >
                  Delete
                </button>
              </td>
            </tr>
            <tr v-if="!coupons.length">
              <td colspan="7" class="py-8 text-center text-slate-400">No coupons active. Click "Create Coupon" to add one.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <BaseModal
      :is-open="isModalOpen"
      :title="editingId ? 'Edit Coupon' : 'Create New Promotional Coupon'"
      @close="isModalOpen = false"
    >
      <form @submit.prevent="saveCoupon" class="space-y-4 text-xs">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-semibold text-[#171C1A] mb-1">Coupon Code *</label>
            <input
              v-model="form.code"
              type="text"
              required
              placeholder="e.g. SUMMER25"
              class="w-full uppercase font-mono bg-white border border-[#E7EBE8] rounded-md px-3 py-2 text-[#171C1A] focus:outline-none focus:ring-1 focus:ring-[#16A36A]"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-[#171C1A] mb-1">Discount Type *</label>
            <select
              v-model="form.discount_type"
              class="w-full bg-white border border-[#E7EBE8] rounded-md px-3 py-2 text-[#171C1A] focus:outline-none focus:ring-1 focus:ring-[#16A36A]"
            >
              <option value="percentage">Percentage Discount (%)</option>
              <option value="fixed">Fixed Cash Amount ($)</option>
            </select>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="block text-xs font-semibold text-[#171C1A] mb-1">Discount Value *</label>
            <input
              v-model.number="form.discount_value"
              type="number"
              step="0.01"
              required
              class="w-full bg-white border border-[#E7EBE8] rounded-md px-3 py-2 text-[#171C1A] focus:outline-none focus:ring-1 focus:ring-[#16A36A]"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-[#171C1A] mb-1">Min Spend ($)</label>
            <input
              v-model.number="form.min_spend"
              type="number"
              step="0.01"
              placeholder="Optional"
              class="w-full bg-white border border-[#E7EBE8] rounded-md px-3 py-2 text-[#171C1A] focus:outline-none focus:ring-1 focus:ring-[#16A36A]"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-[#171C1A] mb-1">Max Cap ($)</label>
            <input
              v-model.number="form.max_discount"
              type="number"
              step="0.01"
              placeholder="Optional"
              class="w-full bg-white border border-[#E7EBE8] rounded-md px-3 py-2 text-[#171C1A] focus:outline-none focus:ring-1 focus:ring-[#16A36A]"
            />
          </div>
        </div>

        <div>
          <label class="block text-xs font-semibold text-[#171C1A] mb-1">Description</label>
          <input
            v-model="form.description"
            type="text"
            placeholder="e.g. 20% off for VIP autumn release"
            class="w-full bg-white border border-[#E7EBE8] rounded-md px-3 py-2 text-[#171C1A] focus:outline-none focus:ring-1 focus:ring-[#16A36A]"
          />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-semibold text-[#171C1A] mb-1">Usage Limit</label>
            <input
              v-model.number="form.usage_limit"
              type="number"
              placeholder="Leave empty for unlimited"
              class="w-full bg-white border border-[#E7EBE8] rounded-md px-3 py-2 text-[#171C1A] focus:outline-none focus:ring-1 focus:ring-[#16A36A]"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-[#171C1A] mb-1">Expiry Date</label>
            <input
              v-model="form.expires_at"
              type="date"
              class="w-full bg-white border border-[#E7EBE8] rounded-md px-3 py-2 text-[#171C1A] focus:outline-none focus:ring-1 focus:ring-[#16A36A]"
            />
          </div>
        </div>

        <div class="flex items-center gap-2 pt-2">
          <input
            v-model="form.is_active"
            type="checkbox"
            id="coupon_active"
            class="rounded border-slate-300 text-[#16A36A] focus:ring-[#16A36A]"
          />
          <label for="coupon_active" class="text-xs font-medium text-[#171C1A]">Active and redeemable at checkout</label>
        </div>

        <div class="pt-4 flex justify-end gap-3 border-t border-[#E7EBE8]">
          <button
            type="button"
            @click="isModalOpen = false"
            class="px-4 py-2 border border-[#E7EBE8] text-xs font-semibold text-[#171C1A] rounded-md hover:bg-slate-50"
          >
            Cancel
          </button>
          <button
            type="submit"
            class="px-4 py-2 bg-[#14382A] hover:bg-[#16A36A] text-white text-xs font-semibold rounded-md shadow-xs transition-colors"
          >
            Save Coupon
          </button>
        </div>
      </form>
    </BaseModal>
  </div>
</template>

<script setup>
import { useAdminDeepLink } from '../../composables/useAdminDeepLink';
import { ref, onMounted } from 'vue';
import BaseModal from '../../components/admin/BaseModal.vue';
import StatusBadge from '../../components/admin/StatusBadge.vue';
import { useToastStore } from '../../stores/toast';
import api from '../../api';

const toast = useToastStore();
const coupons = ref([]);
const isModalOpen = ref(false);
const editingId = ref(null);

const form = ref({
  code: '',
  description: '',
  discount_type: 'percentage',
  discount_value: 10,
  min_spend: null,
  max_discount: null,
  usage_limit: null,
  expires_at: null,
  is_active: true,
});

const fetchCoupons = async () => {
  try {
    const res = await api.get('/admin/coupons');
    coupons.value = res.data?.data?.data || [];
  } catch (err) {
    console.error('Failed to load coupons', err);
  }
};

const openCreateModal = () => {
  editingId.value = null;
  form.value = {
    code: '',
    description: '',
    discount_type: 'percentage',
    discount_value: 10,
    min_spend: null,
    max_discount: null,
    usage_limit: null,
    expires_at: null,
    is_active: true,
  };
  isModalOpen.value = true;
};

const openEditModal = (c) => {
  editingId.value = c.id;
  form.value = { ...c };
  isModalOpen.value = true;
};

const saveCoupon = async () => {
  try {
    if (editingId.value) {
      await api.put(`/admin/coupons/${editingId.value}`, form.value);
      toast.success('Coupon updated successfully');
    } else {
      await api.post('/admin/coupons', form.value);
      toast.success('Coupon created successfully');
    }
    isModalOpen.value = false;
    fetchCoupons();
  } catch (err) {
    toast.error(err.response?.data?.message || 'Failed to save coupon');
  }
};

const deleteCoupon = async (id) => {
  if (!confirm('Are you sure you want to delete this coupon?')) return;
  try {
    await api.delete(`/admin/coupons/${id}`);
    toast.success('Coupon removed');
    fetchCoupons();
  } catch (err) {
    toast.error('Failed to delete coupon');
  }
};

onMounted(() => {
  fetchCoupons();
});

// Open from links like ?search=... or ?new=1 (sidebar, Ctrl+K, dashboard)
useAdminDeepLink({ onNew: openCreateModal });
</script>
