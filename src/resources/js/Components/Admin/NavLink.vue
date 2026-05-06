<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
    href: {
        type: String,
        required: true
    },
    active: {
        type: Boolean,
        default: false
    }
})
</script>

<template>
    <Link
        :href="href"
        class="py-2 px-3 border-b-2 font-medium text-sm transition-all duration-200"
        :class="active
            ? 'border-blue-500 text-blue-600'
            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
    >
        <slot />
    </Link>
</template>
