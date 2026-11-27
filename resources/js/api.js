import axios from 'axios';

// Get or generate session ID for guest carts
let sessionId = localStorage.getItem('atelier_session_id');
const UUID = /^[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i;
// An ID made by an older version (Math.random) is replaced; the server only accepts UUIDs.
if (!sessionId || !UUID.test(sessionId)) {
    sessionId = crypto.randomUUID();
    localStorage.setItem('atelier_session_id', sessionId);
}

const api = axios.create({
    baseURL: '/api/v1',
    headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Session-ID': sessionId,
    },
});

// Request interceptor: inject Sanctum token if present
api.interceptors.request.use(
    (config) => {
        // Admin token has priority for /admin endpoints
        if (config.url?.startsWith('/admin')) {
            const adminToken = localStorage.getItem('atelier_admin_token');
            if (adminToken) {
                config.headers.Authorization = `Bearer ${adminToken}`;
            }
        } else {
            // Customer or general token
            const customerToken = localStorage.getItem('atelier_customer_token');
            if (customerToken) {
                config.headers.Authorization = `Bearer ${customerToken}`;
            }
        }
        return config;
    },
    (error) => Promise.reject(error)
);

// Response interceptor: handle 401s and format errors
api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            if (error.config?.url?.startsWith('/admin')) {
                localStorage.removeItem('atelier_admin_token');
                localStorage.removeItem('atelier_admin_user');
                if (window.location.pathname.startsWith('/admin') && window.location.pathname !== '/admin/login') {
                    window.location.href = '/admin/login';
                }
            } else if (error.config?.url?.startsWith('/customer')) {
                localStorage.removeItem('atelier_customer_token');
                localStorage.removeItem('atelier_customer_user');
            }
        }
        return Promise.reject(error);
    }
);

export default api;
export { sessionId };
