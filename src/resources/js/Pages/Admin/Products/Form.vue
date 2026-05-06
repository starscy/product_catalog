<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, onMounted, computed } from 'vue'
import AdminLayout from "../../../Layouts/AdminLayout.vue";
import { useApi} from "../../../Composables/useApi.js";

const props = defineProps({
    product: Object,      // для редактирования (опционально)
    categories: Array     // список категорий
})

const isEdit = computed(() => !!props.product?.id)

// Используем API composable
const { loading, error, post, put, get } = useApi()

// Состояние формы
const form = ref({
    name: props.product?.name || '',
    description: props.product?.description || '',
    price: props.product?.price || '',
    category_id: props.product?.category_id || null
})

// Ошибки валидации (отдельно от error)
const validationErrors = ref({})

// Категории
const categories = ref(props.categories || [])

// Загружаем категории, если не переданы из контроллера
const loadCategories = async () => {
    if (!categories.value.length) {
        try {
            const data = await get('/api/categories')
            categories.value = data?.data || data || []
        } catch (e) {
            console.error('Failed to load categories:', e)
        }
    }
}

// Отправка формы
const submit = async () => {
    // Сбрасываем ошибки валидации
    validationErrors.value = {}

    // Подготавливаем данные
    const submitData = {
        ...form.value,
        price: parseFloat(form.value.price)
    }

    try {
        let result

        if (isEdit.value) {
            // Обновление товара
            result = await put(`/api/products/${props.product.id}`, submitData)
        } else {
            // Создание товара
            result = await post('/api/products', submitData)
        }

        // Успех — редирект в список
        if (result) {
            router.visit('/admin/products', {
                preserveScroll: true,
                onSuccess: () => {
                    // Можно показать уведомление об успехе
                    console.log(isEdit.value ? 'Товар обновлён' : 'Товар создан')
                }
            })
        }
    } catch (err) {
        // Обрабатываем ошибки валидации
        if (err.message && err.message.includes('Ошибка валидации')) {
            // Здесь можно парнуть ошибки из ответа
            // Но useApi пока не возвращает детальные ошибки
            validationErrors.value = {
                form: [err.message]
            }
        }
        console.error('Submit error:', err)
    }
}

// Загрузка категорий при монтировании
onMounted(() => {
    loadCategories()
})
</script>

<template>
    <Head :title="isEdit ? 'Редактирование товара' : 'Новый товар'" />

    <AdminLayout>
        <div class="max-w-2xl">
            <!-- Заголовок -->
            <div class="mb-6">
                <Link href="/admin/products" class="text-sm text-gray-600 hover:text-blue-600">
                    ← Назад к списку
                </Link>
                <h2 class="text-2xl font-bold text-gray-900 mt-2">
                    {{ isEdit ? 'Редактирование товара' : 'Новый товар' }}
                </h2>
            </div>

            <!-- Форма -->
            <form @submit.prevent="submit" class="bg-white rounded-lg shadow p-6 space-y-6">

                <!-- Индикатор загрузки -->
                <div v-if="loading" class="rounded-md bg-blue-50 p-4 text-blue-700 text-sm">
                    ⏳ Сохранение...
                </div>

                <!-- Ошибки -->
                <div v-if="error" class="rounded-md bg-red-50 p-4">
                    <div class="text-sm text-red-700">{{ error }}</div>
                </div>

                <!-- Ошибки валидации -->
                <div v-if="Object.keys(validationErrors).length > 0" class="rounded-md bg-red-50 p-4">
                    <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                        <li v-for="(message, field) in validationErrors" :key="field">
                            <strong>{{ field }}:</strong> {{ Array.isArray(message) ? message.join(', ') : message }}
                        </li>
                    </ul>
                </div>

                <!-- Название -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Название товара *
                    </label>
                    <input
                        v-model="form.name"
                        type="text"
                        required
                        :class="[
                            'block w-full rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500',
                            validationErrors.name ? 'border-red-500' : 'border-gray-300'
                        ]"
                        placeholder="Например: Смартфон XYZ"
                    >
                    <p v-if="validationErrors.name" class="mt-1 text-sm text-red-600">
                        {{ validationErrors.name[0] || validationErrors.name }}
                    </p>
                </div>

                <!-- Описание -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Описание *
                    </label>
                    <textarea
                        v-model="form.description"
                        required
                        rows="4"
                        :class="[
                            'block w-full rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500',
                            validationErrors.description ? 'border-red-500' : 'border-gray-300'
                        ]"
                        placeholder="Подробное описание товара..."
                    ></textarea>
                    <p v-if="validationErrors.description" class="mt-1 text-sm text-red-600">
                        {{ validationErrors.description[0] || validationErrors.description }}
                    </p>
                </div>

                <!-- Цена -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Цена (₽) *
                    </label>
                    <input
                        v-model="form.price"
                        type="number"
                        step="0.01"
                        min="0"
                        required
                        :class="[
                            'block w-full rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500',
                            validationErrors.price ? 'border-red-500' : 'border-gray-300'
                        ]"
                        placeholder="999.99"
                    >
                    <p v-if="validationErrors.price" class="mt-1 text-sm text-red-600">
                        {{ validationErrors.price[0] || validationErrors.price }}
                    </p>
                </div>

                <!-- Категория -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Категория *
                    </label>
                    <select
                        v-model="form.category_id"
                        required
                        :class="[
                            'block w-full rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500',
                            validationErrors.category_id ? 'border-red-500' : 'border-gray-300'
                        ]"
                    >
                        <option :value="null" disabled>Выберите категорию</option>
                        <option
                            v-for="category in categories"
                            :key="category.id"
                            :value="category.id"
                        >
                            {{ category.name }}
                        </option>
                    </select>
                    <p v-if="validationErrors.category_id" class="mt-1 text-sm text-red-600">
                        {{ validationErrors.category_id[0] || validationErrors.category_id }}
                    </p>
                </div>

                <!-- Кнопки -->
                <div class="flex justify-end space-x-3 pt-4 border-t">
                    <Link
                        href="/admin/products"
                        class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 transition"
                    >
                        Отмена
                    </Link>
                    <button
                        type="submit"
                        :disabled="loading"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition"
                    >
                        {{ loading ? 'Сохранение...' : 'Сохранить' }}
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>

<style scoped>
input:disabled, textarea:disabled, select:disabled, button:disabled {
    cursor: not-allowed;
    opacity: 0.6;
}
</style>
