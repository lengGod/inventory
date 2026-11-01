<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/Card.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { useAuthorization } from '@/Utils/authorization';
import { Head, useForm } from '@inertiajs/vue3';
import { formatDateTime, formatNumber } from '@/Utils/format';

const { can } = useAuthorization();

const props = defineProps({
    stockOut: Object,
});

const totalQty = () => props.stockOut.items.reduce((sum, item) => sum + parseFloat(item.quantity), 0);

const form = useForm({});

const post = () => {
    form.post(route('stock-outs.post', props.stockOut.id));
};

const canPost = () => props.stockOut.status === 'draft';
</script>

<template>
    <Head title="Detail Barang Keluar" />

    <AuthenticatedLayout>
        <div class="bg-canvas min-h-screen py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
                    <div>
                        <h1 class="text-display font-bold text-2xl sm:text-3xl text-ink break-all">{{ stockOut.doc_number }}</h1>
                        <p class="text-muted text-sm mt-1">Dibuat {{ formatDateTime(stockOut.created_at) }}</p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                        <PrimaryButton v-if="can('manage_transactions') && canPost()" @click="post" :loading="form.processing" class="w-full sm:w-auto">Post Barang Keluar</PrimaryButton>
                        <SecondaryButton as="link" :href="route('stock-outs.index')" class="w-full sm:w-auto">Kembali</SecondaryButton>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Detail -->
                    <div class="lg:col-span-2 space-y-4">
                        <!-- Status Card -->
                        <Card>
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-muted">Status</p>
                                    <p class="text-2xl font-bold text-ink font-display">
                                        {{ stockOut.status === 'draft' ? 'Draft' : stockOut.status === 'posted' ? 'Posted' : 'Dibatalkan' }}
                                    </p>
                                </div>
                                <span :class="['px-4 py-2 rounded font-semibold', stockOut.status === 'draft' ? 'bg-surface-2 text-muted' : stockOut.status === 'posted' ? 'bg-lime/10 text-lime' : 'bg-red-400/10 text-red-400']">
                                    {{ stockOut.status === 'draft' ? '●' : '✓' }}
                                </span>
                            </div>
                        </Card>

                        <!-- Items -->
                        <Card>
                            <h2 class="text-xl font-bold text-ink font-display mb-4">Item Barang Keluar</h2>
                            
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead class="border-b-2 border-line">
                                        <tr>
                                            <th class="text-left py-3 px-4 font-semibold text-ink">Barang</th>
                                            <th class="text-left py-3 px-4 font-semibold text-ink">Batch</th>
                                            <th class="text-center py-3 px-4 font-semibold text-ink">Qty</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="item in stockOut.items" :key="item.id" class="border-b border-line">
                                            <td class="py-3 px-4 font-semibold text-ink">{{ item.product_name }}</td>
                                            <td class="py-3 px-4 text-muted">{{ item.batch_number }}</td>
                                            <td class="py-3 px-4 text-center">{{ formatNumber(item.quantity) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </Card>

                        <!-- Notes -->
                        <Card v-if="stockOut.notes">
                            <h2 class="text-lg font-bold text-ink font-display mb-2">Catatan</h2>
                            <p class="text-muted text-sm">{{ stockOut.notes }}</p>
                        </Card>
                    </div>

                    <!-- Summary -->
                    <div>
                        <Card class="sticky top-[7rem]">
                            <h2 class="text-xl font-bold text-ink font-display mb-4">Ringkasan</h2>
                            
                            <div class="space-y-3 pb-6 border-b-2 border-line mb-6">
                                <div class="flex justify-between">
                                    <span class="text-muted">Total Item</span>
                                    <span class="text-lg font-bold text-lime">{{ stockOut.items.length }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-muted">Total Qty</span>
                                    <span class="text-lg font-bold text-lime">{{ formatNumber(totalQty()) }}</span>
                                </div>
                            </div>

                            <div v-if="stockOut.status === 'posted'" class="bg-lime/10 px-4 py-3 rounded border-l-2 border-lime">
                                <p class="text-sm font-semibold text-lime">Posted {{ formatDateTime(stockOut.posted_at) }}</p>
                            </div>
                        </Card>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
