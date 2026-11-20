<template>
  <Teleport to="body">
    <div class="pe-root fixed inset-0 z-[60] bg-admin-bg overflow-y-auto" role="dialog" aria-modal="true" :aria-label="title">
      <!-- ============================================================ top bar -->
      <div class="sticky top-0 z-10 bg-white/95 backdrop-blur border-b border-admin-border">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center gap-3">
          <button type="button" class="adm-btn adm-btn-ghost -ml-2 px-2" @click="requestClose" title="পণ্যের তালিকায় ফিরুন (Esc)">
            <ArrowLeft :size="18" />
          </button>
          <div class="min-w-0 flex-1">
            <p class="text-[11px] text-admin-muted leading-none">{{ isEdit ? 'পণ্য এডিট' : 'নতুন পণ্য' }}</p>
            <h2 class="text-[15px] font-semibold text-admin-text truncate mt-1">{{ form.name || 'নাম ছাড়া পণ্য' }}</h2>
          </div>
          <span v-if="dirty" class="hidden sm:inline-flex items-center gap-1.5 text-[11.5px] text-amber-700">
            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> সেভ হয়নি
          </span>
          <a v-if="isEdit && savedSlug" :href="`/product/${savedSlug}`" target="_blank" rel="noopener" class="adm-btn hidden sm:inline-flex">
            <ExternalLink :size="14" /> সাইটে দেখুন
          </a>
          <button type="button" class="adm-btn adm-btn-primary" :disabled="saving || loading" @click="save">
            <Loader2 v-if="saving" :size="14" class="animate-spin" />
            <Check v-else :size="15" />
            {{ saving ? 'সেভ হচ্ছে…' : 'সেভ করুন' }}
          </button>
        </div>
      </div>

      <div v-if="loading" class="max-w-6xl mx-auto px-4 sm:px-6 py-6 grid lg:grid-cols-[minmax(0,1fr)_320px] gap-6">
        <div class="space-y-5"><div v-for="n in 3" :key="n" class="adm-card h-48 animate-pulse"></div></div>
        <div class="space-y-5"><div v-for="n in 2" :key="n" class="adm-card h-40 animate-pulse"></div></div>
      </div>

      <form v-else class="max-w-6xl mx-auto px-4 sm:px-6 py-6 grid lg:grid-cols-[minmax(0,1fr)_320px] gap-6 items-start" @submit.prevent="save" novalidate>
        <!-- ======================================================== main column -->
        <div class="space-y-5 min-w-0">
          <!-- Error summary -->
          <div v-if="errorList.length" class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">
            <p class="font-semibold flex items-center gap-2"><AlertCircle :size="16" /> সেভ করার আগে এগুলো ঠিক করুন:</p>
            <ul class="mt-2 list-disc pl-6 space-y-0.5 text-[13px]">
              <li v-for="e in errorList" :key="e">{{ e }}</li>
            </ul>
          </div>

          <!-- 1. Basics -->
          <section class="adm-card" id="pe-basic">
            <header class="adm-card-head"><div><h3 class="adm-card-title">মূল তথ্য</h3><p class="adm-card-sub">গ্রাহক প্রথমে যা দেখবে</p></div></header>
            <div class="adm-card-body space-y-4">
              <div>
                <label class="adm-label" for="pe-name">পণ্যের নাম <span class="text-rose-600">*</span></label>
                <input id="pe-name" ref="nameInput" v-model="form.name" class="adm-input" :class="err('name') && 'is-invalid'" maxlength="255" placeholder="যেমন: প্রিমিয়াম কটন পাঞ্জাবি" />
                <p v-if="err('name')" class="adm-error">{{ err('name') }}</p>
              </div>
              <div>
                <label class="adm-label" for="pe-short">ছোট বিবরণ</label>
                <input id="pe-short" v-model="form.short_description" class="adm-input" maxlength="500" placeholder="এক লাইনে পণ্যের মূল কথা — দামের নিচে দেখায়" />
                <p class="adm-help">{{ bnNumber((form.short_description || '').length) }}/৫০০</p>
              </div>
              <div>
                <label class="adm-label" for="pe-desc">বিস্তারিত বিবরণ</label>
                <textarea id="pe-desc" v-model="form.description" rows="6" class="adm-input" placeholder="কাপড়, ডিজাইন, কোথায় পরবেন — “বিবরণ” ট্যাবে দেখায়"></textarea>
              </div>
            </div>
          </section>

          <!-- 2. Images -->
          <section class="adm-card" id="pe-images">
            <header class="adm-card-head">
              <div><h3 class="adm-card-title">ছবি</h3><p class="adm-card-sub">প্রথম ছবিটাই মূল ছবি — তালিকা ও শেয়ারে এটাই দেখায়। তীর দিয়ে ক্রম বদলান।</p></div>
              <span class="text-xs text-admin-muted">{{ bnNumber(images.length) }}টি</span>
            </header>
            <div class="adm-card-body">
              <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-3">
                <div v-for="(src, i) in images" :key="src + i" class="pe-thumb group" :class="i === 0 && 'is-main'">
                  <img :src="src" alt="" class="w-full h-full object-cover" />
                  <span v-if="i === 0" class="absolute top-1.5 left-1.5 adm-chip bg-admin-sidebar text-white">মূল ছবি</span>
                  <div class="pe-thumb-actions">
                    <button type="button" :disabled="i === 0" @click="moveImage(i, -1)" title="আগে নিন"><ChevronLeft :size="14" /></button>
                    <button v-if="i !== 0" type="button" @click="makeMain(i)" title="মূল ছবি বানান"><Star :size="13" /></button>
                    <button type="button" :disabled="i === images.length - 1" @click="moveImage(i, 1)" title="পরে নিন"><ChevronRight :size="14" /></button>
                    <button type="button" class="hover:!bg-rose-600" @click="removeImage(i)" title="সরান"><X :size="14" /></button>
                  </div>
                </div>
                <div class="aspect-[3/4]">
                  <MediaPicker multiple :label="images.length ? 'আরও ছবি' : 'ছবি যোগ করুন'" @pick="addImages" />
                </div>
              </div>
              <p v-if="!images.length" class="adm-help mt-3">ছবি ছাড়া পণ্য সাইটে খালি দেখাবে। অন্তত একটা ছবি দিন — ৩:৪ মাপের (যেমন ১২০০×১৬০০) ছবি সবচেয়ে ভালো দেখায়।</p>
            </div>
          </section>

          <!-- 3. Pricing -->
          <section class="adm-card" id="pe-price">
            <header class="adm-card-head"><div><h3 class="adm-card-title">দাম</h3><p class="adm-card-sub">সব দাম টাকায় (৳)</p></div></header>
            <div class="adm-card-body">
              <div class="grid sm:grid-cols-3 gap-4">
                <div>
                  <label class="adm-label" for="pe-price-in">দাম <span class="text-rose-600">*</span></label>
                  <div class="pe-money"><span>৳</span><input id="pe-price-in" v-model.number="form.price" type="number" min="0" step="1" class="adm-input" :class="err('price') && 'is-invalid'" /></div>
                  <p v-if="err('price')" class="adm-error">{{ err('price') }}</p>
                </div>
                <div>
                  <label class="adm-label" for="pe-sale">সেল প্রাইস</label>
                  <div class="pe-money"><span>৳</span><input id="pe-sale" v-model.number="form.sale_price" type="number" min="0" step="1" class="adm-input" :class="err('sale_price') && 'is-invalid'" placeholder="খালি = সেল নেই" /></div>
                  <p v-if="err('sale_price')" class="adm-error">{{ err('sale_price') }}</p>
                  <p v-else-if="discount > 0" class="adm-help text-rose-600 font-semibold">{{ bnNumber(discount) }}% ছাড় দেখাবে</p>
                </div>
                <div>
                  <label class="adm-label" for="pe-cost">কেনা দাম <span class="font-normal text-admin-muted">(শুধু আপনি দেখবেন)</span></label>
                  <div class="pe-money"><span>৳</span><input id="pe-cost" v-model.number="form.cost_price" type="number" min="0" step="1" class="adm-input" placeholder="ঐচ্ছিক" /></div>
                  <p v-if="profit !== null" :class="['adm-help font-semibold', profit >= 0 ? 'text-emerald-700' : 'text-rose-600']">
                    প্রতি পিসে লাভ {{ money(profit) }} ({{ bnNumber(profitPct) }}%)
                  </p>
                </div>
              </div>
            </div>
          </section>

          <!-- 4. Sizes, colours, stock -->
          <section class="adm-card" id="pe-variants">
            <header class="adm-card-head">
              <div><h3 class="adm-card-title">সাইজ, রং ও স্টক</h3><p class="adm-card-sub">সাইজ আর রং দিয়ে “টেবিল বানান” চাপুন — প্রতিটা কম্বিনেশনের আলাদা স্টক রাখা যাবে।</p></div>
              <span class="text-xs text-admin-muted whitespace-nowrap">মোট স্টক: <strong class="text-admin-text">{{ bnNumber(totalStock) }}</strong></span>
            </header>
            <div class="adm-card-body space-y-5">
              <!-- Builder -->
              <div class="grid md:grid-cols-2 gap-4 p-4 rounded-xl bg-admin-bg border border-admin-border">
                <div>
                  <p class="adm-label">সাইজ</p>
                  <div class="flex flex-wrap gap-1.5 mb-2">
                    <span v-for="(s, i) in builder.sizes" :key="s" class="pe-tag">{{ s }}<button type="button" @click="builder.sizes.splice(i, 1)" :aria-label="s + ' সরান'"><X :size="12" /></button></span>
                    <span v-if="!builder.sizes.length" class="text-[11.5px] text-admin-muted">কোনো সাইজ নেই</span>
                  </div>
                  <input v-model="builder.sizeText" class="adm-input" placeholder="সাইজ লিখে Enter চাপুন (যেমন M বা 32)" @keydown.enter.prevent="addSizes" @blur="addSizes" />
                  <div class="flex flex-wrap gap-1.5 mt-2">
                    <button v-for="set in sizePresets" :key="set.label" type="button" class="pe-preset" @click="addSizeSet(set.sizes)">+ {{ set.label }}</button>
                  </div>
                </div>
                <div>
                  <p class="adm-label">রং <span class="font-normal text-admin-muted">(না থাকলে খালি রাখুন)</span></p>
                  <div class="flex flex-wrap gap-1.5 mb-2">
                    <span v-for="(c, i) in builder.colors" :key="c.name" class="pe-tag">
                      <span class="w-3 h-3 rounded-full border border-black/10" :style="{ background: c.code }"></span>{{ c.name }}
                      <button type="button" @click="builder.colors.splice(i, 1)" :aria-label="c.name + ' সরান'"><X :size="12" /></button>
                    </span>
                    <span v-if="!builder.colors.length" class="text-[11.5px] text-admin-muted">কোনো রং নেই</span>
                  </div>
                  <div class="flex gap-2">
                    <input v-model="builder.colorCode" type="color" class="w-10 h-[38px] rounded-[9px] border border-[#D5DCD8] p-1 bg-white cursor-pointer shrink-0" aria-label="রঙের কোড" />
                    <input v-model="builder.colorName" class="adm-input" placeholder="রঙের নাম (যেমন কালো)" @keydown.enter.prevent="addColor" />
                    <button type="button" class="adm-btn shrink-0" @click="addColor">যোগ</button>
                  </div>
                </div>
                <div class="md:col-span-2 flex flex-wrap items-center gap-3 pt-1">
                  <label class="text-xs text-admin-muted flex items-center gap-2">নতুন সারির স্টক
                    <input v-model.number="builder.stock" type="number" min="0" class="adm-input !w-20 !py-1.5" />
                  </label>
                  <button type="button" class="adm-btn adm-btn-primary" :disabled="!builder.sizes.length && !builder.colors.length" @click="buildRows">
                    <Grid3x3 :size="15" /> টেবিল বানান
                  </button>
                  <span class="text-[11.5px] text-admin-muted">আগের সারিগুলো থাকবে, শুধু নতুন কম্বিনেশন যোগ হবে।</span>
                </div>
              </div>

              <!-- Rows -->
              <div v-if="form.variants.length" class="space-y-2">
                <div class="flex flex-wrap items-center justify-between gap-2">
                  <p class="text-xs text-admin-muted">{{ bnNumber(form.variants.length) }}টি সারি · আলাদা দাম খালি রাখলে ওপরের দামই প্রযোজ্য</p>
                  <div class="flex items-center gap-2 text-xs">
                    <span class="text-admin-muted">সব সারির স্টক</span>
                    <input v-model.number="bulkStock" type="number" min="0" class="adm-input !w-20 !py-1.5" placeholder="০" />
                    <button type="button" class="adm-btn !py-1.5" :disabled="bulkStock === '' || bulkStock === null" @click="applyBulkStock">প্রয়োগ</button>
                  </div>
                </div>
                <div class="overflow-x-auto rounded-xl border border-admin-border">
                  <table class="w-full text-[13px] min-w-[640px]">
                    <thead class="bg-admin-bg text-[11.5px] text-admin-muted">
                      <tr>
                        <th class="text-left font-semibold px-3 py-2">সাইজ</th>
                        <th class="text-left font-semibold px-3 py-2">রং</th>
                        <th class="text-left font-semibold px-3 py-2 w-28">স্টক</th>
                        <th class="text-left font-semibold px-3 py-2 w-32">আলাদা দাম</th>
                        <th class="text-left font-semibold px-3 py-2">SKU</th>
                        <th class="w-10"></th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(v, i) in form.variants" :key="v._k" class="border-t border-admin-border">
                        <td class="px-3 py-2"><input v-model.trim="v.size" class="adm-input !py-1.5 w-20" placeholder="—" /></td>
                        <td class="px-3 py-2">
                          <div class="flex items-center gap-1.5">
                            <input v-model="v.color_code" type="color" class="w-8 h-8 rounded-md border border-[#D5DCD8] p-0.5 bg-white cursor-pointer shrink-0" :disabled="!v.color" :title="v.color ? 'রঙের কোড' : 'আগে রঙের নাম দিন'" />
                            <input v-model.trim="v.color" class="adm-input !py-1.5 w-28" placeholder="—" />
                          </div>
                        </td>
                        <td class="px-3 py-2">
                          <input v-model.number="v.stock" type="number" min="0" class="adm-input !py-1.5" :class="[err(`variants.${i}.stock`) && 'is-invalid', Number(v.stock) <= 0 && 'pe-zero']" />
                        </td>
                        <td class="px-3 py-2"><div class="pe-money pe-money-sm"><span>৳</span><input v-model.number="v.price" type="number" min="0" class="adm-input !py-1.5" :placeholder="String(form.price || '')" /></div></td>
                        <td class="px-3 py-2"><input v-model.trim="v.sku" class="adm-input !py-1.5 font-mono text-[12px]" placeholder="নিজে থেকে তৈরি হবে" /></td>
                        <td class="px-2 py-2 text-center">
                          <button type="button" class="p-1.5 rounded-md text-admin-muted hover:text-rose-600 hover:bg-rose-50" @click="form.variants.splice(i, 1)" :aria-label="`${v.size || ''} ${v.color || ''} সারি মুছুন`"><Trash2 :size="15" /></button>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
                <button type="button" class="adm-btn adm-btn-ghost text-admin-sidebar" @click="addRow"><Plus :size="15" /> একটা সারি যোগ করুন</button>
              </div>

              <!-- No variants -->
              <div v-else class="grid sm:grid-cols-[200px_1fr] gap-4 items-end">
                <div>
                  <label class="adm-label" for="pe-stock">স্টক (পিস)</label>
                  <input id="pe-stock" v-model.number="form.stock" type="number" min="0" class="adm-input" />
                </div>
                <p class="adm-help pb-2">সাইজ বা রং না থাকলে এই একটা স্টকই ব্যবহার হবে। সাইজ থাকলে ওপরে সাইজ দিয়ে টেবিল বানান — তাহলে গ্রাহক সাইজ বেছে কিনতে পারবে।</p>
              </div>
            </div>
          </section>

          <!-- 5. Product page text -->
          <section class="adm-card" id="pe-info">
            <header class="adm-card-head"><div><h3 class="adm-card-title">প্রোডাক্ট পেজের তথ্য</h3><p class="adm-card-sub">খালি রাখলে দোকানের সাধারণ লেখা দেখাবে</p></div></header>
            <div class="adm-card-body grid md:grid-cols-3 gap-4">
              <div>
                <label class="adm-label" for="pe-fabric">ফেব্রিক ও যত্ন</label>
                <textarea id="pe-fabric" v-model="form.fabric_care" rows="4" class="adm-input" placeholder="যেমন: ১০০% সুতি। ঠান্ডা পানিতে ধুয়ে ছায়ায় শুকান।"></textarea>
                <p class="adm-help">“সাইজ ও ফিট” ট্যাবে দেখায়</p>
              </div>
              <div>
                <label class="adm-label" for="pe-ship">ডেলিভারি তথ্য</label>
                <textarea id="pe-ship" v-model="form.shipping_info" rows="4" class="adm-input" placeholder="খালি রাখলে ডেলিভারি চার্জ পেজের তথ্য দেখাবে"></textarea>
                <p class="adm-help">শুধু এই পণ্যে আলাদা কিছু হলে লিখুন</p>
              </div>
              <div>
                <label class="adm-label" for="pe-ret">এক্সচেঞ্জ তথ্য</label>
                <textarea id="pe-ret" v-model="form.returns_info" rows="4" class="adm-input" placeholder="খালি রাখলে “৭ দিনের মধ্যে সহজ এক্সচেঞ্জ” দেখাবে"></textarea>
                <p class="adm-help">“ডেলিভারি ও এক্সচেঞ্জ” ট্যাবে দেখায়</p>
              </div>
            </div>
          </section>

          <!-- 6. SEO -->
          <section class="adm-card" id="pe-seo">
            <header class="adm-card-head cursor-pointer select-none" @click="seoOpen = !seoOpen">
              <div><h3 class="adm-card-title">গুগল ও শেয়ার (SEO)</h3><p class="adm-card-sub">ঐচ্ছিক — খালি রাখলে নাম ও ছোট বিবরণ ব্যবহার হবে</p></div>
              <ChevronDown :size="18" :class="['text-admin-muted transition-transform', seoOpen && 'rotate-180']" />
            </header>
            <div v-show="seoOpen" class="adm-card-body space-y-4">
              <div class="rounded-xl border border-admin-border p-4 bg-white">
                <p class="text-[12px] text-[#1a7f37] truncate">{{ siteHost }}/product/{{ form.slug || autoSlug || '…' }}</p>
                <p class="text-[17px] text-[#1a0dab] leading-snug truncate mt-0.5">{{ form.seo_title || form.name || 'পণ্যের নাম' }}</p>
                <p class="text-[13px] text-[#4d5156] line-clamp-2 mt-0.5">{{ form.seo_description || form.short_description || 'ছোট বিবরণ এখানে দেখাবে।' }}</p>
              </div>
              <div>
                <label class="adm-label" for="pe-slug">লিংক (URL)</label>
                <div class="flex items-stretch">
                  <span class="px-3 flex items-center text-xs text-admin-muted bg-admin-bg border border-r-0 border-[#D5DCD8] rounded-l-[9px] whitespace-nowrap">/product/</span>
                  <input id="pe-slug" v-model.trim="form.slug" class="adm-input !rounded-l-none font-mono text-[12.5px]" :class="err('slug') && 'is-invalid'" :placeholder="autoSlug || 'নিজে থেকে তৈরি হবে'" />
                </div>
                <p v-if="err('slug')" class="adm-error">{{ err('slug') }}</p>
                <p v-else class="adm-help">শুধু ইংরেজি ছোট হাতের অক্ষর, সংখ্যা আর - । প্রকাশের পর বদলালে পুরনো লিংক কাজ করবে না।</p>
              </div>
              <div>
                <label class="adm-label" for="pe-seo-title">শিরোনাম</label>
                <input id="pe-seo-title" v-model="form.seo_title" class="adm-input" maxlength="255" :placeholder="form.name" />
                <p class="adm-help">{{ bnNumber((form.seo_title || '').length) }}/৬০ অক্ষরের মধ্যে রাখলে ভালো</p>
              </div>
              <div>
                <label class="adm-label" for="pe-seo-desc">বিবরণ</label>
                <textarea id="pe-seo-desc" v-model="form.seo_description" rows="2" maxlength="500" class="adm-input" :placeholder="form.short_description"></textarea>
                <p class="adm-help">{{ bnNumber((form.seo_description || '').length) }}/১৬০ অক্ষরের মধ্যে রাখলে ভালো</p>
              </div>
            </div>
          </section>
        </div>

        <!-- ======================================================== side column -->
        <aside class="space-y-5 lg:sticky lg:top-[88px]">
          <!-- Status -->
          <section class="adm-card">
            <header class="adm-card-head"><h3 class="adm-card-title">অবস্থা</h3></header>
            <div class="adm-card-body space-y-2">
              <label v-for="s in statuses" :key="s.value" :class="['pe-status', form.status === s.value && 'is-on']">
                <input v-model="form.status" type="radio" name="pe-status" :value="s.value" class="sr-only" />
                <span :class="['w-2.5 h-2.5 rounded-full mt-1 shrink-0', s.dot]"></span>
                <span>
                  <span class="block text-[13px] font-semibold text-admin-text">{{ s.label }}</span>
                  <span class="block text-[11.5px] text-admin-muted leading-snug">{{ s.help }}</span>
                </span>
              </label>
            </div>
          </section>

          <!-- Organisation -->
          <section class="adm-card">
            <header class="adm-card-head"><h3 class="adm-card-title">ক্যাটাগরি ও কোড</h3></header>
            <div class="adm-card-body space-y-4">
              <div>
                <label class="adm-label" for="pe-cat">ক্যাটাগরি</label>
                <select id="pe-cat" v-model="form.category_id" class="adm-input" :class="err('category_id') && 'is-invalid'">
                  <option :value="null">— বাছাই করুন —</option>
                  <optgroup v-for="g in categoryGroups" :key="g.id" :label="g.name">
                    <option :value="g.id">{{ g.name }} (সব)</option>
                    <option v-for="c in g.children" :key="c.id" :value="c.id">{{ c.name }}</option>
                  </optgroup>
                  <option v-for="c in looseCategories" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
                <p v-if="!form.category_id" class="adm-help text-amber-700">ক্যাটাগরি না দিলে পণ্যটি কোনো ক্যাটাগরি পেজে আসবে না।</p>
              </div>
              <div v-if="brands.length">
                <label class="adm-label" for="pe-brand">ব্র্যান্ড</label>
                <select id="pe-brand" v-model="form.brand_id" class="adm-input">
                  <option :value="null">— নেই —</option>
                  <option v-for="b in brands" :key="b.id" :value="b.id">{{ b.name }}</option>
                </select>
              </div>
              <div>
                <label class="adm-label" for="pe-sku">SKU (পণ্য কোড)</label>
                <input id="pe-sku" v-model.trim="form.sku" class="adm-input font-mono text-[12.5px]" :class="err('sku') && 'is-invalid'" placeholder="খালি রাখলে নিজে থেকে তৈরি হবে" />
                <p v-if="err('sku')" class="adm-error">{{ err('sku') }}</p>
              </div>
            </div>
          </section>

          <!-- Labels -->
          <section class="adm-card">
            <header class="adm-card-head"><h3 class="adm-card-title">লেবেল</h3></header>
            <div class="adm-card-body space-y-3">
              <label v-for="f in flags" :key="f.key" class="flex items-start gap-3 cursor-pointer">
                <input v-model="form[f.key]" type="checkbox" class="pe-switch mt-0.5" />
                <span>
                  <span class="block text-[13px] font-semibold text-admin-text">{{ f.label }}</span>
                  <span class="block text-[11.5px] text-admin-muted leading-snug">{{ f.help }}</span>
                </span>
              </label>
            </div>
          </section>

          <!-- Card preview -->
          <section class="adm-card">
            <header class="adm-card-head"><h3 class="adm-card-title">সাইটে যেমন দেখাবে</h3></header>
            <div class="adm-card-body">
              <div class="rounded-lg overflow-hidden bg-ivory aspect-[3/4] relative">
                <img v-if="images[0]" :src="images[0]" alt="" class="w-full h-full object-cover" />
                <div v-else class="w-full h-full flex items-center justify-center text-admin-muted"><ImageOff :size="28" :stroke-width="1.5" /></div>
                <span v-if="discount > 0" class="absolute top-2 left-2 adm-chip bg-rose-600 text-white">-{{ bnNumber(discount) }}%</span>
                <span v-else-if="form.is_new" class="absolute top-2 left-2 adm-chip bg-primary-black text-white">নতুন</span>
              </div>
              <p class="mt-2.5 text-[13px] font-medium text-admin-text line-clamp-2">{{ form.name || 'পণ্যের নাম' }}</p>
              <p class="mt-1 text-[13px]">
                <strong class="text-admin-text">{{ money(discount > 0 ? form.sale_price : form.price) }}</strong>
                <s v-if="discount > 0" class="ml-1.5 text-admin-muted text-[12px]">{{ money(form.price) }}</s>
              </p>
            </div>
          </section>

          <!-- More -->
          <section v-if="isEdit" class="adm-card">
            <div class="adm-card-body flex flex-col gap-2">
              <button type="button" class="adm-btn justify-start" @click="$emit('duplicate', { id: productId })"><Copy :size="15" /> কপি করে নতুন পণ্য বানান</button>
              <button type="button" class="adm-btn adm-btn-danger justify-start" @click="$emit('delete', { id: productId, name: form.name })"><Trash2 :size="15" /> পণ্যটি মুছুন</button>
            </div>
          </section>
        </aside>
      </form>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onBeforeUnmount, nextTick } from 'vue';
