<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/Card.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DeleteConfirm from '@/Components/DeleteConfirm.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    suppliers: {
        type: Array,
        default: [],
    },
});

const showModal = ref(false);
const editingId = ref(null);
const deleteConfirm = ref(false);
const deletingSupplier = ref(null);

const form = useForm({
    name: '',
    phone: '',
    email: '',
    address: '',
});

const openModal = (supplier = null) => {
    if (supplier) {
        editingId.value = supplier.id;
        form.name = supplier.name;
        form.phone = supplier.phone || '';
        form.email = supplier.email || '';
        form.address = supplier.address || '';
    } else {
        editingId.value = null;
        form.name = '';
        form.phone = '';
        form.email = '';
        form.address = '';
    }
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editingId.value = null;
    form.reset();
};

const submit = () => {
    if (editingId.value) {
        form.patch(route('suppliers.update', editingId.value), {
            onSuccess: closeModal,
        });
    } else {
        form.post(route('suppliers.store'), {
            onSuccess: closeModal,
        });
    }
};

const confirmDelete = (supplier) => {
    deletingSupplier.value = supplier;
    deleteConfirm.value = true;
};

const executeDelete = () => {
    if (deletingSupplier.value) {
        form.delete(route('suppliers.destroy', deletingSupplier.value.id), {
            onSuccess: () => {
                deleteConfirm.value = false;
                deletingSupplier.value = null;
            },
        });
    }
};
</script>

<template>
    <Head title="Supplier" />

    <AuthenticatedLayout>
        <div class="bg-canvas min-h-screen py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
                    <div>
                        <p class="text-muted text-sm">Master Data</p>
                        <h1 class="text-display font-bold text-3xl text-ink">Supplier</h1>
                    </div>
                    <PrimaryButton @click="openModal()" class="w-full sm:w-auto">+ Supplier</PrimaryButton>
                </div>

                <!-- Table Card -->
                <Card>
                    <div v-if="suppliers.length === 0" class="text-center py-8">
                        <p class="text-muted text-sm">Belum ada supplier.</p>
                    </div>
                    <div v-else class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b-2 border-line">
                                    <th class="text-left py-3 px-4 font-semibold text-muted">Nama</th>
                                    <th class="text-left py-3 px-4 font-semibold text-muted">Telepon</th>
                                    <th class="text-left py-3 px-4 font-semibold text-muted">Email</th>
                                    <th class="text-right py-3 px-4 font-semibold text-muted">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="sup in suppliers" :key="sup.id" class="border-b border-surface-2 hover:bg-surface-2">
                                    <td class="py-4 px-4">{{ sup.name }}</td>
                                    <td class="py-4 px-4 text-muted text-xs">{{ sup.phone || '-' }}</td>
                                    <td class="py-4 px-4 text-muted text-xs">{{ sup.email || '-' }}</td>
                                    <td class="py-4 px-4 text-right space-x-2">
                                        <SecondaryButton @click="openModal(sup)" class="text-xs">Ubah</SecondaryButton>
                                        <button 
                                            @click="confirmDelete(sup)"
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

            <!-- Modal Add/Edit -->
            <div v-show="showModal" class="fixed inset-0 bg-ink bg-opacity-60 flex items-center justify-center z-50">
                <Card class="w-full max-w-md">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-bold text-ink font-display">
                            {{ editingId ? 'Ubah Supplier' : 'Tambah Supplier' }}
                        </h2>
                        <button @click="closeModal" class="text-ink text-xl hover:text-ink-2">×</button>
                    </div>

                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-muted mb-2">Nama</label>
                            <input v-model="form.name" type="text" class="w-full h-12 px-4 border-2 border-line text-sm focus:border-ink focus:outline-none" />
                            <p v-if="form.errors.name" class="text-bad-fg text-xs mt-1">{{ form.errors.name }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-muted mb-2">Telepon</label>
                            <input v-model="form.phone" type="text" class="w-full h-12 px-4 border-2 border-line text-sm focus:border-ink focus:outline-none" />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-muted mb-2">Email</label>
                            <input v-model="form.email" type="email" class="w-full h-12 px-4 border-2 border-line text-sm focus:border-ink focus:outline-none" />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-muted mb-2">Alamat</label>
                            <textarea v-model="form.address" class="w-full px-4 py-3 border-2 border-line text-sm focus:border-ink focus:outline-none" rows="3"></textarea>
                        </div>

                        <div class="flex gap-4 pt-4">
                            <PrimaryButton :loading="form.processing" type="submit">
                                {{ editingId ? 'Simpan' : 'Tambah' }}
                            </PrimaryButton>
                            <SecondaryButton @click="closeModal" :disabled="form.processing" type="button">Batal</SecondaryButton>
                        </div>
                    </form>
                </Card>
            </div>

            <DeleteConfirm
                :is-open="deleteConfirm"
                :title="'Hapus Supplier'"
                :message="'Yakin hapus supplier ' + (deletingSupplier?.name || '') + '?'"
                :processing="form.processing"
                @confirm="executeDelete"
                @cancel="deleteConfirm = false"
            />
        </div>
    </AuthenticatedLayout>
</template>