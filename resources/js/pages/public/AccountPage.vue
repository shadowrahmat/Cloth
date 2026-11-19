<template>
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-14">
    <!-- ======================= SIGNED OUT ======================= -->
    <div v-if="!auth.isCustomerAuthenticated" class="grid lg:grid-cols-2 gap-8 lg:gap-16 items-center">
      <section class="order-2 lg:order-1 space-y-6" v-reveal>
        <div>
          <h1 class="font-serif text-3xl sm:text-4xl text-[#111111] leading-tight">আপনার অ্যাকাউন্ট</h1>
          <p class="mt-2 text-[15px] text-[#69736E]">লগইন করলে কেনাকাটা আরও সহজ।</p>
        </div>
        <ul class="space-y-4">
          <li v-for="b in BENEFITS" :key="b.title" class="flex gap-3.5">
            <span class="w-10 h-10 rounded-full bg-white border border-[#E5DED3] flex items-center justify-center shrink-0"><component :is="b.icon" class="w-[18px] h-[18px]" :stroke-width="1.6" /></span>
            <div><p class="text-[15px] font-medium text-[#111111]">{{ b.title }}</p><p class="text-[13.5px] text-[#69736E]">{{ b.text }}</p></div>
          </li>
        </ul>
      </section>

      <section class="order-1 lg:order-2 bg-white rounded-2xl border border-[#E5DED3] p-6 sm:p-8" v-reveal="{ delay: 80 }">
        <div class="grid grid-cols-2 p-1 rounded-full bg-[#F7F5F0] mb-6" role="tablist">
          <button v-for="t in [['login', 'লগইন'], ['register', 'নতুন অ্যাকাউন্ট']]" :key="t[0]" type="button" role="tab" :aria-selected="authTab === t[0]"
                  @click="authTab = t[0]; authError = ''"
                  :class="['h-10 rounded-full text-[14px] font-medium transition-all', authTab === t[0] ? 'bg-white shadow-sm text-[#111111]' : 'text-[#69736E]']">{{ t[1] }}</button>
        </div>

        <form v-if="authTab === 'login'" @submit.prevent="handleLogin" class="space-y-4" novalidate>
          <Field label="ইমেইল"><input v-model.trim="loginForm.email" type="email" autocomplete="email" required :class="inputCls" placeholder="you@example.com" /></Field>
          <Field label="পাসওয়ার্ড">
            <div class="relative">
              <input v-model="loginForm.password" :type="showPw ? 'text' : 'password'" autocomplete="current-password" required :class="[inputCls, 'pr-11']" />
              <button type="button" @click="showPw = !showPw" class="absolute right-2 top-1/2 -translate-y-1/2 w-8 h-8 flex items-center justify-center text-[#A89B8A]" :aria-label="showPw ? 'পাসওয়ার্ড লুকান' : 'পাসওয়ার্ড দেখুন'"><EyeOff v-if="showPw" class="w-4 h-4" /><Eye v-else class="w-4 h-4" /></button>
            </div>
          </Field>
          <p v-if="authError" class="text-[13px] text-rose-600">{{ authError }}</p>
          <button type="submit" :disabled="authLoading" class="w-full h-12 rounded-full bg-[#111111] text-white text-[15px] font-medium disabled:opacity-60 inline-flex items-center justify-center gap-2">
            <span v-if="authLoading" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span> লগইন করুন
          </button>
          <p class="text-center text-[13px] text-[#69736E]">অ্যাকাউন্ট নেই? <button type="button" @click="authTab = 'register'" class="font-medium text-[#111111] underline underline-offset-4">নতুন খুলুন</button></p>
        </form>

        <form v-else @submit.prevent="handleRegister" class="space-y-4" novalidate>
          <Field label="পুরো নাম"><input v-model.trim="registerForm.name" autocomplete="name" required :class="inputCls" /></Field>
          <div class="grid sm:grid-cols-2 gap-4">
            <Field label="ইমেইল"><input v-model.trim="registerForm.email" type="email" autocomplete="email" required :class="inputCls" /></Field>
            <Field label="মোবাইল (ঐচ্ছিক)"><input v-model.trim="registerForm.phone" type="tel" inputmode="tel" autocomplete="tel" :class="inputCls" placeholder="01XXXXXXXXX" /></Field>
          </div>
          <Field label="পাসওয়ার্ড" hint="কমপক্ষে ৬ অক্ষর">
            <div class="relative">
              <input v-model="registerForm.password" :type="showPw ? 'text' : 'password'" autocomplete="new-password" required minlength="6" :class="[inputCls, 'pr-11']" />
              <button type="button" @click="showPw = !showPw" class="absolute right-2 top-1/2 -translate-y-1/2 w-8 h-8 flex items-center justify-center text-[#A89B8A]" :aria-label="showPw ? 'পাসওয়ার্ড লুকান' : 'পাসওয়ার্ড দেখুন'"><EyeOff v-if="showPw" class="w-4 h-4" /><Eye v-else class="w-4 h-4" /></button>
            </div>
          </Field>
          <Field label="পাসওয়ার্ড আবার লিখুন"><input v-model="registerForm.password_confirmation" :type="showPw ? 'text' : 'password'" autocomplete="new-password" required :class="inputCls" /></Field>
          <p v-if="authError" class="text-[13px] text-rose-600">{{ authError }}</p>
          <button type="submit" :disabled="authLoading" class="w-full h-12 rounded-full bg-[#111111] text-white text-[15px] font-medium disabled:opacity-60 inline-flex items-center justify-center gap-2">
            <span v-if="authLoading" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span> অ্যাকাউন্ট খুলুন
          </button>
        </form>
      </section>
    </div>

    <!-- ======================= SIGNED IN ======================= -->
    <div v-else class="space-y-6">
      <!-- Profile header -->
      <header class="flex items-center gap-4" v-reveal>
        <span class="w-14 h-14 sm:w-16 sm:h-16 rounded-full bg-[#111111] text-white font-serif text-2xl flex items-center justify-center shrink-0">{{ initial }}</span>
        <div class="min-w-0 flex-1">
          <p class="text-[13px] text-[#A89B8A]">{{ greeting }}</p>
          <h1 class="font-serif text-2xl sm:text-3xl text-[#111111] truncate">{{ user?.name }}</h1>
          <p class="text-[13px] text-[#69736E] truncate">{{ user?.email }}<span v-if="user?.phone"> · {{ user.phone }}</span></p>
        </div>
        <button type="button" @click="handleLogout" class="hidden sm:inline-flex h-10 px-4 items-center gap-2 rounded-full border border-[#E5DED3] text-[13px] hover:border-[#111111]"><LogOut class="w-4 h-4" /> লগআউট</button>
      </header>

      <!-- Stats -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-3" v-reveal="{ delay: 60 }">
        <button v-for="s in stats" :key="s.label" type="button" @click="s.tab && (tab = s.tab)" class="text-left p-4 rounded-xl bg-white border border-[#E5DED3] hover:border-[#111111]/30 transition-colors">
          <component :is="s.icon" class="w-[18px] h-[18px] text-[#A89B8A]" :stroke-width="1.6" />
          <p class="mt-2 text-[20px] sm:text-[22px] font-semibold text-[#111111] tabular-nums">{{ s.value }}</p>
          <p class="text-[12.5px] text-[#69736E]">{{ s.label }}</p>
        </button>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-[220px_minmax(0,1fr)] gap-6 items-start">
        <!-- Nav: sidebar on desktop, sticky chip row on phones -->
        <nav class="sticky top-14 lg:top-24 z-20 -mx-4 px-4 py-2 lg:m-0 lg:p-0 bg-[#F7F5F0]/95 lg:bg-transparent backdrop-blur lg:backdrop-blur-none" aria-label="অ্যাকাউন্ট মেনু">
          <div class="flex lg:flex-col gap-1.5 lg:gap-1 overflow-x-auto no-scrollbar">
            <button v-for="t in TABS" :key="t.key" type="button" @click="tab = t.key" :aria-current="tab === t.key ? 'page' : undefined"
                    :class="['shrink-0 flex items-center gap-2.5 h-10 px-4 lg:px-3 rounded-full lg:rounded-lg text-[14px] transition-colors',
                             tab === t.key ? 'bg-[#111111] text-white lg:bg-white lg:text-[#111111] lg:font-semibold lg:shadow-sm' : 'bg-white lg:bg-transparent text-[#4B5450] border border-[#E5DED3] lg:border-0 hover:lg:bg-white/70']">
              <component :is="t.icon" class="hidden lg:block w-4 h-4" :stroke-width="1.7" />
              {{ t.label }}
              <span v-if="t.count" :class="['ml-auto min-w-5 h-5 px-1.5 rounded-full text-[11px] flex items-center justify-center', tab === t.key ? 'bg-white/20 lg:bg-[#F2EEE7]' : 'bg-[#F2EEE7]']">{{ bnNumber(t.count) }}</span>
            </button>
            <router-link to="/wishlist" class="shrink-0 flex items-center gap-2.5 h-10 px-4 lg:px-3 rounded-full lg:rounded-lg text-[14px] bg-white lg:bg-transparent text-[#4B5450] border border-[#E5DED3] lg:border-0 hover:lg:bg-white/70">
              <Heart class="hidden lg:block w-4 h-4" :stroke-width="1.7" /> উইশলিস্ট
            </router-link>
            <button type="button" @click="handleLogout" class="lg:mt-3 shrink-0 flex items-center gap-2.5 h-10 px-4 lg:px-3 rounded-full lg:rounded-lg text-[14px] text-rose-600 bg-white lg:bg-transparent border border-[#E5DED3] lg:border-0 hover:lg:bg-rose-50">
              <LogOut class="hidden lg:block w-4 h-4" :stroke-width="1.7" /> লগআউট
            </button>
          </div>
        </nav>

        <div class="min-w-0">
          <Transition name="fade" mode="out-in">
            <!-- ---------- OVERVIEW ---------- -->
            <div v-if="tab === 'overview'" key="overview" class="space-y-5">
              <section class="bg-white rounded-2xl border border-[#E5DED3] p-5 sm:p-6">
                <div class="flex items-center justify-between mb-4">
                  <h2 class="text-[16px] font-semibold text-[#111111]">{{ activeOrder ? 'চলমান অর্ডার' : 'সর্বশেষ অর্ডার' }}</h2>
                  <button v-if="orders.length" type="button" @click="tab = 'orders'" class="text-[13px] text-[#111111] underline underline-offset-4">সব অর্ডার</button>
                </div>
                <OrderSummary v-if="spotlight" :order="spotlight" />
                <div v-else class="py-8 text-center">
                  <Package class="w-8 h-8 mx-auto text-[#D8CCBC]" :stroke-width="1.4" />
                  <p class="mt-2 text-[14px] text-[#69736E]">এখনো কোনো অর্ডার নেই</p>
                  <router-link to="/collections" class="mt-4 inline-flex h-10 px-5 items-center rounded-full bg-[#111111] text-white text-[13.5px]">কেনাকাটা শুরু করুন</router-link>
                </div>
              </section>

              <section class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <component :is="a.to ? 'router-link' : 'button'" v-for="a in QUICK" :key="a.label" :to="a.to" type="button" @click="a.tab && (tab = a.tab)"
                           class="p-4 rounded-xl bg-white border border-[#E5DED3] hover:border-[#111111]/30 transition-colors text-left">
                  <component :is="a.icon" class="w-5 h-5 text-[#111111]" :stroke-width="1.6" />
                  <p class="mt-2.5 text-[13.5px] font-medium text-[#111111]">{{ a.label }}</p>
                  <p class="text-[12px] text-[#69736E]">{{ a.hint }}</p>
                </component>
              </section>

              <section class="bg-white rounded-2xl border border-[#E5DED3] p-5 sm:p-6">
                <div class="flex items-center justify-between mb-3">
                  <h2 class="text-[16px] font-semibold text-[#111111]">ডেলিভারি ঠিকানা</h2>
                  <button type="button" @click="tab = 'addresses'" class="text-[13px] text-[#111111] underline underline-offset-4">{{ defaultAddress ? 'বদলান' : 'যোগ করুন' }}</button>
                </div>
                <AddressText v-if="defaultAddress" :addr="defaultAddress" />
                <p v-else class="text-[14px] text-[#69736E]">কোনো ঠিকানা সংরক্ষিত নেই — একবার যোগ করলে চেকআউট দ্রুত হবে।</p>
              </section>
            </div>

            <!-- ---------- ORDERS ---------- -->
            <div v-else-if="tab === 'orders'" key="orders" class="space-y-4">
              <div class="flex gap-2 overflow-x-auto no-scrollbar">
                <button v-for="f in ORDER_FILTERS" :key="f.key" type="button" @click="orderFilter = f.key"
                        :class="['shrink-0 h-9 px-4 rounded-full border text-[13px] transition-colors', orderFilter === f.key ? 'bg-[#111111] text-white border-[#111111]' : 'bg-white border-[#E5DED3] text-[#4B5450]']">
                  {{ f.label }} <span class="opacity-60">{{ bnNumber(filterCount(f.key)) }}</span>
                </button>
              </div>

              <div v-if="customer.loading && !orders.length" class="space-y-3"><div v-for="n in 3" :key="n" class="skeleton h-40 rounded-2xl"></div></div>
              <p v-else-if="!filteredOrders.length" class="py-14 text-center text-[14px] text-[#69736E] bg-white rounded-2xl border border-[#E5DED3]">এই তালিকায় কোনো অর্ডার নেই।</p>

              <article v-for="order in filteredOrders" :key="order.id" class="bg-white rounded-2xl border border-[#E5DED3] overflow-hidden">
                <div class="p-5 sm:p-6">
                  <OrderSummary :order="order" />
                </div>

                <!-- Details -->
                <div class="grid transition-[grid-template-rows] duration-300 ease-out" :style="{ gridTemplateRows: expanded === order.id ? '1fr' : '0fr' }">
                  <div class="overflow-hidden">
                    <div class="px-5 sm:px-6 pb-5 space-y-4 border-t border-[#F0ECE5] pt-4">
                      <ul class="space-y-3">
                        <li v-for="item in order.items" :key="item.id" class="flex items-center gap-3">
                          <div class="w-12 h-14 rounded-lg overflow-hidden bg-[#EFEBE4] shrink-0"><img v-if="itemImage(item)" :src="itemImage(item)" alt="" class="w-full h-full object-cover" /></div>
                          <div class="flex-1 min-w-0">
                            <p class="text-[14px] text-[#111111] truncate">{{ item.product_name }}</p>
                            <p class="text-[12.5px] text-[#69736E]">{{ [item.size && `সাইজ ${item.size}`, item.color].filter(Boolean).join(' · ') }} · {{ bnNumber(item.quantity) }} × {{ taka(item.price) }}</p>
                          </div>
                          <p class="text-[14px] font-medium tabular-nums">{{ taka(item.subtotal) }}</p>
                        </li>
                      </ul>
                      <div class="grid sm:grid-cols-2 gap-4 text-[13.5px]">
                        <div>
                          <p class="text-[12px] text-[#A89B8A] mb-1">ডেলিভারি ঠিকানা</p>
                          <p class="text-[#111111]">{{ formatAddress(order.shipping_address) }}</p>
                          <p v-if="order.tracking_number" class="mt-1 text-[#69736E]">ট্র্যাকিং: <span class="font-mono">{{ order.tracking_number }}</span></p>
                        </div>
                        <dl class="space-y-1 sm:text-right">
                          <div class="flex justify-between sm:justify-end gap-6"><dt class="text-[#69736E]">সাবটোটাল</dt><dd class="tabular-nums">{{ taka(order.subtotal) }}</dd></div>
                          <div v-if="Number(order.discount_amount)" class="flex justify-between sm:justify-end gap-6"><dt class="text-[#69736E]">ছাড়</dt><dd class="tabular-nums">− {{ taka(order.discount_amount) }}</dd></div>
                          <div class="flex justify-between sm:justify-end gap-6"><dt class="text-[#69736E]">ডেলিভারি</dt><dd class="tabular-nums">{{ taka(order.shipping_amount) }}</dd></div>
                          <div class="flex justify-between sm:justify-end gap-6 font-semibold pt-1"><dt>মোট</dt><dd class="tabular-nums">{{ taka(order.total) }}</dd></div>
                          <div class="flex justify-between sm:justify-end gap-6 text-[12.5px]"><dt class="text-[#69736E]">পেমেন্ট</dt><dd>{{ PAYMENT[order.payment_status] || order.payment_status }}</dd></div>
                        </dl>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Actions -->
                <div class="px-5 sm:px-6 py-3 bg-[#FBFAF7] border-t border-[#F0ECE5] flex flex-wrap gap-2">
                  <button type="button" @click="expanded = expanded === order.id ? null : order.id" class="h-9 px-4 rounded-full border border-[#E5DED3] bg-white text-[13px] inline-flex items-center gap-1.5">
                    {{ expanded === order.id ? 'কম দেখুন' : 'বিস্তারিত' }} <ChevronDown :class="['w-3.5 h-3.5 transition-transform', expanded === order.id && 'rotate-180']" />
                  </button>
                  <router-link :to="{ path: '/track-order', query: { order: order.order_number } }" class="h-9 px-4 rounded-full border border-[#E5DED3] bg-white text-[13px] inline-flex items-center gap-1.5"><Truck class="w-3.5 h-3.5" /> ট্র্যাক</router-link>
                  <button v-if="order.order_status === 'delivered'" type="button" @click="openReturn(order)" class="h-9 px-4 rounded-full border border-[#E5DED3] bg-white text-[13px] inline-flex items-center gap-1.5"><RotateCcw class="w-3.5 h-3.5" /> রিটার্ন / এক্সচেঞ্জ</button>
                  <button type="button" @click="reorder(order)" :disabled="reordering === order.id" class="ml-auto h-9 px-4 rounded-full bg-[#111111] text-white text-[13px] inline-flex items-center gap-1.5 disabled:opacity-50">
                    <span v-if="reordering === order.id" class="w-3.5 h-3.5 border-2 border-white/30 border-t-white rounded-full animate-spin"></span><RefreshCw v-else class="w-3.5 h-3.5" /> আবার কিনুন
                  </button>
                </div>
              </article>
            </div>

            <!-- ---------- RETURNS ---------- -->
            <div v-else-if="tab === 'returns'" key="returns" class="space-y-4">
              <p v-if="!customer.returns.length" class="py-14 px-6 text-center text-[14px] text-[#69736E] bg-white rounded-2xl border border-[#E5DED3]">
                কোনো রিটার্ন বা এক্সচেঞ্জ অনুরোধ নেই। ডেলিভারি হওয়া অর্ডারের পাশে “রিটার্ন / এক্সচেঞ্জ” বাটন পাবেন।
              </p>
              <article v-for="ret in customer.returns" :key="ret.id" class="bg-white rounded-2xl border border-[#E5DED3] p-5 sm:p-6 space-y-4">
                <div class="flex items-start justify-between gap-3">
                  <div>
                    <p class="font-mono text-[14px] font-semibold text-[#111111]">{{ ret.return_number }}</p>
                    <p class="text-[12.5px] text-[#69736E]">অর্ডার {{ ret.order?.order_number }} · {{ ret.type === 'exchange' ? 'এক্সচেঞ্জ' : 'রিটার্ন' }} · {{ formatDate(ret.created_at) }}</p>
                  </div>
                  <span :class="['shrink-0 px-2.5 h-7 inline-flex items-center rounded-full text-[12px] font-medium', RETURN_STATUS[ret.status]?.[1]]">{{ RETURN_STATUS[ret.status]?.[0] || ret.status }}</span>
                </div>
                <Stepper v-if="ret.status !== 'rejected'" :steps="['অনুরোধ', 'অনুমোদিত', 'সম্পন্ন']" :current="{ pending: 0, approved: 1, completed: 2 }[ret.status] ?? 0" />
                <ul class="text-[14px] text-[#111111] space-y-0.5"><li v-for="ri in ret.items" :key="ri.id">{{ ri.order_item?.product_name }} × {{ bnNumber(ri.quantity) }}</li></ul>
                <div class="flex flex-wrap gap-x-6 gap-y-1 text-[13px] text-[#69736E]">
                  <span>কারণ: <span class="text-[#111111]">{{ ret.reason }}</span></span>
                  <span v-if="ret.type === 'return' && Number(ret.refund_amount)">রিফান্ড: <span class="text-[#111111] font-medium">{{ taka(ret.refund_amount) }}</span></span>
                </div>
                <p v-if="ret.admin_notes" class="p-3 rounded-lg bg-[#F7F5F0] text-[13.5px] text-[#111111]"><span class="font-medium">স্টোরের নোট:</span> {{ ret.admin_notes }}</p>
              </article>
            </div>

            <!-- ---------- ADDRESSES ---------- -->
            <div v-else-if="tab === 'addresses'" key="addresses" class="space-y-4">
              <div class="grid sm:grid-cols-2 gap-3">
                <article v-for="addr in customer.addresses" :key="addr.id" class="relative bg-white rounded-2xl border border-[#E5DED3] p-5">
                  <div class="flex items-center gap-2 mb-2">
                    <MapPin class="w-4 h-4 text-[#A89B8A]" />
                    <p class="text-[14px] font-semibold text-[#111111]">{{ addr.first_name }} {{ addr.last_name }}</p>
                    <span v-if="addr.is_default" class="ml-auto px-2 h-6 inline-flex items-center rounded-full bg-[#111111] text-white text-[11px]">ডিফল্ট</span>
                  </div>
                  <AddressText :addr="addr" />
                  <button type="button" @click="removeAddress(addr)" class="mt-3 text-[13px] text-[#69736E] hover:text-rose-600 inline-flex items-center gap-1"><Trash2 class="w-3.5 h-3.5" /> মুছুন</button>
                </article>
                <button v-if="!addressFormOpen" type="button" @click="addressFormOpen = true"
                        class="min-h-[140px] rounded-2xl border-2 border-dashed border-[#D8CCBC] text-[14px] text-[#4B5450] hover:border-[#111111] hover:text-[#111111] flex flex-col items-center justify-center gap-2 transition-colors">
                  <Plus class="w-5 h-5" /> নতুন ঠিকানা যোগ করুন
                </button>
              </div>

              <form v-if="addressFormOpen" @submit.prevent="handleSaveAddress" class="bg-white rounded-2xl border border-[#E5DED3] p-5 sm:p-6 space-y-4" v-reveal>
                <div class="flex items-center justify-between">
                  <h3 class="text-[16px] font-semibold">নতুন ঠিকানা</h3>
                  <button type="button" @click="addressFormOpen = false" class="w-8 h-8 rounded-full flex items-center justify-center hover:bg-black/5" aria-label="বন্ধ করুন"><X class="w-4 h-4" /></button>
                </div>
                <div class="grid grid-cols-2 gap-3">
                  <Field label="নামের প্রথম অংশ"><input v-model="addressForm.first_name" required :class="inputCls" /></Field>
                  <Field label="শেষ অংশ"><input v-model="addressForm.last_name" required :class="inputCls" /></Field>
                </div>
                <Field label="ঠিকানা"><input v-model="addressForm.address_line_1" required :class="inputCls" placeholder="বাড়ি, রোড, এলাকা" /></Field>
                <Field label="ফ্ল্যাট / ল্যান্ডমার্ক (ঐচ্ছিক)"><input v-model="addressForm.address_line_2" :class="inputCls" /></Field>
                <div class="grid grid-cols-2 gap-3">
                  <Field label="শহর / জেলা"><input v-model="addressForm.city" required :class="inputCls" /></Field>
                  <Field label="পোস্ট কোড"><input v-model="addressForm.postal_code" required inputmode="numeric" :class="inputCls" /></Field>
                </div>
                <Field label="মোবাইল নম্বর"><input v-model="addressForm.phone" type="tel" inputmode="tel" :class="inputCls" /></Field>
                <label class="flex items-center gap-2.5 text-[14px]"><input v-model="addressForm.is_default" type="checkbox" class="w-4 h-4 accent-[#111111]" /> এটাকে ডিফল্ট ঠিকানা করুন</label>
                <p v-if="addressMsg.text" :class="['text-[13px]', addressMsg.error ? 'text-rose-600' : 'text-emerald-700']">{{ addressMsg.text }}</p>
                <button type="submit" :disabled="addressSaving" class="h-11 px-6 rounded-full bg-[#111111] text-white text-[14px] disabled:opacity-60">{{ addressSaving ? 'সংরক্ষণ হচ্ছে…' : 'ঠিকানা সংরক্ষণ করুন' }}</button>
              </form>
            </div>

            <!-- ---------- PROFILE ---------- -->
            <div v-else key="profile" class="grid gap-4 md:grid-cols-2 items-start">
              <form @submit.prevent="saveProfile" class="bg-white rounded-2xl border border-[#E5DED3] p-5 sm:p-6 space-y-4">
                <h3 class="text-[16px] font-semibold">ব্যক্তিগত তথ্য</h3>
                <Field label="ইমেইল" hint="ইমেইল বদলাতে আমাদের সাথে যোগাযোগ করুন"><input :value="user?.email" disabled :class="[inputCls, 'opacity-60']" /></Field>
                <Field label="পুরো নাম"><input v-model="profileForm.name" required :class="inputCls" /></Field>
                <Field label="মোবাইল নম্বর"><input v-model="profileForm.phone" type="tel" inputmode="tel" :class="inputCls" /></Field>
                <p v-if="profileMsg.text" :class="['text-[13px]', profileMsg.error ? 'text-rose-600' : 'text-emerald-700']">{{ profileMsg.text }}</p>
                <button type="submit" :disabled="profileSaving" class="h-11 px-6 rounded-full bg-[#111111] text-white text-[14px] disabled:opacity-60">{{ profileSaving ? 'সংরক্ষণ হচ্ছে…' : 'তথ্য সংরক্ষণ করুন' }}</button>
              </form>

              <form @submit.prevent="savePassword" class="bg-white rounded-2xl border border-[#E5DED3] p-5 sm:p-6 space-y-4">
                <h3 class="text-[16px] font-semibold">পাসওয়ার্ড পরিবর্তন</h3>
                <Field label="নতুন পাসওয়ার্ড" hint="কমপক্ষে ৬ অক্ষর"><input v-model="pwForm.password" :type="showPw ? 'text' : 'password'" autocomplete="new-password" :class="inputCls" /></Field>
                <Field label="আবার লিখুন"><input v-model="pwForm.confirm" :type="showPw ? 'text' : 'password'" autocomplete="new-password" :class="inputCls" /></Field>
                <label class="flex items-center gap-2 text-[13px] text-[#69736E]"><input v-model="showPw" type="checkbox" class="accent-[#111111]" /> পাসওয়ার্ড দেখুন</label>
                <p v-if="pwMsg.text" :class="['text-[13px]', pwMsg.error ? 'text-rose-600' : 'text-emerald-700']">{{ pwMsg.text }}</p>
                <button type="submit" :disabled="pwSaving" class="h-11 px-6 rounded-full border border-[#111111] text-[14px] disabled:opacity-60">{{ pwSaving ? 'বদলানো হচ্ছে…' : 'পাসওয়ার্ড বদলান' }}</button>
              </form>
            </div>
          </Transition>
        </div>
      </div>
    </div>

    <!-- Return / exchange modal -->
    <BaseModal :is-open="!!returnOrder" :title="`রিটার্ন / এক্সচেঞ্জ · ${returnOrder?.order_number || ''}`" max-width="xl" @close="returnOrder = null">
      <form id="return-form" @submit.prevent="submitReturn" class="space-y-5 text-[14px] text-[#111111]">
        <div class="grid grid-cols-2 p-1 rounded-full bg-[#F7F5F0]">
          <button v-for="opt in [['return', 'রিটার্ন (টাকা ফেরত)'], ['exchange', 'এক্সচেঞ্জ']]" :key="opt[0]" type="button" @click="returnForm.type = opt[0]"
                  :class="['h-10 rounded-full text-[13.5px] transition-all', returnForm.type === opt[0] ? 'bg-white shadow-sm font-medium' : 'text-[#69736E]']">{{ opt[1] }}</button>
        </div>
        <div>
          <p class="font-medium mb-2">কোন পোশাক?</p>
          <div class="divide-y divide-[#F0ECE5] border border-[#E5DED3] rounded-xl">
            <label v-for="line in returnForm.lines" :key="line.item.id" class="flex items-center gap-3 p-3 cursor-pointer">
              <input v-model="line.selected" type="checkbox" class="w-4 h-4 accent-[#111111]" />
              <span class="flex-1 min-w-0 truncate">{{ line.item.product_name }} <span class="text-[#A89B8A]">{{ [line.item.size, line.item.color].filter(Boolean).join(' / ') }}</span></span>
              <select v-model.number="line.quantity" :disabled="!line.selected" class="h-8 px-2 rounded-lg border border-[#E5DED3] disabled:opacity-40" aria-label="পরিমাণ">
                <option v-for="n in line.item.quantity" :key="n" :value="n">{{ n }}</option>
              </select>
            </label>
          </div>
        </div>
        <Field label="কারণ">
          <select v-model="returnForm.condition" :class="inputCls">
            <option v-for="(label, key) in CONDITION_REASON" :key="key" :value="key">{{ label }}</option>
          </select>
        </Field>
        <Field v-if="returnForm.type === 'exchange'" label="বদলে কী চান?"><input v-model="returnForm.replacement_product" :class="inputCls" placeholder="যেমন: একই পোশাক, সাইজ L" /></Field>
        <Field label="বিস্তারিত (ঐচ্ছিক)"><textarea v-model="returnForm.customer_notes" rows="3" maxlength="2000" :class="[inputCls, 'h-auto py-2.5']"></textarea></Field>
        <p v-if="returnError" class="text-[13px] text-rose-600">{{ returnError }}</p>
      </form>
      <template #footer>
        <button type="button" @click="returnOrder = null" class="h-10 px-4 text-[13.5px] text-[#69736E]">বাতিল</button>
        <button type="submit" form="return-form" :disabled="returnSubmitting" class="h-10 px-5 rounded-full bg-[#111111] text-white text-[13.5px] disabled:opacity-60">{{ returnSubmitting ? 'পাঠানো হচ্ছে…' : 'অনুরোধ পাঠান' }}</button>
      </template>
    </BaseModal>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted, h } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import {
  Eye, EyeOff, LogOut, Heart, Package, Truck, RotateCcw, RefreshCw, ChevronDown, MapPin, Trash2, Plus, X,
  LayoutDashboard, ShoppingBag, Undo2, UserRound, Wallet, Clock, Zap, Headphones,
} from 'lucide-vue-next';
import { useAuthStore } from '../../stores/auth';
import { useCustomerStore } from '../../stores/customer';
import { useCartStore } from '../../stores/cart';
import { useWishlistStore } from '../../stores/wishlist';
import BaseModal from '../../components/common/BaseModal.vue';
import { taka, bnNumber } from '../../utils/bn';

