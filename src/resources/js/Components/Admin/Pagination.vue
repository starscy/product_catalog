<script setup>
defineProps({ links: Array })
</script>

<template>
    <div class="flex justify-center gap-1">
        <template v-for="(link, i) in links" :key="i">
            <component
                :is="link.url ? 'Link' : 'span'"
                :href="link.url"
                class="px-3 py-1 text-sm rounded border transition"
                :class="{
                    'bg-blue-600 text-white border-blue-600': link.active,
                    'bg-white text-gray-700 border-gray-300 hover:bg-gray-50': !link.active && link.url,
                    'bg-gray-100 text-gray-400 border-gray-200 cursor-not-allowed': !link.url
                }"
                v-html="link.label"
            />
        </template>
    </div>
</template>
