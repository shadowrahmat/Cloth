import { defineStore } from 'pinia';
import api from '../api';

const STORAGE_KEY = 'admin_ui_prefs';

const loadPrefs = () => {
  try {
    return JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}');
  } catch {
    return {};
  }
};

export const useAdminUiStore = defineStore('adminUi', {
  state: () => {
    const prefs = loadPrefs();
    return {
      badges: {},
      badgesLoadedAt: 0,
      sidebarCollapsed: !!prefs.sidebarCollapsed,
      closedGroups: Array.isArray(prefs.closedGroups) ? prefs.closedGroups : [],
      paletteOpen: false,
    };
  },

  getters: {
    /** Things that need someone's attention, used by the bell and the dashboard. */
    alerts: (state) => {
      const b = state.badges;
      return [
        { key: 'pending_orders', count: b.pending_orders, label: 'টি নতুন অর্ডার কনফার্ম করা বাকি', to: '/admin/orders', tone: 'warn' },
        { key: 'pending_returns', count: b.pending_returns, label: 'টি রিটার্ন অনুরোধ অপেক্ষমাণ', to: '/admin/returns', tone: 'danger' },
        { key: 'low_stock', count: b.low_stock, label: 'টি পণ্যের স্টক ৫ বা তার কম', to: '/admin/inventory', tone: 'warn' },
        { key: 'new_inquiries', count: b.new_inquiries, label: 'টি নতুন মেসেজ পড়া হয়নি', to: '/admin/messages', tone: 'info' },
        { key: 'unapproved_reviews', count: b.unapproved_reviews, label: 'টি রিভিউ অনুমোদনের অপেক্ষায়', to: '/admin/reviews', tone: 'info' },
      ].filter((a) => a.count > 0);
    },
    alertCount() {
      return this.alerts.reduce((sum, a) => sum + a.count, 0);
    },
  },

  actions: {
    async fetchBadges({ force = false } = {}) {
      // Route changes call this often; skip if fetched in the last 15 seconds.
      if (!force && Date.now() - this.badgesLoadedAt < 15000) return;
      this.badgesLoadedAt = Date.now();
      try {
        const res = await api.get('/admin/dashboard/badges');
        this.badges = res.data?.data || {};
      } catch {
        // Badges are a convenience; never break the console over them.
      }
    },

    toggleSidebar() {
      this.sidebarCollapsed = !this.sidebarCollapsed;
      this.savePrefs();
    },

    toggleGroup(key) {
      this.closedGroups = this.closedGroups.includes(key)
        ? this.closedGroups.filter((k) => k !== key)
        : [...this.closedGroups, key];
      this.savePrefs();
    },

    savePrefs() {
      try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify({
          sidebarCollapsed: this.sidebarCollapsed,
          closedGroups: this.closedGroups,
        }));
      } catch {
        // Private mode / storage disabled: preferences just won't persist.
      }
    },
  },
});
