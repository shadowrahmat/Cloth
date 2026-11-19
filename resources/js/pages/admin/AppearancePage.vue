<template>
  <div class="space-y-4">
    <!-- Title + actions (sticky so Save is always reachable) -->
    <div class="sticky top-16 z-20 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-3 bg-[#F5F7F6]/95 backdrop-blur flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-[#ECEFED]">
      <div>
        <h2 class="text-lg font-bold text-[#171C1A]">সাইট কাস্টমাইজ</h2>
        <p class="text-[12.5px] text-[#69736E]">লোগো, হেডার মেনু, ঘোষণা বার, ফুটার, যোগাযোগ ও SEO — এখানে বদলালেই পুরো ওয়েবসাইটে বদলে যাবে।</p>
      </div>
      <div class="flex items-center gap-2">
        <span v-if="dirty" class="hidden sm:inline-flex items-center gap-1.5 text-[12px] text-amber-700"><span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>সংরক্ষণ করা হয়নি</span>
        <a href="/" target="_blank" rel="noopener" class="h-9 px-3 inline-flex items-center gap-1.5 rounded-lg text-[13px] text-[#4B5450] hover:bg-white"><ExternalLink class="w-4 h-4" /> সাইট দেখুন</a>
        <button type="button" @click="resetAll" class="h-9 px-3 rounded-lg text-[13px] text-[#4B5450] hover:bg-white">ডিফল্টে ফেরান</button>
        <button
          type="button"
          @click="save"
          :disabled="saving || !dirty"
          class="h-9 px-4 inline-flex items-center gap-2 rounded-lg bg-[#14382A] hover:bg-[#16A36A] text-white text-[13px] font-semibold disabled:opacity-40 transition-colors"
        >
          <span v-if="saving" class="w-3.5 h-3.5 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
          <Save v-else class="w-4 h-4" />
          সংরক্ষণ করুন
        </button>
      </div>
    </div>

    <div v-if="errorList.length" class="p-3.5 rounded-lg bg-rose-50 border border-rose-200 text-[13px] text-rose-700">
      <p class="font-semibold mb-1">কিছু তথ্য ঠিক করতে হবে:</p>
      <ul class="list-disc pl-5 space-y-0.5"><li v-for="(e, i) in errorList" :key="i">{{ e }}</li></ul>
    </div>

    <div v-if="!cfg" class="grid lg:grid-cols-[220px_1fr] gap-4">
      <div class="h-64 bg-white rounded-xl animate-pulse"></div><div class="h-96 bg-white rounded-xl animate-pulse"></div>
    </div>

    <div v-else class="grid lg:grid-cols-[220px_1fr] gap-4 items-start">
      <!-- Tabs -->
      <nav class="flex lg:flex-col gap-1 overflow-x-auto no-scrollbar lg:sticky lg:top-36">
        <button
          v-for="t in tabs"
          :key="t.key"
          type="button"
          @click="tab = t.key"
          :class="[
            'shrink-0 flex items-center gap-2.5 px-3 h-10 rounded-lg text-[13.5px] text-left transition-colors',
            tab === t.key ? 'bg-white text-[#14382A] font-semibold shadow-sm border border-[#ECEFED]' : 'text-[#4B5450] hover:bg-white/70',
          ]"
        >
          <component :is="t.icon" class="w-4 h-4 shrink-0" /> {{ t.label }}
        </button>
      </nav>

      <div class="space-y-4 min-w-0">
        <!-- ============ BRANDING ============ -->
        <template v-if="tab === 'branding'">
          <Card title="ব্র্যান্ডের নাম" hint="ব্রাউজারের ট্যাব, ফুটার ও সার্চ রেজাল্টে দেখাবে।">
            <div class="grid sm:grid-cols-2 gap-4">
              <Field label="সাইটের নাম"><input v-model="cfg.branding.site_name" :class="input" /></Field>
              <Field label="ট্যাগলাইন"><input v-model="cfg.branding.tagline" :class="input" /></Field>
            </div>
          </Card>
          <Card title="লোগো" hint="স্বচ্ছ ব্যাকগ্রাউন্ডের PNG বা SVG সবচেয়ে ভালো দেখায়।">
            <div class="grid md:grid-cols-2 gap-5">
              <div class="space-y-2">
                <p class="text-[13px] font-medium text-[#171C1A]">মূল লোগো <span class="text-[#9AA39F] font-normal">(হেডার, হালকা ব্যাকগ্রাউন্ড)</span></p>
                <div class="h-24 rounded-lg bg-[#F7F5F0] border border-[#ECEFED] flex items-center justify-center p-4">
                  <img v-if="cfg.branding.logo" :src="cfg.branding.logo" class="max-h-full max-w-full object-contain" alt="" />
                  <span v-else class="font-serif text-xl">{{ cfg.branding.site_name }}</span>
                </div>
                <MediaPicker v-model="cfg.branding.logo" />
              </div>
              <div class="space-y-2">
                <p class="text-[13px] font-medium text-[#171C1A]">সাদা লোগো <span class="text-[#9AA39F] font-normal">(ফুটার, গাঢ় ব্যাকগ্রাউন্ড)</span></p>
                <div class="h-24 rounded-lg bg-[#111111] flex items-center justify-center p-4">
                  <img v-if="cfg.branding.logo_light || cfg.branding.logo" :src="cfg.branding.logo_light || cfg.branding.logo" class="max-h-full max-w-full object-contain" alt="" />
                </div>
                <MediaPicker v-model="cfg.branding.logo_light" />
              </div>
            </div>
            <div class="pt-4 mt-4 border-t border-[#F0F2F1] grid sm:grid-cols-[1fr_auto] gap-4 items-end">
              <Field label="ফেভিকন (ব্রাউজার ট্যাবের ছোট আইকন)"><MediaPicker v-model="cfg.branding.favicon" /></Field>
              <div class="w-10 h-10 rounded-lg border border-[#ECEFED] bg-white flex items-center justify-center p-1.5"><img v-if="cfg.branding.favicon" :src="cfg.branding.favicon" class="max-w-full max-h-full" alt="" /></div>
            </div>
          </Card>
        </template>

        <!-- ============ ANNOUNCEMENT ============ -->
        <template v-else-if="tab === 'announcement'">
          <Card title="ঘোষণা বার" hint="সাইটের একদম উপরের সরু বার — অফার বা ফ্রি ডেলিভারির খবর দিন।">
            <template #action><Toggle v-model="cfg.announcement.enabled" label="চালু" /></template>
            <div class="rounded-lg overflow-hidden border border-[#ECEFED] mb-4">
              <div class="h-9 px-4 flex items-center justify-center gap-3 text-xs font-medium" :style="{ background: cfg.announcement.bg_color, color: cfg.announcement.text_color, opacity: cfg.announcement.enabled ? 1 : 0.4 }">
                <span class="truncate">{{ cfg.announcement.text || 'ঘোষণার লেখা এখানে দেখাবে' }}</span>
                <span v-if="cfg.announcement.link_text" class="underline underline-offset-4 font-semibold shrink-0">{{ cfg.announcement.link_text }}</span>
              </div>
              <div class="h-10 bg-[#F7F5F0] flex items-center px-4 text-[11px] text-[#9AA39F]">প্রিভিউ — হেডার এর ঠিক উপরে</div>
            </div>
            <div class="grid gap-4">
              <Field label="লেখা" :count="`${(cfg.announcement.text || '').length}/300`"><input v-model="cfg.announcement.text" maxlength="300" :class="input" /></Field>
              <div class="grid sm:grid-cols-2 gap-4">
                <Field label="বাটনের লেখা (ঐচ্ছিক)"><input v-model="cfg.announcement.link_text" :class="input" /></Field>
                <Field label="বাটনের লিংক"><input v-model="cfg.announcement.link_url" list="site-link-suggestions" :class="input" /></Field>
              </div>
              <Field label="ফ্রি ডেলিভারির সীমা (৳)" hint="কার্টে এর চেয়ে বেশি টাকার পণ্য থাকলে ‘ফ্রি ডেলিভারি’ দেখাবে; ০ দিলে বারটি লুকানো থাকবে।">
                <input v-model.number="cfg.shop.free_shipping_threshold" type="number" min="0" step="50" :class="input" />
              </Field>
              <div class="grid sm:grid-cols-2 gap-4">
                <Field label="ব্যাকগ্রাউন্ড রং"><ColorInput v-model="cfg.announcement.bg_color" /></Field>
                <Field label="লেখার রং"><ColorInput v-model="cfg.announcement.text_color" /></Field>
              </div>
            </div>
          </Card>
        </template>

        <!-- ============ HEADER / MENU ============ -->
        <template v-else-if="tab === 'header'">
          <Card title="হেডার অপশন">
            <div class="grid sm:grid-cols-2 gap-3">
              <Toggle v-model="cfg.header.sticky" label="স্ক্রল করলেও হেডার উপরে থাকবে" />
              <Toggle v-model="cfg.header.show_search" label="সার্চ আইকন দেখান" />
              <Toggle v-model="cfg.header.show_account" label="অ্যাকাউন্ট আইকন দেখান" />
              <Toggle v-model="cfg.header.show_wishlist" label="উইশলিস্ট আইকন দেখান" />
            </div>
          </Card>

          <Card title="মেনু" hint="উপর-নিচ তীর দিয়ে ক্রম বদলান। কোনো আইটেমে সাব-মেনু চালু করলে হোভার করলে বড় মেগা মেনু খুলবে।">
            <TransitionGroup name="list" tag="div" class="space-y-3">
              <div v-for="(item, i) in cfg.header.menu" :key="item._k || (item._k = uid())" class="rounded-xl border border-[#ECEFED] bg-[#FAFBFA]">
                <div class="flex items-center gap-2 p-3">
                  <div class="flex flex-col">
                    <button type="button" :disabled="i === 0" @click="moveIn(cfg.header.menu, i, -1)" class="p-0.5 text-[#9AA39F] hover:text-[#171C1A] disabled:opacity-30"><ChevronUp class="w-4 h-4" /></button>
                    <button type="button" :disabled="i === cfg.header.menu.length - 1" @click="moveIn(cfg.header.menu, i, 1)" class="p-0.5 text-[#9AA39F] hover:text-[#171C1A] disabled:opacity-30"><ChevronDown class="w-4 h-4" /></button>
                  </div>
                  <input v-model="item.label" placeholder="মেনুর নাম" :class="[input, 'w-1/3! shrink-0 font-medium']" />
                  <input v-model="item.url" list="site-link-suggestions" placeholder="/collections বা /category/panjabi" :class="[input, 'w-auto! flex-1 min-w-0 font-mono text-[13px]']" />
                  <button
                    type="button"
                    @click="toggleMega(item)"
                    :class="['h-9 px-3 shrink-0 rounded-lg text-[12.5px] border transition-colors', item.mega?.length ? 'bg-emerald-50 border-emerald-200 text-[#14382A] font-semibold' : 'border-[#E7EBE8] text-[#4B5450] hover:border-[#16A36A]']"
                  >
                    {{ item.mega?.length ? 'সাব-মেনু চালু' : '+ সাব-মেনু' }}
                  </button>
                  <button type="button" @click="cfg.header.menu.splice(i, 1)" class="p-2 rounded-lg text-[#9AA39F] hover:text-rose-600 hover:bg-rose-50" title="মুছুন"><Trash2 class="w-4 h-4" /></button>
                </div>

                <!-- Mega menu columns -->
                <div v-if="item.mega?.length" class="px-3 pb-3">
                  <div class="grid md:grid-cols-2 gap-3">
                    <div v-for="(col, ci) in item.mega" :key="col._k || (col._k = uid())" class="rounded-lg bg-white border border-[#ECEFED] p-3 space-y-2.5">
                      <div class="flex items-center gap-2">
                        <input v-model="col.title" placeholder="কলামের শিরোনাম" :class="[input, 'font-semibold']" />
                        <button type="button" @click="item.mega.splice(ci, 1)" class="p-2 rounded-lg text-[#9AA39F] hover:text-rose-600 hover:bg-rose-50" title="কলাম মুছুন"><X class="w-4 h-4" /></button>
                      </div>
                      <LinkListEditor v-model="col.links" />
                    </div>
                  </div>
                  <button v-if="item.mega.length < 6" type="button" @click="item.mega.push({ title: '', links: [], _k: uid() })" class="mt-3 inline-flex items-center gap-1.5 h-8 px-3 rounded-lg border border-dashed border-[#CFD6D3] text-[13px] text-[#4B5450] hover:border-[#16A36A] hover:text-[#16A36A]">
                    <Plus class="w-3.5 h-3.5" /> কলাম যোগ করুন
                  </button>
                </div>
              </div>
            </TransitionGroup>
            <button v-if="cfg.header.menu.length < 12" type="button" @click="cfg.header.menu.push({ label: '', url: '', _k: uid() })" class="mt-3 inline-flex items-center gap-1.5 h-9 px-3.5 rounded-lg bg-[#14382A] text-white text-[13px] font-medium hover:bg-[#16A36A]">
              <Plus class="w-4 h-4" /> মেনু আইটেম যোগ করুন
            </button>
          </Card>

          <Card title="মেগা মেনুর পণ্যের ছবি" hint="সাব-মেনুর ডান পাশে কিছু পণ্যের ছবি, নাম ও দাম দেখাবে — ক্লিক করলে সরাসরি পণ্যের পেজ।">
            <div class="grid sm:grid-cols-3 gap-4">
              <Field label="শিরোনাম"><input v-model="cfg.header.mega_products.title" :class="input" placeholder="নতুন এসেছে" /></Field>
              <Field label="কোন পণ্য দেখাবে">
                <select v-model="cfg.header.mega_products.source" :class="input">
                  <option value="new">নতুন পণ্য</option>
                  <option value="bestseller">জনপ্রিয় পণ্য</option>
                  <option value="featured">নির্বাচিত (Featured) পণ্য</option>
                  <option value="none">ছবি দেখাবে না</option>
                </select>
              </Field>
              <Field label="কয়টি পণ্য">
                <select v-model.number="cfg.header.mega_products.count" :class="input" :disabled="cfg.header.mega_products.source === 'none'">
                  <option :value="2">২টি</option><option :value="3">৩টি</option><option :value="4">৪টি</option>
                </select>
              </Field>
            </div>
          </Card>
        </template>

        <!-- ============ FOOTER ============ -->
        <template v-else-if="tab === 'footer'">
          <Card title="ফুটারের লেখা">
            <div class="grid gap-4">
              <Field label="ছোট বিবরণ (লোগোর নিচে)"><textarea v-model="cfg.footer.description" rows="3" :class="input"></textarea></Field>
              <Field label="কপিরাইট লেখা" hint="{year} লিখলে বর্তমান বছর বসে যাবে।"><input v-model="cfg.footer.copyright" :class="input" /></Field>
            </div>
          </Card>
          <Card title="লিংকের কলাম" hint="সর্বোচ্চ ৫টি কলাম।">
            <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-3">
              <div v-for="(col, ci) in cfg.footer.columns" :key="col._k || (col._k = uid())" class="rounded-lg border border-[#ECEFED] bg-[#FAFBFA] p-3 space-y-2.5">
                <div class="flex items-center gap-2">
                  <input v-model="col.title" placeholder="কলামের শিরোনাম" :class="[input, 'font-semibold']" />
                  <button type="button" :disabled="ci === 0" @click="moveIn(cfg.footer.columns, ci, -1)" class="p-1.5 text-[#9AA39F] hover:text-[#171C1A] disabled:opacity-30" title="বামে"><ChevronLeft class="w-4 h-4" /></button>
                  <button type="button" @click="cfg.footer.columns.splice(ci, 1)" class="p-1.5 rounded-lg text-[#9AA39F] hover:text-rose-600 hover:bg-rose-50" title="কলাম মুছুন"><X class="w-4 h-4" /></button>
                </div>
                <LinkListEditor v-model="col.links" />
              </div>
            </div>
            <button v-if="cfg.footer.columns.length < 5" type="button" @click="cfg.footer.columns.push({ title: '', links: [], _k: uid() })" class="mt-3 inline-flex items-center gap-1.5 h-9 px-3.5 rounded-lg bg-[#14382A] text-white text-[13px] font-medium hover:bg-[#16A36A]">
              <Plus class="w-4 h-4" /> কলাম যোগ করুন
            </button>
          </Card>
          <Card title="পেমেন্ট মেথড" hint="ফুটারের নিচে ছোট ব্যাজ হিসেবে দেখাবে। Enter চাপলে যোগ হবে।">
            <div class="flex flex-wrap items-center gap-2">
              <span v-for="(m, mi) in cfg.footer.payment_methods" :key="m + mi" class="inline-flex items-center gap-1 h-8 pl-3 pr-1.5 rounded-full bg-[#F2F4F3] text-[13px]">
                {{ m }}
                <button type="button" @click="cfg.footer.payment_methods.splice(mi, 1)" class="p-0.5 rounded-full hover:bg-white"><X class="w-3.5 h-3.5" /></button>
              </span>
              <input v-model="newPayment" @keydown.enter.prevent="addPayment" placeholder="যেমন: রকেট" class="h-8 w-36 px-3 rounded-full border border-dashed border-[#CFD6D3] text-[13px] focus:outline-none focus:border-[#16A36A]" />
            </div>
          </Card>
        </template>

        <!-- ============ CONTACT & SOCIAL ============ -->
        <template v-else-if="tab === 'contact'">
          <Card title="যোগাযোগের তথ্য" hint="কনট্যাক্ট পেজ, ফুটার ও মোবাইল মেনুতে দেখাবে।">
            <div class="grid sm:grid-cols-2 gap-4">
              <Field label="ফোন নম্বর"><input v-model="cfg.contact.phone" :class="input" placeholder="+880 1XXX-XXXXXX" /></Field>
              <Field label="হোয়াটসঅ্যাপ নম্বর" hint="দিলে কনট্যাক্ট পেজে হোয়াটসঅ্যাপ বাটন আসবে।"><input v-model="cfg.contact.whatsapp" :class="input" placeholder="+8801XXXXXXXXX" /></Field>
              <Field label="ইমেইল"><input v-model="cfg.contact.email" type="email" :class="input" /></Field>
              <div></div>
              <Field label="ঠিকানা"><textarea v-model="cfg.contact.address" rows="3" :class="input"></textarea></Field>
              <Field label="সেবার সময়"><textarea v-model="cfg.contact.hours" rows="3" :class="input"></textarea></Field>
            </div>
            <div class="mt-4">
              <Field label="Google Maps লিংক (ঐচ্ছিক)" hint="Google Maps → Share → Embed a map → কোডের src=&quot;…&quot; এর ভেতরের লিংকটি দিন।">
                <input v-model="cfg.contact.map_url" :class="[input, 'font-mono text-[12.5px]']" placeholder="https://www.google.com/maps/embed?pb=…" />
              </Field>
            </div>
          </Card>
          <Card title="সোশ্যাল মিডিয়া" hint="লিংক দিলে ফুটার ও কনট্যাক্ট পেজে আইকন দেখাবে, খালি রাখলে লুকানো থাকবে।">
            <div class="grid sm:grid-cols-2 gap-3">
              <label v-for="s in cfg.social" :key="s.platform" class="flex items-center gap-2.5">
                <span class="w-9 h-9 rounded-lg bg-[#F2F4F3] flex items-center justify-center shrink-0"><SocialIcon :platform="s.platform" class="w-4 h-4" /></span>
                <input v-model="s.url" :placeholder="`${PLATFORM_LABEL[s.platform]} লিংক (https://…)`" :class="[input, 'text-[13px]']" />
              </label>
            </div>
          </Card>
        </template>

        <!-- ============ PAGE CONTENT ============ -->
        <template v-else-if="tab === 'pages'">
          <div class="flex gap-1 p-1 bg-white rounded-lg border border-[#ECEFED] w-fit">
            <button v-for="p in PAGE_TABS" :key="p.key" type="button" @click="pageTab = p.key"
                    :class="['h-8 px-3.5 rounded-md text-[13px] transition-colors', pageTab === p.key ? 'bg-[#14382A] text-white font-medium' : 'text-[#4B5450] hover:bg-[#F2F4F3]']">
              {{ p.label }}
            </button>
            <a :href="PAGE_TABS.find((p) => p.key === pageTab).url" target="_blank" rel="noopener" class="h-8 px-2.5 inline-flex items-center text-[#69736E] hover:text-[#171C1A]" title="পেজটি দেখুন"><ExternalLink class="w-4 h-4" /></a>
          </div>

          <template v-if="pageTab === 'about'">
            <Card title="শুরুর অংশ" hint="পেজের একদম উপরে — বড় শিরোনাম, ছোট বিবরণ ও একটি বড় ছবি।">
              <div class="grid md:grid-cols-2 gap-5">
                <div class="space-y-4">
                  <Field label="ছোট উপরের লেখা"><input v-model="cfg.pages.about.kicker" :class="input" /></Field>
                  <Field label="শিরোনাম"><input v-model="cfg.pages.about.title" :class="input" /></Field>
                  <Field label="বিবরণ"><textarea v-model="cfg.pages.about.intro" rows="4" :class="input"></textarea></Field>
                </div>
                <Field label="বড় ছবি" hint="খালি রাখলে জনপ্রিয় পণ্যের ছবি দেখাবে।"><MediaPicker v-model="cfg.pages.about.hero_image" /></Field>
              </div>
            </Card>
            <Card title="আমাদের গল্প" hint="একটি ফাঁকা লাইন দিয়ে আলাদা করলে নতুন অনুচ্ছেদ হবে।">
              <div class="grid md:grid-cols-2 gap-5">
                <div class="space-y-4">
                  <Field label="শিরোনাম"><input v-model="cfg.pages.about.story_title" :class="input" /></Field>
                  <Field label="লেখা"><textarea v-model="cfg.pages.about.story_text" rows="7" :class="input"></textarea></Field>
                </div>
                <Field label="ছবি" hint="খালি রাখলে পণ্যের ছবি দেখাবে।"><MediaPicker v-model="cfg.pages.about.story_image" /></Field>
              </div>
            </Card>
            <Card title="সংখ্যা" hint="যেমন: ১০০% / প্রিমিয়াম কটন। সর্বোচ্চ ৬টি।">
              <RepeatListEditor v-model="cfg.pages.about.stats" :fields="[{ key: 'value', label: 'সংখ্যা (যেমন ৬৪)' }, { key: 'label', label: 'লেখা (যেমন জেলায় ডেলিভারি)' }]" :max="6" inline add-label="সংখ্যা যোগ করুন" />
            </Card>
            <Card title="আমাদের বৈশিষ্ট্য">
              <Field label="শিরোনাম"><input v-model="cfg.pages.about.values_title" :class="input" /></Field>
              <div class="mt-4">
                <RepeatListEditor v-model="cfg.pages.about.values" :fields="[{ key: 'title', label: 'শিরোনাম' }, { key: 'text', label: 'ছোট বিবরণ', type: 'textarea' }]" :max="8" add-label="বৈশিষ্ট্য যোগ করুন" />
              </div>
            </Card>
            <Card title="উক্তি ও শেষের বাটন">
              <div class="grid sm:grid-cols-[2fr_1fr] gap-4">
                <Field label="উক্তি (খালি রাখলে দেখাবে না)"><input v-model="cfg.pages.about.quote" :class="input" /></Field>
                <Field label="কার উক্তি"><input v-model="cfg.pages.about.quote_author" :class="input" /></Field>
              </div>
              <div class="grid sm:grid-cols-2 gap-4 mt-4">
                <Field label="শেষের অংশের শিরোনাম"><input v-model="cfg.pages.about.cta_title" :class="input" /></Field>
                <Field label="বিবরণ"><input v-model="cfg.pages.about.cta_text" :class="input" /></Field>
                <Field label="বাটনের লেখা"><input v-model="cfg.pages.about.cta_button" :class="input" /></Field>
                <Field label="বাটনের লিংক"><input v-model="cfg.pages.about.cta_url" list="site-link-suggestions" :class="input" /></Field>
              </div>
            </Card>
          </template>

          <Card v-else-if="pageTab === 'blog'" title="ব্লগ পেজের উপরের অংশ" hint="লেখাগুলো যোগ/বদল করুন অ্যাডমিন → ব্লগ / জার্নাল থেকে।">
            <div class="grid gap-4">
              <Field label="ছোট উপরের লেখা"><input v-model="cfg.pages.blog.kicker" :class="input" /></Field>
              <Field label="শিরোনাম"><input v-model="cfg.pages.blog.title" :class="input" /></Field>
              <Field label="বিবরণ"><textarea v-model="cfg.pages.blog.intro" rows="3" :class="input"></textarea></Field>
            </div>
          </Card>

          <Card v-else title="সচরাচর জিজ্ঞাসা (FAQ)" hint="যোগাযোগ পেজের নিচে প্রশ্ন-উত্তর আকারে দেখাবে। ফোন, ঠিকানা ইত্যাদি “যোগাযোগ ও সোশ্যাল” ট্যাবে।">
            <Field label="শিরোনাম"><input v-model="cfg.pages.contact.faq_title" :class="input" /></Field>
            <div class="mt-4">
              <RepeatListEditor v-model="cfg.pages.contact.faq" :fields="[{ key: 'q', label: 'প্রশ্ন' }, { key: 'a', label: 'উত্তর', type: 'textarea' }]" :max="20" add-label="প্রশ্ন যোগ করুন" />
            </div>
          </Card>
        </template>

        <!-- ============ SEO ============ -->
        <template v-else-if="tab === 'seo'">
          <Card title="সার্চ ইঞ্জিন (Google)" hint="হোমপেজের টাইটেল ও বিবরণ — Google সার্চ ও ফেসবুকে শেয়ার করলে এটাই দেখাবে।">
            <div class="grid gap-4">
              <Field label="টাইটেল" :count="`${(cfg.seo.title || '').length}/60`"><input v-model="cfg.seo.title" maxlength="120" :class="input" /></Field>
              <Field label="বিবরণ" :count="`${(cfg.seo.description || '').length}/160`"><textarea v-model="cfg.seo.description" rows="3" maxlength="300" :class="input"></textarea></Field>
            </div>
            <div class="mt-5 p-4 rounded-lg border border-[#ECEFED] bg-white max-w-xl">
              <p class="text-[11px] text-[#69736E] mb-1">Google প্রিভিউ</p>
              <p class="text-[12px] text-[#202124]">{{ origin }}</p>
              <p class="text-[18px] leading-snug text-[#1a0dab] truncate">{{ cfg.seo.title || cfg.branding.site_name }}</p>
              <p class="text-[13px] text-[#4d5156] line-clamp-2">{{ cfg.seo.description }}</p>
            </div>
          </Card>
        </template>
      </div>
    </div>

    <!-- URL suggestions shared by every link field -->
    <datalist id="site-link-suggestions">
      <option v-for="s in suggestions" :key="s.url" :value="s.url">{{ s.label }}</option>
    </datalist>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount, h } from 'vue';