const route = useRoute();
const router = useRouter();
const auth = useAuthStore();
const customer = useCustomerStore();
const cart = useCartStore();
const wishlist = useWishlistStore();

const user = computed(() => auth.customerUser);
const orders = computed(() => customer.orders || []);
const inputCls = 'w-full h-11 px-4 rounded-xl bg-[#F7F5F0] border border-[#E5DED3] text-[14px] text-[#111111] placeholder-[#B4AA9C] focus:outline-none focus:bg-white focus:border-[#111111] transition-colors';

// ---------------------------------------------------------------- helpers
const Field = (p, { slots }) => h('label', { class: 'block' }, [
  h('span', { class: 'block text-[13px] font-medium text-[#111111] mb-1.5' }, p.label),
  slots.default?.(),
  p.hint ? h('span', { class: 'block mt-1 text-[12px] text-[#A89B8A]' }, p.hint) : null,
]);
Field.props = ['label', 'hint'];

const itemImage = (i) => i.image || i.product?.featured_image || '';
const formatDate = (v) => (v ? new Date(v).toLocaleDateString('bn-BD', { day: 'numeric', month: 'long', year: 'numeric' }) : '');
const parseAddr = (a) => { if (typeof a === 'string') { try { return JSON.parse(a); } catch { return { address_line_1: a }; } } return a || {}; };
const formatAddress = (a) => {
  const x = parseAddr(a);
  return [x.address_line_1 || x.address, x.address_line_2, x.city, x.state, x.postal_code].filter(Boolean).join(', ') || '—';
};
const AddressText = (p) => h('div', { class: 'text-[14px] text-[#4B5450] leading-relaxed' }, [
  h('p', [p.addr.address_line_1, p.addr.address_line_2].filter(Boolean).join(', ')),
  h('p', [p.addr.city, p.addr.state, p.addr.postal_code].filter(Boolean).join(', ')),
  p.addr.phone ? h('p', { class: 'text-[#69736E]' }, p.addr.phone) : null,
]);
AddressText.props = ['addr'];

