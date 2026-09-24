<script setup lang="ts">
import { computed, ref } from 'vue';
import GuestShell from '@/Layouts/GuestShell.vue';
import GalleryGrid from '@/Components/GalleryGrid.vue';
import { useI18n } from '@/i18n';

defineOptions({ layout: GuestShell });

export interface Branch {
    id: number;
    slug: string;
    name: string;
    city: string | null;
    address: string | null;
    phone: string | null;
    email: string | null;
    short_description: string | null;
    description: string | null;
    cover_image_url: string | null;
    gallery_urls: string[];
    all_photos: string[];
    features: string[];
    latitude: number | null;
    longitude: number | null;
    is_main: boolean;
}

const props = withDefaults(
    defineProps<{
        branches: Branch[];
        active_branch?: string | null;
        title?: string;
        subtitle?: string;
    }>(),
    {
        active_branch: null,
        title: 'Our Branches',
        subtitle: 'Explore our luxury destinations and prime locations',
    },
);

const { t, isRtl } = useI18n();

// Selected branch tab slug
const activeTabSlug = ref<string>(
    props.active_branch || props.branches[0]?.slug || '',
);

// Resolve currently selected branch
const currentBranch = computed<Branch | null>(() => {
    return props.branches.find((b) => b.slug === activeTabSlug.value) || props.branches[0] || null;
});

// Format images for GalleryGrid
const galleryImages = computed(() => {
    if (!currentBranch.value) return [];
    const photos = currentBranch.value.all_photos.length
        ? currentBranch.value.all_photos
        : (currentBranch.value.cover_image_url ? [currentBranch.value.cover_image_url] : []);

    return photos.map((url, i) => ({
        url,
        alt_text: `${currentBranch.value?.name} - Photo ${i + 1}`,
        type: 'image' as const,
    }));
});

// Selected photo index for the prominent showcase preview
const selectedPhotoIndex = ref<number>(0);

function selectTab(slug: string) {
    activeTabSlug.value = slug;
    selectedPhotoIndex.value = 0;
}
</script>

