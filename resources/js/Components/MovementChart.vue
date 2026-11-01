<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import Chart from 'chart.js/auto';
import { formatDateShort, formatNumber } from '@/Utils/format';
import { PackageOpen } from '@lucide/vue';

const props = defineProps({
    labels: {
        type: Array,
        default: () => [],
    },
    inData: {
        type: Array,
        default: () => [],
    },
    outData: {
        type: Array,
        default: () => [],
    },
});

const canvasEl = ref(null);
let chartInstance = null;

const hasData = computed(() =>
    props.inData.some((value) => Number(value) > 0)
    || props.outData.some((value) => Number(value) > 0),
);

const displayLabels = computed(() => props.labels.map((label) => formatDateShort(label)));

const buildChart = () => {
    if (!canvasEl.value || !hasData.value) {
        return;
    }

    chartInstance?.destroy();

    chartInstance = new Chart(canvasEl.value, {
        type: 'bar',
        data: {
            labels: displayLabels.value,
            datasets: [
                {
                    label: 'Masuk',
                    data: props.inData.map((value) => Number(value)),
                    backgroundColor: '#C6F432',
                    borderColor: '#14123A',
                    borderWidth: 2,
                    borderRadius: 0,
                    maxBarThickness: 14,
                },
                {
                    label: 'Keluar',
                    data: props.outData.map((value) => Number(value)),
                    backgroundColor: '#F87171',
                    borderColor: '#14123A',
                    borderWidth: 2,
                    borderRadius: 0,
                    maxBarThickness: 14,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    position: 'top',
                    align: 'end',
                    labels: {
                        color: '#4A4770',
                        boxWidth: 12,
                        boxHeight: 12,
                        borderWidth: 0,
                        font: { family: "'Plus Jakarta Sans', sans-serif", size: 12 },
                        padding: 16,
                    },
                },
                tooltip: {
                    backgroundColor: '#14123A',
                    titleColor: '#C6F432',
                    bodyColor: '#FFFFFF',
                    titleFont: { family: "'Bricolage Grotesque', sans-serif", size: 13 },
                    bodyFont: { family: "'Plus Jakarta Sans', sans-serif", size: 12 },
                    padding: 10,
                    cornerRadius: 0,
                    callbacks: {
                        label: (context) => ` ${context.dataset.label}: ${formatNumber(context.parsed.y, 0)} unit`,
                    },
                },
            },
            scales: {
                x: {
                    grid: { display: false },
                    border: { color: '#D5D7E6' },
                    ticks: {
                        color: '#4A4770',
                        font: { family: "'Plus Jakarta Sans', sans-serif", size: 11 },
                        maxRotation: 0,
                        autoSkip: true,
                        maxTicksLimit: 10,
                    },
                },
                y: {
                    beginAtZero: true,
                    border: { display: false },
                    grid: { color: 'rgba(213, 215, 230, 0.55)' },
                    ticks: {
                        color: '#4A4770',
                        font: { family: "'Plus Jakarta Sans', sans-serif", size: 11 },
                        precision: 0,
                        callback: (value) => formatNumber(value, 0),
                    },
                },
            },
        },
    });
};

onMounted(buildChart);

watch(
    () => [props.labels, props.inData, props.outData],
    () => buildChart(),
    { deep: true },
);

onBeforeUnmount(() => {
    chartInstance?.destroy();
    chartInstance = null;
});
</script>

<template>
    <div v-if="hasData" class="h-64 sm:h-72 w-full">
        <canvas ref="canvasEl" />
    </div>
    <div v-else class="h-64 sm:h-72 flex flex-col items-center justify-center gap-2 bg-surface-2 border-2 border-line text-muted text-sm">
        <PackageOpen class="w-8 h-8" />
        Belum ada mutasi stok dalam 30 hari terakhir
    </div>
</template>
