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
    units: Object,
    filters: Object,
});

const search = useTableSearch((value) => {
    router.get(route('units.index'), { search: value }, { preserveState: true, replace: true });
}, 300, props.filters?.search ?? '');

const showModal = ref(false);
const editingId = ref(null);
const deleteConfirm = ref(false);
const deletingUnit = ref(null);

const form = useForm({
    name: '',
    short_name: '',
});

const openModal = (unit = null) => {
    if (unit) {
        editingId.value = unit.id;
        form.name = unit.name;
        form.short_name = unit.short_name;
    } else {
        editingId.value = null;
        form.name = '';
        form.short_name = '';
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
        form.patch(route('units.update', editingId.value), {
            onSuccess: closeModal,
        });
    } else {
        form.post(route('units.store'), {
            onSuccess: closeModal,
        });
    }
};

const confirmDelete = (unit) => {
    deletingUnit.value = unit;
    deleteConfirm.value = true;
};

const executeDelete = () => {
    if (deletingUnit.value) {
        form.delete(route('units.destroy', deletingUnit.value.id), {
            onSuccess: () => {
                deleteConfirm.value = false;
                deletingUnit.value = null;
            },
        });
    }
};
</script>

<template>
    <Head title="Satuan" />

    <AuthenticatedLayout>
        <div class="bg-canvas min-h-screen py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
                    <div>
                        <p class="text-muted text-sm">Master Data</p>
                        <h1 class="text-display font-bold text-3xl text-ink">Satuan</h1>
                    </div>
                    <PrimaryButton v-if="can('manage_master_data')" @click="openModal()" class="w-full sm:w-auto">+ Satuan</PrimaryButton>
                </div>

                <!-- Toolbar -->
                <Card class="mb-6 flex flex-col sm:flex-row gap-3">
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Cari satuan..."
                        class="flex-1 h-12 px-4 border-2 border-line text-sm focus:border-ink focus:outline-none"
                    />
                </Card>

                <!-- Table Card -->
                <Card>
                    <div v-if="units.data.length === 0" class="text-center py-8">
                        <p class="text-muted text-sm">Belum ada satuan. Buat yang pertama.</p>
                    </div>
                    <div v-else class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b-2 border-line">
                                    <th class="text-left py-3 px-4 font-semibold text-muted">Nama</th>
                                    <th class="text-left py-3 px-4 font-semibold text-muted">Singkatan</th>
                                    <th v-if="can('manage_master_data')" class="text-right py-3 px-4 font-semibold text-muted">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="unit in units.data" :key="unit.id" class="border-b border-surface-2 hover:bg-surface-2">
                                    <td class="py-4 px-4">{{ unit.name }}</td>
                                    <td class="py-4 px-4">{{ unit.short_name }}</td>
                                    <td v-if="can('manage_master_data')" class="py-4 px-4 text-right">
                                        <div class="inline-flex items-center border-2 border-ink bg-surface shadow-hard">
                                            <button @click="openModal(unit)" class="inline-flex h-9 items-center px-3 text-xs font-semibold text-ink hover:bg-hi">Ubah</button>
                                        <button 
                                            @click="confirmDelete(unit)"
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
                    <Pagination :paginator="units" />
                </Card>
            </div>

            <!-- Modal Add/Edit -->
            <div v-show="showModal" class="fixed inset-0 bg-ink bg-opacity-60 flex items-center justify-center z-50">
                <Card class="w-full max-w-md">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-bold text-ink font-display">
                            {{ editingId ? 'Ubah Satuan' : 'Tambah Satuan' }}
                        </h2>
                        <button @click="closeModal" class="text-ink text-xl hover:text-ink-2">×</button>
                    </div>

                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-muted mb-2">Nama</label>
                            <input
                                v-model="form.name"
                                type="text"
                                placeholder="Contoh: Kilogram"
                                class="w-full h-12 px-4 border-2 border-line text-sm focus:border-ink focus:outline-none"
                            />
                            <p v-if="form.errors.name" class="text-bad-fg text-xs mt-1">{{ form.errors.name }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-muted mb-2">Singkatan</label>
                            <input
                                v-model="form.short_name"
                                type="text"
                                placeholder="Contoh: Kg"
                                class="w-full h-12 px-4 border-2 border-line text-sm focus:border-ink focus:outline-none"
                            />
                            <p v-if="form.errors.short_name" class="text-bad-fg text-xs mt-1">{{ form.errors.short_name }}</p>
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
                :title="'Hapus Satuan'"
                :message="'Yakin hapus satuan ' + (deletingUnit?.name || '') + '?'"
                :processing="form.processing"
                @confirm="executeDelete"
                @cancel="deleteConfirm = false"
            />
        </div>
    </AuthenticatedLayout>
</template>
