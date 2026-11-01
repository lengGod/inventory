<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    as: {
        type: String,
        default: 'button',
    },
    href: String,
    disabled: Boolean,
    loading: Boolean,
});
</script>

<template>
    <Link
        v-if="as === 'link'"
        :href="href"
        class="inline-flex items-center justify-center gap-2 h-12 px-6 text-sm font-semibold text-white bg-ink hover:bg-ink-3 transition duration-150 ease-in-out focus:outline-lime focus:outline-3 focus:outline-offset-2 active:bg-ink"
    >
        <slot />
    </Link>
    <button
        v-else
        :disabled="disabled || loading"
        class="inline-flex items-center justify-center gap-2 h-12 px-6 text-sm font-semibold text-white bg-ink hover:bg-ink-3 transition duration-150 ease-in-out focus:outline-lime focus:outline-3 focus:outline-offset-2 active:bg-ink disabled:opacity-50 disabled:cursor-not-allowed"
    >
        <svg v-if="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
        </svg>
        <slot />
    </button>
</template>