import { onBeforeRouteLeave } from 'vue-router';
import {
  Save, ExternalLink, Trash2, Plus, X, ChevronUp, ChevronDown, ChevronLeft,
  BadgeCheck, Megaphone, PanelTop, PanelBottom, Contact, Search, FileText,
} from 'lucide-vue-next';
import api from '../../api';
import { useToastStore } from '../../stores/toast';
import { useSiteStore } from '../../stores/site';
import MediaPicker from '../../components/admin/MediaPicker.vue';
import LinkListEditor from '../../components/admin/LinkListEditor.vue';
import RepeatListEditor from '../../components/admin/RepeatListEditor.vue';
import SocialIcon from '../../components/common/SocialIcon.vue';

const toast = useToastStore();
const siteStore = useSiteStore();

const tabs = [
  { key: 'branding', label: 'লোগো ও নাম', icon: BadgeCheck },
  { key: 'announcement', label: 'ঘোষণা বার', icon: Megaphone },
  { key: 'header', label: 'হেডার ও মেনু', icon: PanelTop },
  { key: 'footer', label: 'ফুটার', icon: PanelBottom },
  { key: 'contact', label: 'যোগাযোগ ও সোশ্যাল', icon: Contact },
  { key: 'pages', label: 'পেজ কনটেন্ট', icon: FileText },
  { key: 'seo', label: 'SEO', icon: Search },
];
const tab = ref('branding');
const PAGE_TABS = [
  { key: 'about', label: 'আমাদের সম্পর্কে', url: '/about' },
  { key: 'blog', label: 'ব্লগ', url: '/blog' },
  { key: 'contact', label: 'যোগাযোগ (FAQ)', url: '/contact' },
];
const pageTab = ref('about');

