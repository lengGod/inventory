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
    stockOuts: Object,
    filters: Object,
});

const search = useTableSearch((value) => {
    router.get(route('stock-outs.index'), { search: value }, { preserveState: true, replace: true });
}, 300, props.filters?.search ?? '');

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
        form.delete(route('stock-outs.destroy', deletingDoc.value.id), {
            onSuccess: () => {
                deleteConfirm.value = false;
                deletingDoc.value = null;
            },
        });
    }
};
</script>

<template>
    <Head title="Barang Keluar" />

    <AuthenticatedLayout>
        <div class="bg-canvas min-h-screen py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
                    <div>
                        <h1 class="text-display font-bold text-3xl text-ink">Barang Keluar</h1>
                        <p class="text-muted text-sm mt-1">Manajemen stok keluar dengan alokasi FEFO</p>
                    </div>
                    <PrimaryButton v-if="can('manage_transactions')" as="link" :href="route('stock-outs.create')" class="w-full sm:w-auto">+ Barang Keluar</PrimaryButton>
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
                                <th class="text-left py-4 px-6 text-sm font-semibold text-ink">Jumlah Item</th>
                                <th class="text-left py-4 px-6 text-sm font-semibold text-ink">Status</th>
                                <th class="text-left py-4 px-6 text-sm font-semibold text-ink">Dibuat</th>
                                <th class="text-right py-4 px-6 text-sm font-semibold text-ink">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="stockOut in stockOuts.data" :key="stockOut.id" class="border-b border-line hover:bg-surface-2 transition">
                                <td class="py-3 px-6 text-sm font-semibold text-ink">{{ stockOut.doc_number }}</td>
                                <td class="py-3 px-6 text-sm text-muted">{{ stockOut.items.length }} item</td>
                                <td class="py-3 px-6">
                                    <span :class="['px-3 py-1 rounded text-xs font-semibold', getStatusColor(stockOut.status)]">
                                        {{ getStatusLabel(stockOut.status) }}
                                    </span>
                                </td>
                                <td class="py-3 px-6 text-sm text-muted">{{ formatDateTime(stockOut.created_at) }}</td>
                                <td class="py-3 px-6 text-right space-x-2">
                                    <Link :href="route('stock-outs.show', stockOut.id)" class="text-violet-text text-sm font-semibold hover:underline">
                                        Lihat Detail
                                    </Link>
                                    <button 
                                        v-if="can('manage_transactions')"
                                        @click="confirmDelete(stockOut)"
                                        class="inline-flex items-center justify-center h-10 px-4 text-xs font-semibold text-red-400 border-2 border-red-400 bg-transparent hover:bg-red-400/10 transition"
                                    >
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="stockOuts.data.length === 0">
                                <td colspan="5" class="py-8 px-6 text-center text-muted text-sm">
                                    Belum ada data Barang Keluar
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <Pagination :paginator="stockOuts" />
                </Card>
            </div>
        </div>

        <DeleteConfirm
            :is-open="deleteConfirm"
            :title="'Hapus Barang Keluar'"
            :message="'Yakin hapus dokumen ' + (deletingDoc?.doc_number || '') + '?'"
            :processing="form.processing"
            @confirm="executeDelete"
            @cancel="deleteConfirm = false"
        />
    </AuthenticatedLayout>
</template>