import {
  ArrowLeft, Check, Loader2, ExternalLink, AlertCircle, ChevronLeft, ChevronRight, ChevronDown, Star, X, Plus,
  Trash2, Copy, Grid3x3, ImageOff,
} from 'lucide-vue-next';
import api from '../../api';
import MediaPicker from './MediaPicker.vue';
import { useToastStore } from '../../stores/toast';
import { money, bnNumber } from '../../admin-nav';

const props = defineProps({
  product: { type: Object, default: null },
  categories: { type: Array, default: () => [] },
  brands: { type: Array, default: () => [] },
});
const emit = defineEmits(['close', 'saved', 'duplicate', 'delete']);
const toast = useToastStore();

const productId = ref(props.product?.id || null);
const isEdit = computed(() => Boolean(productId.value));
const title = computed(() => (isEdit.value ? 'পণ্য এডিট' : 'নতুন পণ্য'));
const loading = ref(Boolean(props.product?.id));
const saving = ref(false);
const seoOpen = ref(false);
const savedSlug = ref(props.product?.slug || '');
const nameInput = ref(null);
const siteHost = window.location.host;

const statuses = [
  { value: 'published', label: 'প্রকাশিত', help: 'সাইটে সবাই দেখতে ও কিনতে পারবে', dot: 'bg-emerald-500' },
  { value: 'draft', label: 'ড্রাফট', help: 'শুধু অ্যাডমিনে থাকবে — কাজ শেষ হলে প্রকাশ করুন', dot: 'bg-slate-400' },
  { value: 'archived', label: 'আর্কাইভ', help: 'আর বিক্রি হবে না, কিন্তু পুরনো অর্ডারে থাকবে', dot: 'bg-stone-300' },
];
const flags = [
  { key: 'is_new', label: 'নতুন', help: 'ছবিতে “নতুন” ব্যাজ ও “নতুন কালেকশন”-এ দেখাবে' },
  { key: 'is_bestseller', label: 'বেস্টসেলার', help: '“বেস্টসেলার” তালিকায় দেখাবে' },
  { key: 'is_featured', label: 'হোমপেজে দেখান', help: 'হোমপেজের বাছাই করা পণ্যে দেখাবে' },
];
const sizePresets = [
  { label: 'S–XXL', sizes: ['S', 'M', 'L', 'XL', 'XXL'] },
  { label: '২৮–৩৬', sizes: ['28', '30', '32', '34', '36'] },
  { label: '৩৮–৪৬', sizes: ['38', '40', '42', '44', '46'] },
  { label: 'ফ্রি সাইজ', sizes: ['Free'] },
];

