<template>
  <div class="space-y-5">
    <!-- ============================================================ header -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-3">
      <div>
        <h2 class="text-xl font-bold tracking-tight text-admin-text">ইমেইল টেমপ্লেট</h2>
        <p class="text-xs text-admin-muted mt-0.5">অর্ডারের পর গ্রাহক আর আপনার কাছে যে ইমেইল যায়, তার লেখা এখান থেকে বদলান। প্রিভিউতে সাথে সাথে দেখতে পাবেন কেমন দেখাবে।</p>
      </div>
      <nav class="inline-flex p-1 rounded-xl bg-white border border-admin-border self-start md:self-auto" role="tablist">
        <button v-for="t in tabs" :key="t.key" type="button" role="tab" :aria-selected="tab === t.key" @click="tab = t.key"
          :class="['px-3.5 py-1.5 rounded-lg text-[12.5px] font-semibold transition-colors inline-flex items-center gap-1.5', tab === t.key ? 'bg-admin-sidebar text-white' : 'text-admin-muted hover:text-admin-text']">
          <component :is="t.icon" :size="14" /> {{ t.label }}
        </button>
      </nav>
    </div>

    <!-- Mail status -->
    <div v-if="status && !status.ready" class="flex flex-col sm:flex-row sm:items-center gap-3 p-4 rounded-xl border border-amber-200 bg-amber-50 text-amber-900">
      <AlertTriangle :size="18" class="shrink-0" />
      <p class="text-[13px] flex-1">{{ status.message }}</p>
      <button v-can="'@admin'" v-if="tab !== 'setup'" type="button" class="adm-btn shrink-0" @click="tab = 'setup'">ইমেইল সেটআপ করুন</button>
    </div>
    <div v-else-if="status" class="flex items-center gap-2.5 px-4 py-2.5 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-800 text-[13px]">
      <CheckCircle2 :size="16" class="shrink-0" /> {{ status.message }}
    </div>

    <!-- ============================================================ templates -->
    <div v-show="tab === 'templates'" class="grid lg:grid-cols-[270px_minmax(0,1fr)] gap-5 items-start">
      <!-- List -->
      <aside class="adm-card overflow-hidden lg:sticky lg:top-[88px]">
        <div v-if="loading" class="p-4 space-y-2"><div v-for="n in 6" :key="n" class="h-12 rounded-lg bg-admin-bg animate-pulse"></div></div>
        <template v-else>
          <div v-for="g in groups" :key="g.key">
            <p class="px-4 pt-4 pb-1.5 text-[11px] font-semibold text-admin-muted">{{ g.label }}</p>
            <button v-for="t in g.items" :key="t.key" type="button" @click="select(t.key)"
              :class="['w-full text-left px-4 py-2.5 flex items-start gap-3 border-l-[3px] transition-colors', current?.key === t.key ? 'bg-[#F3FAF6] border-admin-accent' : 'border-transparent hover:bg-admin-bg']">
              <span :class="['mt-1.5 w-2 h-2 rounded-full shrink-0', t.is_active ? 'bg-emerald-500' : 'bg-slate-300']" :title="t.is_active ? 'চালু' : 'বন্ধ'"></span>
              <span class="min-w-0 flex-1">
                <span class="block text-[13px] font-semibold text-admin-text truncate">{{ t.name }}</span>
                <span class="block text-[11.5px] text-admin-muted leading-snug">{{ t.trigger }}</span>
                <span class="flex gap-1 mt-1">
                  <span v-if="!t.is_active" class="adm-chip bg-slate-100 text-slate-600">বন্ধ</span>
                  <span v-if="t.is_customized" class="adm-chip bg-sky-50 text-sky-700">আপনার লেখা</span>
                  <span v-if="t.sent_count" class="adm-chip bg-admin-bg text-admin-muted">{{ bnNumber(t.sent_count) }} বার গেছে</span>
                </span>
              </span>
            </button>
          </div>
        </template>
      </aside>

      <!-- Editor + preview -->
      <div v-if="current" class="grid 2xl:grid-cols-2 gap-5 items-start min-w-0">
        <section class="adm-card min-w-0">
          <header class="adm-card-head">
            <div class="min-w-0">
              <h3 class="adm-card-title">{{ current.name }}</h3>
              <p class="adm-card-sub">কখন যায়: {{ current.trigger }} · প্রাপক: {{ current.audience === 'admin' ? adminTo : 'গ্রাহকের ইমেইল' }}</p>
            </div>
            <label class="flex items-center gap-2 text-xs font-semibold text-admin-text cursor-pointer shrink-0">
              <input v-model="draft.is_active" type="checkbox" class="et-switch" />
              {{ draft.is_active ? 'চালু' : 'বন্ধ' }}
            </label>
          </header>
          <div class="adm-card-body space-y-4">
            <div>
              <label class="adm-label" for="et-subject">বিষয় (Subject)</label>
              <input id="et-subject" ref="subjectEl" v-model="draft.subject" class="adm-input" :class="fieldErr('subject') && 'is-invalid'" maxlength="200" @focus="lastField = 'subject'" @blur="rememberCaret" />
              <p v-if="fieldErr('subject')" class="adm-error">{{ fieldErr('subject') }}</p>
            </div>
            <div>
              <label class="adm-label" for="et-heading">শিরোনাম <span class="font-normal text-admin-muted">(ইমেইলের ভেতরে বড় করে)</span></label>
              <input id="et-heading" ref="headingEl" v-model="draft.heading" class="adm-input" maxlength="200" @focus="lastField = 'heading'" @blur="rememberCaret" />
            </div>
            <div>
              <div class="flex items-end justify-between gap-2">
                <label class="adm-label" for="et-body">লেখা</label>
                <span class="text-[11px] text-admin-muted mb-1.5">ফাঁকা লাইন = নতুন প্যারা · **লেখা** = বোল্ড</span>
              </div>
              <textarea id="et-body" ref="bodyEl" v-model="draft.body" rows="12" class="adm-input font-[inherit]" :class="fieldErr('body') && 'is-invalid'" @focus="lastField = 'body'" @blur="rememberCaret"></textarea>
              <p v-if="fieldErr('body')" class="adm-error">{{ fieldErr('body') }}</p>
            </div>

            <!-- Variables -->
            <div class="rounded-xl bg-admin-bg border border-admin-border p-3">
              <p class="text-[11.5px] text-admin-muted mb-2">
                ক্লিক করলে <strong class="text-admin-text">{{ fieldNames[lastField] }}</strong>-এ বসবে — পাঠানোর সময় আসল তথ্য দিয়ে বদলে যাবে
              </p>
              <div class="flex flex-wrap gap-1.5">
                <button v-for="v in variables" :key="v.key" type="button" class="et-var" :title="`{{${v.key}}}`" @click="insertVar(v.key)">{{ v.label }}</button>
              </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
              <div>
                <label class="adm-label" for="et-btn">বাটনের লেখা <span class="font-normal text-admin-muted">(খালি = বাটন নেই)</span></label>
                <input id="et-btn" v-model="draft.button_label" class="adm-input" maxlength="80" @focus="lastField = 'button_label'" />
              </div>
              <div>
                <label class="adm-label" for="et-btn-url">বাটনের লিংক</label>
                <input id="et-btn-url" ref="buttonUrlEl" v-model="draft.button_url" class="adm-input font-mono text-[12px]" maxlength="255" @focus="lastField = 'button_url'" @blur="rememberCaret" />
              </div>
            </div>

            <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-admin-border">
              <button v-can="'update_settings'" type="button" class="adm-btn adm-btn-primary" :disabled="saving || !dirty" @click="saveTemplate">
                <Check :size="15" /> {{ saving ? 'সেভ হচ্ছে…' : 'সেভ করুন' }}
              </button>
              <button v-if="dirty" type="button" class="adm-btn adm-btn-ghost" @click="discard">পরিবর্তন বাতিল</button>
              <button v-can="'update_settings'" v-if="current.is_customized" type="button" class="adm-btn adm-btn-ghost text-admin-muted ml-auto" @click="resetTemplate">
                <RotateCcw :size="14" /> আগের লেখায় ফেরান
              </button>
            </div>
          </div>
        </section>

        <!-- Preview -->
        <section class="adm-card overflow-hidden min-w-0 2xl:sticky 2xl:top-[88px]">
          <header class="adm-card-head">
            <div class="min-w-0">
              <h3 class="adm-card-title">প্রিভিউ</h3>
              <p class="adm-card-sub truncate">বিষয়: <strong class="text-admin-text">{{ preview.subject || '…' }}</strong></p>
            </div>
            <div class="inline-flex p-0.5 rounded-lg bg-admin-bg shrink-0">
              <button type="button" :class="['p-1.5 rounded-md', device === 'desktop' ? 'bg-white shadow-xs text-admin-text' : 'text-admin-muted']" @click="device = 'desktop'" title="কম্পিউটারে"><Monitor :size="15" /></button>
              <button type="button" :class="['p-1.5 rounded-md', device === 'mobile' ? 'bg-white shadow-xs text-admin-text' : 'text-admin-muted']" @click="device = 'mobile'" title="মোবাইলে"><Smartphone :size="15" /></button>
            </div>
          </header>
          <div class="bg-[#E9ECEA] p-3 sm:p-4 relative">
            <div v-if="previewLoading" class="absolute top-6 right-6 z-10 adm-chip bg-white text-admin-muted shadow-xs"><Loader2 :size="12" class="animate-spin" /> আপডেট হচ্ছে</div>
            <iframe
              :srcdoc="preview.html"
              title="ইমেইল প্রিভিউ"
              sandbox=""
              :class="['block mx-auto bg-white rounded-lg shadow-sm transition-all w-full', device === 'mobile' && '!w-[375px] max-w-full']"
              style="height: 720px; border: 0;"
            ></iframe>
          </div>
          <div class="p-4 border-t border-admin-border">
            <p class="text-[11.5px] text-admin-muted mb-2">{{ previewNote }}</p>
            <form class="flex flex-col sm:flex-row gap-2" @submit.prevent="sendTest">
              <input v-model.trim="testEmail" type="email" required class="adm-input" placeholder="আপনার ইমেইল" aria-label="টেস্ট ইমেইল ঠিকানা" />
              <button v-can="'update_settings'" type="submit" class="adm-btn shrink-0" :disabled="testing || !testEmail"><Send :size="14" /> {{ testing ? 'পাঠানো হচ্ছে…' : 'টেস্ট ইমেইল পাঠান' }}</button>
            </form>
          </div>
        </section>
      </div>
    </div>

    <!-- ============================================================ setup -->
    <div v-show="tab === 'setup'" class="grid lg:grid-cols-[minmax(0,1fr)_340px] gap-5 items-start">
      <form class="adm-card" @submit.prevent="saveSettings">
        <header class="adm-card-head">
          <div><h3 class="adm-card-title">ইমেইল পাঠানোর অ্যাকাউন্ট (SMTP)</h3><p class="adm-card-sub">যে ইমেইল অ্যাকাউন্ট দিয়ে দোকানের ইমেইল যাবে</p></div>
          <label class="flex items-center gap-2 text-xs font-semibold text-admin-text cursor-pointer shrink-0">
            <input v-model="mail.enabled" type="checkbox" class="et-switch" /> {{ mail.enabled ? 'চালু' : 'বন্ধ' }}
          </label>
        </header>
        <div class="adm-card-body space-y-5">
          <div class="grid sm:grid-cols-2 gap-4">
            <div>
              <label class="adm-label" for="m-from-name">প্রেরকের নাম</label>
              <input id="m-from-name" v-model.trim="mail.from_name" class="adm-input" placeholder="DRAWER CLOTHING" />
              <p class="adm-help">গ্রাহক ইনবক্সে এই নাম দেখবে</p>
            </div>
            <div>
              <label class="adm-label" for="m-from">যে ঠিকানা থেকে যাবে</label>
              <input id="m-from" v-model.trim="mail.from_address" type="email" class="adm-input" :class="mailErr('from_address') && 'is-invalid'" placeholder="orders@yourshop.com" />
              <p v-if="mailErr('from_address')" class="adm-error">{{ mailErr('from_address') }}</p>
            </div>
          </div>
          <div>
            <label class="adm-label" for="m-admin">নতুন অর্ডারের খবর যাবে এই ইমেইলে</label>
            <input id="m-admin" v-model.trim="mail.admin_emails" class="adm-input" :class="mailErr('admin_emails') && 'is-invalid'" placeholder="owner@gmail.com, staff@gmail.com" />
            <p v-if="mailErr('admin_emails')" class="adm-error">{{ mailErr('admin_emails') }}</p>
            <p v-else class="adm-help">একাধিক হলে কমা দিন। খালি রাখলে “সাইট কাস্টমাইজ → যোগাযোগ”-এর ইমেইলে যাবে।</p>
          </div>

          <div class="pt-4 border-t border-admin-border grid sm:grid-cols-[1fr_110px_130px] gap-4">
            <div>
              <label class="adm-label" for="m-host">SMTP হোস্ট</label>
              <input id="m-host" v-model.trim="mail.smtp_host" class="adm-input font-mono text-[12.5px]" :class="mailErr('smtp_host') && 'is-invalid'" placeholder="smtp.gmail.com" />
              <p v-if="mailErr('smtp_host')" class="adm-error">{{ mailErr('smtp_host') }}</p>
            </div>
            <div>
              <label class="adm-label" for="m-port">পোর্ট</label>
              <input id="m-port" v-model.number="mail.smtp_port" type="number" class="adm-input" placeholder="587" />
            </div>
            <div>
              <label class="adm-label" for="m-enc">নিরাপত্তা</label>
              <select id="m-enc" v-model="mail.smtp_encryption" class="adm-input">
                <option value="tls">TLS (৫৮৭)</option>
                <option value="ssl">SSL (৪৬৫)</option>
              </select>
            </div>
          </div>
          <div class="grid sm:grid-cols-2 gap-4">
            <div>
              <label class="adm-label" for="m-user">ইউজারনেম</label>
              <input id="m-user" v-model.trim="mail.smtp_username" class="adm-input" autocomplete="off" placeholder="সাধারণত পুরো ইমেইল ঠিকানা" />
            </div>
            <div>
              <label class="adm-label" for="m-pass">পাসওয়ার্ড</label>
              <input id="m-pass" v-model="mail.smtp_password" type="password" class="adm-input" autocomplete="new-password" :placeholder="mail.has_password ? 'সেভ করা আছে — বদলাতে নতুনটা দিন' : 'অ্যাপ পাসওয়ার্ড'" />
            </div>
          </div>

          <div class="flex flex-wrap items-center gap-2 pt-2">
            <button v-can="'@admin'" type="submit" class="adm-btn adm-btn-primary" :disabled="savingMail"><Check :size="15" /> {{ savingMail ? 'সেভ হচ্ছে…' : 'সেভ করুন' }}</button>
            <span class="text-[11.5px] text-admin-muted">সেভ করার পর “টেমপ্লেট” ট্যাব থেকে টেস্ট ইমেইল পাঠিয়ে দেখে নিন।</span>
          </div>
        </div>
      </form>

      <aside class="adm-card">
        <header class="adm-card-head"><h3 class="adm-card-title">Gmail দিয়ে চালু করতে</h3></header>
        <ol class="adm-card-body list-decimal pl-9 space-y-2 text-[12.5px] text-admin-text leading-relaxed">
          <li>Google অ্যাকাউন্টে <strong>২-ধাপ যাচাই (2-Step Verification)</strong> চালু করুন।</li>
          <li><strong>myaccount.google.com/apppasswords</strong> থেকে একটা “App password” বানান।</li>
          <li>হোস্ট <code class="et-code">smtp.gmail.com</code>, পোর্ট <code class="et-code">587</code>, TLS।</li>
          <li>ইউজারনেম = আপনার Gmail, পাসওয়ার্ড = ওই ১৬ অক্ষরের App password।</li>
          <li>“যে ঠিকানা থেকে যাবে”-তেও একই Gmail দিন, তারপর চালু করে সেভ করুন।</li>
        </ol>
        <p class="px-5 pb-5 text-[11.5px] text-admin-muted">নিজের ডোমেইনের ইমেইল (যেমন cPanel/Zoho) থাকলে তাদের দেওয়া SMTP তথ্য দিন — সেটা স্প্যামে কম যায়।</p>
      </aside>
    </div>

    <!-- ============================================================ log -->
    <section v-show="tab === 'log'" class="adm-card overflow-hidden">
      <div class="flex flex-col md:flex-row md:items-center gap-3 p-4 border-b border-admin-border">
        <div class="flex flex-wrap gap-1.5">
          <button v-for="s in logFilters" :key="s.key" type="button" @click="logStatus = s.key"
            :class="['px-3 py-1.5 rounded-full text-[12px] font-semibold border transition-colors', logStatus === s.key ? 'bg-admin-sidebar text-white border-admin-sidebar' : 'bg-white text-admin-muted border-admin-border hover:text-admin-text']">{{ s.label }}</button>
        </div>
        <div class="relative md:ml-auto md:w-72">
          <Search :size="15" class="absolute left-3 top-1/2 -translate-y-1/2 text-admin-muted pointer-events-none" />
          <input v-model="logSearch" type="search" class="adm-input pl-9" placeholder="ইমেইল ঠিকানা বা বিষয়…" />
        </div>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-[13px] min-w-[760px]">
          <thead class="bg-admin-bg text-[11.5px] text-admin-muted">
            <tr>
              <th class="text-left font-semibold px-4 py-2.5">সময়</th>
              <th class="text-left font-semibold px-3 py-2.5">ইমেইল</th>
              <th class="text-left font-semibold px-3 py-2.5">প্রাপক</th>
              <th class="text-left font-semibold px-3 py-2.5">অর্ডার</th>
              <th class="text-left font-semibold px-3 py-2.5">অবস্থা</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="logLoading"><td colspan="5" class="px-4 py-10 text-center text-admin-muted text-xs">লোড হচ্ছে…</td></tr>
            <tr v-else-if="!logs.length"><td colspan="5" class="px-4 py-12 text-center text-admin-muted text-xs">এখনো কোনো ইমেইল যায়নি। নতুন অর্ডার এলে বা টেস্ট পাঠালে এখানে দেখাবে।</td></tr>
            <tr v-for="l in logs" :key="l.id" class="border-t border-admin-border align-top">
              <td class="px-4 py-3 whitespace-nowrap text-admin-muted text-[12px]">{{ fmtTime(l.created_at) }}</td>
              <td class="px-3 py-3 min-w-0">
                <span class="block font-medium text-admin-text max-w-[340px] truncate" :title="l.subject">{{ l.subject }}</span>
                <span class="block text-[11.5px] text-admin-muted">{{ templateName(l.template_key) }}<template v-if="l.is_test"> · টেস্ট</template></span>
              </td>
              <td class="px-3 py-3 text-[12.5px]">{{ l.to_email }}</td>
              <td class="px-3 py-3 text-[12.5px]">
                <router-link v-if="l.order" :to="`/admin/orders?search=${l.order.order_number}`" class="font-mono text-admin-sidebar hover:underline">{{ l.order.order_number }}</router-link>
                <span v-else class="text-admin-muted">—</span>
              </td>
              <td class="px-3 py-3">
                <span :class="['adm-chip', logMeta[l.status]?.chip]">{{ logMeta[l.status]?.label || l.status }}</span>
                <p v-if="l.error" class="text-[11px] text-rose-600 mt-1 max-w-[260px] break-words">{{ l.error }}</p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-if="logMeta_.last_page > 1" class="flex items-center justify-between px-4 py-3 border-t border-admin-border text-xs text-admin-muted">
        <span>মোট {{ bnNumber(logMeta_.total) }}টি</span>
        <div class="flex gap-1">
          <button type="button" class="adm-btn px-2.5" :disabled="logMeta_.current_page <= 1" @click="loadLogs(logMeta_.current_page - 1)"><ChevronLeft :size="15" /></button>
          <span class="px-3 py-2">{{ bnNumber(logMeta_.current_page) }} / {{ bnNumber(logMeta_.last_page) }}</span>
          <button type="button" class="adm-btn px-2.5" :disabled="logMeta_.current_page >= logMeta_.last_page" @click="loadLogs(logMeta_.current_page + 1)"><ChevronRight :size="15" /></button>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted, nextTick } from 'vue';
