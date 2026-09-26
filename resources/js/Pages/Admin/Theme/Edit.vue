<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useI18n } from '@/i18n';
import { AdminCard, AdminInput, AdminSelect, AdminButton } from '@/Components/Admin';

defineOptions({ layout: AdminLayout });

interface ThemeModel {
    id?: number;
    hotel_branch_id?: number | null;
    name: string;
    primary_color: string;
    secondary_color: string;
    header_bg?: string;
    footer_bg?: string;
    font_family: string;
    border_radius: string;
    button_style: string;
    card_style: string;
}

interface ThemePreset {
    id: string;
    name: string;
    name_ar?: string;
    description: string;
    primary_color: string;
    secondary_color: string;
    header_bg?: string;
    footer_bg?: string;
}

interface BranchItem {
    id: number;
    name: string;
    slug: string;
    city?: string | null;
}

const props = defineProps<{
    theme: ThemeModel;
    presets: ThemePreset[];
    flash_ok?: string | null;
    branches?: BranchItem[];
    selected_branch_id?: number | null;
    selected_branch?: BranchItem | null;
    has_custom_branch_theme?: boolean;
}>();

const { t, locale } = useI18n();

const form = useForm({
    hotel_branch_id: props.selected_branch_id ?? null,
    name: props.theme?.name ?? (props.selected_branch ? `${props.selected_branch.name} Theme` : 'Guest Layout Theme'),
    primary_color: props.theme?.primary_color ?? '#059669',
    secondary_color: props.theme?.secondary_color ?? '#10B981',
    header_bg: props.theme?.header_bg ?? '#064e3b',
    footer_bg: props.theme?.footer_bg ?? '#022c22',
    font_family: props.theme?.font_family ?? 'Instrument Sans',
    border_radius: props.theme?.border_radius ?? 'medium',
    button_style: props.theme?.button_style ?? 'rounded',
    card_style: props.theme?.card_style ?? 'elevated',
});

const previewMode = ref<'classic' | 'tv'>('classic');

function applyPreset(preset: ThemePreset) {
    form.primary_color = preset.primary_color;
    form.secondary_color = preset.secondary_color;
    if (preset.header_bg) form.header_bg = preset.header_bg;
    if (preset.footer_bg) form.footer_bg = preset.footer_bg;
}

function autoMatchHeaderFooter() {
    form.header_bg = deriveDarkenedHex(form.primary_color, 0.35);
    form.footer_bg = deriveDarkenedHex(form.primary_color, 0.22);
}

function deriveDarkenedHex(hex: string, factor = 0.35): string {
    let clean = hex.replace('#', '');
    if (clean.length === 3) clean = clean.split('').map(c => c + c).join('');
    if (clean.length !== 6) return '#0b0e13';
    const r = Math.max(0, Math.min(255, Math.round(parseInt(clean.substring(0, 2), 16) * factor)));
    const g = Math.max(0, Math.min(255, Math.round(parseInt(clean.substring(2, 4), 16) * factor)));
    const b = Math.max(0, Math.min(255, Math.round(parseInt(clean.substring(4, 6), 16) * factor)));
    return `#${r.toString(16).padStart(2, '0')}${g.toString(16).padStart(2, '0')}${b.toString(16).padStart(2, '0')}`;
}

function selectBranch(branchId: number | null) {
    router.get('/admin/theme', branchId ? { branch_id: branchId } : {}, {
        preserveState: false,
    });
}

function resetToMasterTheme() {
    if (!props.selected_branch_id) return;
    const msg = locale.value === 'ar'
        ? 'هل أنت متأكد من رغبتك في حذف المظهر المخصص لهذا الفرع والرجوع لوراثة المظهر العام للفندق؟'
        : 'Are you sure you want to reset this branch theme to inherit the hotel master theme?';
    if (confirm(msg)) {
        router.delete('/admin/theme/branch-reset', {
            data: { hotel_branch_id: props.selected_branch_id },
        });
    }
}

function save() {
    form.put('/admin/theme');
}

const fontOptions = [
    { value: 'Instrument Sans', label: 'Instrument Sans (Clean, Modern)' },
    { value: 'Fraunces', label: 'Fraunces (Editorial, Luxury Serif)' },
    { value: 'Cairo', label: 'Cairo (Arabic & Modern Sans)' },
    { value: 'Tajawal', label: 'Tajawal (Arabic & English Geometric)' },
    { value: 'Inter', label: 'Inter (Technical & Neutral)' },
];

