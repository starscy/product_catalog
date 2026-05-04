<template>
    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300">
        <div class="p-4">
            <h3 class="text-lg font-semibold text-gray-800 mb-2">
                {{ product.name }}
            </h3>

            <p class="text-gray-600 text-sm mb-3 line-clamp-2">
                {{ product.description }}
            </p>

            <div class="mb-2">
                <span class="text-xs text-gray-500">Категория:</span>
                <span class="text-xs font-medium text-blue-600 ml-1">
          {{ product.category?.name }}
        </span>
            </div>

            <div class="flex justify-between items-center mt-3">
        <span class="text-xl font-bold text-green-600">
          {{ formatPrice(product.price) }}
        </span>

                <Link
                    :href="productUrl"
                    class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                >
                    Подробнее →
                </Link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({ product: Object })

// ✅ Вычисляемое свойство для безопасного URL
const productUrl = computed(() => route('web.products.show', { product: props.product.id }))

const formatPrice = (price) => {
    return new Intl.NumberFormat('ru-RU', {
        style: 'currency',
        currency: 'RUB'
    }).format(price)
}
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
