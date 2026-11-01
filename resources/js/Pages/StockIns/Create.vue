<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/Card.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    docNumber: String,
    suppliers: Array,
    locations: Array,
    products: Array,
});

const form = useForm({
    doc_number: props.docNumber,
    date: new Date().toISOString().split('T')[0],
    supplier_id: '',
    notes: '',
    items: [],
});

const newItem = ref({
    product_id: '',
    location_id: '',
    batch_id: '',
    qty: '',
    unit_price: '',
});

const selectedProduct = computed(() => {
    if (!newItem.value.product_id) return null;
    return props.products.find(p => p.id == newItem.value.product_id);
});

const addItem = () => {
    if (!newItem.value.product_id || !newItem.value.location_id || !newItem.value.qty) {
        alert('Lengkapi produk, lokasi, dan qty');
        return;
    }

    form.items.push({
        product_id: parseInt(newItem.value.product_id),
        location_id: parseInt(newItem.value.location_id),
        batch_id: newItem.value.batch_id ? parseInt(newItem.value.batch_id) : null,
        qty: parseFloat(newItem.value.qty),
        unit_price: newItem.value.unit_price ? parseFloat(newItem.value.unit_price) : null,
    });

    newItem.value = { product_id: '', location_id: '', batch_id: '', qty: '', unit_price: '' };
};

const removeItem = (idx) => {
    form.items.splice(idx, 1);
};

const submitDraft = () => {
    form.post(route('stock-ins.store'));
};

const getProductName = (productId) => {
    const p = props.products.find(x => x.id == productId);
    return p ? `${p.sku} - ${p.name}` : '';
};

const getLocationName = (locationId) => {
    const l = props.locations.find(x => x.id == locationId);
    return l ? l.name : '';
};
</script>

<template>
    <Head title="Barang Masuk" />

    <AuthenticatedLayout>
        <div class="bg-canvas min-h-screen py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-8">
                    <div>
                        <p class="text-muted text-sm">Transaksi</p>
                        <h1 class="text-display font-bold text-3xl text-ink">Barang Masuk</h1>
                        <p class="text-muted text-sm mt-2">{{ docNumber }}</p>
                    </div>
                    <SecondaryButton as="link" :href="route('stock-ins.index')" class="w-full sm:w-auto">Kembali</SecondaryButton>
                </div>

                <!-- Form Header -->
                <Card class="mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-muted mb-2">Tanggal Terima*</label>
                            <input v-model="form.date" type="date" class="w-full h-12 px-4 border-2 border-line text-sm focus:border-ink focus:outline-none" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-muted mb-2">Supplier</label>
                            <select v-model="form.supplier_id" class="w-full h-12 px-4 border-2 border-line text-sm focus:border-ink focus:outline-none">
                                <option value="">-- Pilih Supplier --</option>
                                <option v-for="sup in suppliers" :key="sup.id" :value="sup.id">{{ sup.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-muted mb-2">Catatan</label>
                            <input v-model="form.notes" type="text" placeholder="(opsional)" class="w-full h-12 px-4 border-2 border-line text-sm focus:border-ink focus:outline-none" />
                        </div>
                    </div>
                </Card>

                <!-- Item Input -->
                <Card class="mb-6">
                    <h2 class="text-xl font-bold text-ink font-display mb-4">Tambah Item</h2>
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-4">
                        <div>
                            <label class="block text-xs font-semibold text-muted mb-1">Barang*</label>
                            <select v-model="newItem.product_id" class="w-full h-12 px-3 border-2 border-line text-xs focus:border-ink focus:outline-none">
                                <option value="">-- Pilih --</option>
                                <option v-for="p in products" :key="p.id" :value="p.id">{{ p.sku }} - {{ p.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-muted mb-1">Lokasi*</label>
                            <select v-model="newItem.location_id" class="w-full h-12 px-3 border-2 border-line text-xs focus:border-ink focus:outline-none">
                                <option value="">-- Pilih --</option>
                                <option v-for="loc in locations" :key="loc.id" :value="loc.id">{{ loc.code }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-muted mb-1">Qty*</label>
                            <input v-model="newItem.qty" type="number" step="0.01" placeholder="0" class="w-full h-12 px-3 border-2 border-line text-xs focus:border-ink focus:outline-none" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-muted mb-1">Harga/Unit</label>
                            <input v-model="newItem.unit_price" type="number" step="0.01" placeholder="0" class="w-full h-12 px-3 border-2 border-line text-xs focus:border-ink focus:outline-none" />
                        </div>
                        <div class="flex items-end">
                            <PrimaryButton @click="addItem" class="w-full">Tambah</PrimaryButton>
                        </div>
                    </div>
                </Card>

                <!-- Items Table -->
                <Card class="mb-6">
                    <h2 class="text-xl font-bold text-ink font-display mb-4">Item Diterima</h2>
                    <div v-if="form.items.length === 0" class="text-center py-8">
                        <p class="text-muted text-sm">Belum ada item. Tambahkan item di atas.</p>
                    </div>
                    <div v-else class="overflow-x-auto">
                        <table class="w-full text-xs">
                            <thead>
                                <tr class="border-b-2 border-line">
                                    <th class="text-left py-3 px-4 font-semibold text-muted">Barang</th>
                                    <th class="text-left py-3 px-4 font-semibold text-muted">Lokasi</th>
                                    <th class="text-center py-3 px-4 font-semibold text-muted">Qty</th>
                                    <th class="text-center py-3 px-4 font-semibold text-muted">Harga/Unit</th>
                                    <th class="text-center py-3 px-4 font-semibold text-muted">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(item, idx) in form.items" :key="idx" class="border-b border-surface-2 hover:bg-surface-2">
                                    <td class="py-3 px-4">{{ getProductName(item.product_id) }}</td>
                                    <td class="py-3 px-4">{{ getLocationName(item.location_id) }}</td>
                                    <td class="py-3 px-4 text-center font-semibold">{{ item.qty }}</td>
                                    <td class="py-3 px-4 text-center">{{ item.unit_price || '-' }}</td>
                                    <td class="py-3 px-4 text-center">
                                        <button @click="removeItem(idx)" class="text-bad-fg hover:underline text-xs font-semibold">Hapus</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </Card>

                <!-- Actions -->
                <div class="flex gap-4">
                    <PrimaryButton @click="submitDraft" :loading="form.processing" :disabled="form.items.length === 0">
                        Simpan Sebagai Draft
                    </PrimaryButton>
                    <SecondaryButton @click="$inertia.visit(route('stock-ins.index'))" type="button">
                        Batal
                    </SecondaryButton>
                </div>

                <div v-if="form.errors && Object.keys(form.errors).length > 0" class="mt-6 p-4 bg-bad-bg border-2 border-bad-fg text-bad-fg text-sm">
                    <p v-for="(err, key) in form.errors" :key="key" class="font-semibold">{{ err }}</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
