<script setup>
import { ref, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import AccentButton from '@/Components/AccentButton.vue';
import FlashMessage from '@/Components/FlashMessage.vue';

const page = usePage();
const user = page.props.auth.user;
const showProfileMenu = ref(false);
const activeDropdown = ref(null);
const showMobileMenu = ref(false);
const mobileOpenGroup = ref(null);

const navigation = [
    { label: 'Dashboard', route: 'dashboard', direct: true },
    {
        label: 'Master Data',
        items: [
            { label: 'Barang', route: 'products.index' },
            { label: 'Kategori', route: 'categories.index' },
            { label: 'Satuan', route: 'units.index' },
            { label: 'Supplier', route: 'suppliers.index' },
            { label: 'Lokasi', route: 'locations.index' },
        ]
    },
    {
        label: 'Transaksi',
        items: [
            { label: 'Barang Masuk', route: 'stock-ins.index' },
            { label: 'Barang Keluar', route: 'stock-outs.index' },
            { label: 'Penyesuaian Stok', route: 'stock-adjustments.index' },
        ]
    },
];

const toggleDropdown = (label) => {
    activeDropdown.value = activeDropdown.value === label ? null : label;
};

const closeMenus = () => {
    activeDropdown.value = null;
    showProfileMenu.value = false;
};

const toggleMobileGroup = (label) => {
    mobileOpenGroup.value = mobileOpenGroup.value === label ? null : label;
};

const closeMobileMenu = () => {
    showMobileMenu.value = false;
    mobileOpenGroup.value = null;
};

const isDropdownActive = (dropdownItems) => {
    return dropdownItems.some(item => route().current(item.route));
};

// Lock scroll when mobile menu open
watch(showMobileMenu, (val) => {
    document.body.style.overflow = val ? 'hidden' : '';
});
</script>

<template>
    <div>
        <FlashMessage />
        <!-- Topbar -->
        <nav class="fixed top-0 left-0 right-0 h-16 bg-ink flex items-center px-4 sm:px-6 z-50 shadow-hard">
            <div class="flex items-center justify-between w-full max-w-full gap-2">
                <!-- Left: Hamburger + Logo -->
                <div class="flex items-center gap-2 flex-shrink-0">
                    <!-- Hamburger (mobile) -->
                    <button @click="showMobileMenu = true" class="lg:hidden text-white hover:text-lime transition p-2 -ml-2" aria-label="Buka menu">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <Link :href="route('dashboard')" class="text-lime font-bold text-lg sm:text-xl font-display hover:text-yellow-300 transition flex-shrink-0">
                        GudangKu
                    </Link>
                </div>

                <!-- Navigation - Desktop Center -->
                <div class="hidden lg:flex gap-6 flex-1 justify-center ml-16">
                    <Link v-for="item in navigation.filter(n => n.direct)" :key="item.label" :href="route(item.route)" 
                        :class="['px-3 py-2 text-white text-sm font-semibold hover:text-lime hover:bg-ink-3 transition whitespace-nowrap', route().current(item.route) ? 'text-lime border-b-2 border-lime' : '']">
                        {{ item.label }}
                    </Link>

                    <div v-for="item in navigation.filter(n => !n.direct)" :key="item.label" class="relative z-50">
                        <button @click="toggleDropdown(item.label)" 
                            :class="['px-3 py-2 text-white text-sm font-semibold hover:text-lime transition flex items-center gap-1 whitespace-nowrap hover:border-b-2 hover:border-lime', activeDropdown === item.label || isDropdownActive(item.items) ? 'text-lime border-b-2 border-lime' : 'border-b-2 border-transparent']">
                            {{ item.label }}
                            <svg :class="['w-4 h-4 transition', activeDropdown === item.label ? 'rotate-180' : '']" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>

                        <div v-show="activeDropdown === item.label" class="absolute left-0 top-full mt-1 w-52 bg-surface border-2 border-ink shadow-hard z-[9999] py-1">
                            <Link v-for="subitem in item.items" :key="subitem.label" :href="route(subitem.route)" 
                                @click="closeMenus"
                                :class="['block px-6 py-3 text-sm text-ink hover:bg-surface-2 hover:text-violet-text transition border-b border-line last:border-b-0', route().current(subitem.route) ? 'bg-hi font-semibold' : '']">
                                {{ subitem.label }}
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Right Actions -->
                <div class="flex items-center gap-1 sm:gap-4 ml-auto">
                    <AccentButton class="text-xs hidden md:inline-flex whitespace-nowrap">Scan & Label</AccentButton>

                    <div class="relative">
                        <button @click="showProfileMenu = !showProfileMenu" 
                            :class="['flex items-center gap-2 px-2 sm:px-3 py-2 text-white hover:text-lime transition text-sm font-semibold', showProfileMenu ? 'text-lime' : '']">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                            </svg>
                            <span class="hidden sm:inline">{{ user.name }}</span>
                        </button>

                        <div v-if="showProfileMenu" class="absolute right-0 top-full mt-1 w-48 bg-surface border-2 border-ink shadow-hard z-50 py-1">
                            <Link :href="route('profile.edit')" @click="closeMenus" class="block px-6 py-3 text-sm text-ink hover:bg-surface-2 border-b border-line">
                                Profil Saya
                            </Link>
                            <Link :href="route('logout')" method="post" as="button" @click="closeMenus" 
                                class="w-full text-left px-6 py-3 text-sm text-ink hover:bg-surface-2">
                                Keluar
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Click outside to close dropdowns -->
        <div v-if="activeDropdown || showProfileMenu" @click="closeMenus" class="fixed inset-0 z-40 mt-16"></div>

        <!-- Mobile Drawer -->
        <Transition
            enter-active-class="transition-opacity duration-200"
            enter-from-class="opacity-0"
            leave-active-class="transition-opacity duration-200"
            leave-to-class="opacity-0"
        >
            <div v-if="showMobileMenu" class="lg:hidden fixed inset-0 z-[9998] bg-black/50" @click="closeMobileMenu"></div>
        </Transition>

        <Transition
            enter-active-class="transition-transform duration-300 ease-out"
            enter-from-class="-translate-x-full"
            leave-active-class="transition-transform duration-300 ease-in"
            leave-to-class="-translate-x-full"
        >
            <aside v-if="showMobileMenu" class="lg:hidden fixed top-0 left-0 bottom-0 w-72 max-w-[85vw] bg-ink z-[9999] flex flex-col shadow-hard">
                <!-- Drawer Header -->
                <div class="h-16 flex items-center justify-between px-4 border-b border-ink-3 flex-shrink-0">
                    <Link :href="route('dashboard')" @click="closeMobileMenu" class="text-lime font-bold text-xl font-display">
                        GudangKu
                    </Link>
                    <button @click="closeMobileMenu" class="text-white hover:text-lime transition p-2 -mr-2" aria-label="Tutup menu">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Drawer Nav -->
                <div class="flex-1 overflow-y-auto py-2">
                    <!-- Direct links -->
                    <Link v-for="item in navigation.filter(n => n.direct)" :key="item.label"
                        :href="route(item.route)" @click="closeMobileMenu"
                        :class="['block px-5 py-3 text-sm font-semibold border-b border-ink-3', route().current(item.route) ? 'text-lime bg-ink-3' : 'text-white hover:text-lime hover:bg-ink-3 transition']">
                        {{ item.label }}
                    </Link>

                    <!-- Groups (accordion) -->
                    <div v-for="item in navigation.filter(n => !n.direct)" :key="item.label" class="border-b border-ink-3">
                        <button @click="toggleMobileGroup(item.label)"
                            :class="['w-full flex items-center justify-between px-5 py-3 text-sm font-semibold', isDropdownActive(item.items) ? 'text-lime' : 'text-white hover:text-lime transition']">
                            {{ item.label }}
                            <svg :class="['w-4 h-4 transition', mobileOpenGroup === item.label ? 'rotate-180' : '']" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        <div v-show="mobileOpenGroup === item.label" class="bg-ink-2 pb-1">
                            <Link v-for="subitem in item.items" :key="subitem.label"
                                :href="route(subitem.route)" @click="closeMobileMenu"
                                :class="['block pl-8 pr-5 py-2.5 text-sm', route().current(subitem.route) ? 'text-lime font-semibold' : 'text-white/80 hover:text-lime transition']">
                                {{ subitem.label }}
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Drawer Footer -->
                <div class="border-t border-ink-3 p-4 flex-shrink-0">
                    <p class="text-white/60 text-xs mb-1">Masuk sebagai</p>
                    <p class="text-white text-sm font-semibold mb-3 truncate">{{ user.name }}</p>
                    <Link :href="route('logout')" method="post" as="button" @click="closeMobileMenu"
                        class="w-full px-4 py-2 text-sm font-semibold text-ink bg-lime hover:bg-yellow-300 transition text-center block">
                        Keluar
                    </Link>
                </div>
            </aside>
        </Transition>

        <!-- Page Content -->
        <div class="mt-16 bg-canvas min-h-screen overflow-visible">
            <slot />
        </div>
    </div>
</template>