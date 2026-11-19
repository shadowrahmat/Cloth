import { defineStore } from 'pinia';
import api from '../api';

export const useCartStore = defineStore('cart', {
    state: () => ({
        items: [],
        subtotal: 0.0,
        totalItems: 0,
        appliedCoupon: null,
        isDrawerOpen: false,
        loading: false,
    }),

    getters: {
        itemCount: (state) => state.totalItems,
        discountAmount: (state) => state.appliedCoupon?.discount_amount || 0.0,
        finalTotal: (state) => Math.max(0, state.subtotal - (state.appliedCoupon?.discount_amount || 0.0)),
    },

    actions: {
        async fetchCart() {
            try {
                const response = await api.get('/public/cart');
                const data = response.data.data;
                this.items = data.items || [];
                this.subtotal = data.subtotal || 0.0;
                this.totalItems = data.total_items || 0;
            } catch (err) {
                console.error('Failed to load cart', err);
            }
        },

        async addToCart(productId, variantId = null, quantity = 1, color = null, size = null) {
            this.loading = true;
            try {
                const response = await api.post('/public/cart/add', {
                    product_id: productId,
                    variant_id: variantId,
                    quantity,
                    color,
                    size,
                });
                const data = response.data.data;
                this.items = data.items || [];
                this.subtotal = data.subtotal || 0.0;
                this.totalItems = data.total_items || 0;
                this.isDrawerOpen = true; // Auto open mini cart drawer on add
                return response.data;
            } catch (err) {
                throw err;
            } finally {
                this.loading = false;
            }
        },

        /** Returns true on success, false on failure (callers may ignore it). */
        async updateQuantity(itemId, quantity) {
            try {
                const response = await api.put(`/public/cart/items/${itemId}`, { quantity });
                const data = response.data.data;
                this.items = data.items || [];
                this.subtotal = data.subtotal || 0.0;
                this.totalItems = data.total_items || 0;
                return true;
            } catch (err) {
                console.error('Failed to update cart quantity', err);
                return false;
            }
        },

        /** Returns true on success, false on failure (callers may ignore it). */
        async removeItem(itemId) {
            try {
                const response = await api.delete(`/public/cart/items/${itemId}`);
                const data = response.data.data;
                this.items = data.items || [];
                this.subtotal = data.subtotal || 0.0;
                this.totalItems = data.total_items || 0;
                return true;
            } catch (err) {
                console.error('Failed to remove item', err);
                return false;
            }
        },

        async applyCoupon(code) {
            try {
                const response = await api.post('/public/cart/coupon', { code });
                this.appliedCoupon = response.data.data;
                return response.data;
            } catch (err) {
                this.appliedCoupon = null;
                throw err;
            }
        },

        removeCoupon() {
            this.appliedCoupon = null;
        },

        openDrawer() {
            this.isDrawerOpen = true;
        },

        closeDrawer() {
            this.isDrawerOpen = false;
        },
    },
});
