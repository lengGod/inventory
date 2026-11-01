<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/Card.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ChartColumn, Clock, TriangleAlert, Wallet } from '@lucide/vue';
import { formatNumber, formatRupiah } from '@/Utils/format';

defineProps({
    summary: {
        type: Object,
        default: {},
    },
});

const reports = [
    {
        title: 'Laporan Stok',
        description: 'Posisi stok seluruh barang beserta nilai persediaan.',
        route: 'reports.stock',
        accent: 'text-violet-text',
        icon: Wallet,
    },
    {
        title: 'Stok Menipis',
        description: 'Barang yang kuantitasnya sudah di bawah stok minimum.',
        route: 'reports.low-stock',
        accent: 'text-yellow-400',
        icon: TriangleAlert,
    },
    {
        title: 'Kedaluwarsa',
        description: 'Batch yang sudah kedaluwarsa maupun mendekati kedaluwarsa.',
        route: 'reports.expiry',
        accent: 'text-red-400',
        icon: Clock,
    },
    {
        title: 'Mutasi Stok',
        description: 'Riwayat lengkap pergerakan stok masuk, keluar, dan penyesuaian.',
        route: 'reports.movements',
        accent: 'text-lime',
        icon: ChartColumn,
    },
];

const stats = [
    { key: 'total_sku', label: 'Total SKU Aktif', type: 'number' },
    { key: 'total_qty', label: 'Total Kuantitas Stok', type: 'number' },
    { key: 'total_value', label: 'Nilai Persediaan', type: 'currency' },
    { key: 'low_stock', label: 'Barang Stok Menipis', type: 'number' },
    { key: 'near_expiry', label: 'Batch Akan Kedaluwarsa', type: 'number' },
    { key: 'expired', label: 'Batch Kedaluwarsa', type: 'number' },
];
</script>

<template>
    <Head title="Laporan" />

    <AuthenticatedLayout>
        <div class="bg-canvas min-h-screen py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
                    <div>
                        <p class="text-muted text-sm">Inventori</p>
                        <h1 class="text-display font-bold text-3xl text-ink">Laporan</h1>
                        <p class="text-muted text-sm mt-1">Ringkasan kondisi persediaan gudang saat ini</p>
                    </div>
                </div>

                <!-- Summary -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
                    <Card v-for="stat in stats" :key="stat.key" class="border-l-4 border-lime">
                        <p class="text-muted text-xs font-semibold mb-2">{{ stat.label }}</p>
                        <p class="text-2xl font-bold text-ink">
                            {{ stat.type === 'currency' ? formatRupiah(summary[stat.key]) : formatNumber(summary[stat.key], 0) }}
                        </p>
                    </Card>
                </div>

                <!-- Report cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <Link v-for="report in reports" :key="report.route" :href="route(report.route)">
                        <Card class="h-full flex items-start gap-4 hover:border-lime transition group">
                            <div :class="['w-11 h-11 flex-shrink-0 flex items-center justify-center bg-surface-2', report.accent]">
                                <component :is="report.icon" class="w-5 h-5" />
                            </div>
                            <div class="flex-1">
                                <p class="font-bold text-ink group-hover:text-violet-text transition">{{ report.title }}</p>
                                <p class="text-muted text-sm mt-1">{{ report.description }}</p>
                                <p class="text-xs font-semibold text-violet-text mt-3">Buka laporan &rarr;</p>
                            </div>
                        </Card>
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