// ------------------------------------------------------- order status UI
const STEPS = ['অর্ডার', 'কনফার্ম', 'প্রস্তুত', 'পাঠানো', 'ডেলিভারি'];
const STEP_OF = { pending: 0, confirmed: 1, processing: 2, ready_to_ship: 2, shipped: 3, delivered: 4 };
const ORDER_STATUS = {
  pending: ['অপেক্ষমাণ', 'bg-amber-50 text-amber-800'],
  confirmed: ['কনফার্ম হয়েছে', 'bg-sky-50 text-sky-800'],
  processing: ['প্রস্তুত হচ্ছে', 'bg-sky-50 text-sky-800'],
  ready_to_ship: ['শিপিংয়ের জন্য প্রস্তুত', 'bg-sky-50 text-sky-800'],
  shipped: ['পাঠানো হয়েছে', 'bg-indigo-50 text-indigo-800'],
  delivered: ['ডেলিভারি সম্পন্ন', 'bg-emerald-50 text-emerald-800'],
  cancelled: ['বাতিল', 'bg-rose-50 text-rose-700'],
};
const PAYMENT = { paid: 'পরিশোধিত', unpaid: 'ডেলিভারিতে পরিশোধ', pending: 'অপেক্ষমাণ', failed: 'ব্যর্থ', refunded: 'ফেরত দেওয়া হয়েছে' };
const RETURN_STATUS = {
  pending: ['পর্যালোচনাধীন', 'bg-amber-50 text-amber-800'],
  approved: ['অনুমোদিত', 'bg-sky-50 text-sky-800'],
  rejected: ['প্রত্যাখ্যাত', 'bg-rose-50 text-rose-700'],
  completed: ['সম্পন্ন', 'bg-emerald-50 text-emerald-800'],
};

