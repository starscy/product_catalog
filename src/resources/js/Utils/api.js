// resources/js/Utils/api.js
import { useAuth} from "../Composables/useAuth.js";

/**
 * Универсальный клиент для API-запросов
 */
export const api = {
    /**
     * Выполняет fetch-запрос с авторизацией
     */
    async request(url, options = {}) {
        // Получаем экземпляр useAuth (один раз на запрос)
        const auth = useAuth()

        const token = auth.token?.value || localStorage.getItem('admin_token')
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content

        const headers = {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json',
            ...(token && { 'Authorization': `Bearer ${token}` }),
            ...(csrfToken && { 'X-CSRF-TOKEN': csrfToken }),
            ...options.headers
        }

        const response = await fetch(url, {
            credentials: 'same-origin',
            ...options,
            headers
        })

        // Обработка 401: токен невалиден → выход
        if (response.status === 401) {
            auth.clearStorage()
            if (!window.location.pathname.includes('/login')) {
                window.location.href = '/login'
            }
            throw new Error('Unauthorized')
        }

        // Обработка 419: CSRF mismatch
        if (response.status === 419) {
            console.error('CSRF token mismatch')
            throw new Error('CSRF token mismatch. Обновите страницу.')
        }

        return response
    },

    /**
     * Получить JSON-ответ
     */
    async getJson(url, params) {
        const response = await this.get(url, params)
        return response.json()
    },

    /**
     * Получить JSON-ответ после POST
     */
    async postJson(url, body) {
        const response = await this.post(url, body)
        return response.json()
    },

    // Удобные обёртки
    get: (url, params) => {
        const queryString = params ? '?' + new URLSearchParams(params).toString() : ''
        return api.request(url + queryString, { method: 'GET' })
    },

    post: (url, body) => api.request(url, {
        method: 'POST',
        body: JSON.stringify(body)
    }),

    put: (url, body) => api.request(url, {
        method: 'PUT',
        body: JSON.stringify(body)
    }),

    patch: (url, body) => api.request(url, {
        method: 'PATCH',
        body: JSON.stringify(body)
    }),

    delete: (url) => api.request(url, { method: 'DELETE' })
}