let rowKey = 0;
const blank = () => ({
  name: '', short_description: '', description: '', featured_image: '', gallery: [],
  price: null, sale_price: null, cost_price: null, stock: 0, variants: [],
  fabric_care: '', shipping_info: '', returns_info: '', slug: '', seo_title: '', seo_description: '',
  status: 'published', category_id: null, brand_id: null, sku: '', is_new: true, is_bestseller: false, is_featured: false,
});
const form = reactive(blank());
const images = ref([]);
const errors = ref({});
const bulkStock = ref('');
const builder = reactive({ sizes: [], sizeText: '', colors: [], colorName: '', colorCode: '#111111', stock: 10 });

const num = (v) => (v === '' || v === null || v === undefined ? null : Number(v));

const load = (p) => {
  const price = num(p.price);
  Object.assign(form, blank(), {
    name: p.name || '', short_description: p.short_description || '', description: p.description || '',
    price, sale_price: num(p.sale_price) || null, cost_price: num(p.cost_price), stock: Number(p.stock || 0),
    fabric_care: p.fabric_care || '', shipping_info: p.shipping_info || '', returns_info: p.returns_info || '',
    slug: p.slug || '', seo_title: p.seo_title || '', seo_description: p.seo_description || '',
    status: p.status || 'published', category_id: p.category_id ?? null, brand_id: p.brand_id ?? null, sku: p.sku || '',
    is_new: Boolean(p.is_new), is_bestseller: Boolean(p.is_bestseller), is_featured: Boolean(p.is_featured),
    variants: (p.variants || []).map((v) => ({
      _k: ++rowKey, id: v.id, size: v.size || '', color: v.color || '', color_code: v.color_code || '#111111', sku: v.sku || '',
      stock: Number(v.stock || 0),
      price: num(v.price) !== null && num(v.price) !== price ? num(v.price) : null, // same as product price = "no separate price"
    })),
  });
  images.value = [p.featured_image, ...(Array.isArray(p.gallery) ? p.gallery : [])].filter(Boolean).filter((s, i, a) => a.indexOf(s) === i);
  builder.sizes = [...new Set(form.variants.map((v) => v.size).filter(Boolean))];
  const colors = new Map();
  form.variants.forEach((v) => v.color && !colors.has(v.color) && colors.set(v.color, { name: v.color, code: v.color_code }));
  builder.colors = [...colors.values()];
  savedSlug.value = p.slug || '';
  snapshot.value = serialize();
};