const Stepper = (p) => h('ol', { class: 'flex items-center' }, p.steps.map((label, i) => h('li', { class: ['flex items-center', i < p.steps.length - 1 ? 'flex-1' : ''] }, [
  h('div', { class: 'flex flex-col items-center gap-1.5' }, [
    h('span', { class: ['w-6 h-6 rounded-full flex items-center justify-center text-[11px] font-semibold transition-colors', i <= p.current ? 'bg-[#111111] text-white' : 'bg-[#EFEBE4] text-[#A89B8A]'] }, i < p.current ? '✓' : bnNumber(i + 1)),
    h('span', { class: ['text-[11px] whitespace-nowrap', i <= p.current ? 'text-[#111111]' : 'text-[#A89B8A]'] }, label),
  ]),
  i < p.steps.length - 1 ? h('span', { class: ['flex-1 h-0.5 mx-1.5 -mt-5 rounded-full', i < p.current ? 'bg-[#111111]' : 'bg-[#EFEBE4]'] }) : null,
])));
Stepper.props = ['steps', 'current'];

const OrderSummary = (p) => {
  const o = p.order;
  const st = ORDER_STATUS[o.order_status] || [o.order_status, 'bg-[#F2EEE7] text-[#111111]'];
  const items = o.items || [];
  return h('div', { class: 'space-y-4' }, [
    h('div', { class: 'flex items-start justify-between gap-3' }, [
      h('div', [
        h('p', { class: 'font-mono text-[14px] font-semibold text-[#111111]' }, `#${o.order_number}`),
        h('p', { class: 'text-[12.5px] text-[#69736E]' }, `${formatDate(o.created_at)} · ${bnNumber(items.reduce((s, i) => s + (i.quantity || 1), 0))}টি পণ্য`),
      ]),
      h('span', { class: ['shrink-0 px-2.5 h-7 inline-flex items-center rounded-full text-[12px] font-medium', st[1]] }, st[0]),
    ]),
    o.order_status !== 'cancelled' ? h(Stepper, { steps: STEPS, current: STEP_OF[o.order_status] ?? 0 }) : null,
    h('div', { class: 'flex items-center justify-between gap-3' }, [
      h('div', { class: 'flex -space-x-2' }, [
        ...items.slice(0, 4).map((i) => h('span', { class: 'w-10 h-12 rounded-lg overflow-hidden bg-[#EFEBE4] ring-2 ring-white' }, itemImage(i) ? [h('img', { src: itemImage(i), alt: '', class: 'w-full h-full object-cover' })] : [])),
        items.length > 4 ? h('span', { class: 'w-10 h-12 rounded-lg bg-[#F2EEE7] ring-2 ring-white flex items-center justify-center text-[12px] text-[#4B5450]' }, `+${bnNumber(items.length - 4)}`) : null,
      ]),
      h('p', { class: 'text-[16px] font-semibold tabular-nums text-[#111111]' }, taka(o.total)),
    ]),
  ]);
};
OrderSummary.props = ['order'];

