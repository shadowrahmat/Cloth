<template>
  <div class="py-12 sm:py-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Success Confirmation State -->
    <div v-if="orderCompleted" class="max-w-2xl mx-auto bg-white p-8 sm:p-12 border border-[#D8CCBC]/40 shadow-lg text-center space-y-6">
      <div class="w-16 h-16 bg-[#111111] text-white rounded-full flex items-center justify-center mx-auto">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
      </div>

      <span class="text-xs uppercase tracking-[0.25em] text-[#16A36A] font-medium block">অর্ডার কনফার্ম হয়েছে</span>
      <h1 class="font-serif text-3xl sm:text-4xl text-[#111111]">আপনার অর্ডারের জন্য আন্তরিক ধন্যবাদ!</h1>

      <p class="text-xs sm:text-sm text-[#111111]/80 max-w-md mx-auto font-light leading-relaxed">
        আপনার অর্ডারটি সফলভাবে গ্রহণ করা হয়েছে। বিস্তারিত রসিদ ও ডেলিভারি আপডেট <strong>{{ completedOrder.customer_email }}</strong> ঠিকানায় পাঠানো হয়েছে।
      </p>

      <div class="p-6 bg-[#F7F5F0] border border-[#D8CCBC]/60 max-w-md mx-auto text-left space-y-2 text-xs">
        <div class="flex justify-between">
          <span class="text-[#A89B8A]">অর্ডার নম্বর:</span>
          <span class="font-mono font-bold text-[#111111]">{{ completedOrder.order_number }}</span>
        </div>
        <div class="flex justify-between">
          <span class="text-[#A89B8A]">সর্বমোট প্রদেয়:</span>
          <span class="font-semibold text-[#111111]">৳ {{ Number(completedOrder.total).toLocaleString() }}</span>
        </div>
        <div class="flex justify-between">
          <span class="text-[#A89B8A]">পেমেন্ট মাধ্যম:</span>
          <span class="capitalize text-[#111111]">{{ completedOrder.payment_method === 'cod' ? 'ক্যাশ অন ডেলিভারি' : completedOrder.payment_method }}</span>
        </div>
        <div class="flex justify-between">
          <span class="text-[#A89B8A]">অর্ডার স্ট্যাটাস:</span>
          <span class="capitalize font-semibold text-emerald-800">{{ completedOrder.order_status }}</span>
        </div>
      </div>

      <div class="pt-6 flex justify-center gap-4">
        <router-link
          to="/"
          class="px-8 py-3.5 bg-[#111111] text-white text-xs uppercase tracking-widest font-semibold hover:bg-black transition-colors"
        >
          হোমপেজে ফিরে যান
        </router-link>
        <router-link
          to="/track-order"
          class="px-8 py-3.5 border border-[#111111] text-[#111111] text-xs uppercase tracking-widest font-semibold hover:bg-[#F7F5F0] transition-colors"
        >
          অর্ডার ট্র্যাক করুন
        </router-link>
      </div>
    </div>

    <!-- Active Checkout Form -->
    <div v-else class="grid grid-cols-1 lg:grid-cols-12 gap-12">
      <!-- Checkout Form (Left) -->
      <div class="lg:col-span-7 space-y-10">
        <div>
          <span class="text-xs uppercase tracking-[0.25em] text-[#A89B8A] font-medium block mb-1">নিরাপদ চেকআউট</span>
          <h1 class="font-serif text-3xl text-[#111111]">গ্রাহকের তথ্য ও ডেলিভারি ঠিকানা</h1>
        </div>

        <form @submit.prevent="submitOrder" class="space-y-8">
          <!-- Step 1: Contact -->
          <div class="bg-white p-6 border border-[#D8CCBC]/40 shadow-2xs space-y-4">
            <h3 class="font-serif text-lg text-[#111111] border-b border-[#D8CCBC]/30 pb-2">১. যোগাযোগের তথ্য</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
              <div class="sm:col-span-2">
                <label class="block font-semibold uppercase tracking-wider mb-1">ইমেইল ঠিকানা *</label>
                <input
                  type="email"
                  v-model="form.email"
                  required
                  placeholder="name@example.com"
                  class="w-full px-4 py-2.5 bg-[#F7F5F0] border border-[#D8CCBC] text-xs focus:outline-none focus:border-[#111111]"
                />
              </div>
              <div>
                <label class="block font-semibold uppercase tracking-wider mb-1">নামের প্রথম অংশ *</label>
                <input
                  type="text"
                  v-model="form.first_name"
                  required
                  class="w-full px-4 py-2.5 bg-[#F7F5F0] border border-[#D8CCBC] text-xs focus:outline-none focus:border-[#111111]"
                />
              </div>
              <div>
                <label class="block font-semibold uppercase tracking-wider mb-1">নামের শেষ অংশ *</label>
                <input
                  type="text"
                  v-model="form.last_name"
                  required
                  class="w-full px-4 py-2.5 bg-[#F7F5F0] border border-[#D8CCBC] text-xs focus:outline-none focus:border-[#111111]"
                />
              </div>
              <div class="sm:col-span-2">
                <label class="block font-semibold uppercase tracking-wider mb-1">মোবাইল নম্বর *</label>
                <input
                  type="tel"
                  v-model="form.phone"
                  required
                  placeholder="017XXXXXXXX"
                  class="w-full px-4 py-2.5 bg-[#F7F5F0] border border-[#D8CCBC] text-xs focus:outline-none focus:border-[#111111]"
                />
              </div>
            </div>
          </div>

          <!-- Step 2: Delivery Address -->
          <div class="bg-white p-6 border border-[#D8CCBC]/40 shadow-2xs space-y-4">
            <h3 class="font-serif text-lg text-[#111111] border-b border-[#D8CCBC]/30 pb-2">২. ডেলিভারির পূর্ণাঙ্গ ঠিকানা</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
              <div class="sm:col-span-2">
                <label class="block font-semibold uppercase tracking-wider mb-1">ঠিকানা (বাসা/ফ্ল্যাট, রোড, এলাকা) *</label>
                <input
                  type="text"
                  v-model="form.address_line_1"
                  required
                  placeholder="বাড়ি নং, রোড নং, এলাকার নাম"
                  class="w-full px-4 py-2.5 bg-[#F7F5F0] border border-[#D8CCBC] text-xs focus:outline-none focus:border-[#111111]"
                />
              </div>
              <div class="sm:col-span-2">
                <label class="block font-semibold uppercase tracking-wider mb-1">ল্যান্ডমার্ক / অতিরিক্ত তথ্য (ঐচ্ছিক)</label>
                <input
                  type="text"
                  v-model="form.address_line_2"
                  placeholder="কাছের পরিচিত স্থান বা ফ্লোর নম্বর"
                  class="w-full px-4 py-2.5 bg-[#F7F5F0] border border-[#D8CCBC] text-xs focus:outline-none focus:border-[#111111]"
                />
              </div>
              <div>
                <label class="block font-semibold uppercase tracking-wider mb-1">জেলা / শহর *</label>
                <input
                  type="text"
                  v-model="form.city"
                  required
                  placeholder="ঢাকা"
                  class="w-full px-4 py-2.5 bg-[#F7F5F0] border border-[#D8CCBC] text-xs focus:outline-none focus:border-[#111111]"
                />
              </div>
              <div>
                <label class="block font-semibold uppercase tracking-wider mb-1">বিভাগ</label>
                <input
                  type="text"
                  v-model="form.state"
                  placeholder="ঢাকা"
                  class="w-full px-4 py-2.5 bg-[#F7F5F0] border border-[#D8CCBC] text-xs focus:outline-none focus:border-[#111111]"
                />
              </div>
              <div>
                <label class="block font-semibold uppercase tracking-wider mb-1">পোস্ট কোড *</label>
                <input
                  type="text"
                  v-model="form.postal_code"
                  required
                  placeholder="১২১২"
                  class="w-full px-4 py-2.5 bg-[#F7F5F0] border border-[#D8CCBC] text-xs focus:outline-none focus:border-[#111111]"
                />
              </div>
              <div>
                <label class="block font-semibold uppercase tracking-wider mb-1">দেশ</label>
                <select
                  v-model="form.country"
                  class="w-full px-4 py-2.5 bg-[#F7F5F0] border border-[#D8CCBC] text-xs focus:outline-none focus:border-[#111111]"
                >
                  <option value="Bangladesh">বাংলাদেশ (Bangladesh)</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Step 3: Shipping Method Selection -->
          <div class="bg-white p-6 border border-[#D8CCBC]/40 shadow-2xs space-y-4">
            <h3 class="font-serif text-lg text-[#111111] border-b border-[#D8CCBC]/30 pb-2">৩. ডেলিভারি ও কুরিয়ার সার্ভিস</h3>
            <div class="space-y-3">
              <label
                v-for="method in shippingMethods"
                :key="method.code"
                :class="[
                  'flex items-center justify-between p-4 border cursor-pointer transition-all',
                  form.shipping_method === method.code ? 'border-[#111111] bg-[#F7F5F0]' : 'border-[#D8CCBC]/50 bg-white hover:border-[#111111]'
                ]"
              >
                <div class="flex items-center gap-3">
                  <input
                    type="radio"
                    name="shipping_method"
                    :value="method.code"
                    v-model="form.shipping_method"
                    class="text-[#111111] focus:ring-black"
                  />
                  <div>
                    <h5 class="text-xs font-semibold text-[#111111]">{{ method.name }}</h5>
                    <p class="text-[11px] text-[#A89B8A]">{{ method.description }}</p>
                  </div>
                </div>
                <span class="text-xs font-semibold text-[#111111]">
                  {{ getShippingMethodCost(method) === 0 ? 'ফ্রি ডেলিভারি' : `৳${getShippingMethodCost(method).toFixed(0)}` }}
                </span>
              </label>
            </div>
          </div>

          <!-- Step 4: Payment Method -->
          <div class="bg-white p-6 border border-[#D8CCBC]/40 shadow-2xs space-y-4">
            <h3 class="font-serif text-lg text-[#111111] border-b border-[#D8CCBC]/30 pb-2">৪. পেমেন্ট পদ্ধতি</h3>
            <div class="space-y-3">
              <label
                :class="[
                  'flex items-center gap-3 p-4 border cursor-pointer transition-all',
                  form.payment_method === 'cod' ? 'border-[#111111] bg-[#F7F5F0]' : 'border-[#D8CCBC]/50 bg-white'
                ]"
              >
                <input
                  type="radio"
                  name="payment_method"
                  value="cod"
                  v-model="form.payment_method"
                  class="text-[#111111] focus:ring-black"
                />
                <div>
                  <h5 class="text-xs font-semibold text-[#111111]">ক্যাশ অন ডেলিভারি (পণ্য পেয়ে মূল্য পরিশোধ)</h5>
                  <p class="text-[11px] text-[#A89B8A]">হোম ডেলিভারিতে পোশাক হাতে পেয়ে নগদ বা মোবাইল ব্যাংকিংয়ে টাকা দিন।</p>
                </div>
              </label>

              <label
                :class="[
                  'flex items-center gap-3 p-4 border cursor-pointer transition-all',
                  form.payment_method === 'stripe' ? 'border-[#111111] bg-[#F7F5F0]' : 'border-[#D8CCBC]/50 bg-white'
                ]"
              >
                <input
                  type="radio"
                  name="payment_method"
                  value="stripe"
                  v-model="form.payment_method"
                  class="text-[#111111] focus:ring-black"
                />
                <div>
                  <h5 class="text-xs font-semibold text-[#111111]">অনলাইন পেমেন্ট / কার্ড (ভিসা, মাস্টারকার্ড, বিকাশ)</h5>
                  <p class="text-[11px] text-[#A89B8A]">নিরাপদ ডিজিটাল গেটওয়ের মাধ্যমে সরাসরি পেমেন্ট করুন।</p>
                </div>
              </label>

              <!-- Simulated Card Fields if Card selected -->
              <div v-if="form.payment_method === 'stripe'" class="p-4 bg-white border border-[#D8CCBC] space-y-3 text-xs mt-2">
                <div>
                  <label class="block font-semibold mb-1">কার্ড নম্বর</label>
                  <input type="text" placeholder="•••• •••• •••• 4242" class="w-full px-3 py-2 border border-[#D8CCBC]" />
                </div>
                <div class="grid grid-cols-2 gap-3">
                  <div>
                    <label class="block font-semibold mb-1">মেয়াদ (MM/YY)</label>
                    <input type="text" placeholder="12/28" class="w-full px-3 py-2 border border-[#D8CCBC]" />
                  </div>
                  <div>
                    <label class="block font-semibold mb-1">CVV সিকিউরিটি কোড</label>
                    <input type="text" placeholder="•••" class="w-full px-3 py-2 border border-[#D8CCBC]" />
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Order Notes -->
          <div class="bg-white p-6 border border-[#D8CCBC]/40 shadow-2xs space-y-2">
            <label class="block text-xs font-semibold uppercase tracking-wider text-[#111111]">অর্ডার সংক্রান্ত বিশেষ কোনো নোট (ঐচ্ছিক)</label>
            <textarea
              rows="2"
              v-model="form.order_notes"
              placeholder="যেমন: ডেলিভারির পূর্বে ফোন করবেন..."
              class="w-full px-4 py-2.5 bg-[#F7F5F0] border border-[#D8CCBC] text-xs focus:outline-none focus:border-[#111111]"
            />
          </div>

          <!-- Error Message Banner -->
          <div v-if="errorMessage" class="p-4 bg-rose-50 border border-rose-200 text-rose-800 text-xs font-medium">
            {{ errorMessage }}
          </div>

          <!-- Submit Button -->
          <button
            type="submit"
            :disabled="submitting || !cartStore.items.length"
            class="w-full py-4 bg-[#111111] hover:bg-black text-white text-xs uppercase tracking-[0.2em] font-semibold transition-colors disabled:opacity-40 shadow-lg"
          >
            {{ submitting ? 'অর্ডার প্রসেস হচ্ছে...' : `অর্ডার কনফার্ম করুন — ৳${Number(orderTotal).toLocaleString()}` }}
          </button>
        </form>
      </div>

      <!-- Order Review Sidebar (Right) -->
      <div class="lg:col-span-5 bg-white p-6 sm:p-8 border border-[#D8CCBC]/40 shadow-xs lg:sticky lg:top-28 space-y-6">
        <h3 class="font-serif text-xl text-[#111111] border-b border-[#D8CCBC]/40 pb-4">
          অর্ডার সামারি ({{ cartStore.itemCount }}টি পণ্য)
        </h3>

        <!-- Items list -->
        <div class="divide-y divide-[#D8CCBC]/30 max-h-80 overflow-y-auto pr-2">
          <div
            v-for="item in cartStore.items"
            :key="item.id"
            class="py-3 first:pt-0 flex gap-4 items-center"
          >
            <div class="relative w-14 h-18 bg-[#F7F5F0] overflow-hidden shrink-0 border border-[#D8CCBC]/40">
              <img :src="item.image" :alt="item.name" class="w-full h-full object-cover" />
              <span class="absolute top-0 right-0 bg-[#111111] text-white text-[9px] w-4 h-4 flex items-center justify-center font-bold">
                {{ item.quantity }}
              </span>
            </div>
            <div class="flex-1 min-w-0">
              <h5 class="font-serif text-xs text-[#111111] truncate">{{ item.name }}</h5>
              <p class="text-[10px] text-[#A89B8A]">
                <span v-if="item.color">{{ item.color }}</span>
                <span v-if="item.color && item.size"> / </span>
                <span v-if="item.size">{{ item.size }}</span>
              </p>
            </div>
            <span class="text-xs font-semibold text-[#111111]">
              ৳ {{ Number(item.price * item.quantity).toLocaleString() }}
            </span>
          </div>
        </div>

        <!-- Cost Breakdown -->
        <div class="pt-4 border-t border-[#D8CCBC]/40 space-y-2.5 text-xs text-[#111111]">
          <div class="flex justify-between">
            <span>মোট পণ্যের মূল্য</span>
            <span>৳ {{ Number(cartStore.subtotal).toLocaleString() }}</span>
          </div>

          <div v-if="cartStore.appliedCoupon" class="flex justify-between text-emerald-800">
            <span>কুপন ছাড় ({{ cartStore.appliedCoupon.code }})</span>
            <span>-৳ {{ Number(cartStore.appliedCoupon.discount_amount).toLocaleString() }}</span>
          </div>

          <div class="flex justify-between">
            <span>ডেলিভারি চার্জ</span>
            <span>{{ selectedShippingCost === 0 ? 'ফ্রি ডেলিভারি' : `৳${Number(selectedShippingCost).toLocaleString()}` }}</span>
          </div>

          <div class="flex justify-between font-serif text-xl font-semibold border-t border-[#D8CCBC]/40 pt-4">
            <span>সর্বমোট প্রদেয়</span>
            <span>৳ {{ Number(orderTotal).toLocaleString() }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue';
import { useCartStore } from '../../stores/cart';
import { useAuthStore } from '../../stores/auth';
import api from '../../api';

const cartStore = useCartStore();
const authStore = useAuthStore();

const shippingMethods = ref([]);
const submitting = ref(false);
const errorMessage = ref('');
const orderCompleted = ref(false);
const completedOrder = ref({});

const form = reactive({
  email: authStore.customerUser?.email || '',
  first_name: authStore.customerUser?.name?.split(' ')[0] || '',
  last_name: authStore.customerUser?.name?.split(' ')[1] || '',
  phone: authStore.customerUser?.phone || '',
  address_line_1: '',
  address_line_2: '',
  city: '',
  state: '',
  postal_code: '',
  country: 'Bangladesh',
  shipping_method: 'standard',
  payment_method: 'cod',
  order_notes: '',
});

const selectedShippingCost = computed(() => {
  const method = shippingMethods.value.find(m => m.code === form.shipping_method);
  if (!method) return 0;
  return getShippingMethodCost(method);
});

const getShippingMethodCost = (method) => {
  if (method.free_threshold && cartStore.subtotal >= Number(method.free_threshold)) {
    return 0.0;
  }
  return Number(method.rate);
};

// Keep the cart drawer's delivery area in sync if it's changed here.
watch(() => form.shipping_method, (code) => {
  if (!code) return;
  try { localStorage.setItem('cart_delivery_area', code); } catch { /* storage blocked */ }
});

const orderTotal = computed(() => {
  const sub = cartStore.subtotal - (cartStore.appliedCoupon?.discount_amount || 0);
  return Math.max(0, sub + selectedShippingCost.value);
});

const submitOrder = async () => {
  if (!cartStore.items.length) {
    errorMessage.value = 'আপনার শপিং ব্যাগ খালি রয়েছে।';
    return;
  }

  submitting.value = true;
  errorMessage.value = '';

  try {
    const payload = {
      ...form,
      coupon_code: cartStore.appliedCoupon?.code || undefined,
    };

    const res = await api.post('/public/checkout', payload);
    completedOrder.value = res.data.data;
    orderCompleted.value = true;
    cartStore.items = [];
    cartStore.subtotal = 0;
    cartStore.totalItems = 0;
  } catch (err) {
    errorMessage.value = err.response?.data?.message || 'অর্ডার সম্পন্ন করতে সমস্যা হয়েছে। তথ্যগুলো পুনরায় চেক করুন।';
  } finally {
    submitting.value = false;
  }
};

onMounted(async () => {
  try {
    const res = await api.get('/public/shipping-methods');
    shippingMethods.value = res.data.data || [];
    if (shippingMethods.value.length) {
      // Pre-select the delivery area chosen in the cart drawer, if still offered.
      let chosen = null;
      try { chosen = localStorage.getItem('cart_delivery_area'); } catch { /* storage blocked */ }
      form.shipping_method = shippingMethods.value.some((m) => m.code === chosen) ? chosen : shippingMethods.value[0].code;
    }
  } catch (err) {
    console.error('Failed to load shipping methods', err);
  }
});
</script>
