<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/Card.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);
const stats = computed(() => page.props.stats);
const expiringBatches = computed(() => page.props.expiringBatches);
const lowStockItems = computed(() => page.props.lowStockItems);
const recentMovements = computed(() => page.props.recentMovements);

const colorClasses = (color) => {
    const map = {
        lime: 'text-lime',
        'violet-text': 'text-violet-text',
        'yellow-400': 'text-yellow-400',
        'red-400': 'text-red-400',
    };
    return map[color] || 'text-lime';
};

const bgColorClasses = (color) => {
    const map = {
        lime: 'bg-lime/10',
        'violet-text': 'bg-violet-text/10',
        'yellow-400': 'bg-yellow-400/10',
        'red-400': 'bg-red-400/10',
    };
    return map[color] || 'bg-lime/10';
};

const getIcon = (iconName) => {
    const icons = {
        package: '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a6 6 0 006.364 0l-1.548-.773a1 1 0 01-.54-1.06l.74-4.435A1 1 0 018.847 2H11a1 1 0 011 1v2h2V3a3 3 0 00-3-3H3a3 3 0 00-3 3v10a3 3 0 003 3h2v-2H3a1 1 0 01-1-1V3z"/></svg>',
        wallet: '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a1 1 0 100-2 1 1 0 000 2z"/></svg>',
        alert: '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>',
        clock: '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00-.293.707l-1.414 1.414a1 1 0 101.414 1.414L9 9.414V6z" clip-rule="evenodd"/></svg>',
        trending: '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/></svg>',
        chart: '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/></svg>',
    };
    return icons[iconName] || icons.package;
};

const getMovementTypeLabel = (type) => {
    const labels = {
        'in': 'Masuk',
        'out': 'Keluar',
        'adjust': 'Penyesuaian',
    };
    return labels[type] || type;
};

