<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/Card.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DeleteConfirm from '@/Components/DeleteConfirm.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    locations: {
        type: Array,
        default: [],
    },
});

const showModal = ref(false);
const editingId = ref(null);
const deleteConfirm = ref(false);
const deletingLocation = ref(null);

const form = useForm({
    code: '',
    name: '',
    barcode: '',
});

const openModal = (location = null) => {
    if (location) {
        editingId.value = location.id;
        form.code = location.code;
        form.name = location.name;
        form.barcode = location.barcode || '';
    } else {
        editingId.value = null;
        form.code = '';
        form.name = '';
        form.barcode = '';
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
        form.patch(route('locations.update', editingId.value), {
            onSuccess: closeModal,
        });
    } else {
        form.post(route('locations.store'), {
            onSuccess: closeModal,
        });
    }
};

const confirmDelete = (location) => {
    deletingLocation.value = location;
    deleteConfirm.value = true;
};

const executeDelete = () => {
    if (deletingLocation.value) {
        form.delete(route('locations.destroy', deletingLocation.value.id), {
            onSuccess: () => {
                deleteConfirm.value = false;
                deletingLocation.value = null;
            },
        });
    }
};
</script>

<template>
    <Head title="Lokasi" />

    <AuthenticatedLayout>
        <div class="bg-canvas min-h-screen py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
                    <div>
                        <p class="text-muted text-sm">Master Data</p>
                        <h1 class="text-display font-bold text-3xl text-ink">Lokasi/Rak</h1>
                    </div>
                    <PrimaryButton @click="openModal()" class="w-full sm:w-auto">+ Lokasi</PrimaryButton>
                </div>

                <!-- Table Card -->
                <Card>
                    <div v-if="locations.length === 0" class="text-center py-8">
                        <p class="text-muted text-sm">Belum ada lokasi.</p>
                    </div>
                    <div v-else class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b-2 border-line">
                                    <th class="text-left py-3 px-4 font-semibold text-muted">Kode</th>
                                    <th class="text-left py-3 px-4 font-semibold text-muted">Nama</th>
                                    <th class="text-left py-3 px-4 font-semibold text-muted">Barcode</th>
                                    <th class="text-right py-3 px-4 font-semibold text-muted">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="loc in locations" :key="loc.id" class="border-b border-surface-2 hover:bg-surface-2">
                                    <td class="py-4 px-4 font-semibold">{{ loc.code }}</td>
                                    <td class="py-4 px-4">{{ loc.name }}</td>
                                    <td class="py-4 px-4 text-muted text-xs font-mono">{{ loc.barcode || '-' }}</td>
                                    <td class="py-4 px-4 text-right space-x-2">
                                        <SecondaryButton @click="openModal(loc)" class="text-xs">Ubah</SecondaryButton>
                                        <button 
                                            @click="confirmDelete(loc)"
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
                            {{ editingId ? 'Ubah Lokasi' : 'Tambah Lokasi' }}
                        </h2>
                        <button @click="closeModal" class="text-ink text-xl hover:text-ink-2">×</button>
                    </div>

                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-muted mb-2">Kode</label>
                            <input v-model="form.code" type="text" placeholder="RAK-A1" class="w-full h-12 px-4 border-2 border-line text-sm focus:border-ink focus:outline-none" />
                            <p v-if="form.errors.code" class="text-bad-fg text-xs mt-1">{{ form.errors.code }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-muted mb-2">Nama</label>
                            <input v-model="form.name" type="text" placeholder="Rak A Baris 1" class="w-full h-12 px-4 border-2 border-line text-sm focus:border-ink focus:outline-none" />
                            <p v-if="form.errors.name" class="text-bad-fg text-xs mt-1">{{ form.errors.name }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-muted mb-2">Barcode (opsional)</label>
                            <input v-model="form.barcode" type="text" placeholder="LOC001" class="w-full h-12 px-4 border-2 border-line text-sm focus:border-ink focus:outline-none" />
                            <p v-if="form.errors.barcode" class="text-bad-fg text-xs mt-1">{{ form.errors.barcode }}</p>
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
                :title="'Hapus Lokasi'"
                :message="'Yakin hapus lokasi ' + (deletingLocation?.code || '') + ' - ' + (deletingLocation?.name || '') + '?'"
                :processing="form.processing"
                @confirm="executeDelete"
                @cancel="deleteConfirm = false"
            />
        </div>
    </AuthenticatedLayout>
</template>