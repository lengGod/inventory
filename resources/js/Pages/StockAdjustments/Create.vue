<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/Card.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    products: Array,
    currentStocks: Object,
});

const form = useForm({
    type: 'increase',
    reason: '',
    notes: '',
    items: [],
});

const selectedProduct = ref(null);
const selectedQuantity = ref(1);

const addItem = () => {
    if (!selectedProduct.value || !selectedQuantity.value) return;

    const product = products.find(p => p.id === parseInt(selectedProduct.value));
    const currentQty = currentStocks[product.id]?.totalQty || 0;
    
    form.items.push({
        product_id: product.id,
        product_name: product.name,
        sku: product.sku,
        quantity: selectedQuantity.value,
        currentQty: currentQty,
    });

    selectedProduct.value = null;
    selectedQuantity.value = 1;
};

const removeItem = (idx) => {
    form.items.splice(idx, 1);
};

const submit = () => {
    form.post(route('stock-adjustments.store'));
};

const totalQty = () => {
    return form.items.reduce((sum, item) => sum + item.quantity, 0);
};

const getReasonOptions = () => {
    return form.type === 'increase' 
        ? ['Return dari Penjualan', 'Pembelian Tambahan', 'Koreksi Sistem', 'Lainnya']
        : ['Rusak/Hilang', 'Penulisan Salah', 'Opname Fisik', 'Lainnya'];
};
</script>

<template>
    <Head title="Buat Penyesuaian Stok" />

    <AuthenticatedLayout>
        <div class="bg-canvas min-h-screen py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
                    <div>
                        <h1 class="text-display font-bold text-3xl text-ink">Buat Penyesuaian Stok</h1>
                        <p class="text-muted text-sm mt-1">Opname atau koreksi stok</p>
                    </div>
                    <SecondaryButton as="link" :href="route('stock-adjustments.index')" class="w-full sm:w-auto">Kembali</SecondaryButton>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Form -->
                    <div class="lg:col-span-2 space-y-4">
                        <Card>
                            <h2 class="text-xl font-bold text-ink font-display mb-4">Tipe Penyesuaian</h2>
                            
                            <div class="space-y-4">
                                <!-- Type Select -->
                                <div>
                                    <label class="block text-sm font-semibold text-ink mb-3">Pilih Tipe</label>
                                    <div class="flex gap-4">
                                        <button 
                                            @click="form.type = 'increase'"
                                            :class="['flex-1 px-4 py-3 rounded border-2 font-semibold transition', form.type === 'increase' ? 'bg-lime text-ink border-lime' : 'bg-surface text-ink border-line hover:border-lime']">
                                            Penambahan Stok
                                        </button>
                                        <button 
                                            @click="form.type = 'decrease'"
                                            :class="['flex-1 px-4 py-3 rounded border-2 font-semibold transition', form.type === 'decrease' ? 'bg-red-400 text-ink border-red-400' : 'bg-surface text-ink border-line hover:border-red-400']">
                                            Pengurangan Stok
                                        </button>
                                    </div>
                                </div>

                                <!-- Reason Select -->
                                <div>
                                    <label class="block text-sm font-semibold text-ink mb-2">Alasan</label>
                                    <select v-model="form.reason" class="w-full px-4 py-2 border-2 border-line rounded bg-surface text-ink">
                                        <option value="">-- Pilih Alasan --</option>
                                        <option v-for="reason in getReasonOptions()" :key="reason" :value="reason">
                                            {{ reason }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </Card>

                        <Card>
                            <h2 class="text-xl font-bold text-ink font-display mb-4">Tambah Item</h2>
                            
                            <div class="space-y-4">
                                <!-- Product Select -->
                                <div>
                                    <label class="block text-sm font-semibold text-ink mb-2">Barang</label>
                                    <select v-model="selectedProduct" class="w-full px-4 py-2 border-2 border-line rounded bg-surface text-ink">
                                        <option value="">-- Pilih Barang --</option>
                                        <option v-for="product in products" :key="product.id" :value="product.id">
                                            {{ product.sku }} - {{ product.name }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Quantity -->
                                <div>
                                    <label class="block text-sm font-semibold text-ink mb-2">Jumlah</label>
                                    <input v-model.number="selectedQuantity" type="number" min="1" class="w-full px-4 py-2 border-2 border-line rounded bg-surface text-ink" />
                                </div>

                                <!-- Add Button -->
                                <button @click="addItem" class="w-full px-4 py-2 bg-lime text-ink font-semibold rounded hover:bg-yellow-300 transition">
                                    Tambah Item
                                </button>
                            </div>
                        </Card>

                        <!-- Items Table -->
                        <Card v-if="form.items.length > 0">
                            <h2 class="text-xl font-bold text-ink font-display mb-4">Item Penyesuaian</h2>
                            
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead class="border-b-2 border-line">
                                        <tr>
                                            <th class="text-left py-3 px-4 font-semibold text-ink">SKU</th>
                                            <th class="text-left py-3 px-4 font-semibold text-ink">Barang</th>
                                            <th class="text-center py-3 px-4 font-semibold text-ink">Stok Saat Ini</th>
                                            <th class="text-center py-3 px-4 font-semibold text-ink">Qty</th>
                                            <th class="text-right py-3 px-4 font-semibold text-ink">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(item, idx) in form.items" :key="idx" class="border-b border-line">
                                            <td class="py-3 px-4">{{ item.sku }}</td>
                                            <td class="py-3 px-4">{{ item.product_name }}</td>
                                            <td class="py-3 px-4 text-center text-muted">{{ item.currentQty }}</td>
                                            <td class="py-3 px-4 text-center font-semibold">{{ item.quantity }}</td>
                                            <td class="py-3 px-4 text-right">
                                                <button @click="removeItem(idx)" class="text-red-400 text-xs font-semibold hover:underline">
                                                    Hapus
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </Card>
                    </div>

                    <!-- Summary -->
                    <div class="space-y-4">
                        <Card class="sticky top-24">
                            <h2 class="text-xl font-bold text-ink font-display mb-4">Ringkasan</h2>
                            
                            <div class="space-y-3 mb-6 pb-6 border-b-2 border-line">
                                <div class="flex justify-between">
                                    <span class="text-muted">Total Item</span>
                                    <span :class="['text-xl font-bold', form.type === 'increase' ? 'text-lime' : 'text-red-400']">
                                        {{ form.items.length }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-muted">Total Qty</span>
                                    <span :class="['text-xl font-bold', form.type === 'increase' ? 'text-lime' : 'text-red-400']">
                                        {{ totalQty() }}
                                    </span>
                                </div>
                            </div>

                            <!-- Notes -->
                            <div class="mb-6">
                                <label class="block text-sm font-semibold text-ink mb-2">Catatan</label>
                                <textarea v-model="form.notes" class="w-full px-4 py-2 border-2 border-line rounded bg-surface text-ink text-sm" rows="4" placeholder="Tambahkan catatan (opsional)"></textarea>
                            </div>

                            <!-- Actions -->
                            <div class="space-y-2">
                                <PrimaryButton @click="submit" :loading="form.processing" :disabled="form.items.length === 0 || !form.reason" class="w-full">
                                    Simpan Draft
                                </PrimaryButton>
                                <SecondaryButton as="link" :href="route('stock-adjustments.index')" class="w-full text-center">
                                    Batal
                                </SecondaryButton>
                            </div>
                        </Card>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
