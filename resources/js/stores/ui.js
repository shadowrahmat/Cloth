import { defineStore } from 'pinia';

/**
 * Storefront overlays that several components open: the search drawer and the
 * mobile menu are triggered from the header AND the mobile bottom tab bar.
 */
export const useUiStore = defineStore('ui', {
  state: () => ({
    searchOpen: false,
    mobileMenuOpen: false,
  }),
  actions: {
    openSearch() { this.mobileMenuOpen = false; this.searchOpen = true; },
    closeAll() { this.searchOpen = false; this.mobileMenuOpen = false; },
  },
});