import {
  Mail, Settings2, History, AlertTriangle, CheckCircle2, Check, RotateCcw, Monitor, Smartphone, Loader2, Send,
  Search, ChevronLeft, ChevronRight,
} from 'lucide-vue-next';
import api from '../../api';
import { useToastStore } from '../../stores/toast';
import { bnNumber } from '../../admin-nav';

const toast = useToastStore();

const tabs = [
  { key: 'templates', label: 'টেমপ্লেট', icon: Mail },
  { key: 'setup', label: 'ইমেইল সেটআপ', icon: Settings2 },
  { key: 'log', label: 'পাঠানো ইমেইল', icon: History },
];
const initialTab = new URLSearchParams(window.location.search).get('tab');
const tab = ref(tabs.some((t) => t.key === initialTab) ? initialTab : 'templates');

// ------------------------------------------------------------------ templates
const loading = ref(true);
const templates = ref([]);
const variables = ref([]);
const status = ref(null);
const adminRecipients = ref([]);
const currentKey = ref(null);
const current = computed(() => templates.value.find((t) => t.key === currentKey.value) || null);
const adminTo = computed(() => adminRecipients.value.join(', ') || 'সেটআপে ইমেইল দিন');

const groups = computed(() => [
  { key: 'customer', label: 'গ্রাহকের কাছে যায়', items: templates.value.filter((t) => t.audience === 'customer') },
  { key: 'admin', label: 'আপনার কাছে আসে', items: templates.value.filter((t) => t.audience === 'admin') },
]);