// --------------------------------------------------------------- signed out
const BENEFITS = [
  { icon: Package, title: 'সব অর্ডার এক জায়গায়', text: 'কোন অর্ডার কোথায় আছে, এক নজরে দেখুন।' },
  { icon: Zap, title: 'দ্রুত চেকআউট', text: 'ঠিকানা সংরক্ষিত থাকে, প্রতিবার লিখতে হয় না।' },
  { icon: RotateCcw, title: 'সহজ রিটার্ন ও এক্সচেঞ্জ', text: 'এক ক্লিকে রিটার্ন বা সাইজ বদলের অনুরোধ।' },
  { icon: Heart, title: 'পছন্দের তালিকা', text: 'ভালো লাগা পোশাক সেভ করে রাখুন।' },
];
const authTab = ref(route.query.mode === 'register' ? 'register' : 'login');
const showPw = ref(false);
const authLoading = ref(false);
const authError = ref('');
const loginForm = reactive({ email: '', password: '' });
const registerForm = reactive({ name: '', email: '', phone: '', password: '', password_confirmation: '' });
const firstError = (err, fallback) => {
  const e = err.response?.data?.errors;
  return e ? Object.values(e).flat()[0] : (err.response?.data?.message || fallback);
};
const handleLogin = async () => {
  if (!loginForm.email || !loginForm.password) { authError.value = 'ইমেইল ও পাসওয়ার্ড লিখুন।'; return; }
  authLoading.value = true; authError.value = '';
  try { await auth.customerLogin(loginForm.email, loginForm.password); customer.fetchAll(); }
  catch (err) { authError.value = firstError(err, 'লগইন হয়নি। ইমেইল ও পাসওয়ার্ড আবার দেখুন।'); }
  finally { authLoading.value = false; }
};
const handleRegister = async () => {
  if (registerForm.password !== registerForm.password_confirmation) { authError.value = 'দুটি পাসওয়ার্ড মেলেনি।'; return; }
  authLoading.value = true; authError.value = '';
  try { await auth.customerRegister(registerForm); customer.fetchAll(); }
  catch (err) { authError.value = firstError(err, 'অ্যাকাউন্ট খোলা যায়নি। তথ্যগুলো আবার দেখুন।'); }
  finally { authLoading.value = false; }
};
const handleLogout = async () => {
  await auth.customerLogout();
  customer.reset();
  tab.value = 'overview';
};