const radiusOptions = [
    { value: 'none', label: 'None (0px sharp corners)' },
    { value: 'small', label: 'Small (4px subtle)' },
    { value: 'medium', label: 'Medium (8px modern balanced)' },
    { value: 'large', label: 'Large (16px soft)' },
    { value: 'full', label: 'Full (Pill shapes)' },
];

const buttonOptions = [
    { value: 'rounded', label: 'Rounded' },
    { value: 'pill', label: 'Pill' },
    { value: 'square', label: 'Square' },
];

const cardOptions = [
    { value: 'elevated', label: 'Elevated (Subtle Shadow)' },
    { value: 'outlined', label: 'Outlined (Clean Border)' },
    { value: 'flat', label: 'Flat' },
];
</script>

<template>
    <div class="max-w-6xl space-y-6">
        <div>
            <h1 class="text-xl font-semibold text-slate-800 mb-1">
                {{ locale === 'ar' ? 'مظهر وتصميم تجربة النزيل' : 'Guest Layout Theme & Colors' }}
            </h1>
            <p class="text-sm text-slate-500">
                {{ locale === 'ar'
                    ? 'تحكم في ألوان وهوية موقع النزيل وتطبيق الشاشات الذكية، سواء للمظهر العام أو لفروع محددة.'
                    : 'Customize primary branding colors, header bar, and dock colors seen by guests across the main hotel and individual branches.'
                }}
            </p>
        </div>

        <!-- Branch / Master Theme Selector Tabs -->
        <div v-if="branches && branches.length > 0" class="flex items-center gap-2 border-b border-slate-200 pb-3 overflow-x-auto">
            <button
                type="button"
                class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold rounded-lg transition-colors whitespace-nowrap"
                :class="!selected_branch_id ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                @click="selectBranch(null)"
            >
                <span>🏨</span>
                <span>{{ locale === 'ar' ? 'المظهر العام للفندق (كل الفروع)' : 'Hotel Master Theme (All Branches)' }}</span>
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
            <div v-if="has_custom_branch_theme" class="rounded-lg bg-blue-50 border border-blue-200 p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <span class="text-base">🎨</span>
                    <div>
                        <h3 class="text-xs font-semibold text-blue-900">
                            {{ locale === 'ar' ? `مظهر مخصص نشط لفرع: ${selected_branch.name}` : `Custom Theme Active for: ${selected_branch.name}` }}
                        </h3>
                        <p class="text-xs text-blue-700 mt-0.5">
                            {{ locale === 'ar' ? 'هذا الفرع يستخدم حالياً مظهراً مستقلاً وخاصاً به عند زيارة النزلاء لموقعه.' : 'This branch is currently rendered with its own dedicated theme palette.' }}
                        </p>
                    </div>
                </div>
                <button
                    type="button"
                    class="text-xs font-medium text-red-600 hover:text-red-700 underline shrink-0"
                    @click="resetToMasterTheme"
                >
                    {{ locale === 'ar' ? 'إعادة التعيين لوراثة المظهر العام' : 'Reset to Master Theme' }}
                </button>
            </div>
            <div v-else class="rounded-lg bg-amber-50 border border-amber-200 p-4 flex items-center gap-2.5">
                <span class="text-base">ℹ️</span>
                <div>
                    <h3 class="text-xs font-semibold text-amber-900">
                        {{ locale === 'ar' ? `يرث المظهر العام للفندق: ${selected_branch.name}` : `Inheriting Master Theme: ${selected_branch.name}` }}
                    </h3>
                    <p class="text-xs text-amber-700 mt-0.5">
                        {{ locale === 'ar' ? 'يقوم هذا الفرع حالياً بوراثة المظهر العام للفندق. عند تعديل أي ألوان وحفظها، سيتم إنشاء مظهر مخصص لهذا الفرع فقط.' : 'This branch currently inherits the master hotel styling. Modifying colors and clicking save will publish a custom theme override for this branch.' }}
                    </p>
                </div>
            </div>
        </div>

        <div v-if="flash_ok && !form.isDirty" class="rounded-lg bg-emerald-50 border border-emerald-200 p-4 flex items-center gap-3">
            <svg class="h-5 w-5 text-emerald-600 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
            </svg>
            <span class="text-sm font-medium text-emerald-800">{{ flash_ok }}</span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <!-- Left Column: Controls & Presets -->
            <form class="lg:col-span-7 space-y-6" @submit.prevent="save">
                <!-- Color Presets -->
                <AdminCard
                    :title="locale === 'ar' ? 'أنماط الألوان الجاهزة (يدعم الأخضر)' : 'Curated Color Presets (Green Styles & More)'"
                    :subtitle="locale === 'ar' ? 'اختر نمطاً جاهزاً لتطبيق ألوان متناسقة للواجهة والشريطين العلوي والسفلي' : 'Select a pre-tuned luxury hospitality palette with matching header and footer colors'"
                >
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <button
                            v-for="preset in presets"
                            :key="preset.id"
                            type="button"
                            class="flex items-start gap-3 p-3 rounded-lg border text-start transition-all"
                            :class="[
                                form.primary_color === preset.primary_color && form.header_bg === preset.header_bg
                                    ? 'border-slate-800 bg-slate-50/80 shadow-xs ring-2 ring-slate-800/15'
                                    : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50/40'
                            ]"
                            @click="applyPreset(preset)"
                        >
                            <div class="flex -space-x-1 rtl:space-x-reverse pt-0.5 shrink-0">
                                <span
                                    class="h-6 w-6 rounded-full border-2 border-white shadow-xs"
                                    :style="{ backgroundColor: preset.primary_color }"
                                    :title="'Primary: ' + preset.primary_color"
                                />
                                <span
                                    class="h-6 w-6 rounded-full border-2 border-white shadow-xs"
                                    :style="{ backgroundColor: preset.header_bg || preset.secondary_color }"
                                    :title="'Header: ' + (preset.header_bg || preset.secondary_color)"
                                />
                            </div>
                            <div class="min-w-0 flex-1">
                                <span class="block text-xs font-semibold text-slate-800 truncate">
                                    {{ locale === 'ar' && preset.name_ar ? preset.name_ar : preset.name }}
                                </span>
                                <span class="block text-[11px] text-slate-400 mt-0.5 line-clamp-1">
                                    {{ preset.description }}
                                </span>
                            </div>
                        </button>
                    </div>
                </AdminCard>

                <!-- Primary & Secondary Accent Colors -->
                <AdminCard
                    :title="locale === 'ar' ? 'ألوان الهوية والتمييز' : 'Brand Accent Colors'"
                    :subtitle="locale === 'ar' ? 'اللون الرئيسي للأزرار والعناصر البارزة واللون الثانوي للتفاصيل الفاخرة' : 'Primary color for active buttons and secondary for subtle luxury highlights'"
                >
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Primary Color -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                {{ locale === 'ar' ? 'اللون الرئيسي (Primary)' : 'Primary Brand Color' }}
                            </label>
                            <div class="flex items-center gap-2">
                                <input
                                    v-model="form.primary_color"
                                    type="color"
                                    class="h-10 w-12 rounded border border-slate-300 cursor-pointer p-0.5 bg-white"
                                />
                                <AdminInput
                                    v-model="form.primary_color"
                                    type="text"
                                    maxlength="7"
                                    placeholder="#059669"
                                    :error="form.errors.primary_color"
                                    class="font-mono"
                                />
                            </div>
                            <p class="mt-1 text-xs text-slate-400">
                                {{ locale === 'ar' ? 'يُستخدم للأزرار النشطة، والعناصر البارزة' : 'Active navigation buttons, badges, and primary accents.' }}
                            </p>
                        </div>

                        <!-- Secondary Color -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                {{ locale === 'ar' ? 'اللون الثانوي (Secondary / Accent)' : 'Secondary / Accent Color' }}
                            </label>
                            <div class="flex items-center gap-2">
                                <input
                                    v-model="form.secondary_color"
                                    type="color"
                                    class="h-10 w-12 rounded border border-slate-300 cursor-pointer p-0.5 bg-white"
                                />
                                <AdminInput
                                    v-model="form.secondary_color"
                                    type="text"
                                    maxlength="7"
                                    placeholder="#10B981"
                                    :error="form.errors.secondary_color"
                                    class="font-mono"
                                />
                            </div>
                            <p class="mt-1 text-xs text-slate-400">
                                {{ locale === 'ar' ? 'يُستخدم للإبرازات والتفاصيل الثانوية' : 'Highlights, tags, and subtle luxury details.' }}
                            </p>
                        </div>
                    </div>
                </AdminCard>

                <!-- Header & Footer / Dock Colors -->
                <AdminCard
                    :title="locale === 'ar' ? 'ألوان الشريط العلوي (Header) والقائمة السفلية (Footer / Dock)' : 'Header & Footer / Bottom Dock Colors'"
                    :subtitle="locale === 'ar' ? 'تحكم بدقة في لون الشريط العلوي ولون شريط التنقل السفلي والفوتر' : 'Set custom colors for the fixed top bar and the bottom navigation dock & footer'"
                >
                    <template #actions>
                        <button
                            type="button"
                            class="text-xs text-slate-600 hover:text-slate-900 underline font-medium"
                            @click="autoMatchHeaderFooter"
                        >
                            {{ locale === 'ar' ? 'ملاءمة تلقائية مع اللون الرئيسي' : 'Auto-match from Primary' }}
                        </button>
                    </template>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Header Background Color -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                {{ locale === 'ar' ? 'لون الشريط العلوي (Top Header Bar)' : 'Top Header Bar Color' }}
                            </label>
                            <div class="flex items-center gap-2">
                                <input
                                    v-model="form.header_bg"
                                    type="color"
                                    class="h-10 w-12 rounded border border-slate-300 cursor-pointer p-0.5 bg-white"
                                />
                                <AdminInput
                                    v-model="form.header_bg"
                                    type="text"
                                    maxlength="7"
                                    placeholder="#064e3b"
                                    :error="form.errors.header_bg"
                                    class="font-mono"
                                />
                            </div>
                            <p class="mt-1 text-xs text-slate-400">
                                {{ locale === 'ar' ? 'لون شريط الهوية واللغة والوقت في أعلى الصفحة' : 'Applies to top bar with glassmorphic transparency.' }}
                            </p>
                        </div>

                        <!-- Footer / Dock Background Color -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                {{ locale === 'ar' ? 'لون القائمة السفلية والفوتر (Bottom Dock & Footer)' : 'Bottom Dock & Footer Color' }}
                            </label>
                            <div class="flex items-center gap-2">
                                <input
                                    v-model="form.footer_bg"
                                    type="color"
                                    class="h-10 w-12 rounded border border-slate-300 cursor-pointer p-0.5 bg-white"
                                />
                                <AdminInput
                                    v-model="form.footer_bg"
                                    type="text"
                                    maxlength="7"
                                    placeholder="#022c22"
                                    :error="form.errors.footer_bg"
                                    class="font-mono"
                                />
                            </div>
                            <p class="mt-1 text-xs text-slate-400">
                                {{ locale === 'ar' ? 'لون شريط الأزرار البيضاوية بالأسفل وفوتر الموقع' : 'Applies to the fixed bottom pill dock and page footer.' }}
                            </p>
                        </div>
                    </div>
                </AdminCard>

                <!-- Typography & Shapes -->
                <AdminCard
                    :title="locale === 'ar' ? 'الخطوط والأشكال' : 'Typography & Geometry'"
                    :subtitle="locale === 'ar' ? 'حدد نمط الخطوط وانحناء الحواف' : 'Configure font family and component corner roundness'"
                >
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <AdminSelect
                            v-model="form.font_family"
                            :label="locale === 'ar' ? 'نوع الخط' : 'Font Family'"
                            :options="fontOptions"
                        />
                        <AdminSelect
                            v-model="form.border_radius"
                            :label="locale === 'ar' ? 'انحناء الحواف' : 'Border Radius'"
                            :options="radiusOptions"
                        />
                        <AdminSelect
                            v-model="form.button_style"
                            :label="locale === 'ar' ? 'شكل الأزرار' : 'Button Style'"
                            :options="buttonOptions"
                        />
                        <AdminSelect
                            v-model="form.card_style"
                            :label="locale === 'ar' ? 'شكل البطاقات' : 'Card Style'"
                            :options="cardOptions"
                        />
                    </div>
                </AdminCard>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <AdminButton
                        type="submit"
                        :loading="form.processing"
                        :disabled="form.processing"
                        size="lg"
                    >
                        {{ form.processing ? (locale === 'ar' ? 'جاري الحفظ...' : 'Saving…') : (locale === 'ar' ? 'حفظ ونشر المظهر' : (selected_branch ? 'Save Branch Theme' : 'Save & Publish Theme')) }}
                    </AdminButton>
                </div>
            </form>

            <!-- Right Column: Live Guest Layout Preview -->
            <div class="lg:col-span-5 space-y-4 lg:sticky lg:top-6">
                <AdminCard
                    :title="locale === 'ar' ? 'معاينة حية للمظهر' : 'Live Guest Experience Preview'"
                    :subtitle="locale === 'ar' ? 'شاهد تأثير ألوان الشريط والفوتر مباشرة' : 'Real-time look at how header and dock colors render for guests'"
                >
                    <template #actions>
                        <div class="flex items-center gap-1 rounded-md bg-slate-100 p-0.5 text-xs">
                            <button
                                type="button"
                                class="px-2.5 py-1 rounded transition-colors"
                                :class="previewMode === 'classic' ? 'bg-white font-medium text-slate-800 shadow-2xs' : 'text-slate-600 hover:text-slate-900'"
                                @click="previewMode = 'classic'"
                            >
                                Classic Dock
                            </button>
                            <button
                                type="button"
                                class="px-2.5 py-1 rounded transition-colors"
                                :class="previewMode === 'tv' ? 'bg-white font-medium text-slate-800 shadow-2xs' : 'text-slate-600 hover:text-slate-900'"
                                @click="previewMode = 'tv'"
                            >
                                Smart TV
                            </button>
                        </div>
                    </template>

                    <!-- Preview Frame -->
                    <div class="rounded-xl overflow-hidden border border-slate-300 bg-slate-900 text-white shadow-md">
                        <!-- Simulated Classic Layout -->
                        <div v-if="previewMode === 'classic'" class="relative aspect-video flex flex-col justify-between p-3 sm:p-4 bg-gradient-to-b from-slate-950/90 via-slate-900 to-slate-950">
                            <!-- Top Bar with live Header Color -->
                            <div
                                class="flex items-center justify-between border-b border-white/15 px-3 py-2 rounded-lg transition-colors shadow-xs backdrop-blur-md"
                                :style="{ background: form.header_bg ? form.header_bg + 'e6' : 'rgba(10, 12, 16, 0.85)' }"
                            >
                                <div class="flex items-center gap-2">
                                    <span class="h-2.5 w-2.5 rounded-full" :style="{ backgroundColor: form.primary_color }" />
                                    <span class="text-xs sm:text-sm font-semibold tracking-wide">
                                        {{ selected_branch ? selected_branch.name : 'Grand Horizon' }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-2 text-[11px] text-white/70">
                                    <span class="hidden sm:inline">English</span>
                                    <span>14:30</span>
                                </div>
                            </div>

                            <!-- Mock Content Body -->
                            <div class="space-y-2 py-4">
                                <div class="inline-block px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider rounded text-white" :style="{ backgroundColor: form.primary_color }">
                                    Featured
                                </div>
                                <div class="text-sm sm:text-base font-medium text-white/90">
                                    {{ selected_branch ? `Welcome to ${selected_branch.name}` : 'Welcome to Grand Horizon Luxury Resort' }}
                                </div>
                                <p class="text-xs text-white/50 line-clamp-2">
                                    Experience ultimate relaxation with tailored dining, spa wellness, and panoramic sea views.
                                </p>
                            </div>

                            <!-- Bottom Floating Dock with live Footer Color -->
                            <div
                                class="mx-auto flex items-center gap-2 px-3 py-1.5 rounded-full border border-white/20 transition-colors shadow-lg backdrop-blur-md"
                                :style="{ background: form.footer_bg ? form.footer_bg + 'f0' : 'rgba(2, 44, 34, 0.92)' }"
                            >
                                <button
                                    type="button"
                                    class="px-2.5 py-1 rounded-full text-xs font-semibold text-white shadow-xs transition-colors"
                                    :style="{ backgroundColor: form.primary_color }"
                                >
                                    Home
                                </button>
                                <button
                                    type="button"
                                    class="px-2.5 py-1 rounded-full text-xs text-white/80 hover:text-white transition-colors"
                                >
                                    Rooms
                                </button>
                                <button
                                    type="button"
                                    class="px-2.5 py-1 rounded-full text-xs text-white/80 hover:text-white transition-colors"
                                >
                                    Dining
                                </button>
                                <button
                                    type="button"
                                    class="px-2 py-1 rounded-full text-xs font-bold transition-colors"
                                    :style="{ color: form.secondary_color }"
                                >
                                    ✦
                                </button>
                            </div>
                        </div>

                        <!-- Simulated Smart TV Layout -->
                        <div v-else class="relative aspect-video flex flex-col justify-between p-3 sm:p-4 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-950">
                            <!-- TV Brand Header -->
                            <div
                                class="flex items-center justify-between p-2 rounded-lg transition-colors"
                                :style="{ background: form.header_bg ? form.header_bg + 'cc' : 'transparent' }"
                            >
                                <div class="flex items-center gap-2">
                                    <span class="h-2 w-2 rounded-full" :style="{ backgroundColor: form.primary_color }" />
                                    <span class="text-sm sm:text-base font-semibold">
                                        {{ selected_branch ? selected_branch.name : 'Grand Horizon' }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-2 text-xs text-white/60">
                                    <span>24°C Sunny</span>
                                    <span>14:30</span>
                                </div>
                            </div>

                            <!-- Big Tile Grid with live dock color backdrop -->
                            <div
                                class="p-2 rounded-xl transition-colors mt-auto"
                                :style="{ background: form.footer_bg ? form.footer_bg + 'dd' : 'transparent' }"
                            >
                                <div class="grid grid-cols-4 gap-2">
                                    <div
                                        class="p-2 rounded-lg flex flex-col justify-between aspect-4/3 ring-2 shadow-lg"
                                        :style="{
                                            backgroundColor: form.primary_color,
                                            borderColor: '#ffffff',
                                        }"
                                    >
                                        <span class="text-sm">🏨</span>
                                        <span class="text-[10px] sm:text-xs font-semibold truncate">Rooms</span>
                                    </div>
                                    <div class="p-2 rounded-lg flex flex-col justify-between aspect-4/3 bg-white/10 border border-white/15">
                                        <span class="text-sm">☀️</span>
                                        <span class="text-[10px] sm:text-xs text-slate-300 truncate">Weather</span>
                                    </div>
                                    <div class="p-2 rounded-lg flex flex-col justify-between aspect-4/3 bg-white/10 border border-white/15">
                                        <span class="text-sm">📍</span>
                                        <span class="text-[10px] sm:text-xs text-slate-300 truncate">Branches</span>
                                    </div>
                                    <div class="p-2 rounded-lg flex flex-col justify-between aspect-4/3 bg-white/10 border border-white/15">
                                        <span class="text-sm">🍽️</span>
                                        <span class="text-[10px] sm:text-xs text-slate-300 truncate">Dining</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Palette summary token list -->
                    <div class="mt-4 p-3 rounded-lg bg-slate-50 border border-slate-200/80 text-xs text-slate-600 space-y-2">
                        <div class="flex items-center justify-between font-medium text-slate-800">
                            <span>{{ locale === 'ar' ? 'ملخص درجات المظهر النشطة' : 'Active Theme Palette' }}</span>
                            <span class="font-mono text-[10px] text-slate-400">Tokens</span>
                        </div>
                        <div class="grid grid-cols-2 gap-2 pt-1 font-mono text-[11px]">
                            <div class="flex items-center gap-2">
                                <span class="h-3 w-3 rounded-full border border-slate-300 shrink-0" :style="{ backgroundColor: form.primary_color }" />
                                <span class="truncate">Primary: {{ form.primary_color }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="h-3 w-3 rounded-full border border-slate-300 shrink-0" :style="{ backgroundColor: form.secondary_color }" />
                                <span class="truncate">Accent: {{ form.secondary_color }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="h-3 w-3 rounded-full border border-slate-300 shrink-0" :style="{ backgroundColor: form.header_bg }" />
                                <span class="truncate">Header: {{ form.header_bg }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="h-3 w-3 rounded-full border border-slate-300 shrink-0" :style="{ backgroundColor: form.footer_bg }" />
                                <span class="truncate">Footer: {{ form.footer_bg }}</span>
                            </div>
                        </div>
                    </div>
                </AdminCard>
            </div>
        </div>
    </div>
</template>
