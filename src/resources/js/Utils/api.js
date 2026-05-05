// resources/js/Utils/api.js
import { getAuthHeaders } from './auth'

/**
 * Универсальный клиент для API-запросов
 */
export const api = {
    /**
     * Выполняет fetch-запрос с авторизацией
     */
    async request(url, options = {}) {
        const response = await fetch(url, {
            credentials: 'same-origin',
            ...options,
            headers: {
                ...getAuthHeaders(),
                ...options.headers
            }
        })

        // Обработка 401: токен невалиден → выход
        if (response.status === 401) {
            localStorage.removeItem('admin_token')
            localStorage.removeItem('admin_user')
            window.location.href = '/login'
            throw new Error('Unauthorized')
        }

        // Обработка 419: CSRF mismatch
        if (response.status === 419) {
            console.error('CSRF token mismatch')
            window.location.reload()
            throw new Error('CSRF token mismatch')
        }

        return response
    },

    // Удобные обёртки
    get: (url, params) => {
        const queryString = params ? '?' + new URLSearchParams(params) : ''
        return api.request(url + queryString, { method: 'GET' })
    },
    post: (url, body) => api.request(url, { method: 'POST', body: JSON.stringify(body) }),
    put: (url, body) => api.request(url, { method: 'PUT', body: JSON.stringify(body) }),
    patch: (url, body) => api.request(url, { method: 'PATCH', body: JSON.stringify(body) }),
    delete: (url) => api.request(url, { method: 'DELETE' }),
}
