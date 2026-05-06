import { reactive, ref } from 'vue'

/**
 * Базовый composable для работы с формами
 * @param {object} initialData - начальные данные формы
 * @returns {object}
 */
export function useForm(initialData) {
    const form = reactive({ ...initialData })
    const processing = ref(false)
    const errors = ref({})

    /**
     * Сброс формы к начальному состоянию
     */
    const resetForm = () => {
        Object.keys(form).forEach(key => {
            if (initialData.hasOwnProperty(key)) {
                form[key] = initialData[key]
            }
        })
        errors.value = {}
        processing.value = false
    }

    /**
     * Обновить конкретное поле
     */
    const updateField = (field, value) => {
        if (form.hasOwnProperty(field)) {
            form[field] = value
        }
    }

    /**
     * Проверить, есть ли ошибки для конкретного поля
     */
    const hasError = (field) => {
        return !!errors.value[field]
    }

    /**
     * Получить ошибку для поля
     */
    const getError = (field) => {
        return errors.value[field]?.[0] || errors.value[field] || null
    }

    return {
        form,
        processing,
        errors,
        resetForm,
        updateField,
        hasError,
        getError
    }
}