// ------------------------------------------------------------------ dirty tracking
const snapshot = ref('');
const serialize = () => JSON.stringify({ ...form, variants: form.variants.map(({ _k, ...v }) => v), images: images.value });
const dirty = computed(() => snapshot.value !== '' && serialize() !== snapshot.value);

// ------------------------------------------------------------------ derived
const discount = computed(() => {
  const p = Number(form.price), s = Number(form.sale_price);
  return p > 0 && s > 0 && s < p ? Math.round((1 - s / p) * 100) : 0;
});
const profit = computed(() => {
  if (!form.cost_price && form.cost_price !== 0) return null;
  if (!form.price) return null;
  return (discount.value > 0 ? Number(form.sale_price) : Number(form.price)) - Number(form.cost_price);
});
const profitPct = computed(() => {
  const sell = discount.value > 0 ? Number(form.sale_price) : Number(form.price);
  return sell ? Math.round((profit.value / sell) * 100) : 0;
});
const totalStock = computed(() => (form.variants.length ? form.variants.reduce((s, v) => s + Math.max(0, Number(v.stock) || 0), 0) : Number(form.stock) || 0));
const autoSlug = computed(() => (form.name || '').toLowerCase().normalize('NFKD').replace(/[^a-z0-9\s-]/g, '').trim().replace(/[\s-]+/g, '-'));

