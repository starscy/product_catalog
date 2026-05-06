<script setup>
import { ref, computed } from 'vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    userName: {
        type: String,
        default: 'Администратор'
    },
    userEmail: {
        type: String,
        default: ''
    }
})

const emit = defineEmits(['logout'])

const isMenuOpen = ref(false)

const toggleMenu = () => {
    isMenuOpen.value = !isMenuOpen.value
}

const closeMenu = () => {
    isMenuOpen.value = false
}

const handleLogout = () => {
    closeMenu()
    emit('logout')
}

const userInitial = computed(() => {
    return props.userName ? props.userName.charAt(0).toUpperCase() : '👤'
})

const displayName = computed(() => {
    if (props.userName && props.userName !== 'Гость') {
        return props.userName.length > 20 ? props.userName.substring(0, 20) + '...' : props.userName
    }
    return 'Администратор'
})

// Закрываем меню при клике вне его
const handleClickOutside = (event) => {
    if (isMenuOpen.value && !event.target.closest('.relative')) {
        closeMenu()
    }
}

// Добавляем/удаляем слушатель кликов
if (typeof window !== 'undefined') {
    window.addEventListener('click', handleClickOutside)
}
</script>

<template>
    <nav class="flex items-center space-x-4">
        <div class="relative">
            <button
                @click="toggleMenu"
                class="flex items-center space-x-2 text-sm text-gray-600 hover:text-gray-900 focus:outline-none"
            >
                <span class="flex items-center gap-2">
                    <span class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center text-blue-600">
                        {{ userInitial }}
                    </span>
                    <span>{{ displayName }}</span>
                </span>
                <svg
                    class="w-4 h-4 transition-transform"
                    :class="{ 'rotate-180': isMenuOpen }"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <!-- Выпадающее меню -->
            <transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="transform scale-95 opacity-0"
                enter-to-class="transform scale-100 opacity-100"
                leave-active-class="transition duration-100 ease-in"
                leave-from-class="transform scale-100 opacity-100"
                leave-to-class="transform scale-95 opacity-0"
            >
                <div
                    v-if="isMenuOpen"
                    class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 border"
                >
                    <div class="px-4 py-2 border-b">
                        <p class="text-sm font-medium text-gray-900">{{ displayName }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ userEmail }}</p>
                    </div>
                    <Link
                        href="/admin/profile"
                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                        @click="closeMenu"
                    >
                        👤 Профиль
                    </Link>
                    <Link
                        href="/admin/settings"
                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                        @click="closeMenu"
                    >
                        ⚙️ Настройки
                    </Link>
                    <hr class="my-1">
                    <button
                        @click="handleLogout"
                        class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100"
                    >
                        🚪 Выйти
                    </button>
                </div>
            </transition>
        </div>
    </nav>
</template>
