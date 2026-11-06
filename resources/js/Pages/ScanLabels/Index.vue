<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/Card.vue';
import LabelPreview from '@/Components/LabelPreview.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import StatusChip from '@/Components/StatusChip.vue';
import { Head } from '@inertiajs/vue3';
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { AlertCircle, Camera, CameraOff, History, Minus, Plus, Printer, ScanBarcode, Tag } from '@lucide/vue';
import { Html5Qrcode } from 'html5-qrcode';
import { formatDate, formatDateTime, formatNumber } from '@/Utils/format';

const props = defineProps({
    history: {
        type: Array,
        default: () => [],
    },
    stats: {
        type: Object,
        default: () => ({ today: 0, total: 0, logs: 0 }),
    },
    quickPicks: {
        type: Array,
        default: () => [],
    },
    initialCode: {
        type: String,
        default: '',
    },
});

const TYPE_LABELS = {
    product: 'Barang',
    batch: 'Batch',
    location: 'Lokasi',
};

const TYPE_STATUS = {
    product: 'info',
    batch: 'adj',
    location: 'ok',
};

const typeLabel = (type) => TYPE_LABELS[type] ?? type;
const typeStatusFor = (type) => TYPE_STATUS[type] ?? 'info';

const code = ref('');
const result = ref(null);
const lookupLoading = ref(false);
const lookupError = ref('');

const scanning = ref(false);
const cameraError = ref('');
let scannerInstance = null;

const selectedKey = ref('');
const copies = ref(1);
const printing = ref(false);
const printError = ref('');
const historyList = ref([...props.history]);

const clampCopies = () => {
    const parsed = Number.parseInt(copies.value, 10);
    copies.value = Number.isNaN(parsed) ? 1 : Math.min(50, Math.max(1, parsed));
};

const batchOption = (batch, product) => ({
    key: `batch:${batch.id}`,
    type: 'batch',
    referenceId: batch.id,
    code: batch.batch_number,
    title: product?.name ?? 'Batch',
    subtitle: `Batch ${batch.batch_number}`,
    optionLabel: `Batch — ${batch.batch_number}${product?.name ? ' • ' + product.name : ''}`,
    meta: [
        { label: 'Batch', value: batch.batch_number },
        { label: 'Kedaluwarsa', value: batch.expiry_date ? formatDate(batch.expiry_date) : '-' },
        { label: 'Satuan', value: product?.unit || '-' },
    ],
});

const labelOptions = computed(() => {
    const data = result.value;

    if (!data) {
        return [];
    }

    const options = [];
    const unit = data.product?.unit || '-';

    if (data.product) {
        options.push({
            key: `product:${data.product.id}`,
            type: 'product',
            referenceId: data.product.id,
            code: data.product.barcode || data.product.sku,
            title: data.product.name,
            subtitle: `SKU ${data.product.sku}`,
            optionLabel: `Barang — ${data.product.name}`,
            meta: [
                { label: 'Kategori', value: data.product.category || '-' },
                { label: 'Satuan', value: unit },
                { label: 'Barcode', value: data.product.barcode || '-' },
                { label: 'Total Stok', value: `${formatNumber(data.total_stock)} ${unit}` },
            ],
        });
    }

    if (data.batch) {
        options.push({
            ...batchOption(data.batch, data.product),
            meta: [
                { label: 'Batch', value: data.batch.batch_number },
                { label: 'Kedaluwarsa', value: data.batch.expiry_date ? formatDate(data.batch.expiry_date) : '-' },
                { label: 'Supplier', value: data.batch.supplier || '-' },
                { label: 'Satuan', value: unit },
            ],
        });
    }

    if (data.type === 'product' && data.stocks?.length) {
        const seen = new Set();

        for (const stock of data.stocks) {
            if (!stock.batch || seen.has(stock.batch.id)) {
                continue;
            }

            seen.add(stock.batch.id);
            options.push(batchOption(stock.batch, data.product));
        }
    }

    if (data.location) {
        options.push({
            key: `location:${data.location.id}`,
            type: 'location',
            referenceId: data.location.id,
            code: data.location.barcode || data.location.code,
            title: data.location.name,
            subtitle: `Kode ${data.location.code}`,
            optionLabel: `Lokasi — ${data.location.code}`,
            meta: [
                { label: 'Kode', value: data.location.code },
                { label: 'Barcode', value: data.location.barcode || '-' },
                { label: 'Total Stok', value: formatNumber(data.total_stock) },
            ],
        });
    }

    return options;
});