const categoryGroups = computed(() => {
  const roots = props.categories.filter((c) => !c.parent_id);
  return roots.map((r) => ({ ...r, children: props.categories.filter((c) => c.parent_id === r.id) }));
});
const looseCategories = computed(() => props.categories.filter((c) => c.parent_id && !props.categories.some((r) => r.id === c.parent_id)));

// ------------------------------------------------------------------ images
const addImages = (urls) => {
  urls.forEach((u) => !images.value.includes(u) && images.value.push(u));
};
const moveImage = (i, d) => {
  const j = i + d;
  if (j < 0 || j >= images.value.length) return;
  const arr = [...images.value];
  [arr[i], arr[j]] = [arr[j], arr[i]];
  images.value = arr;
};
const makeMain = (i) => {
  const arr = [...images.value];
  const [img] = arr.splice(i, 1);
  images.value = [img, ...arr];
};
const removeImage = (i) => images.value.splice(i, 1);

// ------------------------------------------------------------------ variants
const addSizes = () => {
  builder.sizeText.split(/[,\s]+/).map((s) => s.trim()).filter(Boolean).forEach((s) => !builder.sizes.includes(s) && builder.sizes.push(s));
  builder.sizeText = '';
};
const addSizeSet = (list) => list.forEach((s) => !builder.sizes.includes(s) && builder.sizes.push(s));
const addColor = () => {
  const name = builder.colorName.trim();
  if (!name) return;
  const existing = builder.colors.find((c) => c.name.toLowerCase() === name.toLowerCase());
  existing ? (existing.code = builder.colorCode) : builder.colors.push({ name, code: builder.colorCode });
  builder.colorName = '';
};
const buildRows = () => {
  addSizes();
  addColor();
  const sizes = builder.sizes.length ? builder.sizes : [''];
  const colors = builder.colors.length ? builder.colors : [{ name: '', code: '#111111' }];
  let added = 0;
  sizes.forEach((size) => colors.forEach((c) => {
    const exists = form.variants.some((v) => (v.size || '') === size && (v.color || '').toLowerCase() === c.name.toLowerCase());
    if (!exists) {
      form.variants.push({ _k: ++rowKey, id: null, size, color: c.name, color_code: c.code, sku: '', stock: Number(builder.stock) || 0, price: null });
      added++;
    }
  }));
  // keep colour codes in sync with the builder
  form.variants.forEach((v) => {
    const c = builder.colors.find((x) => x.name.toLowerCase() === (v.color || '').toLowerCase());
    if (c) v.color_code = c.code;
  });
  toast.success(added ? `${bnNumber(added)}টি নতুন সারি যোগ হয়েছে` : 'সব কম্বিনেশন আগে থেকেই আছে');
};
const addRow = () => form.variants.push({ _k: ++rowKey, id: null, size: '', color: '', color_code: '#111111', sku: '', stock: 0, price: null });
const applyBulkStock = () => {
  form.variants.forEach((v) => (v.stock = Number(bulkStock.value) || 0));
  bulkStock.value = '';
};