const FIELDS = ['subject', 'heading', 'body', 'button_label', 'button_url', 'is_active'];
const draft = reactive({ subject: '', heading: '', body: '', button_label: '', button_url: '', is_active: true });
const original = ref('');
const dirty = computed(() => JSON.stringify(pick(draft)) !== original.value);
const pick = (o) => Object.fromEntries(FIELDS.map((f) => [f, o[f] ?? (f === 'is_active' ? true : '')]));
const errors = ref({});
const fieldErr = (k) => errors.value[k]?.[0] || '';

const select = (key) => {
  if (key === currentKey.value) return;
  if (dirty.value && !confirm('এই টেমপ্লেটে সেভ না করা পরিবর্তন আছে। বাদ দিয়ে অন্যটায় যাবেন?')) return;
  currentKey.value = key;
  Object.assign(draft, pick(current.value));
  original.value = JSON.stringify(pick(draft));
  errors.value = {};
  refreshPreview(true);
};
const discard = () => {
  Object.assign(draft, JSON.parse(original.value));
  errors.value = {};
};

const load = async () => {
  try {
    const res = await api.get('/admin/email-templates');
    const d = res.data.data;
    templates.value = d.templates;
    variables.value = d.variables;
    status.value = d.status;
    adminRecipients.value = d.admin_recipients;
    if (!currentKey.value && d.templates.length) select(d.templates[0].key);
  } catch {
    toast.error('টেমপ্লেট লোড হয়নি');
  } finally {
    loading.value = false;
  }
};

