<template>
  <section class="nl" aria-labelledby="nl-heading">
    <!-- same container as the header and footer -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="nl-card">
      <span class="nl-glow" aria-hidden="true"></span>

      <!-- Left: pitch -->
      <div class="nl-copy">
        <p class="nl-kicker"><span aria-hidden="true"></span>{{ content.kicker || 'নিউজলেটার' }}</p>
        <h2 id="nl-heading" class="nl-heading">{{ content.heading || 'ফ্যাশন ও অফারের সাথে যুক্ত থাকুন' }}</h2>
        <p class="nl-desc">
          {{ content.description || 'নতুন কালেকশন রিলিজ, বিশেষ ডিসকাউন্ট এবং সিজনাল ক্যাম্পেইনের আপডেট সবার আগে পেতে যুক্ত হোন।' }}
        </p>
        <ul v-if="benefits.length" class="nl-benefits">
          <li v-for="b in benefits" :key="b">
            <span class="nl-tick" aria-hidden="true"><Check :size="13" :stroke-width="2.5" /></span>{{ b }}
          </li>
        </ul>
      </div>

      <!-- Right: form / thank-you -->
      <div class="nl-side">
        <Transition name="nl-swap" mode="out-in">
          <div v-if="done" key="done" class="nl-done" role="status">
            <span class="nl-done-icon" aria-hidden="true"><MailCheck :size="26" :stroke-width="1.75" /></span>
            <p class="nl-done-title">{{ doneMessage }}</p>
            <p class="nl-done-text">নতুন কালেকশন আর অফারের খবর যাবে <strong>{{ doneEmail }}</strong> ঠিকানায়।</p>
            <button type="button" class="nl-again" @click="reset">অন্য ইমেইল দিন</button>
          </div>

          <form v-else key="form" class="nl-form" novalidate @submit.prevent="handleSubmit">
            <label for="nl-email" class="nl-label">আপনার ইমেইল</label>
            <div class="nl-row" :class="error && 'has-error'">
              <Mail :size="18" class="nl-row-icon" aria-hidden="true" />
              <input
                id="nl-email"
                ref="inputEl"
                v-model.trim="email"
                type="email"
                inputmode="email"
                autocomplete="email"
                :placeholder="content.placeholder || 'আপনার ইমেইল লিখুন...'"
                :aria-invalid="error ? 'true' : undefined"
                aria-describedby="nl-error nl-note"
                @input="error = ''"
              />
              <button type="submit" class="nl-btn" :disabled="loading">
                <Loader2 v-if="loading" :size="16" class="animate-spin" aria-hidden="true" />
                <span>{{ loading ? 'যুক্ত হচ্ছে…' : (content.button_text || 'সাবস্ক্রাইব') }}</span>
                <ArrowRight v-if="!loading" :size="16" aria-hidden="true" />
              </button>
            </div>
            <!-- people never see this field; bots fill it in -->
            <input v-model="website" type="text" name="website" tabindex="-1" autocomplete="off" class="nl-hp" aria-hidden="true" />
            <p id="nl-error" class="nl-error" aria-live="polite">{{ error }}</p>
            <p id="nl-note" class="nl-note">
              <ShieldCheck :size="14" aria-hidden="true" />
              {{ content.disclaimer || 'আপনার ইমেইল কারো সাথে শেয়ার করা হয় না। শুধু দরকারি খবর আর অফার পাঠাই।' }}
            </p>
          </form>
        </Transition>
      </div>
    </div>
    </div>
  </section>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Check, Mail, MailCheck, ArrowRight, Loader2, ShieldCheck } from 'lucide-vue-next';
import api from '../../api';

const props = defineProps({
  content: { type: Object, default: () => ({}) },
});

const DEFAULT_BENEFITS = ['নতুন কালেকশন সবার আগে দেখুন', 'শুধু সাবস্ক্রাইবারদের জন্য বিশেষ ছাড়', 'উৎসব ও সিজনাল অফারের খবর'];
// Benefit lines from the builder; all empty = the standard three. show_benefits:false hides the list.
const benefits = computed(() => {
  if (props.content.show_benefits === false) return [];
  const saved = (Array.isArray(props.content.benefits) ? props.content.benefits : []).map((b) => String(b || '').trim()).filter(Boolean);
  return saved.length ? saved : DEFAULT_BENEFITS;
});

