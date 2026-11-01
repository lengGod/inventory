<script setup>
import { Link } from '@inertiajs/vue3';

const tabs = [
    { label: 'Stok', route: 'reports.stock' },
    { label: 'Stok Menipis', route: 'reports.low-stock' },
    { label: 'Kedaluwarsa', route: 'reports.expiry' },
    { label: 'Mutasi Stok', route: 'reports.movements' },
];
</script>

<template>
    <div class="flex gap-2 overflow-x-auto pb-1 mb-6">
        <Link
            v-for="tab in tabs"
            :key="tab.route"
            :href="route(tab.route)"
            :class="[
                'whitespace-nowrap px-4 py-2 text-sm font-semibold border-2 transition',
                route().current(tab.route)
                    ? 'bg-ink text-lime border-ink'
                    : 'bg-surface text-ink border-line hover:border-ink',
            ]"
        >
            {{ tab.label }}
        </Link>
    </div>
</template>
