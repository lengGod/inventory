<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/Card.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DeleteConfirm from '@/Components/DeleteConfirm.vue';
import Pagination from '@/Components/Pagination.vue';
import { useAuthorization } from '@/Utils/authorization';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { useTableSearch } from '@/Composables/useTableSearch';
import { ref } from 'vue';
import { formatDateTime } from '@/Utils/format';

const { can } = useAuthorization();

const props = defineProps({
    adjustments: Object,
    filters: Object,
});

const search = useTableSearch((value) => {
    router.get(route('stock-adjustments.index'), { search: value }, { preserveState: true, replace: true });
}, 300, props.filters?.search ?? '');

const getTypeColor = (type) => {
    return type === 'increase' ? 'text-lime bg-lime/10' : 'text-red-400 bg-red-400/10';
};

const getTypeLabel = (type) => {
    return type === 'increase' ? 'Penambahan' : 'Pengurangan';
};

const getStatusColor = (status) => {
    const colors = {
        'draft': 'text-muted bg-surface-2',
        'posted': 'text-lime bg-lime/10',
        'cancelled': 'text-red-400 bg-red-400/10',
    };
    return colors[status] || 'text-ink';
};

const getStatusLabel = (status) => {
    const labels = {
        'draft': 'Draft',
        'posted': 'Posted',
        'cancelled': 'Dibatalkan',
    };
    return labels[status] || status;
};

const deleteConfirm = ref(false);
const deletingDoc = ref(null);

const form = useForm({});

const confirmDelete = (doc) => {
    deletingDoc.value = doc;
    deleteConfirm.value = true;
};

const executeDelete = () => {
    if (deletingDoc.value) {
        form.delete(route('stock-adjustments.destroy', deletingDoc.value.id), {
            onSuccess: () => {
                deleteConfirm.value = false;
                deletingDoc.value = null;
            },
        });
    }
};
</script>

<template>
    <Head title="Penyesuaian Stok" />

    <AuthenticatedLayout>
        <div class="bg-canvas min-h-screen py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
                    <div>
                        <h1 class="text-display font-bold text-3xl text-ink">Penyesuaian Stok</h1>
                        <p class="text-muted text-sm mt-1">Manajemen opname dan penyesuaian stok</p>
                    </div>
                    <PrimaryButton v-if="can('manage_transactions')" as="link" :href="route('stock-adjustments.create')" class="w-full sm:w-auto">+ Penyesuaian Stok</PrimaryButton>
                </div>

                <!-- Toolbar -->
                <Card class="mb-6 flex flex-col sm:flex-row gap-3">
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Cari nomor dokumen..."
                        class="flex-1 h-12 px-4 border-2 border-line text-sm focus:border-ink focus:outline-none"
                    />
                </Card>

                <!-- Table -->
                <Card class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b-2 border-line">
                            <tr>
                                <th class="text-left py-4 px-6 text-sm font-semibold text-ink">No. Dokumen</th>
                                <th class="text-left py-4 px-6 text-sm font-semibold text-ink">Tipe</th>
                                <th class="text-left py-4 px-6 text-sm font-semibold text-ink">Alasan</th>
                                <th class="text-left py-4 px-6 text-sm font-semibold text-ink">Jumlah Item</th>
                                <th class="text-left py-4 px-6 text-sm font-semibold text-ink">Status</th>
                                <th class="text-left py-4 px-6 text-sm font-semibold text-ink">Dibuat</th>
                                <th class="text-right py-4 px-6 text-sm font-semibold text-ink">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="adjustment in adjustments.data" :key="adjustment.id" class="border-b border-line hover:bg-surface-2 transition">
                                <td class="py-3 px-6 text-sm font-semibold text-ink">{{ adjustment.doc_number }}</td>
                                <td class="py-3 px-6">
                                    <span :class="['px-3 py-1 rounded text-xs font-semibold', getTypeColor(adjustment.type)]">
                                        {{ getTypeLabel(adjustment.type) }}
                                    </span>
                                </td>
                                <td class="py-3 px-6 text-sm text-muted">{{ adjustment.reason }}</td>
                                <td class="py-3 px-6 text-sm text-muted">{{ adjustment.items.length }} item</td>
                                <td class="py-3 px-6">
                                    <span :class="['px-3 py-1 rounded text-xs font-semibold', getStatusColor(adjustment.status)]">
                                        {{ getStatusLabel(adjustment.status) }}
                                    </span>
                                </td>
                                <td class="py-3 px-6 text-sm text-muted">{{ formatDateTime(adjustment.created_at) }}</td>
                                <td class="py-3 px-6 text-right">
                                    <div class="inline-flex items-center border-2 border-ink bg-surface shadow-hard">
                                    <Link :href="route('stock-adjustments.show', adjustment.id)" class="inline-flex h-9 items-center px-3 text-xs font-semibold text-ink hover:bg-hi">
                                        Lihat Detail
                                    </Link>
                                    <button 
                                        v-if="can('manage_transactions')"
                                        @click="confirmDelete(adjustment)"
                                        class="inline-flex h-9 items-center border-l-2 border-ink px-3 text-xs font-semibold text-bad-fg hover:bg-bad-bg"
                                    >
                                        Hapus
                                    </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="adjustments.data.length === 0">
                                <td colspan="7" class="py-8 px-6 text-center text-muted text-sm">
                                    Belum ada data penyesuaian stok
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <Pagination :paginator="adjustments" />
                </Card>
            </div>
        </div>

        <DeleteConfirm
            :is-open="deleteConfirm"
            :title="'Hapus Penyesuaian Stok'"
            :message="'Yakin hapus dokumen ' + (deletingDoc?.doc_number || '') + '?'"
            :processing="form.processing"
            @confirm="executeDelete"
            @cancel="deleteConfirm = false"
        />
    </AuthenticatedLayout>
</template>
