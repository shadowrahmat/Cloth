<template>
  <div class="space-y-5 max-w-5xl">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3">
      <div>
        <h2 class="text-xl font-bold tracking-tight text-admin-text">হোমপেজ বিল্ডার</h2>
        <p class="text-xs text-admin-muted mt-0.5">হোমপেজের প্রতিটা সেকশনের লেখা, ছবি আর পণ্য এখান থেকে বদলান। তীর দিয়ে ক্রম বদলান, সুইচ দিয়ে লুকান।</p>
      </div>
      <a href="/" target="_blank" rel="noopener" class="adm-btn shrink-0"><ExternalLink :size="15" /> হোমপেজ দেখুন</a>
    </div>

    <router-link to="/admin/appearance" class="flex items-center justify-between gap-3 px-4 py-3 rounded-xl bg-[#EEF6F1] border border-[#CFE3D7] text-[13px] text-admin-sidebar hover:bg-[#E3F1E9]">
      <span><strong>লোগো, হেডার মেনু, ঘোষণা বার ও ফুটার</strong> বদলাতে <strong>সাইট কাস্টমাইজ</strong> পেজে যান।</span>
      <span class="shrink-0 font-semibold">সাইট কাস্টমাইজ →</span>
    </router-link>

    <!-- Section list -->
    <div class="adm-card overflow-hidden">
      <div v-if="loading" class="p-4 space-y-2"><div v-for="n in 8" :key="n" class="h-16 rounded-lg bg-admin-bg animate-pulse"></div></div>
      <ol v-else>
        <li v-for="(section, index) in sections" :key="section.id" :class="['flex items-center gap-4 px-4 sm:px-5 py-3.5 border-b border-admin-border last:border-b-0', !section.is_active && 'bg-admin-bg/60']">
          <span class="w-7 text-center text-[12px] font-semibold text-admin-muted tabular-nums">{{ bnNumber(index + 1) }}</span>
          <span :class="['w-10 h-10 rounded-lg flex items-center justify-center shrink-0', section.is_active ? 'bg-[#EEF6F1] text-admin-sidebar' : 'bg-white text-admin-muted border border-admin-border']">
            <component :is="meta(section).icon" :size="19" :stroke-width="1.8" />
          </span>
          <button type="button" class="min-w-0 flex-1 text-left" @click="openEdit(section)">
            <span :class="['block text-[14px] font-semibold truncate', section.is_active ? 'text-admin-text' : 'text-admin-muted']">{{ meta(section).name }}</span>
            <span class="block text-[12px] text-admin-muted truncate">{{ meta(section).desc }}</span>
          </button>
          <div class="flex items-center gap-1 shrink-0">
            <button v-can="'update_cms'" type="button" class="hb-icon" :disabled="index === 0" @click="move(index, -1)" title="ওপরে নিন" aria-label="ওপরে নিন"><ArrowUp :size="16" /></button>
            <button v-can="'update_cms'" type="button" class="hb-icon" :disabled="index === sections.length - 1" @click="move(index, 1)" title="নিচে নিন" aria-label="নিচে নিন"><ArrowDown :size="16" /></button>
            <label class="ml-2 mr-1 flex items-center gap-2 cursor-pointer" :title="section.is_active ? 'সাইটে দেখাচ্ছে — বন্ধ করতে ক্লিক করুন' : 'লুকানো — চালু করতে ক্লিক করুন'">
              <input v-can="'update_cms'" type="checkbox" class="hb-switch" :checked="section.is_active" @change="toggleActive(section)" :aria-label="meta(section).name + ' সাইটে দেখান'" />
              <span class="hidden sm:inline text-[12px] w-9 text-admin-muted">{{ section.is_active ? 'চালু' : 'বন্ধ' }}</span>
            </label>
            <button v-can="'update_cms'" type="button" class="adm-btn" @click="openEdit(section)"><Pencil :size="14" /> এডিট</button>
          </div>
        </li>
      </ol>
    </div>

    <!-- ============================================================ editor -->
    <BaseModal :is-open="editOpen" :title="editing ? `এডিট: ${meta(editing).name}` : ''" max-width="4xl" @close="closeEdit">
      <form v-if="editing" class="space-y-6 text-[13px] text-admin-text" @submit.prevent="save">

        <!-- ---------------------------------------------- hero -->
        <template v-if="editing.type === 'hero'">
          <div class="flex flex-wrap items-end justify-between gap-3">
            <p class="text-[12.5px] text-admin-muted max-w-xl">প্রতিটা স্লাইডে একটা বড় ছবি, শিরোনাম, ছোট লেখা আর বোতাম থাকে। ছবি ১৯২০×১০৮০ (১৬:৯) মাপের হলে সবচেয়ে ভালো দেখায়। মোবাইলের জন্য আলাদা খাড়া ছবি দিতে পারেন।</p>
            <label class="flex items-center gap-2 text-[12.5px]">প্রতি স্লাইড
              <select v-model.number="form.content.seconds" class="adm-input !w-24 !py-1.5">
                <option v-for="s in [4, 5, 6, 7, 8, 10]" :key="s" :value="s">{{ bnNumber(s) }} সেকেন্ড</option>
              </select>
            </label>
          </div>
          <div class="space-y-3">
            <div v-for="(slide, i) in form.content.slides" :key="slide._k" class="rounded-xl border border-admin-border overflow-hidden">
              <div class="flex items-center gap-3 px-4 py-2.5 bg-admin-bg border-b border-admin-border">
                <span class="w-14 h-8 rounded bg-white overflow-hidden shrink-0 border border-admin-border"><img v-if="slide.image" :src="slide.image" alt="" class="w-full h-full object-cover" /></span>
                <span class="flex-1 min-w-0 font-semibold truncate">{{ bnNumber(i + 1) }}. {{ slide.tab || slide.title || 'নতুন স্লাইড' }}</span>
                <button type="button" class="hb-icon" :disabled="i === 0" @click="moveIn(form.content.slides, i, -1)" aria-label="ওপরে"><ArrowUp :size="15" /></button>
                <button type="button" class="hb-icon" :disabled="i === form.content.slides.length - 1" @click="moveIn(form.content.slides, i, 1)" aria-label="নিচে"><ArrowDown :size="15" /></button>
                <button type="button" class="hb-icon hover:!text-rose-600" :disabled="form.content.slides.length === 1" @click="form.content.slides.splice(i, 1)" title="স্লাইড মুছুন" aria-label="স্লাইড মুছুন"><Trash2 :size="15" /></button>
              </div>
              <div class="p-4 grid sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2 grid sm:grid-cols-2 gap-4">
                  <div><p class="adm-label">বড় ছবি (কম্পিউটার) <span class="text-rose-600">*</span></p><MediaPicker v-model="slide.image" /></div>
                  <div><p class="adm-label">মোবাইলের ছবি <span class="font-normal text-admin-muted">(ঐচ্ছিক)</span></p><MediaPicker v-model="slide.mobile_image" /></div>
                </div>
                <div><label class="adm-label">শিরোনাম</label><input v-model="slide.title" class="adm-input" maxlength="80" placeholder="যেমন: Own the Rain" /></div>
                <div><label class="adm-label">নিচের ট্যাবের নাম</label><input v-model="slide.tab" class="adm-input" maxlength="40" placeholder="যেমন: আউটডোর জ্যাকেট" /></div>
                <div class="sm:col-span-2"><label class="adm-label">ছোট লেখা</label><textarea v-model="slide.subtitle" rows="2" class="adm-input" maxlength="250"></textarea></div>
                <div><label class="adm-label">বোতামের লেখা <span class="font-normal text-admin-muted">(খালি = বোতাম নেই)</span></label><input v-model="slide.cta" class="adm-input" maxlength="40" /></div>
                <div><label class="adm-label">বোতামের লিংক</label><input v-model="slide.link" class="adm-input font-mono text-[12.5px]" placeholder="/collection/panjabi" /></div>
              </div>
            </div>
          </div>
          <button v-if="form.content.slides.length < 6" type="button" class="adm-btn" @click="addSlide"><Plus :size="15" /> নতুন স্লাইড যোগ করুন</button>
          <p v-if="slideError" class="adm-error">{{ slideError }}</p>
        </template>

        <!-- ---------------------------------------------- category grid -->
        <template v-else-if="editing.type === 'category_grid'">
          <div class="grid sm:grid-cols-2 gap-4">
            <div><label class="adm-label">শিরোনাম</label><input v-model="form.content.heading" class="adm-input" placeholder="কিউরেটেড ক্যাটাগরি সমূহ" /></div>
            <div><label class="adm-label">ছোট লেখা</label><input v-model="form.content.description" class="adm-input" /></div>
          </div>
          <CategoryPicker v-model="form.content.categories" :all="allCategories" :max="8" empty-text="কিছু বাছাই না করলে প্রথম ৪টি মূল ক্যাটাগরি দেখাবে (ক্যাটাগরি পেজের ক্রম অনুযায়ী)। ৪টি বা ৮টি বাছলে সবচেয়ে সুন্দর দেখায়।" />
          <p class="adm-help">ছবি আসে প্রতিটা ক্যাটাগরির নিজের ছবি থেকে — বদলাতে <router-link to="/admin/categories" class="text-admin-sidebar font-semibold hover:underline">ক্যাটাগরি</router-link> পেজে যান।</p>
        </template>

        <!-- ---------------------------------------------- 3D carousel -->
        <template v-else-if="editing.type === 'favourites_carousel'">
          <div class="grid sm:grid-cols-12 gap-4">
            <div class="sm:col-span-4"><label class="adm-label">ছোট উপরের লেখা</label><input v-model="form.content.eyebrow" maxlength="40" class="adm-input" placeholder="সদ্য এসেছে" /></div>
            <div class="sm:col-span-5"><label class="adm-label">শিরোনাম</label><input v-model="form.content.heading" maxlength="40" class="adm-input" placeholder="এই সপ্তাহের পছন্দ" /></div>
            <div class="sm:col-span-3"><label class="adm-label">সোনালি রঙের শব্দ</label><input v-model="form.content.highlight" maxlength="20" class="adm-input" placeholder="পছন্দ" /></div>
            <div class="sm:col-span-8">
              <label class="adm-label">কোন পণ্য দেখাবে</label>
              <select v-model="form.content.source" class="adm-input">
                <option value="new">সবচেয়ে নতুন পণ্য</option>
                <option value="bestseller">বেস্টসেলার পণ্য</option>
                <option value="sale">সেলে থাকা পণ্য</option>
                <optgroup v-if="allCategories.length" label="একটা ক্যাটাগরি থেকে">
                  <option v-for="c in allCategories" :key="c.slug" :value="'category:' + c.slug">{{ c.is_child ? '— ' : '' }}{{ c.name }}</option>
                </optgroup>
              </select>
            </div>
            <div class="sm:col-span-4">
              <label class="adm-label">সর্বোচ্চ কয়টি পণ্য</label>
              <select v-model.number="form.content.limit" class="adm-input"><option v-for="n in 8" :key="n" :value="n + 4">{{ bnNumber(n + 4) }}টি</option></select>
            </div>
          </div>
          <p class="adm-help">শুধু ছবি আছে এমন প্রকাশিত পণ্য দেখাবে। সেলে থাকলে “সেল”, নতুন চিহ্ন দেওয়া থাকলে “নতুন” ব্যাজ নিজে থেকে বসবে।</p>
        </template>

        <!-- ---------------------------------------------- new arrivals + runway -->
        <template v-else-if="editing.type === 'new_arrivals'">
          <div class="grid sm:grid-cols-3 gap-4">
            <div class="sm:col-span-2"><label class="adm-label">শিরোনাম</label><input v-model="form.content.heading" class="adm-input" placeholder="নতুন কালেকশন" /></div>
            <div>
              <label class="adm-label">কয়টি পণ্য</label>
              <select v-model.number="form.content.limit" class="adm-input"><option v-for="n in [4, 8, 12]" :key="n" :value="n">{{ bnNumber(n) }}টি</option></select>
            </div>
          </div>
          <p class="adm-help -mt-3">সবচেয়ে নতুন প্রকাশিত পণ্যগুলো নিজে থেকে আসে।</p>

          <div class="rounded-xl border border-admin-border">
            <label class="flex items-center justify-between gap-3 px-4 py-3 cursor-pointer">
              <span><span class="block font-semibold">রানওয়ে লুকবুক</span><span class="block text-[12px] text-admin-muted">নতুন পণ্যগুলো কালো পটভূমিতে, স্ক্রল করলে পাশে সরে যায়</span></span>
              <input type="checkbox" class="hb-switch" :checked="form.content.show_runway !== false" @change="form.content.show_runway = $event.target.checked" />
            </label>
            <div v-if="form.content.show_runway !== false" class="px-4 pb-4 pt-1 grid sm:grid-cols-2 gap-4 border-t border-admin-border">
              <p class="sm:col-span-2 text-[12px] text-admin-muted pt-3">খালি রাখলে ধূসর লেখাটাই দেখাবে।</p>
              <div><label class="adm-label">ছোট উপরের লেখা</label><input v-model="form.content.runway.kicker" class="adm-input" :placeholder="RUNWAY_DEFAULTS.kicker" /></div>
              <div><label class="adm-label">শিরোনাম</label><input v-model="form.content.runway.heading" class="adm-input" :placeholder="RUNWAY_DEFAULTS.heading" /></div>
              <div><label class="adm-label">শুরুর কার্ড: ছোট লেখা</label><input v-model="form.content.runway.intro_kicker" class="adm-input" :placeholder="RUNWAY_DEFAULTS.intro_kicker" /></div>
              <div><label class="adm-label">শুরুর কার্ড: শিরোনাম</label><input v-model="form.content.runway.intro_title" class="adm-input" :placeholder="RUNWAY_DEFAULTS.intro_title" /></div>
              <div class="sm:col-span-2"><label class="adm-label">শুরুর কার্ড: লেখা</label><textarea v-model="form.content.runway.intro_text" rows="2" class="adm-input" :placeholder="RUNWAY_DEFAULTS.intro_text"></textarea></div>
              <div><label class="adm-label">শেষের কার্ড: ছোট লেখা</label><input v-model="form.content.runway.outro_kicker" class="adm-input" :placeholder="RUNWAY_DEFAULTS.outro_kicker" /></div>
              <div><label class="adm-label">শেষের কার্ড: শিরোনাম</label><input v-model="form.content.runway.outro_title" class="adm-input" :placeholder="RUNWAY_DEFAULTS.outro_title" /></div>
              <div class="sm:col-span-2"><label class="adm-label">শেষের কার্ড: লেখা</label><textarea v-model="form.content.runway.outro_text" rows="2" class="adm-input" :placeholder="RUNWAY_DEFAULTS.outro_text"></textarea></div>
              <div><label class="adm-label">শেষের কার্ড: বোতাম</label><input v-model="form.content.runway.outro_button" class="adm-input" :placeholder="RUNWAY_DEFAULTS.outro_button" /></div>
              <div><label class="adm-label">বোতামের লিংক</label><input v-model="form.content.runway.outro_link" class="adm-input font-mono text-[12.5px]" :placeholder="RUNWAY_DEFAULTS.outro_link" /></div>
              <div><label class="adm-label">সিজনের নাম</label><input v-model="form.content.runway.season" class="adm-input" :placeholder="RUNWAY_DEFAULTS.season" /></div>
              <div></div>
              <div><label class="adm-label">নিচের লেখা ১</label><input v-model="form.content.runway.feature_1" class="adm-input" :placeholder="RUNWAY_DEFAULTS.feature_1" /></div>
              <div><label class="adm-label">নিচের লেখা ২</label><input v-model="form.content.runway.feature_2" class="adm-input" :placeholder="RUNWAY_DEFAULTS.feature_2" /></div>
            </div>
          </div>
        </template>

        <!-- ---------------------------------------------- editorial -->
        <template v-else-if="editing.type === 'editorial'">
          <div class="grid sm:grid-cols-[220px_1fr] gap-5">
            <div><p class="adm-label">ছবি</p><MediaPicker v-model="form.content.image" /></div>
            <div class="space-y-4">
              <div><label class="adm-label">শিরোনাম</label><input v-model="form.content.heading" class="adm-input" /></div>
              <div><label class="adm-label">উদ্ধৃতি (বাঁকা অক্ষরে দেখায়)</label><textarea v-model="form.content.quote" rows="2" class="adm-input"></textarea></div>
            </div>
          </div>
          <div><label class="adm-label">লেখা</label><textarea v-model="form.content.description" rows="4" class="adm-input"></textarea></div>
          <div class="grid sm:grid-cols-2 gap-4">
            <div><label class="adm-label">বোতামের লেখা</label><input v-model="form.content.cta_text" class="adm-input" placeholder="যেমন: আমাদের গল্প পড়ুন" /></div>
            <div><label class="adm-label">বোতামের লিংক</label><input v-model="form.content.cta_link" class="adm-input font-mono text-[12.5px]" placeholder="/pages/about-us" /></div>
          </div>
        </template>

        <!-- ---------------------------------------------- best sellers -->
        <template v-else-if="editing.type === 'best_sellers'">
          <div class="grid sm:grid-cols-3 gap-4">
            <div class="sm:col-span-2"><label class="adm-label">শিরোনাম</label><input v-model="form.content.heading" class="adm-input" placeholder="সর্বাধিক বিক্রিত পোশাক" /></div>
            <div><label class="adm-label">কয়টি পণ্য</label><select v-model.number="form.content.limit" class="adm-input"><option v-for="n in [4, 8, 12]" :key="n" :value="n">{{ bnNumber(n) }}টি</option></select></div>
          </div>
          <div><label class="adm-label">ছোট লেখা</label><input v-model="form.content.description" class="adm-input" /></div>
          <p class="adm-help">যেসব পণ্যে <router-link to="/admin/products" class="text-admin-sidebar font-semibold hover:underline">“বেস্টসেলার” লেবেল</router-link> দেওয়া, সেগুলো আগে আসে।</p>
        </template>

        <!-- ---------------------------------------------- category showcase -->
        <template v-else-if="editing.type === 'category_showcase'">
          <div class="grid sm:grid-cols-12 gap-4">
            <div class="sm:col-span-4"><label class="adm-label">ছোট উপরের লেখা</label><input v-model="form.content.kicker" class="adm-input" placeholder="ক্যাটাগরি অনুযায়ী দেখুন" /></div>
            <div class="sm:col-span-8"><label class="adm-label">শিরোনাম</label><input v-model="form.content.heading" class="adm-input" placeholder="আপনার স্টাইল বেছে নিন" /></div>
            <div class="sm:col-span-8"><label class="adm-label">ছোট লেখা</label><input v-model="form.content.description" class="adm-input" /></div>
            <div class="sm:col-span-4">
              <label class="adm-label">প্রতি ক্যাটাগরিতে ছবি</label>
              <select v-model.number="form.content.products_per_category" class="adm-input"><option v-for="n in 4" :key="n" :value="n">{{ bnNumber(n) }}টি পণ্য</option></select>
            </div>
          </div>
          <CategoryPicker v-model="form.content.categories" :all="allCategories" :max="8" empty-text="কিছু বাছাই না করলে সবচেয়ে বেশি পণ্য থাকা ৫টি ক্যাটাগরি নিজে থেকে দেখাবে।" />
        </template>

        <!-- ---------------------------------------------- brand story -->
        <template v-else-if="editing.type === 'brand_story'">
          <div><label class="adm-label">শিরোনাম</label><input v-model="form.content.heading" class="adm-input" placeholder="পরিমিতিবোধ ও নিখুঁত কারিগরি শিল্প" /></div>
          <div><label class="adm-label">লেখা</label><textarea v-model="form.content.description" rows="4" class="adm-input"></textarea></div>
          <div>
            <p class="adm-label">৩টি সংখ্যা</p>
            <div class="grid sm:grid-cols-3 gap-3">
              <div v-for="(d, n) in STAT_DEFAULTS" :key="n" class="rounded-xl border border-admin-border p-3 space-y-2">
                <input v-model="form.content[`stat_${n + 1}_number`]" class="adm-input font-serif text-[16px]" :placeholder="d[0]" maxlength="12" :aria-label="`সংখ্যা ${n + 1}`" />
                <input v-model="form.content[`stat_${n + 1}_label`]" class="adm-input" :placeholder="d[1]" maxlength="40" :aria-label="`সংখ্যা ${n + 1}-এর লেখা`" />
              </div>
            </div>
          </div>
        </template>

        <!-- ---------------------------------------------- testimonials -->
        <template v-else-if="editing.type === 'testimonials'">
          <div class="grid sm:grid-cols-2 gap-4">
            <div><label class="adm-label">শিরোনাম</label><input v-model="form.content.heading" class="adm-input" placeholder="গ্রাহকদের অনুভূতি ও অভিজ্ঞতা" /></div>
            <div><label class="adm-label">ছোট লেখা</label><input v-model="form.content.description" class="adm-input" /></div>
          </div>
          <router-link to="/admin/resources/testimonials" class="hb-link"><Quote :size="18" /> <span class="flex-1"><strong>মতামতগুলো যোগ ও এডিট করুন</strong><span class="block text-[12px] text-admin-muted">নাম, ছবি, রেটিং ও লেখা — “হোমপেজে দেখান” টিক দেওয়াগুলো এখানে আসে</span></span> →</router-link>
        </template>

        <!-- ---------------------------------------------- instagram -->
        <template v-else-if="editing.type === 'instagram_gallery'">
          <div class="grid sm:grid-cols-2 gap-4">
            <div><label class="adm-label">শিরোনাম</label><input v-model="form.content.heading" class="adm-input" placeholder="গ্রাহকদের পছন্দের DRAWER লুক" /></div>
            <div><label class="adm-label">ইনস্টাগ্রাম হ্যান্ডেল</label><input v-model="form.content.handle" class="adm-input" placeholder="@drawerclothing" /></div>
          </div>
          <router-link to="/admin/resources/social-gallery-items" class="hb-link"><Instagram :size="18" /> <span class="flex-1"><strong>ছবিগুলো যোগ ও এডিট করুন</strong><span class="block text-[12px] text-admin-muted">প্রথম ৬টি চালু ছবি দেখায়, ক্রম অনুযায়ী</span></span> →</router-link>
        </template>

        <!-- ---------------------------------------------- newsletter -->
        <template v-else-if="editing.type === 'newsletter'">
          <div class="grid sm:grid-cols-3 gap-4">
            <div><label class="adm-label">ছোট উপরের লেখা</label><input v-model="form.content.kicker" class="adm-input" placeholder="নিউজলেটার" /></div>
            <div class="sm:col-span-2"><label class="adm-label">শিরোনাম</label><input v-model="form.content.heading" class="adm-input" placeholder="ফ্যাশন ও অফারের সাথে যুক্ত থাকুন" /></div>
          </div>
          <div><label class="adm-label">লেখা</label><textarea v-model="form.content.description" rows="2" class="adm-input" placeholder="নতুন কালেকশন রিলিজ, বিশেষ ডিসকাউন্ট এবং সিজনাল ক্যাম্পেইনের আপডেট সবার আগে পেতে যুক্ত হোন।"></textarea></div>
          <div>
            <div class="flex items-center justify-between gap-3 mb-1.5">
              <p class="adm-label !mb-0">সুবিধার তালিকা (টিক চিহ্নসহ দেখায়)</p>
              <label class="flex items-center gap-2 text-[12px] cursor-pointer"><input type="checkbox" class="hb-switch" :checked="form.content.show_benefits !== false" @change="form.content.show_benefits = $event.target.checked" /> দেখান</label>
            </div>
            <div v-if="form.content.show_benefits !== false" class="grid sm:grid-cols-3 gap-3">
              <input v-for="(d, n) in NEWSLETTER_BENEFITS" :key="n" v-model="form.content.benefits[n]" class="adm-input" :placeholder="d" maxlength="60" :aria-label="`সুবিধা ${n + 1}`" />
            </div>
          </div>
          <div class="grid sm:grid-cols-2 gap-4">
            <div><label class="adm-label">ইমেইলের ঘরে লেখা</label><input v-model="form.content.placeholder" class="adm-input" placeholder="আপনার ইমেইল লিখুন..." /></div>
            <div><label class="adm-label">বোতামের লেখা</label><input v-model="form.content.button_text" class="adm-input" placeholder="সাবস্ক্রাইব" /></div>
          </div>
          <div><label class="adm-label">নিচের ছোট লেখা</label><input v-model="form.content.disclaimer" class="adm-input" placeholder="আপনার ইমেইল কারো সাথে শেয়ার করা হয় না। শুধু দরকারি খবর আর অফার পাঠাই।" /></div>
          <router-link to="/admin/resources/newsletter-subscribers" class="hb-link"><Send :size="18" /> <span class="flex-1"><strong>সাবস্ক্রাইবারদের তালিকা দেখুন</strong><span class="block text-[12px] text-admin-muted">কারা ইমেইল দিয়েছেন — CSV ডাউনলোড করে অফার পাঠান</span></span> →</router-link>
        </template>

        <!-- ---------------------------------------------- anything else -->
        <template v-else>
          <div><label class="adm-label">শিরোনাম</label><input v-model="form.content.heading" class="adm-input" /></div>
          <div><label class="adm-label">লেখা</label><textarea v-model="form.content.description" rows="3" class="adm-input"></textarea></div>
        </template>

        <!-- common -->
        <div class="flex flex-wrap items-center justify-between gap-4 pt-4 border-t border-admin-border">
          <label class="flex items-center gap-2.5 cursor-pointer">
            <input v-model="form.is_active" type="checkbox" class="hb-switch" />
            <span>সাইটে দেখান</span>
          </label>
          <div class="flex gap-2">
            <button type="button" class="adm-btn" @click="closeEdit">বাতিল</button>
            <button v-can="'update_cms'" type="submit" class="adm-btn adm-btn-primary" :disabled="saving"><Check :size="15" /> {{ saving ? 'সেভ হচ্ছে…' : 'সেভ করুন' }}</button>
          </div>
        </div>
      </form>
    </BaseModal>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, defineComponent, h } from 'vue';