const replaceTemplate = (saved) => {
  const i = templates.value.findIndex((t) => t.key === saved.key);
  if (i !== -1) templates.value[i] = { ...templates.value[i], ...saved };
};

const saving = ref(false);
const saveTemplate = async () => {
  saving.value = true;
  errors.value = {};
  try {
    const res = await api.put(`/admin/email-templates/${currentKey.value}`, pick(draft));
    toast.success(res.data.message);
    original.value = JSON.stringify(pick(draft));
    await load();
  } catch (e) {
    if (e.response?.status === 422) errors.value = e.response.data.errors || {};
    toast.error(e.response?.data?.message || 'সেভ হয়নি');
  } finally {
    saving.value = false;
  }
};

const resetTemplate = async () => {
  if (!confirm('এই টেমপ্লেটের লেখা মুছে দোকানের আগের লেখায় ফেরাবেন?')) return;
  try {
    const res = await api.post(`/admin/email-templates/${currentKey.value}/reset`);
    toast.success(res.data.message);
    await load();
    Object.assign(draft, pick(current.value));
    original.value = JSON.stringify(pick(draft));
    refreshPreview(true);
  } catch {
    toast.error('ফেরানো যায়নি');
  }
};

// ------------------------------------------------------------------ variables
const subjectEl = ref(null);
const headingEl = ref(null);
const bodyEl = ref(null);
const buttonUrlEl = ref(null);
const lastField = ref('body');
const fieldNames = { subject: 'বিষয়', heading: 'শিরোনাম', body: 'লেখা', button_label: 'লেখা', button_url: 'বাটনের লিংক' };
const insertVar = async (key) => {
  const field = lastField.value === 'button_label' ? 'body' : lastField.value;
  const el = { subject: subjectEl, heading: headingEl, body: bodyEl, button_url: buttonUrlEl }[field]?.value;
  const token = `{{${key}}}`;
  const text = draft[field] || '';
  const start = el && document.activeElement === el ? el.selectionStart : el?._lastPos ?? text.length;
  const end = el && document.activeElement === el ? el.selectionEnd : start;
  draft[field] = text.slice(0, start) + token + text.slice(end);
  await nextTick();
  if (el) {
    el.focus();
    const pos = start + token.length;
    el.setSelectionRange(pos, pos);
    el._lastPos = pos;
  }
};
// remember the caret when a field loses focus (clicking a chip blurs the field)
const rememberCaret = (e) => { e.target._lastPos = e.target.selectionStart; };

