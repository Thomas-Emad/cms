<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import BilingualTabs from '@/Components/Admin/BilingualTabs.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useI18n } from '@/i18n';
import MediaManager from '@/Components/Admin/MediaManager.vue';
import TagListInput from '@/Components/Admin/TagListInput.vue';
import type { HotelBranch, MediaItem } from '@/types/branch';
import { AdminCard, AdminInput, AdminTextarea, AdminSelect, AdminButton, PageHeader } from '@/Components/Admin';

defineOptions({ layout: AdminLayout });

const props = defineProps<{
    branch: HotelBranch | null;
    cover: MediaItem[];
    gallery: MediaItem[];
}>();

const { t } = useI18n();
const isEdit = !!props.branch;
const activeTab = ref<'en' | 'ar'>('en');

const rawTranslations = (props.branch as any)?.translations_data?.ar ?? {};

const form = useForm({
    name: props.branch?.name ?? '',
    slug: props.branch?.slug ?? '',
    city: props.branch?.city ?? '',
    address: props.branch?.address ?? '',
    phone: props.branch?.phone ?? '',
    email: props.branch?.email ?? '',
    short_description: props.branch?.short_description ?? '',
    description: props.branch?.description ?? '',
    cover_image_url: props.branch?.cover_image_url ?? '',
    gallery_urls: [...(props.branch?.gallery_urls ?? [])],
    features: [...(props.branch?.features ?? [])],
    latitude: props.branch?.latitude ?? null,
    longitude: props.branch?.longitude ?? null,
    status: props.branch?.status ?? 'published',
    is_main: Boolean(props.branch?.is_main),
    sort_order: props.branch?.sort_order ?? 0,
    translations: {
        ar: {
            name: rawTranslations.name ?? '',
            city: rawTranslations.city ?? '',
            address: rawTranslations.address ?? '',
            short_description: rawTranslations.short_description ?? '',
            description: rawTranslations.description ?? '',
        },
    },
});

const submit = () => {
    if (isEdit) {
        form.put(`/admin/branches/${props.branch!.id}`);
    } else {
        form.post('/admin/branches');
    }
};

const statuses = [
    { value: 'draft', label: 'Draft (hidden)' },
    { value: 'published', label: 'Published' },
    { value: 'archived', label: 'Archived' },
];
</script>