// ------------------------------------------------------------- dashboard
const TABS_BASE = [
  { key: 'overview', label: 'ওভারভিউ', icon: LayoutDashboard },
  { key: 'orders', label: 'অর্ডার', icon: ShoppingBag },
  { key: 'returns', label: 'রিটার্ন', icon: Undo2 },
  { key: 'addresses', label: 'ঠিকানা', icon: MapPin },
  { key: 'profile', label: 'প্রোফাইল', icon: UserRound },
];
const TABS = computed(() => TABS_BASE.map((t) => ({
  ...t,
  count: { orders: orders.value.length, returns: customer.returns.length, addresses: customer.addresses.length }[t.key] || 0,
})));
const tab = ref(TABS_BASE.some((t) => t.key === route.query.tab) ? route.query.tab : 'overview');
watch(tab, (t) => router.replace({ query: { ...route.query, tab: t === 'overview' ? undefined : t } }));

const hour = new Date().getHours();
const greeting = hour < 12 ? 'শুভ সকাল' : hour < 17 ? 'শুভ দুপুর' : hour < 20 ? 'শুভ সন্ধ্যা' : 'শুভ রাত্রি';
const initial = computed(() => (user.value?.name || '?').trim().charAt(0).toUpperCase());

const isActive = (o) => !['delivered', 'cancelled'].includes(o.order_status);
const activeOrder = computed(() => orders.value.find(isActive));
const spotlight = computed(() => activeOrder.value || orders.value[0]);
const defaultAddress = computed(() => customer.addresses.find((a) => a.is_default) || customer.addresses[0]);