const STORE_KEY = 'drawer_newsletter_email';
const email = ref('');
const website = ref('');
const loading = ref(false);
const error = ref('');
const done = ref(false);
const doneEmail = ref('');
const doneMessage = ref('');
const inputEl = ref(null);

const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;

const handleSubmit = async () => {
  if (!email.value) {
    error.value = 'ইমেইল ঠিকানা লিখুন।';
    inputEl.value?.focus();
    return;
  }
  if (!EMAIL_RE.test(email.value)) {
    error.value = 'সঠিক ইমেইল ঠিকানা লিখুন (যেমন name@gmail.com)।';
    inputEl.value?.focus();
    return;
  }
  loading.value = true;
  error.value = '';
  try {
    const res = await api.post('/public/newsletter/subscribe', { email: email.value, website: website.value });
    doneEmail.value = email.value.toLowerCase();
    doneMessage.value = res.data?.message || 'ধন্যবাদ! আপনি যুক্ত হয়েছেন।';
    done.value = true;
    try { localStorage.setItem(STORE_KEY, doneEmail.value); } catch { /* private mode */ }
  } catch (err) {
    const s = err.response?.status;
    error.value = s === 422
      ? (err.response.data?.errors?.email?.[0] || 'সঠিক ইমেইল ঠিকানা লিখুন।')
      : s === 429
        ? 'অল্প সময়ে অনেকবার চেষ্টা হয়েছে। এক মিনিট পরে আবার চেষ্টা করুন।'
        : 'এখন যুক্ত করা যাচ্ছে না। একটু পরে আবার চেষ্টা করুন।';
  } finally {
    loading.value = false;
  }
};

const reset = () => {
  done.value = false;
  email.value = '';
  try { localStorage.removeItem(STORE_KEY); } catch { /* private mode */ }
};

// Someone who already subscribed on this browser sees the thank-you, not the form again.
onMounted(() => {
  try {
    const saved = localStorage.getItem(STORE_KEY);
    if (saved) {
      doneEmail.value = saved;
      doneMessage.value = 'আপনি আমাদের তালিকায় আছেন — ধন্যবাদ!';
      done.value = true;
    }
  } catch { /* private mode */ }
});
</script>

