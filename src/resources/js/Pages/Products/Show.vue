<template>
    <Head :title="product.name" />

    <Layout>
        <div class="container mx-auto px-4 py-8">

            <!-- Кнопка "Назад" -->
            <div class="mb-6">
                <Link
                    href="/products"
                    class="inline-flex items-center text-sm text-gray-600 hover:text-blue-600 transition"
                >
                    ← Назад к каталогу
                </Link>
            </div>

            <!-- Карточка товара -->
            <div class="bg-white rounded-xl shadow-md p-6 lg:p-8 max-w-3xl">

                <!-- Категория -->
                <div v-if="product.category" class="mb-4">
                    <span class="inline-block px-3 py-1 text-xs font-medium text-blue-700 bg-blue-100 rounded-full">
                        {{ product.category.name }}
                    </span>
                </div>

                <!-- Название -->
                <h1 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-4">
                    {{ product.name }}
                </h1>

                <!-- Цена -->
                <div class="mb-6">
                    <span class="text-3xl font-bold text-green-600">
                        {{ formatPrice(product.price) }}
                    </span>
                </div>

                <!-- Описание -->
                <div class="prose prose-sm max-w-none text-gray-600">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Описание</h3>
                    <p class="whitespace-pre-line">{{ product.description }}</p>
                </div>

                <!-- Мета-информация -->
                <div class="mt-8 pt-6 border-t text-sm text-gray-500">
                    <div v-if="product.created_at" class="mb-1">
                        <span class="font-medium text-gray-700">Добавлен:</span>
                        {{ formatDate(product.created_at) }}
                    </div>
                    <div>
                        <span class="font-medium text-gray-700">ID товара:</span>
                        {{ product.id }}
                    </div>
                </div>

            </div>

        </div>
    </Layout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import Layout from '@/Layouts/MainLayout.vue'

const props = defineProps({
    product: {
        type: Object,
        required: true
    }
})

// Форматирование цены (как в ProductCard.vue)
const formatPrice = (value) => {
    return new Intl.NumberFormat('ru-RU', {
        style: 'currency',
        currency: 'RUB',
        minimumFractionDigits: 0
    }).format(value)
}

const formatDate = (dateString) => {
    if (!dateString) return '—'

    return new Date(dateString).toLocaleDateString('ru-RU', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    })
}
</script>

<style scoped>
.prose h3 {
    margin-top: 0;
    margin-bottom: 0.5rem;
    font-size: 1.125rem;
    font-weight: 600;
    color: #111827;
}

.prose p {
    margin: 0;
    line-height: 1.6;
}
</style>