const PLATFORMS = ['facebook', 'instagram', 'tiktok', 'youtube', 'whatsapp', 'x', 'linkedin', 'pinterest'];
const PLATFORM_LABEL = { facebook: 'Facebook', instagram: 'Instagram', tiktok: 'TikTok', youtube: 'YouTube', whatsapp: 'WhatsApp', x: 'X (Twitter)', linkedin: 'LinkedIn', pinterest: 'Pinterest' };

const input = 'w-full h-9 px-3 rounded-lg border border-[#E7EBE8] bg-white text-sm text-[#171C1A] placeholder-[#B4BCB8] focus:outline-none focus:border-[#16A36A] focus:ring-2 focus:ring-[#16A36A]/15 [&:is(textarea)]:h-auto [&:is(textarea)]:py-2';
const origin = window.location.host;

// ---- small presentational helpers --------------------------------------
const Card = (props, { slots }) => h('section', { class: 'bg-white rounded-xl border border-[#ECEFED] p-5' }, [
  h('div', { class: 'flex items-start justify-between gap-3 mb-4' }, [
    h('div', [
      h('h3', { class: 'text-[15px] font-semibold text-[#171C1A]' }, props.title),
      props.hint ? h('p', { class: 'text-[12.5px] text-[#69736E] mt-0.5' }, props.hint) : null,
    ]),
    slots.action?.(),
  ]),
  slots.default?.(),
]);
Card.props = ['title', 'hint'];