<template>
    <div class="max-w-4xl space-y-6">
        <PageHeader
            :title="isEdit ? t('branches.edit', undefined, 'Edit Branch') : t('branches.create', undefined, 'Add Branch')"
            :subtitle="isEdit ? t('branches.edit_desc', undefined, 'Update branch location details, contact info, and amenities.') : t('branches.create_desc', undefined, 'Add a new location or regional property to your hotel chain.')"
            back-url="/admin/branches"
            :back-label="t('branches.all_branches', undefined, 'All Branches')"
        />

        <form @submit.prevent="submit" class="space-y-6">
            <AdminCard>
                <div class="mb-5">
                    <BilingualTabs v-model="activeTab" />
                </div>

                <!-- Arabic Translation Fields -->
                <div v-if="activeTab === 'ar'" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <AdminInput
                            v-model="form.translations.ar.name"
                            :label="t('admin.common.name_ar', undefined, 'Name (AR) / اسم الفرع بالعربية')"
                            dir="rtl"
                            :placeholder="form.name || 'مثال: فندق سمارتيل القاهرة (جاردن سيتي)'"
                            :hint="`EN: ${form.name || '—'}`"
                        />

                        <AdminInput
                            v-model="form.translations.ar.city"
                            :label="t('branches.city_ar', undefined, 'City (AR) / المدينة بالعربية')"
                            dir="rtl"
                            :placeholder="form.city || 'مثال: القاهرة، مصر'"
                            :hint="`EN: ${form.city || '—'}`"
                        />
                    </div>

                    <AdminInput
                        v-model="form.translations.ar.address"
                        :label="t('branches.address_ar', undefined, 'Address (AR) / العنوان بالعربية')"
                        dir="rtl"
                        :placeholder="form.address || 'مثال: ١١١٣ كورنيش النيل، جاردن سيتي، القاهرة'"
                    />

                    <AdminInput
                        v-model="form.translations.ar.short_description"
                        :label="t('admin.common.short_description_ar', undefined, 'Short Description (AR) / الوصف القصير بالعربية')"
                        dir="rtl"
                        :placeholder="form.short_description || 'نبذة تعريفية مختصرة عن هذا الفرع بالعربية...'"
                    />

                    <AdminTextarea
                        v-model="form.translations.ar.description"
                        :label="t('admin.common.description_ar', undefined, 'Description (AR) / الوصف التفصيلي بالعربية')"
                        :rows="4"
                        dir="rtl"
                        :placeholder="form.description || 'الوصف الكامل والشامل للفرع ومرافقه بالعربية...'"
                    />
                </div>

                <!-- English & Base Fields -->
                <div v-else class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <AdminInput
                            v-model="form.name"
                            :label="t('admin.common.name_en', undefined, 'Name (EN)')"
                            :error="form.errors.name"
                            placeholder="e.g. Smarttel Hotel Cairo (Garden City)"
                            required
                        />
                        <AdminInput
                            v-model="form.slug"
                            :label="t('admin.common.slug', undefined, 'Slug')"
                            :error="form.errors.slug"
                            placeholder="auto from name (e.g. smarttel-cairo)"
                            required
                        />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <AdminInput
                            v-model="form.city"
                            :label="t('branches.city', undefined, 'City / Region')"
                            :error="form.errors.city"
                            placeholder="e.g. Cairo, Egypt"
                        />
                        <AdminInput
                            v-model="form.address"
                            :label="t('branches.address', undefined, 'Physical Address')"
                            :error="form.errors.address"
                            placeholder="e.g. 1113 Corniche El Nil, Garden City, Cairo"
                        />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <AdminInput
                            v-model="form.phone"
                            :label="t('branches.phone', undefined, 'Contact Phone')"
                            :error="form.errors.phone"
                            placeholder="e.g. +20 2 2578 0444"
                        />
                        <AdminInput
                            v-model="form.email"
                            type="email"
                            :label="t('branches.email', undefined, 'Contact Email')"
                            :error="form.errors.email"
                            placeholder="e.g. cairo@smarttelhotel.com"
                        />
                    </div>

                    <AdminInput
                        v-model="form.short_description"
                        :label="t('admin.common.short_description_en', undefined, 'Short Description')"
                        :error="form.errors.short_description"
                        placeholder="Key highlight or one-sentence summary"
                    />

                    <AdminTextarea
                        v-model="form.description"
                        :label="t('admin.common.description_en', undefined, 'Full Description')"
                        :error="form.errors.description"
                        :rows="4"
                        placeholder="Comprehensive description of the branch property, setting, amenities, and location..."
                    />

                    <!-- Geographical Coordinates for Weather & Maps -->
                    <div class="rounded-lg bg-slate-50/80 p-4 border border-slate-200/80 space-y-3">
                        <div class="text-xs font-semibold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                            <span>📍</span>
                            <span>{{ t('branches.coordinates', undefined, 'Geographical Coordinates (for Weather & Map)') }}</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <AdminInput
                                v-model.number="form.latitude"
                                type="number"
                                step="any"
                                :label="t('branches.latitude', undefined, 'Latitude (-90 to 90)')"
                                :error="form.errors.latitude"
                                placeholder="e.g. 30.0444"
                            />
                            <AdminInput
                                v-model.number="form.longitude"
                                type="number"
                                step="any"
                                :label="t('branches.longitude', undefined, 'Longitude (-180 to 180)')"
                                :error="form.errors.longitude"
                                placeholder="e.g. 31.2357"
                            />
                        </div>
                    </div>

                    <!-- Amenities and Features -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">
                            {{ t('branches.features', undefined, 'Highlights & Amenities') }}
                        </label>
                        <TagListInput
                            v-model="form.features"
                            :placeholder="t('branches.features_placeholder', undefined, 'e.g. Nile River View, Infinity Pool — press Enter to add')"
                        />
                    </div>

                    <!-- Direct Photo URLs (Optional external links / Unsplash) -->
                    <div class="rounded-lg bg-slate-50/80 p-4 border border-slate-200/80 space-y-3">
                        <div class="text-xs font-semibold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                            <span>🖼️</span>
                            <span>{{ t('branches.direct_photos', undefined, 'Direct Photo URLs (External / Unsplash)') }}</span>
                        </div>
                        <AdminInput
                            v-model="form.cover_image_url"
                            :label="t('branches.cover_image_url', undefined, 'Cover Photo URL')"
                            :error="form.errors.cover_image_url"
                            placeholder="https://images.unsplash.com/..."
                        />
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">
                                {{ t('branches.gallery_urls', undefined, 'Gallery Photo URLs') }}
                            </label>
                            <TagListInput
                                v-model="form.gallery_urls"
                                :placeholder="t('branches.gallery_urls_placeholder', undefined, 'Paste image URL and press Enter to add')"
                            />
                        </div>
                    </div>

                    <!-- Status, Sorting, and Main Branch Flag -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end pt-2">
                        <AdminSelect
                            v-model="form.status"
                            :label="t('admin.common.status', undefined, 'Status')"
                            :options="statuses"
                        />
                        <AdminInput
                            v-model.number="form.sort_order"
                            type="number"
                            min="0"
                            :label="t('admin.common.sort_order', undefined, 'Display Order')"
                        />
                        <label class="flex items-center gap-2 pb-2.5 text-sm text-slate-700 cursor-pointer select-none">
                            <input
                                v-model="form.is_main"
                                type="checkbox"
                                class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 h-4 w-4"
                            />
                            <span class="font-medium text-slate-800">{{ t('branches.is_main_label', undefined, 'Primary / Main Branch') }}</span>
                        </label>
                    </div>
                </div>

                <template #footer>
                    <div class="flex justify-end pt-2">
                        <AdminButton
                            type="submit"
                            :loading="form.processing"
                            :disabled="form.processing"
                        >
                            {{ isEdit ? t('common.save', undefined, 'Save changes') : t('common.create', undefined, 'Create branch') }}
                        </AdminButton>
                    </div>
                </template>
            </AdminCard>
        </form>

        <!-- Media Upload Manager (for local file uploads) -->
        <AdminCard
            :title="t('branches.media_title', undefined, 'File Uploads & Media')"
            :subtitle="t('branches.media_desc', undefined, 'Upload photography files directly for this branch.')"
        >
            <template v-if="isEdit">
                <div class="space-y-6">
                    <MediaManager
                        mediable-type="branch"
                        :mediable-id="branch!.id"
                        collection="cover"
                        :items="cover"
                        :label="t('branches.main_photo', undefined, 'Cover Photo')"
                        :hint="t('branches.main_photo_hint', undefined, 'Displayed on cards and the hero of this branch.')"
                    />
                    <MediaManager
                        mediable-type="branch"
                        :mediable-id="branch!.id"
                        collection="gallery"
                        :items="gallery"
                        :label="t('branches.gallery', undefined, 'Gallery Photos')"
                        :hint="t('branches.gallery_hint', undefined, 'Guests can browse all branch photos in the gallery.')"
                    />
                </div>
            </template>
            <p v-else class="text-sm text-slate-500">
                {{ t('branches.save_first_media', undefined, 'Save the branch first, then you can upload photo files directly.') }}
            </p>
        </AdminCard>
    </div>
</template>
