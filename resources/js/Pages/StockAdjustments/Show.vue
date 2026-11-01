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
    adjustment: Object,
});

const totalQty = () => props.adjustment.items.reduce((sum, item) => sum + parseFloat(item.quantity), 0);

const form = useForm({});

const post = () => {
    form.post(route('stock-adjustments.post', props.adjustment.id));
};

const canPost = () => props.adjustment.status === 'draft';

const getTypeLabel = (type) => {
    return type === 'increase' ? 'Penambahan Stok' : 'Pengurangan Stok';
};

const getTypeColor = (type) => {
    return type === 'increase' ? 'bg-lime/10 text-lime' : 'bg-red-400/10 text-red-400';
};
</script>

<template>
    <Head title="Detail Penyesuaian Stok" />

    <AuthenticatedLayout>
        <div class="bg-canvas min-h-screen py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
                    <div>
                        <h1 class="text-display font-bold text-2xl sm:text-3xl text-ink break-all">{{ adjustment.doc_number }}</h1>
                        <p class="text-muted text-sm mt-1">Dibuat {{ formatDateTime(adjustment.created_at) }}</p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                        <PrimaryButton v-if="can('manage_transactions') && canPost()" @click="post" :loading="form.processing" class="w-full sm:w-auto">Post Penyesuaian</PrimaryButton>
                        <SecondaryButton as="link" :href="route('stock-adjustments.index')" class="w-full sm:w-auto">Kembali</SecondaryButton>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Detail -->
                    <div class="lg:col-span-2 space-y-4">
                        <!-- Status & Type Card -->
                        <Card>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-muted mb-1">Status</p>
                                    <p class="text-2xl font-bold text-ink font-display">
                                        {{ adjustment.status === 'draft' ? 'Draft' : adjustment.status === 'posted' ? 'Posted' : 'Dibatalkan' }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm text-muted mb-1">Tipe</p>
                                    <span :class="['px-3 py-1 rounded font-semibold text-sm', getTypeColor(adjustment.type)]">
                                        {{ getTypeLabel(adjustment.type) }}
                                    </span>
                                </div>
                            </div>
                        </Card>

                        <!-- Reason & Notes -->
                        <Card>
                            <div class="space-y-4">
                                <div>
                                    <p class="text-sm font-semibold text-ink mb-1">Alasan</p>
                                    <p class="text-lg text-ink">{{ adjustment.reason }}</p>
                                </div>
                                <div v-if="adjustment.notes" class="border-t-2 border-line pt-4">
                                    <p class="text-sm font-semibold text-ink mb-1">Catatan</p>
                                    <p class="text-muted text-sm">{{ adjustment.notes }}</p>
                                </div>
                            </div>
                        </Card>

                        <!-- Items -->
                        <Card>
                            <h2 class="text-xl font-bold text-ink font-display mb-4">Item Penyesuaian</h2>
                            
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead class="border-b-2 border-line">
                                        <tr>
                                            <th class="text-left py-3 px-4 font-semibold text-ink">Barang</th>
                                            <th class="text-center py-3 px-4 font-semibold text-ink">Qty</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="item in adjustment.items" :key="item.id" class="border-b border-line">
                                            <td class="py-3 px-4 font-semibold text-ink">{{ item.product_name }}</td>
                                            <td class="py-3 px-4 text-center">{{ formatNumber(item.quantity) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </Card>
                    </div>

                    <!-- Summary -->
                    <div>
                        <Card class="sticky top-[7rem]">
                            <h2 class="text-xl font-bold text-ink font-display mb-4">Ringkasan</h2>
                            
                            <div class="space-y-3 pb-6 border-b-2 border-line mb-6">
                                <div class="flex justify-between">
                                    <span class="text-muted">Total Item</span>
                                    <span :class="['text-lg font-bold', adjustment.type === 'increase' ? 'text-lime' : 'text-red-400']">
                                        {{ adjustment.items.length }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-muted">Total Qty</span>
                                    <span :class="['text-lg font-bold', adjustment.type === 'increase' ? 'text-lime' : 'text-red-400']">
                                        {{ formatNumber(totalQty()) }}
                                    </span>
                                </div>
                            </div>

                            <div v-if="adjustment.status === 'posted'" class="bg-lime/10 px-4 py-3 rounded border-l-2 border-lime">
                                <p class="text-sm font-semibold text-lime">Posted {{ formatDateTime(adjustment.posted_at) }}</p>
                            </div>
                        </Card>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
