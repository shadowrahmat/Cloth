<template>
  <div class="space-y-6">
    <!-- Header with Tabs -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-[#E7EBE8]">
      <div>
        <h2 class="text-xl font-bold tracking-tight text-[#171C1A]">Inquiries & Communications</h2>
        <p class="text-xs text-[#69736E] mt-0.5">Manage customer inquiries, corporate wholesale requests, and supplier messages.</p>
      </div>

      <!-- Tab Switcher -->
      <div class="flex items-center gap-2 bg-[#F7F8FA] p-1 rounded-lg border border-[#E7EBE8]">
        <button
          type="button"
          @click="activeTab = 'general'; fetchMessages()"
          :class="[
            'px-3.5 py-1.5 text-xs font-semibold rounded-md transition-all',
            activeTab === 'general' ? 'bg-white text-[#14382A] shadow-xs' : 'text-[#69736E] hover:text-[#171C1A]'
          ]"
        >
          General Inquiries
        </button>
        <button
          type="button"
          @click="activeTab = 'wholesale'; fetchMessages()"
          :class="[
            'px-3.5 py-1.5 text-xs font-semibold rounded-md transition-all',
            activeTab === 'wholesale' ? 'bg-white text-[#14382A] shadow-xs' : 'text-[#69736E] hover:text-[#171C1A]'
          ]"
        >
          Corporate / B2B Wholesale
        </button>
      </div>
    </div>

    <!-- Messages Table -->
    <div class="bg-white rounded-lg border border-[#E7EBE8] overflow-hidden shadow-xs">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead>
            <tr class="bg-[#F7F8FA] text-[#69736E] border-b border-[#E7EBE8]">
              <th class="py-3 px-4 font-semibold">Sender</th>
              <th v-if="activeTab === 'wholesale'" class="py-3 px-4 font-semibold">Company</th>
              <th class="py-3 px-4 font-semibold">Subject & Message Preview</th>
              <th class="py-3 px-4 font-semibold">Received</th>
              <th class="py-3 px-4 font-semibold text-center">Status</th>
              <th class="py-3 px-4 font-semibold text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#E7EBE8]">
            <tr v-for="m in messages" :key="m.id" class="hover:bg-[#F7F8FA]">
              <td class="py-3 px-4 font-medium text-[#171C1A]">
                <div>{{ m.name }}</div>
                <div class="text-[11px] text-[#69736E]">{{ m.email }}</div>
                <div v-if="m.phone" class="text-[10px] font-mono text-slate-500">{{ m.phone }}</div>
              </td>
              <td v-if="activeTab === 'wholesale'" class="py-3 px-4 font-semibold text-[#14382A]">
                {{ m.company || 'Direct Client' }}
              </td>
              <td class="py-3 px-4 text-[#171C1A] max-w-xs truncate">
                <div class="font-semibold truncate">{{ m.subject || 'No subject' }}</div>
                <div class="text-[11px] text-[#69736E] truncate">{{ m.message }}</div>
              </td>
              <td class="py-3 px-4 text-[#69736E] text-[11px] font-mono whitespace-nowrap">
                {{ new Date(m.created_at).toLocaleDateString() }}
              </td>
              <td class="py-3 px-4 text-center">
                <StatusBadge :status="m.status || (m.is_read ? 'resolved' : 'new')" />
              </td>
              <td class="py-3 px-4 text-right space-x-2 whitespace-nowrap">
                <button
                  type="button"
                  @click="openMessage(m)"
                  class="px-2.5 py-1 text-xs font-semibold text-[#14382A] bg-emerald-50 hover:bg-[#14382A] hover:text-white rounded-md transition-colors"
                >
                  View Details
                </button>
                <button
                  type="button"
                  @click="deleteMessage(m.id)"
                  class="px-2 py-1 text-xs text-rose-600 hover:bg-rose-50 rounded-md transition-colors"
                >
                  Delete
                </button>
              </td>
            </tr>
            <tr v-if="!messages.length">
              <td :colspan="activeTab === 'wholesale' ? 6 : 5" class="py-8 text-center text-slate-400">
                No inquiries in this category.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Message Detail Modal -->
    <BaseModal
      :is-open="isModalOpen"
      :title="`Inquiry from ${selectedMessage?.name || ''}`"
      size="lg"
      @close="isModalOpen = false"
    >
      <div v-if="selectedMessage" class="space-y-4 text-xs">
        <div class="grid grid-cols-2 gap-4 bg-[#F7F8FA] p-3 rounded-md border border-[#E7EBE8]">
          <div>
            <span class="text-[10px] uppercase tracking-wider text-[#69736E] block">Sender Email</span>
            <span class="font-semibold text-[#171C1A]">{{ selectedMessage.email }}</span>
          </div>
          <div>
            <span class="text-[10px] uppercase tracking-wider text-[#69736E] block">Phone / Mobile</span>
            <span class="font-semibold text-[#171C1A]">{{ selectedMessage.phone || 'N/A' }}</span>
          </div>
        </div>

        <div>
          <span class="text-[10px] uppercase tracking-wider text-[#69736E] block">Subject</span>
          <h4 class="text-sm font-semibold text-[#171C1A] mt-0.5">{{ selectedMessage.subject }}</h4>
        </div>

        <div>
          <span class="text-[10px] uppercase tracking-wider text-[#69736E] block">Message Content</span>
          <p class="text-xs text-[#171C1A] leading-relaxed bg-[#F7F8FA] p-4 rounded-md border border-[#E7EBE8] mt-1 whitespace-pre-wrap">
            {{ selectedMessage.message }}
          </p>
        </div>

        <!-- Attachment Download Link if present -->
        <div v-if="selectedMessage.attachment_path" class="p-3 bg-emerald-50 border border-emerald-200 rounded-md flex items-center justify-between">
          <span class="font-medium text-emerald-900">Attachment: {{ selectedMessage.attachment_path }}</span>
          <a :href="`/storage/${selectedMessage.attachment_path}`" target="_blank" class="text-xs font-bold text-[#14382A] underline">
            Download File ↗
          </a>
        </div>

        <!-- Status & Internal Notes -->
        <div class="space-y-3 pt-2 border-t border-[#E7EBE8]">
          <div>
            <label class="block text-xs font-semibold text-[#171C1A] mb-1">Status Workflow</label>
            <select
              v-model="selectedMessage.status"
              class="w-full bg-white border border-[#E7EBE8] rounded-md px-3 py-2 text-[#171C1A] focus:outline-none focus:ring-1 focus:ring-[#16A36A]"
            >
              <option value="new">New Inquiry</option>
              <option value="in_progress">In Progress / Assigned</option>
              <option value="resolved">Resolved</option>
              <option value="archived">Archived</option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-semibold text-[#171C1A] mb-1">Internal Staff Notes</label>
            <textarea
              v-model="selectedMessage.internal_notes"
              rows="3"
              placeholder="Add internal correspondence notes or actions taken..."
              class="w-full bg-white border border-[#E7EBE8] rounded-md p-3 text-[#171C1A] focus:outline-none focus:ring-1 focus:ring-[#16A36A]"
            ></textarea>
          </div>
        </div>

        <div class="pt-4 flex justify-end gap-3 border-t border-[#E7EBE8]">
          <button
            type="button"
            @click="isModalOpen = false"
            class="px-4 py-2 border border-[#E7EBE8] text-xs font-semibold text-[#171C1A] rounded-md hover:bg-slate-50"
          >
            Close
          </button>
          <button
            type="button"
            @click="saveMessageStatus"
            class="px-4 py-2 bg-[#14382A] hover:bg-[#16A36A] text-white text-xs font-semibold rounded-md shadow-xs transition-colors"
          >
            Save Status & Notes
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
const activeTab = ref('general');
const messages = ref([]);
const isModalOpen = ref(false);
const selectedMessage = ref(null);

const fetchMessages = async () => {
  try {
    const res = await api.get('/admin/messages', { params: { type: activeTab.value } });
    messages.value = res.data?.data?.data || [];
  } catch (err) {
    console.error('Failed to load messages', err);
  }
};

const openMessage = (m) => {
  selectedMessage.value = { ...m };
  isModalOpen.value = true;
};

const saveMessageStatus = async () => {
  try {
    await api.put(`/admin/messages/${selectedMessage.value.id}`, {
      status: selectedMessage.value.status || 'resolved',
      internal_notes: selectedMessage.value.internal_notes,
      is_read: true,
    });
    toast.success('Inquiry status updated');
    isModalOpen.value = false;
    fetchMessages();
  } catch (err) {
    toast.error('Failed to update status');
  }
};

const deleteMessage = async (id) => {
  if (!confirm('Are you sure you want to delete this inquiry?')) return;
  try {
    await api.delete(`/admin/messages/${id}`);
    toast.success('Inquiry deleted');
    fetchMessages();
  } catch (err) {
    toast.error('Failed to delete inquiry');
  }
};

onMounted(() => {
  fetchMessages();
});
</script>