const selectedLabel = computed(() => {
    if (!labelOptions.value.length) {
        return null;
    }

    return labelOptions.value.find((option) => option.key === selectedKey.value) ?? labelOptions.value[0];
});

watch(result, async () => {
    await nextTick();
    selectedKey.value = labelOptions.value[0]?.key ?? '';
});

const submitLookup = async (overrideCode = null) => {
    const value = String(overrideCode ?? code.value ?? '').trim();

    if (!value) {
        lookupError.value = 'Masukkan atau pindai kode terlebih dahulu.';
        return;
    }

    lookupLoading.value = true;
    lookupError.value = '';
    printError.value = '';

    try {
        const { data } = await window.axios.get(route('scan-label.lookup'), {
            params: { code: value },
        });

        code.value = value;
        result.value = data;
        copies.value = 1;
    } catch (error) {
        result.value = null;
        lookupError.value = error?.response?.data?.message || 'Kode tidak ditemukan. Coba kode lain.';
    } finally {
        lookupLoading.value = false;
    }
};

const useQuickPick = (pick) => {
    code.value = pick.code;
    submitLookup(pick.code);
};

const SCAN_READER_ID = 'scan-reader';

const cameraErrorMessage = (error) => {
    const name = error?.name || '';
    const message = String(error?.message ?? error ?? '');
    const details = `${name} ${message}`;

    if (!window.isSecureContext) {
        return 'Kamera hanya bisa diakses melalui HTTPS. Buka situs dengan alamat https:// lalu coba lagi.';
    }

    if (!navigator.mediaDevices?.getUserMedia) {
        return 'Browser ini tidak mendukung akses kamera. Gunakan Chrome, Edge, atau Safari terbaru.';
    }

    if (/NotAllowed|Permission|denied/i.test(details)) {
        return 'Izin kamera ditolak. Di browser, buka Pengaturan Situs > Kamera > Izinkan, lalu muat ulang halaman.';
    }

    if (/NotFound|DevicesNotFound/i.test(details)) {
        return 'Kamera tidak ditemukan. Pastikan perangkat memiliki kamera dan tidak sedang dipakai aplikasi lain.';
    }

    if (/NotReadable|TrackStart/i.test(details)) {
        return 'Kamera sedang dipakai aplikasi lain. Tutup aplikasi kamera lain, lalu tekan Buka Kamera lagi.';
    }

    if (/Overconstrained|ConstraintNotSatisfied/i.test(details)) {
        return 'Kamera belakang tidak tersedia. Coba kamera depan atau perangkat lain.';
    }

    return 'Kamera gagal dimulai. Tutup aplikasi lain yang memakai kamera, lalu coba lagi.';
};

const isCameraPermissionError = (error) => /NotAllowed|Permission|denied/i.test(`${error?.name || ''} ${error?.message || error || ''}`);

const buildScanConfig = () => ({
    fps: 10,
    aspectRatio: 1.0,
    qrbox: (viewfinderWidth, viewfinderHeight) => {
        const width = Math.max(140, Math.min(Math.floor(viewfinderWidth * 0.8), 320));
        const height = Math.max(90, Math.min(Math.floor(viewfinderHeight * 0.5), Math.floor(width * 0.62)));

        return { width, height };
    },
});

const disposeScanner = async (instance) => {
    if (!instance) {
        return;
    }

    try {
        await instance.stop();
    } catch {
        // Camera might already be stopped.
    }

    try {
        instance.clear();
    } catch {
        // Scanner container might already be cleared.
    }
};

const createScanner = () => {
    const instance = new Html5Qrcode(SCAN_READER_ID, { verbose: false });
    scannerInstance = instance;

    return instance;
};

