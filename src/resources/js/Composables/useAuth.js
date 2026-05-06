import { ref, computed } from 'vue'

// Глобальное состояние (синглтон)
const user = ref(null)
const token = ref(null)
const isAuthenticated = ref(false)

export function useAuth() {
    /**
     * Получает CSRF-токен из meta-тега или куки
     * @returns {string|null}
     */
    const getCsrfToken = () => {
        const metaToken = document.querySelector('meta[name="csrf-token"]')?.content
        if (metaToken) return metaToken

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
     */
    const authenticate = async (url, data) => {
        const csrfToken = getCsrfToken()

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    ...(csrfToken && { 'X-CSRF-TOKEN': csrfToken })
                },
                body: JSON.stringify(data)
            })

            const result = await response.json()

            if (!response.ok) {
                if (response.status === 419) {
                    return {
                        success: false,
                        errors: { email: 'CSRF token mismatch. Обновите страницу и попробуйте снова.' }
                    }
                }

                return {
                    success: false,
                    errors: result.errors || { email: result.message || 'Ошибка аутентификации' }
                }
            }

            // Успех: сохраняем токен и пользователя
            if (result.token && result.user) {
                localStorage.setItem('admin_token', result.token)
                localStorage.setItem('admin_user', JSON.stringify(result.user))
            }

            return { success: true, user: result.user, token: result.token }

        } catch (error) {
            console.error('Auth request failed:', error)
            return { success: false, errors: { email: 'Ошибка подключения к серверу' } }
        }
    }

    // Загрузка данных из localStorage
    const loadFromStorage = () => {
        const storedToken = localStorage.getItem('admin_token')
        const storedUser = localStorage.getItem('admin_user')

        token.value = storedToken
        isAuthenticated.value = !!storedToken

        if (storedUser) {
            try {
                user.value = JSON.parse(storedUser)
            } catch (e) {
                user.value = null
                isAuthenticated.value = false
            }
        } else {
            user.value = null
        }

        return { token: token.value, user: user.value, isAuthenticated: isAuthenticated.value }
    }

    // Очистка хранилища
    const clearStorage = () => {
        localStorage.removeItem('admin_token')
        localStorage.removeItem('admin_user')
        token.value = null
        user.value = null
        isAuthenticated.value = false
    }

    // Выход из системы
    const logout = async () => {
        const currentToken = token.value || localStorage.getItem('admin_token')

        if (currentToken) {
            try {
                await fetch('/api/logout', {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${currentToken}`,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
            } catch (e) {
                console.error('Logout API error (ignored):', e)
            }
        }

        clearStorage()

        // Редирект на логин
        if (window.location.pathname !== '/login' && !window.location.pathname.startsWith('/register')) {
            window.location.href = '/login'
        }
    }

    // Вход
    const login = async (credentials) => {
        const result = await authenticate('/api/login', credentials)

        if (result.success && result.user) {
            loadFromStorage()
            return { success: true, user: result.user }
        }

        return { success: false, errors: result.errors }
    }

    // Регистрация
    const register = async (userData) => {
        const result = await authenticate('/api/register', userData)

        if (result.success && result.user) {
            loadFromStorage()
            return { success: true, user: result.user }
        }

        return { success: false, errors: result.errors }
    }

    // Геттеры
    const userName = computed(() => user.value?.name || user.value?.email || 'Гость')
    const userEmail = computed(() => user.value?.email || null)
    const isAdmin = computed(() => isAuthenticated.value)

    // Загружаем при первом вызове
    loadFromStorage()

    // Следим за изменениями в других вкладках
    if (typeof window !== 'undefined') {
        window.addEventListener('storage', (e) => {
            if (e.key === 'admin_token' || e.key === 'admin_user') {
                loadFromStorage()
            }
        })
    }

    return {
        // Состояние
        user,
        token,
        isAuthenticated,

        // Геттеры
        userName,
        userEmail,
        isAdmin,

        // Методы
        login,
        register,
        logout,
        loadFromStorage,
        clearStorage
    }
}
