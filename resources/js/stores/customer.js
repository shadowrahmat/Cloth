import { defineStore } from 'pinia';
import api from '../api';

export const useCustomerStore = defineStore('customer', {
    state: () => ({
        orders: [],
        addresses: [],
        loading: false,
    }),

    actions: {
        async fetchOrders() {
            this.loading = true;
            try {
                const response = await api.get('/customer/orders');
                this.orders = response.data.data || [];
            } catch (err) {
                console.error('Failed to load orders', err);
            } finally {
                this.loading = false;
            }
        },

        async fetchAddresses() {
            try {
                const response = await api.get('/customer/addresses');
                this.addresses = response.data.data || [];
            } catch (err) {
                console.error('Failed to load addresses', err);
            }
        },

        async saveAddress(addressData) {
            const response = await api.post('/customer/addresses', addressData);
            await this.fetchAddresses();
            return response.data;
        },

        async deleteAddress(id) {
            await api.delete(`/customer/addresses/${id}`);
            this.addresses = this.addresses.filter(a => a.id !== id);
        },
    },
});
