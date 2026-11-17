<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-[#E7EBE8]">
      <div>
        <h2 class="text-xl font-bold tracking-tight text-[#171C1A]">Security Audit Logs</h2>
        <p class="text-xs text-[#69736E] mt-0.5">Tamper-resistant audit trail recording staff actions, administrative events, and security access logs.</p>
      </div>

      <div class="flex items-center gap-3">
        <select
          v-model="actionFilter"
          @change="fetchLogs"
          class="text-xs bg-white border border-[#E7EBE8] rounded-md px-3 py-2 text-[#171C1A] focus:outline-none focus:ring-1 focus:ring-[#16A36A]"
        >
          <option value="">All Security Events</option>
          <option value="login">Staff Logins</option>
          <option value="created">Created Records</option>
          <option value="updated">Updated Records</option>
          <option value="deleted">Deleted Records</option>
          <option value="denied">Authorization Failures</option>
        </select>
      </div>
    </div>

    <!-- Audit Logs Table -->
    <div class="bg-white rounded-lg border border-[#E7EBE8] overflow-hidden shadow-xs">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead>
            <tr class="bg-[#F7F8FA] text-[#69736E] border-b border-[#E7EBE8]">
              <th class="py-3 px-4 font-semibold">Timestamp</th>
              <th class="py-3 px-4 font-semibold">Staff User</th>
              <th class="py-3 px-4 font-semibold">Action / Event</th>
              <th class="py-3 px-4 font-semibold">Event Description</th>
              <th class="py-3 px-4 font-semibold">IP Address</th>
              <th class="py-3 px-4 font-semibold text-right">Device / Client</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#E7EBE8]">
            <tr v-for="log in logs" :key="log.id" class="hover:bg-[#F7F8FA]">
              <td class="py-3 px-4 font-mono text-[#69736E] whitespace-nowrap text-[11px]">
                {{ new Date(log.created_at).toLocaleString() }}
              </td>
              <td class="py-3 px-4 font-medium text-[#171C1A]">
                <div class="font-semibold">{{ log.user?.name || 'System Guard' }}</div>
                <div class="text-[10px] text-[#69736E] font-mono">{{ log.user?.email || 'System / Daemon' }}</div>
              </td>
              <td class="py-3 px-4">
                <span
                  :class="[
                    'px-2 py-0.5 rounded text-[10px] font-mono uppercase font-bold',
                    log.action === 'deleted' ? 'bg-rose-100 text-rose-800' :
                    log.action === 'login' ? 'bg-sky-100 text-sky-800' :
                    log.action === 'created' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-800'
                  ]"
                >
                  {{ log.action }}
                </span>
              </td>
              <td class="py-3 px-4 text-[#171C1A]">
                <span class="font-medium">{{ log.description || log.action }}</span>
                <span v-if="log.model_type" class="text-[11px] text-[#69736E] block font-mono">
                  Target: {{ log.model_type }} #{{ log.model_id }}
                </span>
              </td>
              <td class="py-3 px-4 font-mono text-[#14382A]">
                {{ log.ip_address || '127.0.0.1' }}
              </td>
              <td class="py-3 px-4 text-right text-[11px] text-[#69736E] max-w-xs truncate font-mono">
                {{ log.user_agent ? log.user_agent.substring(0, 32) + '...' : 'Internal API Client' }}
              </td>
            </tr>
            <tr v-if="!logs.length">
              <td colspan="6" class="py-8 text-center text-slate-400">No security audit logs found for this filter.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../../api';

const logs = ref([]);
const actionFilter = ref('');

const fetchLogs = async () => {
  try {
    const res = await api.get('/admin/audit-logs', { params: { action: actionFilter.value } });
    logs.value = res.data?.data?.data || [];
  } catch (err) {
    console.error('Failed to load audit logs', err);
  }
};

onMounted(() => {
  fetchLogs();
});
</script>
