<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { ref } from 'vue';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Masuk" />

    <div class="min-h-screen flex">
        <!-- Left Panel: Branding (ink background) -->
        <div class="hidden lg:flex lg:w-1/2 bg-ink flex-col justify-between p-12">
            <div>
                <h1 class="text-4xl font-bold text-lime font-display">GudangKu</h1>
                <p class="text-white text-lg mt-4">Sistem Inventaris Gudang Modern</p>
                <p class="text-white text-sm mt-2 opacity-80">Kelola stok barang dengan akurat, real-time, dan terpercaya.</p>
            </div>
            
            <!-- Decorative barcode pattern -->
            <div class="space-y-2 opacity-20">
                <div class="h-1 w-48 bg-lime"></div>
                <div class="h-1 w-32 bg-lime"></div>
                <div class="h-1 w-40 bg-lime"></div>
                <div class="h-1 w-28 bg-lime"></div>
            </div>
        </div>

        <!-- Right Panel: Form (canvas background) -->
        <div class="w-full lg:w-1/2 bg-canvas flex items-center justify-center p-6">
            <div class="w-full max-w-md">
                <!-- Mobile logo -->
                <div class="lg:hidden mb-8">
                    <h1 class="text-3xl font-bold text-ink font-display">GudangKu</h1>
                </div>

                <!-- Form Card -->
                <div class="bg-surface border-2 border-ink p-8">
                    <h2 class="text-2xl font-bold text-ink font-display mb-6">Masuk</h2>

                    <!-- Status message -->
                    <div v-if="status" class="mb-4 p-4 bg-ok-bg border-2 border-ok-fg text-ok-fg text-sm font-semibold">
                        {{ status }}
                    </div>

                    <!-- Error messages -->
                    <div v-if="form.errors.email || form.errors.password" class="mb-4 p-4 bg-bad-bg border-2 border-bad-fg text-bad-fg text-sm">
                        <p v-if="form.errors.email" class="font-semibold">{{ form.errors.email }}</p>
                        <p v-if="form.errors.password" class="font-semibold">{{ form.errors.password }}</p>
                    </div>

                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Email -->
                        <div>
                            <label class="block text-xs font-semibold text-muted mb-2">Email</label>
                            <input
                                v-model="form.email"
                                type="email"
                                placeholder="admin@gudangku.test"
                                class="w-full h-12 px-4 border-2 border-line text-sm focus:border-ink focus:outline-none"
                                required
                                autofocus
                            />
                        </div>

                        <!-- Password -->
                        <div>
                            <label class="block text-xs font-semibold text-muted mb-2">Kata Sandi</label>
                            <div class="relative">
                                <input
                                    v-model="form.password"
                                    :type="showPassword ? 'text' : 'password'"
                                    class="w-full h-12 px-4 border-2 border-line text-sm focus:border-ink focus:outline-none pr-12"
                                    required
                                />
                                <button
                                    type="button"
                                    @click="showPassword = !showPassword"
                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-ink hover:text-ink-2 text-sm font-semibold"
                                >
                                    {{ showPassword ? 'Sembunyikan' : 'Tampilkan' }}
                                </button>
                            </div>
                        </div>

                        <!-- Remember me -->
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input
                                v-model="form.remember"
                                type="checkbox"
                                class="w-5 h-5 border-2 border-ink accent-lime"
                            />
                            <span class="text-sm text-muted">Ingat saya</span>
                        </label>

                        <!-- Submit button -->
                        <PrimaryButton
                            :disabled="form.processing"
                            type="submit"
                            class="w-full"
                        >
                            {{ form.processing ? 'Memproses...' : 'Masuk' }}
                        </PrimaryButton>

                        <!-- Forgot password -->
                        <div v-if="canResetPassword" class="text-center">
                            <Link
                                :href="route('password.request')"
                                class="text-violet-text text-sm font-semibold hover:underline"
                            >
                                Lupa kata sandi?
                            </Link>
                        </div>
                    </form>
                </div>

                <!-- Footer -->
                <p class="text-center text-muted text-xs mt-6">
                    Demo: admin@gudangku.test / password
                </p>
            </div>
        </div>
    </div>
</template>