// ------------------------------------------------------------------ preview
const preview = reactive({ subject: '', html: '' });
const previewLoading = ref(false);
const device = ref('desktop');
const previewNote = 'প্রিভিউতে আপনার সর্বশেষ অর্ডারের তথ্য দেখানো হচ্ছে। টেস্ট ইমেইলেও একই তথ্য যাবে, গ্রাহকের কাছে কিছু যাবে না।';
let previewTimer;
let previewReq = 0;
const refreshPreview = (now = false) => {
  clearTimeout(previewTimer);
  previewTimer = setTimeout(async () => {
    if (!currentKey.value) return;
    const id = ++previewReq;
    previewLoading.value = true;
    try {
      const { is_active, ...content } = pick(draft);
      const res = await api.post(`/admin/email-templates/${currentKey.value}/preview`, content);
      if (id !== previewReq) return;
      preview.subject = res.data.data.subject;
      preview.html = res.data.data.html;
    } catch {
      /* keep the last good preview while the text is invalid (e.g. empty body) */
    } finally {
      if (id === previewReq) previewLoading.value = false;
    }
  }, now ? 0 : 450);
};
watch(() => [draft.subject, draft.heading, draft.body, draft.button_label, draft.button_url], () => refreshPreview());

const testEmail = ref('');
const testing = ref(false);
const sendTest = async () => {
  testing.value = true;
  try {
    const { is_active, ...content } = pick(draft);
    const res = await api.post(`/admin/email-templates/${currentKey.value}/test`, { email: testEmail.value, ...content });
    status.value?.ready ? toast.success(res.data.message) : toast.warning(res.data.message, 6000);
    try { localStorage.setItem('adm_test_email', testEmail.value); } catch { /* private mode */ }
    if (tab.value === 'log' || logsLoaded) loadLogs(1);
  } catch (e) {
    toast.error(e.response?.data?.message || 'পাঠানো যায়নি');
  } finally {
    testing.value = false;
  }
};