import {
  ExternalLink, ArrowUp, ArrowDown, Pencil, Plus, Trash2, Check, Quote, Instagram, X,
  Image as ImageIcon, LayoutGrid, GalleryHorizontal, Sparkles, Newspaper, TrendingUp, Layers, BookOpen, Mail, Square, Send,
} from 'lucide-vue-next';
import api from '../../api';
import BaseModal from '../../components/common/BaseModal.vue';
import MediaPicker from '../../components/admin/MediaPicker.vue';
import { useToastStore } from '../../stores/toast';
import { bnNumber } from '../../admin-nav';
import { HERO_DEFAULT_SLIDES, HERO_DEFAULT_SECONDS, RUNWAY_DEFAULTS } from '../../utils/homeDefaults';

const toast = useToastStore();

const TYPES = {
  hero: { name: 'হিরো ব্যানার', desc: 'পেজের একদম ওপরের বড় ছবির স্লাইড', icon: ImageIcon },
  category_grid: { name: 'বিশেষ ক্যাটাগরি', desc: 'ছবিসহ ক্যাটাগরির কার্ড', icon: LayoutGrid },
  favourites_carousel: { name: 'এই সপ্তাহের পছন্দ (৩ডি)', desc: 'ঘুরতে থাকা পণ্যের ক্যারোসেল', icon: GalleryHorizontal },
  new_arrivals: { name: 'নতুন আগমন ও রানওয়ে', desc: 'সবচেয়ে নতুন পণ্য আর রানওয়ে লুকবুক', icon: Sparkles },
  editorial: { name: 'ব্র্যান্ড এডিটোরিয়াল', desc: 'বড় ছবি, উদ্ধৃতি আর লেখা', icon: Newspaper },
  best_sellers: { name: 'সেরা বিক্রিত', desc: 'বেস্টসেলার পণ্যের তালিকা', icon: TrendingUp },
  category_showcase: { name: 'ক্যাটাগরি শোকেস', desc: 'স্ক্রল করলে ক্যাটাগরি বদলায়', icon: Layers },
  brand_story: { name: 'ব্র্যান্ড স্টোরি', desc: 'আপনার গল্প আর ৩টি সংখ্যা', icon: BookOpen },
  testimonials: { name: 'গ্রাহকের মতামত', desc: 'গ্রাহকদের রিভিউ স্লাইডার', icon: Quote },
  instagram_gallery: { name: 'ইনস্টাগ্রাম গ্যালারি', desc: 'ইনস্টাগ্রামের ছবি', icon: Instagram },
  newsletter: { name: 'নিউজলেটার', desc: 'ইমেইল সাবস্ক্রিপশন বক্স', icon: Mail },
};
const meta = (s) => TYPES[s.type] || { name: s.title || s.type, desc: s.type, icon: Square };
const NEWSLETTER_BENEFITS = ['নতুন কালেকশন সবার আগে দেখুন', 'শুধু সাবস্ক্রাইবারদের জন্য বিশেষ ছাড়', 'উৎসব ও সিজনাল অফারের খবর'];
const STAT_DEFAULTS = [['১০০%', 'প্রিমিয়াম সুতি ও ফেব্রিক'], ['২৪/৭', 'সার্বক্ষণিক গ্রাহক সেবা'], ['৭ দিন', 'সহজ এক্সচেঞ্জ সুবিধা']];

