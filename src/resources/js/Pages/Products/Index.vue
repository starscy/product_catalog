<template>
    <Layout>
        <div class="container mx-auto px-4 py-8">
            <h1 class="text-3xl font-bold mb-8">Каталог товаров</h1>

                <div class="p-8">
                    <h1 class="text-3xl font-bold text-blue-600">
                        Tailwind v4 работает! 🎉
                    </h1>
                    <p class="mt-2 text-gray-700">
                        Если вы видите синий заголовок — всё настроено верно.
                    </p>
                </div>

            <!-- Фильтры -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Категория
                        </label>
                        <select
                            v-model="filters.category_id"
                            @change="applyFilters"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                            <option :value="null">Все категории</option>
                            <option
                                v-for="category in categories"
                                :key="category.id"
                                :value="category.id"
                            >
                                {{ category.name }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Сортировать по
                        </label>
                        <select
                            v-model="filters.sort_by"
                            @change="applyFilters"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                            <option :value="null">По умолчанию</option>
                            <option value="name">Названию</option>
                            <option value="price">Цене</option>
                            <option value="created_at">Дате добавления</option>
                        </select>
                    </div>

                    <div v-if="filters.sort_by">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Направление
                        </label>
                        <select
                            v-model="filters.direction"
                            @change="applyFilters"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                            <option value="asc">По возрастанию</option>
                            <option value="desc">По убыванию</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Товары -->
            <div v-if="loading" class="text-center py-12">
                <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
            </div>

            <div v-else-if="products.data.length === 0" class="text-center py-12 bg-gray-50 rounded-lg">
                <p class="text-gray-500 text-lg">Товары не найдены</p>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <ProductCard
                    v-for="product in products.data"
                    :key="product.id"
                    :product="product"
                />
            </div>

            <!-- Пагинация -->
            <div v-if="products.links" class="mt-8">
                <Pagination :links="products.links" />
            </div>
        </div>
    </Layout>
</template>

<script setup>
import {ref, watch} from 'vue'
import {router} from '@inertiajs/vue3'
import Layout from '@/Layouts/MainLayout.vue'
import ProductCard from '@/Components/ProductCard.vue'
import Pagination from '@/Components/Pagination.vue'

const props = defineProps({
    products: Object,
    categories: Array,
    filters: Object
})

const loading = ref(false)
const localFilters = ref({...props.filters})

const applyFilters = () => {
    loading.value = true

    router.get(
        route('products.index'),
        localFilters.value,
        {
            preserveState: true,
            preserveScroll: true,
            onFinish: () => {
                loading.value = false
            }
        }
    )
}

// Следим за изменением filters из props
watch(() => props.filters, (newFilters) => {
    localFilters.value = {...newFilters}
}, {deep: true})
</script>