// ------------------------------------------------------------------ setup
const mail = reactive({ enabled: false, from_name: '', from_address: '', admin_emails: '', smtp_host: '', smtp_port: 587, smtp_encryption: 'tls', smtp_username: '', smtp_password: '', has_password: false });
const mailErrors = ref({});
const mailErr = (k) => mailErrors.value[k]?.[0] || '';
const savingMail = ref(false);
const loadSettings = async () => {
  try {
    const res = await api.get('/admin/email-settings');
    const { status: s, ...rest } = res.data.data;
    Object.assign(mail, rest, { smtp_password: '' });
  } catch { /* shown as empty form */ }
};
const saveSettings = async () => {
  savingMail.value = true;
  mailErrors.value = {};
  try {
    const { has_password, ...payload } = mail;
    const res = await api.put('/admin/email-settings', payload);
    const { status: s, ...rest } = res.data.data;
    Object.assign(mail, rest, { smtp_password: '' });
    status.value = s;
    toast.success(res.data.message);
  } catch (e) {
    if (e.response?.status === 422) mailErrors.value = e.response.data.errors || {};
    toast.error(e.response?.data?.message || 'সেভ হয়নি');
  } finally {
    savingMail.value = false;
  }
};

// ------------------------------------------------------------------ log
const logs = ref([]);
const logLoading = ref(false);
const logMeta_ = ref({ current_page: 1, last_page: 1, total: 0 });
const logStatus = ref('');
const logSearch = ref('');
let logsLoaded = false;
const logFilters = [
  { key: '', label: 'সব' },
  { key: 'sent', label: 'পাঠানো হয়েছে' },
  { key: 'logged', label: 'শুধু লগে' },
  { key: 'failed', label: 'ব্যর্থ' },
];
const logMeta = {
  sent: { label: 'পাঠানো হয়েছে', chip: 'bg-emerald-50 text-emerald-700' },
  logged: { label: 'শুধু লগে (ইমেইল বন্ধ)', chip: 'bg-amber-50 text-amber-700' },
  failed: { label: 'ব্যর্থ', chip: 'bg-rose-50 text-rose-700' },
};
const templateName = (key) => templates.value.find((t) => t.key === key)?.name || key;
const fmtTime = (iso) => {
  const d = new Date(iso);
  return d.toLocaleString('bn-BD', { day: 'numeric', month: 'short', hour: 'numeric', minute: '2-digit' });
};
const loadLogs = async (page = 1) => {
  logsLoaded = true;
  logLoading.value = true;
  try {
    const res = await api.get('/admin/email-logs', { params: { page, status: logStatus.value || undefined, search: logSearch.value || undefined } });
    logs.value = res.data.data;
    logMeta_.value = res.data.meta;
  } catch {
    toast.error('লগ লোড হয়নি');
  } finally {
    logLoading.value = false;
  }
};
let logTimer;
watch(logSearch, () => { clearTimeout(logTimer); logTimer = setTimeout(() => loadLogs(1), 300); });
watch(logStatus, () => loadLogs(1));
watch(tab, (t) => {
  if (t === 'log') loadLogs(1);
  const url = new URL(window.location.href);
  t === 'templates' ? url.searchParams.delete('tab') : url.searchParams.set('tab', t);
  window.history.replaceState(window.history.state, '', url);
}, { immediate: false });