<style scoped>
/* Brand: ink #111111, ivory #F7F5F0, gold #C5A880, taupe #A89B8A (6.9:1 on ink) */
.nl { background: #F7F5F0; padding: 88px 0 96px; }
.nl-card {
  position: relative; overflow: hidden; border-radius: 28px;
  background: #111111; color: #F7F5F0; padding: 72px clamp(40px, 6vw, 96px);
  display: grid; grid-template-columns: minmax(0, 1.1fr) minmax(0, 1fr); gap: 64px; align-items: center;
}
.nl-glow {
  position: absolute; width: 520px; height: 520px; right: -160px; top: -220px; border-radius: 999px; pointer-events: none;
  background: radial-gradient(closest-side, rgba(197, 168, 128, 0.22), rgba(197, 168, 128, 0));
}
.nl-copy, .nl-side { position: relative; }

.nl-kicker { display: flex; align-items: center; gap: 10px; font-size: 13px; font-weight: 500; color: #C5A880; }
.nl-kicker > span { width: 28px; height: 1px; background: #C5A880; }
.nl-heading { margin-top: 14px; font-family: var(--font-serif); font-weight: 400; font-size: 42px; line-height: 1.15; color: #F7F5F0; }
.nl-desc { margin-top: 14px; font-size: 15px; line-height: 1.75; color: #A89B8A; max-width: 520px; }
.nl-benefits { margin-top: 24px; display: grid; gap: 12px; }
.nl-benefits li { display: flex; align-items: center; gap: 12px; font-size: 14.5px; color: #F7F5F0; }
.nl-tick {
  width: 24px; height: 24px; flex-shrink: 0; border-radius: 999px; display: flex; align-items: center; justify-content: center;
  background: rgba(197, 168, 128, 0.16); color: #C5A880;
}

/* form */
.nl-side { background: rgba(247, 245, 240, 0.04); border: 1px solid rgba(247, 245, 240, 0.1); border-radius: 22px; padding: 32px; }
.nl-label { display: block; font-size: 13px; font-weight: 500; color: #F7F5F0; margin-bottom: 10px; }
.nl-row {
  display: flex; align-items: center; gap: 8px; padding: 6px 6px 6px 18px; border-radius: 999px;
  background: rgba(247, 245, 240, 0.06); border: 1px solid rgba(247, 245, 240, 0.18); transition: border-color .2s, box-shadow .2s;
}
.nl-row:focus-within { border-color: #C5A880; box-shadow: 0 0 0 3px rgba(197, 168, 128, 0.2); }
.nl-row.has-error { border-color: #FDA4AF; }
.nl-row-icon { color: #A89B8A; flex-shrink: 0; }
.nl-row input {
  flex: 1; min-width: 0; height: 44px; background: transparent; border: 0; outline: none;
  font-size: 15px; color: #F7F5F0;
}
.nl-row input::placeholder { color: rgba(247, 245, 240, 0.45); }
.nl-btn {
  height: 46px; padding: 0 22px; border-radius: 999px; flex-shrink: 0; display: inline-flex; align-items: center; gap: 8px;
  background: #C5A880; color: #111111; font-size: 14.5px; font-weight: 600; transition: background .2s, transform .2s;
}
.nl-btn:hover:not(:disabled) { background: #D3BB98; }
.nl-btn:hover:not(:disabled) svg:last-child { transform: translateX(2px); }
.nl-btn svg { transition: transform .2s; }
.nl-btn:disabled { opacity: .7; cursor: wait; }
.nl-btn:focus-visible { outline: 2px solid #F7F5F0; outline-offset: 2px; }
.nl-hp { position: absolute; left: -9999px; width: 1px; height: 1px; opacity: 0; }
.nl-error { min-height: 20px; margin-top: 8px; font-size: 13px; color: #FDA4AF; }
.nl-note { display: flex; align-items: flex-start; gap: 8px; font-size: 12.5px; line-height: 1.6; color: #A89B8A; }
.nl-note svg { flex-shrink: 0; margin-top: 2px; color: #C5A880; }

/* thank-you */
.nl-done { text-align: center; padding: 8px 0; }
.nl-done-icon {
  width: 60px; height: 60px; margin: 0 auto; border-radius: 999px; display: flex; align-items: center; justify-content: center;
  background: #C5A880; color: #111111;
}
.nl-done-title { margin-top: 18px; font-family: var(--font-serif); font-size: 24px; line-height: 1.3; color: #F7F5F0; }
.nl-done-text { margin-top: 8px; font-size: 14px; line-height: 1.6; color: #A89B8A; word-break: break-word; }
.nl-done-text strong { color: #F7F5F0; font-weight: 500; }
.nl-again { margin-top: 16px; font-size: 13px; color: #C5A880; text-decoration: underline; text-underline-offset: 4px; }

.nl-swap-enter-active, .nl-swap-leave-active { transition: opacity .3s ease, transform .3s ease; }
.nl-swap-enter-from { opacity: 0; transform: translateY(8px); }
.nl-swap-leave-to { opacity: 0; transform: translateY(-8px); }

/* tablet */
@media (max-width: 1023.98px) {
  .nl { padding: 64px 0 72px; }
  .nl-card { grid-template-columns: 1fr; gap: 36px; padding: 48px 40px; }
  .nl-heading { font-size: 36px; }
}
/* phone */
@media (max-width: 639.98px) {
  .nl { padding: 48px 0 56px; }
  .nl-card { padding: 36px 22px; border-radius: 24px; gap: 28px; }
  .nl-heading { font-size: 28px; }
  .nl-desc { font-size: 14px; }
  .nl-side { padding: 20px; border-radius: 18px; }
  /* input and button stacked, both full width */
  .nl-row { flex-wrap: wrap; border-radius: 16px; padding: 6px 6px 6px 14px; }
  .nl-row input { flex-basis: calc(100% - 30px); }
  .nl-btn { width: 100%; justify-content: center; border-radius: 12px; }
}
@media (prefers-reduced-motion: reduce) {
  .nl-swap-enter-active, .nl-swap-leave-active { transition: none; }
}
</style>
