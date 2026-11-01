<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { usePage, router } from '@inertiajs/vue3';

const page = usePage();
const showSuccess = ref(false);
const showError = ref(false);
const successMessage = ref('');
const errorMessage = ref('');
let successTimer = null;
let errorTimer = null;

const checkFlash = (flash) => {
    if (!flash) return;

    if (flash.success || flash.message) {
        successMessage.value = flash.success || flash.message;
        showSuccess.value = true;
        clearTimeout(successTimer);
        successTimer = setTimeout(() => (showSuccess.value = false), 5000);
    }
    if (flash.error) {
        errorMessage.value = flash.error;
        showError.value = true;
        clearTimeout(errorTimer);
        errorTimer = setTimeout(() => (showError.value = false), 5000);
    }
};

// Initial load
checkFlash(page.props.flash);

// Fire on every successful Inertia visit (after redirect with flash)
const removeListener = router.on('success', (event) => {
    checkFlash(event.detail.page.props.flash);
});

onUnmounted(() => {
    removeListener();
    clearTimeout(successTimer);
    clearTimeout(errorTimer);
});
</script>

<template>
    <Teleport to="body">
        <!-- Success Toast -->
        <div v-if="showSuccess" class="fixed top-20 right-6 z-[9999] animate-slide-in">
            <div class="bg-lime/95 border-2 border-lime text-ink px-6 py-4 rounded shadow-hard flex items-center gap-3 min-w-[300px] max-w-md">
                <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span class="text-sm font-semibold">{{ successMessage }}</span>
                <button @click="showSuccess = false" class="ml-auto text-ink hover:text-lime transition">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Error Toast -->
        <div v-if="showError" class="fixed top-20 right-6 z-[9999] animate-slide-in">
            <div class="bg-red-400/95 border-2 border-red-400 text-ink px-6 py-4 rounded shadow-hard flex items-center gap-3 min-w-[300px] max-w-md">
                <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
                <span class="text-sm font-semibold">{{ errorMessage }}</span>
                <button @click="showError = false" class="ml-auto text-ink hover:text-red-400 transition">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" />
                    </svg>
                </button>
            </div>
        </div>
    </Teleport>
</template>