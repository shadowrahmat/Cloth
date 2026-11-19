import { defineStore } from 'pinia';
import api from '../api';

/**
 * Storefront content managed from Admin → সাইট কাস্টমাইজ:
 * branding, announcement bar, header menu, footer, contact, social, SEO.
 * The server embeds it in the page (window.__SITE__), so it is available
 * before the first render; refresh() re-reads it after an admin save.
 */
const embedded = (typeof window !== 'undefined' && window.__SITE__) || {};

export const useSiteStore = defineStore('site', {
  state: () => ({ config: embedded }),

  getters: {
    branding: (s) => s.config.branding || {},
    announcement: (s) => s.config.announcement || {},
    header: (s) => s.config.header || { menu: [] },
    footer: (s) => s.config.footer || { columns: [] },
    contact: (s) => s.config.contact || {},
    social: (s) => (s.config.social || []).filter((x) => x.url),
    seo: (s) => s.config.seo || {},
    pages: (s) => s.config.pages || { about: {}, blog: {}, contact: {} },
    freeShippingThreshold: (s) => Number(s.config.shop?.free_shipping_threshold ?? 2500),
    siteName: (s) => s.config.branding?.site_name || 'DRAWER CLOTHING',
  },

  actions: {
    async refresh() {
      try {
        const res = await api.get('/public/site');
        this.config = res.data.data || this.config;
      } catch {
        // keep the embedded copy
      }
    },
  },
});

/** "+880 1700-000000" -> "8801700000000" for tel:/wa.me links. */
export const phoneDigits = (p) => String(p || '').replace(/[^\d+]/g, '').replace(/^\+/, '');

/** Internal paths go through the router, everything else is a normal link. */
export const isExternal = (url) => /^(https?:)?\/\//.test(url || '') || /^(mailto|tel):/.test(url || '');