const stats = computed(() => [
  { label: 'মোট অর্ডার', value: bnNumber(orders.value.length), icon: ShoppingBag, tab: 'orders' },
  { label: 'চলমান অর্ডার', value: bnNumber(orders.value.filter(isActive).length), icon: Clock, tab: 'orders' },
  { label: 'মোট কেনাকাটা', value: taka(orders.value.filter((o) => o.order_status !== 'cancelled').reduce((s, o) => s + Number(o.total || 0), 0)), icon: Wallet },
  { label: 'উইশলিস্টে', value: bnNumber(wishlist.count || 0), icon: Heart },
]);

const QUICK = [
  { label: 'অর্ডার ট্র্যাক', hint: 'পার্সেল কোথায়', icon: Truck, to: '/track-order' },
  { label: 'উইশলিস্ট', hint: 'সেভ করা পোশাক', icon: Heart, to: '/wishlist' },
  { label: 'রিটার্ন', hint: 'অনুরোধের অবস্থা', icon: RotateCcw, tab: 'returns' },
  { label: 'সাহায্য', hint: 'আমাদের লিখুন', icon: Headphones, to: '/contact' },
];

// ---------------------------------------------------------------- orders
const ORDER_FILTERS = [
  { key: 'all', label: 'সব' },
  { key: 'active', label: 'চলমান' },
  { key: 'delivered', label: 'ডেলিভারি সম্পন্ন' },
  { key: 'cancelled', label: 'বাতিল' },
];
const orderFilter = ref('all');
const matches = (o, f) => (f === 'all' ? true : f === 'active' ? isActive(o) : o.order_status === f);
const filterCount = (f) => orders.value.filter((o) => matches(o, f)).length;
const filteredOrders = computed(() => orders.value.filter((o) => matches(o, orderFilter.value)));
const expanded = ref(null);