// ------------------------------------------------------------------ list
const sections = ref([]);
const loading = ref(true);
const CHROME = ['announcement', 'header', 'footer'];   // site-wide, managed in সাইট কাস্টমাইজ
const pageSections = (list) => list.filter((s) => !CHROME.includes(s.type));

const fetchSections = async () => {
  try {
    const res = await api.get('/admin/homepage-builder');
    sections.value = pageSections(res.data.data || []);
  } catch {
    toast.error('সেকশন লোড হয়নি');
  } finally {
    loading.value = false;
  }
};

const move = async (index, dir) => {
  const list = [...sections.value];
  const [x] = list.splice(index, 1);
  list.splice(index + dir, 0, x);
  sections.value = list;
  try {
    const res = await api.post('/admin/homepage-builder/reorder', { order: list.map((s, i) => ({ id: s.id, display_order: i + 3 })) });
    sections.value = pageSections(res.data.data);
  } catch {
    toast.error('ক্রম বদলানো যায়নি');
    fetchSections();
  }
};

const toggleActive = async (section) => {
  const next = !section.is_active;
  section.is_active = next;
  try {
    await api.put(`/admin/homepage-builder/${section.id}`, { is_active: next });
    toast.success(next ? `${meta(section).name} সাইটে দেখাচ্ছে` : `${meta(section).name} লুকানো হয়েছে`);
  } catch {
    section.is_active = !next;
    toast.error('বদলানো যায়নি');
  }
};