const Field = (props, { slots }) => h('label', { class: 'block' }, [
  h('span', { class: 'flex items-center justify-between text-[13px] font-medium text-[#171C1A] mb-1.5' }, [
    props.label,
    props.count ? h('span', { class: 'text-[11px] font-normal text-[#9AA39F] tabular-nums' }, props.count) : null,
  ]),
  slots.default?.(),
  props.hint ? h('span', { class: 'block mt-1 text-[11.5px] text-[#9AA39F]' }, props.hint) : null,
]);
Field.props = ['label', 'hint', 'count'];

const Toggle = (props, { emit }) => h('button', {
  type: 'button',
  class: 'flex items-center gap-3 text-[13.5px] text-[#171C1A] text-left',
  role: 'switch',
  'aria-checked': String(!!props.modelValue),
  onClick: () => emit('update:modelValue', !props.modelValue),
}, [
  h('span', { class: ['relative w-10 h-6 rounded-full transition-colors shrink-0', props.modelValue ? 'bg-[#16A36A]' : 'bg-[#D5DBD8]'] }, [
    h('span', { class: ['absolute top-0.5 left-0.5 w-5 h-5 rounded-full bg-white shadow transition-transform', props.modelValue ? 'translate-x-4' : ''] }),
  ]),
  props.label,
]);
Toggle.props = ['modelValue', 'label'];
Toggle.emits = ['update:modelValue'];

