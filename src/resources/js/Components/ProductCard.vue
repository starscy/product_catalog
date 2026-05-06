<script setup>
import { Link } from '@inertiajs/vue3'
import { useFormatters } from '@/Composables/useFormatters'

const props = defineProps({
    product: {
        type: Object,
        required: true
    }
})

const { formatPrice, formatRelativeDate } = useFormatters()
</script>

<template>
    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300">
        <div class="p-4">
            <h3 class="text-lg font-semibold text-gray-800 mb-2 line-clamp-1">
                {{ product.name }}
            </h3>

            <p class="text-gray-600 text-sm mb-3 line-clamp-2">
                {{ product.description || 'Описание отсутствует' }}
            </p>

            <div class="flex justify-between items-center text-xs text-gray-500 mb-3">
                <div>
                    <span>Категория:</span>
                    <span class="font-medium text-blue-600 ml-1">
                        {{ product.category?.name || 'Без категории' }}
                    </span>
                </div>
                <div v-if="product.created_at" class="text-gray-400">
                    🆕 {{ formatRelativeDate(product.created_at) }}
                </div>
            </div>

            <div class="flex justify-between items-center mt-3">
                <span class="text-xl font-bold text-green-600">
                    {{ formatPrice(product.price) }}
                </span>

                <Link
                    :href="route('web.products.show', { product: product.id })"
                    class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-800 text-sm font-medium group"
                >
                    <span>Подробнее</span>
                    <span class="group-hover:translate-x-1 transition-transform">→</span>
                </Link>
            </div>
        </div>
    </div>
</template>

<style scoped>
.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
