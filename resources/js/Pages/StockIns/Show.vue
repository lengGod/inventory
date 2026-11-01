<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/Card.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import StatusChip from '@/Components/StatusChip.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

defineProps({
    stockIn: {
        type: Object,
        required: true,
    },
});

const form = useForm({});

const post = () => {
    if (confirm('Posting barang masuk ini? Stok akan bertambah.')) {
        form.post(route('stock-ins.post', props.stockIn.id));
    }
};
</script>

<template>
    <Head title="Barang Masuk Detail" />

    <AuthenticatedLayout>
        <div class="bg-canvas min-h-screen py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-8">
                    <div>
                        <p class="text-muted text-sm">Transaksi / Barang Masuk</p>
                        <h1 class="text-display font-bold text-2xl sm:text-3xl text-ink break-all">{{ stockIn.doc_number }}</h1>
                        <div class="mt-2">
                            <StatusChip :status="stockIn.status === 'posted' ? 'ok' : 'warn'">
                                {{ stockIn.status === 'draft' ? 'Draft' : stockIn.status === 'posted' ? 'Posted' : stockIn.status }}
                            </StatusChip>
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                        <PrimaryButton v-if="stockIn.status === 'draft'" @click="post" :loading="form.processing" class="w-full sm:w-auto">Posting Stok</PrimaryButton>
                        <SecondaryButton as="link" :href="route('stock-ins.index')" class="w-full sm:w-auto">Kembali</SecondaryButton>
                    </div>
                </div>

                <!-- Info -->
                <Card class="mb-6">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                        <div>
                            <p class="text-muted text-xs font-semibold mb-1">Tanggal</p>
                            <p class="text-sm">{{ stockIn.date }}</p>
                        </div>
                        <div>
                            <p class="text-muted text-xs font-semibold mb-1">Supplier</p>
                            <p class="text-sm">{{ stockIn.supplier?.name || '-' }}</p>
                        </div>
                        <div>
                            <p class="text-muted text-xs font-semibold mb-1">Dibuat Oleh</p>
                            <p class="text-sm">{{ stockIn.creator?.name }}</p>
                        </div>
                        <div>
                            <p class="text-muted text-xs font-semibold mb-1">Diposting</p>
                            <p class="text-sm">{{ stockIn.posted_at || '-' }}</p>
                        </div>
                    </div>
                    <div v-if="stockIn.notes" class="mt-4 pt-4 border-t border-line">
                        <p class="text-muted text-xs font-semibold mb-1">Catatan</p>
                        <p class="text-sm">{{ stockIn.notes }}</p>
                    </div>
                </Card>

                <!-- Items -->
                <Card>
                    <h2 class="text-xl font-bold text-ink font-display mb-4">Item</h2>
                    <div v-if="!stockIn.items || stockIn.items.length === 0" class="text-center py-8">
                        <p class="text-muted text-sm">Belum ada item</p>
                    </div>
                    <div v-else class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b-2 border-line">
                                    <th class="text-left py-3 px-4 font-semibold text-muted">Barang</th>
                                    <th class="text-left py-3 px-4 font-semibold text-muted">Lokasi</th>
                                    <th class="text-center py-3 px-4 font-semibold text-muted">Qty</th>
                                    <th class="text-right py-3 px-4 font-semibold text-muted">Harga/Unit</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in stockIn.items" :key="item.id" class="border-b border-surface-2">
                                    <td class="py-3 px-4">{{ item.product?.name }}</td>
                                    <td class="py-3 px-4">{{ item.location?.code }}</td>
                                    <td class="py-3 px-4 text-center">{{ item.qty }}</td>
                                    <td class="py-3 px-4 text-right">{{ item.unit_price || '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