const ColorInput = (props, { emit }) => h('div', { class: 'flex items-center gap-2' }, [
  h('input', { type: 'color', value: props.modelValue || '#111111', class: 'w-10 h-9 rounded-lg border border-[#E7EBE8] cursor-pointer p-0.5 bg-white', onInput: (e) => emit('update:modelValue', e.target.value.toUpperCase()) }),
  h('input', { value: props.modelValue, class: input + ' font-mono uppercase', maxlength: 7, onInput: (e) => emit('update:modelValue', e.target.value) }),
]);
ColorInput.props = ['modelValue'];
ColorInput.emits = ['update:modelValue'];

// ---- state ---------------------------------------------------------------
const cfg = ref(null);
const saved = ref('');
const saving = ref(false);
const errorList = ref([]);
const newPayment = ref('');

let n = 0;
const uid = () => `k${Date.now().toString(36)}${n++}`;
const strip = (obj) => JSON.parse(JSON.stringify(obj, (k, v) => (k === '_k' ? undefined : v)));
const snapshot = () => JSON.stringify(strip(cfg.value));
const dirty = computed(() => !!cfg.value && snapshot() !== saved.value);

const normalize = (data) => {
  const c = structuredClone(data);
  c.header.menu = (c.header.menu || []).map((m) => ({ ...m, mega: m.mega || [] }));
  c.header.mega_promo = c.header.mega_promo || {};
  c.header.mega_products = { title: 'নতুন এসেছে', source: 'new', count: 3, ...(c.header.mega_products || {}) };
  c.shop = { free_shipping_threshold: 2500, ...(c.shop || {}) };
  c.pages = c.pages || {};
  c.pages.about = { stats: [], values: [], ...(c.pages.about || {}) };
  c.pages.blog = { ...(c.pages.blog || {}) };
  c.pages.contact = { faq: [], ...(c.pages.contact || {}) };
  c.footer.columns = c.footer.columns || [];
  c.footer.payment_methods = c.footer.payment_methods || [];
  // One row per platform, keeping saved URLs.
  const urls = Object.fromEntries((c.social || []).map((s) => [s.platform, s.url]));
  c.social = PLATFORMS.map((p) => ({ platform: p, url: urls[p] || '' }));
  return c;
};