// ------------------------------------------------------------------ errors
const FIELD_LABELS = { name: 'নাম', price: 'দাম', sale_price: 'সেল প্রাইস', slug: 'লিংক', sku: 'SKU', category_id: 'ক্যাটাগরি' };
const err = (key) => errors.value[key]?.[0] || '';
const errorList = computed(() => Object.entries(errors.value).map(([k, v]) => {
  const m = k.match(/^variants\.(\d+)\./);
  if (m) {
    const row = form.variants[Number(m[1])];
    return `সাইজ/রং সারি ${bnNumber(Number(m[1]) + 1)} (${[row?.size, row?.color].filter(Boolean).join(' / ') || '—'}): ${v[0]}`;
  }
  return FIELD_LABELS[k] && !v[0].includes(FIELD_LABELS[k]) ? `${FIELD_LABELS[k]}: ${v[0]}` : v[0];
}));

const validateLocally = () => {
  const e = {};
  if (!form.name.trim()) e.name = ['পণ্যের নাম দিন।'];
  if (form.price === null || form.price === '' || Number(form.price) < 0) e.price = ['দাম দিন।'];
  if (form.sale_price && Number(form.sale_price) >= Number(form.price)) e.sale_price = ['সেল প্রাইস আসল দামের চেয়ে কম হতে হবে।'];
  if (form.slug && !/^[a-z0-9-_]+$/i.test(form.slug)) e.slug = ['লিংকে শুধু ইংরেজি অক্ষর, সংখ্যা ও - ব্যবহার করুন।'];
  return e;
};

