<script setup>
import { reactive, ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import { useAuth} from "../../Composables/useAuth.js";
import Layout from '@/Layouts/MainLayout.vue'

const form = reactive({
    name: '',
    email: '',
    password: '',
    password_confirmation: ''
})

const errors = ref({})
const processing = ref(false)
const { register } = useAuth()

const submit = async () => {
    processing.value = true
    errors.value = {}

    const result = await register(form)

    if (result.success) {
        window.location.href = '/admin/products'
    } else {
        errors.value = result.errors || { email: 'Ошибка регистрации' }
    }

    processing.value = false
}
</script>

<template>
    <Layout>
        <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-lg shadow">
                <div>
                    <h2 class="text-center text-3xl font-extrabold text-gray-900">
                        Регистрация
                    </h2>
                </div>

                <form class="mt-8 space-y-6" @submit.prevent="submit">
                    <div class="rounded-md shadow-sm space-y-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Имя</label>
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                required
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                :class="{ 'border-red-500': errors.name }"
                            />
                            <p v-if="errors.name" class="text-red-500 text-sm mt-1">{{ errors.name }}</p>
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                required
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                :class="{ 'border-red-500': errors.email }"
                            />
                            <p v-if="errors.email" class="text-red-500 text-sm mt-1">{{ errors.email }}</p>
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700">Пароль</label>
                            <input
                                id="password"
                                v-model="form.password"
                                type="password"
                                required
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                :class="{ 'border-red-500': errors.password }"
                            />
                            <p v-if="errors.password" class="text-red-500 text-sm mt-1">{{ errors.password }}</p>
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                                Подтверждение пароля
                            </label>
                            <input
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                type="password"
                                required
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            />
                        </div>
                    </div>

                    <div>
                        <button
                            type="submit"
                            :disabled="processing"
                            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {{ processing ? 'Регистрация...' : 'Зарегистрироваться' }}
                        </button>
                    </div>

                    <div class="text-center">
                        <Link href="/login" class="text-sm text-blue-600 hover:text-blue-500">
                            Уже есть аккаунт? Войдите
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </Layout>
</template>
