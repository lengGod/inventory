<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/Card.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import StatusChip from '@/Components/StatusChip.vue';
import DeleteConfirm from '@/Components/DeleteConfirm.vue';
import Pagination from '@/Components/Pagination.vue';
import { useAuthorization } from '@/Utils/authorization';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { useTableSearch } from '@/Composables/useTableSearch';
import { ref } from 'vue';
import { formatDate } from '@/Utils/format';

const { can } = useAuthorization();

const props = defineProps({
    stockIns: Object,
    filters: Object,
});

const search = useTableSearch((value) => {
    router.get(route('stock-ins.index'), { search: value }, { preserveState: true, replace: true });
}, 300, props.filters?.search ?? '');

const deleteConfirm = ref(false);
const deletingDoc = ref(null);

const form = useForm({});

const confirmDelete = (doc) => {
    deletingDoc.value = doc;
    deleteConfirm.value = true;
};

const executeDelete = () => {
    if (deletingDoc.value) {
        form.delete(route('stock-ins.destroy', deletingDoc.value.id), {
            onSuccess: () => {
                deleteConfirm.value = false;
                deletingDoc.value = null;
            },
        });
    }
};
</script>

<template>
    <Head title="Barang Masuk" />

    <AuthenticatedLayout>
        <div class="bg-canvas min-h-screen py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
                    <div>
                        <p class="text-muted text-sm">Transaksi</p>
                        <h1 class="text-display font-bold text-3xl text-ink">Barang Masuk</h1>
                    </div>
                    <PrimaryButton v-if="can('manage_transactions')" as="link" :href="route('stock-ins.create')" class="w-full sm:w-auto">+ Barang Masuk</PrimaryButton>
                </div>

                <!-- Toolbar -->
                <Card class="mb-6 flex flex-col sm:flex-row gap-3">
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Cari nomor dokumen atau supplier..."
                        class="flex-1 h-12 px-4 border-2 border-line text-sm focus:border-ink focus:outline-none"
                    />
                </Card>

                <!-- Table -->
                <Card>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b-2 border-line">
                                    <th class="text-left py-3 px-4 font-semibold text-muted">Nomor Dokumen</th>
                                    <th class="text-left py-3 px-4 font-semibold text-muted">Tanggal</th>
                                    <th class="text-left py-3 px-4 font-semibold text-muted">Supplier</th>
                                    <th class="text-left py-3 px-4 font-semibold text-muted">Status</th>
                                    <th class="text-left py-3 px-4 font-semibold text-muted">Dibuat Oleh</th>
                                    <th class="text-right py-3 px-4 font-semibold text-muted">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="!stockIns.data || stockIns.data.length === 0">
                                    <td colspan="6" class="px-4 py-10 text-center text-sm text-muted">Belum ada dokumen barang masuk</td>
                                </tr>
                                <tr v-for="doc in stockIns.data" :key="doc.id" class="border-b border-surface-2 hover:bg-surface-2">
                                    <td class="py-3 px-4 font-semibold">{{ doc.doc_number }}</td>
                                    <td class="py-3 px-4">{{ formatDate(doc.date) }}</td>
                                    <td class="py-3 px-4">{{ doc.supplier?.name || '-' }}</td>
                                    <td class="py-3 px-4">
                                        <StatusChip :status="doc.status === 'posted' ? 'ok' : 'warn'">
                                            {{ doc.status === 'draft' ? 'Draft' : 'Posted' }}
                                        </StatusChip>
                                    </td>
                                    <td class="py-3 px-4 text-muted text-xs">{{ doc.creator?.name }}</td>
                                    <td class="py-3 px-4 text-right">
                                        <div class="inline-flex items-center border-2 border-ink bg-surface shadow-hard">
                                        <Link :href="route('stock-ins.show', doc.id)" class="inline-flex h-9 items-center px-3 text-xs font-semibold text-ink hover:bg-hi">
                                            Lihat
                                        </Link>
                                        <button 
                                            v-if="can('manage_transactions')"
                                            @click="confirmDelete(doc)"
                                            class="inline-flex h-9 items-center border-l-2 border-ink px-3 text-xs font-semibold text-bad-fg hover:bg-bad-bg"
                                        >
                                            Hapus
                                        </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <Pagination :paginator="stockIns" />
                </Card>
            </div>
        </div>

        <DeleteConfirm
            :is-open="deleteConfirm"
            :title="'Hapus Barang Masuk'"
            :message="'Yakin hapus dokumen ' + (deletingDoc?.doc_number || '') + '?'"
            :processing="form.processing"
            @confirm="executeDelete"
            @cancel="deleteConfirm = false"
        />
    </AuthenticatedLayout>
</template>
