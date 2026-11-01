<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/Card.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Pagination from '@/Components/Pagination.vue';
import ReportTabs from '@/Components/ReportTabs.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { formatDateTime, formatNumber } from '@/Utils/format';

const props = defineProps({
    movements: {
        type: Object,
        default: {},
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

const form = ref({
    date_from: props.filters.date_from ?? '',
    date_to: props.filters.date_to ?? '',
    type: props.filters.type ?? '',
    search: props.filters.search ?? '',
});

const searching = ref(false);

const typeOptions = [
    { value: '', label: 'Semua Tipe' },
    { value: 'in', label: 'Masuk' },
    { value: 'out', label: 'Keluar' },
    { value: 'adjust', label: 'Penyesuaian' },
    { value: 'void', label: 'Void' },
];

const typeLabel = {
    in: 'Masuk',
    out: 'Keluar',
    adjust: 'Penyesuaian',
    void: 'Void',
};

const typeClass = {
    in: 'text-lime',
    out: 'text-red-400',
    adjust: 'text-violet-text',
    void: 'text-muted',
};

const debounce = (fn, delay = 300) => {
    let timer = null;
    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => fn(...args), delay);
    };
};

const submit = () => {
    router.get(route('reports.movements'), { ...form.value }, {
        preserveState: true,
        preserveScroll: true,
        onStart: () => { searching.value = true; },
        onFinish: () => { searching.value = false; },
    });
};

const reset = () => {
    form.value = { date_from: '', date_to: '', type: '', search: '' };
    submit();
};

watch(() => form.value.search, debounce(submit));
watch(() => form.value.type, submit);

const signedQty = (qty) => {
    const value = Number(qty ?? 0);
    return `${value > 0 ? '+' : ''}${formatNumber(value)}`;
};
</script>

<template>
    <Head title="Laporan Mutasi Stok" />

    <AuthenticatedLayout>
        <div class="bg-canvas min-h-screen py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <p class="text-muted text-sm">Inventori / Laporan</p>
                    <h1 class="text-display font-bold text-3xl text-ink">Mutasi Stok</h1>
                </div>

                <ReportTabs />

                <!-- Summary -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <Card class="border-l-4 border-ink">
                        <p class="text-muted text-xs font-semibold mb-2">Total Transaksi</p>
                        <p class="text-2xl font-bold text-ink">{{ formatNumber(summary.total_rows, 0) }}</p>
                    </Card>
                    <Card class="border-l-4 border-lime">
                        <p class="text-muted text-xs font-semibold mb-2">Total Masuk</p>
                        <p class="text-2xl font-bold text-lime">{{ formatNumber(summary.total_in) }}</p>
                    </Card>
                    <Card class="border-l-4 border-red-400">
                        <p class="text-muted text-xs font-semibold mb-2">Total Keluar</p>
                        <p class="text-2xl font-bold text-red-400">{{ formatNumber(summary.total_out) }}</p>
                    </Card>
                    <Card class="border-l-4 border-violet-text">
                        <p class="text-muted text-xs font-semibold mb-2">Net Penyesuaian</p>
                        <p class="text-2xl font-bold text-violet-text">{{ signedQty(summary.total_adjust) }}</p>
                    </Card>
                </div>

                <!-- Toolbar -->
                <Card class="mb-6">
                    <form @submit.prevent="submit" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-muted mb-1">Dari Tanggal</label>
                            <input v-model="form.date_from" type="date" class="w-full h-12 px-3 border-2 border-line text-sm focus:border-ink focus:outline-none" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-muted mb-1">Sampai Tanggal</label>
                            <input v-model="form.date_to" type="date" class="w-full h-12 px-3 border-2 border-line text-sm focus:border-ink focus:outline-none" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-muted mb-1">Tipe</label>
                            <select v-model="form.type" class="w-full h-12 px-3 border-2 border-line text-sm focus:border-ink focus:outline-none">
                                <option v-for="option in typeOptions" :key="option.value" :value="option.value">
                                    {{ option.label }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-muted mb-1">Cari</label>
                            <input v-model="form.search" type="text" placeholder="Barang / batch..." class="w-full h-12 px-3 border-2 border-line text-sm focus:border-ink focus:outline-none" />
                        </div>
                        <div class="flex flex-wrap items-end gap-2 sm:col-span-2 lg:col-span-4">
                            <PrimaryButton type="submit" :loading="searching" class="flex-1 sm:flex-none">Terapkan</PrimaryButton>
                            <PrimaryButton as="link" :href="route('reports.movements.export', form)" class="flex-1 sm:flex-none">Ekspor CSV</PrimaryButton>
                            <SecondaryButton type="button" class="flex-1 sm:flex-none" @click="reset">Reset</SecondaryButton>
                        </div>
                    </form>
                </Card>

                <!-- Table -->
                <Card>
                    <div v-if="!movements.data || movements.data.length === 0" class="text-center py-7">
                        <p class="text-muted text-sm">Tidak ada mutasi stok pada rentang/filter ini.</p>
                    </div>
                    <div v-else class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b-2 border-line">
                                    <th class="text-left py-3 px-4 font-semibold text-muted">Waktu</th>
                                    <th class="text-left py-3 px-4 font-semibold text-muted">Barang</th>
                                    <th class="text-left py-3 px-4 font-semibold text-muted">Tipe</th>
                                    <th class="text-right py-3 px-4 font-semibold text-muted">Qty</th>
                                    <th class="text-right py-3 px-4 font-semibold text-muted">Saldo</th>
                                    <th class="text-left py-3 px-4 font-semibold text-muted">Lokasi</th>
                                    <th class="text-left py-3 px-4 font-semibold text-muted">Batch</th>
                                    <th class="text-left py-3 px-4 font-semibold text-muted">Oleh</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="movement in movements.data" :key="movement.id" class="border-b border-surface-2 hover:bg-surface-2">
                                    <td class="py-3 px-4 whitespace-nowrap text-muted text-xs">{{ formatDateTime(movement.date) }}</td>
                                    <td class="py-3 px-4">
                                        <p class="font-semibold">{{ movement.product }}</p>
                                        <p class="text-muted text-xs font-mono">{{ movement.sku }}</p>
                                    </td>
                                    <td :class="['py-3 px-4 text-xs font-semibold', typeClass[movement.type]]">{{ typeLabel[movement.type] || movement.type }}</td>
                                    <td :class="['py-3 px-4 text-right font-bold', movement.qty > 0 ? 'text-lime' : 'text-red-400']">{{ signedQty(movement.qty) }}</td>
                                    <td class="py-3 px-4 text-right">{{ formatNumber(movement.balance_after) }}</td>
                                    <td class="py-3 px-4 text-muted">{{ movement.location || '-' }}</td>
                                    <td class="py-3 px-4 text-muted text-xs">{{ movement.batch || '-' }}</td>
                                    <td class="py-3 px-4 text-muted text-xs">{{ movement.creator || '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <Pagination :paginator="movements" />
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
