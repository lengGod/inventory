<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/Card.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps({
    categories: {
        type: Array,
        default: [],
    },
    units: {
        type: Array,
        default: [],
    },
});

const form = useForm({
    sku: '',
    barcode: '',
    name: '',
    category_id: '',
    unit_id: '',
    min_stock: 0,
    purchase_price: '',
    track_batch: false,
    track_expiry: false,
    expiry_alert_days: 30,
    description: '',
});

const submit = () => {
    form.post(route('products.store'));
};
</script>

<template>
    <Head title="Tambah Barang" />

    <AuthenticatedLayout>
        <div class="bg-canvas min-h-screen py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
                    <div>
                        <p class="text-muted text-sm">Master Data</p>
                        <h1 class="text-display font-bold text-3xl text-ink">Tambah Barang</h1>
                    </div>
                    <SecondaryButton as="link" :href="route('products.index')" class="w-full sm:w-auto">Kembali</SecondaryButton>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Identitas -->
                    <Card>
                        <h2 class="text-xl font-bold text-ink font-display mb-4">Identitas</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-muted mb-2">SKU*</label>
                                <input v-model="form.sku" type="text" class="w-full h-12 px-4 border-2 border-line text-sm focus:border-ink focus:outline-none" />
                                <p v-if="form.errors.sku" class="text-bad-fg text-xs mt-1">{{ form.errors.sku }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-muted mb-2">Barcode</label>
                                <input v-model="form.barcode" type="text" class="w-full h-12 px-4 border-2 border-line text-sm focus:border-ink focus:outline-none" />
                            </div>
                        </div>

                        <div class="mt-4">
                            <label class="block text-xs font-semibold text-muted mb-2">Nama Barang*</label>
                            <input v-model="form.name" type="text" class="w-full h-12 px-4 border-2 border-line text-sm focus:border-ink focus:outline-none" />
                            <p v-if="form.errors.name" class="text-bad-fg text-xs mt-1">{{ form.errors.name }}</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label class="block text-xs font-semibold text-muted mb-2">Kategori*</label>
                                <select v-model="form.category_id" class="w-full h-12 px-4 border-2 border-line text-sm focus:border-ink focus:outline-none">
                                    <option value="">-- Pilih Kategori --</option>
                                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                </select>
                                <p v-if="form.errors.category_id" class="text-bad-fg text-xs mt-1">{{ form.errors.category_id }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-muted mb-2">Satuan*</label>
                                <select v-model="form.unit_id" class="w-full h-12 px-4 border-2 border-line text-sm focus:border-ink focus:outline-none">
                                    <option value="">-- Pilih Satuan --</option>
                                    <option v-for="unit in units" :key="unit.id" :value="unit.id">{{ unit.name }}</option>
                                </select>
                                <p v-if="form.errors.unit_id" class="text-bad-fg text-xs mt-1">{{ form.errors.unit_id }}</p>
                            </div>
                        </div>

                        <div class="mt-4">
                            <label class="block text-xs font-semibold text-muted mb-2">Deskripsi</label>
                            <textarea v-model="form.description" class="w-full px-4 py-3 border-2 border-line text-sm focus:border-ink focus:outline-none" rows="3"></textarea>
                        </div>
                    </Card>

                    <!-- Stok & Harga -->
                    <Card>
                        <h2 class="text-xl font-bold text-ink font-display mb-4">Stok & Harga</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-muted mb-2">Stok Minimum</label>
                                <input v-model="form.min_stock" type="number" step="0.01" class="w-full h-12 px-4 border-2 border-line text-sm focus:border-ink focus:outline-none" />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-muted mb-2">Harga Beli (Rp)</label>
                                <input v-model="form.purchase_price" type="number" step="0.01" class="w-full h-12 px-4 border-2 border-line text-sm focus:border-ink focus:outline-none" />
                            </div>
                        </div>
                    </Card>

                    <!-- Pelacakan -->
                    <Card>
                        <h2 class="text-xl font-bold text-ink font-display mb-4">Pelacakan</h2>
                        
                        <div class="space-y-4">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input v-model="form.track_batch" type="checkbox" class="w-5 h-5 border-2 border-ink" />
                                <span class="text-sm font-semibold text-ink">Lacak per Batch/Lot</span>
                            </label>

                            <label class="flex items-center gap-3 cursor-pointer">
                                <input v-model="form.track_expiry" type="checkbox" :disabled="!form.track_batch" class="w-5 h-5 border-2 border-ink disabled:opacity-50" />
                                <span class="text-sm font-semibold" :class="form.track_batch ? 'text-ink' : 'text-muted'">
                                    Lacak Tanggal Kedaluwarsa
                                </span>
                            </label>

                            <div v-if="form.track_expiry">
                                <label class="block text-xs font-semibold text-muted mb-2">Ambang Peringatan (hari)</label>
                                <input v-model="form.expiry_alert_days" type="number" min="1" class="w-full h-12 px-4 border-2 border-line text-sm focus:border-ink focus:outline-none" />
                                <p class="text-muted text-xs mt-1">Default: 30 hari sebelum kedaluwarsa</p>
                            </div>
                        </div>
                    </Card>

                    <!-- Actions -->
                    <div class="flex gap-4">
                        <PrimaryButton :loading="form.processing" type="submit">Simpan Barang</PrimaryButton>
                        <SecondaryButton @click="$inertia.visit(route('products.index'))" type="button">Batal</SecondaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
