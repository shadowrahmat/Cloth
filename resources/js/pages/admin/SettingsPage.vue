<template>
  <div class="space-y-6 max-w-5xl">
    <div>
      <h2 class="text-xl font-bold tracking-tight text-admin-text">স্টোর সেটিংস</h2>
      <p class="text-xs text-admin-muted mt-0.5">দোকানের সব সেটিং কোথায় আছে, আর ইনভয়েসে কী ছাপা হবে।</p>
    </div>

    <!-- Where everything lives -->
    <section>
      <h3 class="text-[13px] font-semibold text-admin-text mb-3">সেটিং খুঁজছেন?</h3>
      <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
        <router-link v-for="l in links" :key="l.to" :to="l.to" class="adm-card p-4 flex items-start gap-3 hover:border-admin-accent transition-colors group">
          <span class="w-9 h-9 rounded-lg bg-[#EEF6F1] text-admin-sidebar flex items-center justify-center shrink-0"><component :is="l.icon" :size="18" :stroke-width="1.8" /></span>
          <span class="min-w-0">
            <span class="block text-[13px] font-semibold text-admin-text group-hover:text-admin-accent">{{ l.label }}</span>
            <span class="block text-[11.5px] text-admin-muted leading-snug mt-0.5">{{ l.help }}</span>
          </span>
        </router-link>
      </div>
    </section>

    <!-- Invoice -->
    <form class="adm-card" @submit.prevent="save">
      <header class="adm-card-head">
        <div>
          <h3 class="adm-card-title">ইনভয়েস (PDF)</h3>
          <p class="adm-card-sub">অর্ডার পেজ থেকে ডাউনলোড করা ইনভয়েসের ওপরে ও নিচে এগুলো ছাপা হয়। PDF-এ বাংলা অক্ষর ঠিকমতো আসে না, তাই ইংরেজিতে লিখুন।</p>
        </div>
        <button type="button" class="adm-btn shrink-0" :disabled="previewing" @click="previewInvoice">
          <FileText :size="15" /> {{ previewing ? 'খুলছে…' : 'নমুনা দেখুন' }}
        </button>
      </header>
      <div class="adm-card-body grid sm:grid-cols-2 gap-4">
        <div>
          <label class="adm-label" for="s-name">ব্যবসার নাম</label>
          <input id="s-name" v-model.trim="form.invoice_business_name" class="adm-input" placeholder="DRAWER CLOTHING" />
        </div>
        <div>
          <label class="adm-label" for="s-bin">BIN / ভ্যাট নম্বর <span class="font-normal text-admin-muted">(না থাকলে খালি)</span></label>
          <input id="s-bin" v-model.trim="form.vat_bin_number" class="adm-input" />
        </div>
        <div class="sm:col-span-2">
          <label class="adm-label" for="s-addr">ঠিকানা</label>
          <textarea id="s-addr" v-model="form.invoice_address" rows="2" class="adm-input" placeholder="House 42, Road 11, Gulshan, Dhaka 1212"></textarea>
        </div>
        <div>
          <label class="adm-label" for="s-phone">ফোন</label>
          <input id="s-phone" v-model.trim="form.invoice_phone" class="adm-input" placeholder="+880 1700 000000" />
        </div>
        <div>
          <label class="adm-label" for="s-email">ইমেইল</label>
          <input id="s-email" v-model.trim="form.invoice_email" type="email" class="adm-input" placeholder="support@yourshop.com" />
        </div>
        <div>
          <label class="adm-label" for="s-prefix">ইনভয়েস নম্বরের শুরু</label>
          <input id="s-prefix" v-model.trim="form.invoice_prefix" class="adm-input font-mono" placeholder="INV-" maxlength="12" />
          <p class="adm-help">যেমন: {{ form.invoice_prefix || 'INV-' }}ORD-20261120-AB12C</p>
        </div>
        <div class="sm:col-span-2">
          <label class="adm-label" for="s-note">নিচের লেখা</label>
          <textarea id="s-note" v-model="form.invoice_note" rows="2" class="adm-input" placeholder="Thank you for shopping with us. Size or fit not right? Exchange within 7 days of delivery."></textarea>
        </div>
        <div class="sm:col-span-2 flex items-center gap-3 pt-2 border-t border-admin-border">
          <button v-can="'update_settings'" type="submit" class="adm-btn adm-btn-primary" :disabled="saving || !dirty"><Check :size="15" /> {{ saving ? 'সেভ হচ্ছে…' : 'সেভ করুন' }}</button>
          <span v-if="dirty" class="text-[11.5px] text-amber-700">সেভ হয়নি এমন পরিবর্তন আছে</span>
        </div>
      </div>
    </form>

    <!-- Not connected yet -->
    <section class="grid md:grid-cols-2 gap-3">
      <div class="adm-card p-4 flex items-start gap-3">
        <span class="w-9 h-9 rounded-lg bg-admin-bg text-admin-muted flex items-center justify-center shrink-0"><Wallet :size="18" /></span>
        <div>
          <p class="text-[13px] font-semibold text-admin-text">পেমেন্ট: শুধু ক্যাশ অন ডেলিভারি</p>
          <p class="text-[11.5px] text-admin-muted leading-snug mt-0.5">অনলাইন পেমেন্ট (bKash / SSLCommerz) চালু করতে মার্চেন্ট অ্যাকাউন্ট লাগবে। অ্যাকাউন্ট হলে আসল যাচাইসহ যোগ করা হবে।</p>
        </div>
      </div>
      <div class="adm-card p-4 flex items-start gap-3">
        <span class="w-9 h-9 rounded-lg bg-admin-bg text-admin-muted flex items-center justify-center shrink-0"><MessageSquareText :size="18" /></span>
        <div>
          <p class="text-[13px] font-semibold text-admin-text">SMS: এখনো চালু নেই</p>
          <p class="text-[11.5px] text-admin-muted leading-snug mt-0.5">কোন SMS সার্ভিস (যেমন BulkSMSBD, SSL Wireless) নেবেন ঠিক হলে অর্ডারের SMS যোগ করা যাবে। ততক্ষণ ইমেইল টেমপ্লেট ব্যবহার করুন।</p>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { reactive, ref, computed, onMounted } from 'vue';
