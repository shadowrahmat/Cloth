import { defineStore } from 'pinia';
import api from '../api';
import { useAuthStore } from './auth';

export const useCustomerStore = defineStore('customer', {
    state: () => ({
        orders: [],
        addresses: [],
        returns: [],
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

        async fetchReturns() {
            try {
                const response = await api.get('/customer/returns');
                this.returns = response.data.data || [];
            } catch (err) {
                console.error('Failed to load returns', err);
            }
        },

        async fetchAll() {
            await Promise.all([this.fetchOrders(), this.fetchAddresses(), this.fetchReturns()]);
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

        async updateProfile(profileData) {
            const response = await api.put('/customer/profile', profileData);
            const user = response.data.user;
            const authStore = useAuthStore();
            authStore.customerUser = { ...authStore.customerUser, name: user.name, phone: user.phone };
            localStorage.setItem('atelier_customer_user', JSON.stringify(authStore.customerUser));
            return response.data;
        },

        async submitReturn(orderNumber, payload) {
            const response = await api.post(`/customer/orders/${orderNumber}/returns`, payload);
            await this.fetchReturns();
            return response.data;
        },

        reset() {
            this.orders = [];
            this.addresses = [];
            this.returns = [];
        },
    },
});
