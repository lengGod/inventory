<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/Card.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import StatusChip from '@/Components/StatusChip.vue';
import { useAuthorization } from '@/Utils/authorization';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { formatDate, formatNumber, formatRupiah } from '@/Utils/format';

const { can } = useAuthorization();

defineProps({
    product: {
        type: Object,
        required: true,
    },
});

const activeTab = ref('stocks');

const formatCurrency = (val) => {
    return formatRupiah(val);
};
</script>

<template>
    <Head :title="product.name" />

    <AuthenticatedLayout>
        <div class="bg-canvas min-h-screen py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-8">
                    <div>
                        <p class="text-muted text-sm">Master Data / Barang</p>
                        <h1 class="text-display font-bold text-2xl sm:text-3xl text-ink break-words">{{ product.name }}</h1>
                        <p class="text-muted text-sm mt-2">{{ product.sku }}</p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                        <PrimaryButton v-if="can('manage_master_data')" as="link" :href="route('products.edit', product.id)" class="w-full sm:w-auto">Ubah</PrimaryButton>
                        <SecondaryButton as="link" :href="route('products.index')" class="w-full sm:w-auto">Kembali</SecondaryButton>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                    <Card>
                        <p class="text-muted text-xs font-semibold mb-2">Stok Total</p>
                        <p class="text-3xl font-bold text-ink font-display">0</p>
                        <p class="text-muted text-xs mt-2">{{ product.unit?.short_name }}</p>
                    </Card>
                    <Card>
                        <p class="text-muted text-xs font-semibold mb-2">Batch Aktif</p>
                        <p class="text-3xl font-bold text-ink font-display">{{ product.batches?.length || 0 }}</p>
                    </Card>
                    <Card>
                        <p class="text-muted text-xs font-semibold mb-2">Kategori</p>
                        <p class="text-lg font-semibold text-ink">{{ product.category?.name }}</p>
                    </Card>
                </div>

                <!-- Info Card -->
                <Card class="mb-8">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                        <div>
                            <p class="text-muted text-xs font-semibold mb-1">Barcode</p>
                            <p class="text-sm font-mono">{{ product.barcode || '-' }}</p>
                        </div>
                        <div>
                            <p class="text-muted text-xs font-semibold mb-1">Stok Minimum</p>
                            <p class="text-sm">{{ formatNumber(product.min_stock) }} {{ product.unit?.short_name }}</p>
                        </div>
                        <div>
                            <p class="text-muted text-xs font-semibold mb-1">Harga Beli</p>
                            <p class="text-sm">{{ formatCurrency(product.purchase_price) }}</p>
                        </div>
                        <div>
                            <p class="text-muted text-xs font-semibold mb-1">Track Batch</p>
                            <p class="text-sm">{{ product.track_batch ? '✓ Ya' : '-' }}</p>
                        </div>
                    </div>
                </Card>

                <!-- Tabs -->
                <Card>
                    <div class="flex gap-4 border-b-2 border-line mb-6">
                        <button
                            @click="activeTab = 'stocks'"
                            :class="[
                                'px-4 py-3 text-sm font-semibold border-b-3 -mb-2 transition',
                                activeTab === 'stocks'
                                    ? 'text-ink border-b-lime'
                                    : 'text-muted border-b-transparent hover:text-ink',
                            ]"
                        >
                            Kartu Stok
                        </button>
                        <button
                            @click="activeTab = 'batches'"
                            :class="[
                                'px-4 py-3 text-sm font-semibold border-b-3 -mb-2 transition',
                                activeTab === 'batches'
                                    ? 'text-ink border-b-lime'
                                    : 'text-muted border-b-transparent hover:text-ink',
                            ]"
                        >
                            Batch ({{ product.batches?.length || 0 }})
                        </button>
                        <button
                            @click="activeTab = 'locations'"
                            :class="[
                                'px-4 py-3 text-sm font-semibold border-b-3 -mb-2 transition',
                                activeTab === 'locations'
                                    ? 'text-ink border-b-lime'
                                    : 'text-muted border-b-transparent hover:text-ink',
                            ]"
                        >
                            Lokasi
                        </button>
                    </div>

                    <!-- Tab: Kartu Stok -->
                    <div v-show="activeTab === 'stocks'" class="space-y-4">
                        <div v-if="!product.stocks || product.stocks.length === 0" class="text-center py-8">
                            <p class="text-muted text-sm">Belum ada mutasi stok</p>
                        </div>
                        <div v-else class="text-center py-8">
                            <p class="text-muted text-sm">Kartu stok (fase 3 dengan filter periode)</p>
                        </div>
                    </div>

                    <!-- Tab: Batch -->
                    <div v-show="activeTab === 'batches'" class="space-y-4">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b-2 border-line">
                                        <th class="text-left py-3 px-4 font-semibold text-muted">Nomor Batch</th>
                                        <th class="text-left py-3 px-4 font-semibold text-muted">Kedaluwarsa</th>
                                        <th class="text-left py-3 px-4 font-semibold text-muted">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="!product.batches || product.batches.length === 0">
                                        <td colspan="3" class="px-4 py-10 text-center text-sm text-muted">Belum ada batch</td>
                                    </tr>
                                    <tr v-for="batch in product.batches" :key="batch.id" class="border-b border-surface-2">
                                        <td class="py-4 px-4">{{ batch.batch_number }}</td>
                                        <td class="py-4 px-4">{{ formatDate(batch.expiry_date, '-') }}</td>
                                        <td class="py-4 px-4">
                                            <StatusChip :status="batch.status || 'info'">{{ batch.status }}</StatusChip>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab: Lokasi -->
                    <div v-show="activeTab === 'locations'" class="space-y-4">
                        <div class="text-center py-8">
                            <p class="text-muted text-sm">Stok per lokasi (fase 3)</p>
                        </div>
                    </div>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
