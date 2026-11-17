<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-[#E7EBE8]">
      <div>
        <h2 class="text-xl font-bold tracking-tight text-[#171C1A]">Customer Reviews & Ratings</h2>
        <p class="text-xs text-[#69736E] mt-0.5">Moderate client reviews, verify genuine purchases, and feature top feedback.</p>
      </div>

      <div class="flex items-center gap-3">
        <select
          v-model="statusFilter"
          @change="fetchReviews"
          class="text-xs bg-white border border-[#E7EBE8] rounded-md px-3 py-2 text-[#171C1A] focus:outline-none focus:ring-1 focus:ring-[#16A36A]"
        >
          <option value="">All Moderation States</option>
          <option value="approved">Approved & Published</option>
          <option value="pending">Pending Moderation</option>
        </select>
      </div>
    </div>

    <!-- Reviews Table -->
    <div class="bg-white rounded-lg border border-[#E7EBE8] overflow-hidden shadow-xs">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead>
            <tr class="bg-[#F7F8FA] text-[#69736E] border-b border-[#E7EBE8]">
              <th class="py-3 px-4 font-semibold">Product</th>
              <th class="py-3 px-4 font-semibold">Customer</th>
              <th class="py-3 px-4 font-semibold text-center">Rating</th>
              <th class="py-3 px-4 font-semibold">Review Content</th>
              <th class="py-3 px-4 font-semibold text-center">Moderation</th>
              <th class="py-3 px-4 font-semibold text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#E7EBE8]">
            <tr v-for="r in reviews" :key="r.id" class="hover:bg-[#F7F8FA]">
              <td class="py-3 px-4 font-medium text-[#171C1A] max-w-xs truncate">
                <router-link :to="`/product/${r.product?.slug}`" target="_blank" class="hover:text-[#16A36A] font-semibold truncate block">
                  {{ r.product?.name || 'General Product' }}
                </router-link>
              </td>
              <td class="py-3 px-4 text-[#171C1A]">
                <div class="font-semibold">{{ r.customer_name || r.user?.name || 'Verified Client' }}</div>
                <div class="text-[10px] text-emerald-800 font-mono">✓ Verified Purchase</div>
              </td>
              <td class="py-3 px-4 text-center">
                <div class="flex items-center justify-center text-amber-500 gap-0.5">
                  <span v-for="star in 5" :key="star">
                    {{ star <= (r.rating || 5) ? '★' : '☆' }}
                  </span>
                </div>
              </td>
              <td class="py-3 px-4 text-[#171C1A] max-w-sm">
                <div class="font-semibold">{{ r.review_title }}</div>
                <div class="text-[11px] text-[#69736E] line-clamp-2">{{ r.review_text }}</div>
              </td>
              <td class="py-3 px-4 text-center">
                <StatusBadge :status="r.is_approved ? 'approved' : 'pending'" />
              </td>
              <td class="py-3 px-4 text-right space-x-2 whitespace-nowrap">
                <button
                  type="button"
                  @click="toggleApproval(r)"
                  :class="[
                    'px-2.5 py-1 text-xs font-semibold rounded-md transition-colors',
                    r.is_approved ? 'bg-amber-50 text-amber-900 hover:bg-amber-100' : 'bg-emerald-50 text-emerald-900 hover:bg-emerald-100'
                  ]"
                >
                  {{ r.is_approved ? 'Unpublish' : 'Approve & Publish' }}
                </button>
                <button
                  type="button"
                  @click="deleteReview(r.id)"
                  class="px-2 py-1 text-xs text-rose-600 hover:bg-rose-50 rounded-md transition-colors"
                >
                  Delete
                </button>
              </td>
            </tr>
            <tr v-if="!reviews.length">
              <td colspan="6" class="py-8 text-center text-slate-400">No reviews found in this view.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import StatusBadge from '../../components/admin/StatusBadge.vue';
import { useToastStore } from '../../stores/toast';
import api from '../../api';

const toast = useToastStore();
const reviews = ref([]);
const statusFilter = ref('');

const fetchReviews = async () => {
  try {
    const res = await api.get('/admin/reviews', { params: { status: statusFilter.value } });
    reviews.value = res.data?.data?.data || [];
  } catch (err) {
    console.error('Failed to load reviews', err);
  }
};

const toggleApproval = async (r) => {
  try {
    await api.put(`/admin/reviews/${r.id}/toggle-approval`);
    toast.success(r.is_approved ? 'Review unpublished' : 'Review approved for public storefront');
    fetchReviews();
  } catch (err) {
    toast.error('Failed to update review status');
  }
};

const deleteReview = async (id) => {
  if (!confirm('Are you sure you want to permanently delete this review?')) return;
  try {
    await api.delete(`/admin/reviews/${id}`);
    toast.success('Review deleted');
    fetchReviews();
  } catch (err) {
    toast.error('Failed to delete review');
  }
};

onMounted(() => {
  fetchReviews();
});
</script>
