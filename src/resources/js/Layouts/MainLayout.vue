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

                        <template v-if="user">
                            <Link
                                href="/admin/products"
                                class="text-purple-600 hover:text-purple-800 font-medium"
                            >
                                Админ-панель
                            </Link>

                            <button
                                @click="logout"
                                class="text-red-600 hover:text-red-800"
                            >
                                Выйти ({{ user.name }})
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

<script setup>
import { Link, router, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const page = usePage()

const user = computed(() => {
    return page.props.auth?.user || null
})

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