import { Check, FileText, Palette, Banknote, Mail, Ruler, UserCog, Truck, Wallet, MessageSquareText } from 'lucide-vue-next';
import api from '../../api';
import { useToastStore } from '../../stores/toast';

const toast = useToastStore();

const links = [
  { label: 'নাম, লোগো ও যোগাযোগ', help: 'সাইটের নাম, লোগো, ফোন, ঠিকানা, সোশ্যাল লিংক', to: '/admin/appearance', icon: Palette },
  { label: 'ডেলিভারি চার্জ', help: 'ঢাকার ভেতরে/বাইরে চার্জ, সময়, ফ্রি ডেলিভারি', to: '/admin/delivery-charges', icon: Banknote },
  { label: 'ইমেইল ও SMTP', help: 'অর্ডারের ইমেইল, কোন অ্যাকাউন্ট থেকে যাবে', to: '/admin/email-templates', icon: Mail },
  { label: 'সাইজ চার্ট', help: 'ক্যাটাগরি অনুযায়ী মাপের টেবিল', to: '/admin/appearance?tab=sizes', icon: Ruler },
  { label: 'কুরিয়ার', help: 'Steadfast পার্সেল ও ট্র্যাকিং', to: '/admin/logistics', icon: Truck },
  { label: 'স্টাফ ও পাসওয়ার্ড', help: 'কে অ্যাডমিনে ঢুকতে পারবে', to: '/admin/users', icon: UserCog },
];

const KEYS = ['invoice_business_name', 'invoice_address', 'invoice_phone', 'invoice_email', 'vat_bin_number', 'invoice_prefix', 'invoice_note'];
const form = reactive(Object.fromEntries(KEYS.map((k) => [k, ''])));
const original = ref('');
const dirty = computed(() => JSON.stringify(form) !== original.value);
const saving = ref(false);
const previewing = ref(false);

onMounted(async () => {
  try {
    const res = await api.get('/admin/settings');
    const d = res.data?.data || {};
    KEYS.forEach((k) => (form[k] = d[k] ?? ''));
  } catch {
    toast.error('সেটিং লোড হয়নি');
  }
  original.value = JSON.stringify(form);
});

const save = async () => {
  saving.value = true;
  try {
    await api.post('/admin/settings', { group: 'invoice', settings: { ...form } });
    original.value = JSON.stringify(form);
    toast.success('ইনভয়েসের তথ্য সেভ হয়েছে');
  } catch {
    toast.error('সেভ হয়নি');
  } finally {
    saving.value = false;
  }
};

/** Open the latest order's invoice so the owner sees exactly what gets printed. */
const previewInvoice = async () => {
  if (dirty.value) await save();
  previewing.value = true;
  try {
    const list = await api.get('/admin/orders', { params: { per_page: 1 } });
    const order = (list.data?.data?.data || list.data?.data || [])[0];
    if (!order) {
      toast.warning('এখনো কোনো অর্ডার নেই — প্রথম অর্ডারের পর নমুনা দেখা যাবে');
      return;
    }
    const pdf = await api.get(`/admin/orders/${order.id}/invoice`, { params: { stream: 1 }, responseType: 'blob' });
    window.open(URL.createObjectURL(new Blob([pdf.data], { type: 'application/pdf' })), '_blank');
  } catch {
    toast.error('ইনভয়েস খোলা যায়নি');
  } finally {
    previewing.value = false;
  }
};
</script>
