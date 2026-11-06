<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/Card.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Pagination from '@/Components/Pagination.vue';
import ReportTabs from '@/Components/ReportTabs.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { formatNumber } from '@/Utils/format';

const props = defineProps({
    products: {
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

const search = ref(props.filters.search ?? '');
const searching = ref(false);

const debounce = (fn, delay = 300) => {
    let timer = null;
    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => fn(...args), delay);
    };
};

const submit = () => {
    router.get(route('reports.low-stock'), { search: search.value }, {
        preserveState: true,
        preserveScroll: true,
        onStart: () => { searching.value = true; },
        onFinish: () => { searching.value = false; },
    });
};

const reset = () => {
    search.value = '';
    submit();
};

watch(search, debounce(submit));
</script>

<template>
    <Head title="Laporan Stok Menipis" />

    <AuthenticatedLayout>
        <div class="bg-canvas min-h-screen py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <p class="text-muted text-sm">Inventori / Laporan</p>
                    <h1 class="text-display font-bold text-3xl text-ink">Stok Menipis</h1>
                </div>

                <ReportTabs />

                <!-- Summary -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                    <Card class="border-l-4 border-yellow-400">
                        <p class="text-muted text-xs font-semibold mb-2">Barang di Bawah Minimum</p>
                        <p class="text-2xl font-bold text-ink">{{ formatNumber(summary.count, 0) }}</p>
                    </Card>
                    <Card class="border-l-4 border-red-400">
                        <p class="text-muted text-xs font-semibold mb-2">Total Kekurangan</p>
                        <p class="text-2xl font-bold text-ink">{{ formatNumber(summary.total_shortage) }}</p>
                    </Card>
                </div>

                <!-- Toolbar -->
                <Card class="mb-6">
                    <form @submit.prevent="submit" class="flex flex-col sm:flex-row gap-3">
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Cari nama atau SKU..."
                            class="flex-1 h-12 px-4 border-2 border-line text-sm focus:border-ink focus:outline-none"
                        />
                        <div class="flex gap-2">
                            <PrimaryButton type="submit" :loading="searching" class="flex-1 sm:flex-none">Cari</PrimaryButton>
                            <PrimaryButton as="link" :href="route('reports.low-stock.export', { search })" class="flex-1 sm:flex-none">Ekspor CSV</PrimaryButton>
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
                                    <th class="text-left py-3 px-4 font-semibold text-muted">SKU</th>
                                    <th class="text-left py-3 px-4 font-semibold text-muted">Nama Barang</th>
                                    <th class="text-left py-3 px-4 font-semibold text-muted">Kategori</th>
                                    <th class="text-right py-3 px-4 font-semibold text-muted">Stok Saat Ini</th>
                                    <th class="text-right py-3 px-4 font-semibold text-muted">Stok Minimum</th>
                                    <th class="text-right py-3 px-4 font-semibold text-muted">Kekurangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="!products.data || products.data.length === 0">
                                    <td colspan="6" class="px-4 py-10 text-center text-sm text-muted">Tidak ada barang dengan stok menipis.</td>
                                </tr>
                                <tr v-for="product in products.data" :key="product.id" class="border-b border-surface-2 hover:bg-surface-2">
                                    <td class="py-3 px-4 font-mono text-xs">{{ product.sku }}</td>
                                    <td class="py-3 px-4 font-semibold">{{ product.name }}</td>
                                    <td class="py-3 px-4 text-muted">{{ product.category }}</td>
                                    <td class="py-3 px-4 text-right font-semibold text-yellow-500">{{ formatNumber(product.total_qty) }} {{ product.unit }}</td>
                                    <td class="py-3 px-4 text-right text-muted">{{ formatNumber(product.min_stock) }}</td>
                                    <td class="py-3 px-4 text-right font-bold text-red-400">{{ formatNumber(product.shortage) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <Pagination :paginator="products" />
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
