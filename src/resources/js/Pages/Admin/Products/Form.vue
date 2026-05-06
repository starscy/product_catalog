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

                <!-- Ошибки -->
                <div v-if="Object.keys(errors).length > 0" class="rounded-md bg-red-50 p-4">
                    <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                        <li v-for="(message, field) in errors" :key="field">{{ message }}</li>
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
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        placeholder="Например: Смартфон XYZ"
                    >
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
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        placeholder="Подробное описание товара..."
                    ></textarea>
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
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        placeholder="999.99"
                    >
                </div>

                <!-- Категория -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Категория *
                    </label>
                    <select
                        v-model="form.category_id"
                        required
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
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
                        :disabled="processing"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition"
                    >
                        {{ processing ? 'Сохранение...' : 'Сохранить' }}
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, onMounted, computed } from 'vue'
import AdminLayout from '@/Pages/Admin/AdminLayout.vue'

const props = defineProps({
    product: Object,      // для редактирования (опционально)
    categories: Array     // список категорий
})

const isEdit = computed(() => !!props.product?.id)

const form = ref({
    name: props.product?.name || '',
    description: props.product?.description || '',
    price: props.product?.price || '',
    category_id: props.product?.category_id || null
})

const errors = ref({})
const processing = ref(false)
const categories = ref(props.categories || [])

// Загружаем категории, если не переданы из контроллера
onMounted(async () => {
    if (!categories.value.length) {
        try {
            const response = await fetch('/api/categories', {
                headers: { 'Accept': 'application/json' }
            })
            const data = await response.json()
            categories.value = data.data || data
        } catch (e) {
            console.error('Failed to load categories:', e)
        }
    }
})

const submit = async () => {
    processing.value = true
    errors.value = {}

    const token = localStorage.getItem('admin_token')
    const method = isEdit.value ? 'PUT' : 'POST'
    const url = isEdit.value
        ? `/api/products/${props.product.id}`
        : '/api/products'

    try {
        const response = await fetch(url, {
            method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                ...form.value,
                price: parseFloat(form.value.price)
            })
        })

        const data = await response.json()

        if (response.ok) {
            // Успех — редирект в список
            window.location.href = '/admin/products'
        } else {
            // Ошибки валидации
            if (data.errors) {
                errors.value = data.errors
            } else if (data.message) {
                alert(data.message)
            }
        }
    } catch (e) {
        console.error('Submit error:', e)
        alert('Ошибка подключения к серверу')
    } finally {
        processing.value = false
    }
}
</script>
