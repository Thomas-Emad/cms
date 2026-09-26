<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { TEMPLATES, TILE_DIMENSIONS, type GuestLayoutConfig, type MenuItem, type TileSize } from '@/Layouts/guest/shellConfig';
import ClassicShell from '@/Layouts/guest/ClassicShell.vue';
import TvShell from '@/Layouts/guest/TvShell.vue';
import { router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useI18n } from '@/i18n';

defineOptions({ layout: AdminLayout });

interface BranchItem {
    id: number;
    name: string;
    slug: string;
    city?: string | null;
}

const props = defineProps<{
    config: GuestLayoutConfig;
    defaults: GuestLayoutConfig;
    errors_list: string[];
    flash_ok: string | null;
    branches?: BranchItem[];
    selected_branch_id?: number | null;
    selected_branch?: BranchItem | null;
    has_custom_branch_layout?: boolean;
}>();

const { t, locale } = useI18n();

const form = useForm<GuestLayoutConfig>({
    ...props.config,
    items: props.config.items.map((i) => ({ ...i })),
});

function selectBranch(branchId: number | null) {
    router.get('/admin/layout', branchId ? { branch_id: branchId } : {}, {
        preserveState: false,
    });
}

function resetToMasterLayout() {
    if (!props.selected_branch_id) return;
    const msg = locale.value === 'ar'
        ? 'هل أنت متأكد من رغبتك في حذف التخطيط المخصص لهذا الفرع والرجوع لوراثة التخطيط العام للفندق؟'
        : 'Are you sure you want to reset this branch layout to inherit the hotel master layout?';
    if (confirm(msg)) {
        router.delete('/admin/layout/branch-reset', {
            data: { hotel_branch_id: props.selected_branch_id },
        });
    }
}

function addItem() {
    form.items.push({ href: '/', label: 'New item', icon: '⭐', color: null, visible: true });
}
function removeItem(i: number) {
    form.items.splice(i, 1);
}
function move(i: number, dir: -1 | 1) {
    const j = i + dir;
    if (j < 0 || j >= form.items.length) return;
    [form.items[i], form.items[j]] = [form.items[j], form.items[i]];
}
function resetItems() {
    if (confirm(t('admin.layout.reset_confirm', undefined, 'Replace the menu with the default items?'))) form.items = props.defaults.items.map((i) => ({ ...i }));
}

const save = () => {
    form.transform((data) => ({
        ...data,
        hotel_branch_id: props.selected_branch_id ?? null,
    })).put('/admin/layout', { preserveScroll: true });
};

// A safe, real Hotel object for the live preview (not the actual current hotel).
const previewHotel = { name: props.selected_branch ? props.selected_branch.name : 'Grand Horizon', slug: 'preview' } as any;
const previewTab = ref<'home' | 'other'>('home');
const previewConfig = computed<GuestLayoutConfig>(() => form.data());

const field = 'w-full rounded-md border border-slate-300 px-2.5 py-1.5 text-sm';
const label = 'mb-1 mt-3 block text-xs font-medium text-slate-500';
</script>