const startScanner = async (instance) => {
    try {
        await instance.start(
            { facingMode: 'environment' },
            buildScanConfig(),
            handleScanSuccess,
            () => {},
        );
    } catch (error) {
        if (isCameraPermissionError(error)) {
            throw error;
        }

        // Sebagian browser mobile menolak facingMode walau kamera belakang tersedia.
        // Coba lagi memakai deviceId kamera belakang.
        await disposeScanner(instance);
        await new Promise((resolve) => setTimeout(resolve, 250));

        let cameras = [];

        try {
            cameras = await Html5Qrcode.getCameras();
        } catch (cameraListError) {
            if (isCameraPermissionError(cameraListError)) {
                throw cameraListError;
            }

            throw error;
        }

        if (!cameras.length) {
            throw error;
        }

        const backCamera = cameras.find((camera) => /back|rear|environment|belakang/i.test(camera.label));
        const camera = backCamera ?? cameras[cameras.length - 1];
        const retryInstance = createScanner();

        await retryInstance.start(
            { deviceId: { exact: camera.id } },
            buildScanConfig(),
            handleScanSuccess,
            () => {},
        );
    }
};

const stopCamera = async () => {
    scanning.value = false;

    const instance = scannerInstance;
    scannerInstance = null;

    await disposeScanner(instance);
};

const handleScanSuccess = (decodedText) => {
    if (!scanning.value) {
        return;
    }

    stopCamera();
    code.value = decodedText;
    submitLookup(decodedText);
};

const startCamera = async () => {
    if (scanning.value) {
        return;
    }

    cameraError.value = '';
    scanning.value = true;
    await nextTick();

    if (!document.getElementById(SCAN_READER_ID)) {
        cameraError.value = 'Area kamera tidak ditemukan. Muat ulang halaman lalu coba lagi.';
        scanning.value = false;

        return;
    }

    try {
        if (!window.isSecureContext) {
            throw new Error('Kamera membutuhkan HTTPS.');
        }

        if (!navigator.mediaDevices?.getUserMedia) {
            throw new Error('Browser tidak mendukung akses kamera.');
        }

        await startScanner(createScanner());
    } catch (error) {
        console.error('[kamera]', error);
        cameraError.value = cameraErrorMessage(error);
        scanning.value = false;

        const instance = scannerInstance;
        scannerInstance = null;

        await disposeScanner(instance);
    }
};

const decreaseCopies = () => {
    copies.value = Math.max(1, Number(copies.value) - 1);
};

const increaseCopies = () => {
    copies.value = Math.min(50, Number(copies.value) + 1);
};

const waitForPrintImages = async () => {
    await nextTick();

    const images = Array.from(document.querySelectorAll('#label-print-area img'));

    await Promise.all(images.map((image) => {
        if (image.complete) {
            return Promise.resolve();
        }

        return new Promise((resolve) => {
            image.onload = resolve;
            image.onerror = resolve;
        });
    }));
};

const printLabels = async () => {
    if (!selectedLabel.value) {
        return;
    }

    clampCopies();
    printing.value = true;
    printError.value = '';

    try {
        const { data } = await window.axios.post(route('scan-label.print'), {
            label_type: selectedLabel.value.type,
            reference_id: selectedLabel.value.referenceId,
            copies: copies.value,
        });

        if (data.log) {
            historyList.value = [data.log, ...historyList.value].slice(0, 20);
        }

        await waitForPrintImages();

        const cleanupPrintClass = () => document.body.classList.remove('printing-label');

        window.addEventListener('afterprint', cleanupPrintClass, { once: true });
        document.body.classList.add('printing-label');
        window.print();
    } catch (error) {
        printError.value = error?.response?.data?.message
            || Object.values(error?.response?.data?.errors || {}).flat()[0]
            || 'Gagal mencatat cetak label. Coba lagi.';
    } finally {
        printing.value = false;
    }
};

onMounted(() => {
    if (props.initialCode) {
        code.value = props.initialCode;
        submitLookup(props.initialCode);
    }
});

onBeforeUnmount(() => {
    stopCamera();
    document.body.classList.remove('printing-label');
});
</script>