onMounted(() => {
  try { testEmail.value = localStorage.getItem('adm_test_email') || ''; } catch { /* private mode */ }
  load();
  loadSettings();
  if (tab.value === 'log') loadLogs(1);
});
</script>

<style scoped>
.et-var {
  font-size: 11.5px; font-weight: 600; padding: 3px 9px; border-radius: 999px; background: #fff; color: var(--color-admin-sidebar);
  border: 1px solid #CFE3D7; transition: background .15s;
}
.et-var:hover { background: #EEF6F1; }
.et-code { font-family: ui-monospace, monospace; font-size: 11.5px; padding: 1px 5px; border-radius: 5px; background: var(--color-admin-bg); }
.et-switch {
  appearance: none; flex-shrink: 0; width: 34px; height: 20px; border-radius: 999px; background: #D5DCD8; position: relative; cursor: pointer; transition: background .15s;
}
.et-switch::after {
  content: ''; position: absolute; top: 2px; left: 2px; width: 16px; height: 16px; border-radius: 999px; background: #fff;
  box-shadow: 0 1px 2px rgba(0, 0, 0, .2); transition: transform .15s;
}
.et-switch:checked { background: var(--color-admin-accent); }
.et-switch:checked::after { transform: translateX(14px); }
.et-switch:focus-visible { outline: 2px solid var(--color-admin-accent); outline-offset: 2px; }
</style>