const load = async () => {
  const res = await api.get('/admin/appearance');
  cfg.value = normalize(res.data.data);
  saved.value = snapshot();
};

const moveIn = (list, i, d) => {
  const [item] = list.splice(i, 1);
  list.splice(i + d, 0, item);
};
const toggleMega = (item) => {
  if (item.mega?.length) {
    if (!window.confirm(`"${item.label || 'এই আইটেম'}" এর সাব-মেনু মুছে ফেলবেন?`)) return;
    item.mega = [];
  } else {
    item.mega = [{ title: 'কলাম ১', links: [], _k: uid() }];
  }
};
const addPayment = () => {
  const v = newPayment.value.trim();
  if (v && !cfg.value.footer.payment_methods.includes(v)) cfg.value.footer.payment_methods.push(v);
  newPayment.value = '';
};

const payload = () => {
  const c = strip(cfg.value);
  // Rows the admin added but left completely empty are simply dropped.
  const links = (list) => (list || []).filter((l) => (l.label || '').trim() || (l.url || '').trim());
  c.header.menu = c.header.menu
    .filter((m) => (m.label || '').trim() || (m.url || '').trim())
    .map((m) => {
      const mega = (m.mega || []).map((col) => ({ ...col, links: links(col.links) })).filter((col) => col.title || col.links.length);
      return mega.length ? { label: m.label, url: m.url, mega } : { label: m.label, url: m.url };
    });
  c.footer.columns = c.footer.columns.map((col) => ({ ...col, links: links(col.links) })).filter((col) => col.title || col.links.length);
  // Drop list rows that were added but left empty.
  const filled = (list, keys) => (list || []).filter((r) => keys.some((k) => String(r[k] || '').trim()));
  c.pages.about.stats = filled(c.pages.about.stats, ['value', 'label']);
  c.pages.about.values = filled(c.pages.about.values, ['title', 'text']);
  c.pages.contact.faq = filled(c.pages.contact.faq, ['q', 'a']);
  return c;
};

