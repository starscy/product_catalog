<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch, onBeforeUnmount, computed } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import AdminPagination from '@/Components/Admin/Pagination.vue'
import { useApi } from '@/Composables/useApi'

const props = defineProps({
    products: Object,
    filters: Object
})

const { loading, error, delete: deleteApi, post: postApi } = useApi()

// Состояние фильтров
const searchQuery = ref(props.filters?.search || '')
const showTrashed = ref(props.filters?.trashed === '1' || props.filters?.trashed === true)

const searchTimeout = ref(null)
const DEBOUNCE_DELAY = 300

// Форматирование цены
const formatPrice = (value) => {
    return new Intl.NumberFormat('ru-RU', {
        style: 'currency',
        currency: 'RUB',
        minimumFractionDigits: 0
    }).format(value)
}

// Форматирование даты
const formatDate = (dateString) => {
    if (!dateString) return ''
    return new Date(dateString).toLocaleDateString('ru-RU', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

// Применение фильтров (основная функция)
const applyFilters = () => {
    const queryParams = {
        search: searchQuery.value || undefined,
        trashed: showTrashed.value ? '1' : undefined,
        page: 1
    }

    // Удаляем undefined значения
    const cleanParams = Object.fromEntries(
        Object.entries(queryParams).filter(([_, v]) => v !== null && v !== undefined && v !== '')
    )

    router.get('/admin/products', cleanParams, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    })
}

// Обработчик ввода поиска с дебаунсом
const handleSearchInput = () => {
    if (searchTimeout.value) clearTimeout(searchTimeout.value)
    searchTimeout.value = setTimeout(() => applyFilters(), DEBOUNCE_DELAY)
}

// Переключение режима "удалённые" (исправлено)
const toggleTrashed = () => {
    showTrashed.value = !showTrashed.value
    // Сбрасываем поиск при переключении
    searchQuery.value = ''
    // Применяем фильтры
    applyFilters()
}

// Очистка поиска
const clearSearch = () => {
    searchQuery.value = ''
    applyFilters()
}

// Удаление товара (мягкое)
const confirmDelete = async (product) => {
    if (!confirm(`Удалить "${product.name}"?`)) return

    try {
        await deleteApi(`/api/products/${product.id}`)
        router.reload({ preserveScroll: true })
    } catch (err) {
        alert(err.message || 'Ошибка при удалении')
    }
}

// Восстановление товара
const restoreProduct = async (product) => {
    if (!confirm(`Восстановить "${product.name}"?`)) return

    try {
        await postApi(`/api/products/${product.id}/restore`)
        router.reload({ preserveScroll: true })
    } catch (err) {
        alert(err.message || 'Ошибка при восстановлении')
    }
}

// Полное удаление товара
const forceDeleteProduct = async (product) => {
    if (!confirm(`⚠️ Удалить "${product.name}" НАВСЕГДА? Это действие нельзя отменить!`)) return

    try {
        await deleteApi(`/api/products/${product.id}/force`)
        router.reload({ preserveScroll: true })
    } catch (err) {
        alert(err.message || 'Ошибка при полном удалении')
    }
}

// Синхронизация с props.filters из сервера
watch(
    () => props.filters,
    (newFilters) => {
        if (newFilters) {
            if (newFilters.search !== undefined && searchQuery.value !== newFilters.search) {
                searchQuery.value = newFilters.search || ''
            }
            const newTrashed = newFilters.trashed === '1' || newFilters.trashed === true
            if (showTrashed.value !== newTrashed) {
                showTrashed.value = newTrashed
            }
        }
    },
    { deep: true, immediate: true }
)

// Очистка таймера
onBeforeUnmount(() => {
    if (searchTimeout.value) clearTimeout(searchTimeout.value)
})
</script>

<template>
    <Head title="Управление товарами" />

    <AdminLayout>
        <!-- Заголовок + поиск + фильтры + кнопка добавления -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <h2 class="text-2xl font-bold text-gray-900">Товары</h2>

            <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                <!-- Переключатель "Показывать удалённые" (исправлен) -->
                <label class="flex items-center space-x-2 text-sm text-gray-600 cursor-pointer">
                    <input
                        type="checkbox"
                        :checked="showTrashed"
                        @change="toggleTrashed"
                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer"
                    >
                    <span>Показывать удалённые</span>
                </label>

                <!-- Поиск -->
                <div class="relative">
                    <input
                        v-model="searchQuery"
                        @input="handleSearchInput"
                        type="search"
                        placeholder="Поиск по названию..."
                        class="w-full sm:w-64 pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                    >
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                        🔍
                    </span>
                    <button
                        v-if="searchQuery"
                        @click="clearSearch"
                        class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                    >
                        ✕
                    </button>
                </div>

                <!-- Кнопка добавления (скрываем, если показываем удалённые) -->
                <Link
                    v-if="!showTrashed"
                    href="/admin/products/create"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium whitespace-nowrap text-center"
                >
                    + Добавить товар
                </Link>
            </div>
        </div>

        <!-- Активные фильтры -->
        <div v-if="searchQuery || showTrashed" class="mb-4 flex flex-wrap items-center gap-2 text-sm">
            <span
                v-if="showTrashed"
                class="px-2 py-1 bg-red-100 text-red-800 rounded font-medium flex items-center gap-1"
            >
                🗑️ Удалённые
                <button
                    @click="toggleTrashed"
                    class="ml-1 text-red-600 hover:text-red-800 font-bold"
                >
                    ✕
                </button>
            </span>
            <span
                v-if="searchQuery"
                class="px-2 py-1 bg-blue-100 text-blue-800 rounded font-medium flex items-center gap-1"
            >
                Поиск: "{{ searchQuery }}"
                <button
                    @click="clearSearch"
                    class="ml-1 text-blue-600 hover:text-blue-800 font-bold"
                >
                    ✕
                </button>
            </span>
        </div>

        <!-- Остальной шаблон без изменений... -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Название
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Категория
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Цена
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Действия
                    </th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                <tr v-if="loading">
                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">Загрузка...</td>
                </tr>
                <tr v-else-if="!products?.data?.length">
                    <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                        <p v-if="searchQuery">
                            По запросу "{{ searchQuery }}" ничего не найдено.
                        </p>
                        <p v-else-if="showTrashed">
                            Удалённые товары не найдены.
                        </p>
                        <p v-else>
                            Товары не найдены.
                        </p>
                    </td>
                </tr>
                <tr
                    v-for="product in products?.data"
                    :key="product.id"
                    class="hover:bg-gray-50"
                    :class="{ 'bg-red-50': product.deleted_at }"
                >
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <span v-if="product.deleted_at" class="text-red-500" title="Удалён">🗑️</span>
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ product.name }}</div>
                                <div class="text-sm text-gray-500 truncate max-w-xs">{{ product.description }}</div>
                                <div v-if="product.deleted_at" class="text-xs text-red-500 mt-1">
                                    Удалён: {{ formatDate(product.deleted_at) }}
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span v-if="product.category" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            {{ product.category.name }}
                        </span>
                        <span v-else class="text-sm text-gray-400">—</span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900 font-medium">
                        {{ formatPrice(product.price) }}
                    </td>
                    <td class="px-6 py-4 text-right text-sm font-medium space-x-2">
                        <template v-if="product.deleted_at">
                            <button
                                @click="restoreProduct(product)"
                                class="text-green-600 hover:text-green-900"
                                :disabled="loading"
                                title="Восстановить"
                            >
                                ♻️ Восстановить
                            </button>
                            <button
                                @click="forceDeleteProduct(product)"
                                class="text-red-800 hover:text-red-900"
                                :disabled="loading"
                                title="Удалить навсегда"
                            >
                                🗑️ Навсегда
                            </button>
                        </template>
                        <template v-else>
                            <Link
                                :href="`/admin/products/${product.id}/edit`"
                                class="text-blue-600 hover:text-blue-900"
                            >
                                ✏️ Редактировать
                            </Link>
                            <button
                                @click="confirmDelete(product)"
                                class="text-red-600 hover:text-red-900"
                                :disabled="loading"
                            >
                                🗑️ Удалить
                            </button>
                        </template>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <div v-if="products?.links" class="mt-4">
            <AdminPagination :links="products.links" />
        </div>
    </AdminLayout>
</template>

<style scoped>
button:disabled {
    cursor: not-allowed;
    opacity: 0.5;
}
</style>
