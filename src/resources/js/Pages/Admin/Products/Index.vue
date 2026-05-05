<template>
    <Head title="Управление товарами" />

    <AdminLayout>
        <!-- Заголовок + поиск + кнопка добавления -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <h2 class="text-2xl font-bold text-gray-900">Товары</h2>

            <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
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
                    <!-- Кнопка очистки поиска -->
                    <button
                        v-if="searchQuery"
                        @click="clearSearch"
                        class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                    >
                        ✕
                    </button>
                </div>

                <!-- Кнопка добавления -->
                <Link
                    href="/admin/products/create"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium whitespace-nowrap"
                >
                    + Добавить товар
                </Link>
            </div>
        </div>

        <!-- Активные фильтры (показываем, если есть поиск) -->
        <div v-if="searchQuery" class="mb-4 flex items-center gap-2 text-sm">
            <span class="text-gray-500">Поиск:</span>
            <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded font-medium">
                "{{ searchQuery }}"
            </span>
            <button @click="clearSearch" class="text-gray-400 hover:text-gray-600">✕</button>
        </div>

        <!-- Таблица товаров -->
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
                <tr v-if="loading" class="animate-pulse">
                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">Загрузка...</td>
                </tr>

                <tr v-else-if="products?.data?.length === 0">
                    <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                        <p v-if="searchQuery">
                            По запросу "{{ searchQuery }}" ничего не найдено.
                            <button @click="clearSearch" class="text-blue-600 hover:underline">Сбросить поиск</button>
                        </p>
                        <p v-else>
                            Товары не найдены.
                            <Link href="/admin/products/create" class="text-blue-600 hover:underline">Добавить первый</Link>
                        </p>
                    </td>
                </tr>

                <tr v-for="product in products?.data" :key="product.id" class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-900">{{ product.name }}</div>
                        <div class="text-sm text-gray-500 truncate max-w-xs">{{ product.description }}</div>
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
                    <td class="px-6 py-4 text-right text-sm font-medium">
                        <Link
                            :href="`/admin/products/${product.id}/edit`"
                            class="text-blue-600 hover:text-blue-900 mr-4"
                        >
                            Редактировать
                        </Link>
                        <button
                            @click="confirmDelete(product)"
                            class="text-red-600 hover:text-red-900"
                            :disabled="loading"
                        >
                            Удалить
                        </button>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <!-- Пагинация -->
        <div v-if="products?.links" class="mt-4">
            <AdminPagination :links="products.links" />
        </div>
    </AdminLayout>
</template>

<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { ref, onMounted, watch } from 'vue'
import AdminLayout from '@/Pages/Admin/Layout.vue'
import AdminPagination from '@/Components/Admin/Pagination.vue'

const props = defineProps({
    products: Object,
    filters: Object // { search: string, ... }
})

const loading = ref(false)
const searchQuery = ref(props.filters?.search || '')
const searchTimeout = ref(null)
const DEBOUNCE_DELAY = 300 // мс

// Форматирование цены
const formatPrice = (value) => {
    return new Intl.NumberFormat('ru-RU', {
        style: 'currency',
        currency: 'RUB',
        minimumFractionDigits: 0
    }).format(value)
}

// Обработчик ввода поиска с дебаунсом
const handleSearchInput = () => {
    // Очищаем предыдущий таймер
    if (searchTimeout.value) {
        clearTimeout(searchTimeout.value)
    }

    // Устанавливаем новый таймер
    searchTimeout.value = setTimeout(() => {
        applySearch()
    }, DEBOUNCE_DELAY)
}

// Применение поиска
const applySearch = () => {
    loading.value = true

    // Собираем текущие параметры из URL (чтобы сохранить пагинацию и другие фильтры)
    const page = usePage()
    const currentFilters = page.props.filters || {}

    // Формируем новые параметры
    const queryParams = {
        ...currentFilters,
        search: searchQuery.value || undefined, // undefined удалит параметр из URL
        page: 1 // Сбрасываем на первую страницу при новом поиске
    }

    // Очищаем от undefined/null значений
    const cleanParams = Object.fromEntries(
        Object.entries(queryParams).filter(([_, v]) => v !== null && v !== undefined && v !== '')
    )

    router.get('/admin/products', cleanParams, {
        preserveState: true,
        preserveScroll: true,
        replace: true, // Не создаём новую запись в истории
        onFinish: () => {
            loading.value = false
        },
        onError: (errors) => {
            console.error('Search error:', errors)
            loading.value = false
        }
    })
}

// Очистка поиска
const clearSearch = () => {
    searchQuery.value = ''
    applySearch()
}

// Удаление товара
const confirmDelete = (product) => {
    if (loading.value) return
    if (!confirm(`Удалить товар "${product.name}"?`)) return

    loading.value = true
    const token = localStorage.getItem('admin_token')

    fetch(`/api/products/${product.id}`, {
        method: 'DELETE',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => {
            if (response.ok) {
                router.reload({ preserveScroll: true })
            } else {
                alert('Ошибка при удалении')
            }
        })
        .catch(e => {
            console.error('Delete error:', e)
            alert('Ошибка подключения')
        })
        .finally(() => {
            loading.value = false
        })
}

// Синхронизация: если фильтры изменились извне (пагинация, назад/вперёд)
watch(
    () => props.filters?.search,
    (newSearch) => {
        if (searchQuery.value !== newSearch) {
            searchQuery.value = newSearch || ''
        }
    }
)

// Очистка таймера при размонтировании
onMounted(() => {
    // Инициализация при загрузке, если нужно
})

// Важно: очищаем таймер при уходе со страницы
import { onBeforeUnmount } from 'vue'
onBeforeUnmount(() => {
    if (searchTimeout.value) {
        clearTimeout(searchTimeout.value)
    }
})
</script>

<style scoped>
/* Плавная анимация для появления/исчезновения кнопки очистки */
button.absolute {
    transition: opacity 0.2s;
}
</style>
