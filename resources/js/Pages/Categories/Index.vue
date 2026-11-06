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
    categories: Object,
    filters: Object,
});

const search = useTableSearch((value) => {
    router.get(route('categories.index'), { search: value }, { preserveState: true, replace: true });
}, 300, props.filters?.search ?? '');

const showModal = ref(false);
const editingId = ref(null);
const deleteConfirm = ref(false);
const deletingCategory = ref(null);

const form = useForm({
    name: '',
});

const openModal = (category = null) => {
    if (category) {
        editingId.value = category.id;
        form.name = category.name;
    } else {
        editingId.value = null;
        form.name = '';
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
        form.patch(route('categories.update', editingId.value), {
            onSuccess: closeModal,
        });
    } else {
        form.post(route('categories.store'), {
            onSuccess: closeModal,
        });
    }
};

const confirmDelete = (category) => {
    deletingCategory.value = category;
    deleteConfirm.value = true;
};

const executeDelete = () => {
    if (deletingCategory.value) {
        form.delete(route('categories.destroy', deletingCategory.value.id), {
            onSuccess: () => {
                deleteConfirm.value = false;
                deletingCategory.value = null;
            },
        });
    }
};
</script>

<template>
    <Head title="Kategori" />

    <AuthenticatedLayout>
        <div class="bg-canvas min-h-screen py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
                    <div>
                        <p class="text-muted text-sm">Master Data</p>
                        <h1 class="text-display font-bold text-3xl text-ink">Kategori</h1>
                    </div>
                    <PrimaryButton v-if="can('manage_master_data')" @click="openModal()" class="w-full sm:w-auto">+ Kategori</PrimaryButton>
                </div>

                <!-- Toolbar -->
                <Card class="mb-6 flex flex-col sm:flex-row gap-3">
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Cari kategori..."
                        class="flex-1 h-12 px-4 border-2 border-line text-sm focus:border-ink focus:outline-none"
                    />
                </Card>

                <!-- Table Card -->
                <Card>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b-2 border-line">
                                    <th class="text-left py-3 px-4 font-semibold text-muted">Nama</th>
                                    <th v-if="can('manage_master_data')" class="text-right py-3 px-4 font-semibold text-muted">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="categories.data.length === 0">
                                    <td :colspan="can('manage_master_data') ? 2 : 1" class="px-4 py-10 text-center text-sm text-muted">Belum ada kategori. Buat yang pertama.</td>
                                </tr>
                                <tr v-for="cat in categories.data" :key="cat.id" class="border-b border-surface-2 hover:bg-surface-2">
                                    <td class="py-4 px-4">{{ cat.name }}</td>
                                    <td v-if="can('manage_master_data')" class="py-4 px-4 text-right space-x-2">
                                        <SecondaryButton @click="openModal(cat)" class="text-xs">Ubah</SecondaryButton>
                                        <button 
                                            @click="confirmDelete(cat)"
                                            class="inline-flex h-9 items-center border-l-2 border-ink px-3 text-xs font-semibold text-bad-fg hover:bg-bad-bg"
                                        >
                                            Hapus
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <Pagination :paginator="categories" />
                </Card>
            </div>

            <!-- Modal Add/Edit -->
            <div v-show="showModal" class="fixed inset-0 bg-ink bg-opacity-60 flex items-center justify-center z-50">
                <Card class="w-full max-w-md">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-bold text-ink font-display">
                            {{ editingId ? 'Ubah Kategori' : 'Tambah Kategori' }}
                        </h2>
                        <button @click="closeModal" class="text-ink text-xl hover:text-ink-2">×</button>
                    </div>

                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-muted mb-2">Nama</label>
                            <input
                                v-model="form.name"
                                type="text"
                                class="w-full h-12 px-4 border-2 border-line text-sm focus:border-ink focus:outline-none"
                            />
                            <p v-if="form.errors.name" class="text-bad-fg text-xs mt-1">{{ form.errors.name }}</p>
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
                :title="'Hapus Kategori'"
                :message="'Yakin hapus kategori ' + (deletingCategory?.name || '') + '?'"
                :processing="form.processing"
                @confirm="executeDelete"
                @cancel="deleteConfirm = false"
            />
        </div>
    </AuthenticatedLayout>
</template>