// ------------------------------------------------------------------ editor
const editOpen = ref(false);
const editing = ref(null);
const saving = ref(false);
const slideError = ref('');
const form = reactive({ title: '', is_active: true, content: {} });
let snapshot = '';
let slideKey = 0;

const openEdit = (section) => {
  editing.value = section;
  slideError.value = '';
  form.title = section.title;
  form.is_active = Boolean(section.is_active);
  const c = JSON.parse(JSON.stringify(section.content || {}));

  if (section.type === 'hero') {
    const saved = Array.isArray(c.slides) && c.slides.length ? c.slides : HERO_DEFAULT_SLIDES;
    c.slides = saved.map((s) => ({ tab: '', title: '', subtitle: '', cta: '', link: '', image: '', mobile_image: '', ...s, _k: ++slideKey }));
    c.seconds ||= HERO_DEFAULT_SECONDS;
  }
  if (['category_grid', 'category_showcase'].includes(section.type) && !Array.isArray(c.categories)) c.categories = [];
  if (section.type === 'category_showcase') c.products_per_category ||= 3;
  if (section.type === 'favourites_carousel') { c.source ||= 'new'; c.limit ||= 8; }
  if (['new_arrivals', 'best_sellers'].includes(section.type)) c.limit = [4, 8, 12].includes(Number(c.limit)) ? Number(c.limit) : 8;
  if (section.type === 'new_arrivals') c.runway = { ...(c.runway || {}) };
  if (section.type === 'newsletter') c.benefits = [0, 1, 2].map((i) => (Array.isArray(c.benefits) ? c.benefits[i] : '') || '');

  form.content = c;
  snapshot = JSON.stringify(form);
  if (['category_grid', 'category_showcase', 'favourites_carousel'].includes(section.type)) loadCategories();
  editOpen.value = true;
};

