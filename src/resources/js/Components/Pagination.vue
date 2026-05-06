<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
    links: Array
})
</script>

<template>
    <div v-if="links.length > 3" class="flex flex-wrap justify-center space-x-1">
        <div v-for="(link, index) in links" :key="index">
            <Link
                v-if="link.url"
                :href="link.url"
                class="px-3 py-2 rounded-md text-sm font-medium"
                :class="{
          'bg-blue-600 text-white': link.active,
          'bg-white text-gray-700 hover:bg-gray-50': !link.active
        }"
                v-html="link.label"
            />
            <span
                v-else
                class="px-3 py-2 rounded-md text-sm font-medium text-gray-400 bg-gray-100"
                v-html="link.label"
            />
        </div>
    </div>
</template>
