<script setup>
import { router } from '@inertiajs/vue3'
import Layout from '@/Layouts/MainLayout.vue'
import Pagination from '@/Components/Pagination.vue'

const props = defineProps({
    products: Object,
    categories: Array
})

const formatPrice = (price) => {
    return new Intl.NumberFormat('ru-RU', {
        style: 'currency',
        currency: 'RUB'
    }).format(price)
}

const deleteProduct = (product) => {
    if (confirm(`Вы уверены, что хотите удалить товар "${product.name}"?`)) {
        router.delete(route('products.destroy', product.id))
    }
}
</script>

<template>
    <Layout>
        <div class="container mx-auto px-4 py-8">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Панель управления</h1>

                <Link
                    href="/products/create"
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition"
                >
                    + Добавить товар
                </Link>
            </div>

            <!-- Flash сообщения -->
            <div v-if="$page.props.flash.success" class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ $page.props.flash.success }}
            </div>

            <!-- Таблица товаров -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            ID
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Название
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Категория
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Цена
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Действия
                        </th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="product in products.data" :key="product.id">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ product.id }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ product.name }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ product.category?.name }}
                        </td>
                        <td class="px-6 py-4 text-sm font-medium text-green-600">
                            {{ formatPrice(product.price) }}
                        </td>
                        <td class="px-6 py-4 text-sm space-x-2">
                            <Link
                                :href="route('products.edit', product.id)"
                                class="text-blue-600 hover:text-blue-900"
                            >
                                Редактировать
                            </Link>
                            <button
                                @click="deleteProduct(product)"
                                class="text-red-600 hover:text-red-900"
                            >
                                Удалить
                            </button>
                        </td>
                    </tr>
                    </tbody>
                </table>

                <!-- Пагинация -->
                <div class="px-6 py-4">
                    <Pagination :links="products.links" />
                </div>
            </div>
        </div>
    </Layout>
</template>