const closeEdit = () => {
  if (JSON.stringify(form) !== snapshot && !confirm('সেভ না করা পরিবর্তন আছে। বাদ দেবেন?')) return;
  editOpen.value = false;
};

const addSlide = () => form.content.slides.push({ tab: '', title: '', subtitle: '', cta: '', link: '', image: '', mobile_image: '', _k: ++slideKey });
const moveIn = (list, i, d) => {
  const [x] = list.splice(i, 1);
  list.splice(i + d, 0, x);
};

const save = async () => {
  const content = JSON.parse(JSON.stringify(form.content));
  if (editing.value.type === 'hero') {
    if (content.slides.some((s) => !s.image)) {
      slideError.value = 'প্রতিটা স্লাইডে একটা বড় ছবি দিন, অথবা ছবি ছাড়া স্লাইডটা মুছে দিন।';
      return;
    }
    content.slides = content.slides.map(({ _k, ...s }) => s);
  }
  if (content.runway) {
    // keep only what the admin actually typed; empty = standard text
    content.runway = Object.fromEntries(Object.entries(content.runway).filter(([, v]) => String(v ?? '').trim() !== ''));
  }
  saving.value = true;
  try {
    await api.put(`/admin/homepage-builder/${editing.value.id}`, { title: form.title, is_active: form.is_active, content });
    toast.success(`${meta(editing.value).name} সেভ হয়েছে`);
    snapshot = JSON.stringify(form);
    editOpen.value = false;
    fetchSections();
  } catch (err) {
    toast.error(err.response?.data?.message || 'সেভ হয়নি');
  } finally {
    saving.value = false;
  }
};

