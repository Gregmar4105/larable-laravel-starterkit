import axios from 'axios';

/**
 * ─── Larable Axios Instance ──────────────────────────────────────────
 *
 * Pre-configured Axios instance for communicating with the Laravel API.
 * Handles:
 *   - Base URL configuration
 *   - CSRF cookie for Sanctum SPA authentication
 *   - Bearer token from localStorage
 *   - 401 unauthorized interceptor (auto-logout)
 */

const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL
    ? `${import.meta.env.VITE_API_BASE_URL}/api/v1`
    : '/api/v1',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  withCredentials: true, // Required for Sanctum SPA authentication
});

// Helper function to read a cookie value by name
function getCookie(name: string): string | undefined {
  if (typeof document === 'undefined') return undefined;
  const value = `; ${document.cookie}`;
  const parts = value.split(`; ${name}=`);
  if (parts.length === 2) return parts.pop()?.split(';').shift();
  return undefined;
}

// ─── Request Interceptor: Attach Bearer & CSRF Token ─────────────────
api.interceptors.request.use(
  (config) => {
    // Attach CSRF Token manually for cross-origin requests
    const csrfToken = getCookie('XSRF-TOKEN');
    if (csrfToken) {
      config.headers['X-XSRF-TOKEN'] = decodeURIComponent(csrfToken);
    }

    // Attach Bearer Token
    const token = localStorage.getItem('larable_token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => Promise.reject(error),
);


// ─── Response Interceptor: Handle 401 ────────────────────────────────
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('larable_token');
      localStorage.removeItem('larable_user');

      // Only redirect if not already on a public page
      const publicPaths = ['/login', '/register', '/forgot-password', '/reset-password'];
      const currentPath = window.location.pathname;
      if (!publicPaths.some((p) => currentPath.startsWith(p))) {
        window.location.href = '/login';
      }
    }
    return Promise.reject(error);
  },
);

/**
 * Get CSRF cookie from Sanctum before making authenticated requests.
 * Call this before login/register.
 */
export const getCsrfCookie = async (): Promise<void> => {
  const baseUrl = import.meta.env.VITE_API_BASE_URL || '';
  await axios.get(`${baseUrl}/sanctum/csrf-cookie`, { withCredentials: true });
};

export default api;
