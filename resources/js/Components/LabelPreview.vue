<script setup>
import { computed } from 'vue';

const props = defineProps({
    label: {
        type: Object,
        default: null,
    },
});

const TYPE_LABELS = {
    product: 'Barang',
    batch: 'Batch',
    location: 'Lokasi',
};

const barcodeUrl = computed(() => {
    if (!props.label?.code) {
        return null;
    }

    return route('scan-label.barcode', { code: props.label.code });
});
</script>

<template>
    <div v-if="label" class="label-card bg-white border-2 border-ink p-3 text-ink">
        <div class="flex items-center justify-between gap-2 border-b border-ink/20 pb-1.5 mb-2">
            <span class="text-[9px] font-bold tracking-[0.22em]">GUDANGKU</span>
            <span class="text-[9px] font-semibold uppercase tracking-wide bg-ink text-white px-1.5 py-0.5">
                {{ TYPE_LABELS[label.type] ?? label.type }}
            </span>
        </div>

        <p class="font-display font-bold text-sm leading-tight break-words">{{ label.title }}</p>
        <p class="text-[10px] text-muted mt-0.5 mb-2 break-words">{{ label.subtitle }}</p>

        <div v-if="label.meta?.length" class="grid grid-cols-2 gap-x-2 gap-y-1 text-[9px] mb-2">
            <div v-for="row in label.meta" :key="row.label" class="min-w-0">
                <p class="text-muted uppercase tracking-wide">{{ row.label }}</p>
                <p class="font-semibold break-words">{{ row.value }}</p>
            </div>
        </div>

        <img
            v-if="barcodeUrl"
            :src="barcodeUrl"
            :alt="`Barcode ${label.code}`"
            class="w-full h-12 object-contain"
        />
        <p class="text-center font-mono text-[10px] tracking-[0.18em] mt-1">{{ label.code }}</p>
    </div>
</template>
