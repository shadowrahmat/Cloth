<template>
  <div class="py-12 sm:py-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center max-w-xl mx-auto mb-12 space-y-3">
      <span class="text-xs uppercase tracking-[0.25em] text-[#A89B8A] font-medium block">গ্রাহক সহায়তা ও সেবা</span>
      <h1 class="font-serif text-3xl sm:text-5xl text-[#111111]">যোগাযোগ করুন</h1>
      <p class="text-xs sm:text-sm text-[#A89B8A] font-light leading-relaxed">
        পোশাকের সাইজ, ডেলিভারি বা যেকোনো তথ্যের জন্য আমাদের গ্রাহক প্রতিনিধির সাথে যোগাযোগ করুন।
      </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">
      <!-- Contact Info Card -->
      <div class="lg:col-span-5 bg-white p-8 border border-[#D8CCBC]/40 shadow-xs space-y-6 text-xs text-[#111111]">
        <div>
          <h4 class="font-serif text-xl mb-2">প্রধান কার্যালয়</h4>
          <p class="text-[#A89B8A] leading-relaxed">
            হাউজ ৪২, রোড ১১<br />
            গুলশান অ্যাভিনিউ, ঢাকা ১২১২<br />
            বাংলাদেশ
          </p>
        </div>

        <div class="border-t border-[#D8CCBC]/30 pt-4 space-y-2">
          <h5 class="font-semibold uppercase tracking-wider text-[11px]">সরাসরি যোগাযোগ</h5>
          <p class="text-[#A89B8A]">support@drawerclothing.com</p>
          <p class="text-[#A89B8A]">+880 1700 000000</p>
        </div>

        <div class="border-t border-[#D8CCBC]/30 pt-4 space-y-1 text-[#A89B8A]">
          <h5 class="font-semibold uppercase tracking-wider text-[11px] text-[#111111]">গ্রাহক সেবার সময়</h5>
          <p>শনিবার – বৃহস্পতিবার: সকাল ১০টা – রাত ৮টা</p>
          <p>শুক্রবার: দুপুর ২টা – রাত ৮টা</p>
        </div>
      </div>

      <!-- Contact Message Form -->
      <div class="lg:col-span-7 bg-white p-8 border border-[#D8CCBC]/40 shadow-xs">
        <div v-if="submitted" class="p-6 bg-emerald-50 text-emerald-800 text-xs font-medium border border-emerald-200 text-center space-y-2">
          <h5 class="font-serif text-base text-emerald-900">বার্তাটি পৌঁছেছে</h5>
          <p>DRAWER ক্লদিং-এ যোগাযোগ করার জন্য ধন্যবাদ। আমাদের প্রতিনিধি দ্রুত আপনার সাথে যোগাযোগ করবেন।</p>
        </div>

        <form v-else @submit.prevent="handleSubmit" class="space-y-4 text-xs">
          <div>
            <label class="block font-semibold uppercase tracking-wider mb-1">আপনার পুরো নাম *</label>
            <input type="text" v-model="form.name" required class="w-full px-4 py-2.5 bg-[#F7F5F0] border border-[#D8CCBC]" />
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block font-semibold uppercase tracking-wider mb-1">ইমেইল ঠিকানা *</label>
              <input type="email" v-model="form.email" required class="w-full px-4 py-2.5 bg-[#F7F5F0] border border-[#D8CCBC]" />
            </div>
            <div>
              <label class="block font-semibold uppercase tracking-wider mb-1">মোবাইল নম্বর</label>
              <input type="tel" v-model="form.phone" class="w-full px-4 py-2.5 bg-[#F7F5F0] border border-[#D8CCBC]" />
            </div>
          </div>
          <div>
            <label class="block font-semibold uppercase tracking-wider mb-1">বিষয়</label>
            <input type="text" v-model="form.subject" placeholder="সাইজ পরামর্শ, ডেলিভারি বা অর্ডার সংক্রান্ত..." class="w-full px-4 py-2.5 bg-[#F7F5F0] border border-[#D8CCBC]" />
          </div>
          <div>
            <label class="block font-semibold uppercase tracking-wider mb-1">আপনার বার্তা *</label>
            <textarea rows="5" v-model="form.message" required class="w-full px-4 py-2.5 bg-[#F7F5F0] border border-[#D8CCBC]"></textarea>
          </div>
          <button
            type="submit"
            :disabled="loading"
            class="w-full py-3.5 bg-[#111111] text-white uppercase tracking-widest font-semibold hover:bg-black transition-colors"
          >
            {{ loading ? 'বার্তা পাঠানো হচ্ছে...' : 'বার্তা পাঠান' }}
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import api from '../../api';

const form = reactive({
  name: '',
  email: '',
  phone: '',
  subject: '',
  message: '',
});
const loading = ref(false);
const submitted = ref(false);

const handleSubmit = async () => {
  loading.value = true;
  try {
    await api.post('/public/contact', form);
    submitted.value = true;
  } catch (err) {
    alert(err.response?.data?.message || 'বার্তা পাঠাতে সমস্যা হয়েছে। পুনরায় চেষ্টা করুন।');
  } finally {
    loading.value = false;
  }
};
</script>
