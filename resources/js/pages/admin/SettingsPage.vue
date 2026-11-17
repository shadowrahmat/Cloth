<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-[#E7EBE8]">
      <div>
        <h2 class="text-xl font-bold tracking-tight text-[#171C1A]">Central Settings Console</h2>
        <p class="text-xs text-[#69736E] mt-0.5">Configure store branding, payment gateways, delivery rules, invoices, SMTP, and SMS providers.</p>
      </div>

      <div class="flex items-center gap-3">
        <button
          type="button"
          :disabled="saving"
          @click="saveSettings"
          class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-[#14382A] hover:bg-[#16A36A] text-white text-xs font-semibold rounded-md transition-all shadow-xs disabled:opacity-50"
        >
          <span v-if="saving" class="w-3.5 h-3.5 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
          <span>{{ saving ? 'Saving Console Settings...' : 'Save All Settings' }}</span>
        </button>
      </div>
    </div>

    <!-- 7-Tab Navigation -->
    <div class="flex border-b border-[#E7EBE8] space-x-1 text-xs overflow-x-auto no-scrollbar bg-white p-1 rounded-t-lg">
      <button
        v-for="tab in tabs"
        :key="tab.id"
        type="button"
        @click="activeTab = tab.id"
        :class="[
          'px-4 py-2.5 font-medium rounded-md whitespace-nowrap transition-all text-xs flex items-center gap-2',
          activeTab === tab.id ? 'bg-[#14382A] text-white shadow-xs' : 'text-[#69736E] hover:text-[#171C1A] hover:bg-[#F7F8FA]'
        ]"
      >
        <span>{{ tab.icon }}</span>
        <span>{{ tab.label }}</span>
      </button>
    </div>

    <!-- Tab 1: General Branding -->
    <div v-if="activeTab === 'branding'" class="bg-white p-6 rounded-b-lg border border-[#E7EBE8] shadow-xs space-y-4 text-xs">
      <h3 class="text-sm font-semibold text-[#171C1A] pb-2 border-b border-[#E7EBE8]">Brand Identity & Currency</h3>
      
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block font-semibold text-[#171C1A] mb-1">Store / Brand Name</label>
          <input type="text" v-model="settings.site_name" class="w-full px-3 py-2 border border-[#E7EBE8] rounded-md focus:ring-1 focus:ring-[#16A36A]" />
        </div>
        <div>
          <label class="block font-semibold text-[#171C1A] mb-1">Brand Tagline</label>
          <input type="text" v-model="settings.site_tagline" class="w-full px-3 py-2 border border-[#E7EBE8] rounded-md focus:ring-1 focus:ring-[#16A36A]" />
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
          <label class="block font-semibold text-[#171C1A] mb-1">Currency Code (e.g. BDT, USD)</label>
          <input type="text" v-model="settings.currency_code" class="w-full px-3 py-2 border border-[#E7EBE8] rounded-md focus:ring-1 focus:ring-[#16A36A]" />
        </div>
        <div>
          <label class="block font-semibold text-[#171C1A] mb-1">Currency Symbol (e.g. ৳, $)</label>
          <input type="text" v-model="settings.currency_symbol" class="w-full px-3 py-2 border border-[#E7EBE8] rounded-md focus:ring-1 focus:ring-[#16A36A]" />
        </div>
        <div>
          <label class="block font-semibold text-[#171C1A] mb-1">Low-Stock Alert Threshold</label>
          <input type="number" v-model="settings.low_stock_threshold" class="w-full px-3 py-2 border border-[#E7EBE8] rounded-md focus:ring-1 focus:ring-[#16A36A]" />
        </div>
      </div>
    </div>

    <!-- Tab 2: Delivery & Shipping -->
    <div v-else-if="activeTab === 'shipping'" class="bg-white p-6 rounded-b-lg border border-[#E7EBE8] shadow-xs space-y-4 text-xs">
      <h3 class="text-sm font-semibold text-[#171C1A] pb-2 border-b border-[#E7EBE8]">Logistics & Delivery Thresholds</h3>
      
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
          <label class="block font-semibold text-[#171C1A] mb-1">Inside City Delivery Charge (৳ / $)</label>
          <input type="number" step="0.01" v-model="settings.inside_city_charge" class="w-full px-3 py-2 border border-[#E7EBE8] rounded-md focus:ring-1 focus:ring-[#16A36A]" />
        </div>
        <div>
          <label class="block font-semibold text-[#171C1A] mb-1">Outside City Delivery Charge (৳ / $)</label>
          <input type="number" step="0.01" v-model="settings.outside_city_charge" class="w-full px-3 py-2 border border-[#E7EBE8] rounded-md focus:ring-1 focus:ring-[#16A36A]" />
        </div>
        <div>
          <label class="block font-semibold text-[#171C1A] mb-1">Free Shipping Threshold Amount</label>
          <input type="number" step="0.01" v-model="settings.free_shipping_threshold" class="w-full px-3 py-2 border border-[#E7EBE8] rounded-md focus:ring-1 focus:ring-[#16A36A]" />
        </div>
      </div>

      <!-- Critical Option: Delivery Charge To Be Added Later -->
      <div class="p-4 bg-emerald-50/60 border border-emerald-200 rounded-lg">
        <label class="flex items-start gap-3 cursor-pointer">
          <input type="checkbox" v-model="settings.delivery_charge_later" class="rounded border-emerald-300 text-[#16A36A] focus:ring-[#16A36A] mt-0.5" />
          <div>
            <span class="font-semibold text-[#14382A] block text-xs">Enable "Delivery Charge To Be Added Later" Mode</span>
            <span class="text-[11px] text-[#69736E] leading-relaxed block mt-0.5">
              When checked, checkout displays "Delivery charge to be calculated upon courier handover" and prevents erroneous shipping charge calculations.
            </span>
          </div>
        </label>
      </div>
    </div>

    <!-- Tab 3: Payment Gateways -->
    <div v-else-if="activeTab === 'payment'" class="bg-white p-6 rounded-b-lg border border-[#E7EBE8] shadow-xs space-y-6 text-xs">
      <h3 class="text-sm font-semibold text-[#171C1A] pb-2 border-b border-[#E7EBE8]">Payment Gateway Integrations</h3>

      <!-- bKash Gateway -->
      <div class="p-4 border border-[#E7EBE8] rounded-lg bg-[#F7F8FA] space-y-3">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-pink-600"></span>
            <span class="font-bold text-sm text-[#171C1A]">bKash Payment Gateway</span>
          </div>
          <label class="flex items-center gap-2 cursor-pointer font-medium text-xs">
            <input type="checkbox" v-model="settings.enable_bkash" class="rounded text-pink-600 focus:ring-pink-600" />
            <span>Enable bKash</span>
          </label>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" v-if="settings.enable_bkash">
          <div>
            <label class="block font-medium mb-1 text-[11px]">App Key</label>
            <input type="password" v-model="settings.bkash_app_key" class="w-full px-3 py-1.5 border border-[#E7EBE8] rounded bg-white" />
          </div>
          <div>
            <label class="block font-medium mb-1 text-[11px]">App Secret</label>
            <input type="password" v-model="settings.bkash_app_secret" class="w-full px-3 py-1.5 border border-[#E7EBE8] rounded bg-white" />
          </div>
        </div>
      </div>

      <!-- Nagad Gateway -->
      <div class="p-4 border border-[#E7EBE8] rounded-lg bg-[#F7F8FA] space-y-3">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-amber-600"></span>
            <span class="font-bold text-sm text-[#171C1A]">Nagad Merchant Gateway</span>
          </div>
          <label class="flex items-center gap-2 cursor-pointer font-medium text-xs">
            <input type="checkbox" v-model="settings.enable_nagad" class="rounded text-amber-600 focus:ring-amber-600" />
            <span>Enable Nagad</span>
          </label>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" v-if="settings.enable_nagad">
          <div>
            <label class="block font-medium mb-1 text-[11px]">Merchant ID</label>
            <input type="text" v-model="settings.nagad_merchant_id" class="w-full px-3 py-1.5 border border-[#E7EBE8] rounded bg-white" />
          </div>
          <div>
            <label class="block font-medium mb-1 text-[11px]">Public Key</label>
            <input type="password" v-model="settings.nagad_public_key" class="w-full px-3 py-1.5 border border-[#E7EBE8] rounded bg-white" />
          </div>
        </div>
      </div>

      <!-- SSLCommerz Gateway -->
      <div class="p-4 border border-[#E7EBE8] rounded-lg bg-[#F7F8FA] space-y-3">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
            <span class="font-bold text-sm text-[#171C1A]">SSLCommerz Gateway</span>
          </div>
          <label class="flex items-center gap-2 cursor-pointer font-medium text-xs">
            <input type="checkbox" v-model="settings.enable_sslcommerz" class="rounded text-blue-600 focus:ring-blue-600" />
            <span>Enable SSLCommerz</span>
          </label>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" v-if="settings.enable_sslcommerz">
          <div>
            <label class="block font-medium mb-1 text-[11px]">Store ID</label>
            <input type="text" v-model="settings.ssl_store_id" class="w-full px-3 py-1.5 border border-[#E7EBE8] rounded bg-white" />
          </div>
          <div>
            <label class="block font-medium mb-1 text-[11px]">Store Password</label>
            <input type="password" v-model="settings.ssl_store_password" class="w-full px-3 py-1.5 border border-[#E7EBE8] rounded bg-white" />
          </div>
        </div>
      </div>

      <!-- Cash on Delivery -->
      <div class="p-4 border border-[#E7EBE8] rounded-lg bg-[#F7F8FA] flex items-center justify-between">
        <div>
          <span class="font-bold text-sm text-[#171C1A] block">Cash on Delivery (White Glove Handover)</span>
          <span class="text-[11px] text-[#69736E]">Allow customers to settle payment upon physical courier handover.</span>
        </div>
        <label class="flex items-center gap-2 cursor-pointer font-medium text-xs">
          <input type="checkbox" v-model="settings.enable_cod" class="rounded text-[#16A36A] focus:ring-[#16A36A]" />
          <span>Active</span>
        </label>
      </div>
    </div>

    <!-- Tab 4: Invoice Settings -->
    <div v-else-if="activeTab === 'invoice'" class="bg-white p-6 rounded-b-lg border border-[#E7EBE8] shadow-xs space-y-4 text-xs">
      <h3 class="text-sm font-semibold text-[#171C1A] pb-2 border-b border-[#E7EBE8]">Legal Invoice & Billing Configuration</h3>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block font-semibold text-[#171C1A] mb-1">Registered Business Name</label>
          <input type="text" v-model="settings.invoice_business_name" class="w-full px-3 py-2 border border-[#E7EBE8] rounded-md focus:ring-1 focus:ring-[#16A36A]" />
        </div>
        <div>
          <label class="block font-semibold text-[#171C1A] mb-1">VAT / BIN Registration Number</label>
          <input type="text" v-model="settings.vat_bin_number" class="w-full px-3 py-2 border border-[#E7EBE8] rounded-md focus:ring-1 focus:ring-[#16A36A]" />
        </div>
      </div>

      <div>
        <label class="block font-semibold text-[#171C1A] mb-1">Registered Official Address</label>
        <input type="text" v-model="settings.invoice_address" class="w-full px-3 py-2 border border-[#E7EBE8] rounded-md focus:ring-1 focus:ring-[#16A36A]" />
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block font-semibold text-[#171C1A] mb-1">Invoice Number Prefix (e.g. DH-)</label>
          <input type="text" v-model="settings.invoice_prefix" class="w-full px-3 py-2 border border-[#E7EBE8] rounded-md focus:ring-1 focus:ring-[#16A36A]" />
        </div>
        <div>
          <label class="block font-semibold text-[#171C1A] mb-1">Billing Support Email</label>
          <input type="email" v-model="settings.invoice_email" class="w-full px-3 py-2 border border-[#E7EBE8] rounded-md focus:ring-1 focus:ring-[#16A36A]" />
        </div>
      </div>
    </div>

    <!-- Tab 5: Email Configuration & SMTP -->
    <div v-else-if="activeTab === 'email'" class="bg-white p-6 rounded-b-lg border border-[#E7EBE8] shadow-xs space-y-4 text-xs">
      <h3 class="text-sm font-semibold text-[#171C1A] pb-2 border-b border-[#E7EBE8]">SMTP Server & Email Delivery</h3>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
          <label class="block font-semibold text-[#171C1A] mb-1">SMTP Host</label>
          <input type="text" v-model="settings.smtp_host" placeholder="smtp.mailtrap.io" class="w-full px-3 py-2 border border-[#E7EBE8] rounded-md focus:ring-1 focus:ring-[#16A36A]" />
        </div>
        <div>
          <label class="block font-semibold text-[#171C1A] mb-1">Port</label>
          <input type="number" v-model="settings.smtp_port" placeholder="587" class="w-full px-3 py-2 border border-[#E7EBE8] rounded-md focus:ring-1 focus:ring-[#16A36A]" />
        </div>
        <div>
          <label class="block font-semibold text-[#171C1A] mb-1">Encryption</label>
          <select v-model="settings.smtp_encryption" class="w-full px-3 py-2 border border-[#E7EBE8] rounded-md focus:ring-1 focus:ring-[#16A36A]">
            <option value="tls">TLS</option>
            <option value="ssl">SSL</option>
          </select>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block font-semibold text-[#171C1A] mb-1">SMTP Username</label>
          <input type="text" v-model="settings.smtp_username" class="w-full px-3 py-2 border border-[#E7EBE8] rounded-md focus:ring-1 focus:ring-[#16A36A]" />
        </div>
        <div>
          <label class="block font-semibold text-[#171C1A] mb-1">SMTP Password</label>
          <input type="password" v-model="settings.smtp_password" class="w-full px-3 py-2 border border-[#E7EBE8] rounded-md focus:ring-1 focus:ring-[#16A36A]" />
        </div>
      </div>
    </div>

    <!-- Tab 6: SMS Configuration -->
    <div v-else-if="activeTab === 'sms'" class="bg-white p-6 rounded-b-lg border border-[#E7EBE8] shadow-xs space-y-4 text-xs">
      <h3 class="text-sm font-semibold text-[#171C1A] pb-2 border-b border-[#E7EBE8]">SMS Notifications Gateway</h3>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block font-semibold text-[#171C1A] mb-1">SMS Gateway Provider</label>
          <select v-model="settings.sms_provider" class="w-full px-3 py-2 border border-[#E7EBE8] rounded-md focus:ring-1 focus:ring-[#16A36A]">
            <option value="greenweb">Greenweb SMS</option>
            <option value="mimsms">MiMSMS Bangladesh</option>
            <option value="twilio">Twilio Global</option>
          </select>
        </div>
        <div>
          <label class="block font-semibold text-[#171C1A] mb-1">Registered Sender ID (Masking)</label>
          <input type="text" v-model="settings.sms_sender_id" placeholder="e.g. ATELIER" class="w-full px-3 py-2 border border-[#E7EBE8] rounded-md focus:ring-1 focus:ring-[#16A36A]" />
        </div>
      </div>

      <div>
        <label class="block font-semibold text-[#171C1A] mb-1">SMS API Token / Key</label>
        <input type="password" v-model="settings.sms_api_key" class="w-full px-3 py-2 border border-[#E7EBE8] rounded-md focus:ring-1 focus:ring-[#16A36A]" />
      </div>
    </div>

    <!-- Tab 7: reCAPTCHA v3 -->
    <div v-else-if="activeTab === 'recaptcha'" class="bg-white p-6 rounded-b-lg border border-[#E7EBE8] shadow-xs space-y-4 text-xs">
      <h3 class="text-sm font-semibold text-[#171C1A] pb-2 border-b border-[#E7EBE8]">Google reCAPTCHA v3 Bot Defense</h3>

      <div class="flex items-center gap-2 mb-2">
        <input type="checkbox" v-model="settings.enable_recaptcha" id="recaptcha_active" class="rounded text-[#16A36A] focus:ring-[#16A36A]" />
        <label for="recaptcha_active" class="font-semibold text-xs text-[#171C1A]">Enable reCAPTCHA v3 verification on checkout and contact forms</label>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block font-semibold text-[#171C1A] mb-1">Site Key</label>
          <input type="text" v-model="settings.recaptcha_site_key" class="w-full px-3 py-2 border border-[#E7EBE8] rounded-md focus:ring-1 focus:ring-[#16A36A]" />
        </div>
        <div>
          <label class="block font-semibold text-[#171C1A] mb-1">Secret Key</label>
          <input type="password" v-model="settings.recaptcha_secret_key" class="w-full px-3 py-2 border border-[#E7EBE8] rounded-md focus:ring-1 focus:ring-[#16A36A]" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useToastStore } from '../../stores/toast';
import api from '../../api';

const toast = useToastStore();
const activeTab = ref('branding');
const saving = ref(false);

const tabs = [
  { id: 'branding', label: '1. General Branding', icon: '🏷️' },
  { id: 'shipping', label: '2. Delivery & Shipping', icon: '🚚' },
  { id: 'payment', label: '3. Payment Gateways', icon: '💳' },
  { id: 'invoice', label: '4. Invoice Settings', icon: '🧾' },
  { id: 'email', label: '5. Email & SMTP', icon: '✉️' },
  { id: 'sms', label: '6. SMS Gateway', icon: '📱' },
  { id: 'recaptcha', label: '7. Bot Defense v3', icon: '🛡️' },
];

const settings = ref({
  site_name: 'ATELIER ÉTOILE',
  site_tagline: 'Haute Prêt-à-Porter',
  currency_code: 'BDT',
  currency_symbol: '৳',
  low_stock_threshold: 5,
  inside_city_charge: 80,
  outside_city_charge: 150,
  free_shipping_threshold: 2500,
  delivery_charge_later: false,
  enable_bkash: true,
  bkash_app_key: '',
  bkash_app_secret: '',
  enable_nagad: true,
  nagad_merchant_id: '',
  nagad_public_key: '',
  enable_sslcommerz: true,
  ssl_store_id: '',
  ssl_store_password: '',
  enable_cod: true,
  invoice_business_name: 'Atelier Etoile Ltd.',
  vat_bin_number: 'BIN-002918291-0101',
  invoice_address: 'Level 12, Gulshan Fashion Avenue, Dhaka 1212',
  invoice_prefix: 'DH-',
  invoice_email: 'concierge@atelieretoile.com',
  smtp_host: 'smtp.mailtrap.io',
  smtp_port: 587,
  smtp_encryption: 'tls',
  smtp_username: '',
  smtp_password: '',
  sms_provider: 'greenweb',
  sms_sender_id: 'ATELIER',
  sms_api_key: '',
  enable_recaptcha: false,
  recaptcha_site_key: '',
  recaptcha_secret_key: '',
});

const fetchSettings = async () => {
  try {
    const res = await api.get('/admin/settings');
    if (res.data?.data) {
      settings.value = { ...settings.value, ...res.data.data };
    }
  } catch (err) {
    console.error('Failed to load settings', err);
  }
};

const saveSettings = async () => {
  saving.value = true;
  try {
    await api.post('/admin/settings', settings.value);
    toast.success('Central settings console updated successfully');
  } catch (err) {
    toast.error('Failed to save settings');
  } finally {
    saving.value = false;
  }
};

onMounted(() => {
  fetchSettings();
});
</script>
