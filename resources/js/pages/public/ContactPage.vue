<template>
  <div class="py-12 sm:py-20 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
    <header class="text-center max-w-xl mx-auto mb-10 sm:mb-14 space-y-3" v-reveal>
      <p class="text-[11px] tracking-normal text-[#A89B8A] font-medium">গ্রাহক সহায়তা</p>
      <h1 class="font-serif text-3xl sm:text-5xl text-[#111111]">যোগাযোগ করুন</h1>
      <p class="text-sm text-[#69736E] leading-relaxed">সাইজ, ডেলিভারি বা অর্ডার — যেকোনো প্রশ্নে আমরা আছি। সাধারণত কয়েক ঘণ্টার মধ্যেই উত্তর দিই।</p>
    </header>

    <!-- Quick actions -->
    <!-- auto-fit: 2 or 3 cards always fill the row (WhatsApp card only shows when a number is set) -->
    <div class="grid grid-cols-1 sm:grid-cols-[repeat(auto-fit,minmax(220px,1fr))] gap-3 mb-10" v-reveal="{ delay: 80 }">
      <a v-if="site.contact.phone" :href="`tel:${phoneDigits(site.contact.phone)}`" class="group flex items-center gap-3 p-4 bg-white border border-[#D8CCBC]/50 rounded-sm hover:border-[#111111] transition-colors">
        <span class="w-10 h-10 rounded-full bg-[#F7F5F0] flex items-center justify-center group-hover:bg-[#111111] group-hover:text-white transition-colors"><Phone class="w-4 h-4" /></span>
        <span><span class="block text-[12px] text-[#69736E]">কল করুন</span><span class="block text-sm font-semibold text-[#111111]">{{ site.contact.phone }}</span></span>
      </a>
      <a v-if="whatsapp" :href="`https://wa.me/${whatsapp}`" target="_blank" rel="noopener" class="group flex items-center gap-3 p-4 bg-white border border-[#D8CCBC]/50 rounded-sm hover:border-[#25D366] transition-colors">
        <span class="w-10 h-10 rounded-full bg-[#F7F5F0] flex items-center justify-center group-hover:bg-[#25D366] group-hover:text-white transition-colors"><SocialIcon platform="whatsapp" class="w-4 h-4" /></span>
        <span><span class="block text-[12px] text-[#69736E]">হোয়াটসঅ্যাপ</span><span class="block text-sm font-semibold text-[#111111]">মেসেজ পাঠান</span></span>
      </a>
      <a v-if="site.contact.email" :href="`mailto:${site.contact.email}`" class="group flex items-center gap-3 p-4 bg-white border border-[#D8CCBC]/50 rounded-sm hover:border-[#111111] transition-colors min-w-0">
        <span class="w-10 h-10 rounded-full bg-[#F7F5F0] flex items-center justify-center shrink-0 group-hover:bg-[#111111] group-hover:text-white transition-colors"><Mail class="w-4 h-4" /></span>
        <span class="min-w-0"><span class="block text-[12px] text-[#69736E]">ইমেইল</span><span class="block text-sm font-semibold text-[#111111] truncate">{{ site.contact.email }}</span></span>
      </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-10 items-start">
      <!-- Form -->
      <section class="lg:col-span-7 bg-white p-6 sm:p-8 border border-[#D8CCBC]/40 rounded-sm" v-reveal="{ delay: 120 }">
        <Transition name="fade" mode="out-in">
          <div v-if="submitted" key="ok" class="py-10 text-center space-y-4">
            <div class="w-14 h-14 mx-auto rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center"><Check class="w-7 h-7" /></div>
            <h2 class="font-serif text-2xl text-[#111111]">বার্তাটি পৌঁছেছে, ধন্যবাদ!</h2>
            <p class="text-sm text-[#69736E] max-w-sm mx-auto">আমাদের প্রতিনিধি খুব শিগগিরই {{ form.phone ? 'ফোনে বা ' : '' }}ইমেইলে আপনার সাথে যোগাযোগ করবেন।</p>
            <button type="button" @click="resetForm" class="text-sm font-medium underline underline-offset-4 text-[#111111]">আরেকটি বার্তা পাঠান</button>
          </div>

          <form v-else key="form" @submit.prevent="handleSubmit" class="space-y-5" novalidate>
            <h2 class="font-serif text-xl text-[#111111]">আমাদের লিখুন</h2>

            <Field label="আপনার নাম" required :error="errors.name">
              <input v-model.trim="form.name" type="text" autocomplete="name" :class="inputClass('name')" placeholder="যেমন: রাহাত হোসেন" />
            </Field>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
              <Field label="ইমেইল" required :error="errors.email">
                <input v-model.trim="form.email" type="email" autocomplete="email" inputmode="email" :class="inputClass('email')" placeholder="you@example.com" />
              </Field>
              <Field label="মোবাইল নম্বর" :error="errors.phone">
                <input v-model.trim="form.phone" type="tel" autocomplete="tel" inputmode="tel" :class="inputClass('phone')" placeholder="01XXXXXXXXX" />
              </Field>
            </div>

            <div>
              <p class="text-[13px] font-medium text-[#111111] mb-2">বিষয়</p>
              <div class="flex flex-wrap gap-2">
                <button
                  v-for="t in topics"
                  :key="t"
                  type="button"
                  @click="form.subject = form.subject === t ? '' : t"
                  :class="['px-3.5 py-1.5 rounded-full border text-[13px] transition-all', form.subject === t ? 'bg-[#111111] text-white border-[#111111]' : 'border-[#D8CCBC] text-[#111111]/80 hover:border-[#111111]']"
                >{{ t }}</button>
              </div>
            </div>

            <Field label="আপনার বার্তা" required :error="errors.message">
              <textarea v-model.trim="form.message" rows="5" :class="inputClass('message')" placeholder="আপনার প্রশ্ন বা মতামত লিখুন…"></textarea>
            </Field>

            <p v-if="errors._form" class="text-sm text-rose-600">{{ errors._form }}</p>

            <button
              type="submit"
              :disabled="loading"
              class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-3.5 bg-[#111111] text-white text-sm font-medium rounded-sm hover:bg-black active:scale-[0.99] transition-all disabled:opacity-60"
            >
              <span v-if="loading" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
              {{ loading ? 'পাঠানো হচ্ছে…' : 'বার্তা পাঠান' }}
              <Send v-if="!loading" class="w-4 h-4" />
            </button>
          </form>
        </Transition>
      </section>

      <!-- Info -->
      <aside class="lg:col-span-5 space-y-4" v-reveal="{ delay: 180 }">
        <div class="bg-white p-6 border border-[#D8CCBC]/40 rounded-sm space-y-5 text-sm">
          <div v-if="site.contact.address" class="flex gap-3">
            <MapPin class="w-5 h-5 text-[#A89B8A] shrink-0" />
            <div><p class="font-medium text-[#111111] mb-1">আমাদের ঠিকানা</p><p class="text-[#69736E] whitespace-pre-line leading-relaxed">{{ site.contact.address }}</p></div>
          </div>
          <div v-if="site.contact.hours" class="flex gap-3 pt-5 border-t border-[#D8CCBC]/30">
            <Clock class="w-5 h-5 text-[#A89B8A] shrink-0" />
            <div><p class="font-medium text-[#111111] mb-1">সেবার সময়</p><p class="text-[#69736E] whitespace-pre-line leading-relaxed">{{ site.contact.hours }}</p></div>
          </div>
          <div v-if="site.social.length" class="flex items-center gap-2 pt-5 border-t border-[#D8CCBC]/30">
            <a v-for="s in site.social" :key="s.platform" :href="s.url" target="_blank" rel="noopener" :aria-label="s.platform"
               class="w-9 h-9 rounded-full border border-[#D8CCBC] flex items-center justify-center text-[#111111]/70 hover:bg-[#111111] hover:text-white hover:border-[#111111] transition-all">
              <SocialIcon :platform="s.platform" class="w-4 h-4" />
            </a>
          </div>
        </div>
        <div v-if="site.contact.map_url" class="aspect-[4/3] bg-white border border-[#D8CCBC]/40 rounded-sm overflow-hidden">
          <iframe :src="site.contact.map_url" class="w-full h-full" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="ম্যাপ"></iframe>
        </div>
      </aside>
    </div>

    <!-- FAQ -->
    <section v-if="faq.length" class="mt-16 sm:mt-24 grid lg:grid-cols-12 gap-8 lg:gap-12">
      <div class="lg:col-span-4" v-reveal>
        <h2 class="font-serif text-3xl text-[#111111]">{{ contactPage.faq_title || 'সচরাচর জিজ্ঞাসা' }}</h2>
        <p class="mt-3 text-sm text-[#69736E] leading-relaxed">উত্তর না পেলে উপরের ফর্মে লিখুন বা সরাসরি কল করুন — আমরা সাহায্য করব।</p>
      </div>
      <div class="lg:col-span-8 divide-y divide-[#E5DED3] border-y border-[#E5DED3]" v-reveal="{ delay: 100 }">
        <div v-for="(item, i) in faq" :key="i">
          <button type="button" @click="openFaq = openFaq === i ? null : i" :aria-expanded="openFaq === i"
                  class="w-full flex items-center justify-between gap-4 py-5 text-left">
            <span class="text-[15px] font-medium text-[#111111]">{{ item.q }}</span>
            <Plus :class="['w-5 h-5 shrink-0 text-[#A89B8A] transition-transform duration-300', openFaq === i && 'rotate-45 text-[#111111]']" />
          </button>
          <div class="grid transition-[grid-template-rows] duration-300 ease-out" :style="{ gridTemplateRows: openFaq === i ? '1fr' : '0fr' }">
            <div class="overflow-hidden"><p class="pb-5 pr-10 text-[14.5px] text-[#69736E] leading-relaxed">{{ item.a }}</p></div>
          </div>
        </div>
      </div>
    </section>

    <!-- Order help -->
    <section class="mt-16 sm:mt-20 p-8 sm:p-10 bg-white border border-[#E5DED3] flex flex-col sm:flex-row sm:items-center justify-between gap-5" v-reveal>
      <div>
        <h3 class="font-serif text-2xl text-[#111111]">অর্ডার নিয়ে প্রশ্ন?</h3>
        <p class="mt-1 text-sm text-[#69736E]">অর্ডার নম্বর দিয়ে এখনই দেখে নিন আপনার পার্সেল কোথায় আছে।</p>
      </div>
      <router-link to="/track-order" class="shrink-0 h-11 px-6 inline-flex items-center gap-2 bg-[#111111] text-white text-sm rounded-sm hover:bg-black transition-colors">
        অর্ডার ট্র্যাক করুন <ArrowRight class="w-4 h-4" />
      </router-link>
    </section>
  </div>
</template>

<script setup>
import { ref, reactive, computed, h } from 'vue';
import { Phone, Mail, MapPin, Clock, Check, Send, Plus, ArrowRight } from 'lucide-vue-next';
import api from '../../api';
import { useSiteStore, phoneDigits } from '../../stores/site';
import SocialIcon from '../../components/common/SocialIcon.vue';

const site = useSiteStore();
const whatsapp = computed(() => phoneDigits(site.contact.whatsapp));
const contactPage = computed(() => site.pages.contact || {});
const faq = computed(() => contactPage.value.faq || []);
const openFaq = ref(0);
const topics = ['সাইজ পরামর্শ', 'অর্ডার সংক্রান্ত', 'ডেলিভারি', 'রিটার্ন / এক্সচেঞ্জ', 'অন্যান্য'];

// Small labelled field wrapper with inline error
const Field = (props, { slots }) => h('label', { class: 'block' }, [
  h('span', { class: 'block text-[13px] font-medium text-[#111111] mb-1.5' }, [props.label, props.required ? h('span', { class: 'text-rose-500 ml-0.5' }, '*') : null]),
  slots.default?.(),
  props.error ? h('span', { class: 'block mt-1 text-[12px] text-rose-600' }, props.error) : null,
]);
Field.props = ['label', 'required', 'error'];

const blank = () => ({ name: '', email: '', phone: '', subject: '', message: '' });
const form = reactive(blank());
const errors = reactive({});
const loading = ref(false);
const submitted = ref(false);

const inputClass = (field) => [
  'w-full px-4 py-3 bg-[#F7F5F0] border rounded-sm text-sm text-[#111111] placeholder-[#A89B8A] transition-colors focus:outline-none focus:bg-white',
  errors[field] ? 'border-rose-400 focus:border-rose-500' : 'border-[#E5DED3] focus:border-[#111111]',
];

const validate = () => {
  Object.keys(errors).forEach((k) => delete errors[k]);
  if (!form.name) errors.name = 'নাম লিখুন';
  if (!form.email) errors.email = 'ইমেইল লিখুন';
  else if (!/^\S+@\S+\.\S+$/.test(form.email)) errors.email = 'সঠিক ইমেইল লিখুন';
  if (form.phone && !/^[+\d][\d\s-]{6,}$/.test(form.phone)) errors.phone = 'সঠিক মোবাইল নম্বর লিখুন';
  if (!form.message) errors.message = 'বার্তা লিখুন';
  return !Object.keys(errors).length;
};

const handleSubmit = async () => {
  if (!validate()) return;
  loading.value = true;
  try {
    await api.post('/public/contact', form);
    submitted.value = true;
  } catch (err) {
    const serverErrors = err.response?.data?.errors;
    if (serverErrors) Object.entries(serverErrors).forEach(([k, v]) => { errors[k] = v[0]; });
    else errors._form = 'বার্তা পাঠানো যায়নি। ইন্টারনেট সংযোগ দেখে আবার চেষ্টা করুন।';
  } finally {
    loading.value = false;
  }
};

const resetForm = () => {
  Object.assign(form, blank());
  submitted.value = false;
};
</script>