// ------------------------------------------------------------------ categories
const allCategories = ref([]);
const loadCategories = async () => {
  if (allCategories.value.length) return;
  try {
    const res = await api.get('/public/categories');
    allCategories.value = (res.data.data || []).flatMap((p) => [
      { slug: p.slug, name: p.name, is_child: false },
      ...(p.children || []).map((c) => ({ slug: c.slug, name: c.name, is_child: true })),
    ]);
  } catch { /* list stays empty */ }
};

/** Ordered category chooser shared by the grid and the showcase. */
const CategoryPicker = defineComponent({
  props: { modelValue: { type: Array, default: () => [] }, all: { type: Array, default: () => [] }, max: { type: Number, default: 8 }, emptyText: String },
  emits: ['update:modelValue'],
  setup(props, { emit }) {
    const nameOf = (slug) => props.all.find((c) => c.slug === slug)?.name || slug;
    const set = (list) => emit('update:modelValue', list);
    const move = (i, d) => { const l = [...props.modelValue]; const [x] = l.splice(i, 1); l.splice(i + d, 0, x); set(l); };
    return () => h('div', { class: 'grid sm:grid-cols-2 gap-4' }, [
      h('div', [
        h('p', { class: 'adm-label' }, `দেখানো ক্যাটাগরি (ক্রম অনুযায়ী) — ${bnNumber(props.modelValue.length)}/${bnNumber(props.max)}`),
        props.modelValue.length
          ? h('ol', { class: 'rounded-xl border border-admin-border divide-y divide-admin-border' }, props.modelValue.map((slug, i) => h('li', { key: slug, class: 'flex items-center gap-2 px-3 py-2' }, [
            h('span', { class: 'w-5 text-admin-muted tabular-nums' }, bnNumber(i + 1) + '.'),
            h('span', { class: 'flex-1' }, nameOf(slug)),
            h('button', { type: 'button', class: 'hb-icon', disabled: i === 0, onClick: () => move(i, -1), 'aria-label': 'ওপরে' }, [h(ArrowUp, { size: 14 })]),
            h('button', { type: 'button', class: 'hb-icon', disabled: i === props.modelValue.length - 1, onClick: () => move(i, 1), 'aria-label': 'নিচে' }, [h(ArrowDown, { size: 14 })]),
            h('button', { type: 'button', class: 'hb-icon hover:!text-rose-600', onClick: () => set(props.modelValue.filter((s) => s !== slug)), 'aria-label': 'সরান' }, [h(X, { size: 14 })]),
          ])))
          : h('p', { class: 'p-3 rounded-xl border border-dashed border-admin-border text-[12.5px] text-admin-muted' }, props.emptyText),
      ]),
      h('div', [
        h('p', { class: 'adm-label' }, 'ক্যাটাগরি যোগ করুন'),
        h('div', { class: 'max-h-60 overflow-y-auto rounded-xl border border-admin-border divide-y divide-admin-border' },
          props.all.length
            ? props.all.filter((c) => !props.modelValue.includes(c.slug)).map((c) => h('button', {
              key: c.slug, type: 'button', disabled: props.modelValue.length >= props.max,
              class: ['w-full text-left px-3 py-2 hover:bg-admin-bg disabled:opacity-40', c.is_child && 'pl-7 text-admin-muted'],
              onClick: () => set([...props.modelValue, c.slug]),
            }, '+ ' + c.name))
            : [h('p', { class: 'p-3 text-[12.5px] text-admin-muted' }, 'ক্যাটাগরি লোড হচ্ছে…')]),
      ]),
    ]);
  },
});

