<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-[#E7EBE8]">
      <div>
        <h2 class="text-xl font-bold tracking-tight text-[#171C1A]">Returns & Exchanges Workflow</h2>
        <p class="text-xs text-[#69736E] mt-0.5">Manage customer return authorizations, product conditions, size exchanges, and refund transactions.</p>
      </div>

      <div class="flex items-center gap-3">
        <select
          v-model="statusFilter"
          @change="fetchReturns"
          class="text-xs bg-white border border-[#E7EBE8] rounded-md px-3 py-2 text-[#171C1A] focus:outline-none focus:ring-1 focus:ring-[#16A36A]"
        >
          <option value="">All Statuses</option>
          <option value="pending">Pending Approval</option>
          <option value="approved">Approved / In Transit</option>
          <option value="completed">Completed & Refunded</option>
          <option value="rejected">Rejected</option>
        </select>
      </div>
    </div>

    <!-- Returns Table -->
    <div class="bg-white rounded-lg border border-[#E7EBE8] overflow-hidden shadow-xs">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead>
            <tr class="bg-[#F7F8FA] text-[#69736E] border-b border-[#E7EBE8]">
              <th class="py-3 px-4 font-semibold">RMA #</th>
              <th class="py-3 px-4 font-semibold">Order #</th>
              <th class="py-3 px-4 font-semibold">Customer</th>
              <th class="py-3 px-4 font-semibold">Type & Reason</th>
              <th class="py-3 px-4 font-semibold text-right">Refund / Value</th>
              <th class="py-3 px-4 font-semibold text-center">Status</th>
              <th class="py-3 px-4 font-semibold text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#E7EBE8]">
            <tr v-for="r in returns" :key="r.id" class="hover:bg-[#F7F8FA]">
              <td class="py-3 px-4 font-mono font-bold text-[#14382A]">
                {{ r.return_number }}
              </td>
              <td class="py-3 px-4 font-mono font-medium text-[#171C1A]">
                {{ r.order?.order_number || 'N/A' }}
              </td>
              <td class="py-3 px-4 text-[#171C1A]">
                <div class="font-semibold">{{ r.order?.customer_name || 'Guest Customer' }}</div>
                <div class="text-[11px] text-[#69736E]">{{ r.order?.customer_email }}</div>
              </td>
              <td class="py-3 px-4 text-[#171C1A]">
                <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase mr-1 bg-slate-100 text-slate-800">
                  {{ r.type }}
                </span>
                <span class="text-xs text-[#69736E]">{{ r.reason }}</span>
              </td>
              <td class="py-3 px-4 text-right font-bold text-rose-700">
                ${{ Number(r.refund_amount || 0).toFixed(2) }}
              </td>
              <td class="py-3 px-4 text-center">
                <StatusBadge :status="r.status" />
              </td>
              <td class="py-3 px-4 text-right space-x-2 whitespace-nowrap">
                <button
                  type="button"
                  @click="openModal(r)"
                  class="px-2.5 py-1 text-xs font-semibold text-[#14382A] bg-emerald-50 hover:bg-[#14382A] hover:text-white rounded-md transition-colors"
                >
                  Process RMA
                </button>
              </td>
            </tr>
            <tr v-if="!returns.length">
              <td colspan="7" class="py-8 text-center text-slate-400">No return requests filed yet.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Process Return Modal -->
    <BaseModal
      :is-open="isModalOpen"
      :title="`Return Authorization: ${selectedReturn?.return_number || ''}`"
      size="md"
      @close="isModalOpen = false"
    >
      <div v-if="selectedReturn" class="space-y-4 text-xs">
        <div class="bg-[#F7F8FA] p-3 rounded-md border border-[#E7EBE8] space-y-1">
          <div class="flex justify-between">
            <span class="text-[#69736E]">Customer Reason:</span>
            <span class="font-semibold text-[#171C1A]">{{ selectedReturn.reason }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-[#69736E]">Reported Condition:</span>
            <span class="font-semibold text-[#171C1A] capitalize">{{ selectedReturn.condition || 'Unopened' }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-[#69736E]">Associated Order:</span>
            <span class="font-mono font-bold text-[#14382A]">{{ selectedReturn.order?.order_number }}</span>
          </div>
        </div>

        <div>
          <label class="block text-xs font-semibold text-[#171C1A] mb-1">RMA Decision Status *</label>
          <select
            v-model="selectedReturn.status"
            class="w-full bg-white border border-[#E7EBE8] rounded-md px-3 py-2 text-[#171C1A] focus:outline-none focus:ring-1 focus:ring-[#16A36A]"
          >
            <option value="pending">Pending Review</option>
            <option value="approved">Approve Return (Issue Return Label)</option>
            <option value="completed">Completed & Refund Processed</option>
            <option value="rejected">Reject Request</option>
          </select>
        </div>

        <div>
          <label class="block text-xs font-semibold text-[#171C1A] mb-1">Refund Amount ($)</label>
          <input
            v-model.number="selectedReturn.refund_amount"
            type="number"
            step="0.01"
            class="w-full bg-white border border-[#E7EBE8] rounded-md px-3 py-2 text-[#171C1A] focus:outline-none focus:ring-1 focus:ring-[#16A36A]"
          />
        </div>

        <div>
          <label class="block text-xs font-semibold text-[#171C1A] mb-1">Staff / Audit Notes</label>
          <textarea
            v-model="selectedReturn.admin_notes"
            rows="3"
            placeholder="Record internal inspection findings or refund confirmation ID..."
            class="w-full bg-white border border-[#E7EBE8] rounded-md p-3 text-[#171C1A] focus:outline-none focus:ring-1 focus:ring-[#16A36A]"
          ></textarea>
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
            type="button"
            v-can="'update_orders'" @click="saveReturn"
            class="px-4 py-2 bg-[#14382A] hover:bg-[#16A36A] text-white text-xs font-semibold rounded-md shadow-xs transition-colors"
          >
            Update RMA Workflow
          </button>
        </div>
      </div>
    </BaseModal>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import BaseModal from '../../components/admin/BaseModal.vue';
import StatusBadge from '../../components/admin/StatusBadge.vue';
import { useToastStore } from '../../stores/toast';
import api from '../../api';

const toast = useToastStore();
const returns = ref([]);
const statusFilter = ref('');
const isModalOpen = ref(false);
const selectedReturn = ref(null);

const fetchReturns = async () => {
  try {
    const res = await api.get('/admin/returns', { params: { status: statusFilter.value } });
    returns.value = res.data?.data?.data || [];
  } catch (err) {
    console.error('Failed to load returns', err);
  }
};

const openModal = (r) => {
  selectedReturn.value = { ...r };
  isModalOpen.value = true;
};

const saveReturn = async () => {
  try {
    await api.put(`/admin/returns/${selectedReturn.value.id}`, {
      status: selectedReturn.value.status,
      admin_notes: selectedReturn.value.admin_notes,
      refund_amount: selectedReturn.value.refund_amount,
    });
    toast.success('Return authorization updated');
    isModalOpen.value = false;
    fetchReturns();
  } catch (err) {
    toast.error('Failed to update return');
  }
};

onMounted(() => {
  fetchReturns();
});
</script>