// ------------------------------------------------------------------ save
const save = async () => {
  if (saving.value) return;
  errors.value = validateLocally();
  if (Object.keys(errors.value).length) {
    document.querySelector('.pe-root')?.scrollTo({ top: 0, behavior: 'smooth' });
    return;
  }
  saving.value = true;
  const payload = {
    ...form,
    featured_image: images.value[0] || '',
    gallery: images.value.slice(1),
    sale_price: form.sale_price || null,
    slug: form.slug || null,
    sku: form.sku || null,
    stock: totalStock.value,
    variants: form.variants.map(({ _k, ...v }) => ({ ...v, price: v.price === '' || v.price === null ? null : Number(v.price), stock: Number(v.stock) || 0 })),
  };
  try {
    const res = isEdit.value
      ? await api.put(`/admin/products/${productId.value}`, payload)
      : await api.post('/admin/products', payload);
    const saved = res.data.data;
    toast.success(isEdit.value ? 'পণ্য সেভ হয়েছে' : 'নতুন পণ্য যোগ হয়েছে');
    productId.value = saved.id;
    load(saved);
    errors.value = {};
    emit('saved', saved, { close: false });
  } catch (e) {
    if (e.response?.status === 422) {
      errors.value = e.response.data.errors || {};
      document.querySelector('.pe-root')?.scrollTo({ top: 0, behavior: 'smooth' });
    } else {
      toast.error(e.response?.data?.message || 'সেভ করা যায়নি');
    }
  } finally {
    saving.value = false;
  }
};

