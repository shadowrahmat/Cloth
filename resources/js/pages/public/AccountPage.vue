<template>
  <div class="py-12 sm:py-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Unauthenticated View: Login / Register Tabs -->
    <div v-if="!authStore.isCustomerAuthenticated" class="max-w-md mx-auto bg-white p-8 border border-[#D8CCBC]/40 shadow-xs">
      <div class="flex border-b border-[#D8CCBC]/40 mb-8">
        <button
          type="button"
          @click="activeAuthTab = 'login'"
          :class="[
            'flex-1 py-3 text-xs uppercase tracking-widest font-semibold border-b-2 transition-colors',
            activeAuthTab === 'login' ? 'border-[#111111] text-[#111111]' : 'border-transparent text-[#A89B8A] hover:text-[#111111]'
          ]"
        >
          লগইন
        </button>
        <button
          type="button"
          @click="activeAuthTab = 'register'"
          :class="[
            'flex-1 py-3 text-xs uppercase tracking-widest font-semibold border-b-2 transition-colors',
            activeAuthTab === 'register' ? 'border-[#111111] text-[#111111]' : 'border-transparent text-[#A89B8A] hover:text-[#111111]'
          ]"
        >
          নতুন অ্যাকাউন্ট
        </button>
      </div>

      <!-- Login Form -->
      <form v-if="activeAuthTab === 'login'" @submit.prevent="handleLogin" class="space-y-4 text-xs">
        <div>
          <label class="block font-semibold uppercase tracking-wider mb-1">ইমেইল ঠিকানা</label>
          <input
            type="email"
            v-model="loginForm.email"
            required
            placeholder="customer@cloth.test"
            class="w-full px-4 py-2.5 bg-[#F7F5F0] border border-[#D8CCBC]"
          />
        </div>
        <div>
          <label class="block font-semibold uppercase tracking-wider mb-1">পাসওয়ার্ড</label>
          <input
            type="password"
            v-model="loginForm.password"
            required
            placeholder="পাসওয়ার্ড লিখুন"
            class="w-full px-4 py-2.5 bg-[#F7F5F0] border border-[#D8CCBC]"
          />
        </div>
        <p v-if="authError" class="text-rose-600">{{ authError }}</p>
        <button
          type="submit"
          :disabled="authLoading"
          class="w-full py-3.5 bg-[#111111] text-white uppercase tracking-widest font-semibold hover:bg-black transition-colors"
        >
          {{ authLoading ? 'লগইন হচ্ছে...' : 'লগইন করুন' }}
        </button>
      </form>

      <!-- Register Form -->
      <form v-else @submit.prevent="handleRegister" class="space-y-4 text-xs">
        <div>
          <label class="block font-semibold uppercase tracking-wider mb-1">সম্পূর্ণ নাম</label>
          <input type="text" v-model="registerForm.name" required class="w-full px-4 py-2.5 bg-[#F7F5F0] border border-[#D8CCBC]" />
        </div>
        <div>
          <label class="block font-semibold uppercase tracking-wider mb-1">ইমেইল ঠিকানা</label>
          <input type="email" v-model="registerForm.email" required class="w-full px-4 py-2.5 bg-[#F7F5F0] border border-[#D8CCBC]" />
        </div>
        <div>
          <label class="block font-semibold uppercase tracking-wider mb-1">মোবাইল নম্বর (ঐচ্ছিক)</label>
          <input type="tel" v-model="registerForm.phone" class="w-full px-4 py-2.5 bg-[#F7F5F0] border border-[#D8CCBC]" />
        </div>
        <div>
          <label class="block font-semibold uppercase tracking-wider mb-1">পাসওয়ার্ড</label>
          <input type="password" v-model="registerForm.password" required class="w-full px-4 py-2.5 bg-[#F7F5F0] border border-[#D8CCBC]" />
        </div>
        <div>
          <label class="block font-semibold uppercase tracking-wider mb-1">পাসওয়ার্ড নিশ্চিত করুন</label>
          <input type="password" v-model="registerForm.password_confirmation" required class="w-full px-4 py-2.5 bg-[#F7F5F0] border border-[#D8CCBC]" />
        </div>
        <p v-if="authError" class="text-rose-600">{{ authError }}</p>
        <button
          type="submit"
          :disabled="authLoading"
          class="w-full py-3.5 bg-[#111111] text-white uppercase tracking-widest font-semibold hover:bg-black transition-colors"
        >
          {{ authLoading ? 'অ্যাকাউন্ট তৈরি হচ্ছে...' : 'অ্যাকাউন্ট খুলুন' }}
        </button>
      </form>
    </div>

    <!-- Authenticated Customer Dashboard -->
    <div v-else class="space-y-10">
      <div class="flex flex-col sm:flex-row sm:items-baseline justify-between border-b border-[#D8CCBC]/40 pb-6 gap-4">
        <div>
          <span class="text-xs uppercase tracking-[0.25em] text-[#A89B8A] font-medium block mb-1">গ্রাহক প্রোফাইল</span>
          <h1 class="font-serif text-3xl sm:text-4xl text-[#111111]">
            স্বাগতম, {{ authStore.customerUser?.name }}
          </h1>
        </div>
        <button
          type="button"
          @click="authStore.customerLogout()"
          class="text-xs uppercase tracking-wider text-[#A89B8A] hover:text-[#111111] underline"
        >
          লগআউট
        </button>
      </div>

      <!-- Account Content Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        <!-- Order History (8 cols) -->
        <div class="lg:col-span-8 bg-white p-6 sm:p-8 border border-[#D8CCBC]/40 shadow-2xs space-y-6">
          <h3 class="font-serif text-xl text-[#111111] border-b border-[#D8CCBC]/30 pb-3">পূর্ববর্তী অর্ডারসমূহ</h3>

          <div v-if="customerStore.orders.length" class="divide-y divide-[#D8CCBC]/30">
            <div
              v-for="order in customerStore.orders"
              :key="order.id"
              class="py-4 first:pt-0 flex flex-col sm:flex-row sm:items-center justify-between gap-4 text-xs"
            >
              <div>
                <span class="font-mono font-bold text-[#111111]">{{ order.order_number }}</span>
                <p class="text-[#A89B8A] mt-0.5">
                  তারিখ: {{ new Date(order.created_at).toLocaleDateString() }} • {{ order.items?.length || 0 }} টি পোশাক
                </p>
              </div>

              <div class="flex items-center gap-4">
                <span :class="[
                  'px-2.5 py-1 text-[10px] uppercase tracking-wider font-semibold',
                  order.order_status === 'delivered' ? 'bg-emerald-100 text-emerald-800' : 'bg-[#F7F5F0] text-[#111111]'
                ]">
                  {{ order.order_status }}
                </span>

                <span class="font-semibold text-sm text-[#111111]">৳ {{ Number(order.total).toLocaleString() }}</span>
              </div>
            </div>
          </div>

          <p v-else class="text-xs text-[#A89B8A] py-8 text-center">
            আপনার অ্যাকাউন্টে এখনো কোনো অর্ডারের তথ্য নেই।
          </p>
        </div>

        <!-- Address Book & Concierge Info (4 cols) -->
        <div class="lg:col-span-4 space-y-6">
          <div class="bg-white p-6 border border-[#D8CCBC]/40 shadow-2xs space-y-4">
            <h4 class="font-serif text-lg text-[#111111] border-b border-[#D8CCBC]/30 pb-2">ব্যক্তিগত তথ্য</h4>
            <div class="text-xs text-[#111111] space-y-1">
              <p><strong>ইমেইল:</strong> {{ authStore.customerUser?.email }}</p>
              <p><strong>ফোন:</strong> {{ authStore.customerUser?.phone || 'যুক্ত নেই' }}</p>
            </div>
          </div>

          <div class="bg-white p-6 border border-[#D8CCBC]/40 shadow-2xs space-y-4">
            <h4 class="font-serif text-lg text-[#111111] border-b border-[#D8CCBC]/30 pb-2">সংরক্ষিত ঠিকানা</h4>
            <div v-if="customerStore.addresses.length" class="space-y-3">
              <div
                v-for="addr in customerStore.addresses"
                :key="addr.id"
                class="p-3 bg-[#F7F5F0] border border-[#D8CCBC]/40 text-xs text-[#111111] space-y-1"
              >
                <p class="font-semibold">{{ addr.first_name }} {{ addr.last_name }}</p>
                <p>{{ addr.address_line_1 }}</p>
                <p>{{ addr.city }}, {{ addr.state }} {{ addr.postal_code }}</p>
                <p>{{ addr.country }}</p>
              </div>
            </div>
            <p v-else class="text-xs text-[#A89B8A]">কোনো সংরক্ষিত ঠিকানা পাওয়া যায়নি।</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useAuthStore } from '../../stores/auth';
