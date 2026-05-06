import { ref } from 'vue'

export function useApi() {
    const loading = ref(false)
    const error = ref(null)
    const lastResponse = ref(null)

    /**
     * Получить токен из localStorage
     */
    const getToken = () => {
        return localStorage.getItem('admin_token')
    }

    /**
     * Получить CSRF-токен из meta-тега
     */
    const getCsrfToken = () => {
        return document.querySelector('meta[name="csrf-token"]')?.content
    }

    /**
     * Очистить данные авторизации
     */
    const clearAuth = () => {
        localStorage.removeItem('admin_token')
        localStorage.removeItem('admin_user')
    }

    /**
     * Базовый запрос
     */
    const request = async (url, options = {}) => {
        loading.value = true
        error.value = null

        try {
            const token = getToken()
            const csrfToken = getCsrfToken()

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

            lastResponse.value = response

            // Обработка 401: токен невалиден → выход
            if (response.status === 401) {
                clearAuth()
                if (!window.location.pathname.includes('/login') && !window.location.pathname.includes('/register')) {
                    window.location.href = '/login'
                }
                throw new Error('Сессия истекла. Пожалуйста, войдите снова.')
            }

            // Обработка 403: доступ запрещён
            if (response.status === 403) {
                throw new Error('Доступ запрещён. У вас недостаточно прав.')
            }

            // Обработка 419: CSRF mismatch
            if (response.status === 419) {
                throw new Error('CSRF токен устарел. Обновите страницу и попробуйте снова.')
            }

            // Обработка 422: ошибки валидации
            if (response.status === 422) {
                const data = await response.json()
                const validationErrors = data.errors || {}
                const errorMessage = Object.values(validationErrors).flat().join(', ')
                throw new Error(errorMessage || 'Ошибка валидации данных')
            }

            return response
        } catch (e) {
            error.value = e.message
            throw e
        } finally {
            loading.value = false
        }
    }

    /**
     * Получить JSON из ответа
     */
    const toJson = async (response) => {
        try {
            return await response.json()
        } catch {
            return null
        }
    }

    /**
     * GET запрос
     */
    const get = async (url, params = null) => {
        const queryString = params ? '?' + new URLSearchParams(params).toString() : ''
        const response = await request(url + queryString, { method: 'GET' })
        return toJson(response)
    }

    /**
     * POST запрос
     */
    const post = async (url, body = null) => {
        const response = await request(url, {
            method: 'POST',
            body: body ? JSON.stringify(body) : undefined
        })
        return toJson(response)
    }

    /**
     * PUT запрос
     */
    const put = async (url, body = null) => {
        const response = await request(url, {
            method: 'PUT',
            body: body ? JSON.stringify(body) : undefined
        })
        return toJson(response)
    }

    /**
     * PATCH запрос
     */
    const patch = async (url, body = null) => {
        const response = await request(url, {
            method: 'PATCH',
            body: body ? JSON.stringify(body) : undefined
        })
        return toJson(response)
    }

    /**
     * DELETE запрос
     */
    const del = async (url) => {
        const response = await request(url, { method: 'DELETE' })
        return toJson(response)
    }

    /**
     * Upload файла (multipart/form-data)
     */
    const upload = async (url, formData, onProgress = null) => {
        loading.value = true
        error.value = null

        try {
            const token = getToken()
            const csrfToken = getCsrfToken()

            const xhr = new XMLHttpRequest()

            const promise = new Promise((resolve, reject) => {
                xhr.open('POST', url)
                xhr.setRequestHeader('Accept', 'application/json')
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest')
                if (token) {
                    xhr.setRequestHeader('Authorization', `Bearer ${token}`)
                }
                if (csrfToken) {
                    xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken)
                }

                if (onProgress) {
                    xhr.upload.addEventListener('progress', (e) => {
                        if (e.lengthComputable) {
                            onProgress(Math.round((e.loaded / e.total) * 100))
                        }
                    })
                }

                xhr.onload = () => {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        try {
                            const response = JSON.parse(xhr.responseText)
                            resolve(response)
                        } catch {
                            resolve(null)
                        }
                    } else if (xhr.status === 401) {
                        clearAuth()
                        window.location.href = '/login'
                        reject(new Error('Сессия истекла'))
                    } else {
                        reject(new Error(`Ошибка загрузки: ${xhr.status}`))
                    }
                    loading.value = false
                }

                xhr.onerror = () => {
                    reject(new Error('Ошибка сети при загрузке'))
                    loading.value = false
                }

                xhr.send(formData)
            })

            return await promise
        } catch (e) {
            error.value = e.message
            loading.value = false
            throw e
        }
    }

    /**
     * Сбросить состояние ошибки
     */
    const resetError = () => {
        error.value = null
    }

    return {
        // Состояние
        loading,
        error,
        lastResponse,

        // Методы
        get,
        post,
        put,
        patch,
        delete: del,
        upload,
        resetError,

        // Низкоуровневый доступ
        request,
        toJson
    }
}