const getMovementTypeColor = (type) => {
    const colors = {
        'in': 'text-lime',
        'out': 'text-red-400',
        'adjust': 'text-yellow-400',
    };
    return colors[type] || 'text-ink';
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <div class="bg-canvas min-h-screen py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
                    <div>
                        <p class="text-muted text-sm">Selamat pagi</p>
                        <h1 class="text-display font-bold text-3xl sm:text-4xl text-ink">{{ user.name }}</h1>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                        <SecondaryButton class="w-full sm:w-auto">Pencarian Global</SecondaryButton>
                        <PrimaryButton as="link" :href="route('stock-ins.create')" class="w-full sm:w-auto">+ Barang Masuk</PrimaryButton>
                    </div>
                </div>

                <!-- Stat Cards Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                    <Card v-for="(stat, idx) in stats" :key="idx" class="flex flex-col justify-between relative overflow-hidden group hover:shadow-hard transition">
                        <!-- Background accent -->
                        <div :class="['absolute top-0 right-0 w-24 h-24 rounded-full opacity-20 group-hover:opacity-30 transition', bgColorClasses(stat.color)]"></div>
                        
                        <div class="relative z-10">
                            <div class="text-muted text-xs font-semibold mb-3 flex items-center gap-2">
                                <span :class="['w-5 h-5 flex items-center justify-center', colorClasses(stat.color)]" v-html="getIcon(stat.icon)"></span>
                                {{ stat.label }}
                            </div>
                            <div class="flex items-baseline gap-2 mb-3">
                                <div :class="['text-4xl font-bold font-display', colorClasses(stat.color)]">{{ stat.value }}</div>
                            </div>
                            <div :class="['h-1 w-12 rounded-full', `bg-${stat.color === 'lime' ? 'lime' : stat.color === 'violet-text' ? 'violet' : 'yellow'}`]"></div>
                        </div>
                    </Card>
                </div>

                <!-- Charts & Quick Lists -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <!-- Grafik Masuk vs Keluar -->
                    <Card class="relative overflow-hidden">
                        <div class="absolute top-0 left-0 w-1 h-full bg-lime"></div>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="w-5 h-5 text-lime" v-html="getIcon('trending')"></span>
                            <h2 class="text-xl font-bold text-ink font-display">Masuk vs Keluar (30 hari)</h2>
                        </div>
                        <div class="h-48 bg-surface-2 flex items-center justify-center text-muted text-sm rounded border-2 border-line">
                            Grafik (fase 3)
                        </div>
                    </Card>

                    <!-- Segera Kedaluwarsa -->
                    <Card class="relative overflow-hidden">
                        <div class="absolute top-0 left-0 w-1 h-full bg-red-400"></div>
                        <div class="flex justify-between items-center mb-4">
                            <div class="flex items-center gap-2">
                                <span class="w-5 h-5 text-red-400" v-html="getIcon('clock')"></span>
                                <h2 class="text-xl font-bold text-ink font-display">Segera Kedaluwarsa</h2>
                            </div>
                            <a href="#" class="text-violet-text text-sm font-semibold hover:underline">Lihat semua</a>
                        </div>
                        <div v-if="expiringBatches.length === 0" class="text-muted text-sm py-8 text-center">
                            <div class="text-3xl mb-2" v-html="getIcon('package')"></div>
                            Belum ada batch yang mendekati kedaluwarsa. Bagus!
                        </div>
                        <div v-else class="space-y-2">
                            <div v-for="batch in expiringBatches" :key="batch.batch" class="flex justify-between items-center p-3 bg-surface-2 rounded border-l-2 border-red-400">
                                <div>
                                    <p class="text-sm font-semibold text-ink">{{ batch.product }}</p>
                                    <p class="text-xs text-muted">{{ batch.batch }}</p>
                                </div>
                                <span :class="batch.daysLeft <= 7 ? 'text-red-400 font-bold' : 'text-yellow-400'">{{ batch.daysLeft }} hari</span>
                            </div>
                        </div>
                    </Card>
                </div>

                <!-- Low Stock & Recent Movements -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Stok Menipis -->
                    <Card class="relative overflow-hidden">
                        <div class="absolute top-0 left-0 w-1 h-full bg-yellow-400"></div>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="w-5 h-5 text-yellow-400" v-html="getIcon('alert')"></span>
                            <h2 class="text-xl font-bold text-ink font-display">Stok Menipis</h2>
                        </div>
                        <div v-if="lowStockItems.length === 0" class="text-muted text-sm py-8 text-center">
                            <div class="text-3xl mb-2" v-html="getIcon('package')"></div>
                            Tidak ada barang dengan stok menipis
                        </div>
                        <div v-else class="space-y-2">
                            <div v-for="item in lowStockItems" :key="item.name" class="flex justify-between items-center p-3 bg-surface-2 rounded border-l-2 border-yellow-400">
                                <div>
                                    <p class="text-sm font-semibold text-ink">{{ item.name }}</p>
                                    <p class="text-xs text-muted">{{ item.current }} / {{ item.minimum }} unit</p>
                                </div>
                                <span class="text-red-400 font-bold text-sm">-{{ item.shortage }}</span>
                            </div>
                        </div>
                    </Card>

                    <!-- Mutasi Terbaru -->
                    <Card class="relative overflow-hidden">
                        <div class="absolute top-0 left-0 w-1 h-full bg-violet-text"></div>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="w-5 h-5 text-violet-text" v-html="getIcon('chart')"></span>
                            <h2 class="text-xl font-bold text-ink font-display">Mutasi Terbaru</h2>
                        </div>
                        <div v-if="recentMovements.length === 0" class="text-muted text-sm py-8 text-center">
                            <div class="text-3xl mb-2" v-html="getIcon('package')"></div>
                            Belum ada transaksi
                        </div>
                        <div v-else class="space-y-2">
                            <div v-for="(movement, idx) in recentMovements" :key="idx" class="flex justify-between items-center p-3 bg-surface-2 rounded border-l-2 border-violet-text">
                                <div>
                                    <p class="text-sm font-semibold text-ink">{{ movement.product }}</p>
                                    <p :class="['text-xs', getMovementTypeColor(movement.type)]">{{ getMovementTypeLabel(movement.type) }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-semibold text-ink">{{ movement.quantity }}</p>
                                    <p class="text-xs text-muted">{{ new Date(movement.date).toLocaleDateString('id-ID', { month: 'short', day: 'numeric' }) }}</p>
                                </div>
                            </div>
                        </div>
                    </Card>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