const requestClose = () => {
  if (dirty.value && !confirm('সেভ না করা পরিবর্তন আছে। সেভ না করেই বের হবেন?')) return;
  emit('close');
};

const onKey = (e) => {
  if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') {
    e.preventDefault();
    save();
  } else if (e.key === 'Escape' && !document.querySelector('.fixed.inset-0.z-\\[70\\]')) {
    // (a media-library modal on top handles its own Escape)
    requestClose();
  }
};
const onBeforeUnload = (e) => {
  if (dirty.value) {
    e.preventDefault();
    e.returnValue = '';
  }
};

onMounted(async () => {
  document.documentElement.style.overflow = 'hidden';
  window.addEventListener('keydown', onKey);
  window.addEventListener('beforeunload', onBeforeUnload);
  if (props.product?.id) {
    try {
      const res = await api.get(`/admin/products/${props.product.id}`);
      load(res.data.data);
    } catch {
      load(props.product);
    } finally {
      loading.value = false;
    }
  } else {
    snapshot.value = serialize();
    await nextTick();
    nameInput.value?.focus();
  }
});
onBeforeUnmount(() => {
  document.documentElement.style.overflow = '';
  window.removeEventListener('keydown', onKey);
  window.removeEventListener('beforeunload', onBeforeUnload);
});
</script>

<style scoped>
.pe-thumb { position: relative; aspect-ratio: 3 / 4; border-radius: 10px; overflow: hidden; background: var(--color-admin-bg); border: 1px solid var(--color-admin-border); }
.pe-thumb.is-main { outline: 2px solid var(--color-admin-sidebar); outline-offset: 1px; }
.pe-thumb-actions {
  position: absolute; inset: auto 0 0 0; display: flex; justify-content: center; gap: 4px; padding: 6px;
  background: linear-gradient(to top, rgba(0, 0, 0, .6), transparent); opacity: 0; transition: opacity .15s;
}
.pe-thumb:hover .pe-thumb-actions, .pe-thumb:focus-within .pe-thumb-actions { opacity: 1; }
@media (hover: none) { .pe-thumb-actions { opacity: 1; } }
.pe-thumb-actions button {
  width: 26px; height: 26px; border-radius: 7px; display: flex; align-items: center; justify-content: center;
  background: rgba(255, 255, 255, .92); color: #171C1A; transition: background .15s, color .15s;
}
.pe-thumb-actions button:hover:not(:disabled) { background: var(--color-admin-sidebar); color: #fff; }
.pe-thumb-actions button:disabled { opacity: .35; }

.pe-money { position: relative; }
.pe-money > span { position: absolute; left: 11px; top: 50%; transform: translateY(-50%); font-size: 13px; color: var(--color-admin-muted); pointer-events: none; }
.pe-money > input { padding-left: 26px; }
.pe-money-sm > span { left: 9px; font-size: 12px; }
.pe-money-sm > input { padding-left: 22px; }

.pe-tag {
  display: inline-flex; align-items: center; gap: 6px; padding: 3px 4px 3px 10px; border-radius: 999px;
  background: #fff; border: 1px solid var(--color-admin-border); font-size: 12px; font-weight: 600; color: var(--color-admin-text);
}
.pe-tag button { width: 18px; height: 18px; border-radius: 999px; display: flex; align-items: center; justify-content: center; color: var(--color-admin-muted); }
.pe-tag button:hover { background: #FEE2E2; color: #DC2626; }
.pe-preset { font-size: 11.5px; font-weight: 600; padding: 3px 9px; border-radius: 999px; color: var(--color-admin-sidebar); background: #EEF6F1; }
.pe-preset:hover { background: #DCEFE4; }
.pe-zero { color: #DC2626; background: #FFF7F7; }

.pe-status {
  display: flex; align-items: flex-start; gap: 10px; padding: 10px 12px; border-radius: 10px; cursor: pointer;
  border: 1px solid var(--color-admin-border); transition: border-color .15s, background .15s;
}
.pe-status:hover { border-color: #C9D2CD; }
.pe-status.is-on { border-color: var(--color-admin-accent); background: #F3FAF6; box-shadow: 0 0 0 1px var(--color-admin-accent) inset; }
.pe-status:focus-within { box-shadow: 0 0 0 3px rgba(22, 163, 106, .2); }

.pe-switch {
  appearance: none; flex-shrink: 0; width: 34px; height: 20px; border-radius: 999px; background: #D5DCD8; position: relative; cursor: pointer; transition: background .15s;
}
.pe-switch::after {
  content: ''; position: absolute; top: 2px; left: 2px; width: 16px; height: 16px; border-radius: 999px; background: #fff;
  box-shadow: 0 1px 2px rgba(0, 0, 0, .2); transition: transform .15s;
}
.pe-switch:checked { background: var(--color-admin-accent); }
.pe-switch:checked::after { transform: translateX(14px); }
.pe-switch:focus-visible { outline: 2px solid var(--color-admin-accent); outline-offset: 2px; }
</style>
