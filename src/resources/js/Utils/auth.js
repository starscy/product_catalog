// resources/js/Utils/auth.js

/**
 * Утилита для аутентификации: вход, регистрация, выход
 */

/**
 * Получает CSRF-токен из meta-тега или куки
 * @returns {string|null}
 */
const getCsrfToken = () => {
    // Пробуем получить из meta-тега (стандарт Laravel)
    const metaToken = document.querySelector('meta[name="csrf-token"]')?.content
    if (metaToken) return metaToken

    // Пробуем получить из куки XSRF-TOKEN (Laravel также устанавливает её)
    const cookies = document.cookie.split(';')
    for (const cookie of cookies) {
        const [name, value] = cookie.trim().split('=')
        if (name === 'XSRF-TOKEN') {
            return decodeURIComponent(value)
        }
    }

    return null
}

/**
 * Выполняет авторизационный запрос (login/register)
 * @param {string} url - '/login' или '/register'
 * @param {object} data - данные формы
 * @returns {Promise<{success: boolean, errors?: object, user?: object}>}
 */
export const authenticate = async (url, data) => {
    const csrfToken = getCsrfToken()

    if (!csrfToken) {
        console.warn('⚠️ CSRF token not found')
    }

    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                // Добавляем токен, если он есть
                ...(csrfToken && { 'X-CSRF-TOKEN': csrfToken })
            },
            body: JSON.stringify(data)
        })

        const result = await response.json()

        // Обработка ошибок
        if (!response.ok) {
            // 419 = CSRF token mismatch
            if (response.status === 419) {
                return {
                    success: false,
                    errors: { email: 'CSRF token mismatch. Обновите страницу и попробуйте снова.' }
                }
            }

            // Ошибки валидации или другие
            return {
                success: false,
                errors: result.errors || { email: result.message || 'Ошибка аутентификации' }
            }
        }

        // ✅ Успех: сохраняем токен и пользователя
        if (result.token && result.user) {
            localStorage.setItem('admin_token', result.token)
            localStorage.setItem('admin_user', JSON.stringify(result.user))
        }

        return { success: true, user: result.user }

    } catch (error) {
        console.error('Auth request failed:', error)
        return { success: false, errors: { email: 'Ошибка подключения к серверу' } }
    }
}

/**
 * Выход из системы
 * @returns {Promise<boolean>}
 */
export const logout = async () => {
    const token = localStorage.getItem('admin_token')

    if (token) {
        try {
            await fetch('/api/logout', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
        } catch (e) {
            console.warn('Logout API error (ignored):', e)
        }
    }

    // Очищаем хранилище независимо от результата запроса
    localStorage.removeItem('admin_token')
    localStorage.removeItem('admin_user')

    return true
}

/**
 * Проверка: авторизован ли пользователь
 * @returns {boolean}
 */
export const isAuthenticated = () => {
    return !!localStorage.getItem('admin_token')
}

/**
 * Получить данные текущего пользователя
 * @returns {object|null}
 */
export const getUser = () => {
    try {
        const userStr = localStorage.getItem('admin_user')
        return userStr ? JSON.parse(userStr) : null
    } catch {
        return null
    }
}

/**
 * Получить токен для использования в заголовках
 * @returns {string|null}
 */
export const getToken = () => {
    return localStorage.getItem('admin_token')
}

/**
 * Получить заголовки для авторизованных API-запросов
 * @returns {object}
 */
export const getAuthHeaders = () => {
    const token = getToken()
    const csrf = getCsrfToken()

    const headers = {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'Content-Type': 'application/json'
    }

    if (token) {
        headers['Authorization'] = `Bearer ${token}`
    }
    if (csrf) {
        headers['X-CSRF-TOKEN'] = csrf
    }

    return headers
}
