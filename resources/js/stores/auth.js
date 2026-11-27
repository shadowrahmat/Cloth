import { defineStore } from 'pinia';
import api from '../api';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        adminToken: localStorage.getItem('atelier_admin_token') || null,
        adminUser: JSON.parse(localStorage.getItem('atelier_admin_user') || 'null'),
        customerToken: localStorage.getItem('atelier_customer_token') || null,
        customerUser: JSON.parse(localStorage.getItem('atelier_customer_user') || 'null'),
        loading: false,
        error: null,
    }),

    getters: {
        isAdminAuthenticated: (state) => !!state.adminToken,
        isCustomerAuthenticated: (state) => !!state.customerToken,
        userRole: (state) => state.adminUser?.role || state.customerUser?.role || 'guest',
        hasPermission: (state) => (permission) => {
            if (state.adminUser?.role === 'admin') return true;
            return state.adminUser?.permissions?.includes(permission) || false;
        },
    },

    actions: {
        async adminLogin(email, password) {
            this.loading = true;
            this.error = null;
            try {
                const response = await api.post('/admin/login', { email, password });
                const { token, user } = response.data;
                this.adminToken = token;
                this.adminUser = user;
                localStorage.setItem('atelier_admin_token', token);
                localStorage.setItem('atelier_admin_user', JSON.stringify(user));
                return response.data;
            } catch (err) {
                this.error = err.response?.data?.message || 'Login failed';
                throw err;
            } finally {
                this.loading = false;
            }
        },

        /**
         * Reload the signed-in staff member's permissions. The login response is a snapshot,
         * so without this a role edited later would not apply until the next login.
         */
        async refreshAdmin() {
            if (!this.adminToken) return;
            try {
                const response = await api.get('/admin/me');
                this.adminUser = response.data.user;
                localStorage.setItem('atelier_admin_user', JSON.stringify(this.adminUser));
            } catch (e) {
                // 401/403 is handled by the api interceptor; keep the cached user on a network blip
            }
        },

        async adminLogout() {
            try {
                if (this.adminToken) {
                    await api.post('/admin/logout');
                }
            } catch (e) {
                // ignore
            } finally {
                this.adminToken = null;
                this.adminUser = null;
                localStorage.removeItem('atelier_admin_token');
                localStorage.removeItem('atelier_admin_user');
            }
        },

        async customerLogin(email, password) {
            this.loading = true;
            this.error = null;
            try {
                const response = await api.post('/customer/login', { email, password });
                const { token, user } = response.data;
                this.customerToken = token;
                this.customerUser = user;
                localStorage.setItem('atelier_customer_token', token);
                localStorage.setItem('atelier_customer_user', JSON.stringify(user));
                return response.data;
            } catch (err) {
                this.error = err.response?.data?.message || 'Login failed';
                throw err;
            } finally {
                this.loading = false;
            }
        },

        async customerRegister(formData) {
            this.loading = true;
            this.error = null;
            try {
                const response = await api.post('/customer/register', formData);
                const { token, user } = response.data;
                this.customerToken = token;
                this.customerUser = user;
                localStorage.setItem('atelier_customer_token', token);
                localStorage.setItem('atelier_customer_user', JSON.stringify(user));
                return response.data;
            } catch (err) {
                this.error = err.response?.data?.message || 'Registration failed';
                throw err;
            } finally {
                this.loading = false;
            }
        },

        async customerLogout() {
            try {
                if (this.customerToken) {
                    await api.post('/customer/logout');
                }
            } catch (e) {
                // ignore
            } finally {
                this.customerToken = null;
                this.customerUser = null;
                localStorage.removeItem('atelier_customer_token');
                localStorage.removeItem('atelier_customer_user');
            }
        },
    },
});