onMounted(fetchSections);
</script>

<style scoped>
.hb-icon {
  width: 30px; height: 30px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px;
  color: var(--color-admin-muted); transition: background .15s, color .15s;
}
.hb-icon:hover:not(:disabled) { background: var(--color-admin-bg); color: var(--color-admin-text); }
.hb-icon:disabled { opacity: .3; cursor: not-allowed; }
.hb-switch {
  appearance: none; flex-shrink: 0; width: 34px; height: 20px; border-radius: 999px; background: #D5DCD8; position: relative; cursor: pointer; transition: background .15s;
}
.hb-switch::after {
  content: ''; position: absolute; top: 2px; left: 2px; width: 16px; height: 16px; border-radius: 999px; background: #fff;
  box-shadow: 0 1px 2px rgba(0, 0, 0, .2); transition: transform .15s;
}
.hb-switch:checked { background: var(--color-admin-accent); }
.hb-switch:checked::after { transform: translateX(14px); }
.hb-switch:focus-visible { outline: 2px solid var(--color-admin-accent); outline-offset: 2px; }
.hb-link {
  display: flex; align-items: center; gap: 12px; padding: 14px 16px; border-radius: 12px; background: #EEF6F1;
  border: 1px solid #CFE3D7; color: var(--color-admin-sidebar);
}
.hb-link:hover { background: #E3F1E9; }
</style>