import { useCustomerStore } from '../../stores/customer';

const authStore = useAuthStore();
const customerStore = useCustomerStore();

const activeAuthTab = ref('login');
const authLoading = ref(false);
const authError = ref('');

const loginForm = reactive({
  email: 'customer@cloth.test',
  password: 'password',
});

const registerForm = reactive({
  name: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: '',
});

const handleLogin = async () => {
  authLoading.value = true;
  authError.value = '';
  try {
    await authStore.customerLogin(loginForm.email, loginForm.password);
    customerStore.fetchOrders();
    customerStore.fetchAddresses();
  } catch (err) {
    authError.value = err.response?.data?.message || 'লগইন ব্যর্থ হয়েছে। ইমেইল ও পাসওয়ার্ড পুনরায় যাচাই করুন।';
  } finally {
    authLoading.value = false;
  }
};

const handleRegister = async () => {
  authLoading.value = true;
  authError.value = '';
  try {
    await authStore.customerRegister(registerForm);
  } catch (err) {
    authError.value = err.response?.data?.message || 'অ্যাকাউন্ট তৈরি করতে সমস্যা হয়েছে। সঠিক তথ্য দিয়ে পুনরায় চেষ্টা করুন।';
  } finally {
    authLoading.value = false;
  }
};

onMounted(() => {
  if (authStore.isCustomerAuthenticated) {
    customerStore.fetchOrders();
    customerStore.fetchAddresses();
  }
});
</script>