const save = async () => {
  saving.value = true;
  errorList.value = [];
  try {
    const res = await api.put('/admin/appearance', payload());
    cfg.value = normalize(res.data.data);
    saved.value = snapshot();
    window.__SITE__ = res.data.data;   // page titles
    siteStore.config = res.data.data;  // storefront chrome inside this SPA
    toast.success('সাইট আপডেট হয়েছে — ওয়েবসাইটে এখনই দেখা যাবে');
  } catch (err) {
    const errors = err.response?.data?.errors;
    errorList.value = errors ? [...new Set(Object.values(errors).flat())] : ['সংরক্ষণ করা যায়নি, আবার চেষ্টা করুন।'];
    toast.error('কিছু তথ্য ঠিক করতে হবে');
    window.scrollTo({ top: 0, behavior: 'smooth' });
  } finally {
    saving.value = false;
  }
};

const resetAll = async () => {
  if (!window.confirm('সব পরিবর্তন মুছে ডিফল্ট লোগো, মেনু, ফুটার ও যোগাযোগের তথ্যে ফিরে যাবেন?')) return;
  const res = await api.post('/admin/appearance/reset');
  cfg.value = normalize(res.data.data);
  saved.value = snapshot();
  siteStore.config = res.data.data;
  window.__SITE__ = res.data.data;
  toast.success('ডিফল্টে ফেরানো হয়েছে');
};

