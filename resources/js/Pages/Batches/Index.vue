<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/Card.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Pagination from '@/Components/Pagination.vue';
import StatusChip from '@/Components/StatusChip.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { formatDate, formatNumber } from '@/Utils/format';

const props = defineProps({
    batches: {
        type: Object,
        default: {},
    },
    products: {
        type: Array,
        default: [],
    },
    filters: {
        type: Object,
        default: {},
    },
    summary: {
        type: Object,
        default: {},
    },
});

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');
const productId = ref(props.filters.product_id ?? '');
const searching = ref(false);

const statusOptions = [
    { value: '', label: 'Semua Status' },
    { value: 'active', label: 'Aktif' },
    { value: 'near_expiry', label: 'Segera Kedaluwarsa' },
    { value: 'expired', label: 'Kedaluwarsa' },
    { value: 'depleted', label: 'Habis' },
];

const statusLabel = {
    expired: 'Kedaluwarsa',
    near_expiry: 'Segera Kedaluwarsa',
    active: 'Aktif',
    depleted: 'Habis',
};

const debounce = (fn, delay) => {
    let timer = null;
    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => fn(...args), delay);
    };
};

const submit = () => {
    router.get(route('batches.index'), {
        search: search.value,
        status: status.value,
        product_id: productId.value,
    }, {
        preserveState: true,
        preserveScroll: true,
        onStart: () => { searching.value = true; },
        onFinish: () => { searching.value = false; },
    });
};

watch([status, productId], () => {
    submit();
});

const reset = () => {
    search.value = '';
    status.value = '';
    productId.value = '';
    submit();
};

watch(search, debounce(submit, 300));

const daysLabel = (batch) => {
    if (batch.days_left === null || batch.days_left === undefined) return '-';
    if (batch.days_left < 0) return `${Math.abs(batch.days_left)} hari lalu`;
    if (batch.days_left === 0) return 'Hari ini';
    return `${batch.days_left} hari`;
};
</script>

<template>
    <Head title="Batch & Kedaluwarsa" />

    <AuthenticatedLayout>
        <div class="bg-canvas min-h-screen py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
                    <div>
                        <p class="text-muted text-sm">Inventori</p>
                        <h1 class="text-display font-bold text-3xl text-ink">Batch &amp; Kedaluwarsa</h1>
                        <p class="text-muted text-sm mt-1">Pantau seluruh batch beserta sisa masa simpannya</p>
                    </div>
                    <SecondaryButton as="link" :href="route('reports.expiry')" class="w-full sm:w-auto">Laporan Kedaluwarsa</SecondaryButton>
                </div>

                <!-- Summary -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <Card class="border-l-4 border-lime">
                        <p class="text-muted text-xs font-semibold mb-2">Aktif</p>
                        <p class="text-2xl font-bold text-ink">{{ formatNumber(summary.active, 0) }}</p>
                    </Card>
                    <Card class="border-l-4 border-yellow-400">
                        <p class="text-muted text-xs font-semibold mb-2">Segera Kedaluwarsa</p>
                        <p class="text-2xl font-bold text-ink">{{ formatNumber(summary.near_expiry, 0) }}</p>
                    </Card>
                    <Card class="border-l-4 border-red-400">
                        <p class="text-muted text-xs font-semibold mb-2">Kedaluwarsa</p>
                        <p class="text-2xl font-bold text-ink">{{ formatNumber(summary.expired, 0) }}</p>
                    </Card>
                    <Card class="border-l-4 border-ink">
                        <p class="text-muted text-xs font-semibold mb-2">Habis</p>
                        <p class="text-2xl font-bold text-ink">{{ formatNumber(summary.depleted, 0) }}</p>
                    </Card>
                </div>

                <!-- Toolbar -->
                <Card class="mb-6">
                    <form @submit.prevent="submit" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Cari barang, SKU, atau batch..."
                            class="h-12 px-4 border-2 border-line text-sm focus:border-ink focus:outline-none lg:col-span-2"
                        />
                        <select v-model="productId" class="h-12 px-4 border-2 border-line text-sm focus:border-ink focus:outline-none">
                            <option value="">Semua Barang</option>
                            <option v-for="product in products" :key="product.id" :value="String(product.id)">
                                {{ product.name }}
                            </option>
                        </select>
                        <select v-model="status" class="h-12 px-4 border-2 border-line text-sm focus:border-ink focus:outline-none">
                            <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                                {{ option.label }}
                            </option>
                        </select>
                        <div class="flex gap-2 sm:col-span-2 lg:col-span-4">
                            <PrimaryButton type="submit" :loading="searching" class="flex-1 sm:flex-none">Terapkan</PrimaryButton>
                            <SecondaryButton type="button" class="flex-1 sm:flex-none" @click="reset">Reset</SecondaryButton>
                        </div>
                    </form>
                </Card>

                <!-- Table -->
                <Card>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b-2 border-line">
                                    <th class="text-left py-3 px-4 font-semibold text-muted">Barang</th>
                                    <th class="text-left py-3 px-4 font-semibold text-muted">Nomor Batch</th>
                                    <th class="text-left py-3 px-4 font-semibold text-muted">Supplier</th>
                                    <th class="text-left py-3 px-4 font-semibold text-muted">Kedaluwarsa</th>
                                    <th class="text-left py-3 px-4 font-semibold text-muted">Sisa Waktu</th>
                                    <th class="text-right py-3 px-4 font-semibold text-muted">Stok</th>
                                    <th class="text-left py-3 px-4 font-semibold text-muted">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="!batches.data || batches.data.length === 0">
                                    <td colspan="7" class="px-4 py-10 text-center text-sm text-muted">Tidak ada batch yang cocok dengan filter.</td>
                                </tr>
                                <tr v-for="batch in batches.data" :key="batch.id" class="border-b border-surface-2 hover:bg-surface-2">
                                    <td class="py-3 px-4">
                                        <Link :href="route('products.show', batch.product_id)" class="font-semibold hover:text-violet-text hover:underline">
                                            {{ batch.product }}
                                        </Link>
                                        <p class="text-muted text-xs font-mono">{{ batch.sku }}</p>
                                    </td>
                                    <td class="py-3 px-4 font-mono text-xs">{{ batch.batch_number }}</td>
                                    <td class="py-3 px-4 text-muted">{{ batch.supplier || '-' }}</td>
                                    <td class="py-3 px-4">{{ formatDate(batch.expiry_date, '-') }}</td>
                                    <td class="py-3 px-4">{{ daysLabel(batch) }}</td>
                                    <td class="py-3 px-4 text-right font-semibold">{{ formatNumber(batch.total_qty) }} {{ batch.unit }}</td>
                                    <td class="py-3 px-4">
                                        <StatusChip :status="batch.status">{{ statusLabel[batch.status] }}</StatusChip>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <Pagination :paginator="batches" />
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