// "Buy again": add every item of a past order back into the bag.
const reordering = ref(null);
const reorder = async (order) => {
  reordering.value = order.id;
  try {
    for (const item of order.items || []) {
      if (!item.product_id) continue;
      try { await cart.addToCart(item.product_id, item.variant_id || null, item.quantity || 1, item.color || null, item.size || null); } catch { /* out of stock items are skipped */ }
    }
  } finally { reordering.value = null; }
};

// --------------------------------------------------------------- returns
const CONDITION_REASON = { size_issue: 'সাইজ মেলেনি', defective: 'ত্রুটিপূর্ণ পণ্য', wrong_item: 'ভুল পণ্য এসেছে', unopened: 'অব্যবহৃত / পছন্দ হয়নি' };
const returnOrder = ref(null);
const returnSubmitting = ref(false);
const returnError = ref('');
const returnForm = reactive({ type: 'return', condition: 'size_issue', replacement_product: '', customer_notes: '', lines: [] });
const openReturn = (order) => {
  returnError.value = '';
  Object.assign(returnForm, {
    type: 'return', condition: 'size_issue', replacement_product: '', customer_notes: '',
    lines: (order.items || []).map((item) => ({ item, selected: order.items.length === 1, quantity: 1 })),
  });
  returnOrder.value = order;
};
const submitReturn = async () => {
  const items = returnForm.lines.filter((l) => l.selected).map((l) => ({ order_item_id: l.item.id, quantity: l.quantity }));
  if (!items.length) { returnError.value = 'অন্তত একটি পোশাক বাছাই করুন।'; return; }
  returnSubmitting.value = true; returnError.value = '';
  try {
    await customer.submitReturn(returnOrder.value.order_number, {
      type: returnForm.type,
      reason: CONDITION_REASON[returnForm.condition],
      condition: returnForm.condition,
      replacement_product: returnForm.type === 'exchange' ? returnForm.replacement_product || undefined : undefined,
      customer_notes: returnForm.customer_notes || undefined,
      items,
    });
    returnOrder.value = null;
    tab.value = 'returns';
  } catch (err) {
    returnError.value = firstError(err, 'অনুরোধ পাঠানো যায়নি।');
  } finally { returnSubmitting.value = false; }
};

// ------------------------------------------------------------- addresses
const blankAddress = () => ({ first_name: '', last_name: '', address_line_1: '', address_line_2: '', city: '', postal_code: '', phone: '', country: 'Bangladesh', is_default: false });
const addressForm = reactive(blankAddress());
const addressFormOpen = ref(false);
const addressSaving = ref(false);
const addressMsg = reactive({ text: '', error: false });
const handleSaveAddress = async () => {
  addressSaving.value = true; addressMsg.text = '';
  try {
    await customer.saveAddress({ ...addressForm });
    Object.assign(addressForm, blankAddress());
    addressFormOpen.value = false;
  } catch (err) {
    Object.assign(addressMsg, { text: firstError(err, 'ঠিকানা সংরক্ষণ করা যায়নি।'), error: true });
  } finally { addressSaving.value = false; }
};
const removeAddress = async (addr) => {
  if (!window.confirm('এই ঠিকানাটি মুছে ফেলবেন?')) return;
  await customer.deleteAddress(addr.id);
};

// --------------------------------------------------------------- profile
const profileForm = reactive({ name: '', phone: '' });
const profileSaving = ref(false);
const profileMsg = reactive({ text: '', error: false });
watch(user, (u) => { profileForm.name = u?.name || ''; profileForm.phone = u?.phone || ''; }, { immediate: true });
const saveProfile = async () => {
  profileSaving.value = true; profileMsg.text = '';
  try {
    await customer.updateProfile({ name: profileForm.name, phone: profileForm.phone || null });
    Object.assign(profileMsg, { text: 'তথ্য আপডেট হয়েছে।', error: false });
  } catch (err) {
    Object.assign(profileMsg, { text: firstError(err, 'আপডেট করা যায়নি।'), error: true });
  } finally { profileSaving.value = false; }
};

const pwForm = reactive({ password: '', confirm: '' });
const pwSaving = ref(false);
const pwMsg = reactive({ text: '', error: false });
const savePassword = async () => {
  pwMsg.text = '';
  if (pwForm.password.length < 6) { Object.assign(pwMsg, { text: 'কমপক্ষে ৬ অক্ষরের পাসওয়ার্ড দিন।', error: true }); return; }
  if (pwForm.password !== pwForm.confirm) { Object.assign(pwMsg, { text: 'দুটি পাসওয়ার্ড মেলেনি।', error: true }); return; }
  pwSaving.value = true;
  try {
    await customer.updateProfile({ name: profileForm.name || user.value?.name, phone: profileForm.phone || null, password: pwForm.password });
    Object.assign(pwForm, { password: '', confirm: '' });
    Object.assign(pwMsg, { text: 'পাসওয়ার্ড বদলানো হয়েছে।', error: false });
  } catch (err) {
    Object.assign(pwMsg, { text: firstError(err, 'পাসওয়ার্ড বদলানো যায়নি।'), error: true });
  } finally { pwSaving.value = false; }
};

onMounted(() => {
  if (auth.isCustomerAuthenticated) {
    customer.fetchAll();
    wishlist.fetchWishlist?.();
  }
});
</script>
