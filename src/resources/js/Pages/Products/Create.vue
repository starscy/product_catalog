<template>
    <Layout>
        <div class="container mx-auto px-4 py-8 max-w-2xl">
            <h1 class="text-2xl font-bold mb-6">Добавление товара</h1>

            <form @submit.prevent="submitForm" class="bg-white rounded-lg shadow-md p-6">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Название *
                        </label>
                        <input
                            v-model="form.name"
                            type="text"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            :class="{ 'border-red-500': errors.name }"
                        />
                        <p v-if="errors.name" class="text-red-500 text-sm mt-1">
                            {{ errors.name }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Описание *
                        </label>
                        <textarea
                            v-model="form.description"
                            rows="4"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            :class="{ 'border-red-500': errors.description }"
                        ></textarea>
                        <p v-if="errors.description" class="text-red-500 text-sm mt-1">
                            {{ errors.description }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Цена *
                        </label>
                        <input
                            v-model="form.price"
                            type="number"
                            step="0.01"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            :class="{ 'border-red-500': errors.price }"
                        />
                        <p v-if="errors.price" class="text-red-500 text-sm mt-1">
                            {{ errors.price }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Категория *
                        </label>
                        <select
                            v-model="form.category_id"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            :class="{ 'border-red-500': errors.category_id }"
                        >
                            <option :value="null">Выберите категорию</option>
                            <option
                                v-for="category in categories"
                                :key="category.id"
                                :value="category.id"
                            >
                                {{ category.name }}
                            </option>
                        </select>
                        <p v-if="errors.category_id" class="text-red-500 text-sm mt-1">
                            {{ errors.category_id }}
                        </p>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4">
                        <Link
                            href="/dashboard"
                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                        >
                            Отмена
                        </Link>
                        <button
                            type="submit"
                            :disabled="submitting"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:bg-gray-400"
                        >
                            {{ submitting ? 'Сохранение...' : 'Сохранить' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </Layout>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import Layout from '@/Layouts/MainLayout.vue'

const props = defineProps({
    categories: Array
})

const form = reactive({
    name: '',
    description: '',
    price: '',
    category_id: null
})

const errors = ref({})
const submitting = ref(false)

const submitForm = () => {
    submitting.value = true
    errors.value = {}

    router.post('/products', form, {
        onError: (error) => {
            errors.value = error
            submitting.value = false
        },
        onFinish: () => {
            submitting.value = false
        }
    })
}
</script>