<template>
    <div class="space-y-6 max-w-7xl">
        <!-- Branch Switcher Tabs -->
        <div v-if="branches && branches.length > 0" class="flex items-center gap-2 border-b border-slate-200 pb-3 overflow-x-auto">
            <button
                type="button"
                class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold rounded-lg transition-colors whitespace-nowrap"
                :class="!selected_branch_id ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                @click="selectBranch(null)"
            >
                <span>🏨</span>
                <span>{{ locale === 'ar' ? 'التخطيط العام للفندق (كل الفروع)' : 'Hotel Master Layout (All Branches)' }}</span>
            </button>
            <button
                v-for="b in branches"
                :key="b.id"
                type="button"
                class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold rounded-lg transition-colors whitespace-nowrap"
                :class="selected_branch_id === b.id ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                @click="selectBranch(b.id)"
            >
                <span>📍</span>
                <span>{{ b.name }} {{ b.city ? `(${b.city})` : '' }}</span>
            </button>
        </div>

        <!-- Branch Context Banner -->
        <div v-if="selected_branch" class="space-y-2">
            <div v-if="has_custom_branch_layout" class="rounded-lg bg-blue-50 border border-blue-200 p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <span class="text-base">📺</span>
                    <div>
                        <h3 class="text-xs font-semibold text-blue-900">
                            {{ locale === 'ar' ? `تخطيط مخصص نشط لفرع: ${selected_branch.name}` : `Custom Layout Active for: ${selected_branch.name}` }}
                        </h3>
                        <p class="text-xs text-blue-700 mt-0.5">
                            {{ locale === 'ar' ? 'هذا الفرع يستخدم حالياً تخطيطاً مستقلاً عن التخطيط العام للفندق.' : 'This branch is currently rendered using its own dedicated screen layout and navigation menu.' }}
                        </p>
                    </div>
                </div>
                <button
                    type="button"
                    class="text-xs font-medium text-red-600 hover:text-red-700 underline shrink-0"
                    @click="resetToMasterLayout"
                >
                    {{ locale === 'ar' ? 'إعادة التعيين لوراثة التخطيط العام' : 'Reset to Master Layout' }}
                </button>
            </div>
            <div v-else class="rounded-lg bg-amber-50 border border-amber-200 p-4 flex items-center gap-2.5">
                <span class="text-base">ℹ️</span>
                <div>
                    <h3 class="text-xs font-semibold text-amber-900">
                        {{ locale === 'ar' ? `يرث التخطيط العام للفندق: ${selected_branch.name}` : `Inheriting Master Layout: ${selected_branch.name}` }}
                    </h3>
                    <p class="text-xs text-amber-700 mt-0.5">
                        {{ locale === 'ar' ? 'يقوم هذا الفرع حالياً بوراثة التخطيط العام للفندق. عند إجراء تعديلات وحفظها، سيتم إنشاء تخطيط مخصص لهذا الفرع فقط.' : 'This branch currently inherits the master hotel layout. Modifying and clicking save will publish a custom layout override for this branch.' }}
                    </p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-[26rem_1fr]">
            <!-- ============ form ============ -->
            <form class="min-w-0" @submit.prevent="save">
                <h1 class="text-xl font-semibold text-slate-800">
                    {{ selected_branch ? (locale === 'ar' ? `تخطيط شاشة النزيل: ${selected_branch.name}` : `Guest Layout: ${selected_branch.name}`) : $t('admin.layout.title', undefined, 'Guest Layout') }}
                </h1>
                <p class="mt-1 text-sm text-slate-500">{{ $t('admin.layout.subtitle') }}</p>

                <p v-if="flash_ok && !form.isDirty" class="mt-3 rounded-md bg-emerald-50 px-3 py-2 text-sm text-emerald-700"
                    data-testid="saved">{{ flash_ok }}</p>
                <div v-if="errors_list.length"
                    class="mt-3 rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700" data-testid="errors">
                    <p class="font-medium">Not saved:</p>
                    <ul class="mt-1 list-disc pl-5">
                        <li v-for="e in errors_list" :key="e">{{ e }}</li>
                    </ul>
                </div>

                <!-- template picker -->
                <div class="mt-4 grid grid-cols-2 gap-3">
                    <button v-for="t in TEMPLATES" :key="t.id" type="button"
                        class="rounded-lg border p-3 text-start transition"
                        :class="form.template === t.id ? 'border-slate-800 ring-2 ring-slate-800' : 'border-slate-200 hover:bg-slate-50'"
                        :data-testid="`template-${t.id}`" @click="form.template = t.id">
                        <span class="block text-sm font-semibold text-slate-800">{{ t.name }}</span>
                        <span class="mt-0.5 block text-xs text-slate-500">{{ t.description }}</span>
                    </button>
                </div>

                <div class="mt-2 rounded-lg border border-slate-200 bg-white p-4">
                    <label class="flex items-center justify-between text-sm">{{ $t('admin.layout.show_clock') }} <input
                            v-model="form.show_clock" type="checkbox" /></label>

                    <template v-if="form.template === 'tv'">
                        <label class="mt-3 flex items-center justify-between text-sm">
                            {{ $t('admin.layout.lock_home_scroll', undefined, 'Lock the home screen (no scrolling)') }}
                            <input v-model="form.lock_home_scroll" type="checkbox" data-testid="lock-scroll" />
                        </label>
                        <label :class="label">{{ $t('admin.layout.tile_size', undefined, 'Tile size') }}</label>
                        <select v-model="form.tile_size" :class="field" data-testid="tile-size">
                            <option v-for="s in (Object.keys(TILE_DIMENSIONS) as TileSize[])" :key="s" :value="s">{{
                                s[0].toUpperCase() + s.slice(1) }}</option>
                        </select>
                        <label :class="label">{{ $t('admin.layout.headline_label') }}</label>
                        <input v-model="form.headline" maxlength="80" placeholder="e.g. Welcome to Grand Horizon"
                            :class="field" />
                        <label :class="label">
                            {{ $t('admin.layout.tagline_label') }}
                        </label>
                        <input v-model="form.tagline" maxlength="80" placeholder="e.g. A Smarter Stay" :class="field" />
                    </template>
                </div>

                <!-- menu items -->
                <div class="mt-4 rounded-lg border border-slate-200 bg-white p-4">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-semibold text-slate-700">{{ $t('admin.layout.menu_items', undefined, 'Menu items') }}
                        </p>
                        <button type="button" class="text-xs text-slate-400 hover:underline" @click="resetItems">{{
                            $t('admin.layout.reset_defaults', undefined, 'Reset to defaults') }}</button>
                    </div>

                    <ul class="mt-2 space-y-2" data-testid="items">
                        <li v-for="(item, i) in form.items" :key="i"
                            class="flex items-center gap-2 rounded border border-slate-200 bg-slate-50 p-2 text-sm">
                            <input v-model="item.icon" maxlength="4" class="w-10 rounded border border-slate-300 p-1 text-center"
                                :aria-label="`Icon ${i + 1}`" />
                            <input v-model="item.label" class="flex-1 rounded border border-slate-300 px-2 py-1"
                                :aria-label="`Label ${i + 1}`" />
                            <input v-model="item.href" class="w-28 rounded border border-slate-300 px-2 py-1 font-mono text-xs"
                                :aria-label="`Href ${i + 1}`" />
                            <button type="button" class="px-1 text-slate-400 hover:text-slate-700" :disabled="i === 0"
                                @click="move(i, -1)">↑</button>
                            <button type="button" class="px-1 text-slate-400 hover:text-slate-700"
                                :disabled="i === form.items.length - 1" @click="move(i, 1)">↓</button>
                            <button type="button" class="px-1 text-red-500 hover:text-red-700"
                                @click="removeItem(i)">✕</button>
                        </li>
                    </ul>

                    <button type="button" class="mt-3 text-xs font-semibold text-slate-700 hover:underline"
                        data-testid="add-item" @click="addItem">+ {{ $t('admin.layout.add_item', undefined, 'Add menu item') }}</button>
                </div>

                <div class="mt-4 flex items-center gap-2">
                    <button type="submit" :disabled="form.processing"
                        class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800 disabled:opacity-50"
                        data-testid="save">
                        {{ form.processing ? $t('common.saving', undefined, 'Saving...') : (selected_branch ? (locale === 'ar' ? 'حفظ تخطيط الفرع' : 'Save Branch Layout') : $t('common.save', undefined, 'Save')) }}
                    </button>
                    <span v-if="form.isDirty" class="text-xs text-amber-600">{{ $t('admin.layout.unsaved_hint', undefined, 'Unsaved changes') }}</span>
                </div>
            </form>

            <!-- ============ live preview ============ -->
            <div class="min-w-0">
                <div class="sticky top-6">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{
                            $t('admin.layout.preview_label', undefined, 'Live preview') }}</span>
                        <div class="flex gap-1 text-xs">
                            <button type="button" class="rounded px-2 py-1 font-medium transition"
                                :class="previewTab === 'home' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100'"
                                @click="previewTab = 'home'">{{ $t('common.home', undefined, 'Home') }}</button>
                            <button type="button" class="rounded px-2 py-1 font-medium transition"
                                :class="previewTab === 'other' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100'"
                                @click="previewTab = 'other'">{{ $t('admin.layout.preview_subpage', undefined, 'Subpage') }}</button>
                        </div>
                    </div>

                    <div
                        class="mt-2 aspect-[16/10] w-full overflow-hidden rounded-xl border border-slate-300 bg-slate-900 shadow-xl">
                        <TvShell v-if="previewConfig.template === 'tv'" :hotel="previewHotel" :config="previewConfig">
                            <div class="p-6 text-white">
                                <h2 class="text-2xl font-light">{{ previewTab === 'home' ? (previewConfig.headline ||
                                    'Welcome') : 'Dining & Restaurants' }}</h2>
                                <p class="mt-2 text-sm text-slate-300">{{ previewTab === 'home' ? (previewConfig.tagline
                                    || 'Explore our amenities') : 'Browse our on-site dining and room service menus.' }}
                                </p>
                            </div>
                        </TvShell>
                        <ClassicShell v-else :hotel="previewHotel" :config="previewConfig">
                            <div class="p-6">
                                <h2 class="text-2xl font-light text-slate-900">{{ previewTab === 'home' ? 'Welcome to Grand Horizon' : 'Dining & Restaurants' }}</h2>
                                <p class="mt-2 text-sm text-slate-600">{{ previewTab === 'home' ? 'A luxury retreat on the Red Sea.' : 'Browse our on-site dining and room service menus.' }}</p>
                            </div>
                        </ClassicShell>
                    </div>
                    <p class="mt-2 text-center text-xs text-slate-400">{{ $t('admin.layout.preview_hint', undefined, 'Preview shows layout structure. Real guest pages use live content & theme.') }}</p>
                </div>
            </div>
        </div>
    </div>
</template>
