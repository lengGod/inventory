<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/Card.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import StatusChip from '@/Components/StatusChip.vue';
import DeleteConfirm from '@/Components/DeleteConfirm.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    stockIns: {
        type: Object,
        default: {},
    },
});

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
                    <PrimaryButton as="link" :href="route('stock-ins.create')" class="w-full sm:w-auto">+ Barang Masuk</PrimaryButton>
                </div>

                <!-- Table -->
                <Card>
                    <div v-if="!stockIns.data || stockIns.data.length === 0" class="text-center py-8">
                        <p class="text-muted text-sm">Belum ada dokumen barang masuk</p>
                    </div>
                    <div v-else class="overflow-x-auto">
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
                                <tr v-for="doc in stockIns.data" :key="doc.id" class="border-b border-surface-2 hover:bg-surface-2">
                                    <td class="py-3 px-4 font-semibold">{{ doc.doc_number }}</td>
                                    <td class="py-3 px-4">{{ doc.date }}</td>
                                    <td class="py-3 px-4">{{ doc.supplier?.name || '-' }}</td>
                                    <td class="py-3 px-4">
                                        <StatusChip :status="doc.status === 'posted' ? 'ok' : 'warn'">
                                            {{ doc.status === 'draft' ? 'Draft' : 'Posted' }}
                                        </StatusChip>
                                    </td>
                                    <td class="py-3 px-4 text-muted text-xs">{{ doc.creator?.name }}</td>
                                    <td class="py-3 px-4 text-right space-x-2">
                                        <Link :href="route('stock-ins.show', doc.id)">
                                            <SecondaryButton class="text-xs">Lihat</SecondaryButton>
                                        </Link>
                                        <button 
                                            @click="confirmDelete(doc)"
                                            class="inline-flex items-center justify-center h-10 px-4 text-xs font-semibold text-red-400 border-2 border-red-400 bg-transparent hover:bg-red-400/10 transition"
                                        >
                                            Hapus
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
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