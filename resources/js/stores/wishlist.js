import { defineStore } from 'pinia';
import api from '../api';

export const useWishlistStore = defineStore('wishlist', {
    state: () => ({
        items: [],
        loading: false,
    }),

    getters: {
        wishlistIds: (state) => state.items.map(p => p.id),
        isInWishlist: (state) => (productId) => state.items.some(p => p.id === productId),
        count: (state) => state.items.length,
    },

    actions: {
        async fetchWishlist() {
            try {
                const response = await api.get('/public/wishlist');
                this.items = response.data.data || [];
            } catch (err) {
                console.error('Failed to load wishlist', err);
            }
        },

        async toggleWishlist(product) {
            try {
                const response = await api.post('/public/wishlist/toggle', { product_id: product.id });
                if (response.data.in_wishlist) {
                    if (!this.items.some(p => p.id === product.id)) {
                        this.items.push(product);
                    }
                } else {
                    this.items = this.items.filter(p => p.id !== product.id);
                }
                return response.data;
            } catch (err) {
                console.error('Failed to toggle wishlist', err);
            }
        },
    },
});
