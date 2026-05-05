import axios from 'axios';
window.axios = axios;

// ── Sanctum SPA defaults ────────────────────────────────────────────────
axios.defaults.withCredentials = true;
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.headers.common['Accept'] = 'application/json';

// Redirect to login on expired Sanctum token
axios.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            window.location.href = '/login';
        }
        return Promise.reject(error);
    }
);

// Decrypt XSRF-TOKEN cookie → X-XSRF-TOKEN header for stateful API requests
axios.interceptors.request.use((config) => {
    const xsrfCookie = document.cookie
        .split('; ')
        .find((row) => row.startsWith('XSRF-TOKEN='));

    if (xsrfCookie) {
        const raw = decodeURIComponent(xsrfCookie.split('=')[1]);
        config.headers['X-XSRF-TOKEN'] = raw;
    }

    return config;
});
