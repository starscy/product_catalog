<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import NavLink from '@/Components/Admin/NavLink.vue'

const menuItems = ref([
    { href: '/admin/products', label: 'Управление товарами', icon: '📦' },
    // { href: '/admin/categories', label: 'Категории', icon: '🏷️' },
    // { href: '/admin/orders', label: 'Заказы', icon: '📋' },
    // { href: '/admin/users', label: 'Пользователи', icon: '👥' },
    // { href: '/admin/settings', label: 'Настройки', icon: '⚙️' }
])

const currentPath = ref('')

const isActive = (path) => {
    return currentPath.value.startsWith(path)
}

const updatePath = () => {
    if (typeof window !== 'undefined') {
        currentPath.value = window.location.pathname
    }
}

onMounted(() => {
    updatePath()
    window.addEventListener('popstate', updatePath)
})

onUnmounted(() => {
    window.removeEventListener('popstate', updatePath)
})
</script>

<template>
    <aside class="bg-white border-b">
        <div class="max-w-7xl mx-auto px-4 py-2 sm:px-6 lg:px-8">
            <nav class="flex flex-wrap gap-2">
                <NavLink
                    v-for="item in menuItems"
                    :key="item.href"
                    :href="item.href"
                    :active="isActive(item.href)"
                >
                    {{ item.icon }} {{ item.label }}
                </NavLink>
            </nav>
        </div>
    </aside>
</template>
