<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/Card.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DeleteConfirm from '@/Components/DeleteConfirm.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    products: {
        type: Object,
        default: {},
    },
    categories: {
        type: Array,
        default: [],
    },
});

const deleteConfirm = ref(false);
const deletingProduct = ref(null);

const form = useForm({});

const confirmDelete = (product) => {
    deletingProduct.value = product;
    deleteConfirm.value = true;
};

const executeDelete = () => {
    if (deletingProduct.value) {
        form.delete(route('products.destroy', deletingProduct.value.id), {
            onSuccess: () => {
                deleteConfirm.value = false;
                deletingProduct.value = null;
            },
        });
    }
};
</script>

<template>
    <Head title="Barang" />

    <AuthenticatedLayout>
        <div class="bg-canvas min-h-screen py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
                    <div>
                        <p class="text-muted text-sm">Master Data</p>
                        <h1 class="text-display font-bold text-3xl text-ink">Barang</h1>
                    </div>
                    <PrimaryButton as="link" :href="route('products.create')" class="w-full sm:w-auto">+ Barang</PrimaryButton>
                </div>

                <!-- Toolbar -->
                <Card class="mb-6 flex flex-col sm:flex-row gap-3">
                    <input
                        type="text"
                        placeholder="Cari nama/SKU..."
                        class="flex-1 h-12 px-4 border-2 border-line text-sm focus:border-ink focus:outline-none"
                    />
                    <select class="h-12 px-4 border-2 border-line text-sm focus:border-ink focus:outline-none">
                        <option value="">Semua Kategori</option>
                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                            {{ cat.name }}
                        </option>
                    </select>
                </Card>

                <!-- Table Card -->
                <Card>
                    <div v-if="products.data && products.data.length === 0" class="text-center py-8">
                        <p class="text-muted text-sm">Belum ada barang. Buat yang pertama.</p>
                    </div>
                    <div v-else class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b-2 border-line">
                                    <th class="text-left py-3 px-4 font-semibold text-muted">SKU</th>
                                    <th class="text-left py-3 px-4 font-semibold text-muted">Nama</th>
                                    <th class="text-left py-3 px-4 font-semibold text-muted">Kategori</th>
                                    <th class="text-left py-3 px-4 font-semibold text-muted">Satuan</th>
                                    <th class="text-left py-3 px-4 font-semibold text-muted">Track Batch</th>
                                    <th class="text-right py-3 px-4 font-semibold text-muted">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="product in products.data" :key="product.id" class="border-b border-surface-2 hover:bg-surface-2">
                                    <td class="py-4 px-4">{{ product.sku }}</td>
                                    <td class="py-4 px-4">{{ product.name }}</td>
                                    <td class="py-4 px-4">{{ product.category?.name }}</td>
                                    <td class="py-4 px-4">{{ product.unit?.short_name }}</td>
                                    <td class="py-4 px-4">{{ product.track_batch ? '✓' : '-' }}</td>
                                    <td class="py-4 px-4 text-right space-x-2">
                                        <Link :href="route('products.show', product.id)">
                                            <SecondaryButton class="text-xs">Lihat</SecondaryButton>
                                        </Link>
                                        <Link :href="route('products.edit', product.id)">
                                            <SecondaryButton class="text-xs">Ubah</SecondaryButton>
                                        </Link>
                                        <button 
                                            @click="confirmDelete(product)"
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
            :title="'Hapus Barang'"
            :message="'Yakin hapus ' + (deletingProduct?.name || '') + '? Data tidak bisa dikembalikan.'"
            :processing="form.processing"
            @confirm="executeDelete"
            @cancel="deleteConfirm = false"
        />
    </AuthenticatedLayout>
</template>