<template>
    <div class="branches-page min-h-screen bg-white">
        <div class="mx-auto max-w-7xl px-6 lg:px-10 pt-24 lg:pt-28 pb-16 lg:pb-24">
            <!-- Page Header -->
            <div class="mb-8 lg:mb-12">
                <span class="text-xs uppercase tracking-[0.25em] font-semibold" style="color: var(--luxury-champagne, #c5a880)">
                    {{ t('branches.title', undefined, 'Our Branches') }}
                </span>
                <h1
                    class="mt-2 text-3xl lg:text-5xl tracking-tight text-slate-900"
                    style="font-family: var(--font-display); color: var(--color-primary, #1f4b5a)"
                >
                    {{ title }}
                </h1>
                <p class="mt-2 text-base lg:text-lg text-slate-500 max-w-2xl">
                    {{ subtitle }}
                </p>
            </div>

            <!-- Empty State -->
            <div v-if="!branches.length" class="text-center py-16">
                <p class="text-xl text-slate-400">{{ t('branches.empty', undefined, 'No branches available at the moment.') }}</p>
            </div>

            <!-- Branch Content with Tabs -->
            <div v-else>
                <!-- Branch Tabs Row -->
                <div class="mb-8 border-b border-slate-200">
                    <div class="no-scrollbar flex items-center gap-3 overflow-x-auto pb-4 snap-x">
                        <button
                            v-for="branch in branches"
                            :key="branch.slug"
                            type="button"
                            class="group shrink-0 flex items-center gap-3 px-5 py-3 rounded-2xl text-base font-medium transition-all duration-200 cursor-pointer border snap-start"
                            :class="[
                                branch.slug === activeTabSlug
                                    ? 'shadow-md border-transparent text-white'
                                    : 'border-slate-200 bg-slate-50 hover:bg-white hover:border-slate-300 text-slate-700',
                            ]"
                            :style="branch.slug === activeTabSlug ? { background: 'var(--color-primary, #1f4b5a)' } : undefined"
                            @click="selectTab(branch.slug)"
                        >
                            <span class="text-xl">🏨</span>
                            <div class="text-start">
                                <span class="block leading-snug font-semibold">{{ branch.name }}</span>
                                <span
                                    v-if="branch.city"
                                    class="block text-xs opacity-75 mt-0.5"
                                    :class="branch.slug === activeTabSlug ? 'text-white/80' : 'text-slate-500'"
                                >
                                    {{ branch.city }}
                                </span>
                            </div>
                            <span
                                v-if="branch.is_main"
                                class="ms-1.5 px-2 py-0.5 rounded-full text-[10px] uppercase font-bold tracking-wider"
                                :class="branch.slug === activeTabSlug ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700'"
                            >
                                {{ t('branches.main_branch', undefined, 'Main') }}
                            </span>
                        </button>
                    </div>
                </div>

                <!-- Active Branch Details View -->
                <div v-if="currentBranch" class="space-y-10 lg:space-y-12">
                    <!-- Branch Hero & Overview Card -->
                    <div class="rounded-3xl border border-slate-200/80 bg-slate-50/50 p-6 lg:p-10 shadow-xs">
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                            <!-- Left / Info Column -->
                            <div class="lg:col-span-7 space-y-4">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span
                                        v-if="currentBranch.is_main"
                                        class="px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wider text-slate-900"
                                        style="background: var(--luxury-champagne, #c5a880)"
                                    >
                                        {{ t('branches.flagship', undefined, 'Flagship Property') }}
                                    </span>
                                    <span v-if="currentBranch.city" class="text-sm font-medium text-slate-500">
                                        📍 {{ currentBranch.city }}
                                    </span>
                                </div>

                                <h2
                                    class="text-3xl lg:text-4xl text-slate-900 tracking-tight"
                                    style="font-family: var(--font-display)"
                                >
                                    {{ currentBranch.name }}
                                </h2>

                                <p v-if="currentBranch.short_description" class="text-lg text-slate-600 leading-relaxed font-medium">
                                    {{ currentBranch.short_description }}
                                </p>

                                <p v-if="currentBranch.description" class="text-base text-slate-500 leading-relaxed">
                                    {{ currentBranch.description }}
                                </p>

                                <!-- Address & Quick Actions -->
                                <div class="pt-4 border-t border-slate-200/80 flex flex-wrap items-center gap-4">
                                    <a
                                        v-if="currentBranch.phone"
                                        :href="`tel:${currentBranch.phone}`"
                                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-sm font-semibold text-white shadow-sm active:scale-95 transition-all"
                                        style="background: var(--color-primary, #1f4b5a)"
                                    >
                                        <span>📞</span>
                                        <span>{{ currentBranch.phone }}</span>
                                    </a>

                                    <a
                                        v-if="currentBranch.email"
                                        :href="`mailto:${currentBranch.email}`"
                                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-sm font-medium border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 active:scale-95 transition-all"
                                    >
                                        <span>✉️</span>
                                        <span>{{ t('branches.email_us', undefined, 'Email') }}</span>
                                    </a>

                                    <a
                                        v-if="currentBranch.latitude && currentBranch.longitude"
                                        :href="`https://www.google.com/maps/search/?api=1&query=${currentBranch.latitude},${currentBranch.longitude}`"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-sm font-medium border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 active:scale-95 transition-all"
                                    >
                                        <span>🗺️</span>
                                        <span>{{ t('branches.get_directions', undefined, 'Get Directions') }}</span>
                                    </a>
                                </div>
                            </div>

                            <!-- Right / Address Box -->
                            <div class="lg:col-span-5 rounded-2xl bg-white p-6 shadow-sm border border-slate-200 space-y-5">
                                <div>
                                    <span class="block text-xs uppercase tracking-wider text-slate-400 font-semibold mb-1">
                                        {{ t('branches.address', undefined, 'Address') }}
                                    </span>
                                    <p class="text-lg font-medium text-slate-800 leading-snug">
                                        {{ currentBranch.address || currentBranch.city || '—' }}
                                    </p>
                                </div>

                                <div v-if="currentBranch.phone" class="pt-3 border-t border-slate-100">
                                    <span class="block text-xs uppercase tracking-wider text-slate-400 font-semibold mb-1">
                                        {{ t('branches.phone', undefined, 'Phone') }}
                                    </span>
                                    <p class="text-base text-slate-700 font-mono">
                                        {{ currentBranch.phone }}
                                    </p>
                                </div>

                                <div v-if="currentBranch.email" class="pt-3 border-t border-slate-100">
                                    <span class="block text-xs uppercase tracking-wider text-slate-400 font-semibold mb-1">
                                        {{ t('branches.email', undefined, 'Email') }}
                                    </span>
                                    <p class="text-base text-slate-700 font-mono break-all">
                                        {{ currentBranch.email }}
                                    </p>
                                </div>

                                <!-- Features & Amenities Tags -->
                                <div v-if="currentBranch.features && currentBranch.features.length" class="pt-3 border-t border-slate-100">
                                    <span class="block text-xs uppercase tracking-wider text-slate-400 font-semibold mb-2">
                                        {{ t('branches.features', undefined, 'Highlights & Amenities') }}
                                    </span>
                                    <div class="flex flex-wrap gap-1.5">
                                        <span
                                            v-for="feat in currentBranch.features"
                                            :key="feat"
                                            class="inline-block px-3 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-700"
                                        >
                                            {{ feat }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Branch Photos & Gallery Section -->
                    <div>
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h3
                                    class="text-2xl lg:text-3xl text-slate-900 tracking-tight"
                                    style="font-family: var(--font-display)"
                                >
                                    {{ t('branches.photos', undefined, 'Photos') }}
                                </h3>
                                <p class="text-sm text-slate-500 mt-1">
                                    {{ currentBranch.name }} — {{ t('branches.gallery', undefined, 'Photo Gallery') }}
                                </p>
                            </div>
                        </div>

                        <!-- Gallery Photos Grid with interactive lightbox -->
                        <div v-if="galleryImages.length">
                            <GalleryGrid :images="galleryImages" :columns="3" />
                        </div>
                        <p v-else class="text-slate-400 text-sm">
                            {{ t('common.empty', undefined, 'No photos available for this branch.') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.branches-page {
    font-family: var(--font-sans);
}
</style>
