<script setup>
import { Link } from '@inertiajs/vue3'
import { useAuth} from "../Composables/useAuth.js";

const { isAuthenticated, userName, logout } = useAuth()

const handleLogout = async () => {
    await logout()
}
</script>

<template>
    <div class="min-h-screen bg-gray-100">
        <nav class="bg-white shadow-md">
            <div class="container mx-auto px-4">
                <div class="flex justify-between items-center py-4">
                    <Link href="/" class="text-xl font-bold text-blue-600">
                        Магазин
                    </Link>

                    <div class="flex space-x-4">
                        <Link href="/products" class="text-gray-700 hover:text-blue-600">
                            Товары
                        </Link>

                        <!-- Для админа показываем админку -->
                        <template v-if="isAuthenticated">
                            <Link
                                href="/admin/products"
                                class="text-purple-600 hover:text-purple-800 font-medium"
                            >
                                Админ-панель
                            </Link>
                        </template>
                    </div>

                    <!-- Блок авторизации справа -->
                    <div class="flex space-x-4">
                        <template v-if="isAuthenticated">
                            <span class="text-gray-700">{{ userName }}</span>
                            <button
                                @click="handleLogout"
                                class="text-red-600 hover:text-red-800"
                            >
                                Выйти
                            </button>
                        </template>

                        <template v-else>
                            <Link href="/login" class="text-gray-700 hover:text-blue-600">
                                Войти
                            </Link>
                            <Link href="/register" class="text-gray-700 hover:text-blue-600">
                                Регистрация
                            </Link>
                        </template>
                    </div>
                </div>
            </div>
        </nav>

        <main>
            <slot />
        </main>

        <footer class="bg-white shadow-md mt-8 py-6">
            <div class="container mx-auto px-4 text-center text-gray-600">
                © 2026 Магазин. Все права защищены.
            </div>
        </footer>
    </div>
</template>
