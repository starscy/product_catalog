<script setup>
import { Link, router } from '@inertiajs/vue3'
import { useAuth } from '@/Composables/useAuth'
import { onMounted } from 'vue'
import UserMenu from '@/Components/Admin/UserMenu.vue'
import NavMenu from '@/Components/Admin/NavMenu.vue'

const { isAuthenticated, userName, logout, loadFromStorage } = useAuth()

const checkAuth = () => {
    loadFromStorage()

    if (!isAuthenticated.value) {
        router.get('/login')
    }
}

const handleLogout = async () => {
    await logout()
}

onMounted(() => {
    checkAuth()
})
</script>

<template>
    <div class="min-h-screen bg-gray-100">
        <!-- Верхняя панель -->
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 py-4 sm:px-6 lg:px-8 flex justify-between items-center">
                <div class="flex items-center space-x-8">
                    <h2 class="text-xl font-bold text-gray-900">
                        <Link href="/admin/products" class="hover:text-blue-600 transition">
                            🛍️ Админ-панель
                        </Link>
                    </h2>
                    <h2 class="text-xl font-bold text-gray-900">
                        <Link href="/" class="hover:text-blue-600 transition">
                            🌐 Сайт
                        </Link>
                    </h2>
                </div>

                <UserMenu :user-name="userName" @logout="handleLogout" />
            </div>
        </header>

        <!-- Меню -->
        <NavMenu />

        <!-- Контент -->
        <main class="max-w-7xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
            <slot />
        </main>
    </div>
</template>
