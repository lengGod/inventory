<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/Card.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DeleteConfirm from '@/Components/DeleteConfirm.vue';
import Pagination from '@/Components/Pagination.vue';
import { useAuthorization } from '@/Utils/authorization';
import { Head, useForm, router } from '@inertiajs/vue3';
import { useTableSearch } from '@/Composables/useTableSearch';
import { ref } from 'vue';

const { can } = useAuthorization();

const props = defineProps({
    locations: Object,
    filters: Object,
});

const search = useTableSearch((value) => {
    router.get(route('locations.index'), { search: value }, { preserveState: true, replace: true });
}, 300, props.filters?.search ?? '');

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
                    <PrimaryButton v-if="can('manage_master_data')" @click="openModal()" class="w-full sm:w-auto">+ Lokasi</PrimaryButton>
                </div>

                <!-- Toolbar -->
                <Card class="mb-6 flex flex-col sm:flex-row gap-3">
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Cari kode, nama, atau barcode..."
                        class="flex-1 h-12 px-4 border-2 border-line text-sm focus:border-ink focus:outline-none"
                    />
                </Card>

                <!-- Table Card -->
                <Card>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b-2 border-line">
                                    <th class="text-left py-3 px-4 font-semibold text-muted">Kode</th>
                                    <th class="text-left py-3 px-4 font-semibold text-muted">Nama</th>
                                    <th class="text-left py-3 px-4 font-semibold text-muted">Barcode</th>
                                    <th v-if="can('manage_master_data')" class="text-right py-3 px-4 font-semibold text-muted">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="locations.data.length === 0">
                                    <td :colspan="can('manage_master_data') ? 4 : 3" class="px-4 py-10 text-center text-sm text-muted">Belum ada lokasi.</td>
                                </tr>
                                <tr v-for="loc in locations.data" :key="loc.id" class="border-b border-surface-2 hover:bg-surface-2">
                                    <td class="py-4 px-4 font-semibold">{{ loc.code }}</td>
                                    <td class="py-4 px-4">{{ loc.name }}</td>
                                    <td class="py-4 px-4 text-muted text-xs font-mono">{{ loc.barcode || '-' }}</td>
                                    <td v-if="can('manage_master_data')" class="py-4 px-4 text-right">
                                        <div class="inline-flex items-center border-2 border-ink bg-surface shadow-hard">
                                            <button @click="openModal(loc)" class="inline-flex h-9 items-center px-3 text-xs font-semibold text-ink hover:bg-hi">Ubah</button>
                                        <button 
                                            @click="confirmDelete(loc)"
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
                    <Pagination :paginator="locations" />
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
