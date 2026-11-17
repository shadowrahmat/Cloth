<template>
  <div class="py-12 sm:py-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Success Confirmation State -->
    <div v-if="orderCompleted" class="max-w-2xl mx-auto bg-white p-8 sm:p-12 border border-[#D8CCBC]/40 shadow-lg text-center space-y-6">
      <div class="w-16 h-16 bg-[#111111] text-white rounded-full flex items-center justify-center mx-auto">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
      </div>

      <span class="text-xs uppercase tracking-[0.25em] text-[#A89B8A] font-medium block">Order Confirmed</span>
      <h1 class="font-serif text-3xl sm:text-4xl text-[#111111]">Thank You for Your Order</h1>

      <p class="text-xs sm:text-sm text-[#111111]/80 max-w-md mx-auto font-light leading-relaxed">
        Your order has been recorded in our atelier ledger. A detailed order receipt and dispatch timeline have been sent to <strong>{{ completedOrder.customer_email }}</strong>.
      </p>

      <div class="p-6 bg-[#F7F5F0] border border-[#D8CCBC]/60 max-w-md mx-auto text-left space-y-2 text-xs">
        <div class="flex justify-between">
          <span class="text-[#A89B8A]">Order Reference:</span>
          <span class="font-mono font-bold text-[#111111]">{{ completedOrder.order_number }}</span>
        </div>
        <div class="flex justify-between">
          <span class="text-[#A89B8A]">Order Total:</span>
          <span class="font-semibold text-[#111111]">${{ Number(completedOrder.total).toFixed(2) }}</span>
        </div>
        <div class="flex justify-between">
          <span class="text-[#A89B8A]">Payment Method:</span>
          <span class="capitalize text-[#111111]">{{ completedOrder.payment_method }}</span>
        </div>
        <div class="flex justify-between">
          <span class="text-[#A89B8A]">Status:</span>
          <span class="capitalize font-semibold text-emerald-800">{{ completedOrder.order_status }}</span>
        </div>
      </div>

      <div class="pt-6 flex justify-center gap-4">
        <router-link
          to="/"
          class="px-8 py-3.5 bg-[#111111] text-white text-xs uppercase tracking-widest font-semibold hover:bg-black transition-colors"
        >
          Return to Storefront
        </router-link>
        <router-link
          to="/track-order"
          class="px-8 py-3.5 border border-[#111111] text-[#111111] text-xs uppercase tracking-widest font-semibold hover:bg-[#F7F5F0] transition-colors"
        >
          Track Shipment
        </router-link>
      </div>
    </div>

    <!-- Active Checkout Form -->
    <div v-else class="grid grid-cols-1 lg:grid-cols-12 gap-12">
      <!-- Checkout Form (Left) -->
      <div class="lg:col-span-7 space-y-10">
        <div>
          <span class="text-xs uppercase tracking-[0.25em] text-[#A89B8A] font-medium block mb-1">Secure Checkout</span>
          <h1 class="font-serif text-3xl text-[#111111]">Client Information & Delivery</h1>
        </div>

        <form @submit.prevent="submitOrder" class="space-y-8">
          <!-- Step 1: Contact -->
          <div class="bg-white p-6 border border-[#D8CCBC]/40 shadow-2xs space-y-4">
            <h3 class="font-serif text-lg text-[#111111] border-b border-[#D8CCBC]/30 pb-2">1. Contact Information</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
              <div class="sm:col-span-2">
                <label class="block font-semibold uppercase tracking-wider mb-1">Email Address *</label>
                <input
                  type="email"
                  v-model="form.email"
                  required
                  placeholder="elena@example.com"
                  class="w-full px-4 py-2.5 bg-[#F7F5F0] border border-[#D8CCBC] text-xs focus:outline-none focus:border-[#111111]"
                />
              </div>
              <div>
                <label class="block font-semibold uppercase tracking-wider mb-1">First Name *</label>
                <input
                  type="text"
                  v-model="form.first_name"
                  required
                  class="w-full px-4 py-2.5 bg-[#F7F5F0] border border-[#D8CCBC] text-xs focus:outline-none focus:border-[#111111]"
                />
              </div>
              <div>
                <label class="block font-semibold uppercase tracking-wider mb-1">Last Name *</label>
                <input
                  type="text"
                  v-model="form.last_name"
                  required
                  class="w-full px-4 py-2.5 bg-[#F7F5F0] border border-[#D8CCBC] text-xs focus:outline-none focus:border-[#111111]"
                />
              </div>
              <div class="sm:col-span-2">
                <label class="block font-semibold uppercase tracking-wider mb-1">Telephone *</label>
                <input
                  type="tel"
                  v-model="form.phone"
                  required
                  placeholder="+1 (555) 000-0000"
                  class="w-full px-4 py-2.5 bg-[#F7F5F0] border border-[#D8CCBC] text-xs focus:outline-none focus:border-[#111111]"
                />
              </div>
            </div>
          </div>

          <!-- Step 2: Delivery Address -->
          <div class="bg-white p-6 border border-[#D8CCBC]/40 shadow-2xs space-y-4">
            <h3 class="font-serif text-lg text-[#111111] border-b border-[#D8CCBC]/30 pb-2">2. Shipping Destination</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
              <div class="sm:col-span-2">
                <label class="block font-semibold uppercase tracking-wider mb-1">Street Address *</label>
                <input
                  type="text"
                  v-model="form.address_line_1"
                  required
                  placeholder="House / Building, Street Name"
                  class="w-full px-4 py-2.5 bg-[#F7F5F0] border border-[#D8CCBC] text-xs focus:outline-none focus:border-[#111111]"
                />
              </div>
              <div class="sm:col-span-2">
                <label class="block font-semibold uppercase tracking-wider mb-1">Apartment, Suite, Unit (Optional)</label>
                <input
                  type="text"
                  v-model="form.address_line_2"
                  class="w-full px-4 py-2.5 bg-[#F7F5F0] border border-[#D8CCBC] text-xs focus:outline-none focus:border-[#111111]"
                />
              </div>
              <div>
                <label class="block font-semibold uppercase tracking-wider mb-1">City *</label>
                <input
                  type="text"
                  v-model="form.city"
                  required
                  class="w-full px-4 py-2.5 bg-[#F7F5F0] border border-[#D8CCBC] text-xs focus:outline-none focus:border-[#111111]"
                />
              </div>
              <div>
                <label class="block font-semibold uppercase tracking-wider mb-1">State / Province</label>
                <input
                  type="text"
                  v-model="form.state"
                  class="w-full px-4 py-2.5 bg-[#F7F5F0] border border-[#D8CCBC] text-xs focus:outline-none focus:border-[#111111]"
                />
              </div>
              <div>
                <label class="block font-semibold uppercase tracking-wider mb-1">Postal / ZIP Code *</label>
                <input
                  type="text"
                  v-model="form.postal_code"
                  required
                  class="w-full px-4 py-2.5 bg-[#F7F5F0] border border-[#D8CCBC] text-xs focus:outline-none focus:border-[#111111]"
                />
              </div>
              <div>
                <label class="block font-semibold uppercase tracking-wider mb-1">Country</label>
                <select
                  v-model="form.country"
                  class="w-full px-4 py-2.5 bg-[#F7F5F0] border border-[#D8CCBC] text-xs focus:outline-none focus:border-[#111111]"
                >
                  <option value="United States">United States</option>
                  <option value="United Kingdom">United Kingdom</option>
                  <option value="France">France</option>
                  <option value="Italy">Italy</option>
                  <option value="Canada">Canada</option>
                  <option value="Australia">Australia</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Step 3: Shipping Method Selection -->
          <div class="bg-white p-6 border border-[#D8CCBC]/40 shadow-2xs space-y-4">
            <h3 class="font-serif text-lg text-[#111111] border-b border-[#D8CCBC]/30 pb-2">3. Courier Service</h3>
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
                  {{ getShippingMethodCost(method) === 0 ? 'Complimentary' : `$${getShippingMethodCost(method).toFixed(2)}` }}
                </span>
              </label>
            </div>
          </div>

          <!-- Step 4: Payment Method -->
          <div class="bg-white p-6 border border-[#D8CCBC]/40 shadow-2xs space-y-4">
            <h3 class="font-serif text-lg text-[#111111] border-b border-[#D8CCBC]/30 pb-2">4. Payment Authorization</h3>
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
                  <h5 class="text-xs font-semibold text-[#111111]">Cash on Delivery (White Glove Courier)</h5>
                  <p class="text-[11px] text-[#A89B8A]">Inspect garments upon doorstep arrival and settle in cash or card terminal.</p>
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
                  <h5 class="text-xs font-semibold text-[#111111]">Credit / Debit Card (Stripe Secured)</h5>
                  <p class="text-[11px] text-[#A89B8A]">Visa, Mastercard, American Express, Apple Pay.</p>
                </div>
              </label>

              <!-- Simulated Card Fields if Card selected -->
              <div v-if="form.payment_method === 'stripe'" class="p-4 bg-white border border-[#D8CCBC] space-y-3 text-xs mt-2">
                <div>
                  <label class="block font-semibold mb-1">Card Number</label>
                  <input type="text" placeholder="•••• •••• •••• 4242" class="w-full px-3 py-2 border border-[#D8CCBC]" />
                </div>
                <div class="grid grid-cols-2 gap-3">
                  <div>
                    <label class="block font-semibold mb-1">Expiration (MM/YY)</label>
                    <input type="text" placeholder="12/28" class="w-full px-3 py-2 border border-[#D8CCBC]" />
                  </div>
                  <div>
                    <label class="block font-semibold mb-1">CVV Security Code</label>
                    <input type="text" placeholder="•••" class="w-full px-3 py-2 border border-[#D8CCBC]" />
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Order Notes -->
          <div class="bg-white p-6 border border-[#D8CCBC]/40 shadow-2xs space-y-2">
            <label class="block text-xs font-semibold uppercase tracking-wider text-[#111111]">Client Instructions / Doorstep Notes</label>
            <textarea
              rows="2"
              v-model="form.order_notes"
              placeholder="e.g. Leave package with front desk concierge..."
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
            {{ submitting ? 'Authorizing & Submitting Order...' : `Complete Order — $${orderTotal.toFixed(2)}` }}
          </button>
        </form>
      </div>

      <!-- Order Review Sidebar (Right) -->
      <div class="lg:col-span-5 bg-white p-6 sm:p-8 border border-[#D8CCBC]/40 shadow-xs lg:sticky lg:top-28 space-y-6">
        <h3 class="font-serif text-xl text-[#111111] border-b border-[#D8CCBC]/40 pb-4">
          Bag Review ({{ cartStore.itemCount }})
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
              ${{ (item.price * item.quantity).toFixed(2) }}
            </span>
          </div>
        </div>

        <!-- Cost Breakdown -->
        <div class="pt-4 border-t border-[#D8CCBC]/40 space-y-2.5 text-xs text-[#111111]">
          <div class="flex justify-between">
            <span>Subtotal</span>
            <span>${{ cartStore.subtotal.toFixed(2) }}</span>
          </div>

          <div v-if="cartStore.appliedCoupon" class="flex justify-between text-emerald-800">
            <span>Discount ({{ cartStore.appliedCoupon.code }})</span>
            <span>-${{ cartStore.appliedCoupon.discount_amount.toFixed(2) }}</span>
          </div>

          <div class="flex justify-between">
            <span>Shipping</span>
            <span>{{ selectedShippingCost === 0 ? 'Complimentary' : `$${selectedShippingCost.toFixed(2)}` }}</span>
          </div>

          <div class="flex justify-between font-serif text-xl font-semibold border-t border-[#D8CCBC]/40 pt-4">
            <span>Total</span>
            <span>${{ orderTotal.toFixed(2) }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
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
  country: 'United States',
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

const orderTotal = computed(() => {
  const sub = cartStore.subtotal - (cartStore.appliedCoupon?.discount_amount || 0);
  return Math.max(0, sub + selectedShippingCost.value);
});

const submitOrder = async () => {
  if (!cartStore.items.length) {
    errorMessage.value = 'Your shopping bag is empty.';
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
    errorMessage.value = err.response?.data?.message || 'Error processing checkout. Please check inputs.';
  } finally {
    submitting.value = false;
  }
};

onMounted(async () => {
  try {
    const res = await api.get('/public/shipping-methods');
    shippingMethods.value = res.data.data || [];
    if (shippingMethods.value.length) {
      form.shipping_method = shippingMethods.value[0].code;
    }
  } catch (err) {
    console.error('Failed to load shipping methods', err);
  }
});
</script>
