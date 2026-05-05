<template>
    <div class="min-h-screen bg-gray-100">
        <!-- Верхняя панель -->
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 py-4 sm:px-6 lg:px-8 flex justify-between items-center">
                <h2 class="text-xl font-bold text-gray-900">
                    <Link href="/admin/products" class="hover:text-blue-600">🛍️ Админ-панель</Link>
                </h2>
                <h2 class="text-xl font-bold text-gray-900">
                    <Link href="/" class="hover:text-blue-600">Сайт</Link>
                </h2>

                <nav class="flex items-center space-x-4">
                    <span class="text-sm text-gray-600">{{ user?.name || 'Администратор' }}</span>
                    <button
                        @click="logout"
                        class="text-sm text-red-600 hover:text-red-800 font-medium"
                    >
                        Выйти
                    </button>
                </nav>
            </div>
        </header>

        <!-- Меню -->
        <aside class="bg-white border-b">
            <div class="max-w-7xl mx-auto px-4 py-2 sm:px-6 lg:px-8">
                <nav class="flex space-x-6">
                    <Link
                        href="/admin/products"
                        class="py-2 px-1 border-b-2 font-medium text-sm transition"
                        :class="isActive('/admin/products')
                            ? 'border-blue-500 text-blue-600'
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    >
                        📦 Управление товарами
                    </Link>
                </nav>
            </div>
        </aside>

        <!-- Контент -->
        <main class="max-w-7xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
            <slot />
        </main>
    </div>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

const page = usePage()

const user = computed(() => {
    return page.props.auth?.user || JSON.parse(localStorage.getItem('admin_user') || 'null')
})

const isActive = (path) => {
    return window.location.pathname.startsWith(path)
}

const logout = () => {
    localStorage.removeItem('admin_token')
    localStorage.removeItem('admin_user')

    router.post('/logout', {}, {
        onSuccess: () => {
            localStorage.removeItem('admin_token')
            localStorage.removeItem('admin_user')
        }
    })

    window.location.href = '/login'
}
</script>
