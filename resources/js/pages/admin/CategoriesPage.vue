<template>
  <div class="space-y-6">
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-[#E7EBE8]">
      <div>
        <div class="flex items-center gap-2">
          <span class="text-xs uppercase tracking-[0.2em] text-[#16A36A] font-bold">Catalog & Taxonomy</span>
          <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800">
            মোট {{ categories.length }}টি ক্যাটাগরি
          </span>
        </div>
        <h1 class="text-2xl font-bold tracking-tight text-[#171C1A] mt-1">ক্যাটাগরি ম্যানেজমেন্ট (Categories)</h1>
        <p class="text-xs text-[#69736E] mt-0.5">
          অনলাইন স্টোরের সকল ক্যাটাগরি, সাব-ক্যাটাগরি, প্রোডাক্ট কাউন্ট ও ভিজিবিলিটি সহজে নিয়ন্ত্রণ করুন।
        </p>
      </div>

      <div class="flex flex-wrap items-center gap-2.5">
        <a
          href="/collections"
          target="_blank"
          class="px-3.5 py-2 border border-[#E7EBE8] bg-white text-[#171C1A] text-xs font-semibold rounded-md hover:bg-[#F7F8FA] transition-colors shadow-2xs inline-flex items-center gap-1.5"
          title="ভিজিট শপ পেজ"
        >
          <svg class="w-3.5 h-3.5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
          </svg>
          <span>শপ পেজে দেখুন</span>
        </a>

        <button
          type="button"
          @click="fetchCategories"
          :disabled="loading"
          class="px-3 py-2 border border-[#E7EBE8] bg-white text-[#171C1A] text-xs font-semibold rounded-md hover:bg-[#F7F8FA] transition-colors shadow-2xs inline-flex items-center gap-1.5"
          title="রিফ্রেশ করুন"
        >
          <svg :class="['w-3.5 h-3.5', loading ? 'animate-spin text-emerald-600' : 'text-[#69736E]']" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          <span>রিফ্রেশ</span>
        </button>

        <button
          type="button"
          @click="openCreateModal"
          class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#14382A] text-white text-xs font-semibold rounded-md hover:bg-[#16A36A] transition-colors shadow-xs"
        >
          <span class="text-sm leading-none">+</span>
          <span>নতুন ক্যাটাগরি তৈরি করুন</span>
        </button>
      </div>
    </div>

    <!-- 4 Quick Stat Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
      <div class="bg-white p-4 rounded-lg border border-[#E7EBE8] shadow-2xs">
        <span class="text-[11px] font-medium text-[#69736E] block">মোট ক্যাটাগরি</span>
        <div class="flex items-baseline justify-between mt-1">
          <span class="text-2xl font-bold text-[#171C1A]">{{ totalCount }}</span>
          <span class="text-xs text-emerald-700 font-medium">Total</span>
        </div>
      </div>

      <div class="bg-white p-4 rounded-lg border border-[#E7EBE8] shadow-2xs">
        <span class="text-[11px] font-medium text-[#69736E] block">সক্রিয় ক্যাটাগরি</span>
        <div class="flex items-baseline justify-between mt-1">
          <span class="text-2xl font-bold text-emerald-600">{{ activeCount }}</span>
          <span class="text-[11px] text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full">সক্রিয়</span>
        </div>
      </div>

      <div class="bg-white p-4 rounded-lg border border-[#E7EBE8] shadow-2xs">
        <span class="text-[11px] font-medium text-[#69736E] block">রুট / মূল ক্যাটাগরি</span>
        <div class="flex items-baseline justify-between mt-1">
          <span class="text-2xl font-bold text-[#171C1A]">{{ rootCount }}</span>
          <span class="text-xs text-[#69736E]">Top-level</span>
        </div>
      </div>

      <div class="bg-white p-4 rounded-lg border border-[#E7EBE8] shadow-2xs">
        <span class="text-[11px] font-medium text-[#69736E] block">যুক্ত পণ্য সংখ্যা</span>
        <div class="flex items-baseline justify-between mt-1">
          <span class="text-2xl font-bold text-[#14382A]">{{ totalProductsCount }}</span>
          <span class="text-xs text-[#69736E]">পণ্য মোট</span>
        </div>
      </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="bg-white p-4 rounded-lg border border-[#E7EBE8] shadow-2xs flex flex-col md:flex-row gap-4 items-center justify-between">
      <!-- Search -->
      <div class="relative w-full md:w-80">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="ক্যাটাগরির নাম বা স্লাগ খুঁজুন..."
          class="w-full bg-[#F7F8FA] border border-[#E7EBE8] rounded-md pl-9 pr-3 py-2 text-xs text-[#171C1A] placeholder-[#69736E] focus:outline-none focus:ring-1 focus:ring-[#16A36A] focus:border-[#16A36A] transition-all"
        />
        <svg class="w-4 h-4 text-[#69736E] absolute left-3 top-2.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <button
          v-if="searchQuery"
          @click="searchQuery = ''"
          class="absolute right-2.5 top-2.5 text-[#69736E] hover:text-[#171C1A] text-xs"
        >
          ✕
        </button>
      </div>

      <!-- Filter Tabs & View Mode -->
      <div class="flex flex-wrap items-center gap-2 w-full md:w-auto justify-between md:justify-end">
        <div class="inline-flex rounded-md shadow-2xs border border-[#E7EBE8] p-0.5 bg-[#F7F8FA] text-xs">
          <button
            type="button"
            @click="activeFilter = 'all'"
            :class="['px-3 py-1.5 rounded-xs font-medium transition-colors', activeFilter === 'all' ? 'bg-white text-[#171C1A] shadow-xs' : 'text-[#69736E] hover:text-[#171C1A]']"
          >
            সব ({{ totalCount }})
          </button>
          <button
            type="button"
            @click="activeFilter = 'root'"
            :class="['px-3 py-1.5 rounded-xs font-medium transition-colors', activeFilter === 'root' ? 'bg-white text-[#171C1A] shadow-xs' : 'text-[#69736E] hover:text-[#171C1A]']"
          >
            রুট ({{ rootCount }})
          </button>
          <button
            type="button"
            @click="activeFilter = 'featured'"
            :class="['px-3 py-1.5 rounded-xs font-medium transition-colors', activeFilter === 'featured' ? 'bg-white text-[#171C1A] shadow-xs' : 'text-[#69736E] hover:text-[#171C1A]']"
          >
            ফিচার্ড ({{ featuredCount }})
          </button>
          <button
            type="button"
            @click="activeFilter = 'active'"
            :class="['px-3 py-1.5 rounded-xs font-medium transition-colors', activeFilter === 'active' ? 'bg-white text-[#171C1A] shadow-xs' : 'text-[#69736E] hover:text-[#171C1A]']"
          >
            সক্রিয় ({{ activeCount }})
          </button>
        </div>

        <!-- View Switcher (Table vs Grid) -->
        <div class="inline-flex rounded-md border border-[#E7EBE8] bg-[#F7F8FA] p-0.5 text-xs">
          <button
            type="button"
            @click="viewMode = 'table'"
            :class="['p-1.5 rounded-xs transition-colors', viewMode === 'table' ? 'bg-white text-[#171C1A] shadow-xs' : 'text-[#69736E] hover:text-[#171C1A]']"
            title="টেবিল ভিউ"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
            </svg>
          </button>
          <button
            type="button"
            @click="viewMode = 'grid'"
            :class="['p-1.5 rounded-xs transition-colors', viewMode === 'grid' ? 'bg-white text-[#171C1A] shadow-xs' : 'text-[#69736E] hover:text-[#171C1A]']"
            title="গ্রিড কার্ড ভিউ"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="bg-white rounded-lg border border-[#E7EBE8] p-12 text-center shadow-xs">
      <div class="inline-block animate-spin text-emerald-600 mb-3">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
      </div>
      <p class="text-xs font-semibold text-[#171C1A]">ক্যাটাগরি সমূহ লোড হচ্ছে...</p>
      <p class="text-[11px] text-[#69736E] mt-0.5">অনুগ্রহ করে একটু অপেক্ষা করুন।</p>
    </div>

    <!-- TABLE VIEW -->
    <div v-else-if="viewMode === 'table'" class="bg-white rounded-lg border border-[#E7EBE8] overflow-hidden shadow-xs">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead>
            <tr class="bg-[#F7F8FA] text-[#69736E] border-b border-[#E7EBE8]">
              <th class="py-3.5 px-4 font-semibold">ক্যাটাগরি (Category)</th>
              <th class="py-3.5 px-4 font-semibold">প্যারেন্ট / স্তর</th>
              <th class="py-3.5 px-4 font-semibold text-center">যুক্ত পণ্য</th>
              <th class="py-3.5 px-4 font-semibold text-center">ডিসপ্লে ক্রম</th>
              <th class="py-3.5 px-4 font-semibold text-center">ফিচার্ড</th>
              <th class="py-3.5 px-4 font-semibold text-center">স্ট্যাটাস</th>
              <th class="py-3.5 px-4 font-semibold text-right">অ্যাকশন</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#E7EBE8]">
            <tr
              v-for="cat in filteredCategories"
              :key="cat.id"
              class="hover:bg-[#F7F8FA] transition-colors"
            >
              <!-- Category info -->
              <td class="py-3 px-4 font-medium text-[#171C1A]">
                <div class="flex items-center gap-3">
                  <div class="w-11 h-11 rounded-md bg-[#F7F8FA] border border-[#E7EBE8] overflow-hidden shrink-0 flex items-center justify-center">
                    <img
                      v-if="cat.image"
                      :src="cat.image"
                      :alt="cat.name"
                      class="w-full h-full object-cover"
                    />
                    <span v-else class="text-base text-emerald-800/60 font-serif font-bold">
                      {{ cat.name.charAt(0).toUpperCase() }}
                    </span>
                  </div>
                  <div class="min-w-0">
                    <div class="flex items-center gap-1.5">
                      <span class="font-bold text-sm text-[#171C1A] truncate">{{ cat.name }}</span>
                      <span v-if="cat.is_featured" class="text-amber-500 font-bold text-xs" title="ফিচার্ড ক্যাটাগরি">★</span>
                    </div>
                    <div class="flex items-center gap-2 mt-0.5">
                      <span class="text-[11px] font-mono text-[#69736E] bg-slate-100 px-1.5 py-0.2 rounded">
                        /{{ cat.slug }}
                      </span>
                      <a
                        :href="'/shop?category=' + cat.slug"
                        target="_blank"
                        class="text-[10px] text-emerald-700 hover:text-emerald-900 inline-flex items-center gap-0.5 hover:underline"
                        title="স্টোরে দেখুন"
                      >
                        <span>ভিউ</span>
                        <span>↗</span>
                      </a>
                    </div>
                  </div>
                </div>
              </td>

              <!-- Parent / Hierarchy -->
              <td class="py-3 px-4 text-[#69736E]">
                <span v-if="cat.parent" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-800 border border-emerald-200">
                  ↳ {{ cat.parent.name }}
                </span>
                <span v-else class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 text-slate-700">
                  👑 মূল (Root)
                </span>
              </td>

              <!-- Products Count -->
              <td class="py-3 px-4 text-center">
                <router-link
                  :to="'/admin/products?category_id=' + cat.id"
                  class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200 hover:bg-emerald-100 transition-colors"
                  :title="'Filter products in ' + cat.name"
                >
                  <span>{{ cat.products_count ?? 0 }} টি পণ্য</span>
                  <span class="text-[10px] text-emerald-600">→</span>
                </router-link>
              </td>

              <!-- Display Order -->
              <td class="py-3 px-4 text-center text-[#69736E] font-mono">
                {{ cat.display_order ?? 0 }}
              </td>

              <!-- Featured Toggle -->
              <td class="py-3 px-4 text-center">
                <button
                  type="button"
                  @click="toggleFeatured(cat)"
                  class="p-1 rounded hover:bg-slate-100 transition-colors"
                  :title="cat.is_featured ? 'ফিচার্ড বন্ধ করুন' : 'ফিচার্ড চালু করুন'"
                >
                  <span v-if="cat.is_featured" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                    ★ ফিচার্ড
                  </span>
                  <span v-else class="text-slate-400 hover:text-amber-600 text-xs font-medium">
                    ☆ সাধারণ
                  </span>
                </button>
              </td>

              <!-- Status Toggle -->
              <td class="py-3 px-4 text-center">
                <button
                  type="button"
                  @click="toggleActive(cat)"
                  class="transition-transform active:scale-95"
                  :title="cat.is_active ? 'নিষ্ক্রিয় করতে ক্লিক করুন' : 'সক্রিয় করতে ক্লিক করুন'"
                >
                  <StatusBadge :status="cat.is_active ? 'active' : 'inactive'" />
                </button>
              </td>

              <!-- Actions -->
              <td class="py-3 px-4 text-right space-x-2 whitespace-nowrap">
                <a
                  :href="'/shop?category=' + cat.slug"
                  target="_blank"
                  class="px-2 py-1 text-xs text-emerald-700 hover:bg-emerald-50 rounded-md transition-colors font-medium"
                >
                  স্টোরে দেখুন
                </a>
                <button
                  type="button"
                  @click="openEditModal(cat)"
                  class="px-2.5 py-1 text-xs text-[#14382A] font-semibold hover:bg-slate-100 rounded-md transition-colors"
                >
                  এডিট
                </button>
                <button
                  type="button"
                  @click="deleteCategory(cat.id)"
                  class="px-2.5 py-1 text-xs text-rose-600 font-semibold hover:bg-rose-50 rounded-md transition-colors"
                >
                  ডিলিট
                </button>
              </td>
            </tr>

            <!-- Empty rows in filter -->
            <tr v-if="!filteredCategories.length">
              <td colspan="7" class="py-12 text-center text-[#69736E]">
                <div class="max-w-xs mx-auto space-y-2">
                  <span class="text-3xl block">🔍</span>
                  <p class="font-medium text-sm text-[#171C1A]">কোনো ক্যাটাগরি পাওয়া যায়নি</p>
                  <p class="text-xs">আপনার সার্চ কুয়েরি অথবা ফিল্টার পরিবর্তন করে পুনরায় চেষ্টা করুন।</p>
                  <button
                    type="button"
                    @click="searchQuery = ''; activeFilter = 'all'"
                    class="mt-2 px-3 py-1.5 bg-[#14382A] text-white text-xs font-semibold rounded-md"
                  >
                    ফিল্টার রিসেট করুন
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- GRID CARD VIEW -->
    <div v-else-if="viewMode === 'grid'" class="space-y-4">
      <div v-if="filteredCategories.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        <div
          v-for="cat in filteredCategories"
          :key="cat.id"
          class="bg-white rounded-lg border border-[#E7EBE8] overflow-hidden shadow-2xs hover:shadow-md transition-all flex flex-col group"
        >
          <!-- Card Image Header -->
          <div class="h-32 bg-[#F7F8FA] relative overflow-hidden flex items-center justify-center border-b border-[#E7EBE8]">
            <img
              v-if="cat.image"
              :src="cat.image"
              :alt="cat.name"
              class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
            />
            <div v-else class="text-center p-4">
              <span class="text-3xl font-serif font-bold text-emerald-800/40">
                {{ cat.name.charAt(0).toUpperCase() }}
              </span>
            </div>

            <!-- Badges overlay -->
            <div class="absolute top-2 left-2 flex flex-col gap-1">
              <span
                v-if="cat.is_featured"
                class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-400 text-amber-950 shadow-xs"
              >
                ★ Featured
              </span>
              <span
                v-if="!cat.parent_id"
                class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-white/90 text-[#14382A] backdrop-blur-xs border border-slate-200"
              >
                Root Category
              </span>
            </div>

            <div class="absolute top-2 right-2">
              <StatusBadge :status="cat.is_active ? 'active' : 'inactive'" />
            </div>
          </div>

          <!-- Card Body -->
          <div class="p-4 flex-1 flex flex-col justify-between space-y-3">
            <div>
              <div class="flex items-start justify-between gap-2">
                <h3 class="font-bold text-sm text-[#171C1A] leading-snug">{{ cat.name }}</h3>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-800 shrink-0">
                  {{ cat.products_count ?? 0 }} items
                </span>
              </div>
              <p class="text-[11px] font-mono text-[#69736E] mt-0.5">/{{ cat.slug }}</p>
              <p v-if="cat.description" class="text-xs text-[#69736E] mt-2 line-clamp-2">
                {{ cat.description }}
              </p>
            </div>

            <!-- Card Footer Actions -->
            <div class="pt-3 border-t border-[#E7EBE8] flex items-center justify-between">
              <a
                :href="'/shop?category=' + cat.slug"
                target="_blank"
                class="text-[11px] text-emerald-700 hover:text-emerald-900 font-semibold"
              >
                শপে দেখুন ↗
              </a>

              <div class="flex items-center gap-1.5">
                <button
                  type="button"
                  @click="openEditModal(cat)"
                  class="px-2.5 py-1 text-xs text-[#14382A] font-semibold hover:bg-slate-100 rounded-md transition-colors"
                >
                  এডিট
                </button>
                <button
                  type="button"
                  @click="deleteCategory(cat.id)"
                  class="px-2.5 py-1 text-xs text-rose-600 font-semibold hover:bg-rose-50 rounded-md transition-colors"
                >
                  ডিলিট
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty state grid -->
      <div v-else class="bg-white rounded-lg border border-[#E7EBE8] p-12 text-center shadow-xs">
        <span class="text-3xl block mb-2">🔍</span>
        <p class="font-semibold text-sm text-[#171C1A]">কোনো ক্যাটাগরি পাওয়া যায়নি</p>
        <button
          type="button"
          @click="searchQuery = ''; activeFilter = 'all'"
          class="mt-3 px-3 py-1.5 bg-[#14382A] text-white text-xs font-semibold rounded-md"
        >
          ফিল্টার রিসেট করুন
        </button>
      </div>
    </div>

    <!-- Category Create / Edit Modal -->
    <BaseModal
      :is-open="isModalOpen"
      :title="editingId ? 'ক্যাটাগরি এডিট করুন (Edit Category)' : 'নতুন ক্যাটাগরি তৈরি করুন (Create Category)'"
      size="lg"
      @close="isModalOpen = false"
    >
      <form @submit.prevent="saveCategory" class="space-y-4 text-xs">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-semibold text-[#171C1A] mb-1">ক্যাটাগরির নাম (Category Name) *</label>
            <input
              v-model="form.name"
              type="text"
              required
              placeholder="e.g. Panjabi, T-shirt, Jeans..."
              @input="onNameInput"
              class="w-full bg-white border border-[#E7EBE8] rounded-md px-3 py-2 text-[#171C1A] focus:outline-none focus:ring-1 focus:ring-[#16A36A]"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-[#171C1A] mb-1">স্লাগ URL (Slug)</label>
            <input
              v-model="form.slug"
              type="text"
              placeholder="auto-generated from name"
              class="w-full bg-white border border-[#E7EBE8] rounded-md px-3 py-2 text-[#171C1A] focus:outline-none focus:ring-1 focus:ring-[#16A36A]"
            />
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-semibold text-[#171C1A] mb-1">প্যারেন্ট ক্যাটাগরি (Parent Category)</label>
            <select
              v-model="form.parent_id"
              class="w-full bg-white border border-[#E7EBE8] rounded-md px-3 py-2 text-[#171C1A] focus:outline-none focus:ring-1 focus:ring-[#16A36A]"
            >
              <option :value="null">কোনোটি নয় (মূল / Root Category)</option>
              <option v-for="c in rootCategories" :key="c.id" :value="c.id">
                {{ c.name }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-semibold text-[#171C1A] mb-1">ডিসপ্লে ক্রম (Display Order)</label>
            <input
              v-model.number="form.display_order"
              type="number"
              class="w-full bg-white border border-[#E7EBE8] rounded-md px-3 py-2 text-[#171C1A] focus:outline-none focus:ring-1 focus:ring-[#16A36A]"
            />
          </div>
        </div>

        <div>
          <label class="block text-xs font-semibold text-[#171C1A] mb-1">ক্যাটাগরি ছবি (Image / Banner)</label>
          <MediaPicker v-model="form.image" />
        </div>

        <div>
          <label class="block text-xs font-semibold text-[#171C1A] mb-1">বিবরণ (Description)</label>
          <textarea
            v-model="form.description"
            rows="2"
            placeholder="ক্যাটাগরির সংক্ষিপ্ত বিবরণ..."
            class="w-full bg-white border border-[#E7EBE8] rounded-md p-3 text-[#171C1A] focus:outline-none focus:ring-1 focus:ring-[#16A36A]"
          ></textarea>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-semibold text-[#171C1A] mb-1">SEO Title</label>
            <input
              v-model="form.seo_title"
              type="text"
              placeholder="SEO Friendly Title"
              class="w-full bg-white border border-[#E7EBE8] rounded-md px-3 py-2 text-[#171C1A] focus:outline-none focus:ring-1 focus:ring-[#16A36A]"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-[#171C1A] mb-1">SEO Description</label>
            <input
              v-model="form.seo_description"
              type="text"
              placeholder="Meta Description for Google"
              class="w-full bg-white border border-[#E7EBE8] rounded-md px-3 py-2 text-[#171C1A] focus:outline-none focus:ring-1 focus:ring-[#16A36A]"
            />
          </div>
        </div>

        <div class="flex items-center gap-6 pt-2">
          <label class="flex items-center gap-2 text-xs font-medium text-[#171C1A] cursor-pointer">
            <input v-model="form.is_active" type="checkbox" class="rounded border-slate-300 text-[#16A36A] focus:ring-[#16A36A]" />
            <span>সক্রিয় ক্যাটাগরি (Active Status)</span>
          </label>
          <label class="flex items-center gap-2 text-xs font-medium text-[#171C1A] cursor-pointer">
            <input v-model="form.is_featured" type="checkbox" class="rounded border-slate-300 text-[#16A36A] focus:ring-[#16A36A]" />
            <span>হোমপেজে ফিচার্ড রাখুন (Featured on Storefront)</span>
          </label>
        </div>

        <div class="pt-4 flex justify-end gap-3 border-t border-[#E7EBE8]">
          <button
            type="button"
            @click="isModalOpen = false"
            class="px-4 py-2 border border-[#E7EBE8] text-xs font-semibold text-[#171C1A] rounded-md hover:bg-slate-50 transition-colors"
          >
            বাতিল
          </button>
          <button
            type="submit"
            :disabled="saving"
            class="px-5 py-2 bg-[#14382A] hover:bg-[#16A36A] text-white text-xs font-semibold rounded-md shadow-xs transition-colors disabled:opacity-50 inline-flex items-center gap-1.5"
          >
            <span v-if="saving" class="animate-spin text-white">⟳</span>
            <span>{{ saving ? 'সংরক্ষণ হচ্ছে...' : (editingId ? 'আপডেট করুন' : 'ক্যাটাগরি তৈরি করুন') }}</span>
          </button>
        </div>
      </form>
    </BaseModal>
  </div>
</template>

<script setup>
import { useAdminDeepLink } from '../../composables/useAdminDeepLink';
import { ref, computed, onMounted } from 'vue';
import BaseModal from '../../components/admin/BaseModal.vue';
import StatusBadge from '../../components/admin/StatusBadge.vue';
import MediaPicker from '../../components/admin/MediaPicker.vue';
import { useToastStore } from '../../stores/toast';
import api from '../../api';

const toast = useToastStore();
const categories = ref([]);
const loading = ref(false);
const saving = ref(false);
const isModalOpen = ref(false);
const editingId = ref(null);

const searchQuery = ref('');
const activeFilter = ref('all');
const viewMode = ref('table');

const form = ref({
  name: '',
  slug: '',
  parent_id: null,
  image: '',
  description: '',
  seo_title: '',
  seo_description: '',
  display_order: 0,
  is_active: true,
  is_featured: false,
});

const onNameInput = () => {
  if (!editingId.value) {
    form.value.slug = form.value.name
      .toLowerCase()
      .trim()
      .replace(/[^\w\s-]/g, '')
      .replace(/[\s_-]+/g, '-')
      .replace(/^-+|-+$/g, '');
  }
};

const totalCount = computed(() => categories.value.length);
const activeCount = computed(() => categories.value.filter(c => c.is_active).length);
const rootCount = computed(() => categories.value.filter(c => !c.parent_id).length);
const featuredCount = computed(() => categories.value.filter(c => c.is_featured).length);
const totalProductsCount = computed(() =>
  categories.value.reduce((acc, c) => acc + (c.products_count || 0), 0)
);

const rootCategories = computed(() => {
  return categories.value.filter(c => c.id !== editingId.value && !c.parent_id);
});

const filteredCategories = computed(() => {
  let list = categories.value;

  if (activeFilter.value === 'active') {
    list = list.filter(c => c.is_active);
  } else if (activeFilter.value === 'inactive') {
    list = list.filter(c => !c.is_active);
  } else if (activeFilter.value === 'root') {
    list = list.filter(c => !c.parent_id);
  } else if (activeFilter.value === 'featured') {
    list = list.filter(c => c.is_featured);
  }

  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase().trim();
    list = list.filter(c =>
      (c.name && c.name.toLowerCase().includes(q)) ||
      (c.slug && c.slug.toLowerCase().includes(q)) ||
      (c.description && c.description.toLowerCase().includes(q)) ||
      (c.parent?.name && c.parent.name.toLowerCase().includes(q))
    );
  }

  return list;
});

const fetchCategories = async () => {
  loading.value = true;
  try {
    const res = await api.get('/admin/resources/categories?per_page=-1');
    const raw = res.data?.data?.data || res.data?.data || res.data || [];
    categories.value = Array.isArray(raw) ? raw : [];
  } catch (err) {
    console.error('Failed to load categories', err);
    toast.error('ক্যাটাগরি তালিকা লোড করতে ব্যর্থ হয়েছে');
  } finally {
    loading.value = false;
  }
};

const toggleFeatured = async (cat) => {
  try {
    const updated = !cat.is_featured;
    await api.put(`/admin/resources/categories/${cat.id}`, { ...cat, is_featured: updated });
    cat.is_featured = updated;
    toast.success(`${cat.name} ${updated ? 'ফিচার্ড হিসেবে চিহ্নিত করা হয়েছে' : 'ফিচার্ড থেকে সরানো হয়েছে'}`);
  } catch (err) {
    toast.error('ফিচার্ড স্ট্যাটাস আপডেট করা যায়নি');
  }
};

const toggleActive = async (cat) => {
  try {
    const updated = !cat.is_active;
    await api.put(`/admin/resources/categories/${cat.id}`, { ...cat, is_active: updated });
    cat.is_active = updated;
    toast.success(`${cat.name} ${updated ? 'সক্রিয় করা হয়েছে' : 'নিষ্ক্রিয় করা হয়েছে'}`);
  } catch (err) {
    toast.error('স্ট্যাটাস আপডেট করা যায়নি');
  }
};

const openCreateModal = () => {
  editingId.value = null;
  form.value = {
    name: '',
    slug: '',
    parent_id: null,
    image: '',
    description: '',
    seo_title: '',
    seo_description: '',
    display_order: categories.value.length + 1,
    is_active: true,
    is_featured: false,
  };
  isModalOpen.value = true;
};

const openEditModal = (cat) => {
  editingId.value = cat.id;
  form.value = {
    ...cat,
    parent_id: cat.parent_id || null,
  };
  isModalOpen.value = true;
};

const saveCategory = async () => {
  saving.value = true;
  try {
    if (editingId.value) {
      await api.put(`/admin/resources/categories/${editingId.value}`, form.value);
      toast.success('ক্যাটাগরি সফলভাবে আপডেট করা হয়েছে');
    } else {
      await api.post('/admin/resources/categories', form.value);
      toast.success('নতুন ক্যাটাগরি সফলভাবে তৈরি করা হয়েছে');
    }
    isModalOpen.value = false;
    await fetchCategories();
  } catch (err) {
    toast.error(err.response?.data?.message || 'ক্যাটাগরি সংরক্ষণ ব্যর্থ হয়েছে');
  } finally {
    saving.value = false;
  }
};

const deleteCategory = async (id) => {
  if (!confirm('আপনি কি নিশ্চিত যে এই ক্যাটাগরি মুছে ফেলতে চান?')) return;
  try {
    await api.delete(`/admin/resources/categories/${id}`);
    toast.success('ক্যাটাগরি মুছে ফেলা হয়েছে');
    await fetchCategories();
  } catch (err) {
    toast.error(err.response?.data?.message || 'ক্যাটাগরি মুছতে ব্যর্থ হয়েছে');
  }
};

onMounted(() => {
  fetchCategories();
});

// Open from links like ?search=... or ?new=1 (sidebar, Ctrl+K, dashboard)
useAdminDeepLink({ search: searchQuery, onNew: openCreateModal });
</script>