// ---- link suggestions (pages, categories, collections) ------------------
const suggestions = ref([
  { url: '/', label: 'হোমপেজ' }, { url: '/collections', label: 'সব পোশাক' },
  { url: '/collection/new-arrivals', label: 'নতুন কালেকশন' }, { url: '/#lookbook', label: 'লুকবুক' },
  { url: '/blog', label: 'ব্লগ' }, { url: '/about', label: 'আমাদের সম্পর্কে' },
  { url: '/contact', label: 'যোগাযোগ' }, { url: '/track-order', label: 'অর্ডার ট্র্যাক' },
  { url: '/account', label: 'অ্যাকাউন্ট' }, { url: '/wishlist', label: 'উইশলিস্ট' },
  { url: '/pages/shipping-returns', label: 'শিপিং ও রিটার্ন' }, { url: '/pages/size-guide', label: 'সাইজ গাইড' },
]);
const loadSuggestions = async () => {
  try {
    const [cats, cols] = await Promise.all([api.get('/public/categories'), api.get('/public/collections')]);
    const flat = (list) => list.flatMap((c) => [c, ...(c.children || [])]);
    suggestions.value.push(
      ...flat(cats.data.data || []).map((c) => ({ url: `/category/${c.slug}`, label: `ক্যাটাগরি: ${c.name}` })),
      ...(cols.data.data || []).map((c) => ({ url: `/collection/${c.slug}`, label: `কালেকশন: ${c.name}` })),
    );
  } catch { /* suggestions are optional */ }
};

// ---- unsaved-changes guard -----------------------------------------------
const beforeUnload = (e) => { if (dirty.value) { e.preventDefault(); e.returnValue = ''; } };
onBeforeRouteLeave(() => (dirty.value ? window.confirm('পরিবর্তন সংরক্ষণ করা হয়নি। তবুও পেজ ছাড়বেন?') : true));
const onKey = (e) => {
  if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') { e.preventDefault(); if (dirty.value) save(); }
};

onMounted(() => {
  load();
  loadSuggestions();
  window.addEventListener('beforeunload', beforeUnload);
  window.addEventListener('keydown', onKey);
});
onBeforeUnmount(() => {
  window.removeEventListener('beforeunload', beforeUnload);
  window.removeEventListener('keydown', onKey);
});
</script>

<style scoped>
.list-move, .list-enter-active, .list-leave-active { transition: all 0.25s ease; }
.list-enter-from, .list-leave-to { opacity: 0; transform: translateY(-6px); }
</style>