<template>
    <Head title="Scan & Label" />

    <AuthenticatedLayout>
        <div class="bg-canvas min-h-screen py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-8">
                    <div>
                        <p class="text-muted text-sm">Inventori</p>
                        <h1 class="text-display font-bold text-3xl text-ink">Scan &amp; Label</h1>
                        <p class="text-muted text-sm mt-1">Pindai barcode untuk mencari data, lalu cetak label barang, batch, atau lokasi.</p>
                    </div>
                    <div class="flex items-center gap-2 text-xs font-semibold text-muted">
                        <span class="w-2 h-2 bg-lime"></span>
                        Scanner siap digunakan
                    </div>
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                    <Card class="border-l-4 border-lime">
                        <p class="text-muted text-xs font-semibold mb-2">Label Hari Ini</p>
                        <p class="text-2xl font-bold text-ink">{{ formatNumber(stats.today, 0) }}</p>
                        <p class="text-muted text-xs mt-1">lembar tercetak</p>
                    </Card>
                    <Card class="border-l-4 border-violet-text">
                        <p class="text-muted text-xs font-semibold mb-2">Total Label</p>
                        <p class="text-2xl font-bold text-ink">{{ formatNumber(stats.total, 0) }}</p>
                        <p class="text-muted text-xs mt-1">lembar sepanjang waktu</p>
                    </Card>
                    <Card class="border-l-4 border-ink">
                        <p class="text-muted text-xs font-semibold mb-2">Sesi Cetak</p>
                        <p class="text-2xl font-bold text-ink">{{ formatNumber(stats.logs, 0) }}</p>
                        <p class="text-muted text-xs mt-1">aktivitas tercatat</p>
                    </Card>
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-5 gap-6">
                    <!-- Scanner + result -->
                    <div class="min-w-0 xl:col-span-3 space-y-6">
                        <Card>
                            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-4">
                                <div>
                                    <h2 class="text-xl font-bold text-ink font-display flex items-center gap-2">
                                        <ScanBarcode class="w-5 h-5" />
                                        Pindai Kode
                                    </h2>
                                    <p class="text-muted text-sm mt-1">Barcode barang, SKU, nomor batch, atau barcode lokasi.</p>
                                </div>
                                <StatusChip :status="scanning ? 'ok' : 'info'">
                                    {{ scanning ? 'Kamera aktif' : 'Siap' }}
                                </StatusChip>
                            </div>

                            <form @submit.prevent="submitLookup()" class="flex flex-col sm:flex-row gap-3">
                                <div class="relative flex-1">
                                    <ScanBarcode class="w-5 h-5 text-muted absolute left-4 top-1/2 -translate-y-1/2" />
                                    <input
                                        v-model="code"
                                        type="text"
                                        autocomplete="off"
                                        placeholder="Contoh: 8991001234003, OBAT001, B-251001-01"
                                        class="w-full h-12 pl-12 pr-4 border-2 border-line text-sm focus:border-ink focus:outline-none"
                                    />
                                </div>
                                <PrimaryButton type="submit" :loading="lookupLoading" class="sm:w-auto">
                                    Cari
                                </PrimaryButton>
                            </form>

                            <div class="flex flex-wrap items-center gap-3 mt-3">
                                <SecondaryButton v-if="!scanning" class="h-10 px-4 text-xs" @click="startCamera">
                                    <Camera class="w-4 h-4" />
                                    Buka Kamera
                                </SecondaryButton>
                                <SecondaryButton v-else class="h-10 px-4 text-xs" @click="stopCamera">
                                    <CameraOff class="w-4 h-4" />
                                    Hentikan Kamera
                                </SecondaryButton>
                                <p class="text-muted text-xs">Gunakan kamera belakang untuk memindai barcode/QR.</p>
                            </div>

                            <p v-if="cameraError" class="mt-3 text-xs font-semibold text-red-500 flex items-start gap-2">
                                <AlertCircle class="w-4 h-4 flex-shrink-0 mt-0.5" />
                                {{ cameraError }}
                            </p>

                            <div v-show="scanning" class="mt-4">
                                <div id="scan-reader" class="mx-auto min-h-[220px] w-full max-w-md overflow-hidden border-2 border-ink bg-ink"></div>
                                <p class="text-center text-muted text-xs mt-2">Arahkan kamera ke barcode hingga terbaca otomatis.</p>
                            </div>

                            <p v-if="lookupError" class="mt-3 text-xs font-semibold text-red-500 flex items-start gap-2">
                                <AlertCircle class="w-4 h-4 flex-shrink-0 mt-0.5" />
                                {{ lookupError }}
                            </p>

                            <div v-if="quickPicks.length" class="mt-6 pt-5 border-t-2 border-line">
                                <p class="text-xs font-semibold text-muted mb-2">Coba kode contoh</p>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    <button
                                        v-for="pick in quickPicks"
                                        :key="`${pick.type}-${pick.code}`"
                                        type="button"
                                        class="text-left border-2 border-line hover:border-ink hover:bg-hi transition px-3 py-2"
                                        @click="useQuickPick(pick)"
                                    >
                                        <span class="block text-[10px] uppercase tracking-wide text-muted font-semibold">{{ typeLabel(pick.type) }}</span>
                                        <span class="block text-xs font-semibold text-ink truncate">{{ pick.label }}</span>
                                        <span class="block text-[10px] text-muted truncate">{{ pick.hint }}</span>
                                    </button>
                                </div>
                            </div>
                        </Card>

                        <Card v-if="result">
                            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-5">
                                <div>
                                    <StatusChip :status="typeStatusFor(result.type)">{{ typeLabel(result.type) }}</StatusChip>
                                    <h2 class="text-xl font-bold text-ink font-display mt-2">
                                        <template v-if="result.type === 'location'">{{ result.location?.name }}</template>
                                        <template v-else>{{ result.product?.name }}</template>
                                    </h2>
                                    <p v-if="result.type === 'product'" class="text-muted text-sm">
                                        {{ result.product?.sku }} &middot; {{ result.product?.category || '-' }} &middot; {{ result.product?.unit || '-' }}
                                    </p>
                                    <p v-else-if="result.type === 'batch'" class="text-muted text-sm">
                                        Batch {{ result.batch?.batch_number }} &middot; Kedaluwarsa {{ formatDate(result.batch?.expiry_date, '-') }}
                                    </p>
                                    <p v-else class="text-muted text-sm">Kode {{ result.location?.code }}</p>
                                </div>
                                <div class="flex gap-2 flex-wrap">
                                    <span class="px-3 py-2 bg-surface-2 border-2 border-line text-xs font-semibold">
                                        Stok: {{ formatNumber(result.total_stock) }} {{ result.product?.unit || '' }}
                                    </span>
                                    <span class="px-3 py-2 bg-surface-2 border-2 border-line text-xs font-semibold">
                                        {{ result.total_locations }} lokasi
                                    </span>
                                </div>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="border-b-2 border-line">
                                            <th class="text-left py-2 px-3 font-semibold text-muted">Lokasi</th>
                                            <template v-if="result.type !== 'location'">
                                                <th class="text-left py-2 px-3 font-semibold text-muted">Batch</th>
                                                <th class="text-left py-2 px-3 font-semibold text-muted">Kedaluwarsa</th>
                                            </template>
                                            <template v-else>
                                                <th class="text-left py-2 px-3 font-semibold text-muted">Barang</th>
                                                <th class="text-left py-2 px-3 font-semibold text-muted">Batch</th>
                                            </template>
                                            <th class="text-right py-2 px-3 font-semibold text-muted">Qty</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-if="!result.stocks?.length">
                                            <td colspan="4" class="px-4 py-10 text-center text-sm text-muted">Tidak ada stok aktif untuk data ini.</td>
                                        </tr>
                                        <tr v-for="stock in result.stocks" :key="stock.id" class="border-b border-surface-2">
                                            <td class="py-2 px-3 font-semibold">{{ stock.location?.code || '-' }}</td>
                                            <template v-if="result.type !== 'location'">
                                                <td class="py-2 px-3 font-mono text-xs">
                                                    {{ result.type === 'batch' ? result.batch?.batch_number : (stock.batch?.batch_number || '-') }}
                                                </td>
                                                <td class="py-2 px-3">
                                                    {{ formatDate(result.type === 'batch' ? result.batch?.expiry_date : stock.batch?.expiry_date, '-') }}
                                                </td>
                                            </template>
                                            <template v-else>
                                                <td class="py-2 px-3">
                                                    {{ stock.product?.name || '-' }}
                                                    <span class="block text-[10px] text-muted font-mono">{{ stock.product?.sku }}</span>
                                                </td>
                                                <td class="py-2 px-3 font-mono text-xs">{{ stock.batch?.batch_number || '-' }}</td>
                                            </template>
                                            <td class="py-2 px-3 text-right font-semibold">{{ formatNumber(stock.quantity) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </Card>

                        <Card v-else class="border-dashed">
                            <div class="text-center py-8">
                                <ScanBarcode class="w-10 h-10 text-muted mx-auto mb-3" />
                                <p class="font-semibold text-ink">Belum ada hasil pindai</p>
                                <p class="text-muted text-sm mt-1">Gunakan kamera, masukkan kode manual, atau pilih kode contoh di atas.</p>
                            </div>
                        </Card>
                    </div>

                    <!-- Label studio -->
                    <div class="min-w-0 xl:col-span-2 space-y-6">
                        <Card class="border-t-4 border-t-violet-text">
                            <h2 class="text-xl font-bold text-ink font-display flex items-center gap-2">
                                <Tag class="w-5 h-5" />
                                Studio Label
                            </h2>
                            <p class="text-muted text-sm mt-1 mb-4">Pilih jenis label, atur jumlah, lalu cetak.</p>

                            <div v-if="!selectedLabel" class="text-center py-8 border-2 border-dashed border-line">
                                <Tag class="w-8 h-8 text-muted mx-auto mb-2" />
                                <p class="text-muted text-sm">Pindai atau cari kode terlebih dahulu untuk membuat label.</p>
                            </div>

                            <template v-else>
                                <div v-if="labelOptions.length > 1" class="mb-4">
                                    <label class="block text-xs font-semibold text-muted mb-2">Jenis Label</label>
                                    <select v-model="selectedKey" class="w-full h-12 px-4 border-2 border-line text-sm focus:border-ink focus:outline-none">
                                        <option v-for="option in labelOptions" :key="option.key" :value="option.key">
                                            {{ option.optionLabel }}
                                        </option>
                                    </select>
                                </div>
                                <div v-else class="mb-4">
                                    <p class="text-xs font-semibold text-muted mb-1">Jenis Label</p>
                                    <p class="text-sm font-semibold text-ink">{{ selectedLabel.optionLabel }}</p>
                                </div>

                                <div class="mb-5">
                                    <label class="block text-xs font-semibold text-muted mb-2">Jumlah Salinan</label>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <div class="inline-flex items-center border-2 border-ink h-11">
                                            <button
                                                type="button"
                                                :disabled="copies <= 1"
                                                class="w-10 h-full flex items-center justify-center hover:bg-hi disabled:opacity-30 transition"
                                                aria-label="Kurangi salinan"
                                                @click="decreaseCopies"
                                            >
                                                <Minus class="w-4 h-4" />
                                            </button>
                                            <input
                                                v-model.number="copies"
                                                type="number"
                                                min="1"
                                                max="50"
                                                class="w-16 h-full text-center border-x-2 border-ink text-sm font-semibold focus:outline-none"
                                                @change="clampCopies"
                                            />
                                            <button
                                                type="button"
                                                :disabled="copies >= 50"
                                                class="w-10 h-full flex items-center justify-center hover:bg-hi disabled:opacity-30 transition"
                                                aria-label="Tambah salinan"
                                                @click="increaseCopies"
                                            >
                                                <Plus class="w-4 h-4" />
                                            </button>
                                        </div>
                                        <button
                                            v-for="preset in [1, 5, 10]"
                                            :key="preset"
                                            type="button"
                                            :class="['h-9 px-3 border-2 text-xs font-semibold transition', copies === preset ? 'border-ink bg-ink text-white' : 'border-line hover:border-ink']"
                                            @click="copies = preset"
                                        >
                                            {{ preset }}
                                        </button>
                                        <span class="text-muted text-xs">maks. 50</span>
                                    </div>
                                </div>

                                <div class="bg-surface-2 border-2 border-line p-4 mb-5">
                                    <div class="max-w-[320px] mx-auto">
                                        <LabelPreview :label="selectedLabel" />
                                    </div>
                                    <p class="text-center text-muted text-[11px] mt-2">Pratinjau label 60 &times; 40 mm</p>
                                </div>

                                <PrimaryButton class="w-full" :loading="printing" @click="printLabels">
                                    <Printer class="w-4 h-4" />
                                    Cetak {{ copies }} Label
                                </PrimaryButton>

                                <p v-if="printError" class="mt-3 text-xs font-semibold text-red-500 flex items-start gap-2">
                                    <AlertCircle class="w-4 h-4 flex-shrink-0 mt-0.5" />
                                    {{ printError }}
                                </p>

                                <p class="text-muted text-[11px] mt-3">Dialog cetak browser akan terbuka. Pilih printer label atau simpan sebagai PDF.</p>
                            </template>
                        </Card>

                        <Card tone="lime-soft" class="border-l-4 border-lime">
                            <h3 class="font-bold text-ink font-display mb-2">Tips pemindaian</h3>
                            <ul class="text-sm text-muted space-y-1.5">
                                <li>&bull; Pastikan barcode tidak terlipat dan pencahayaan cukup.</li>
                                <li>&bull; Barcode barang dicari lebih dulu, lalu SKU, batch, dan lokasi.</li>
                                <li>&bull; Gunakan label batch untuk menandai produk yang memiliki tanggal kedaluwarsa.</li>
                            </ul>
                        </Card>
                    </div>
                </div>

                <!-- History -->
                <Card class="mt-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4">
                        <h2 class="text-xl font-bold text-ink font-display flex items-center gap-2">
                            <History class="w-5 h-5" />
                            Riwayat Cetak
                        </h2>
                        <p class="text-muted text-xs">20 cetak terakhir</p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b-2 border-line">
                                    <th class="text-left py-3 px-3 font-semibold text-muted">Waktu</th>
                                    <th class="text-left py-3 px-3 font-semibold text-muted">Label</th>
                                    <th class="text-left py-3 px-3 font-semibold text-muted">Kode</th>
                                    <th class="text-left py-3 px-3 font-semibold text-muted">Jenis</th>
                                    <th class="text-right py-3 px-3 font-semibold text-muted">Salinan</th>
                                    <th class="text-left py-3 px-3 font-semibold text-muted">Oleh</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="historyList.length === 0">
                                    <td colspan="6" class="px-4 py-10 text-center text-sm text-muted">Belum ada label yang dicetak.</td>
                                </tr>
                                <tr v-for="log in historyList" :key="log.id" class="border-b border-surface-2 hover:bg-surface-2">
                                    <td class="py-3 px-3 whitespace-nowrap">{{ formatDateTime(log.created_at) }}</td>
                                    <td class="py-3 px-3 font-semibold">{{ log.reference_label || '-' }}</td>
                                    <td class="py-3 px-3 font-mono text-xs">{{ log.reference_code || '-' }}</td>
                                    <td class="py-3 px-3">
                                        <StatusChip :status="typeStatusFor(log.label_type)">{{ typeLabel(log.label_type) }}</StatusChip>
                                    </td>
                                    <td class="py-3 px-3 text-right font-semibold">{{ formatNumber(log.copies, 0) }}</td>
                                    <td class="py-3 px-3 text-muted">{{ log.printed_by || '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </Card>
            </div>
        </div>

        <!-- Print area: only visible while printing -->
        <div id="label-print-area" class="hidden print:block">
            <LabelPreview v-for="copy in copies" :key="copy" :label="selectedLabel" />
        </div>
    </AuthenticatedLayout>
</template>

<style>
@media print {
    body.printing-label * {
        visibility: hidden;
    }

    body.printing-label #label-print-area,
    body.printing-label #label-print-area * {
        visibility: visible;
    }

    #label-print-area {
        display: block !important;
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        margin: 0;
        padding: 0;
    }

    #label-print-area .label-card {
        width: 60mm;
        min-height: 40mm;
        margin: 0 0 3mm 0;
        border: 1px solid #14123a;
        break-inside: avoid;
        page-break-inside: avoid;
    }

    #label-print-area .label-card img {
        height: 12mm;
    }

    @page {
        margin: 5mm;
    }
}
</style>
